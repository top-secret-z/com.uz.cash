<?php
namespace cash\system\cache\builder;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the open claims.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class OpenClaimsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [];
		
		// add status adn exclude some actions
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('status = ?', [1]);
		
		$sql = "SELECT	amount, currency
				FROM	cash".WCF_N."_cash_claim_user
				".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['currency']])) {
				$data[$row['currency']] = $row['amount'];
			}
			else {
				$data[$row['currency']] += $row['amount'];
			}
		}
		return $data;
	}
}
	