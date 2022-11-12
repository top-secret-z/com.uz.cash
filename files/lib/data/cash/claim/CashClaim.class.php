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
namespace cash\data\cash\claim;

use LogicException;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\data\DatabaseObject;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\condition\ConditionHandler;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a cash claim.
 */
class CashClaim extends DatabaseObject implements IRouteController
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'cash_claim';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'claimID';

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->subject;
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Claim', [
            'application' => 'cash',
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * affected cache
     *
     */
    protected static $affectedCache;

    /**
     * Returns true if the active user is affected by this claim.
     */
    public function isAffected()
    {
        // only users
        if (!WCF::getUser()->userID) {
            return false;
        }

        if (self::$affectedCache === null) {
            self::loadAffectedCache();
        }

        return isset(self::$affectedCache[$this->claimID]);
    }

    /**
     * Returns true if the active user has the permission to read this claim.
     */
    public function canRead()
    {
        // manager
        if (WCF::getSession()->getPermission('user.cash.canManage')) {
            return true;
        }

        // disabled
        if ($this->isDisabled) {
            return false;
        }

        // affected
        if ($this->isAffected()) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if current user can edit this claim.
     */
    public function canEdit()
    {
        // manager
        if (WCF::getSession()->getPermission('user.cash.canManage')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the current user can delete this claim.
     */
    public function canDelete()
    {
        // admin
        if (WCF::getSession()->getPermission('admin.cash.canManage')) {
            return true;
        }

        return false;
    }

    /**
     * Returns the ids of claims the user is affected by.
     */
    public static function getAffectedCache()
    {
        if (self::$affectedCache === null) {
            self::loadAffectedCache();
        }

        return self::$affectedCache;
    }

    /**
     * Loads the ids of claims the user is affected by.
     */
    protected static function loadAffectedCache()
    {
        self::$affectedCache = [];
        if (!WCF::getUser()->userID) {
            return;
        }

        $sql = "SELECT    claimID
                FROM    cash" . WCF_N . "_cash_claim_user
                WHERE    userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([WCF::getUser()->userID]);
        while ($claimID = $statement->fetchColumn()) {
            self::$affectedCache[$claimID] = $claimID;
        }
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->process($this->getMessage(), 'com.uz.cash.claim', $this->claimID);

        return $processor->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Loads the embedded objects.
     */
    public function loadEmbeddedObjects()
    {
        if ($this->hasEmbeddedObjects && !$this->embeddedObjectsLoaded) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.cash.claim', [$this->claimID]);
            $this->embeddedObjectsLoaded = true;
        }
    }

    /**
     * Returns and assigns embedded attachments.
     */
    public function getAttachments()
    {
        if (MODULE_ATTACHMENT == 1 && $this->attachments) {
            $attachmentList = new GroupedAttachmentList('com.uz.cash.claim');
            $attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', [$this->claimID]);
            $attachmentList->readObjects();
            //$attachmentList->setPermissions([]);

            AttachmentBBCode::setAttachmentList($attachmentList);

            return $attachmentList;
        }

        return null;
    }

    /**
     * Returns a version of this message optimized for use in emails.
     */
    public function getMailText($mimeType = 'text/plain')
    {
        switch ($mimeType) {
            case 'text/plain':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/plain');
                $processor->process($this->getMessage(), 'com.uz.cash.claim', $this->claimID);

                return $processor->getHtml();
            case 'text/html':
                return $this->getSimplifiedFormattedMessage();
        }

        throw new LogicException('Unreachable');
    }

    /**
     * Returns a simplified version of the formatted message.
     */
    public function getSimplifiedFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($this->getMessage(), 'com.uz.cash.claim', $this->claimID);

        return $processor->getHtml();
    }

    /**
     * Returns the user conditions of the claim.
     */
    public function getUserConditions()
    {
        return ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.user', $this->claimID);
    }

    /**
     * Returns the objectID
     */
    public function getObjectID()
    {
        return $this->claimID;
    }
}
