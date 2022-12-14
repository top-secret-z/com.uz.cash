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
namespace cash\system\worker;

use cash\data\cash\CashList;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\system\worker\AbstractRebuildDataWorker;

/**
 * Worker implementation for updating cash.
 */
class CashRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = UserList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 50;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'user_table.userID';
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        if (!$this->loopCount) {
            // reset cash and storage
            BalanceCacheBuilder::getInstance()->reset();
            OpenClaimsCacheBuilder::getInstance()->reset();
            UserStorageHandler::getInstance()->resetAll('cashOpenClaims');
        }

        if (!\count($this->objectList)) {
            return;
        }

        foreach ($this->objectList as $user) {
            $balance = [];
            $openClaims = 0;

            // get cash data
            $list = new CashList();
            $list->getConditionBuilder()->add('userID = ?', [$user->userID]);
            $list->sqlOrderBy = 'cashID ASC';
            $list->readObjects();
            $cashes = $list->getObjects();
            if (!\count($cashes)) {
                // update user and continue
                $action = new UserAction([$user], 'update', [
                    'data' => [
                        'cashBalance' => \serialize($balance),
                    ],
                ]);
                $action->executeAction();
                continue;
            }

            // balance
            foreach ($cashes as $cash) {
                if ($cash->type == 'claimBalanced') {
                    continue;
                }
                if ($cash->type == 'creditChanged') {
                    continue;
                }
                if ($cash->type == 'posting') {
                    continue;
                }

                if (\substr($cash->type, 0, 2) === 'cl') {
                    if (isset($balance[$cash->currency])) {
                        $balance[$cash->currency] += $cash->amount;
                    } else {
                        $balance[$cash->currency] = $cash->amount;
                    }
                } else {
                    if (isset($balance[$cash->currency])) {
                        $balance[$cash->currency] -= $cash->amount;
                    } else {
                        $balance[$cash->currency] = -1 * $cash->amount;
                    }
                }
            }

            // open claims
            $sql = "SELECT    COUNT(*)
                    FROM    cash" . WCF_N . "_cash_claim_user
                    WHERE    userID = ? AND status = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$user->userID, 1]);
            $openClaims = $statement->fetchColumn();

            // update user
            $action = new UserAction([$user], 'update', [
                'data' => [
                    'cashBalance' => \serialize($balance),
                ],
            ]);
            $action->executeAction();
            if ($openClaims) {
                UserStorageHandler::getInstance()->update($user->userID, 'cashOpenClaims', $openClaims);
            }
        }
    }
}
