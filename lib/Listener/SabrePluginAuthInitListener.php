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

namespace OCA\AppEcosystemV2\Listener;

use OCA\AppEcosystemV2\AEAuthBackend;

use OCA\DAV\Events\SabrePluginAuthInitEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-extends IEventListener<SabrePluginAuthInitListener>
 */
class SabrePluginAuthInitListener implements IEventListener {
	private AEAuthBackend $aeAuth;

	public function __construct(AEAuthBackend $aeAuth) {
		$this->aeAuth = $aeAuth;
	}

	public function handle(Event $event): void {
		if (!$event instanceof SabrePluginAuthInitEvent) {
			return;
		}

		$server = $event->getServer();
		$authPlugin = $server->getPlugin('auth');
		if ($authPlugin instanceof \Sabre\DAV\Auth\Plugin) {
			$authPlugin->addBackend($this->aeAuth);
		}
	}
}
