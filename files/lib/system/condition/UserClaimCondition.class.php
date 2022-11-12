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
namespace cash\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\AbstractSingleFieldCondition;
use wcf\system\condition\IContentCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\condition\IUserCondition;
use wcf\system\condition\TObjectListUserCondition;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Condition implementation for the absence of a user.
 */
class UserClaimCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectListCondition, IUserCondition
{
    use TObjectListUserCondition;

    /**
     * @inheritDoc
     */
    protected $label = 'cash.claim.condition';

    /**
     * true if the user has open claims or not
     */
    protected $userHasClaims = 0;

    protected $userHasNotClaims = 0;

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof UserList)) {
            throw new ParentClassException(\get_class($objectList), UserList::class);
        }

        if (isset($conditionData['userHasClaims'])) {
            $objectList->getConditionBuilder()->add('user_table.userID IN (SELECT userID FROM cash' . WCF_N . '_cash_claim_user WHERE status <> ?)', [2]);
        }
        if (isset($conditionData['userHasNotClaims'])) {
            $objectList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM cash' . WCF_N . '_cash_claim_user WHERE status <> ?)', [2]);
        }
    }

    /**
     * @inheritDoc
     */
    public function checkUser(Condition $condition, User $user)
    {
        $hasClaims = false;

        if (UserStorageHandler::getInstance()->getField('cashOpenClaims', $user->userID)) {
            $hasClaims = true;
        }

        if ($condition->userHasClaims !== null && !$hasClaims) {
            return false;
        }
        if ($condition->userHasNotClaims !== null && $hasClaims) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $data = [];

        if ($this->userHasClaims) {
            $data['userHasClaims'] = 1;
        }
        if ($this->userHasNotClaims) {
            $data['userHasNotClaims'] = 1;
        }

        if (!empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * Returns the "checked" attribute for an input element.
     */
    protected function getCheckedAttribute($propertyName)
    {
        if ($this->{$propertyName}) {
            return ' checked';
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getFieldElement()
    {
        $userHasNotClaims = WCF::getLanguage()->get('cash.claim.condition.hasNotClaims');
        $userHasClaims = WCF::getLanguage()->get('cash.claim.condition.hasClaims');

        return <<<HTML
<label><input type="checkbox" name="userHasClaims" value="1"{$this->getCheckedAttribute('userHasClaims')}> {$userHasClaims}</label>
<label><input type="checkbox" name="userHasNotClaims" value="1"{$this->getCheckedAttribute('userHasNotClaims')}> {$userHasNotClaims}</label>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['userHasClaims'])) {
            $this->userHasClaims = 1;
        }
        if (isset($_POST['userHasNotClaims'])) {
            $this->userHasNotClaims = 1;
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->userHasClaims = 0;
        $this->userHasNotClaims = 0;
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        if ($condition->userHasClaims !== null) {
            $this->userHasClaims = $condition->userHasClaims;
        }

        if ($condition->userHasNotClaims !== null) {
            $this->userHasNotClaims = $condition->userHasNotClaims;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->userHasClaims && $this->userHasNotClaims) {
            $this->errorMessage = 'cash.claim.condition.hasClaims.error.conflict';

            throw new UserInputException('userHasClaims', 'conflict');
        }
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
