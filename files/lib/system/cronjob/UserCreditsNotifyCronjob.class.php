<?php
namespace cash\system\cronjob;
use cash\data\cash\CashAction;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\user\UserCashCreditEditor;
use cash\data\cash\credit\user\UserCashCreditList;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\user\notification\object\CreditUserNotificationObject;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Sends notifications about user credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCreditsNotifyCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// only if on
		if (!MODULE_CASH) return;
		
		// get unsent user credits
		$userCreditList = new UserCashCreditList();
		$userCreditList->getConditionBuilder()->add('status = ?', [0]);
		$userCreditList->sqlLimit = 500;
		$userCreditList->readObjects();
		$userCredits = $userCreditList->getObjects();
		if (!count($userCredits)) return;
		
		$users = $credits = [];
		foreach ($userCredits as $userCredit) {
			// store superordinate credits temporarily
			if (!isset($credits[$userCredit->creditID])) {
				$credit = new CashCredit($userCredit->creditID);
				$credits[$userCredit->creditID] = $credit;
			}
			
			// store users temporarily
			if (!isset($users[$userCredit->userID])) {
				$user = new User($userCredit->userID);
				$users[$userCredit->userID] = $user;
			}
			
			// update user credit
			$userCreditEditor = new UserCashCreditEditor($userCredit);
			$userCreditEditor->update([
					'status' => 2
			]);
			
			// log in cash with usercreditID
			$action = new CashAction([], 'create', [
					'data' => [
							'amount' => -1 * $userCredit->amount,
							'currency' => $userCredit->currency,
							'userID' => $users[$userCredit->userID]->userID,
							'username' => $users[$userCredit->userID]->username,
							'time' => TIME_NOW,
							'comment' => empty($userCredit->subject) ? $credits[$userCredit->creditID]->subject : $userCredit->subject,
							'type' => 'creditSent',
							'typeID' => $userCredit->userCreditID
					]
			]);
			$action->executeAction();
			
			// update user
			$balance = unserialize($users[$userCredit->userID]->cashBalance);
			if (isset($balance[$userCredit->currency])) {
				$balance[$userCredit->currency] += $userCredit->amount;
			}
			else {
				$balance[$userCredit->currency] = $userCredit->amount;
			}
			$action = new UserAction([$users[$userCredit->userID]], 'update', [
					'data' => [
							'cashBalance' => serialize($balance)
					]
			]);
			$action->executeAction();
			
			// send notification
			UserNotificationHandler::getInstance()->fireEvent('newCredit', 'com.uz.cash.credit.notification', new CreditUserNotificationObject($credits[$userCredit->creditID]), [$users[$userCredit->userID]->userID]);
		}
		
		// update balance
		BalanceCacheBuilder::getInstance()->reset();
 	}
}
