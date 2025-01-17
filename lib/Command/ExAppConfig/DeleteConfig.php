<?php

declare(strict_types=1);

namespace OCA\AppAPI\Command\ExAppConfig;

use OCA\AppAPI\Service\AppAPIService;
use OCA\AppAPI\Service\ExAppConfigService;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteConfig extends Command {
	private AppAPIService $service;
	private ExAppConfigService $exAppConfigService;

	public function __construct(AppAPIService $service, ExAppConfigService $exAppConfigService) {
		parent::__construct();

		$this->service = $service;
		$this->exAppConfigService = $exAppConfigService;
	}

	protected function configure() {
		$this->setName('app_api:app:config:delete');
		$this->setDescription('Delete ExApp configs');

		$this->addArgument('appid', InputArgument::REQUIRED);
		$this->addArgument('configkey', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$appId = $input->getArgument('appid');
		$exApp = $this->service->getExApp($appId);
		if ($exApp === null) {
			$output->writeln(sprintf('ExApp %s not found.', $appId));
			return 1;
		}
		if ($exApp->getEnabled()) {
			$configKey = $input->getArgument('configkey');
			$exAppConfig = $this->exAppConfigService->getAppConfig($appId, $configKey);
			if ($exAppConfig === null) {
				$output->writeln(sprintf('ExApp %s config %s not found.', $appId, $configKey));
				return 1;
			}
			if ($this->exAppConfigService->deleteAppConfig($exAppConfig) !== 1) {
				$output->writeln(sprintf('Failed to delete ExApp %s config %s.', $appId, $configKey));
				return 1;
			}
			$output->writeln(sprintf('ExApp %s config %s deleted.', $appId, $configKey));
			return 0;
		}
		return 1;
	}
}
