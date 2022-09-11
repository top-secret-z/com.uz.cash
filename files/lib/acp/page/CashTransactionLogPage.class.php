<?php
namespace cash\acp\page;
use cash\data\cash\transaction\log\CashTransactionLog;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows transaction details.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashTransactionLogPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'cash.acp.menu.link.cash.transaction.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.cash.canManage'];
	
	/**
	 * log data
	 */
	public $logID = 0;
	public $log = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->logID = intval($_REQUEST['id']);
		$this->log = new CashTransactionLog($this->logID);
		if (!$this->log->logID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'logID' => $this->logID,
				'log' => $this->log
		]);
	}
}
