<?php
namespace cash\page;
use cash\data\cash\CashList;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use cash\system\cache\builder\BalanceCacheBuilder;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the overview page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class OverviewPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage', 'user.cash.canSeeStatements'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = CashList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'overview';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'cashID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['cashID', 'time', 'amount', 'currency', 'type', 'username', 'comment'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * filter
	 */
	public $availableCurrencies = [];
	public $availableTypes = [];
	public $currency = '';
	public $comment = '';
	public $type = '';
	public $username = '';
	
	/**
	 * balance and open claims
	 */
	public $balance = [];
	public $claims = [];
	
	/**
	 * date (yyyy-mm-dd)
	 */
	public $startDate = '';
	public $endDate = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['currency'])) $this->currency = StringUtil::trim($_REQUEST['currency']);
		if (!empty($_REQUEST['comment'])) $this->comment = StringUtil::trim($_REQUEST['comment']);
		if (!empty($_REQUEST['type'])) $this->type = StringUtil::trim($_REQUEST['type']);
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		
		if (!empty($_REQUEST['startDate'])) $this->startDate = $_REQUEST['startDate'];
		if (!empty($_REQUEST['endDate'])) $this->endDate = $_REQUEST['endDate'];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// get data
		$this->availableCurrencies = $this->objectList->getAvailableCurrencies();
		$this->availableTypes = $this->objectList->getAvailableTypes();
		$this->balance = BalanceCacheBuilder::getInstance()->getData();
		$this->claims = OpenClaimsCacheBuilder::getInstance()->getData();
		
		// filter
		if (!empty($this->currency)) {
			$this->objectList->getConditionBuilder()->add('currency LIKE ?', [$this->currency]);
		}
		if (!empty($this->comment)) {
			$this->objectList->getConditionBuilder()->add('comment LIKE ?', ['%' . $this->comment . '%']);
		}
		if (!empty($this->type)) {
			$this->objectList->getConditionBuilder()->add('type LIKE ?', [$this->type]);
		}
		if (!empty($this->username)) {
			$user = User::getUserByUsername($this->username);
			if ($user->userID) {
				$this->objectList->getConditionBuilder()->add('userID = ?', [$user->userID]);
			}
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
		
		WCF::getTPL()->assign([
				'availableCurrencies' => $this->availableCurrencies,
				'availableTypes' => $this->availableTypes,
				'balance' => $this->balance,
				'claims' => $this->claims,
				'currency' => $this->currency,
				'comment' => $this->comment,
				'type' => $this->type,
				'username' => $this->username,
				'endDate' => $this->endDate,
				'startDate' => $this->startDate
		]);
	}
}
