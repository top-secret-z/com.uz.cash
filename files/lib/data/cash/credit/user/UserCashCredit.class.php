<?php
namespace cash\data\cash\credit\user;
use cash\data\cash\credit\CashCredit;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;

/**
 * Represents a user credit.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashCredit extends DatabaseObject implements IRouteController {
	/**
	 * credit
	 */
	protected $credit = null;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'cash_credit_user';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'userCreditID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$credit = new CashCredit($this->creditID);
		return $credit->subject;
	}
	
	/**
	 * Returns the related credit object.
	 */
	public function getCredit() {
		if ($this->credit === null) {
			$this->credit = new CashCredit($this->creditID);
		}
		
		return $this->credit;
	}
	
	/**
	 * Returns the objectID
	 */
	public function getObjectID() {
		return $this->userCreditID;
	}
}
