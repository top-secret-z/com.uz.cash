<?php
namespace cash\system\attachment;
use cash\data\cash\posting\CashPosting;
use cash\data\cash\posting\CashPostingList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;

/**
 * Attachment object type implementation for posings.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class PostingAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$posting = new CashPosting($objectID);
			if ($posting->canRead()) return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if (WCF::getSession()->getPermission('user.cash.canManage')) return true;
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		return $this->canUpload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {
		$postingList = new CashPostingList();
		$postingList->setObjectIDs(array_unique($objectIDs));
		$postingList->readObjects();
		
		foreach ($postingList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$postingIDs = [];
		foreach ($attachments as $attachment) {
			// set default permissions
			$attachment->setPermissions([
				'canDownload' => false,
				'canViewPreview' => false
			]);
			
			if ($this->getObject($attachment->objectID) === null) {
				$postingIDs[] = $attachment->objectID;
			}
		}
		
		if (!empty($postingIDs)) {
			$this->cacheObjects($postingIDs);
		}
		
		foreach ($attachments as $attachment) {
			$posting = $this->getObject($attachment->objectID);
			if ($posting !== null) {
				if (!$posting->canRead()) continue;
				
				$attachment->setPermissions([
						'canDownload' => true,
						'canViewPreview' => true
				]);
			}
			else if ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
				$attachment->setPermissions([
						'canDownload' => true,
						'canViewPreview' => true
				]);
			}
		}
	}
}
