<?php

declare(strict_types=1);

namespace OCA\AppAPI\Command\ExApp;

use OCA\AppAPI\Db\ExApp;
use OCA\AppAPI\DeployActions\DockerActions;
use OCA\AppAPI\DeployActions\ManualActions;
use OCA\AppAPI\Service\AppAPIService;
use OCA\AppAPI\Service\DaemonConfigService;
use OCA\AppAPI\Service\ExAppApiScopeService;
use OCA\AppAPI\Service\ExAppScopesService;
use OCA\AppAPI\Service\ExAppUsersService;

use OCP\DB\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Register extends Command {
	private AppAPIService $service;
	private DaemonConfigService $daemonConfigService;
	private ExAppApiScopeService $exAppApiScopeService;
	private ExAppScopesService $exAppScopesService;
	private ExAppUsersService $exAppUsersService;
	private DockerActions $dockerActions;
	private ManualActions $manualActions;

	public function __construct(
		AppAPIService        $service,
		DaemonConfigService  $daemonConfigService,
		ExAppApiScopeService $exAppApiScopeService,
		ExAppScopesService   $exAppScopesService,
		ExAppUsersService    $exAppUsersService,
		DockerActions        $dockerActions,
		ManualActions        $manualActions,
	) {
		parent::__construct();

		$this->service = $service;
		$this->daemonConfigService = $daemonConfigService;
		$this->exAppApiScopeService = $exAppApiScopeService;
		$this->exAppScopesService = $exAppScopesService;
		$this->exAppUsersService = $exAppUsersService;
		$this->dockerActions = $dockerActions;
		$this->manualActions = $manualActions;
	}

	protected function configure() {
		$this->setName('app_api:app:register');
		$this->setDescription('Register external app');

		$this->addArgument('appid', InputArgument::REQUIRED);
		$this->addArgument('daemon-config-name', InputArgument::REQUIRED);

		$this->addOption('enabled', 'e', InputOption::VALUE_NONE, 'Enable ExApp after registration');
		$this->addOption('force-scopes', null, InputOption::VALUE_NONE, 'Force scopes approval');
		$this->addOption('info-xml', null, InputOption::VALUE_REQUIRED, '[required] Path to ExApp info.xml file (url or local absolute path)');
		$this->addOption('json-info', null, InputOption::VALUE_REQUIRED, 'ExApp JSON deploy info');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$appId = $input->getArgument('appid');

		if ($this->service->getExApp($appId) !== null) {
			$output->writeln(sprintf('ExApp %s already registered.', $appId));
			return 2;
		}

		$daemonConfigName = $input->getArgument('daemon-config-name');
		$daemonConfig = $this->daemonConfigService->getDaemonConfigByName($daemonConfigName);
		if ($daemonConfig === null) {
			$output->writeln(sprintf('Daemon config %s not found.', $daemonConfigName));
			return 2;
		}

		if ($daemonConfig->getAcceptsDeployId() === $this->dockerActions->getAcceptsDeployId()) {
			$exAppInfo = $this->dockerActions->loadExAppInfo($appId, $daemonConfig);
		} elseif ($daemonConfig->getAcceptsDeployId() === $this->manualActions->getAcceptsDeployId()) {
			$exAppJson = $input->getOption('json-info');
			if ($exAppJson === null) {
				$output->writeln('ExApp JSON is required for manual deploy.');
				return 2;
			}

			$exAppInfo = $this->manualActions->loadExAppInfo($appId, $daemonConfig, [
				'json-info' => $exAppJson,
			]);
		} else {
			$output->writeln(sprintf('Daemon config %s actions for %s not found.', $daemonConfigName, $daemonConfig->getAcceptsDeployId()));
			return 2;
		}

		$appId = $exAppInfo['appid'];
		$version = $exAppInfo['version'];
		$name = $exAppInfo['name'];
		$protocol = $exAppInfo['protocol'] ?? 'http';
		$port = (int) $exAppInfo['port'];
		$host = $exAppInfo['host'];
		$secret = $exAppInfo['secret'];

		$exApp = $this->service->registerExApp($appId, [
			'version' => $version,
			'name' => $name,
			'daemon_config_name' => $daemonConfigName,
			'protocol' => $protocol,
			'host' => $host,
			'port' => $port,
			'secret' => $secret,
		]);

		if ($exApp !== null) {
			if (filter_var($exAppInfo['system_app'], FILTER_VALIDATE_BOOLEAN)) {
				try {
					$this->exAppUsersService->setupSystemAppFlag($exApp);
				} catch (Exception $e) {
					$output->writeln(sprintf('Error while setting app system flag: %s', $e->getMessage()));
					return 1;
				}
			}

			$pathToInfoXml = $input->getOption('info-xml');
			$infoXml = null;
			if ($pathToInfoXml !== null) {
				$infoXml = simplexml_load_string(file_get_contents($pathToInfoXml));
			}

			$requestedExAppScopeGroups = $this->service->getExAppRequestedScopes($exApp, $infoXml, $exAppInfo);
			if (isset($requestedExAppScopeGroups['error'])) {
				$output->writeln($requestedExAppScopeGroups['error']);
				// Fallback unregistering ExApp
				$this->service->unregisterExApp($exApp->getAppid());
				return 2;
			}

			$forceScopes = (bool) $input->getOption('force-scopes');
			$confirmRequiredScopes = $forceScopes;
			$confirmOptionalScopes = $forceScopes;

			if (!$forceScopes && $input->isInteractive()) {
				/** @var QuestionHelper $helper */
				$helper = $this->getHelper('question');

				// Prompt to approve required ExApp scopes
				if (count($requestedExAppScopeGroups['required']) > 0) {
					$output->writeln(sprintf('ExApp %s requested required scopes: %s', $appId, implode(', ', $requestedExAppScopeGroups['required'])));
					$question = new ConfirmationQuestion('Do you want to approve it? [y/N] ', false);
					$confirmRequiredScopes = $helper->ask($input, $output, $question);
				} else {
					$confirmRequiredScopes = true;
				}

				// Prompt to approve optional ExApp scopes
				if ($confirmRequiredScopes && count($requestedExAppScopeGroups['optional']) > 0) {
					$output->writeln(sprintf('ExApp %s requested optional scopes: %s', $appId, implode(', ', $requestedExAppScopeGroups['optional'])));
					$question = new ConfirmationQuestion('Do you want to approve it? [y/N] ', false);
					$confirmOptionalScopes = $helper->ask($input, $output, $question);
				}
			}

			if (!$confirmRequiredScopes && count($requestedExAppScopeGroups['required']) > 0) {
				$output->writeln(sprintf('ExApp %s required scopes not approved.', $appId));
				// Fallback unregistering ExApp
				$this->service->unregisterExApp($exApp->getAppid());
				return 1;
			}

			if (count($requestedExAppScopeGroups['required']) > 0) {
				$this->registerExAppScopes($output, $exApp, $requestedExAppScopeGroups['required'], 'required');
			}
			if ($confirmOptionalScopes && count($requestedExAppScopeGroups['optional']) > 0) {
				$this->registerExAppScopes($output, $exApp, $requestedExAppScopeGroups['optional'], 'optional');
			}

			$enabled = (bool) $input->getOption('enabled');
			if ($enabled) {
				if ($this->service->enableExApp($exApp)) {
					$output->writeln(sprintf('ExApp %s successfully enabled.', $appId));
				} else {
					$output->writeln(sprintf('Failed to enable ExApp %s.', $appId));
					// Fallback unregistering ExApp
					$this->service->unregisterExApp($exApp->getAppid());
					return 1;
				}
			}

			$output->writeln(sprintf('ExApp %s successfully registered.', $appId));
			return 0;
		}

		$output->writeln(sprintf('Failed to register ExApp %s.', $appId));
		return 1;
	}

	private function registerExAppScopes($output, ExApp $exApp, array $requestedExAppScopeGroups, string $scopeType): void {
		$registeredScopeGroups = [];
		foreach ($this->exAppApiScopeService->mapScopeNamesToNumbers($requestedExAppScopeGroups) as $scopeGroup) {
			if ($this->exAppScopesService->setExAppScopeGroup($exApp, $scopeGroup)) {
				$registeredScopeGroups[] = $scopeGroup;
			} else {
				$output->writeln(sprintf('Failed to set %s ExApp scope group: %s', $scopeType, $scopeGroup));
			}
		}
		if (count($registeredScopeGroups) > 0) {
			$output->writeln(sprintf('ExApp %s %s scope groups successfully set: %s', $exApp->getAppid(), $scopeType, implode(', ',
				$this->exAppApiScopeService->mapScopeGroupsToNames($registeredScopeGroups))));
		}
	}
}
