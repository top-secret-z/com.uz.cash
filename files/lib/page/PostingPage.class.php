<?php
namespace cash\page;
use cash\data\cash\posting\CashPosting;
use wcf\data\attachment\Attachment;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Shows the posting page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class PostingPage extends AbstractPage {
	/**
	 * posting
	 */
	public $postingID = 0;
	public $posting;
	
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
	public $templateName = 'posting';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['id'])) $this->postingID = intval($_REQUEST['id']);
		$this->posting = new CashPosting($this->postingID);
		if (!$this->posting->postingID) throw new IllegalLinkException();
		
		// check permissions
		if (!$this->posting->canRead()) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = $this->posting->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->attachmentList = $this->posting->getAttachments();
		$this->posting->loadEmbeddedObjects();
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.cash.posting', $this->postingID);
		$attachments = array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->postingID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'posting' => $this->posting,
				'postingID' => $this->postingID,
				'attachmentList' => $this->attachmentList,
				'allowSpidersToIndexThisPage' => false
		]);
	}
}
