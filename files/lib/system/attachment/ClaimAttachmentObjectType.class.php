<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace cash\system\attachment;

use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\CashClaimList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;

/**
 * Attachment object type implementation for claims.
 */
class ClaimAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if ($objectID) {
            $claim = new CashClaim($objectID);
            if ($claim->canRead()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canViewPreview($objectID)
    {
        return $this->canDownload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0)
    {
        if (WCF::getSession()->getPermission('user.cash.canManage')) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canDelete($objectID)
    {
        return $this->canUpload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs)
    {
        $claimList = new CashClaimList();
        $claimList->setObjectIDs(\array_unique($objectIDs));
        $claimList->readObjects();

        foreach ($claimList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function setPermissions(array $attachments)
    {
        $claimIDs = [];
        foreach ($attachments as $attachment) {
            // set default permissions
            $attachment->setPermissions([
                'canDownload' => false,
                'canViewPreview' => false,
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
                if (!$claim->canRead()) {
                    continue;
                }

                $attachment->setPermissions([
                    'canDownload' => true,
                    'canViewPreview' => true,
                ]);
            } elseif ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
                $attachment->setPermissions([
                    'canDownload' => true,
                    'canViewPreview' => true,
                ]);
            }
        }
    }
}
