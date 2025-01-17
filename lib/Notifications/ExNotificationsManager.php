<?php

declare(strict_types=1);

namespace OCA\AppAPI\Notifications;

use OCP\IGroupManager;
use OCP\Notification\IManager;
use OCP\Notification\INotification;

class ExNotificationsManager {
	private IManager $notificationManager;
	private IGroupManager $groupManager;

	public function __construct(IManager $manager, IGroupManager $groupManager) {
		$this->notificationManager = $manager;
		$this->groupManager = $groupManager;
	}

	/**
	 * Create a notification for ExApp and notify the user
	 *
	 * @param string $appId
	 * @param string|null $userId
	 * @param array $params
	 *
	 * @return INotification
	 */
	public function sendNotification(string $appId, ?string $userId = null, array $params = []): INotification {
		$notification = $this->notificationManager->createNotification();
		$notification
			->setApp($appId)
			->setUser($userId)
			->setDateTime(new \DateTime())
			->setObject($params['object'], $params['object_id'])
			->setSubject($params['subject_type'], $params['subject_params']);
		$this->notificationManager->notify($notification);
		return $notification;
	}

	public function sendAdminsNotification(string $appId, array $params = []): array {
		$admins = $this->groupManager->get("admin")->getUsers();
		$notifications = [];
		foreach ($admins as $adminUser) {
			$notification = $this->notificationManager->createNotification();
			$notification
				->setApp($appId)
				->setUser($adminUser->getUID())
				->setDateTime(new \DateTime())
				->setObject($params['object'], $params['object_id'])
				->setSubject($params['subject_type'], $params['subject_params']);
			$this->notificationManager->notify($notification);
			$notifications[] = $notification;
		}
		return $notifications;
	}
}
