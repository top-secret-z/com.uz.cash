<?php
namespace cash\system\worker;
use cash\data\cash\CashList;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\worker\AbstractRebuildDataWorker;
use wcf\system\WCF;

/**
 * Worker implementation for updating cash.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = UserList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'user_table.userID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset cash and storage
			BalanceCacheBuilder::getInstance()->reset();
			OpenClaimsCacheBuilder::getInstance()->reset();
			UserStorageHandler::getInstance()->resetAll('cashOpenClaims');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		foreach ($this->objectList as $user) {
			$balance = [];
			$openClaims = 0;
			
			// get cash data
			$list = new CashList();
			$list->getConditionBuilder()->add('userID = ?', [$user->userID]);
			$list->sqlOrderBy = 'cashID ASC';
			$list->readObjects();
			$cashes = $list->getObjects();
			if (!count($cashes)) {
				// update user and continue
				$action = new UserAction([$user], 'update', [
						'data' => [
								'cashBalance' => serialize($balance)
						]
				]);
				$action->executeAction();
				continue;
			}
			
			// balance
			foreach($cashes as $cash) {
				if ($cash->type == 'claimBalanced') continue;
				if ($cash->type == 'creditChanged') continue;
				if ($cash->type == 'posting') continue;
				
				if (substr($cash->type, 0, 2) === 'cl') {
					if (isset($balance[$cash->currency])) {
						$balance[$cash->currency] += $cash->amount;
					}
					else {
						$balance[$cash->currency] = $cash->amount;
					}
				}
				else {
					if (isset($balance[$cash->currency])) {
						$balance[$cash->currency] -= $cash->amount;
					}
					else {
						$balance[$cash->currency] = -1 * $cash->amount;
					}
				}
			}
			
			// open claims
			$sql = "SELECT	COUNT(*)
					FROM	cash".WCF_N."_cash_claim_user
					WHERE	userID = ? AND status = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$user->userID, 1]);
			$openClaims = $statement->fetchColumn();
			
			// update user
			$action = new UserAction([$user], 'update', [
					'data' => [
							'cashBalance' => serialize($balance)
					]
			]);
			$action->executeAction();
			if ($openClaims) {
				UserStorageHandler::getInstance()->update($user->userID, 'cashOpenClaims', $openClaims);
			}
		}
	}
}
