<?php
namespace cash\acp\page;
use cash\data\cash\transaction\log\CashTransactionLogList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of cash transactions .
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashTransactionLogListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'cash.acp.menu.link.cash.transaction.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'logTime';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['logID', 'userID', 'userClaimID', 'paymentMethodObjectTypeID', 'logTime', 'transactionID', 'logMessage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = CashTransactionLogList::class;
	
	/**
	 * transaction id
	 */
	public $transactionID = '';
	
	/**
	 * username
	 */
	public $username = '';
	
	/**
	 * user claim id
	 */
	public $userClaimID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['transactionID'])) $this->transactionID = StringUtil::trim($_REQUEST['transactionID']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['userClaimID'])) $this->userClaimID = intval($_REQUEST['userClaimID']);
	}
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->transactionID) {
			$this->objectList->getConditionBuilder()->add('cash_transaction_log.transactionID LIKE ?', ['%' . $this->transactionID . '%']);
		}
		if ($this->username) {
			$this->objectList->getConditionBuilder()->add('cash_transaction_log.userID IN (SELECT userID FROM wcf'.WCF_N.'_user WHERE username LIKE ?)', ['%' . $this->username . '%']);
		}
		if ($this->userClaimID) {
			$this->objectList->getConditionBuilder()->add('cash_transaction_log.userClaimID = ?', [$this->userClaimID]);
		}
		
		$this->objectList->sqlSelects = 'user_table.username, cash_claim_user.subject, cash_claim.subject AS origSubject';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = cash_transaction_log.userID)";
		$this->objectList->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_claim_user cash_claim_user ON (cash_claim_user.userClaimID = cash_transaction_log.userClaimID)";
		$this->objectList->sqlJoins .= " LEFT JOIN cash".WCF_N."_cash_claim cash_claim ON (cash_claim.claimID = cash_claim_user.claimID)";
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'transactionID' => $this->transactionID,
				'username' => $this->username,
				'userClaimID' => $this->userClaimID
		]);
	}
}
