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
namespace cash\acp\page;

use cash\data\cash\transaction\log\CashTransactionLog;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows transaction details.
 */
class CashTransactionLogPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'cash.acp.menu.link.cash.transaction.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.cash.canManage'];

    /**
     * log data
     */
    public $logID = 0;

    public $log;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->logID = \intval($_REQUEST['id']);
        }
        $this->log = new CashTransactionLog($this->logID);
        if (!$this->log->logID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'logID' => $this->logID,
            'log' => $this->log,
        ]);
    }
}
