<?php
namespace cash\data\cash;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of cash entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Cash::class;
	
	/**
	 * Returns a list of available types.
	 */
	public function getAvailableTypes() {
		$types = [];
		$sql = "SELECT	DISTINCT type
				FROM	cash".WCF_N."_cash";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['type']) {
				$types[$row['type']] = WCF::getLanguage()->get('cash.cash.type.' . $row['type']);
			}
		}
		ksort($types);
		
		return $types;
	}
	
	/**
	 * Returns a list of available currencies.
	 */
	public function getAvailableCurrencies() {
		$currencies = [];
		$sql = "SELECT	DISTINCT currency
				FROM	cash".WCF_N."_cash";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['currency']) {
				$currencies[$row['currency']] = $row['currency'];
			}
		}
		ksort($currencies);
		
		return $currencies;
	}
}
