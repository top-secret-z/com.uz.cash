<?php
namespace cash\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Exports user data iwa Gdpr.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CashGdprExportListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// add balance data in user and transactions
		$balance = unserialize($eventObj->user->cashBalance);
		$eventObj->data['cpm.uz.cash'] = [
				'cashBalance' => $balance,
				'cashTransactionLog' => $this->dumpTable('cash' . WCF_N . '_cash_transaction_log', 'userID', $eventObj->user->userID),
		];
	}
	
	/**
	 * dump table copied from action and modified
	 */
	protected function dumpTable($tableName, $userIDColumn, $userID) {
		$sql = "SELECT	*
				FROM	${tableName}
				WHERE	${userIDColumn} = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID]);
		
		$data = [];
		while ($row = $statement->fetchArray()) {
			$data[] = $row;
		}
		
		return $data;
	}
}
