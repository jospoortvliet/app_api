<?php

declare(strict_types=1);

namespace OCA\AppAPI\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Class ExApp
 *
 * @package OCA\AppAPI\Db
 *
 * @method string getAppid()
 * @method string getVersion()
 * @method string getName()
 * @method string getDaemonConfigName()
 * @method string getProtocol()
 * @method string getHost()
 * @method int getPort()
 * @method string getSecret()
 * @method string getStatus()
 * @method int getEnabled()
 * @method int getCreatedTime()
 * @method int getLastCheckTime()
 * @method void setAppid(string $appid)
 * @method void setVersion(string $version)
 * @method void setName(string $name)
 * @method void setDaemonConfigName(string $name)
 * @method void setProtocol(string $protocol)
 * @method void setHost(string $host)
 * @method void setPort(int $port)
 * @method void setSecret(string $secret)
 * @method void setStatus(string $status)
 * @method void setEnabled(int $enabled)
 * @method void setCreatedTime(int $createdTime)
 * @method void setLastCheckTime(int $lastCheckTime)
 */
class ExApp extends Entity implements JsonSerializable {
	protected $appid;
	protected $version;
	protected $name;
	protected $daemonConfigName;
	protected $protocol;
	protected $host;
	protected $port;
	protected $secret;
	protected $status;
	protected $enabled;
	protected $createdTime;
	protected $lastCheckTime;

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		$this->addType('appid', 'string');
		$this->addType('version', 'string');
		$this->addType('name', 'string');
		$this->addType('daemonConfigName', 'string');
		$this->addType('protocol', 'string');
		$this->addType('host', 'string');
		$this->addType('port', 'int');
		$this->addType('secret', 'string');
		$this->addType('status', 'string');
		$this->addType('enabled', 'int');
		$this->addType('createdTime', 'int');
		$this->addType('lastCheckTime', 'int');

		if (isset($params['id'])) {
			$this->setId($params['id']);
		}
		if (isset($params['appid'])) {
			$this->setAppid($params['appid']);
		}
		if (isset($params['version'])) {
			$this->setVersion($params['version']);
		}
		if (isset($params['name'])) {
			$this->setName($params['name']);
		}
		if (isset($params['daemon_config_name'])) {
			$this->setDaemonConfigName($params['daemon_config_name']);
		}
		if (isset($params['protocol'])) {
			$this->setProtocol($params['protocol']);
		}
		if (isset($params['host'])) {
			$this->setHost($params['host']);
		}
		if (isset($params['port'])) {
			$this->setPort($params['port']);
		}
		if (isset($params['secret'])) {
			$this->setSecret($params['secret']);
		}
		if (isset($params['status'])) {
			$this->setStatus($params['status']);
		}
		if (isset($params['enabled'])) {
			$this->setEnabled($params['enabled']);
		}
		if (isset($params['created_time'])) {
			$this->setCreatedTime($params['created_time']);
		}
		if (isset($params['last_check_time'])) {
			$this->setLastCheckTime($params['last_check_time']);
		}
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'appid' => $this->getAppid(),
			'version' => $this->getVersion(),
			'name' => $this->getName(),
			'daemon_config_name' => $this->getDaemonConfigName(),
			'protocol' => $this->getProtocol(),
			'host' => $this->getHost(),
			'port' => $this->getPort(),
			'secret' => $this->getSecret(),
			'status' => $this->getStatus(),
			'enabled' => $this->getEnabled(),
			'created_time' => $this->getCreatedTime(),
			'last_check_time' => $this->getLastCheckTime(),
		];
	}
}
