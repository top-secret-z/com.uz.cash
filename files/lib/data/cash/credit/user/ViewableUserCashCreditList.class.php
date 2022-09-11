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
class ViewableUserCashCreditList extends UserCashCreditList {
	/**
	 * @inheritDoc
	 */
//	public $className = UserCashCredit::class;
	
	/**
	 * Creates a new list object.
	 */
	public function __construct() {
		parent::__construct();
		
		// username
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_table.username";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = cash_credit_user.userID)";
		
		// subject
		$this->sqlSelects .= ", cash_credit.subject AS origSubject";
		$this->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_credit cash_credit ON (cash_credit.creditID = cash_credit_user.creditID)";
	}
	
	/**
	 * Returns a list of available currencies.
	 */
	public function getAvailableCurrencies() {
		$currencies = [];
		$sql = "SELECT	DISTINCT currency
				FROM	cash".WCF_N."_cash_credit_user";
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
				FROM	cash".WCF_N."_cash_credit_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['status'] !== null) {
				switch ($row['status']) {
					case 0:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.credit.user.status.pending');
						break;
					case 1:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.credit.user.status.open');
						break;
					case 2:
						$stati[$row['status']] = WCF::getLanguage()->get('cash.credit.user.status.paid');
						break;
				}
			}
		}
		ksort($stati);
		
		return $stati;
	}
}
