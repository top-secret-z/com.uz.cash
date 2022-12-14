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
namespace cash\form;

use cash\data\cash\claim\CashClaimAction;
use cash\system\condition\UserConditionHandler;
use DateTime;
use DateTimeZone;
use Exception;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\form\MessageForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the claim add form.
 */
class ClaimAddForm extends MessageForm
{
    /**
     * @inheritDoc
     */
    public $attachmentObjectType = 'com.uz.cash.claim';

    /**
     * @inheritDoc
     */
    public $messageObjectType = 'com.uz.cash.claim';

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage'];

    /**
     * category related
     */
    public $categoryID = 0;

    public $categoryList;

    public $categoryWarning = 1;

    /**
     * basic data
     */
    public $isDisabled = 0;

    public $amount = 0.0;

    public $availableCurrencies = [];

    public $currency = '';

    public $currencyWarning = 1;

    public $availablePaymentMethods = [];

    public $excludedPaymentMethods = [];

    /**
     * execution data
     */
    public $frequency = 'once';

    public $executions = 1;

    public $executionTime = '';

    public $executionDateTime;

    public $timezone = '';

    public $timezoneObj;

    /**
     * user data
     */
    public $users = '';

    public $userIDs = [];

    public $userConditions = [];

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['categoryID'])) {
            $this->categoryID = \intval($_POST['categoryID']);
        }
        if (isset($_POST['users'])) {
            $this->users = StringUtil::trim($_POST['users']);
        }
        if (isset($_POST['executionTime'])) {
            $this->executionTime = $_POST['executionTime'];
        }
        if (isset($_POST['timezone'])) {
            $this->timezone = $_POST['timezone'];
        }
        if (isset($_POST['frequency'])) {
            $this->frequency = $_POST['frequency'];
        }
        if (isset($_POST['executions'])) {
            $this->executions = \intval($_POST['executions']);
        }

        if (isset($_POST['amount'])) {
            $this->amount = StringUtil::trim($_POST['amount']);
            $this->amount = \str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $this->amount);
            if (WCF::getLanguage()->get('wcf.global.decimalPoint') != '.') {
                $this->amount = \str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $this->amount);
            }
            $this->amount = \floatval($this->amount);
        }
        if (isset($_POST['currency'])) {
            $this->currency = $_POST['currency'];
        }
        if (isset($_POST['excludedPaymentMethods'])) {
            $this->excludedPaymentMethods = \array_keys(ArrayUtil::trim($_POST['excludedPaymentMethods']));
        }

        // time zone
        try {
            $this->timezoneObj = new DateTimeZone($this->timezone);
        } catch (Exception $e) {
            $this->timezoneObj = WCF::getUser()->getTimeZone();
        }

        // create date time objects
        $this->executionDateTime = DateTime::createFromFormat('Y-m-d\TH:i:s', $this->executionTime, $this->timezoneObj);
        if ($this->executionDateTime !== false) {
            $this->executionTime = $this->executionDateTime->format('c');
        }

        // read conditions
        foreach ($this->userConditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->readFormParameters();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // categories
        $this->categoryNodeTree = new CategoryNodeTree('com.uz.cash.category', 0, false);
        foreach ($this->categoryNodeTree->getIterator() as $category) {
            if (!$category->isDisabled) {
                $this->categoryWarning = 0;
                break;
            }
        }

        // currencies and payment methods
        $this->availableCurrencies = \explode("\n", StringUtil::unifyNewlines(StringUtil::trim(CASH_CURRENCIES)));
        if (!empty($this->availableCurrencies[0])) {
            $this->currencyWarning = 0;
        }

        $temp = \explode(',', AVAILABLE_PAYMENT_METHODS);
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.payment.method');
        foreach ($objectTypes as $objectType) {
            if (\in_array($objectType->objectType, $temp)) {
                $this->availablePaymentMethods[$objectType->objectType] = WCF::getLanguage()->get('wcf.payment.' . $objectType->objectType);
            }
        }
        $this->availablePaymentMethods['cash.payment.method.transfer'] = WCF::getLanguage()->get('cash.payment.method.transfer');

        // conditions
        $this->userConditions = UserConditionHandler::getInstance()->getGroupedObjectTypes();

        // get available time zones
        foreach (DateUtil::getAvailableTimezones() as $timezone) {
            $this->availableTimezones[$timezone] = WCF::getLanguage()->get('wcf.date.timezone.' . \str_replace('/', '.', \strtolower($timezone)));
        }

        if (empty($_POST)) {
            // default time zone
            $this->timezone = WCF::getUser()->getTimeZone()->getName();
            $this->timezoneObj = WCF::getUser()->getTimeZone();

            // set default execution time
            $d = DateUtil::getDateTimeByTimestamp(TIME_NOW + 3600);
            $d->setTimezone($this->timezoneObj);
            $this->executionTime = $d->format('c');
        }

        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'categoryNodeList' => $this->categoryNodeTree->getIterator(),
            'categoryID' => $this->categoryID,
            'categoryWarning' => $this->categoryWarning,
            'availableTimezones' => $this->availableTimezones,
            'executionTime' => $this->executionTime,
            'timezone' => $this->timezone,

            'isDisabled' => $this->isDisabled,
            'amount' => $this->amount,
            'availableCurrencies' => $this->availableCurrencies,
            'currency' => $this->currency,
            'currencyWarning' => $this->currencyWarning,
            'availablePaymentMethods' => $this->availablePaymentMethods,
            'excludedPaymentMethods' => $this->excludedPaymentMethods,
            'frequency' => $this->frequency,
            'executions' => $this->executions,
            'executionTime' => $this->executionTime,
            'executionDateTime' => $this->executionDateTime,
            'timezone' => $this->timezone,
            'timezoneObj' => $this->timezoneObj,

            'userConditions' => $this->userConditions,
            'users' => $this->users,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (!empty($optionHandlerErrors)) {
            throw new UserInputException('options', $optionHandlerErrors);
        }

        // category
        if (empty($this->categoryID)) {
            throw new UserInputException('categoryID');
        }
        $category = new Category($this->categoryID);
        if (!$category->categoryID) {
            throw new UserInputException('categoryID', 'invalid');
        }

        // amount and currency
        if (!$this->amount) {
            throw new UserInputException('amount');
        }
        if ($this->amount < 0.01) {
            throw new UserInputException('amount', 'invalid');
        }
        if (empty($this->currency)) {
            throw new UserInputException('currency');
        }
        if (!\in_array($this->currency, $this->availableCurrencies)) {
            throw new UserInputException('currency', 'invalid');
        }

        // execution time
        if ($this->executionDateTime === false || $this->executionDateTime->getTimestamp() < 0 || $this->executionDateTime->getTimestamp() > 2147483647) {
            throw new UserInputException('executionTime', 'invalid');
        }

        // users
        $error = [];
        $userList = UserProfile::getUserProfilesByUsername(ArrayUtil::trim(\explode(',', $this->users)));

        foreach ($userList as $key => $user) {
            try {
                if ($user === null) {
                    throw new UserInputException('users', 'notFound');
                }

                // no error
                $this->userIDs[$user->userID] = $user->userID;
            } catch (UserInputException $e) {
                $error[] = ['type' => $e->getType(), 'username' => $key];
            }
        }
        if (!empty($error)) {
            throw new UserInputException('users', $error);
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // save claim
        $executionTime = $this->executionDateTime->getTimestamp();
        $data = \array_merge($this->additionalFields, [
            'isDisabled' => $this->isDisabled,
            'subject' => $this->subject,
            'categoryID' => $this->categoryID,
            'time' => TIME_NOW,
            'userID' => WCF::getUser()->userID,
            'username' => WCF::getUser()->username,
            'users' => \serialize($this->userIDs),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'excludedPaymentMethods' => \serialize($this->excludedPaymentMethods),
            'frequency' => $this->frequency,
            'executions' => $this->executions,
            'executionTime' => $executionTime,
            'nextExecution' => $executionTime,
            'timezone' => $this->timezoneObj->getName(),
        ]);

        $claimData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
        ];

        $this->objectAction = new CashClaimAction([], 'create', $claimData);
        $claim = $this->objectAction->executeAction()['returnValues'];

        // transform conditions and save
        $conditions = [];
        foreach ($this->userConditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }
        ConditionHandler::getInstance()->createConditions($claim->claimID, $conditions);

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ClaimList', ['application' => 'cash']));

        exit;
    }
}
