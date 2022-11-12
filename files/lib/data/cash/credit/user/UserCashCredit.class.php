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
namespace cash\data\cash\credit\user;

use cash\data\cash\credit\CashCredit;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;

/**
 * Represents a user credit.
 */
class UserCashCredit extends DatabaseObject implements IRouteController
{
    /**
     * credit
     */
    protected $credit;

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'cash_credit_user';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'userCreditID';

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $credit = new CashCredit($this->creditID);

        return $credit->subject;
    }

    /**
     * Returns the related credit object.
     */
    public function getCredit()
    {
        if ($this->credit === null) {
            $this->credit = new CashCredit($this->creditID);
        }

        return $this->credit;
    }

    /**
     * Returns the objectID
     */
    public function getObjectID()
    {
        return $this->userCreditID;
    }
}
