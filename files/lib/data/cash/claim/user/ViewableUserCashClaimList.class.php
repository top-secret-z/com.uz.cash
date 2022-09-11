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
class ViewableUserCashClaimList extends UserCashClaimList {
	/**
	 * @inheritDoc
	 */
//	public $className = UserCashClaim::class;
	
	/**
	 * Creates a new list object.
	 */
	public function __construct() {
		parent::__construct();
		
		// username
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_table.username";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = cash_claim_user.userID)";
		
		// subject
		$this->sqlSelects .= ", cash_claim.subject AS origSubject";
		$this->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_claim cash_claim ON (cash_claim.claimID = cash_claim_user.claimID)";
	}
	
	/**
	 * Returns a list of available currencies.
	 */
	public function getAvailableCurrencies() {
		$currencies = [];
		$sql = "SELECT	DISTINCT currency
				FROM	cash".WCF_N."_cash_claim_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['currency']) {
				$currencies[$row['currency']] = $row['currency'];
			}
		}
		ksort($currencies);
		
		return $currencies;
	}
	
	/**
	 * Returns a list of available stati.
	 */
	public function getAvailableStati() {
		$stati = [];
		$sql = "SELECT	DISTINCT status
				FROM	cash".WCF_N."_cash_claim_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['status'] !== null) {
				switch ($row['status']) {
					case 0:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.claim.user.status.pending');
						break;
					case 1:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.claim.user.status.open');
						break;
					case 2:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.claim.user.status.paid');
						break;
				}
			}
		}
		ksort($stati);
		
		return $stati;
	}
}
