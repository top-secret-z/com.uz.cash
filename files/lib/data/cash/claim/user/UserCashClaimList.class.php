<?php
namespace cash\data\cash\claim\user;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashClaimList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserCashClaim::class;
}
