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

flagChangeDisplay = false;
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

function showPriceModified() {
    if (typeof combinationsFromController !== "undefined" && typeof combinationsFromController[$('#idCombination').val()] !== "undefined") {
    	if (combinationsFromController[$('#idCombination').val()] != undefined && combinationsFromController[$('#idCombination').val()]['price_modified'] != undefined) {
    		price_modified = combinationsFromController[$('#idCombination').val()]['price_modified'];

    		var nbProduct = parseInt($('#quantity_wanted').val());
            if (combinationsFromController[$('#idCombination').val()]['quantities'] != undefined) {
                if (nbProduct > 1) {
        			var arr = combinationsFromController[$('#idCombination').val()]['quantities'];
        			for (index = 0; index < arr.length; ++index) {
        				if (nbProduct >= parseInt(arr[index]['qty'])) {
        					price_modified_quantity = arr[index]['price_modified_quantity'];
        					if (arr[index]['reduction_type'] == 'percentage') {
        						$('#reduction_percent_display').html('-' + parseFloat(arr[index]['reduction']).toFixed(2) + '%');
        					} else {
        						$('#reduction_amount_display').html('-' + parseFloat(arr[index]['reduction']).toFixed(2));
        					}
        					$('#our_price_display').text(formatCurrency(price_modified_quantity, currencyFormat, currencySign, currencyBlank));
        					$('#reduction_percent_display').show();
        					$('#reduction_percent').show();
        					if ($('#old_price_display .price').length > 0) {
        						$('#old_price_display .price').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));
        					} else {
        						$('#old_price_display').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));
        					}
        					$('#old_price_display').show();
        					$('#old_price').show();
        					$('#old_price').removeClass('hidden');
                            flagChangeDisplay = true;
        				} else {
                            if (flagChangeDisplay) {
                                old_price = price_modified;
                                if (typeof combinationsFromController[$('#idCombination').val()]['old_price'] !== "undefined") {
                                    old_price = combinationsFromController[$('#idCombination').val()]['old_price'];
                                }
                                if (price_modified == old_price) {
                                    $('#old_price_display').hide();
                                    $('#old_price').hide();
                                    $('#old_price').addClass('hidden');
                                    $('#reduction_percent_display').hide();
                                    $('#reduction_percent').hide();
                                    $('#our_price_display').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));
                                } else {
                                    $('#our_price_display').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));
                                    $('#old_price_display .price').text(formatCurrency(old_price, currencyFormat, currencySign, currencyBlank));
                                    $('#old_price_display').text(formatCurrency(old_price, currencyFormat, currencySign, currencyBlank));
                                    $('#reduction_percent_display').html('-' + parseFloat((1 - price_modified / old_price) * 100).toFixed(2) + '%');
                                }
                            }
        				}
        			};
                }
    		} else {
    			$('#our_price_display').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));

    			if (price_modified > 0) {
    				if (combinationsFromController[$('#idCombination').val()] != undefined && combinationsFromController[$('#idCombination').val()]['old_price'] != undefined) {
    					if (combinationsFromController[$('#idCombination').val()]['old_price'] > 0) {
    						old_price = combinationsFromController[$('#idCombination').val()]['old_price'];
    						if ($('#old_price_display .price').length > 0) {
    							$('#old_price_display .price').text(formatCurrency(old_price, currencyFormat, currencySign, currencyBlank));
    						} else {
    							$('#old_price_display').text(formatCurrency(old_price, currencyFormat, currencySign, currencyBlank));
    						}

    						$('#old_price_display, #old_price').show();
    						$('#old_price_display, #old_price').removeClass('hidden');

    						if ($('#reduction_percent').text().trim() != '') {
    							$('#reduction_percent').show();
    						}

    						if ($('#reduction_amount').text().trim() != '') {
    							$('#reduction_amount').show();
    						}
    					}
    				}
    			}
    		}

    		/*else {
    			$('#reduction_percent').hide();
    			$('#old_price_display').hide();
    		}*/
    	}

    	if (combinationsFromController[$('#idCombination').val()] != undefined && combinationsFromController[$('#idCombination').val()]['on_sale'] != undefined) {
    		$('.sale-label').show();
    	} else {
    		$('.sale-label').hide();
    	}
    }
}

if (typeof findSpecificPrice !== "undefined" && typeof combinationsFromController !== "undefined") {
	findSpecificPrice = (function() {
	    var findSpecificPriceCached = findSpecificPrice;

	    return function(json) {
	        findSpecificPriceCached.apply(this, arguments);
	        showPriceModified();
		}
	})();
}