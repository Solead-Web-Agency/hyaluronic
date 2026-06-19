/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$('body').on('click', '#x-checkout-edit', function() {
   location.href = $(this).attr('data-href');
});

var contentRowContainer = $('#checkout-payment-step').closest('.row');

if (contentRowContainer.length) {
   contentRowContainer.before($('#separate-payment-step-hidden-container').html());
}

if ("undefined" !== typeof amazon_ongoing_session && amazon_ongoing_session) {

   $('.payment-options').addClass('amazon_ongoing_session');

   var formEl = $('form[action*="/amazonpay/"]').parent('.js-payment-option-form');
   var additionalEl = formEl.prev('.additional-information');
   var titleEl = additionalEl.prev('div');

   titleEl.find('input[name=payment-option]').prop('checked', true);

   formEl.addClass('amazon-visible');
   additionalEl.addClass('amazon-visible');
   titleEl.addClass('amazon-visible');
}

// Check if there's 'option' parameter in the url, if yes, let's hide everything except single payment method
var searchParams = new URLSearchParams(window.location.search);
var optionSet = searchParams.get('option');
if (optionSet) {
   $('body').addClass('p3i-option-set');
   // $('[id='+optionSet+'-container]').parent('div').addClass('p3i-visible-only');
   $('[id^='+optionSet+'-]').addClass('p3i-visible-only');
   setTimeout(function() {$('[id='+optionSet+']').trigger('click');}, 200);
   $('[id^=conditions_to_approve]').prop('checked', false).trigger('click');
}

$('.checkout-step-btn').on('click', function() {
   let url = new URL(window.location.href);
   let params = new URLSearchParams(url.search);
   params.delete('p3i');
   url.search = params.toString();
   let stepId = $(this).data('step-id');
   url.hash = stepId;
   window.location.href = url.toString();
});

$(document).ready(function() {
   $('body').addClass('tc-separate-payment');
   // E.g. vatchecker module did change cart total (vat exempt) on address save / p3i show
   // that changed cartChecksum, which changed checkout_session, making payment step unreachable
   // Fix can be removing $product['total_wt'] from CartChecksum.php, generateChecksum() method, line 71
   // But it's not recommended, because it's a core change and to improve UX, we can just go back to previous step
   if ($('#checkout-payment-step.-unreachable').length) {
      history.back();
   }
});