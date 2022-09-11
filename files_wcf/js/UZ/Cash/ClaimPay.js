/**
 * Pay a user claim
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Ajax, Language, Dialog_1, UiNotification, Util_1) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	UiNotification = tslib_1.__importStar(UiNotification);
	Util_1 = tslib_1.__importDefault(Util_1);
	
	class UZClaimPay {
		constructor() {
			var buttons = document.querySelectorAll('.jsPayButton');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener("click", (ev) => this._showDialog(ev));
			}
			
			this._objectID = 0;
		}
		
		_showDialog(event) {
			event.preventDefault();
			
			this._objectID = event.currentTarget.dataset.objectId;
			
			Ajax.api(this, {
				actionName: 'getClaimPayDialog',
				parameters: {
					objectID: this._objectID
				}
			});
		}
		
		_submit(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName: 'transferClaim',
				parameters: {
					objectID: this._objectID
				}
			});
		}
		
		_cancel() {
			Dialog_1.default.close(this);
		}
		
		_ajaxSuccess(data) {
			switch (data.actionName) {
				case 'getClaimPayDialog':
					this._render(data);
					break;
				case 'transferClaim':
					Dialog_1.default.close(this);
					UiNotification.show(null, function () {
						window.location.reload();
					});
					
					break;
			}
		}
		
		_enableTransfer() {
			var submitButton = document.querySelector('.jsSubmitButton');
			var method = document.querySelector('.jsPaymentMethod');
			
			if (document.getElementById('transferEnable').checked) {
				if (method !== null) {
					Util_1.default.hide(method);
				}
				Util_1.default.show(submitButton);
			}
			else {
				Util_1.default.hide(submitButton);
				if (method !== null) {
					Util_1.default.show(method);
				}
			}
		}
		
		_render(data) {
			Dialog_1.default.open(this, data.returnValues.template);
			
			var cancelButton = document.querySelector('.jsCancelButton');
			cancelButton.addEventListener("click", (ev) => this._cancel(ev));
			
			var submitButton = document.querySelector('.jsSubmitButton');
			submitButton.addEventListener("click", (ev) => this._submit(ev));
			
			// hide submit button
			Util_1.default.hide(submitButton);
			
			// check box
			var checkBox = document.getElementById('transferEnable');
			checkBox.addEventListener("click", (ev) => this._enableTransfer(ev));
		}
		
		_ajaxSetup() {
			return {
				data: {
					className: 'cash\\data\\cash\\claim\\user\\UserCashClaimAction',
				}
			};
		}
		
		_dialogSetup() {
			return {
				id: 'getClaimPayDialog',
				options: { 
					title: Language.get('cash.claim.user.pay.title') 
				},
				source: null
			};
		}
	}
	
	let uZClaimPay;
	function init() {
		if (!uZClaimPay) {
			uZClaimPay = new UZClaimPay();
		}
	}
	exports.init = init;
});
