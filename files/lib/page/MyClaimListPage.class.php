<?php
namespace cash\page;
use cash\data\cash\claim\user\AccessibleUserCashClaimList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the user claims page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class MyClaimListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage', 'user.cash.isPayer'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleUserCashClaimList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'myClaimList';
	
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
	public $validSortFields = ['userClaimID', 'status', 'amount', 'currency', 'time', 'origSubject'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * user's balance
	 */
	public $userBalance = [];
	
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
		
		if (!empty(WCF::getUser()->cashBalance)) {
			$this->userBalance = unserialize(WCF::getUser()->cashBalance);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// leave ufn
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'currency' => $this->currency,
				'status' => $this->status,
				'subject' => $this->subject,
				'userBalance' => $this->userBalance
		]);
	}
}