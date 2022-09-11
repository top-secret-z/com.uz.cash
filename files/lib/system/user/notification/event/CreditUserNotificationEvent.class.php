<?php
namespace cash\system\user\notification\event;
use wcf\data\user\User;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\request\LinkHandler;

/**
 * Notification event for credit.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = false;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('cash.credit.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('cash.credit.notification.message', [
				'author' => $this->author,
				'credit' => $this->userNotificationObject,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'com.uz.cash.credit/'.$this->getUserNotificationObject()->creditID,
				'template' => 'email_notification_credit',
				'application' => 'cash'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Credit', [
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
