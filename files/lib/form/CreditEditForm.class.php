<?php
namespace cash\form;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\CashCreditAction;
use wcf\form\MessageForm;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the credit edit form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditEditForm extends CreditAddForm {
	/**
	 * credit data
	 */
	public $creditID = 0;
	public $credit = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		// get credit
		if (!empty($_REQUEST['id'])) $this->creditID = intval($_REQUEST['id']);
		$this->credit = new CashCredit($this->creditID);
		if (!$this->credit->creditID) {
			throw new IllegalLinkException();
		}
		if (!$this->credit->canEdit()) {
			throw new PermissionDeniedException();
		}
		
		parent::readParameters();
		
		// set attachment object id
		$this->attachmentObjectID = $this->credit->creditID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->subject = $this->credit->subject;
			$this->text = $this->credit->message;
			
			// time settings
			$this->timezone = $this->credit->timezone;
			$this->timezoneObj = new \DateTimeZone($this->timezone);
			$d = new \DateTime('@'.$this->credit->executionTime);
			$d->setTimezone($this->timezoneObj);
			$this->executionTime = $d->format('c');
			
			// users
			$userIDs = unserialize($this->credit->users);
			$users = UserRuntimeCache::getInstance()->getObjects($userIDs);
			$temp = [];
			$this->users = '';
			if (count($users)) {
				foreach ($users as $user) {
					$temp[] = $user->username;
				}
				$this->users = implode(', ', $temp);
			}
			
			// other
			$this->categoryID = $this->credit->categoryID;
			$this->amount = $this->credit->amount;
			$this->currency = $this->credit->currency;
			$this->frequency = $this->credit->frequency;
			$this->executions = $this->credit->executions;
			
			// conditions
			$conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.credit.user', $this->credit->creditID);
			foreach ($conditions as $condition) {
				$this->userConditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'edit',
				'credit' => $this->credit,
				'creditID' => $this->creditID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		MessageForm::save();
		
		// save credit
		$executionTime = $this->executionDateTime->getTimestamp();
		$data = array_merge($this->additionalFields, [
				'creditID' => $this->creditID,
				'isDisabled' => $this->isDisabled,
				'subject' => $this->subject,
				'message' => $this->text,
				'categoryID' => $this->categoryID,
				'users' => serialize($this->userIDs),
				'amount' => $this->amount,
				'currency' => $this->currency,
				'frequency' => $this->frequency,
				'executions' => $this->executions,
				'executionTime' => $executionTime,
				'nextExecution' => $executionTime,
				'timezone' => $this->timezoneObj->getName()
		]);
		
		$creditData = [
				'data' => $data,
				'attachmentHandler' => $this->attachmentHandler,
				'htmlInputProcessor' => $this->htmlInputProcessor
		];
		
		$this->objectAction = new CashCreditAction([$this->credit], 'update', $creditData);
		$credit = $this->objectAction->executeAction()['returnValues'];
		
		// transform conditions and save
		$conditions = [];
		foreach ($this->userConditions as $groupedObjectTypes) {
			$conditions = array_merge($conditions, $groupedObjectTypes);
		}
		ConditionHandler::getInstance()->updateConditions($credit->creditID, $credit->getUserConditions(), $conditions);
		
		// call saved event
		$this->saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('CreditList', ['application' => 'cash']));
		exit;
	}
}
