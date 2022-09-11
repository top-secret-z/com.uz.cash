<?php
namespace cash\page;
use cash\data\cash\claim\user\ViewableUserCashClaimList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user claims manage page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserClaimListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableUserCashClaimList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'userClaimList';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'userClaimID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['userClaimID', 'status', 'amount', 'currency', 'username', 'time', 'origSubject'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * filter
	 */
	public $availableCurrencies = [];
	public $currency = '';
	public $availableStati = [];
	public $availableTransfers = [];
	public $status = -1;
	public $isTransfer = -1;
	public $subject = '';
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['currency'])) $this->currency = $_REQUEST['currency'];
		$this->status = -1;
		if (isset($_REQUEST['status']) && $_REQUEST['status'] >= 0) $this->status = $_REQUEST['status'];
		$this->isTransfer = -1;
		if (isset($_REQUEST['isTransfer']) && $_REQUEST['isTransfer'] >= 0) $this->isTransfer = $_REQUEST['isTransfer'];
		if (!empty($_REQUEST['subject'])) $this->subject = StringUtil::trim($_REQUEST['subject']);
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// get data
		$this->availableCurrencies = $this->objectList->getAvailableCurrencies();
		$this->availableStati = $this->objectList->getAvailableStati();
		
		$this->availableTransfers[1] = WCF::getLanguage()->get('cash.claim.user.transfer');
		$this->availableTransfers[0] = WCF::getLanguage()->get('cash.claim.user.transfer.not');
		
		// filter
		if (!empty($this->currency)) {
			$this->objectList->getConditionBuilder()->add('cash_claim_user.currency LIKE ?', [$this->currency]);
		}
		if ($this->status >= 0) {
			$this->objectList->getConditionBuilder()->add('cash_claim_user.status = ?', [$this->status]);
		}
		if ($this->isTransfer >= 0) {
			$this->objectList->getConditionBuilder()->add('cash_claim_user.isTransfer = ?', [$this->isTransfer]);
		}
		if (!empty($this->subject)) {
			$this->objectList->getConditionBuilder()->add('(cash_claim_user.subject LIKE ? OR cash_claim_user.claimID IN (SELECT claimID FROM cash'.WCF_N.'_cash_claim WHERE subject LIKE ?))', ['%'.$this->subject.'%', '%'.$this->subject.'%']);
		}
		if (!empty($this->username)) {
			$user = User::getUserByUsername($this->username);
			if ($user->userID) {
				$this->objectList->getConditionBuilder()->add('cash_claim_user.userID = ?', [$user->userID]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'availableCurrencies' => $this->availableCurrencies,
				'availableStati' => $this->availableStati,
				'availableTransfers' => $this->availableTransfers,
				'currency' => $this->currency,
				'status' => $this->status,
				'isTransfer' => $this->isTransfer,
				'subject' => $this->subject,
				'username' => $this->username
		]);
	}
}
