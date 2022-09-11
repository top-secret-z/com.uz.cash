<?php
namespace cash\data\cash\claim\user;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashClaimEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserCashClaim::class;
}
