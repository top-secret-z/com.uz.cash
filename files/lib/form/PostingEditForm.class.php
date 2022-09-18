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
namespace cash\form;

use cash\data\cash\posting\CashPosting;
use cash\data\cash\posting\CashPostingAction;
use wcf\form\MessageForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;

/**
 * Shows the posting edit form.
 */
class PostingEditForm extends PostingAddForm
{
    /**
     * posting data
     */
    public $postingID = 0;

    public $posting;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        // get posting
        if (!empty($_REQUEST['id'])) {
            $this->postingID = \intval($_REQUEST['id']);
        }
        $this->posting = new CashPosting($this->postingID);
        if (!$this->posting->postingID) {
            throw new IllegalLinkException();
        }
        if (!$this->posting->canEdit()) {
            throw new PermissionDeniedException();
        }

        parent::readParameters();

        // set attachment object id
        $this->attachmentObjectID = $this->posting->postingID;
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (!\count($_POST)) {
            $this->categoryID = $this->posting->categoryID;
            $this->amount = $this->posting->amount;
            $this->currency = $this->posting->currency;
            $this->subject = $this->posting->subject;
            $this->text = $this->posting->message;
            $this->type = $this->posting->type;
            $dateTime = DateUtil::getDateTimeByTimestamp($this->posting->time);
            $dateTime->setTimezone(WCF::getUser()->getTimeZone());
            $this->time = $dateTime->format('c');
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit',
            'posting' => $this->posting,
            'postingID' => $this->postingID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        MessageForm::save();

        // save posting
        $data = \array_merge($this->additionalFields, [
            'postingID' => $this->postingID,
            'categoryID' => $this->categoryID,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'subject' => $this->subject,
            'message' => $this->text,
            'type' => $this->type,
            'time' => $this->timeObj->getTimestamp(),
        ]);

        $postingData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
        ];

        $this->objectAction = new CashPostingAction([$this->posting], 'update', $postingData);
        $this->objectAction->executeAction();

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('PostingList', ['application' => 'cash']));

        exit;
    }
}
