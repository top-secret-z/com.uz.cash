<?php
namespace cash\page;
use cash\data\cash\posting\CashPostingList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the postings manage page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class PostingListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = CashPostingList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'postingList';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'postingID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['postingID', 'time', 'categoryID', 'amount', 'currency', 'subject', 'type'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * filter
	 */
	public $availableCategories = [];
	public $availableCurrencies = [];
	public $availableTypes = [];
	public $categoryID = 0;
	public $currency = '';
	public $subject = '';
	public $type = '';
	
	/**
	 * date (yyyy-mm-dd)
	 */
	public $startDate = '';
	public $endDate = '';
	
	/**
	 * balance
	 */
	public $balance = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		if (!empty($_REQUEST['currency'])) $this->currency = $_REQUEST['currency'];
		if (!empty($_REQUEST['subject'])) $this->subject = StringUtil::trim($_REQUEST['subject']);
		if (!empty($_REQUEST['type'])) $this->type = $_REQUEST['type'];
		
		if (!empty($_REQUEST['startDate'])) $this->startDate = $_REQUEST['startDate'];
		if (!empty($_REQUEST['endDate'])) $this->endDate = $_REQUEST['endDate'];
		
		// read total balance, filtered
		$postingList = new CashPostingList();
		if (!empty($this->categoryID)) $postingList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
		if (!empty($this->currency)) $postingList->getConditionBuilder()->add('currency LIKE ?', [$this->currency]);
		if (!empty($this->subject)) $postingList->getConditionBuilder()->add('subject LIKE ?', ['%' . $this->subject . '%']);
		if (!empty($this->type)) $postingList->getConditionBuilder()->add('type LIKE ?', [$this->type]);
		if (!empty($this->startDate)) {
			$timestamp = strtotime($this->startDate) - 1;
			$postingList->getConditionBuilder()->add('time > ?', [$timestamp]);
		}
		if (!empty($this->endDate)) {
			$timestamp = strtotime($this->endDate) + 86399;
			$postingList->getConditionBuilder()->add('time < ?', [$timestamp]);
		}
		$postingList->readObjects();
		$postings = $postingList->getObjects();
		
		// calculate balance
		$this->balance = [];
		if (count($postings)) {
			foreach ($postings as $posting) {
				$amount = $posting->amount;
				if ($posting->type == 'expense') $amount = -1 * $amount;
				
				if (isset($this->balance[$posting->currency])) $this->balance[$posting->currency] += $amount;
				else $this->balance[$posting->currency] = $amount;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// get data
		$this->availableCategories = $this->objectList->getAvailableCategories();
		$this->availableCurrencies = $this->objectList->getAvailableCurrencies();
		$this->availableTypes = $this->objectList->getAvailableTypes();
		
		// filter
		if (!empty($this->categoryID)) {
			$this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
		}
		if (!empty($this->currency)) {
			$this->objectList->getConditionBuilder()->add('currency LIKE ?', [$this->currency]);
		}
		if (!empty($this->subject)) {
			$this->objectList->getConditionBuilder()->add('subject LIKE ?', ['%' . $this->subject . '%']);
		}
		if (!empty($this->type)) {
			$this->objectList->getConditionBuilder()->add('type LIKE ?', [$this->type]);
		}
		
		if (!empty($this->startDate)) {
			$timestamp = strtotime($this->startDate) - 1;
			$this->objectList->getConditionBuilder()->add('time > ?', [$timestamp]);
		}
		if (!empty($this->endDate)) {
			$timestamp = strtotime($this->endDate) + 86399;
			$this->objectList->getConditionBuilder()->add('time < ?', [$timestamp]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// format balance
		$balance = '';
		$temp = [];
		if (count($this->balance)) {
			foreach ($this->balance as $currency => $amount) {
				$amount = number_format(round($amount, 2), 2, WCF::getLanguage()->get('wcf.global.decimalPoint'), WCF::getLanguage()->get('wcf.global.thousandsSeparator'));
				$temp[] = $amount . ' ' . $currency;
			}
			$balance = implode(' | ', $temp);
		}
		
		WCF::getTPL()->assign([
				'availableCategories' => $this->availableCategories,
				'availableCurrencies' => $this->availableCurrencies,
				'availableTypes' => $this->availableTypes,
				'categoryID' => $this->categoryID,
				'currency' => $this->currency,
				'subject' => $this->subject,
				'type' => $this->type,
				'endDate' => $this->endDate,
				'startDate' => $this->startDate,
				'balance' => $balance
		]);
	}
}
