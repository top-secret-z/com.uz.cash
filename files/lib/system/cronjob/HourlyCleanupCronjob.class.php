<?php
namespace cash\system\cronjob;
use cash\data\cash\CashAction;
use cash\data\cash\CashList;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Daily cleanup.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class HourlyCleanupCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// only if on
		if (!MODULE_CASH) return;
		if (!CASH_CLAIM_USER_DELETED && !CASH_CLAIM_USER_INACTIVE) return;
		
		// cancel open claims in cash when user is deleted
		if (CASH_CLAIM_USER_DELETED) {
			// get typeIDs with potentially open claims not touched before
			$typeIDs = [];
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('userID IS NULL');
			$conditionBuilder->add('isDeleted = ?', [0]);
			$conditionBuilder->add('type LIKE ?', ['claim%']);
			
			$sql = "SELECT DISTINCT	typeID
					FROM			cash".WCF_N."_cash
					".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql, 250);
			$statement->execute($conditionBuilder->getParameters());
			while ($row = $statement->fetchArray()) {
				$typeIDs[] = $row['typeID'];
			}
			
			if (count($typeIDs)) {
				$this->deleteUserClaims($typeIDs);
			}
		}
		
		if (CASH_CLAIM_USER_INACTIVE) {
			// get typeIDs with potentially open claims not touched before
			$typeIDs = [];
			$time = TIME_NOW - CASH_CLAIM_USER_INACTIVE_DAYS * 86400;
			
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('status = ?', [1]);
			$conditionBuilder->add('time < ?', [$time]);
			$conditionBuilder->add('userID IN (SELECT userID FROM wcf'.WCF_N.'_user WHERE lastActivityTime < ? AND registrationDate < ?)', [$time, $time]);
			
			$sql = "SELECT DISTINCT	userClaimID
					FROM			cash".WCF_N."_cash_claim_user
					".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql, 250);
			$statement->execute($conditionBuilder->getParameters());
			while ($row = $statement->fetchArray()) {
				$typeIDs[] = $row['userClaimID'];
			}
			
			if (count($typeIDs)) {
				$this->deleteUserClaims($typeIDs);
			}
		}
		
		// reset caches
		BalanceCacheBuilder::getInstance()->reset();
		OpenClaimsCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Does the cancellation
	 */
	public function deleteUserClaims($typeIDs) {
		// step through all typeIDs and check latest entry
		$deleteIDs = [];
		foreach ($typeIDs as $typeID) {
			$cashList = new CashList();
			$cashList->getConditionBuilder()->add('typeID = ?', [$typeID]);
			$cashList->sqlOrderBy = 'cashID DESC';
			$cashList->readObjects();
			$userCashs = $cashList->getObjects();
			$latest = reset($userCashs);
			
			// if open claim
			if ($latest->type == 'claimSent' || $latest->type == 'claimReversed') {
				// mark for deletion
				$deleteIDs[] = $latest->typeID;
				
				// create cash entry
				$action = new CashAction([], 'create', [
						'data' => [
								'amount' => -1 * $latest->amount,
								'currency' => $latest->currency,
								'userID' => $latest->userID,
								'username' => $latest->username,
								'time' => TIME_NOW,
								'comment' => $latest->comment,
								'type' => 'claimDeleted',
								'typeID' => $latest->typeID
						]
				]);
				$action->executeAction();
			}
			
			// update all releated cash entries
			$sql = "UPDATE	cash".WCF_N."_cash
					SET 	isDeleted = ?, typeID = ?
					WHERE	typeID = ? AND type LIKE ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([1, 0, $latest->typeID, 'claim%']);
		}
		
		// delete user claims, do not use action (!)
		// ufn deletion is additionally done by foreign key on userID upon user deletion
		if (count($deleteIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('userClaimID IN (?)', [$deleteIDs]);
			
			$sql = "DELETE FROM cash".WCF_N."_cash_claim_user
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
		}
	}
}
