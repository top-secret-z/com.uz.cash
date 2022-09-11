<?php
namespace cash\data\cash\posting;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cash postings.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashPostingEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CashPosting::class;
}
