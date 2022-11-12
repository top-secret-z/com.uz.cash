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
namespace cash\data\cash\posting;

use wcf\data\category\Category;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of cash postings.
 */
class CashPostingList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = CashPosting::class;

    /**
     * Returns a list of available currencies.
     */
    public function getAvailableCurrencies()
    {
        $currencies = [];
        $sql = "SELECT    DISTINCT currency
                FROM    cash" . WCF_N . "_cash_posting";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());
        while ($row = $statement->fetchArray()) {
            if ($row['currency']) {
                $currencies[$row['currency']] = $row['currency'];
            }
        }
        \ksort($currencies);

        return $currencies;
    }

    /**
     * Returns a list of available categories.
     */
    public function getAvailableCategories()
    {
        $categories = [];
        $sql = "SELECT    DISTINCT categoryID
                FROM    cash" . WCF_N . "_cash_posting";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());
        while ($row = $statement->fetchArray()) {
            if ($row['categoryID'] !== null) {
                $category = new Category($row['categoryID']);
                if (!$category->categoryID) {
                    continue;
                }

                $categories[$row['categoryID']] = WCF::getLanguage()->get($category->title);
            }
        }
        \ksort($categories);

        return $categories;
    }

    /**
     * Returns a list of available types.
     */
    public function getAvailableTypes()
    {
        $types = [];
        $sql = "SELECT    DISTINCT type
                FROM    cash" . WCF_N . "_cash_posting";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());
        while ($row = $statement->fetchArray()) {
            if ($row['type']) {
                $types[$row['type']] = WCF::getLanguage()->get('cash.posting.add.type.' . $row['type']);
            }
        }
        \ksort($types);

        return $types;
    }
}
