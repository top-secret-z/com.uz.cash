<?php
namespace cash\acp\form;
use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Shows the Cash category add form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'cash.acp.menu.link.cash.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.cash.category';
}
