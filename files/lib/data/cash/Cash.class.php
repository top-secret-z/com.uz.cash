<?php
namespace cash\data\cash;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a cash entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class Cash extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'cash';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'cashID';
	
	/**
	 * Get posting cash by id
	 */
	public static function getPostingCashById($id) {
		$sql = "SELECT	*
				FROM	cash".WCF_N."_cash
				WHERE	type LIKE ? AND typeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['posting', $id]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new Cash(null, $row);
	}
	
	/**
	 * Get cash entry for claims for user
	 */
	public static function getCash($type, $typeID, $userID) {
		$sql = "SELECT	*
				FROM	cash".WCF_N."_cash
				WHERE	type LIKE ? AND typeID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$type, $typeID, $userID]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new Cash(null, $row);
	}
}
