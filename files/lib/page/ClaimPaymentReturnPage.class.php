<?php
namespace cash\page;
use wcf\page\AbstractPage;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the claim payment return message.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class ClaimPaymentReturnPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'redirect';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'message' => WCF::getLanguage()->getDynamicVariable('cash.claim.user.pay.returnMessage'),
			'wait' => 60,
			'url' => LinkHandler::getInstance()->getLink('MyClaimList', ['application' => 'cash'])
		]);
	}
}
