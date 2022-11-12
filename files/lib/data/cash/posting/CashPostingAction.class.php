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
namespace cash\data\cash\posting;

use cash\data\cash\Cash;
use cash\data\cash\CashAction;
use cash\system\cache\builder\BalanceCacheBuilder;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Executes posting-related actions.
 */
class CashPostingAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = CashPostingEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['user.cash.canManage'];

    protected $permissionsCreate = ['user.cash.canManage'];

    protected $permissionsDelete = ['user.cash.canManage'];

    /**
     * @inheritDoc
     */
    protected $requireACP = [];

    /**
     * @inheritDoc
     */
    public function create()
    {
        // create posting
        $data = $this->parameters['data'];
        if (!isset($data['enableHtml'])) {
            $data['enableHtml'] = 1;
        }

        // count attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $data['attachments'] = \count($this->parameters['attachmentHandler']);
        }

        // html
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
        }

        $posting = \call_user_func([$this->className, 'create'], $data);
        $postingEditor = new CashPostingEditor($posting);

        // update attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $this->parameters['attachmentHandler']->updateObjectID($posting->postingID);
        }

        // save embedded objects
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['htmlInputProcessor']->setObjectID($posting->postingID);
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $postingEditor->update(['hasEmbeddedObjects' => 1]);
            }
        }

        // write to cash
        $objectAction = new CashAction([], 'create', [
            'data' => [
                'amount' => $posting->type == 'expense' ? -1 * $posting->amount : $posting->amount,
                'currency' => $posting->currency,
                'userID' => $posting->userID,
                'username' => $posting->username,
                'time' => $posting->time,
                'comment' => $posting->subject,
                'type' => 'posting',
                'typeID' => $posting->postingID,
            ],
        ]);
        $objectAction->executeAction();

        BalanceCacheBuilder::getInstance()->reset();

        return new CashPosting($posting->postingID);
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        // count attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $this->parameters['data']['attachments'] = \count($this->parameters['attachmentHandler']);
        }

        // html
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
        }

        parent::update();

        // get posting
        $temp = $this->getObjects();
        $postingEditor = $temp[0];

        // save embedded objects
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['htmlInputProcessor']->setObjectID($postingEditor->postingID);
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $postingEditor->update(['hasEmbeddedObjects' => 1]);
            }
        }

        // change in cash
        $posting = new CashPosting($postingEditor->postingID);
        $cash = Cash::getPostingCashById($posting->postingID);

        if ($cash->cashID) {
            $objectAction = new CashAction([$cash], 'update', [
                'data' => [
                    'amount' => $posting->type == 'expense' ? -1 * $posting->amount : $posting->amount,
                    'currency' => $posting->currency,
                    'comment' => $posting->subject,
                    'time' => $posting->time,
                ],
            ]);
            $objectAction->executeAction();

            BalanceCacheBuilder::getInstance()->reset();
        }

        BalanceCacheBuilder::getInstance()->reset();

        return $postingEditor->getDecoratedObject();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // collect data
        $attachmentPostingIDs = $cashIDs = [];
        foreach ($this->getObjects() as $posting) {
            if ($posting->attachments) {
                $attachmentPostingIDs[] = $posting->postingID;
            }

            $cash = Cash::getPostingCashById($posting->postingID);
            $cashIDs[] = $cash->cashID;
        }

        parent::delete();

        // delete cash entries
        $objectAction = new CashAction($cashIDs, 'delete');
        $objectAction->executeAction();

        // delete attachments
        if (!empty($attachmentPostingIDs)) {
            AttachmentHandler::removeAttachments('com.uz.cash.posting', $attachmentPostingIDs);
        }

        // update balance
        BalanceCacheBuilder::getInstance()->reset();
    }
}
