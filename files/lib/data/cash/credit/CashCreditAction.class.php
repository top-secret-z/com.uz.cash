<?php
namespace cash\data\cash\credit;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\condition\ConditionHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Executes credit-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCreditAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CashCreditEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['user.cash.canManage'];
	protected $permissionsCreate = ['user.cash.canManage'];
	protected $permissionsDelete = ['user.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = [];
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		// create credit
		$data = $this->parameters['data'];
		if (!isset($data['enableHtml'])) $data['enableHtml'] = 1;
		
		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$data['attachments'] = count($this->parameters['attachmentHandler']);
		}
		
		// html
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		$credit = call_user_func([$this->className, 'create'], $data);
		$creditEditor = new CashCreditEditor($credit);
		
		// update attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['attachmentHandler']->updateObjectID($credit->creditID);
		}
		
		// save embedded objects
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['htmlInputProcessor']->setObjectID($credit->creditID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
				$creditEditor->update(['hasEmbeddedObjects' => 1]);
			}
		}
		
		return new CashCredit($credit->creditID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['data']['attachments'] = count($this->parameters['attachmentHandler']);
		}
		
		// html
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		parent::update();
		
		// get credit
		$temp = $this->getObjects();
		$creditEditor = $temp[0];
		
		// save embedded objects
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['htmlInputProcessor']->setObjectID($creditEditor->creditID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
				$creditEditor->update(['hasEmbeddedObjects' => 1]);
			}
		}
		
		return $creditEditor->getDecoratedObject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		// collect data
		$attachmentCreditIDs = $creditIDs = [];
		foreach ($this->getObjects() as $credit) {
			$creditIDs[] = $credit->creditID;
			
			if ($credit->attachments) {
				$attachmentCreditIDs[] = $credit->creditID;
			}
		}
		
		// conditions
		ConditionHandler::getInstance()->deleteConditions('com.uz.cash.condition.credit.user', $creditIDs);
		
		parent::delete();
		
		// delete attachments
		if (!empty($attachmentCreditIDs)) {
			AttachmentHandler::removeAttachments('com.uz.cash.credit', $attachmentCreditIDs);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->objects as $credit) {
			$credit->update([
					'isDisabled' => $credit->isDisabled ? 0 : 1
			]);
		}
	}
}
