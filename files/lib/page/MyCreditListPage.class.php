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

use cash\data\cash\credit\user\AccessibleUserCashCreditList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the user credits page.
 */
class MyCreditListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage', 'user.cash.isPayer'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessibleUserCashCreditList::class;

    /**
     * @inheritDoc
     */
    public $templateName = 'myCreditList';

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
    public $validSortFields = ['userCreditID', 'status', 'amount', 'currency', 'time', 'origSubject'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = CASH_ITEMS_PER_PAGE;

    /**
     * user's balance
     */
    public $userBalance = [];

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

        if (!empty(WCF::getUser()->cashBalance)) {
            $this->userBalance = \unserialize(WCF::getUser()->cashBalance);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        // leave ufn
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'currency' => $this->currency,
            'status' => $this->status,
            'subject' => $this->subject,
            'userBalance' => $this->userBalance,
        ]);
    }
}
