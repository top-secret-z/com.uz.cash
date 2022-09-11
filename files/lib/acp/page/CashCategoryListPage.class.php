<?php
namespace cash\acp\page;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows the Cash category list.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashCategoryListPage extends AbstractCategoryListPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'cash.acp.menu.link.cash.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.cash.category';
}
