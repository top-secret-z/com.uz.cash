<?php
namespace cash\system\page\handler;
use cash\data\cash\claim\user\UserCashClaim;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * Menu page handler for user claims manage page
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class MyClaimListPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (WCF::getSession()->getPermission('user.cash.isPayer')) return true;
		if (WCF::getSession()->getPermission('user.cash.canManage')) return true;
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return UserCashClaim::getOpenClaims();
	}
}