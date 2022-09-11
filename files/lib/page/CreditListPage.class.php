<?php
namespace cash\page;
use cash\data\cash\credit\CashCreditList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the credits manage page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'CreditList';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = CashCreditList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'creditList';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'creditID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['creditID', 'time', 'categoryID', 'amount', 'currency', 'frequency', 'subject'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * filter
	 */
	public $availableCategories = [];
	public $availableCurrencies = [];
	public $categoryID = 0;
	public $currency = '';
	public $availableFrequencies = [];
	public $frequency = '';
	public $subject = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		if (!empty($_REQUEST['currency'])) $this->currency = $_REQUEST['currency'];
		if (!empty($_REQUEST['frequency'])) $this->frequency = $_REQUEST['frequency'];
		if (!empty($_REQUEST['subject'])) $this->subject = StringUtil::trim($_REQUEST['subject']);
		
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// get data
		$this->availableCategories = $this->objectList->getAvailableCategories();
		$this->availableCurrencies = $this->objectList->getAvailableCurrencies();
		$this->availableFrequencies = $this->objectList->getAvailableFrequencies();
		
		// filter
		if (!empty($this->categoryID)) {
			$this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
		}
		if (!empty($this->currency)) {
			$this->objectList->getConditionBuilder()->add('currency LIKE ?', [$this->currency]);
		}
		if (!empty($this->frequency)) {
			$this->objectList->getConditionBuilder()->add('frequency LIKE ?', [$this->frequency]);
		}
		if (!empty($this->subject)) {
			$this->objectList->getConditionBuilder()->add('subject LIKE ?', ['%' . $this->subject . '%']);

		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'availableCategories' => $this->availableCategories,
				'availableCurrencies' => $this->availableCurrencies,
				'availableFrequencies' => $this->availableFrequencies,
				'categoryID' => $this->categoryID,
				'currency' => $this->currency,
				'frequency' => $this->frequency,
				'subject' => $this->subject
		]);
	}
}
