<?php
namespace cash\system\page\handler;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TUserLookupPageHandler;
use wcf\system\page\handler\TUserOnlineLocationPageHandler;
use wcf\system\request\LinkHandler;

/**
 * Menu page handler for a user account page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class UserAccountPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TUserLookupPageHandler;
	use TUserOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return LinkHandler::getInstance()->getLink('Overview', [
				'application' => 'cash',
				'forceFrontend' => true,
				'object' => UserRuntimeCache::getInstance()->getObject($objectID)
		]);
	}
}
