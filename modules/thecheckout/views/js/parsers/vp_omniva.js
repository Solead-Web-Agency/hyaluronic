/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Veebipoed.ee | Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

tc_confirmOrderValidations['vp_omniva'] = function() {
  let selectedDeliveryOption = $('.delivery-option-row.selected');
  if (selectedDeliveryOption && 0 !== selectedDeliveryOption.length) {
    if (selectedDeliveryOption[0].classList.contains('vp_omniva') ||
        selectedDeliveryOption[0].classList.contains('vp_omniva_latvia') ||
        selectedDeliveryOption[0].classList.contains('vp_omniva_lietuva')) {
      let nextElement = selectedDeliveryOption[0].nextElementSibling;
      if (nextElement && 0 !== nextElement.children.length) {
        let selectElements = $(nextElement).find('select');
        if (2 === selectElements.length) {
          if (selectElements[1][0].selected) {
            var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
            shippingErrorMsg.show();
            scrollToElement(shippingErrorMsg);
            return false;
          } else {
            return true;
          }
        } else {
          if (selectElements[0][0].selected) {
            var shippingErrorMsg = $('#thecheckout-shipping .inner-wrapper > .error-msg');
            shippingErrorMsg.show();
            scrollToElement(shippingErrorMsg);
            return false;
          } else {
            return true;
          }
        }
      }
    }
  }

  return true; // default when validations are not needed
}
