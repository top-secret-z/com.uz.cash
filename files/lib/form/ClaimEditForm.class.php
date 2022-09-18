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

use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\CashClaimAction;
use DateTime;
use DateTimeZone;
use wcf\form\MessageForm;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the claim edit form.
 */
class ClaimEditForm extends ClaimAddForm
{
    /**
     * claim data
     */
    public $claimID = 0;

    public $claim;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        // get claim
        if (!empty($_REQUEST['id'])) {
            $this->claimID = \intval($_REQUEST['id']);
        }
        $this->claim = new CashClaim($this->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }
        if (!$this->claim->canEdit()) {
            throw new PermissionDeniedException();
        }

        parent::readParameters();

        // set attachment object id
        $this->attachmentObjectID = $this->claim->claimID;
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (!\count($_POST)) {
            $this->subject = $this->claim->subject;
            $this->text = $this->claim->message;

            // time settings
            $this->timezone = $this->claim->timezone;
            $this->timezoneObj = new DateTimeZone($this->timezone);
            $d = new DateTime('@' . $this->claim->executionTime);
            $d->setTimezone($this->timezoneObj);
            $this->executionTime = $d->format('c');

            // users
            $userIDs = \unserialize($this->claim->users);
            $users = UserRuntimeCache::getInstance()->getObjects($userIDs);
            $temp = [];
            $this->users = '';
            if (\count($users)) {
                foreach ($users as $user) {
                    $temp[] = $user->username;
                }
                $this->users = \implode(', ', $temp);
            }

            // other
            $this->categoryID = $this->claim->categoryID;
            $this->amount = $this->claim->amount;
            $this->currency = $this->claim->currency;
            $this->frequency = $this->claim->frequency;
            $this->executions = $this->claim->executions;

            $this->excludedPaymentMethods = [];
            if ($this->claim->excludedPaymentMethods) {
                $this->excludedPaymentMethods = \unserialize($this->claim->excludedPaymentMethods);
            }

            // conditions
            $conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.user', $this->claim->claimID);
            foreach ($conditions as $condition) {
                $this->userConditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
            }
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
            'claim' => $this->claim,
            'claimID' => $this->claimID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        MessageForm::save();

        // save claim
        $executionTime = $this->executionDateTime->getTimestamp();
        $data = \array_merge($this->additionalFields, [
            'claimID' => $this->claimID,
            'isDisabled' => $this->isDisabled,
            'subject' => $this->subject,
            'message' => $this->text,
            'categoryID' => $this->categoryID,
            'users' => \serialize($this->userIDs),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'excludedPaymentMethods' => \serialize($this->excludedPaymentMethods),
            'frequency' => $this->frequency,
            'executions' => $this->executions,
            'executionTime' => $executionTime,
            'nextExecution' => $executionTime,
            'timezone' => $this->timezoneObj->getName(),
        ]);

        $claimData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
        ];

        $this->objectAction = new CashClaimAction([$this->claim], 'update', $claimData);
        $claim = $this->objectAction->executeAction()['returnValues'];

        // transform conditions and save
        $conditions = [];
        foreach ($this->userConditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }
        ConditionHandler::getInstance()->updateConditions($claim->claimID, $claim->getUserConditions(), $conditions);

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ClaimList', ['application' => 'cash']));

        exit;
    }
}
