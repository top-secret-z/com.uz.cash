<?php
namespace cash\data\cash\credit\user;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashCreditEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserCashCredit::class;
}
