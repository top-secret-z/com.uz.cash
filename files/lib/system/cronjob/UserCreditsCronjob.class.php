<?php
namespace cash\system\cronjob;
use cash\data\cash\credit\CashCreditEditor;
use cash\data\cash\credit\CashCreditList;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Creates user credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCreditsCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// only if on
		if (!MODULE_CASH) return;
		
		// get active credits due to be sent
		$creditList = new CashCreditList();
		$creditList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$creditList->getConditionBuilder()->add('nextExecution > ?', [0]);
		$creditList->getConditionBuilder()->add('nextExecution < ?', [TIME_NOW]);
		$creditList->getConditionBuilder()->add('executionCount < executions');
		$creditList->sqlLimit = 1;
		$creditList->readObjects();
		$credits = $creditList->getObjects();
		if (!count($credits)) return;
		
		$credit = reset($credits);
		
		// set credit execution
		$executionCount = $this->setExecution($credit);
		
		// get users
		$userList = new UserList();
		// usernames
		$userIDs = unserialize($credit->users);
		if (count($userIDs)) {
			$userList->getConditionBuilder()->add('user_table.userID IN (?)', [$userIDs]);
		}
		// conditions
		$conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.credit.user', $credit->creditID);
		foreach ($conditions as $condition) {
			$condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
		}
		
		$userList->readObjects();
		$users = $userList->getObjects();
		if (!count($users)) return;
		
		// set user credits
		$time = TIME_NOW;
		$sql = "INSERT INTO	cash".WCF_N."_cash_credit_user
					(creditID, time, userID, executionCount, amount, currency)
				VALUES		(?, ?, ?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE time = VALUES(time)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($users as $user) {
			$profile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
			if (!$profile->getPermission('user.cash.isPayer')) continue;
			
			$statement->execute([$credit->creditID, $time, $user->userID, $executionCount, $credit->amount, $credit->currency]);
		}
		WCF::getDB()->commitTransaction();
 	}
	
	/**
	 * Sets execution data
	 */
	protected function setExecution($credit) {
		$creditEditor = new CashCreditEditor($credit);
		$executionCount = $credit->executionCount + 1;
		
		if ($executionCount == $credit->executions) {
			$creditEditor->update([
					'executionCount' => $executionCount,
					'nextExecution' => 0
			]);
			
			return $executionCount;
		}
		
		// repetitions
		switch($credit->frequency) {
			case 'week':
				$nextExecution = $credit->nextExecution + 7 * 86400;
				break;
			case 'twoweek':
				$nextExecution = $credit->nextExecution + 14 * 86400;
				break;
			case 'month':
				$nextExecution = strtotime("+1 month", $credit->nextExecution);
				break;
			case 'twomonth':
				$nextExecution = strtotime("+2 month", $credit->nextExecution);
				break;
			case 'quarter':
				$nextExecution = strtotime("+3 month", $credit->nextExecution);
				break;
			case 'halfyear':
				$nextExecution = strtotime("+6 month", $credit->nextExecution);
				break;
			case 'year':
				$nextExecution = strtotime("+1 year", $credit->nextExecution);
				break;
		}
		
		$creditEditor->update([
				'executionCount' => $executionCount,
				'nextExecution' => $nextExecution
		]);
		
		return $executionCount;
	}
}
