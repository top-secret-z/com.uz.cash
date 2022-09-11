<?php
namespace cash\data\cash\credit;
use wcf\data\category\Category;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of cash credits.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCreditList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CashCredit::class;
	
	/**
	 * Returns a list of available currencies.
	 */
	public function getAvailableCurrencies() {
		$currencies = [];
		$sql = "SELECT	DISTINCT currency
				FROM	cash".WCF_N."_cash_credit";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['currency']) {
				$currencies[$row['currency']] = $row['currency'];
			}
		}
		ksort($currencies);
		
		return $currencies;
	}
	
	/**
	 * Returns a list of available categories.
	 */
	public function getAvailableCategories() {
		$categories = [];
		$sql = "SELECT	DISTINCT categoryID
				FROM	cash".WCF_N."_cash_credit";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['categoryID'] !== null) {
				$category = new Category($row['categoryID']);
				if (!$category->categoryID) continue;
				
				$categories[$row['categoryID']] = WCF::getLanguage()->get($category->title);
			}
		}
		ksort($categories);
		
		return $categories;
	}
	
	/**
	 * Returns a list of available frequencies.
	 */
	public function getAvailableFrequencies() {
		$frequencies = [];
		$sql = "SELECT	DISTINCT frequency
				FROM	cash".WCF_N."_cash_credit";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['frequency']) {
				$frequencies[$row['frequency']] = WCF::getLanguage()->get('cash.credit.add.frequency.' . $row['frequency']);
			}
		}
		ksort($frequencies);
		
		return $frequencies;
	}
}
