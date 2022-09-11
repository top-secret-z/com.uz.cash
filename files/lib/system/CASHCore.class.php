<?php
namespace cash\system;
use cash\page\MyAccountPage;
use wcf\system\application\AbstractApplication;

/**
 * This class extends the main WCF class by cash specific functions.
 *
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class CASHCore extends AbstractApplication {
	/**
	 * @inheritDoc
	 */
	protected $primaryController = MyAccountPage::class;
	
	/**
	 * Sets location data.
	 */
	public function setLocation() {
		// do nothing so far
	}
}
