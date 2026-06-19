/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// Default props so that widget can load without errors
colissimoDeliveryAddress = { 'address': '', 'zipcode': '', 'city': '' }
colissimoPreparationTime = 1;

checkoutShippingParser.colissimo = {
    extra_content: function (element) {
        const input = element.html();
        // parse props and re-export to the window object
        const colissimo_props = input.matchAll(/const ([^=]+)=([^;]+);/g)

        for (const x of colissimo_props) {
            [prop, value] = [x[1].trim(), x[2].trim()]
            // console.log(`prop:${prop}, value:${value}`);
            window[prop] = (value.indexOf('{') == 0) ?
                JSON.parse(value.replace(/(['"])?([a-z0-9A-Z_]+)(['"])?:/g, '"$2": ').replace(/'/g,'"'))
                : eval(value); // <-- value is controlled by the developer
        }
    },
    on_ready: function() {
        setTimeout(function(){
            if ($('.iti').length == 0 && 'undefined' !== typeof initMobileField) {
               initMobileField();
               if ($('#colissimo-pickup-mobile-phone').length) {
                 $('#colissimo-pickup-mobile-phone').keyup();
               }
            }
        },100)
    },
    delivery_option: function (element) {
        element.append(' \
            <script> \
              $(document).ready( \
                 checkoutShippingParser.colissimo.on_ready \
              ); \
            </script> \
        ');
    },
}