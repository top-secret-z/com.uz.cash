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
 */
class CreditPage extends AbstractPage
{
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
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['typeID'])) {
            $userCreditID = \intval($_REQUEST['typeID']);
            $userCredit = new UserCashCredit($userCreditID);
            if (!$userCredit->userCreditID) {
                throw new IllegalLinkException();
            }
            $this->creditID = $userCredit->creditID;
        } elseif (!empty($_REQUEST['id'])) {
            $this->creditID = \intval($_REQUEST['id']);
        }

        $this->credit = new CashCredit($this->creditID);
        if (!$this->credit->creditID) {
            throw new IllegalLinkException();
        }

        // check permissions
        if (!$this->credit->canRead()) {
            throw new PermissionDeniedException();
        }

        $this->canonicalURL = $this->credit->getLink();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->attachmentList = $this->credit->getAttachments();
        $this->credit->loadEmbeddedObjects();
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.cash.credit', $this->creditID);
        $attachments = \array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->creditID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'credit' => $this->credit,
            'creditID' => $this->creditID,
            'attachmentList' => $this->attachmentList,
            'allowSpidersToIndexThisPage' => false,
        ]);
    }
}
