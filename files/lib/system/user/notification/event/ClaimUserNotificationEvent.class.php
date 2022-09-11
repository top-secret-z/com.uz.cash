<?php
namespace cash\system\user\notification\event;
use wcf\data\user\User;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\request\LinkHandler;

/**
 * Notification event for claim.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class ClaimUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = false;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('cash.claim.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('cash.claim.notification.message', [
				'author' => $this->author,
				'claim' => $this->userNotificationObject,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'com.uz.cash.claim/'.$this->getUserNotificationObject()->claimID,
				'template' => 'email_notification_claim',
				'application' => 'cash'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Claim', [
				'application' => 'cash',
				'object' => $this->getUserNotificationObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return $this->getUserNotificationObject()->canRead();
	}
}
