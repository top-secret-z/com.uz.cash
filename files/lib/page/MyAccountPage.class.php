<?php
namespace cash\page;
use cash\data\cash\AccessibleCashList;
use cash\data\cash\claim\user\UserCashClaim;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use cash\system\cache\builder\BalanceCacheBuilder;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the user's overview page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class MyAccountPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage', 'user.cash.isPayer', 'user.cash.canSeeStatements'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleCashList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'myAccount';
	
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
	public $validSortFields = ['cashID', 'time', 'amount', 'currency', 'type', 'comment'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CASH_ITEMS_PER_PAGE;
	
	/**
	 * user's balance
	 */
	public $userBalance = [];
	
	/**
	 * balance and open claims
	 */
	public $balance = [];
	public $claims = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty(WCF::getUser()->cashBalance)) {
			$this->userBalance = unserialize(WCF::getUser()->cashBalance);
		}
		
		$this->balance = BalanceCacheBuilder::getInstance()->getData();
		$this->claims = OpenClaimsCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'userBalance' => $this->userBalance,
				'balance' => $this->balance,
				'claims' => $this->claims,
				'hasClaims' => UserCashClaim::getOpenClaims(),
		]);
	}
}
	