<?php

declare(strict_types=1);

namespace OCA\AppAPI\Command\ExApp;

use OCA\AppAPI\Service\AppAPIService;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Enable extends Command {
	private AppAPIService $service;

	public function __construct(AppAPIService $service) {
		parent::__construct();

		$this->service = $service;
	}

	protected function configure() {
		$this->setName('app_api:app:enable');
		$this->setDescription('Enable registered external app');

		$this->addArgument('appid', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$appId = $input->getArgument('appid');
		$exApp = $this->service->getExApp($appId);

		if ($exApp === null) {
			$output->writeln(sprintf('ExApp %s not found. Failed to enable.', $appId));
			return 1;
		}
		if ($exApp->getEnabled()) {
			$output->writeln(sprintf('ExApp %s already enabled.', $appId));
			return 0;
		}

		if ($this->service->enableExApp($exApp)) {
			$output->writeln(sprintf('ExApp %s successfully enabled.', $appId));
			return 0;
		}

		// TODO: Add scopes check (from info.xml) and approval if needed

		$output->writeln(sprintf('Failed to enable ExApp %s.', $appId));
		return 1;
	}
}
