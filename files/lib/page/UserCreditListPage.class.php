<?php
namespace cash\page;
use cash\data\cash\credit\user\ViewableUserCashCreditList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user credits manage page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCreditListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableUserCashCreditList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'userCreditList';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'userCreditID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['userCreditID', 'amount', 'currency', 'username', 'time', 'origSubject'];
	
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
	public $status = -1;
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
		
		// filter
		if (!empty($this->currency)) {
			$this->objectList->getConditionBuilder()->add('cash_credit_user.currency LIKE ?', [$this->currency]);
		}
		if ($this->status >= 0) {
			$this->objectList->getConditionBuilder()->add('cash_credit_user.status = ?', [$this->status]);
		}
		if (!empty($this->subject)) {
			$this->objectList->getConditionBuilder()->add('(cash_credit_user.subject LIKE ? OR cash_credit_user.creditID IN (SELECT creditID FROM cash'.WCF_N.'_cash_credit WHERE subject LIKE ?))', ['%'.$this->subject.'%', '%'.$this->subject.'%']);
		}
		if (!empty($this->username)) {
			$user = User::getUserByUsername($this->username);
			if ($user->userID) {
				$this->objectList->getConditionBuilder()->add('cash_credit_user.userID = ?', [$user->userID]);
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
				'currency' => $this->currency,
				'status' => $this->status,
				'subject' => $this->subject,
				'username' => $this->username
		]);
	}
}
