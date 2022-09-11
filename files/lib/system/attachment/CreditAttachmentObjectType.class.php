<?php
namespace cash\system\attachment;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\CashCreditList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;

/**
 * Attachment object type implementation for credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$credit = new CashCredit($objectID);
			if ($credit->canRead()) return true;
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
		$creditList = new CashCreditList();
		$creditList->setObjectIDs(array_unique($objectIDs));
		$creditList->readObjects();
		
		foreach ($creditList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$creditIDs = [];
		foreach ($attachments as $attachment) {
			// set default permissions
			$attachment->setPermissions([
				'canDownload' => false,
				'canViewPreview' => false
			]);
			
			if ($this->getObject($attachment->objectID) === null) {
				$creditIDs[] = $attachment->objectID;
			}
		}
		
		if (!empty($creditIDs)) {
			$this->cacheObjects($creditIDs);
		}
		
		foreach ($attachments as $attachment) {
			$credit = $this->getObject($attachment->objectID);
			if ($credit !== null) {
				if (!$credit->canRead()) continue;
				
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
