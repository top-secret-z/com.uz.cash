<?php
namespace cash\data\cash\claim\user;
use wcf\system\WCF;

/**
 * Represents a list of user claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class AccessibleUserCashClaimList extends UserCashClaimList {
	/**
	 * Creates a new list object.
	 */
	public function __construct() {
		parent::__construct();
		
		// subject
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "cash_claim.subject AS origSubject";
		$this->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_claim cash_claim ON (cash_claim.claimID = cash_claim_user.claimID)";
		
		$this->getConditionBuilder()->add('cash_claim_user.userID = ?', [WCF::getUser()->userID]);
		$this->getConditionBuilder()->add('cash_claim_user.status > ?', [0]);
	}
}
