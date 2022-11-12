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
namespace cash\system\cronjob;

use cash\data\cash\credit\CashCreditEditor;
use cash\data\cash\credit\CashCreditList;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Creates user credits.
 */
class UserCreditsCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // only if on
        if (!MODULE_CASH) {
            return;
        }

        // get active credits due to be sent
        $creditList = new CashCreditList();
        $creditList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $creditList->getConditionBuilder()->add('nextExecution > ?', [0]);
        $creditList->getConditionBuilder()->add('nextExecution < ?', [TIME_NOW]);
        $creditList->getConditionBuilder()->add('executionCount < executions');
        $creditList->sqlLimit = 1;
        $creditList->readObjects();
        $credits = $creditList->getObjects();
        if (!\count($credits)) {
            return;
        }

        $credit = \reset($credits);

        // set credit execution
        $executionCount = $this->setExecution($credit);

        // get users
        $userList = new UserList();
        // usernames
        $userIDs = \unserialize($credit->users);
        if (\count($userIDs)) {
            $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$userIDs]);
        }
        // conditions
        $conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.credit.user', $credit->creditID);
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
        }

        $userList->readObjects();
        $users = $userList->getObjects();
        if (!\count($users)) {
            return;
        }

        // set user credits
        $time = TIME_NOW;
        $sql = "INSERT INTO    cash" . WCF_N . "_cash_credit_user
                    (creditID, time, userID, executionCount, amount, currency)
                VALUES        (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE time = VALUES(time)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($users as $user) {
            $profile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
            if (!$profile->getPermission('user.cash.isPayer')) {
                continue;
            }

            $statement->execute([$credit->creditID, $time, $user->userID, $executionCount, $credit->amount, $credit->currency]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Sets execution data
     */
    protected function setExecution($credit)
    {
        $creditEditor = new CashCreditEditor($credit);
        $executionCount = $credit->executionCount + 1;

        if ($executionCount == $credit->executions) {
            $creditEditor->update([
                'executionCount' => $executionCount,
                'nextExecution' => 0,
            ]);

            return $executionCount;
        }

        // repetitions
        switch($credit->frequency) {
            case 'week':
                $nextExecution = $credit->nextExecution + 7 * 86400;
                break;
            case 'twoweek':
                $nextExecution = $credit->nextExecution + 14 * 86400;
                break;
            case 'month':
                $nextExecution = \strtotime("+1 month", $credit->nextExecution);
                break;
            case 'twomonth':
                $nextExecution = \strtotime("+2 month", $credit->nextExecution);
                break;
            case 'quarter':
                $nextExecution = \strtotime("+3 month", $credit->nextExecution);
                break;
            case 'halfyear':
                $nextExecution = \strtotime("+6 month", $credit->nextExecution);
                break;
            case 'year':
                $nextExecution = \strtotime("+1 year", $credit->nextExecution);
                break;
        }

        $creditEditor->update([
            'executionCount' => $executionCount,
            'nextExecution' => $nextExecution,
        ]);

        return $executionCount;
    }
}
