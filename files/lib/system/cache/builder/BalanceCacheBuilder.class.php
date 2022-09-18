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
namespace cash\system\cache\builder;

use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the balance.
 */
class BalanceCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 600;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $data = [];

        // exclude some actions
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('type <> ?', ['claimSent']);
        $conditionBuilder->add('type <> ?', ['creditChanged']);
        $conditionBuilder->add('type <> ?', ['claimChanged']);
        $conditionBuilder->add('type <> ?', ['claimDeleted']);

        $sql = "SELECT    amount, currency
                FROM    cash" . WCF_N . "_cash
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        while ($row = $statement->fetchArray()) {
            if (!isset($data[$row['currency']])) {
                $data[$row['currency']] = $row['amount'];
            } else {
                $data[$row['currency']] += $row['amount'];
            }
        }

        return $data;
    }
}
