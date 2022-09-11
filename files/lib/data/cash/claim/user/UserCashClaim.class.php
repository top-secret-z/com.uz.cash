<?php
namespace cash\data\cash\claim\user;
use cash\data\cash\claim\CashClaim;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a user claim.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserCashClaim extends DatabaseObject implements IRouteController {
	/**
	 * claim
	 */
	protected $claim = null;
	
	/**
	 * number of open claims
	 */
	protected static $openClaims;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'cash_claim_user';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'userClaimID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$claim = new CashClaim($this->claimID);
		return $claim->subject;
	}
	
	/**
	 * Returns the related claim object.
	 */
	public function getClaim() {
		if ($this->claim === null) {
			$this->claim = new CashClaim($this->claimID);
		}
		
		return $this->claim;
	}
	
	/**
	 * Returns the number of open claims.
	 */
	public static function getOpenClaims() {
		if (self::$openClaims === null) {
			self::$openClaims = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('cashOpenClaims');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$sql = "SELECT		COUNT(*)
							FROM		cash".WCF_N."_cash_claim_user
							WHERE 		userID = ? AND status = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([WCF::getUser()->userID, 1]);
					self::$openClaims = $statement->fetchSingleColumn();
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'cashOpenClaims', self::$openClaims);
				}
				else {
					self::$openClaims = $data;
				}
			}
		}
		
		return self::$openClaims;
	}
	
	/**
	 * Returns the objectID
	 */
	public function getObjectID() {
		return $this->userClaimID;
	}
}
