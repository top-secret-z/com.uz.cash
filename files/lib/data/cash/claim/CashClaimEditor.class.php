<?php
namespace cash\data\cash\claim;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cash claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashClaimEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CashClaim::class;
}
