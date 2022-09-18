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

use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\CashCreditList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;

/**
 * Attachment object type implementation for credits.
 */
class CreditAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if ($objectID) {
            $credit = new CashCredit($objectID);
            if ($credit->canRead()) {
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
        $creditList = new CashCreditList();
        $creditList->setObjectIDs(\array_unique($objectIDs));
        $creditList->readObjects();

        foreach ($creditList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function setPermissions(array $attachments)
    {
        $creditIDs = [];
        foreach ($attachments as $attachment) {
            // set default permissions
            $attachment->setPermissions([
                'canDownload' => false,
                'canViewPreview' => false,
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
                if (!$credit->canRead()) {
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
