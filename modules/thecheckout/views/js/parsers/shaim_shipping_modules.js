/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* shaim_shipping_modules parser version 1.0.0 */

var shaim_shipping_modules_present = ('undefined' !== typeof exists_opc);

if (shaim_shipping_modules_present) {

  const attachPplEvents = function () {
    setTimeout(function () {
      var ppl_parcelshop_iframe = document.getElementById('ppl_parcelshop_iframe');
      if (typeof ppl_parcelshop_iframe !== 'undefined' && ppl_parcelshop_iframe && typeof iframeListenerPplparcelshop === 'function') {
        var idoc_ppl_parcelshop = ppl_parcelshop_iframe.contentWindow || ppl_parcelshop_iframe.contentDocument;
        if (!idoc_ppl_parcelshop.document.pplParcelshopMapListenerAdded) {
          idoc_ppl_parcelshop.document.addEventListener('ppl-parcelshop-map', function (e) {
            iframeListenerPplparcelshop(e);
          });
          idoc_ppl_parcelshop.document.pplParcelshopMapListenerAdded = true;
        }
      }
    }, 1500);
  }

  document.addEventListener('DOMContentLoaded', function(event) {

    if (typeof CheckHardPplparcelshop === 'function') {
      attachPplEvents();
      prestashop.on('thecheckout_updateDeliveryOption', attachPplEvents);
      // attach windowResize only after small delay, so that it's not call on initial resize on small screens
      setTimeout(function() {
        prestashop.on('thecheckout_windowResize', attachPplEvents);
      }, 200);
    }

  });

  tc_confirmOrderValidations['shaim_shipping_modules'] = function () {

    /* openservis - WEDO - begin */
    if (typeof CheckHardWedo === 'function' && CheckHardWedo() === false) {
      return false;
    }
    /* openservis - WEDO - end */

    /* openservis - Zásilkovna WIDGET - begin */
    if (typeof CheckHardZasilkovnaWidget === 'function' && CheckHardZasilkovnaWidget() === false) {
    return false;
    }
    /* openservis - Zásilkovna WIDGET - end */

    /* openservis - Uloženka - begin */
    if (typeof CheckHardUlozenka === 'function' && CheckHardUlozenka() === false) {
    return false;
    }
    /* openservis - Uloženka - end */

    /* openservis - In Time - begin */
    if (typeof CheckHardIntime === 'function' && CheckHardIntime() === false) {
    return false;
    }
    /* openservis - In Time - end */

    /* openservis - PPL - Parcel Shop - begin */
    if (typeof CheckHardPplparcelshop === 'function' && CheckHardPplparcelshop() === false) {
    return false;
    }
    /* openservis - PPL - Parcel Shop - end */

    /* openservis - GLS - Parcel Shop - begin */
    if (typeof CheckHardGlsparcelshop === 'function' && CheckHardGlsparcelshop() === false) {
    return false;
    }
    /* openservis - GLS - Parcel Shop - end */

    /* openservis - Geis Point - begin */
    if (typeof CheckHardGeispoint === 'function' && CheckHardGeispoint() === false) {
    return false;
    }
    /* openservis - Geis Point - end */

    /* openservis - DPD Parcel Shop (Pick Up) - begin */
    if (typeof CheckHardDpdparcelshop === 'function' && CheckHardDpdparcelshop() === false) {
    return false;
    }
    /* openservis - DPD Parcel Shop (Pick Up) - end */

    /* openservis - Balik na postu - begin */
    if (typeof CheckHardBaliknapostu === 'function' && CheckHardBaliknapostu() === false) {
    return false;
    }
    /* openservis - Balik na postu - end */

    /* openservis - Balikovna - begin */
    if (typeof CheckHardBalikovna === 'function' && CheckHardBalikovna() === false) {
    return false;
    }
    /* openservis - Balikovna - end */

    return true;
    /* openservis - Zásilkovna - end */
  }
}
