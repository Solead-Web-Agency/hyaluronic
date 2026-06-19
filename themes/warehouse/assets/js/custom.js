/*
 * Custom code goes here.
 * A template should always ship with an empty custom.js
 */

// Customs warning for USA deliveries
$(document).ready(function() {
    // Function to check if we should show customs warning
    function checkUSADelivery() {
        // Only proceed if we're on checkout page and language is English
        if ($('body#checkout').length === 0) return;

        // Get current language from HTML lang attribute or prestashop object
        var currentLang = $('html').attr('lang') || (typeof prestashop !== 'undefined' && prestashop.language && prestashop.language.iso_code);

        // Only show for English language
        if (currentLang !== 'en' && currentLang !== 'en-us' && currentLang !== 'en-gb') return;

        // Check delivery address for USA
        var $deliveryAddress = $('.address-block:first .card-body').text();
        var $addressSelector = $('select[name="id_address_delivery"]');

        // Check if "United States" or "USA" appears in the delivery address
        if ($deliveryAddress && ($deliveryAddress.indexOf('United States') > -1 || $deliveryAddress.indexOf('USA') > -1)) {
            showCustomsWarning();
        }

        // Also check when address selector changes
        if ($addressSelector.length > 0) {
            $addressSelector.on('change', function() {
                setTimeout(function() {
                    var newAddress = $('.address-block:first .card-body').text();
                    if (newAddress && (newAddress.indexOf('United States') > -1 || newAddress.indexOf('USA') > -1)) {
                        showCustomsWarning();
                    }
                }, 500);
            });
        }
    }

    // Function to show the customs warning modal
    function showCustomsWarning() {
        // Check if popup was already shown in this session
        if (sessionStorage.getItem('customsWarningShown')) return;

        // Create modal if it doesn't exist
        if ($('#customs-warning-dynamic').length === 0) {
            var modalHtml = `
                <div class="modal fade" id="customs-warning-dynamic" tabindex="-1" role="dialog" aria-labelledby="customsWarningLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="border: 2px solid #dc3545;">
                            <div class="modal-header" style="background-color: #dc3545; color: white;">
                                <h5 class="modal-title" id="customsWarningLabel">
                                    <i class="fa fa-exclamation-triangle"></i> Important Customs Information
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" style="background-color: #fff5f5; padding: 25px;">
                                <div class="alert alert-danger" role="alert" style="margin-bottom: 0; font-size: 16px;">
                                    <h6 class="alert-heading" style="font-weight: bold; margin-bottom: 15px;">
                                        <i class="fa fa-info-circle"></i> Customs Fees Notice for USA Delivery
                                    </h6>
                                    <p style="margin-bottom: 15px;">
                                        <strong>Important:</strong> Your order will be shipped to the United States. Please be aware that:
                                    </p>
                                    <ul style="margin-bottom: 15px;">
                                        <li>Additional customs duties and taxes may be charged by US Customs</li>
                                        <li>These fees will be collected by DHL upon delivery</li>
                                        <li>The amount depends on the order value and product category</li>
                                        <li>These charges are separate from your order total and shipping fees</li>
                                    </ul>
                                    <p style="margin-bottom: 0; font-weight: 600;">
                                        By proceeding with your order, you acknowledge and accept responsibility for any customs duties and taxes that may be applied by US Customs and collected by DHL.
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer" style="background-color: #fff5f5;">
                                <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal">
                                    I Understand and Accept
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        }

        // Show the modal
        $('#customs-warning-dynamic').modal('show');

        // Mark as shown for this session
        sessionStorage.setItem('customsWarningShown', 'true');
    }

    // Check on page load
    checkUSADelivery();

    // Also check when checkout step changes
    $(document).on('click', '.js-edit-addresses, .js-edit-delivery', function() {
        // Reset the warning flag when user edits addresses
        sessionStorage.removeItem('customsWarningShown');
    });

    // Listen for PrestaShop checkout events
    if (typeof prestashop !== 'undefined') {
        prestashop.on('updatedAddressForm', function() {
            setTimeout(checkUSADelivery, 500);
        });

        prestashop.on('updatedDeliveryForm', function() {
            setTimeout(checkUSADelivery, 500);
        });

        prestashop.on('changedCheckoutStep', function() {
            setTimeout(checkUSADelivery, 500);
        });
    }
});
