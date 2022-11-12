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

use cash\data\cash\CashAction;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaimEditor;
use cash\data\cash\claim\user\UserCashClaimList;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use cash\system\user\notification\object\ClaimUserNotificationObject;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Sends notifications about user claims.
 */
class UserClaimsNotifyCronjob extends AbstractCronjob
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

        // get unsent user claims
        $userClaimList = new UserCashClaimList();
        $userClaimList->getConditionBuilder()->add('status = ?', [0]);
        $userClaimList->sqlLimit = 500;
        $userClaimList->readObjects();
        $userClaims = $userClaimList->getObjects();
        if (!\count($userClaims)) {
            return;
        }

        $users = $claims = [];
        foreach ($userClaims as $userClaim) {
            // store superordinate claims temporarily
            if (!isset($claims[$userClaim->claimID])) {
                $claim = new CashClaim($userClaim->claimID);
                $claims[$userClaim->claimID] = $claim;
            }

            // store users temporarily
            if (!isset($users[$userClaim->userID])) {
                $user = new User($userClaim->userID);
                $users[$userClaim->userID] = $user;
            }

            // update user claim
            $userClaimEditor = new UserCashClaimEditor($userClaim);
            $userClaimEditor->update([
                'status' => 1,
            ]);

            // log in cash with userclaimID
            $action = new CashAction([], 'create', [
                'data' => [
                    'amount' => $userClaim->amount,
                    'currency' => $userClaim->currency,
                    'userID' => $users[$userClaim->userID]->userID,
                    'username' => $users[$userClaim->userID]->username,
                    'time' => TIME_NOW,
                    'comment' => empty($userClaim->subject) ? $claims[$userClaim->claimID]->subject : $userClaim->subject,
                    'type' => 'claimSent',
                    'typeID' => $userClaim->userClaimID,
                ],
            ]);
            $action->executeAction();

            // update user
            $balance = \unserialize($users[$userClaim->userID]->cashBalance);
            if (isset($balance[$userClaim->currency])) {
                $balance[$userClaim->currency] += $userClaim->amount;
            } else {
                $balance[$userClaim->currency] = $userClaim->amount;
            }
            $action = new UserAction([$users[$userClaim->userID]], 'update', [
                'data' => [
                    'cashBalance' => \serialize($balance),
                ],
            ]);
            $action->executeAction();

            // send notification
            UserNotificationHandler::getInstance()->fireEvent('newClaim', 'com.uz.cash.claim.notification', new ClaimUserNotificationObject($claims[$userClaim->claimID]), [$users[$userClaim->userID]->userID]);
        }

        OpenClaimsCacheBuilder::getInstance()->reset();

        UserStorageHandler::getInstance()->resetAll('cashOpenClaims');
    }
}
