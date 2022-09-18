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

use wcf\system\WCF;

/**
 * Represents a list of user credits.
 */
class AccessibleUserCashCreditList extends UserCashCreditList
{
    /**
     * Creates a new list object.
     */
    public function __construct()
    {
        parent::__construct();

        // subject
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "cash_credit.subject AS origSubject";
        $this->sqlJoins .= " LEFT JOIN cash" . WCF_N . "_cash_credit cash_credit ON (cash_credit.creditID = cash_credit_user.creditID)";

        $this->getConditionBuilder()->add('cash_credit_user.userID = ?', [WCF::getUser()->userID]);
        $this->getConditionBuilder()->add('cash_credit_user.status > ?', [0]);
    }
}
