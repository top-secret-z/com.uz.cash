<?php
namespace cash\acp\form;
use wcf\acp\form\AbstractCategoryEditForm;

/**
 * Shows the Cash category edit form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'cash.acp.menu.link.cash.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.cash.category';
}
