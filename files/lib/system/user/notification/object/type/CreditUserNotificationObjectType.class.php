<?php
namespace cash\system\user\notification\object\type;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\CashCreditList;
use cash\system\user\notification\object\CreditUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * User notification object type implementation for credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CreditUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = CashCredit::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CashCreditList::class;
}
