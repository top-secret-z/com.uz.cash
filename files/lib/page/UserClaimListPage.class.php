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

use cash\data\cash\claim\user\ViewableUserCashClaimList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user claims manage page.
 */
class UserClaimListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = ViewableUserCashClaimList::class;

    /**
     * @inheritDoc
     */
    public $templateName = 'userClaimList';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'userClaimID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['userClaimID', 'status', 'amount', 'currency', 'username', 'time', 'origSubject'];

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

    public $availableTransfers = [];

    public $status = -1;

    public $isTransfer = -1;

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
        $this->isTransfer = -1;
        if (isset($_REQUEST['isTransfer']) && $_REQUEST['isTransfer'] >= 0) {
            $this->isTransfer = $_REQUEST['isTransfer'];
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

        $this->availableTransfers[1] = WCF::getLanguage()->get('cash.claim.user.transfer');
        $this->availableTransfers[0] = WCF::getLanguage()->get('cash.claim.user.transfer.not');

        // filter
        if (!empty($this->currency)) {
            $this->objectList->getConditionBuilder()->add('cash_claim_user.currency LIKE ?', [$this->currency]);
        }
        if ($this->status >= 0) {
            $this->objectList->getConditionBuilder()->add('cash_claim_user.status = ?', [$this->status]);
        }
        if ($this->isTransfer >= 0) {
            $this->objectList->getConditionBuilder()->add('cash_claim_user.isTransfer = ?', [$this->isTransfer]);
        }
        if (!empty($this->subject)) {
            $this->objectList->getConditionBuilder()->add('(cash_claim_user.subject LIKE ? OR cash_claim_user.claimID IN (SELECT claimID FROM cash' . WCF_N . '_cash_claim WHERE subject LIKE ?))', ['%' . $this->subject . '%', '%' . $this->subject . '%']);
        }
        if (!empty($this->username)) {
            $user = User::getUserByUsername($this->username);
            if ($user->userID) {
                $this->objectList->getConditionBuilder()->add('cash_claim_user.userID = ?', [$user->userID]);
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
            'availableTransfers' => $this->availableTransfers,
            'currency' => $this->currency,
            'status' => $this->status,
            'isTransfer' => $this->isTransfer,
            'subject' => $this->subject,
            'username' => $this->username,
        ]);
    }
}
