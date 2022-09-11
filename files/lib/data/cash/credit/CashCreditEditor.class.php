<?php
namespace cash\data\cash\credit;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cash credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCreditEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CashCredit::class;
}
