/**
 * Change a user credit
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Dom/Traverse"], function (require, exports, tslib_1, Ajax, Language, Dialog_1, UiNotification, Util_1, DomTraverse) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	UiNotification = tslib_1.__importStar(UiNotification);
	Util_1 = tslib_1.__importDefault(Util_1);
	DomTraverse = tslib_1.__importStar(DomTraverse);
	
	class UZCashUserCreditEdit {
		constructor() {
			var buttons = document.querySelectorAll('.jsCreditEditButton');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener("click", (ev) => this._click(ev));
			}
			
			this._objectID = 0;
		}
		
		_click(event) {
			event.preventDefault();
			
			this._objectID = event.currentTarget.dataset.objectId;
			
			Ajax.api(this, {
				actionName: 'getCreditEditDialog',
				parameters: {
					objectID: this._objectID
				}
			});
		}
		
		_cancel() {
			Dialog_1.default.close(this);
		}
		
		_submit() {
			var amountInput = document.querySelector('.jsAmount');
			var amount = amountInput.value;
			var amountError = DomTraverse.nextByClass(amountInput, 'innerError');
			
			// check amount
			amount = +amount.replace(',', '.');
			
			if(isNaN(amount) || amount == 0) {
				if (!amountError) {
					amountError = document.createElement('small');
					amountError.className = 'innerError';
					amountError.innerText = Language.get('cash.credit.user.amount.error');
					Util_1.default.insertAfter(amountError, amountInput);
					amountError.closest('dl').classList.add('formError');
				}
				return;
			}
			else {
				if (amountError) {
					amountError.remove();
					amountInput.closest('dl').classList.remove('formError');
				}
			}
			
			// check text length
			var subjectInput = document.querySelector('.jsSubject');
			var subject = subjectInput.value;
			var subjectError = DomTraverse.nextByClass(subjectInput, 'innerError');
			
			subject = subject.trim();
			if (subject.length > 255) {
				if (!subjectError) {
					subjectError = document.createElement('small');
					subjectError.className = 'innerError';
					subjectError.innerText = Language.get('cash.credit.user.subject.error');
					Util_1.default.insertAfter(subjectError, subjectInput);
					subjectInput.closest('dl').classList.add('formError');
				}
				else {
					subjectError.innerText = Language.get('cash.credit.user.amount.error');
				}
				return;
			}
			else {
				if (subjectError) {
					subjectError.remove();
					subjectInput.closest('dl').classList.remove('formError');
				}
			}
			
			// get currency
			var currencySelect = document.querySelector('.jsCurrency');
			var currency = currencySelect.options[currencySelect.selectedIndex].value;
			
			Ajax.api(this, {
				actionName:	'saveCreditEditDialog',
				parameters:	{
					amount:		amount,
					currency:	currency,
					subject:	subject,
					objectID:	this._objectID
				}
			});
		}
		
		_ajaxSuccess(data) {
			switch (data.actionName) {
				case 'getCreditEditDialog':
					this._render(data);
					break;
				case 'saveCreditEditDialog':
					UiNotification.show(Language.get('cash.credit.user.success'));
					Dialog_1.default.close(this);
					window.location.reload();
					break;
			}
		}
		
		_render(data) {
			Dialog_1.default.open(this, data.returnValues.template);
			
			var submitButton = document.querySelector('.jsSubmitButton');
			submitButton.addEventListener("click", (ev) => this._submit(ev));
			
			var cancelButton = document.querySelector('.jsCancelButton');
			cancelButton.addEventListener("click", (ev) => this._cancel(ev));
		}
		
		_ajaxSetup() {
			return {
				data: {
					className: 'cash\\data\\cash\\credit\\user\\UserCashCreditAction',
				}
			};
		}
		
		_dialogSetup() {
			return {
				id: 		'getUserCreditEditDialog',
				options: 	{ 
					title: Language.get('cash.credit.user.edit') 
				},
				source: 	null
			};
		}
	}
	
	let uZCashUserCreditEdit;
	function init() {
		if (!uZCashUserCreditEdit) {
			uZCashUserCreditEdit = new UZCashUserCreditEdit();
		}
	}
	exports.init = init;
});
