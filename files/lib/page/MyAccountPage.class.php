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

use cash\data\cash\AccessibleCashList;
use cash\data\cash\claim\user\UserCashClaim;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the user's overview page.
 */
class MyAccountPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage', 'user.cash.isPayer', 'user.cash.canSeeStatements'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessibleCashList::class;

    /**
     * @inheritDoc
     */
    public $templateName = 'myAccount';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'cashID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['cashID', 'time', 'amount', 'currency', 'type', 'comment'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = CASH_ITEMS_PER_PAGE;

    /**
     * user's balance
     */
    public $userBalance = [];

    /**
     * balance and open claims
     */
    public $balance = [];

    public $claims = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty(WCF::getUser()->cashBalance)) {
            $this->userBalance = \unserialize(WCF::getUser()->cashBalance);
        }

        $this->balance = BalanceCacheBuilder::getInstance()->getData();
        $this->claims = OpenClaimsCacheBuilder::getInstance()->getData();
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'userBalance' => $this->userBalance,
            'balance' => $this->balance,
            'claims' => $this->claims,
            'hasClaims' => UserCashClaim::getOpenClaims(),
        ]);
    }
}
