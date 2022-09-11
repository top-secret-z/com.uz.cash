<?php
namespace cash\page;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaim;
use wcf\data\attachment\Attachment;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Shows the claim page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class ClaimPage extends AbstractPage {
	/**
	 * claim
	 */
	public $claimID = 0;
	public $claim;
	
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
	public $templateName = 'claim';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['typeID'])) {
			$userClaimID = intval($_REQUEST['typeID']);
			$userClaim = new UserCashClaim($userClaimID);
			if (!$userClaim->userClaimID) throw new IllegalLinkException();
			$this->claimID = $userClaim->claimID;
		}
		else if (!empty($_REQUEST['id'])) {
			$this->claimID = intval($_REQUEST['id']);
		}
		
		$this->claim = new CashClaim($this->claimID);
		if (!$this->claim->claimID) throw new IllegalLinkException();
		
		// check permissions
		if (!$this->claim->canRead()) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = $this->claim->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->attachmentList = $this->claim->getAttachments();
		$this->claim->loadEmbeddedObjects();
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.cash.claim', $this->claimID);
		$attachments = array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->claimID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'claim' => $this->claim,
				'claimID' => $this->claimID,
				'attachmentList' => $this->attachmentList,
				'allowSpidersToIndexThisPage' => false
		]);
	}
}
