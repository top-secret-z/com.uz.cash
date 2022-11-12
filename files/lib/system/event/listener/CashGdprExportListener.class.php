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
namespace cash\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Exports user data iwa Gdpr.
 */
class CashGdprExportListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        // add balance data in user and transactions
        $balance = \unserialize($eventObj->user->cashBalance);
        $eventObj->data['cpm.uz.cash'] = [
            'cashBalance' => $balance,
            'cashTransactionLog' => $this->dumpTable('cash' . WCF_N . '_cash_transaction_log', 'userID', $eventObj->user->userID),
        ];
    }

    /**
     * dump table copied from action and modified
     */
    protected function dumpTable($tableName, $userIDColumn, $userID)
    {
        $sql = "SELECT    *
                FROM    {$tableName}
                WHERE    {$userIDColumn} = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$userID]);

        $data = [];
        while ($row = $statement->fetchArray()) {
            $data[] = $row;
        }

        return $data;
    }
}
