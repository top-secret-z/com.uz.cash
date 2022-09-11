<?php
namespace cash\data\cash\credit\user;
use cash\data\cash\Cash;
use cash\data\cash\CashAction;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\user\UserCashCredit;
use cash\data\cash\credit\user\UserCashCreditEditor;
use cash\system\cache\builder\BalanceCacheBuilder;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes user credit-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashCreditAction extends AbstractDatabaseObjectAction {
	/**
	 * data
	 */
	public $credit = null;
	public $userCredit = null;
	public $user = null;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['user.cash.canManage'];
	protected $permissionsUpdate = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = UserCashCreditEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		parent::delete();
		
		// leave ufn. Presently credits can't be deleted 
	}
	
	/**
	 * Validates the getCreditEditDialog action.
	 * Only if status == 1
	 */
	public function validateGetCreditEditDialog() {
		WCF::getSession()->checkPermissions(['user.cash.canManage']);
		
		$this->userCredit = new UserCashCredit($this->parameters['objectID']);
		if (!$this->userCredit->userCreditID || $this->userCredit->status != 1) {
			throw new IllegalLinkException();
		}
		
		$this->credit = new CashCredit($this->userCredit->creditID);
		if (!$this->credit->creditID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Executes the userCreditEditDialog action.
	 */
	public function getCreditEditDialog () {
		// get available availableCurrencies and subject
		$currencies = explode("\n", StringUtil::unifyNewlines(StringUtil::trim(CASH_CURRENCIES)));
		
		// original subject if not modified
		if (empty($this->userCredit->subject)) $subject = $this->credit->subject;
		else $subject = $this->userCredit->subject;
		
		WCF::getTPL()->assign([
				'availableCurrencies' => $currencies,
				'amount' => $this->userCredit->amount,
				'currency' => $this->userCredit->currency,
				'subject' => $subject
		]);
		
		return [
				'template' => WCF::getTPL()->fetch('creditEditDialog', 'cash')
		];
	}
	
	/**
	 * Validates the saveCreditEditDialog action.
	 * Only revoke credits can be edited; status == 1
	 */
	public function validateSaveCreditEditDialog() {
		WCF::getSession()->checkPermissions(['user.cash.canManage']);
		
		$this->userCredit = new UserCashCredit($this->parameters['objectID']);
		if (!$this->userCredit->userCreditID || $this->userCredit->status != 1) {
			throw new IllegalLinkException();
		}
		
		$this->credit = new CashCredit($this->userCredit->creditID);
		if (!$this->credit->creditID) {
			throw new IllegalLinkException();
		}
		
		$this->user = new User($this->userCredit->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Executes the userCreditEditDialog action.
	 */
	public function saveCreditEditDialog () {
		// update user credit
		$userCreditEditor = new UserCashCreditEditor($this->userCredit);
		$userCreditEditor->update([
				'amount' => $this->parameters['amount'],
				'currency' => $this->parameters['currency'],
				'subject' => strcmp($this->parameters['subject'], $this->credit->subject) ? $this->parameters['subject'] : '',
				'isChanged' => 1
		]);
		
		// log in cash with 2 entries
		$comment = empty($this->userCredit->subject) ? $this->credit->subject : $this->userCredit->subject;
		$action = new CashAction([], 'create', [
				'data' => [
						'amount' => -1 * $this->userCredit->amount,
						'currency' => $this->userCredit->currency,
						'userID' => $this->user->userID,
						'username' => $this->user->username,
						'time' => TIME_NOW,
						'comment' => $comment,
						'type' => 'creditChanged',
						'typeID' => $this->userCredit->userCreditID
				]
		]);
		$action->executeAction();
		
		$action = new CashAction([], 'create', [
				'data' => [
						'amount' => $this->parameters['amount'],
						'currency' => $this->parameters['currency'],
						'userID' => $this->user->userID,
						'username' => $this->user->username,
						'time' => TIME_NOW,
						'comment' => $comment,
						'type' => 'creditChanged',
						'typeID' => $this->userCredit->userCreditID
				]
		]);
		$action->executeAction();
	}
	
	/**
	 * Validates the creditCredit action.
	 * status must be == 1
	 */
	public function validateCreditCredit() {
		WCF::getSession()->checkPermissions(['user.cash.canManage']);
		
		$this->userCredit = new UserCashCredit($this->parameters['objectID']);
		if (!$this->userCredit->userCreditID || $this->userCredit->status != 1) {
			throw new IllegalLinkException();
		}
		
		$this->credit = new CashCredit($this->userCredit->creditID);
		if (!$this->credit->creditID) {
			throw new IllegalLinkException();
		}
		
		$this->user = new User($this->userCredit->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Executes the creditCredit action.
	 */
	public function creditCredit () {
		// set credit to paid
		$userCreditEditor = new UserCashCreditEditor($this->userCredit);
		$userCreditEditor->update([
				'status' => $this->userCredit->isChanged ? 3 : 2
		]);
		
		// log in cash
		$action = new CashAction([], 'create', [
				'data' => [
						'amount' => -1 * $this->userCredit->amount,
						'currency' => $this->userCredit->currency,
						'userID' => $this->user->userID,
						'username' => $this->user->username,
						'time' => TIME_NOW,
						'comment' => empty($this->userCredit->subject) ? $this->credit->subject : $this->userCredit->subject,
						'type' => 'creditPaid',
						'typeID' => $this->userCredit->userCreditID
				]
		]);
		$action->executeAction();
		
		// update user
		$this->updateUser($this->user, $this->userCredit->amount, $this->userCredit->currency);
		
		// update balance
		BalanceCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Validates the uncreditCredit action.
	 * status must not be 1
	 */
	public function validateUncreditCredit() {
		WCF::getSession()->checkPermissions(['user.cash.canManage']);
		
		$this->userCredit = new UserCashCredit($this->parameters['objectID']);
		if (!$this->userCredit->userCreditID || $this->userCredit->status == 1) {
			throw new IllegalLinkException();
		}
		
		$this->credit = new CashCredit($this->userCredit->creditID);
		if (!$this->credit->creditID) {
			throw new IllegalLinkException();
		}
		
		$this->user = new User($this->userCredit->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Executes the uncreditCredit action.
	 */
	public function uncreditCredit () {
		// set credit to unpaid
		$userCreditEditor = new UserCashCreditEditor($this->userCredit);
		$userCreditEditor->update([
				'status' => 1
		]);
		
		// log in cash
		$objectAction = new CashAction([], 'create', [
				'data' => [
						'amount' => $this->userCredit->amount,
						'currency' => $this->userCredit->currency,
						'userID' => $this->user->userID,
						'username' => $this->user->username,
						'time' => TIME_NOW,
						'comment' => empty($this->userCredit->subject) ? $this->credit->subject : $this->userCredit->subject,
						'type' => 'creditReversed',
						'typeID' => $this->userCredit->userCreditID
				]
		]);
		$objectAction->executeAction();
		
		// update user
		$this->updateUser($this->user, -1 * $this->userCredit->amount, $this->userCredit->currency);
		
		// update balance
		BalanceCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * update user cash balance
	 */
	public function updateUser($user, $amount, $currency) {
		$balance = unserialize($user->cashBalance);
		if (isset($balance[$currency])) {
			$balance[$currency] += $amount;
		}
		else {
			$balance[$currency] = $amount;
		}
		$action = new UserAction([$user], 'update', [
				'data' => [
						'cashBalance' => serialize($balance)
				]
		]);
		$action->executeAction();
	}
}
