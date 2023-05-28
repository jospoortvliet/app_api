<?php

declare(strict_types=1);

/**
 *
 * Nextcloud - App Ecosystem V2
 *
 * @copyright Copyright (c) 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AppEcosystemV2\Service;

use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IConfig;

use OCA\AppEcosystemV2\Db\ExAppConfigMapper;
use OCP\Cache\CappedMemoryCache;

class ExAppPreferenceService {
	/** @var IConfig */
	private $config;

	/** @var CappedMemoryCache */
	private $cache;

	/** @var IClient */
	private $client;

	/** @var ExAppConfigMapper */
	private $mapper;

	public function __construct(
		IConfig $config,
		CappedMemoryCache $cache,
		IClientService $clientService,
		ExAppConfigMapper $mapper
	) {
		$this->config = $config;
		$this->cache = $cache;
		$this->client = $clientService->newClient();
		$this->mapper = $mapper;
	}

	public function getAppConfigValue() {
	}

	public function setAppConfigValue() {
	}

	public function deleteAppConfigValue() {
	}

	public function deleteAppConfigValues() {
	}

	public function getAppConfigKeys() {
	}
}