<?php
namespace cash\data\cash\credit\user;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashCreditList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserCashCredit::class;
}
