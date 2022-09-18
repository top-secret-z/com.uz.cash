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

use cash\data\cash\claim\CashClaimEditor;
use cash\data\cash\claim\CashClaimList;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Creates user claims.
 */
class UserClaimsCronjob extends AbstractCronjob
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

        // get active claims due to be sent
        $claimList = new CashClaimList();
        $claimList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $claimList->getConditionBuilder()->add('nextExecution > ?', [0]);
        $claimList->getConditionBuilder()->add('nextExecution < ?', [TIME_NOW]);
        $claimList->getConditionBuilder()->add('executionCount < executions');
        $claimList->sqlLimit = 1;
        $claimList->readObjects();
        $claims = $claimList->getObjects();
        if (!\count($claims)) {
            return;
        }

        $claim = \reset($claims);

        // set claim execution
        $executionCount = $this->setExecution($claim);

        // get users
        $userList = new UserList();
        // usernames
        $userIDs = \unserialize($claim->users);
        if (\count($userIDs)) {
            $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$userIDs]);
        }
        // conditions
        $conditions = ConditionHandler::getInstance()->getConditions('com.uz.cash.condition.user', $claim->claimID);
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
        }

        $userList->readObjects();
        $users = $userList->getObjects();
        if (!\count($users)) {
            return;
        }

        // set user claims
        $time = TIME_NOW;
        $sql = "INSERT INTO    cash" . WCF_N . "_cash_claim_user
                    (claimID, time, userID, executionCount, amount, currency)
                VALUES        (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE time = VALUES(time)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($users as $user) {
            $profile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
            if (!$profile->getPermission('user.cash.isPayer')) {
                continue;
            }

            $statement->execute([$claim->claimID, $time, $user->userID, $executionCount, -1 * $claim->amount, $claim->currency]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Sets execution data
     */
    protected function setExecution($claim)
    {
        $claimEditor = new CashClaimEditor($claim);
        $executionCount = $claim->executionCount + 1;

        if ($executionCount == $claim->executions) {
            $claimEditor->update([
                'executionCount' => $executionCount,
                'nextExecution' => 0,
            ]);

            return $executionCount;
        }

        // repetitions
        switch($claim->frequency) {
            case 'week':
                $nextExecution = $claim->nextExecution + 7 * 86400;
                break;
            case 'twoweek':
                $nextExecution = $claim->nextExecution + 14 * 86400;
                break;
            case 'month':
                $nextExecution = \strtotime("+1 month", $claim->nextExecution);
                break;
            case 'twomonth':
                $nextExecution = \strtotime("+2 month", $claim->nextExecution);
                break;
            case 'quarter':
                $nextExecution = \strtotime("+3 month", $claim->nextExecution);
                break;
            case 'halfyear':
                $nextExecution = \strtotime("+6 month", $claim->nextExecution);
                break;
            case 'year':
                $nextExecution = \strtotime("+1 year", $claim->nextExecution);
                break;
        }

        $claimEditor->update([
            'executionCount' => $executionCount,
            'nextExecution' => $nextExecution,
        ]);

        return $executionCount;
    }
}
