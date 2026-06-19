/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

var smartform = smartform || {};
// To disable, set in custom JS: var smartformSuggestCompany = false;
var smartformSuggestCompany = (typeof smartformSuggestCompany !== 'undefined') ? smartformSuggestCompany : true;
// To disable, set in custom JS: var smartformValidatePhone = false;
var smartformValidatePhone = (typeof smartformValidatePhone !== 'undefined') ? smartformValidatePhone : true;
// To disable, set in custom JS: var smartformValidateEmail = false;
var smartformValidateEmail = (typeof smartformValidateEmail !== 'undefined') ? smartformValidateEmail : true;
// To change, set in custom JS: var msg_SmartformInvalidEmail = 'Email je špatne (custom msg.)';
var msg_SmartformInvalidEmail = (typeof msg_SmartformInvalidEmail !== 'undefined') ? msg_SmartformInvalidEmail : 'Špatně zadaná e-mailová adresa.';
// To change, set in custom JS: var msg_SmartformInvalidPhone = 'Tel. číslo je špatne (custom msg.)';
var msg_SmartformInvalidPhone = (typeof msg_SmartformInvalidPhone !== 'undefined') ? msg_SmartformInvalidPhone : 'Špatně zadané tel. číslo.';
var msg_SmartformInvalidEmail_IncludeResponseFlags = (typeof msg_SmartformInvalidEmail_IncludeResponseFlags !== 'undefined') ? msg_SmartformInvalidEmail_IncludeResponseFlags : false;
var smartformRemoveSpacesFromPhone = (typeof smartformRemoveSpacesFromPhone !== 'undefined') ? smartformRemoveSpacesFromPhone : false;

smartform.beforeInit = function () {
    smartform.setClientId(tc_smartformClientId);
    smartform.enableSetAutocomplete(false); // Smartform is setting autocomplete to 'off' which seems to be ignored by Chrome

    // ---------------- SMARTFORM VALIDATE EMAIL -----------------
    if (smartformValidateEmail) {
        const el = $(`#thecheckout-account .form-group.email input.form-control`);
        el.addClass('smartform-email');
        // smartform.getInstance().emailControl.addValidationCallback(tc_emailValidationCallback);
    }
}

smartform.afterInit = function () {
    tc_smartformLoadedCallback();
}

$.getScript({
    url: 'https://client.smartform.cz/v2/smartform.js',
    cache: true
})

function tc_smartformLoadedCallback() {
    console.log('[tc_smartform] smartform initiated');

    const emailValidationCallback = (response) => {
        console.log(`[tc_smartform] Validation result for '${response?.result?.inputEmail}': ${response?.result?.resultType}, upToDate: ${response?.upToDate}, flags: ${response?.result?.flags}`);
        if (response && response?.upToDate && response?.result?.resultType === 'NOT_EXISTS') {
            const _tc_smartFormEmailError = msg_SmartformInvalidEmail + (msg_SmartformInvalidEmail_IncludeResponseFlags ? ` ${response?.result?.flags}` : '');
            const blockSel = '#thecheckout-account';
            printContextErrors(blockSel, { email: _tc_smartFormEmailError }, undefined, true);
        } else {
            $(`#thecheckout-account .form-group.email input.form-control`).removeClass('-error');
            checkAndHideGlobalError();
        }
    }

    const phoneValidationCallback = (response, addrType, phoneType) => {
        console.log(`[tc_smartform] ${addrType}.${phoneType} validation result for '+${response?.result?.prefix}${response?.result?.number}': ${response?.result?.resultType}, upToDate: ${response?.upToDate}`);
        if (response && response?.upToDate && response?.result?.resultType === 'INVALID') {
            const _tc_smartFormPhoneError = msg_SmartformInvalidPhone;
            const blockSel = `#thecheckout-address-${addrType}`;
            printContextErrors(blockSel, { [phoneType]: _tc_smartFormPhoneError }, undefined, true);
        } else {
            $(`#thecheckout-address-${addrType} .form-group.${phoneType} input.form-control`).removeClass('-error');
            checkAndHideGlobalError();
            // Shall we remove spaces from the phone number?
            if (smartformRemoveSpacesFromPhone) {
                // $(`#thecheckout-address-${addrType} .form-group.${phoneType} input.form-control`).val(function(_, currentValue) {
                //     return currentValue.replace(/\s+/g, '');
                // });
            }
        }
    }

    const addressSelected = (element, text, fieldType, suggestion) => {
        // console.log('addressSelected callback called', element, text, fieldType, suggestion);
        if (fieldType == 'smartform-address-street-and-number') {
            // Check if number is present in address, if not, add 'missing-street-number' class on parent element
            var pattern = /\d/;
            if (!text.match(pattern)) {
                $(element).closest('.form-group').addClass('missing-street-number');
            } else {
                $(element).closest('.form-group').removeClass('missing-street-number');
            }
        }
        return true;
    }

    const attachClass = (addrType, tcField, smartformFields, unbind, extraInstanceSuffix = '') => {
        const el = $(`[data-address-type=${addrType}] .form-group.${tcField} input.form-control`);
        const cls = `smartform-instance-${addrType}${extraInstanceSuffix} ${smartformFields.join(' ')}`;
        if (unbind) {
            el.removeClass(cls);
        } else {
            el.addClass(cls).attr('autocomplete', 'completion-disabled');
        }
    }

    const attachFieldsForAddress = (addrType, unbind, isoCode = 'CZ') => {
        // console.log(` - ${unbind ? 'detach' : 'attach'} classes for '${addrType}'`)
        attachClass(addrType, 'address1', ['smartform-address-street-and-number', 'smartform-company-address-street-and-number'], unbind);
        attachClass(addrType, 'city', ['smartform-address-city', 'smartform-company-address-city'], unbind);
        attachClass(addrType, 'postcode', ['smartform-address-zip', 'smartform-company-address-zip'], unbind);

        if (smartformSuggestCompany) {
            attachClass(addrType, 'company', ['smartform-company-name'], unbind);
            attachClass(addrType, 'dni', ['smartform-company-registration-number'], unbind);
            attachClass(addrType, 'vat_number', ['smartform-company-vat-number'], unbind);
        }

        if (smartformValidatePhone) {
            attachClass(addrType, 'phone', ['smartform-phone-number'], unbind, '-phone');
            attachClass(addrType, 'phone_mobile', ['smartform-phone-number'], unbind, '-phone_mobile');
        }
    }

    const reInitAddresses = () => {
        const smartformInstances = []
        $('.address-fields .js-country option:selected').each(function () {
            const isoCode = $(this).closest('.address-fields').attr('data-iso-code');
            const addrType = $(this).closest('.address-fields').attr('data-address-type');
            console.log(`[tc_smartform] re-init '${addrType}' with country filter '${isoCode}'`)
            if (['CZ', 'SK'].includes(isoCode)) {
                smartformInstances.push({ addrType, unbind: false, isoCode })
                attachFieldsForAddress(addrType, false, isoCode);
            } else {
                smartformInstances.push({ addrType, unbind: true, isoCode })
                attachFieldsForAddress(addrType, true);
            }
        });

        smartform.rebindAllForms(function() {
            smartformInstances.forEach( ({ addrType, unbind, isoCode }) => {
                if (!unbind) {
                    const addressControl = smartform.getInstance(`smartform-instance-${addrType}`)?.addressControl;
                    addressControl.setCountry(isoCode);
                    addressControl.setSelectionCallback(addressSelected);

                    if (smartformValidatePhone) {
                        const phoneControl = smartform.getInstance(`smartform-instance-${addrType}-phone`)?.phoneControl;
                        phoneControl.setDefaultPrefix(isoCode);

                        if ($(`#${addrType}-address .phone.has-call-prefix`).length && $(`#${addrType}-address .phone.has-call-prefix`).is(':visible')) {
                            phoneControl.setAutomaticFormattingFormat('INTERNATIONAL_WITHOUT_PREFIX');
                        } else {
                            // phoneControl.setAutomaticFormattingFormat('INTERNATIONAL');
                            phoneControl.setAutomaticFormattingFormat('E164'); // E164 = INTERNATIONAL without spaces, e.g. +420777123456
                        }

                        phoneControl.addValidationCallback((response) => phoneValidationCallback(response, addrType, 'phone'));
                        // Do same for phone_mobile
                        const phoneMobileControl = smartform.getInstance(`smartform-instance-${addrType}-phone_mobile`)?.phoneControl;
                        phoneMobileControl.setDefaultPrefix(isoCode);
                        phoneMobileControl.addValidationCallback((response) => phoneValidationCallback(response, addrType, 'phone_mobile'));
                    }
                }
            });
            // ---------------- SMARTFORM VALIDATE EMAIL -----------------
            if (smartformValidateEmail) {
                smartform.getInstance().emailControl.addValidationCallback(emailValidationCallback);
            }
        });
    }

    reInitAddresses();

    prestashop.on('thecheckout_updateAddressCountry', function(data) {
        reInitAddresses();
    });
}

