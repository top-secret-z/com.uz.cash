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

use cash\data\cash\CashList;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaim;
use cash\data\cash\credit\CashCredit;
use cash\data\cash\credit\user\UserCashCredit;
use cash\data\cash\posting\CashPosting;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the cash export form.
 */
class CashExportForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.cash.canManage'];

    /**
     * data
     */
    public $categories;

    public $categoryIDs = [];

    public $availableCurrencies = [];

    public $selectedCurrencies = [];

    public $availableContents = [];

    public $selectedContents = [];

    public $startDate = '';

    public $endDate = '';

    public $openClaims = 0;

    /**
     * separator for the exported data and enclosure
     */
    public $separator = ',';

    public $textSeparator = '"';

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['openClaims'])) {
            $this->openClaims = \intval($_POST['openClaims']);
        }
        if (isset($_POST['startDate'])) {
            $this->startDate = $_POST['startDate'];
        }
        if (isset($_POST['endDate'])) {
            $this->endDate = $_POST['endDate'];
        }

        if (isset($_POST['selectedCurrencies'])) {
            $this->selectedCurrencies = \array_keys(ArrayUtil::trim($_POST['selectedCurrencies']));
        }
        if (isset($_POST['selectedContents'])) {
            $this->selectedContents = \array_keys(ArrayUtil::trim($_POST['selectedContents']));
        }
        if (isset($_POST['categoryIDs'])) {
            $this->categoryIDs = ArrayUtil::toIntegerArray($_POST['categoryIDs']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get categories
        $this->categories = (new CategoryNodeTree('com.uz.cash.category'))->getIterator();
        $this->categories->setMaxDepth(0);

        // get availableCurrencies
        $currencies = \explode("\n", StringUtil::unifyNewlines(StringUtil::trim(CASH_CURRENCIES)));
        if (\count($currencies)) {
            foreach ($currencies as $currency) {
                $this->availableCurrencies[$currency] = $currency;
            }
        }

        // get available contents
        $this->availableContents['claim'] = WCF::getLanguage()->get('cash.cash.type.claim');
        $this->availableContents['credit'] = WCF::getLanguage()->get('cash.cash.type.credit');
        $this->availableContents['posting'] = WCF::getLanguage()->get('cash.cash.type.posting');
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryList' => $this->categories,
            'categoryIDs' => $this->categoryIDs,
            'availableCurrencies' => $this->availableCurrencies,
            'selectedCurrencies' => $this->selectedCurrencies,
            'availableContents' => $this->availableContents,
            'selectedContents' => $this->selectedContents,
            'endDate' => $this->endDate,
            'startDate' => $this->startDate,
            'openClaims' => $this->openClaims,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // contents
        if (empty($this->selectedContents)) {
            throw new UserInputException('contents', 'empty');
        }

        // currencies
        if (empty($this->selectedCurrencies)) {
            throw new UserInputException('currencies', 'empty');
        }

        // categories
        if (empty($this->categoryIDs)) {
            throw new UserInputException('categoryIDs', 'empty');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $cashList = new CashList();

        // times
        if (!empty($this->startDate)) {
            $timestamp = \strtotime($this->startDate) - 1;
            $cashList->getConditionBuilder()->add('time > ?', [$timestamp]);
        }
        if (!empty($this->endDate)) {
            $timestamp = \strtotime($this->endDate) + 86399;
            $cashList->getConditionBuilder()->add('time < ?', [$timestamp]);
        }

        if ($this->openClaims) {
            $cashList->getConditionBuilder()->add('type <> ?', ['claimSent']);
        }

        // currencies, must
        $cashList->getConditionBuilder()->add('currency IN (?)', [$this->selectedCurrencies]);

        // contents
        $includePostings = $includeClaims = $includeCredits = 1;
        if (!\in_array('posting', $this->selectedContents)) {
            $includePostings = 0;
            $cashList->getConditionBuilder()->add('type NOT LIKE ?', ['posting']);
        }
        if (!\in_array('claim', $this->selectedContents)) {
            $includeClaims = 0;
            $cashList->getConditionBuilder()->add('type NOT LIKE ?', ['claim%']);
        }
        if (!\in_array('credit', $this->selectedContents)) {
            $includeCredits = 0;
            $cashList->getConditionBuilder()->add('type NOT LIKE ?', ['credit%']);
        }

        // get data
        $cashList->readObjects();
        $cashs = $cashList->getObjects();

        // export
        $language = WCF::getLanguage();

        \header('Content-Type: text/csv; charset=UTF-8');
        \header('Content-Disposition: attachment; filename=postings.csv');
        echo $this->textSeparator . $language->get('wcf.global.objectID') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.category') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.date') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.time') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.username') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.type') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.amount') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.currency') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('cash.cash.comment') . $this->textSeparator . $this->separator;
        echo "\r\n";

        $postingCategory = $claimCategory = $creditCategory = $categoryNames = [];

        foreach ($cashs as $cash) {
            // check open claims
            if ($this->openClaims && $cash->type == 'claimSent') {
                continue;
            }

            // categories, not very efficient. But ...
            if ($includePostings && $cash->type == 'posting') {
                if (!isset($postingCategory[$cash->typeID])) {
                    $posting = new CashPosting($cash->typeID);
                    if (!$posting->postingID) {
                        continue;
                    }
                    $postingCategory[$cash->typeID] = $posting->categoryID;
                }
                if (!\in_array($postingCategory[$cash->typeID], $this->categoryIDs)) {
                    continue;
                }

                $categoryID = $posting->categoryID;
            }

            if ($includeClaims && \strrpos($cash->type, 'claim') !== false) {
                $userClaim = new UserCashClaim($cash->typeID);
                if (!isset($claimCategory[$userClaim->claimID])) {
                    $claim = new CashClaim($userClaim->claimID);
                    if (!$claim->claimID) {
                        continue;
                    }
                    $claimCategory[$userClaim->claimID] = $claim->categoryID;
                }

                if (!\in_array($claimCategory[$userClaim->claimID], $this->categoryIDs)) {
                    continue;
                }

                $categoryID = $claim->categoryID;
            }

            if ($includeCredits && \strrpos($cash->type, 'credit') !== false) {
                $userCredit = new UserCashCredit($cash->typeID);
                if (!isset($creditCategory[$userCredit->creditID])) {
                    $credit = new CashCredit($userCredit->creditID);
                    if (!$credit->creditID) {
                        continue;
                    }
                    $creditCategory[$userCredit->creditID] = $credit->categoryID;
                }
                if (!\in_array($creditCategory[$userCredit->creditID], $this->categoryIDs)) {
                    continue;
                }

                $categoryID = $credit->categoryID;
            }

            if (!isset($categoryNames[$categoryID])) {
                $category = new Category($categoryID);
                if (!$category->categoryID) {
                    $categoryNames[$categoryID] = $language->get('cash.export.noCategory');
                } else {
                    $categoryNames[$categoryID] = $language->get($category->title);
                }
            }

            echo $this->textSeparator . $cash->cashID . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $categoryNames[$categoryID] . $this->textSeparator . $this->separator;
            echo $this->textSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($cash->time), DateUtil::DATE_FORMAT) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($cash->time), DateUtil::TIME_FORMAT) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $cash->username . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $language->get('cash.cash.type.' . $cash->type) . $this->textSeparator . $this->separator;
            $amount = \number_format(\round($cash->amount, 2), 2, WCF::getLanguage()->get('wcf.global.decimalPoint'), WCF::getLanguage()->get('wcf.global.thousandsSeparator'));
            echo $this->textSeparator . $amount . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $cash->currency . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $cash->comment . $this->textSeparator . $this->separator;
            echo "\r\n";
        }

        // call saved event
        $this->saved();

        exit;
    }
}
