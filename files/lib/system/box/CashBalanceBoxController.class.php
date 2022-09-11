<?php
namespace cash\system\box;
use cash\system\cache\builder\BalanceCacheBuilder;
use wcf\system\box\AbstractBoxController;
use wcf\system\WCF;

/**
 * Cash balance box controller.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashBalanceBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// module
		if (!MODULE_CASH) return;
		
		// permissions
		if (!WCF::getSession()->getPermission('user.cash.canSeeBalance') && !WCF::getSession()->getPermission('user.cash.canManage')) {
			return;
		}
		
		WCF::getTPL()->assign([
				'balance' => BalanceCacheBuilder::getInstance()->getData()
		]);
		
		$this->content = WCF::getTPL()->fetch('boxCashBalance', 'cash');
	}
}
