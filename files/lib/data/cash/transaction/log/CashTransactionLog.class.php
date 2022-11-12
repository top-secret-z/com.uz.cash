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
namespace cash\data\cash\transaction\log;

use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaim;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;

/**
 * Represents a cash transaction log entry.
 */
class CashTransactionLog extends DatabaseObject
{
    /**
     * database
     */
    protected static $databaseTableName = 'cash_transaction_log';

    protected static $databaseTableIndexName = 'logID';

    /**
     * data
     */
    protected $user;

    protected $userClaim;

    protected $claim;

    /**
     * Returns the payment method of this transaction.
     */
    public function getPaymentMethodName()
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->paymentMethodObjectTypeID);

        return $objectType->objectType;
    }

    /**
     * Returns transaction details.
     */
    public function getTransactionDetails()
    {
        return \unserialize($this->transactionDetails);
    }

    /**
     * Returns the user of this transaction.
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = new User($this->userID);
        }

        return $this->user;
    }

    /**
     * Returns the user claim subject of this transaction.
     */
    public function getUserClaimSubject()
    {
        if ($this->userClaim === null) {
            $this->userClaim = new UserCashClaim($this->userClaimID);
        }
        if (!empty($this->userClaim->subject)) {
            return $this->userClaim->subject;
        }

        $this->claim = new CashClaim($this->userClaim->claimID);

        return $this->claim->subject;
    }
}
