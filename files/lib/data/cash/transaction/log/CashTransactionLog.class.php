<?php
namespace cash\data\cash\transaction\log;
use cash\data\cash\claim\CashClaim;
use cash\data\cash\claim\user\UserCashClaim;
use wcf\data\user\User;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;

/**
 * Represents a cash transaction log entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashTransactionLog extends DatabaseObject {
	/**
	 * database
	 */
	protected static $databaseTableName = 'cash_transaction_log';
	protected static $databaseTableIndexName = 'logID';
	
	/**
	 * data
	 */
	protected $user = null;
	protected $userClaim = null;
	protected $claim = null;
	/**
	 * Returns the payment method of this transaction.
	 */
	public function getPaymentMethodName() {
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->paymentMethodObjectTypeID);
		return $objectType->objectType;
	}
	
	/**
	 * Returns transaction details.
	 */
	public function getTransactionDetails() {
		return unserialize($this->transactionDetails);
	}
	
	/**
	 * Returns the user of this transaction.
	 */
	public function getUser() {
		if ($this->user === null) {
			$this->user = new User($this->userID);
		}
		
		return $this->user;
	}
	
	/**
	 * Returns the user claim subject of this transaction.
	 */
	public function getUserClaimSubject() {
		if ($this->userClaim === null) {
			$this->userClaim = new UserCashClaim($this->userClaimID);
		}
		if (!empty($this->userClaim->subject)) {
			return $this->userClaim->subject;
		}
		
		$this->claim = new CashClaim($this->userClaim->claimID);
		return $this->claim->subject;
	}
}
