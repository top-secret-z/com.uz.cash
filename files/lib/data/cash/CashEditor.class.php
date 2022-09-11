<?php
namespace cash\data\cash;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cash entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Cash::class;
}
