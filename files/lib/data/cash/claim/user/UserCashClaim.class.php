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
namespace cash\data\cash\claim\user;

use cash\data\cash\claim\CashClaim;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a user claim.
 */
class UserCashClaim extends DatabaseObject implements IRouteController
{
    /**
     * claim
     */
    protected $claim;

    /**
     * number of open claims
     */
    protected static $openClaims;

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'cash_claim_user';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'userClaimID';

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $claim = new CashClaim($this->claimID);

        return $claim->subject;
    }

    /**
     * Returns the related claim object.
     */
    public function getClaim()
    {
        if ($this->claim === null) {
            $this->claim = new CashClaim($this->claimID);
        }

        return $this->claim;
    }

    /**
     * Returns the number of open claims.
     */
    public static function getOpenClaims()
    {
        if (self::$openClaims === null) {
            self::$openClaims = 0;

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField('cashOpenClaims');

                // cache does not exist or is outdated
                if ($data === null) {
                    $sql = "SELECT        COUNT(*)
                            FROM        cash" . WCF_N . "_cash_claim_user
                            WHERE         userID = ? AND status = ?";
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute([WCF::getUser()->userID, 1]);
                    self::$openClaims = $statement->fetchSingleColumn();

                    // update storage data
                    UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'cashOpenClaims', self::$openClaims);
                } else {
                    self::$openClaims = $data;
                }
            }
        }

        return self::$openClaims;
    }

    /**
     * Returns the objectID
     */
    public function getObjectID()
    {
        return $this->userClaimID;
    }
}
