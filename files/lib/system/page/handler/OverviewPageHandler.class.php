<?php
namespace cash\system\page\handler;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * Menu page handler for overview page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class OverviewPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (WCF::getSession()->getPermission('user.cash.canManage')) return true;
		if (WCF::getSession()->getPermission('user.cash.canSeeStatements')) return true;
		
		return false;
	}
}
