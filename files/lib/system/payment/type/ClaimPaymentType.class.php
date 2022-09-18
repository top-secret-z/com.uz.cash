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
namespace cash\system\payment\type;

use cash\data\cash\CashAction;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaim;
use cash\data\cash\claim\user\UserCashClaimEditor;
use cash\data\cash\transaction\log\CashTransactionLogAction;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\SystemException;
use wcf\system\payment\type\AbstractPaymentType;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Payment type for claims.
 */
class ClaimPaymentType extends AbstractPaymentType
{
    /**
     * @inheritdoc
     */
    public function processTransaction($paymentMethodObjectTypeID, $token, $amount, $currency, $transactionID, $status, $transactionDetails)
    {
        $user = $userClaim = $claim = null;
        try {
            $tokenParts = \explode(':', $token);
            if (\count($tokenParts) != 2) {
                throw new SystemException('invalid token');
            }
            [$userID, $userClaimID] = $tokenParts;

            // get user claim
            $userClaim = new UserCashClaim(\intval($userClaimID));
            if (!$userClaim->claimID) {
                throw new SystemException('invalid user claim (' . $token . ')');
            }

            // get claim
            $claim = new CashClaim($userClaim->claimID);
            if (!$claim->claimID) {
                throw new SystemException('invalid claim (' . $claim->claimID . ')');
            }

            // get user
            $user = new User(\intval($userID));
            if (!$user->userID) {
                throw new SystemException('invalid user');
            }

            $logMessage = '';
            if ($status == 'completed') {
                if ($amount != -1 * $userClaim->amount || $currency != $userClaim->currency) {
                    throw new SystemException('invalid payment amount (' . $amount . ' != ' . $userClaim->amount . ')');
                }

                if ($userClaim->status != 1) {
                    throw new SystemException('user claim already processed (' . $token . ')');
                }

                // update status
                $editor = new UserCashClaimEditor($userClaim);
                $editor->update([
                    'status' => 2,
                    'isTransfer' => 0,
                ]);

                // log in cash with userClaimId
                $action = new CashAction([], 'create', [
                    'data' => [
                        'amount' => -1 * $userClaim->amount,
                        'currency' => $userClaim->currency,
                        'userID' => $user->userID,
                        'username' => $user->username,
                        'time' => TIME_NOW,
                        'comment' => empty($userClaim->subject) ? $claim->subject : $userClaim->subject,
                        'type' => 'claimPaid',
                        'typeID' => $userClaim->userClaimID,
                    ],
                ]);
                $action->executeAction();

                // update user
                $this->updateUser($user, -1 * $userClaim->amount, $userClaim->currency);

                // log
                $logMessage = 'payment completed';
            }

            if ($status == 'reversed') {
                // update status
                $editor = new UserCashClaimEditor($userClaim);
                $editor->update([
                    'status' => 1,
                ]);

                // log in cash
                $action = new CashAction([], 'create', [
                    'data' => [
                        'amount' => $userClaim->amount,
                        'currency' => $userClaim->currency,
                        'userID' => $user->userID,
                        'username' => $user->username,
                        'time' => TIME_NOW,
                        'comment' => empty($userClaim->subject) ? $claim->subject : $userClaim->subject,
                        'type' => 'claimReversed',
                        'typeID' => $userClaim->userClaimID,
                    ],
                ]);
                $action->executeAction();

                // update user
                $this->updateUser($user, $userClaim->amount, $userClaim->currency);

                // log
                $logMessage = 'payment reversed';
            }

            if ($status == 'canceled_reversal') {
                // update status
                $editor = new UserCashClaimEditor($userClaim);
                $editor->update([
                    'status' => 2,
                    'isTransfer' => 0,
                ]);

                // log in cash
                $action = new CashAction([], 'create', [
                    'data' => [
                        'amount' => -1 * $userClaim->amount,
                        'currency' => $userClaim->currency,
                        'userID' => $user->userID,
                        'username' => $user->username,
                        'time' => TIME_NOW,
                        'comment' => empty($userClaim->subject) ? $claim->subject : $userClaim->subject,
                        'type' => 'claimRepaid',
                        'typeID' => $userClaim->userClaimID,
                    ],
                ]);
                $action->executeAction();

                // update user
                $this->updateUser($user, -1 * $userClaim->amount, $userClaim->currency);

                // log
                $logMessage = $status;
            }

            // reset cache and user storage
            BalanceCacheBuilder::getInstance()->reset();
            OpenClaimsCacheBuilder::getInstance()->reset();
            UserStorageHandler::getInstance()->reset([$user->userID], 'cashOpenClaims');

            $action = new CashTransactionLogAction([], 'create', [
                'data' => [
                    'userID' => ($user !== null ? $user->userID : null),
                    'userClaimID' => $userClaimID,
                    'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
                    'logTime' => TIME_NOW,
                    'transactionID' => $transactionID,
                    'logMessage' => $logMessage,
                    'transactionDetails' => \serialize($transactionDetails),
                ],
            ]);
            $action->executeAction();
        } catch (SystemException $e) {
            $action = new CashTransactionLogAction([], 'create', [
                'data' => [
                    'userID' => ($user !== null ? $user->userID : null),
                    'userClaimID' => $userClaimID,
                    'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
                    'logTime' => TIME_NOW,
                    'transactionID' => $transactionID,
                    'logMessage' => $e->getMessage(),
                    'transactionDetails' => \serialize($transactionDetails),
                ],
            ]);
            $action->executeAction();

            throw $e;
        }
    }

    /**
     * update user
     */
    public function updateUser($user, $amount, $currency)
    {
        $balance = \unserialize($user->cashBalance);
        if (isset($balance[$currency])) {
            $balance[$currency] += $amount;
        } else {
            $balance[$currency] = $amount;
        }
        $action = new UserAction([$user], 'update', [
            'data' => [
                'cashBalance' => \serialize($balance),
            ],
        ]);
        $action->executeAction();
    }
}
