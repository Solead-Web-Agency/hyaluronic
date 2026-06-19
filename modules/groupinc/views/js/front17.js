/**
* Price increment/discount by groups, categories and prices
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

if (typeof updatePrice !== "undefined") {
	updatePrice = (function() {
	    var updatePriceCached = updatePrice;

	    return function(json) {
	        updatePriceCached.apply(this, arguments);
	        showPriceModified();
		}
	})();
} else if (typeof updateDisplay !== "undefined") {
	updateDisplay = (function() {
	    var updateDisplayCached = updateDisplay;

	    return function(json) {
	        updateDisplayCached.apply(this, arguments);
	        showPriceModified();
		}
	})();
}

function showPriceModified(value) {
	if (typeof combinationsFromController !== "undefined") {
		if (combinationsFromController[parseInt(value)] != undefined) {
			if (combinationsFromController[parseInt(value)] == 1) {
				$('.product-flag.on-sale').show();
			} else {
				if (parseInt($('#quantity_wanted').val()) >= combinationsFromController[parseInt(value)]) {
					$('.product-flag.on-sale').show();
				} else {
					$('.product-flag.on-sale').hide();
				}
			}
		}
	}
}

jQuery(document).on("change", ".product-variants-item li input", function() {
	showPriceModified(parseInt($(this).val()));
});

jQuery(document).ready(function() {
	showPriceModified(parseInt($('.product-variants-item input:radio:checked').val()));
})

jQuery(document).on("click", ".bootstrap-touchspin-up", function() {
	showPriceModified(parseInt($('.product-variants-item input:radio:checked').val()));
});

jQuery(document).on("click", ".bootstrap-touchspin-down", function() {
	showPriceModified(parseInt($(this).val()));
});