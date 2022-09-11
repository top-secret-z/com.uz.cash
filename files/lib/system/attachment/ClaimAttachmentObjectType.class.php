<?php
namespace cash\system\attachment;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\CashClaimList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;

/**
 * Attachment object type implementation for claims.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class ClaimAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$claim = new CashClaim($objectID);
			if ($claim->canRead()) return true;
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
		$claimList = new CashClaimList();
		$claimList->setObjectIDs(array_unique($objectIDs));
		$claimList->readObjects();
		
		foreach ($claimList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$claimIDs = [];
		foreach ($attachments as $attachment) {
			// set default permissions
			$attachment->setPermissions([
				'canDownload' => false,
				'canViewPreview' => false
			]);
			
			if ($this->getObject($attachment->objectID) === null) {
				$claimIDs[] = $attachment->objectID;
			}
		}
		
		if (!empty($claimIDs)) {
			$this->cacheObjects($claimIDs);
		}
		
		foreach ($attachments as $attachment) {
			$claim = $this->getObject($attachment->objectID);
			if ($claim !== null) {
				if (!$claim->canRead()) continue;
				
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
