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

use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\CashCreditAction;
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
 * Shows the credit edit form.
 */
class CreditEditForm extends CreditAddForm
{
    /**
     * credit data
     */
    public $creditID = 0;

    public $credit;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        // get credit
        if (!empty($_REQUEST['id'])) {
            $this->creditID = \intval($_REQUEST['id']);
        }
        $this->credit = new CashCredit($this->creditID);
        if (!$this->credit->creditID) {
            throw new IllegalLinkException();
        }
        if (!$this->credit->canEdit()) {
            throw new PermissionDeniedException();
        }

        parent::readParameters();

        // set attachment object id
        $this->attachmentObjectID = $this->credit->creditID;
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (!\count($_POST)) {
            $this->subject = $this->credit->subject;
            $this->text = $this->credit->message;

            // time settings
            $this->timezone = $this->credit->timezone;
            $this->timezoneObj = new DateTimeZone($this->timezone);
            $d = new DateTime('@' . $this->credit->executionTime);
            $d->setTimezone($this->timezoneObj);
            $this->executionTime = $d->format('c');

            // users
            $userIDs = \unserialize($this->credit->users);
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
            $this->categoryID = $this->credit->categoryID;
            $this->amount = $this->credit->amount;
            $this->currency = $this->credit->currency;
            $this->frequency = $this->credit->frequency;
            $this->executions = $this->credit->executions;

            // conditions
            $conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.credit.user', $this->credit->creditID);
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
            'credit' => $this->credit,
            'creditID' => $this->creditID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        MessageForm::save();

        // save credit
        $executionTime = $this->executionDateTime->getTimestamp();
        $data = \array_merge($this->additionalFields, [
            'creditID' => $this->creditID,
            'isDisabled' => $this->isDisabled,
            'subject' => $this->subject,
            'message' => $this->text,
            'categoryID' => $this->categoryID,
            'users' => \serialize($this->userIDs),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'frequency' => $this->frequency,
            'executions' => $this->executions,
            'executionTime' => $executionTime,
            'nextExecution' => $executionTime,
            'timezone' => $this->timezoneObj->getName(),
        ]);

        $creditData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
        ];

        $this->objectAction = new CashCreditAction([$this->credit], 'update', $creditData);
        $credit = $this->objectAction->executeAction()['returnValues'];

        // transform conditions and save
        $conditions = [];
        foreach ($this->userConditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }
        ConditionHandler::getInstance()->updateConditions($credit->creditID, $credit->getUserConditions(), $conditions);

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('CreditList', ['application' => 'cash']));

        exit;
    }
}
