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

use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\condition\ConditionHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Executes claim-related actions.
 */
class CashClaimAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = CashClaimEditor::class;

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
        // create claim
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

        $claim = \call_user_func([$this->className, 'create'], $data);
        $claimEditor = new CashClaimEditor($claim);

        // update attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $this->parameters['attachmentHandler']->updateObjectID($claim->claimID);
        }

        // save embedded objects
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['htmlInputProcessor']->setObjectID($claim->claimID);
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $claimEditor->update(['hasEmbeddedObjects' => 1]);
            }
        }

        OpenClaimsCacheBuilder::getInstance()->reset();

        return new CashClaim($claim->claimID);
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

        // get claim
        $temp = $this->getObjects();
        $claimEditor = $temp[0];

        // save embedded objects
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['htmlInputProcessor']->setObjectID($claimEditor->claimID);
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $claimEditor->update(['hasEmbeddedObjects' => 1]);
            }
        }

        OpenClaimsCacheBuilder::getInstance()->reset();

        return $claimEditor->getDecoratedObject();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // collect data
        $attachmentClaimIDs = $claimIDs = [];
        foreach ($this->getObjects() as $claim) {
            $claimIDs[] = $claim->claimID;

            if ($claim->attachments) {
                $attachmentClaimIDs[] = $claim->claimID;
            }
        }

        // conditions
        ConditionHandler::getInstance()->deleteConditions('com.uz.cash.condition.user', $claimIDs);

        parent::delete();

        // delete attachments
        if (!empty($attachmentClaimIDs)) {
            AttachmentHandler::removeAttachments('com.uz.cash.claim', $attachmentClaimIDs);
        }

        OpenClaimsCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->objects as $claim) {
            $claim->update([
                'isDisabled' => $claim->isDisabled ? 0 : 1,
            ]);
        }

        OpenClaimsCacheBuilder::getInstance()->reset();
    }
}
