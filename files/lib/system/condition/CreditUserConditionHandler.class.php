<?php
namespace cash\system\condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;

/**
 * Handles user conditions.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CreditUserConditionHandler extends SingletonFactory {
	/**
	 * list of grouped condition object types
	 */
	protected $groupedObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function getGroupedObjectTypes() {
		return $this->groupedObjectTypes;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.uz.cash.condition.credit.user');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
				$this->groupedObjectTypes[$objectType->conditiongroup] = [];
			}
			
			$this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
	}
}
