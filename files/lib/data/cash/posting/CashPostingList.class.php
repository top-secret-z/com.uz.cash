<?php
namespace cash\data\cash\posting;
use wcf\data\category\Category;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of cash postings.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashPostingList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CashPosting::class;
	
	/**
	 * Returns a list of available currencies.
	 */
	public function getAvailableCurrencies() {
		$currencies = [];
		$sql = "SELECT	DISTINCT currency
				FROM	cash".WCF_N."_cash_posting";
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
				FROM	cash".WCF_N."_cash_posting";
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
	 * Returns a list of available types.
	 */
	public function getAvailableTypes() {
		$types = [];
		$sql = "SELECT	DISTINCT type
				FROM	cash".WCF_N."_cash_posting";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			if ($row['type']) {
				$types[$row['type']] = WCF::getLanguage()->get('cash.posting.add.type.' . $row['type']);
			}
		}
		ksort($types);
		
		return $types;
	}
}
