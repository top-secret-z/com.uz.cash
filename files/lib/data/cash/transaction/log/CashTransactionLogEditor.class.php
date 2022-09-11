<?php
namespace cash\data\cash\transaction\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cash transaction log entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashTransactionLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CashTransactionLog::class;
}
