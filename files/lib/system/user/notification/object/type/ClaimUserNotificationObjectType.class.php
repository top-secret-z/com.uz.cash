<?php
namespace cash\system\user\notification\object\type;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\CashClaimList;
use cash\system\user\notification\object\ClaimUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * User notification object type implementation for claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class ClaimUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = ClaimUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = CashClaim::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CashClaimList::class;
}
