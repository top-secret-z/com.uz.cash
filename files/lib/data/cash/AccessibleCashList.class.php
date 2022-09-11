<?php
namespace cash\data\cash;
use wcf\system\WCF;

/**
 * Represents a list of accessible cash entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class AccessibleCashList extends CashList {
	/**
	 * Creates a new list object.
	 */
	public function __construct() {
		parent::__construct();
		
		// exclude other users
		$this->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
		
		// exclude any management entries
		$this->getConditionBuilder()->add('type <> ?', ['posting']);
		$this->getConditionBuilder()->add('type <> ?', ['creditChanged']);
	}
}
