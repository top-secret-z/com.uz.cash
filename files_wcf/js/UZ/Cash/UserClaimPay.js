/**
 * Pay a claim.
 * 
 * @author        2018-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.cash
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Ui/Confirmation"], function (require, exports, tslib_1, Ajax, Language, UiNotification, UiConfirmation) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;

    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);

    class UZCashUserClaimPay {
        constructor() {
            var buttons = document.querySelectorAll('.jsPayButton');
            for (var i = 0, length = buttons.length; i < length; i++) {
                buttons[i].addEventListener("click", (ev) => this._click(ev));
            }

            this._objectID = 0;
        }

        _click(event) {
            event.preventDefault();

            var objectID = event.currentTarget.dataset.objectId;

            UiConfirmation.show({
                confirm: function() {
                    Ajax.apiOnce({
                        data: {
                            actionName:    'payClaim',
                            className:    'cash\\data\\cash\\claim\\user\\UserCashClaimAction',
                            parameters:    {
                                objectID: objectID
                            }
                        },
                        success: function(data) {
                            UiNotification.show();
                            window.location.reload();
                        }
                    });
                },
                message: Language.get('cash.claim.user.pay.confirm')
            });
        }
    }

    let uZCashUserClaimPay;
    function init() {
        if (!uZCashUserClaimPay) {
            uZCashUserClaimPay = new UZCashUserClaimPay();
        }
    }
    exports.init = init;
});
