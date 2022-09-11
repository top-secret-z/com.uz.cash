<?php
namespace cash\page;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\user\UserCashCredit;
use wcf\data\attachment\Attachment;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Shows the credit page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditPage extends AbstractPage {
	/**
	 * credit
	 */
	public $creditID = 0;
	public $credit;
	
	/**
	 * attachment list
	 */
	public $attachmentList;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [];
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'credit';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['typeID'])) {
			$userCreditID = intval($_REQUEST['typeID']);
			$userCredit = new UserCashCredit($userCreditID);
			if (!$userCredit->userCreditID) throw new IllegalLinkException();
			$this->creditID = $userCredit->creditID;
		}
		else if (!empty($_REQUEST['id'])) {
			$this->creditID = intval($_REQUEST['id']);
		}
		
		$this->credit = new CashCredit($this->creditID);
		if (!$this->credit->creditID) throw new IllegalLinkException();
		
		// check permissions
		if (!$this->credit->canRead()) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = $this->credit->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->attachmentList = $this->credit->getAttachments();
		$this->credit->loadEmbeddedObjects();
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.cash.credit', $this->creditID);
		$attachments = array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->creditID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'credit' => $this->credit,
				'creditID' => $this->creditID,
				'attachmentList' => $this->attachmentList,
				'allowSpidersToIndexThisPage' => false
		]);
	}
}
