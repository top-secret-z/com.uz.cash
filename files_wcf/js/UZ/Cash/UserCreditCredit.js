/**
 * Give a user credit.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Ui/Confirmation"], function (require, exports, tslib_1, Ajax, Language, UiNotification, UiConfirmation) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	UiNotification = tslib_1.__importStar(UiNotification);
	UiConfirmation = tslib_1.__importStar(UiConfirmation);
	
	class UZCashUserCreditCredit {
		constructor() {
			var buttons = document.querySelectorAll('.jsCreditButton');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener("click", (ev) => this._click(ev));
			}
		}
		
		_click(event) {
			event.preventDefault();
			
			var objectID = event.currentTarget.dataset.objectId;
			
			UiConfirmation.show({
				confirm: function() {
					Ajax.apiOnce({
						data: {
							actionName:	'creditCredit',
							className:	'cash\\data\\cash\\credit\\user\\UserCashCreditAction',
							parameters:	{
								objectID: objectID
							}
						},
						success: function(data) {
							UiNotification.show();
							window.location.reload();
						}
					});
				},
				message: Language.get('cash.credit.user.credit.confirm')
			});
		}
	}
	
	let uZCashUserCreditCredit;
	function init() {
		if (!uZCashUserCreditCredit) {
			uZCashUserCreditCredit = new UZCashUserCreditCredit();
		}
	}
	exports.init = init;
});
