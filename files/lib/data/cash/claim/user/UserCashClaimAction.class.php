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

use cash\data\cash\Cash;
use cash\data\cash\CashAction;
use cash\data\cash\claim\CashClaim;
use cash\system\cache\builder\BalanceCacheBuilder;
use cash\system\cache\builder\OpenClaimsCacheBuilder;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\payment\method\PaypalPaymentMethod;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes user claim-related actions.
 */
class UserCashClaimAction extends AbstractDatabaseObjectAction
{
    /**
     * data
     */
    public $claim;

    public $userClaim;

    public $user;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['user.cash.canManage'];

    protected $permissionsUpdate = ['user.cash.canManage'];

    /**
     * @inheritDoc
     */
    protected $className = UserCashClaimEditor::class;

    /**
     * @inheritDoc
     */
    public function delete()
    {
        foreach ($this->getObjects() as $userClaim) {
            $userClaim = $userClaim->getDecoratedObject();
            $claim = new CashClaim($userClaim->claimID);
            $user = new User($userClaim->userID);

            // log in cash
            $subject = $claim->claimID ? $claim->subject : 'deleted';
            $comment = empty($userClaim->subject) ? $subject : $userClaim->subject;
            $action = new CashAction([], 'create', [
                'data' => [
                    'amount' => -1 * $userClaim->amount,
                    'currency' => $userClaim->currency,
                    'userID' => $user->userID ? $user->userID : $userClaim->userID,
                    'username' => $user->userID ? $user->username : $userClaim->username,
                    'time' => TIME_NOW,
                    'comment' => $comment,
                    'type' => 'claimDeleted',
                    'typeID' => 0,
                    'isDeleted' => 1,
                ],
            ]);
            $action->executeAction();

            // update all releated cash entries
            $sql = "UPDATE    cash" . WCF_N . "_cash
                    SET     isDeleted = ?, typeID = ?
                    WHERE    typeID = ? AND type LIKE ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([1, 0, $userClaim->userClaimID, 'claim%']);

            // update user
            if ($user->userID) {
                $this->updateUser($user, -1 * $userClaim->amount, $userClaim->currency);

                // reset cache and user storage
                BalanceCacheBuilder::getInstance()->reset();
                OpenClaimsCacheBuilder::getInstance()->reset();

                UserStorageHandler::getInstance()->reset([$user->userID], 'cashOpenClaims');
            }
        }

        parent::delete();
    }

    /**
     * Validates the getClaimEditDialog action.
     * Only if status != 2
     */
    public function validateGetClaimEditDialog()
    {
        WCF::getSession()->checkPermissions(['user.cash.canManage']);
        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status != 1) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the userClaimEditDialog action.
     */
    public function getClaimEditDialog()
    {
        // get available availableCurrencies and subject
        $currencies = \explode("\n", StringUtil::unifyNewlines(StringUtil::trim(CASH_CURRENCIES)));

        // original subject if not modified
        if (empty($this->userClaim->subject)) {
            $subject = $this->claim->subject;
        } else {
            $subject = $this->userClaim->subject;
        }

        WCF::getTPL()->assign([
            'availableCurrencies' => $currencies,
            'amount' => -1 * $this->userClaim->amount,    // positive value in dialog
            'currency' => $this->userClaim->currency,
            'subject' => $subject,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('claimEditDialog', 'cash'),
        ];
    }

    /**
     * Validates the saveClaimEditDialog action.
     * Only reversed claims can be edited
     */
    public function validateSaveClaimEditDialog()
    {
        WCF::getSession()->checkPermissions(['user.cash.canManage']);
        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status != 1) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }

        $this->user = new User($this->userClaim->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the saveClaimEditDialog action.
     */
    public function saveClaimEditDialog()
    {
        // update user claim
        $userClaimEditor = new UserCashClaimEditor($this->userClaim);
        $userClaimEditor->update([
            'amount' => -1 * $this->parameters['amount'],
            'currency' => $this->parameters['currency'],
            'subject' => \strcmp($this->parameters['subject'], $this->claim->subject) ? $this->parameters['subject'] : '',
            'isChanged' => 1,
        ]);

        // log in cash with 2 entries
        $comment = empty($this->userClaim->subject) ? $this->claim->subject : $this->userClaim->subject;
        $action = new CashAction([], 'create', [
            'data' => [
                'amount' => -1 * $this->userClaim->amount,
                'currency' => $this->userClaim->currency,
                'userID' => $this->user->userID,
                'username' => $this->user->username,
                'time' => TIME_NOW,
                'comment' => $comment,
                'type' => 'claimChanged',
                'typeID' => $this->userClaim->userClaimID,
            ],
        ]);
        $action->executeAction();

        // update user and re-read
        $this->updateUser($this->user, -1 * $this->userClaim->amount, $this->userClaim->currency);
        $user = new User($this->user->userID);

        $action = new CashAction([], 'create', [
            'data' => [
                'amount' => -1 * $this->parameters['amount'],
                'currency' => $this->parameters['currency'],
                'userID' => $this->user->userID,
                'username' => $this->user->username,
                'time' => TIME_NOW,
                'comment' => $comment,
                'type' => 'claimChanged',
                'typeID' => $this->userClaim->userClaimID,
            ],
        ]);
        $action->executeAction();

        // update user
        $this->updateUser($user, -1 * $this->parameters['amount'], $this->parameters['currency']);

        OpenClaimsCacheBuilder::getInstance()->reset();
    }

    /**
     * Validates the payClaim action.
     * status must be == 1
     */
    public function validatePayClaim()
    {
        WCF::getSession()->checkPermissions(['user.cash.canManage']);

        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status == 2) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }

        $this->user = new User($this->userClaim->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the payClaim action.
     */
    public function payClaim()
    {
        // set claim to paid
        $userClaimEditor = new UserCashClaimEditor($this->userClaim);
        $userClaimEditor->update([
            'status' => 2,
            'isTransfer' => 0,
        ]);

        // log in cash
        $action = new CashAction([], 'create', [
            'data' => [
                'amount' => -1 * $this->userClaim->amount,
                'currency' => $this->userClaim->currency,
                'userID' => $this->user->userID,
                'username' => $this->user->username,
                'time' => TIME_NOW,
                'comment' => empty($this->userClaim->subject) ? $this->claim->subject : $this->userClaim->subject,
                'type' => 'claimPaid',
                'typeID' => $this->userClaim->userClaimID,
            ],
        ]);
        $action->executeAction();

        // update user
        $this->updateUser($this->user, -1 * $this->userClaim->amount, $this->userClaim->currency);

        // reset cache and user storage
        BalanceCacheBuilder::getInstance()->reset();
        OpenClaimsCacheBuilder::getInstance()->reset();

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'cashOpenClaims');
    }

    /**
     * Validates the unpayClaim action.
     * status must be 2
     */
    public function validateUnpayClaim()
    {
        WCF::getSession()->checkPermissions(['user.cash.canManage']);

        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status != 2) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }

        $this->user = new User($this->userClaim->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the unpayClaim action.
     */
    public function unpayClaim()
    {
        // set claim to unpaid
        $userClaimEditor = new UserCashClaimEditor($this->userClaim);
        $userClaimEditor->update([
            'status' => 1,
        ]);

        // log in cash
        $objectAction = new CashAction([], 'create', [
            'data' => [
                'amount' => $this->userClaim->amount,
                'currency' => $this->userClaim->currency,
                'userID' => $this->user->userID,
                'username' => $this->user->username,
                'time' => TIME_NOW,
                'comment' => empty($this->userClaim->subject) ? $this->claim->subject : $this->userClaim->subject,
                'type' => 'claimReversed',
                'typeID' => $this->userClaim->userClaimID,
            ],
        ]);
        $objectAction->executeAction();

        // update user
        $this->updateUser($this->user, $this->userClaim->amount, $this->userClaim->currency);

        // reset cache and user storage
        BalanceCacheBuilder::getInstance()->reset();
        OpenClaimsCacheBuilder::getInstance()->reset();

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'cashOpenClaims');
    }

    /**
     * Validates the getClaimPayDialog action.
     * status must be != 2
     */
    public function validateGetClaimPayDialog()
    {
        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status == 2) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }

        $this->user = new User($this->userClaim->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the getClaimPayDialog action.
     */
    public function getClaimPayDialog()
    {
        // get payment buttons
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.payment.type', 'com.uz.cash.payment.type.claim');
        $buttons = $paymentMethods = [];

        // get available and excluded payment methods
        $availablePaymentMethods = \explode(',', AVAILABLE_PAYMENT_METHODS);
        $availablePaymentMethods[] = 'cash.payment.method.transfer';
        $excludedPaymentMethods = \unserialize($this->claim->excludedPaymentMethods);

        // generally useable WSC payment methode
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.payment.method');
        foreach ($objectTypes as $objectType) {
            if (\in_array($objectType->objectType, $excludedPaymentMethods)) {
                continue;
            }

            if (\in_array($objectType->objectType, $availablePaymentMethods)) {
                $paymentMethods[] = $objectType->getProcessor();
            }
        }

        // check currencies and get buttons
        foreach ($paymentMethods as $paymentMethod) {
            if (!\in_array($this->userClaim->currency, $paymentMethod->getSupportedCurrencies())) {
                continue;
            }

            $comment = empty($this->userClaim->subject) ? $this->claim->subject : $this->userClaim->subject;
            $button = $paymentMethod->getPurchaseButton(\round(-1 * $this->userClaim->amount, 2), $this->userClaim->currency, $comment, $objectTypeID . ':' . $this->userClaim->userID . ':' . $this->userClaim->userClaimID, LinkHandler::getInstance()->getLink('ClaimPaymentReturn', ['application' => 'cash']), LinkHandler::getInstance()->getLink());

            // modify PayPal button
            if ($paymentMethod instanceof PaypalPaymentMethod) {
                $search = '<button class="small" type="submit">' . WCF::getLanguage()->get('wcf.payment.paypal.button.purchase') . '</button>';
                $replace = '<button class="small" type="submit">' . WCF::getLanguage()->get('cash.claim.user.pay.method.payPal') . '</button>';
                $button = \str_replace($search, $replace, $button);
            }

            $buttons[] = $button;
        }

        // check transfer
        $allowTransfer = true;
        if (\in_array('cash.payment.method.transfer', $excludedPaymentMethods)) {
            $allowTransfer = false;
        }

        WCF::getTPL()->assign([
            'allowTransfer' => $allowTransfer,
            'buttons' => $buttons,
            'userClaim' => $this->userClaim,
            'reference' => $this->userClaim->userClaimID . ' - ' . $this->user->username,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('claimPayDialog', 'cash'),
        ];
    }

    /**
     * Validates the balanceClaim action.
     */
    public function validateBalanceClaim()
    {
        WCF::getSession()->checkPermissions(['user.cash.isPayer']);

        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status == 2) {
            throw new IllegalLinkException();
        }

        $this->claim = new CashClaim($this->userClaim->claimID);
        if (!$this->claim->claimID) {
            throw new IllegalLinkException();
        }

        $this->user = new User($this->userClaim->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the balanceClaim action.
     */
    public function balanceClaim()
    {
        // preset data and get credit
        $userClaimEditor = new UserCashClaimEditor($this->userClaim);

        $balance = \unserialize($this->user->cashBalance);
        $credit = $balance[$this->userClaim->currency] - $this->userClaim->amount;

        if ($credit >= -1 * $this->userClaim->amount) {
            $credit = -1 * $this->userClaim->amount;

            $userClaimEditor->update([
                'status' => 2,
                'isTransfer' => 0,
            ]);
        } else {
            $userClaimEditor->update([
                'amount' => $this->userClaim->amount + $credit,
            ]);
        }
        // no user update since balance includes claims

        // log in cash
        $action = new CashAction([], 'create', [
            'data' => [
                'amount' => $credit,
                'currency' => $this->userClaim->currency,
                'userID' => $this->user->userID,
                'username' => $this->user->username,
                'time' => TIME_NOW,
                'comment' => empty($this->userClaim->subject) ? $this->claim->subject : $this->userClaim->subject,
                'type' => 'claimBalanced',
                'typeID' => $this->userClaim->userClaimID,
            ],
        ]);
        $action->executeAction();

        // reset cache and user storage
        BalanceCacheBuilder::getInstance()->reset();
        OpenClaimsCacheBuilder::getInstance()->reset();

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'cashOpenClaims');
    }

    /**
     * Validates the transferClaim action.
     */
    public function validateTransferClaim()
    {
        $this->userClaim = new UserCashClaim($this->parameters['objectID']);
        if (!$this->userClaim->userClaimID || $this->userClaim->status != 1) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the transferClaim action.
     */
    public function transferClaim()
    {
        $userClaimEditor = new UserCashClaimEditor($this->userClaim);
        $userClaimEditor->update([
            'isTransfer' => 1,
        ]);
    }

    /**
     * update user cash balance
     */
    public function updateUser($user, $amount, $currency)
    {
        $balance = \unserialize($user->cashBalance);
        if (isset($balance[$currency])) {
            $balance[$currency] += $amount;
        } else {
            $balance[$currency] = $amount;
        }
        $action = new UserAction([$user], 'update', [
            'data' => [
                'cashBalance' => \serialize($balance),
            ],
        ]);
        $action->executeAction();
    }
}
