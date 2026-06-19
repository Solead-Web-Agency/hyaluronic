/*
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2023 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
$(document).ready(function () {
    $('.dhl-ec-row input:radio').on('change', function () {
        var countExtracharge = $(".options_dg [id$='_on']:checked").length;

        if (countExtracharge > 3) {
            $("#" + this.name + "_on").prop("checked", false);
            $("#" + this.name + "_off").prop("checked", true);
            alert("You can't check more than three options ! ");
        } else {
            toggleTypeDesignation();
        }
    });
});


function toggleWeightPriceServices() {
    if ($('#DHL_USE_DHL_PRICES_on').prop('checked')) {
        $('.dhl-weight-prices-div').show(400);
        $('.dhl-services-list-div').show(400);
        $('.dhl-use-dhl-packages-div').show(400);
        $('.dhl-use-weight-package-div').show(400);
        $('.dhl-use-weight-product-div').show(400);
        $('.dhl-enable-free-delivery-from').show(400);
        toggleWeightingType();
        toggleFreeDelivery();
    } else {
        $('.dhl-weight-prices-div').hide(200);
        $('.dhl-services-list-div').hide(400);
        $('.dhl-use-dhl-packages-div').hide(400);
        $('.dhl-use-weight-package-div').hide(400);
        $('.dhl-use-weight-product-div').hide(400);
        $('.dhl-weighting-type-div').hide(200);
        $('.dhl-weighting-value-percent-div').hide(200);
        $('.dhl-weighting-value-amount-div').hide(200);
        $('.dhl-enable-free-delivery-from').hide(200);
        $('.dhl-free-delivery-from').hide(200);
    }
}

function toggleWeightPriceTypeServices() {
    if ($('#DHL_USE_PREDEFINED_PACKAGES_on').prop('checked')) {
        $('.dhl-use-weight-package-div').show(400);
        $('.dhl-use-weight-product-div').show(400);
    } else {
        $('.dhl-use-weight-package-div').hide(400);
        $('.dhl-use-weight-product-div').hide(400);
    }
}

function toggleWeightPriceTypePackage() {
    if ($('#DHL_USE_WEIGHT_PACKAGE_on').prop('checked')) {
        $("#DHL_USE_WEIGHT_PRODUCT_off").prop('checked', true)
    } else {
        $("#DHL_USE_WEIGHT_PRODUCT_on").prop('checked', true)
    }
}

function toggleWeightPriceTypeProduct() {
    if ($('#DHL_USE_WEIGHT_PRODUCT_on').prop('checked')) {
        $("#DHL_USE_WEIGHT_PACKAGE_off").prop('checked', true)
    } else {
        $("#DHL_USE_WEIGHT_PACKAGE_on").prop('checked', true)
    }
}

function toggleFreeDelivery() {
    if ($('#DHL_ENABLE_FREE_SHIPPING_FROM_on').prop('checked')) {
        $('.dhl-free-delivery-from').show(400);
    } else {
        $('.dhl-free-delivery-from').hide(200);
    }
}

function toggleWeightingType() {
    if ($('#DHL_WEIGHT_PRICES_on').prop('checked')) {
        $('.dhl-weighting-type-div').show(400);
        toggleWeightingValue();
    } else {
        $('.dhl-weighting-type-div').hide(200);
        $('.dhl-weighting-value-percent-div').hide(200);
        $('.dhl-weighting-value-amount-div').hide(200);
    }
}

function toggleWeightingValue() {
    var percentDiv = $('.dhl-weighting-value-percent-div');
    var amountDiv = $('.dhl-weighting-value-amount-div');
    if ($('#type-percent').prop('checked')) {
        amountDiv.hide(200);
        percentDiv.show(400);
    } else {
        percentDiv.hide(200);
        amountDiv.show(400);
    }
}

function updateWeightSuffix(id) {
    var weightSuffix = $(id + ' .dhl-suffix-weight');
    if ($(id + ' #system-metric').prop('checked')) {
        weightSuffix.html('kg');
    } else {
        weightSuffix.html('lb');
    }

}

function updateDimensionSuffix(id) {
    var dimSuffix = $(id + ' .dhl-suffix-dimension');
    if ($(id + ' #system-metric').prop('checked')) {
        dimSuffix.html('cm');
    } else {
        dimSuffix.html('in');
    }
}

function toggleTypeDesignation() {
    if ($('#extracharge_4_on').prop('checked')) {
        $('.div_dhl_type_designation').show(400);
    } else {
        $('.div_dhl_type_designation').hide(200);
    }
}

$(document).ready(function () {
    toggleWeightPriceServices();
    toggleWeightPriceTypeServices();
   // toggleSignatureInvoice();
    toggleTypeDesignation();
    $('.dhl-upload-signature-div').show(0);
    $('.dhl-new-img-signature-div').show(0); 
});

$(document).on('click', '.dhl-use-dhl-prices-div span', function() {
    toggleWeightPriceServices();
});
$(document).on('click', '.dhl-use-dhl-packages-div span', function() {
    toggleWeightPriceTypeServices();
});
$(document).on('click', '.dhl-enable-free-delivery-from span', function () {
    toggleWeightPriceServices();
});
$(document).on('click', '.dhl-weight-prices-div span' , function () {
    toggleWeightPriceServices();
});
$(document).on('click', '#type-percent', function () {
    toggleWeightingValue();
});
$(document).on('click', '#type-amount', function () {
    toggleWeightingValue();
});
$(document).on('click', '#system-metric', function () {
    updateWeightSuffix('#bo');
    updateDimensionSuffix('#bo');
});
$(document).on('click', '#system-imperial', function () {
    updateWeightSuffix('#bo');
    updateDimensionSuffix('#bo');
});
$(document).on('click', 'input[name=DHL_USE_WEIGHT_PACKAGE]', function() {
    toggleWeightPriceTypePackage();
});
$(document).on('click', 'input[name=DHL_USE_WEIGHT_PRODUCT]', function() {
    toggleWeightPriceTypeProduct();
});

