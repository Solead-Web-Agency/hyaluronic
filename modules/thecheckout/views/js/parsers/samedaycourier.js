/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
  Tested on 28.5.2025 with samedaycourier 1.7.2 by Sameday Courier

  (!) Update modules/samedaycourier/views/templates/front/_partials/checkout_lockers.tpl, change const to var for 3 methods:
  is_set, set_cookie, get_cookie

 */

tc_confirmOrderValidations['samedaycourier_easybox_locker'] = function () {
    if (
        $('#locker_address').length && 
        $('#showLockerMap:visible').length && 
        $('#locker_address').val() === ''
    ) {
      var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
      $('.shipping-validation-details').remove();
      shippingErrorMsg.append('<span class="shipping-validation-details"> (Sameday Courier - Alege easybox)</span>')
      shippingErrorMsg.show();
      scrollToElement(shippingErrorMsg);
      return false;
    } else {
      return true;
    }
};

checkoutShippingParser.samedaycourier = {
    after_load_callback: function (deliveryOptionIds) {

        if (
            typeof docReady !== 'function' ||
            typeof is_set !== 'function' ||
            typeof get_cookie !== 'function' ||
            typeof set_cookie !== 'function' ||
            document.getElementById('showLockerMap')
        ) {
            return;
        }

        docReady(function () {
            if (is_set( () => document.getElementById("locker_name"))) {
                if(get_cookie("samedaycourier_locker_name").length > 1){
                    let lockerNamesCookie = get_cookie("samedaycourier_locker_name");
                    let lockerAddressCookie = get_cookie("samedaycourier_locker_address");
                    document.getElementById("locker_name").value = lockerNamesCookie;
                    document.getElementById("locker_address").value = lockerAddressCookie;

                    document.getElementById("showLockerDetails").style.display = "block";
                    document.getElementById("showLockerDetails").innerHTML = lockerNamesCookie + '<br/>' + lockerAddressCookie;

                }else{
                    document.getElementById("showLockerDetails").style.display = "none";
                }
            }

            const cookie_locker_id = 'samedaycourier_locker_id';
            const cookie_locker_name = 'samedaycourier_locker_name';
            const cookie_locker_address = 'samedaycourier_locker_address';

            let showLockerMap = document.getElementById('showLockerMap');
            let showLockerSelector = document.getElementById('lockerIdSelector');

            console.log(showLockerMap);

            if (is_set(() => showLockerMap)) {
                const clientId="b8cb2ee3-41b9-4c3d-aafe-1527b453d65e";//each integrator will have a unique clientId
                const countryCode= document.getElementById('showLockerMap').getAttribute('data-country').toUpperCase(); //country for which the plugin is used
                const langCode= document.getElementById('showLockerMap').getAttribute('data-country');  //language of the plugin
                const samedayUser= document.getElementById('showLockerMap').getAttribute('data-username'); //sameday username

                window['LockerPlugin'].init({ clientId: clientId, countryCode: countryCode, langCode: langCode, apiUsername: samedayUser });

                let lockerPlugin = window['LockerPlugin'].getInstance();

                showLockerMap.addEventListener('click', () => {
                    lockerPlugin.open();
                }, false);

                lockerPlugin.subscribe((locker) => {
                    let lockerId = locker.lockerId;
                    let lockerName = locker.name;
                    let lockerAddress = locker.address;

                    set_cookie(cookie_locker_id, lockerId, 30);

                    document.getElementById("locker_name").value = lockerName;
                    set_cookie(cookie_locker_name, lockerName, 30);

                    document.getElementById("locker_address").value = lockerAddress;
                    set_cookie(cookie_locker_address, lockerAddress, 30);

                    document.getElementById("showLockerDetails").style.display = "block";
                    document.getElementById("showLockerDetails").innerHTML = lockerName + '<br/>' + lockerAddress;

                    lockerPlugin.close();
                });

            } else {
                showLockerSelector.onchange = (event) => {
                    let _target = event.target;
                    let option = _target.options[_target.selectedIndex];

                    set_cookie(cookie_locker_id, _target.value, 30);
                    set_cookie(cookie_locker_name, option.getAttribute('data-name'), 30);
                    set_cookie(cookie_locker_address, option.getAttribute('data-address'), 30);
                }
            }
        });
    },

    delivery_option: function (element) {

    },

    extra_content: function (element) {

    }

}

