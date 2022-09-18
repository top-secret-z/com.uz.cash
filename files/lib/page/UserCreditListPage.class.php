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
namespace cash\page;

use cash\data\cash\credit\user\ViewableUserCashCreditList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user credits manage page.
 */
class UserCreditListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = ViewableUserCashCreditList::class;

    /**
     * @inheritDoc
     */
    public $templateName = 'userCreditList';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'userCreditID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['userCreditID', 'amount', 'currency', 'username', 'time', 'origSubject'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = CASH_ITEMS_PER_PAGE;

    /**
     * filter
     */
    public $availableCurrencies = [];

    public $currency = '';

    public $availableStati = [];

    public $status = -1;

    public $subject = '';

    public $username = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['currency'])) {
            $this->currency = $_REQUEST['currency'];
        }
        $this->status = -1;
        if (isset($_REQUEST['status']) && $_REQUEST['status'] >= 0) {
            $this->status = $_REQUEST['status'];
        }
        if (!empty($_REQUEST['subject'])) {
            $this->subject = StringUtil::trim($_REQUEST['subject']);
        }
        if (!empty($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        // get data
        $this->availableCurrencies = $this->objectList->getAvailableCurrencies();
        $this->availableStati = $this->objectList->getAvailableStati();

        // filter
        if (!empty($this->currency)) {
            $this->objectList->getConditionBuilder()->add('cash_credit_user.currency LIKE ?', [$this->currency]);
        }
        if ($this->status >= 0) {
            $this->objectList->getConditionBuilder()->add('cash_credit_user.status = ?', [$this->status]);
        }
        if (!empty($this->subject)) {
            $this->objectList->getConditionBuilder()->add('(cash_credit_user.subject LIKE ? OR cash_credit_user.creditID IN (SELECT creditID FROM cash' . WCF_N . '_cash_credit WHERE subject LIKE ?))', ['%' . $this->subject . '%', '%' . $this->subject . '%']);
        }
        if (!empty($this->username)) {
            $user = User::getUserByUsername($this->username);
            if ($user->userID) {
                $this->objectList->getConditionBuilder()->add('cash_credit_user.userID = ?', [$user->userID]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'availableCurrencies' => $this->availableCurrencies,
            'availableStati' => $this->availableStati,
            'currency' => $this->currency,
            'status' => $this->status,
            'subject' => $this->subject,
            'username' => $this->username,
        ]);
    }
}
