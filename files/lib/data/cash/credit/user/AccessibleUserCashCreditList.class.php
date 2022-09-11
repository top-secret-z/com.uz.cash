<?php
namespace cash\data\cash\credit\user;
use wcf\system\WCF;

/**
 * Represents a list of user credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class AccessibleUserCashCreditList extends UserCashCreditList {
	/**
	 * Creates a new list object.
	 */
	public function __construct() {
		parent::__construct();
		
		// subject
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "cash_credit.subject AS origSubject";
		$this->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_credit cash_credit ON (cash_credit.creditID = cash_credit_user.creditID)";
		
		$this->getConditionBuilder()->add('cash_credit_user.userID = ?', [WCF::getUser()->userID]);
		$this->getConditionBuilder()->add('cash_credit_user.status > ?', [0]);
	}
}
