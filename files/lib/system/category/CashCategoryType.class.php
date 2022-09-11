<?php
namespace cash\system\category;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type for Cash.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;

	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'cash.category';

	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 2;

	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}

	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}

	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.cash.canManage');
	}
}
