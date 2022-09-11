<?php
namespace cash\data\cash;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes treasury-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CashEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.cash.canManage'];
	protected $permissionsCreate = ['admin.cash.canManage'];
	protected $permissionsDelete = ['admin.cash.canManage'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
