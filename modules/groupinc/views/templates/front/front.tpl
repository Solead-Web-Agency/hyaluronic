{**
* Price increment/discount by customer groups
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
*}

<script type="text/javascript">

var combinationsFromController = [];
{if isset($combinations_groupinc)}
    {foreach from=$combinations_groupinc key=idCombination item=combination}
    	addComb({$idCombination|intval}, {$combination.price_modified|floatval}, {$combination.old_price|floatval});
    {/foreach}
{/if}

//update display of the availability of the product AND the prices of the product
function updateDisplay()
{
    if (!selectedCombination['unavailable'] && quantityAvailable > 0 && productAvailableForOrder == 1)
    {
        //show the choice of quantities
        $('#quantity_wanted_p:hidden').show('slow');

        //show the "add to cart" button ONLY if it was hidden
        $('#add_to_cart:hidden').fadeIn(600);

        //hide the hook out of stock
        $('#oosHook').hide();

        //hide availability date
        $('#availability_date_label').hide();
        $('#availability_date_value').hide();

        //availability value management
        if (availableNowValue != '')
        {
            //update the availability statut of the product
            $('#availability_value').removeClass('warning_inline');
            $('#availability_value').text(availableNowValue);
            if(stock_management == 1)
                $('#availability_statut:hidden').show();
        }
        else
        {
            //hide the availability value
            $('#availability_statut:visible').hide();
        }

        //'last quantities' message management
        if (!allowBuyWhenOutOfStock)
        {
            if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage)
            $('#last_quantities').show('slow');
            else
                $('#last_quantities').hide('slow');
        }

        if (quantitiesDisplayAllowed)
        {
            $('#pQuantityAvailable:hidden').show('slow');
            $('#quantityAvailable').text(quantityAvailable);

            if (quantityAvailable < 2) // we have 1 or less product in stock and need to show "item" instead of "items"
            {
                $('#quantityAvailableTxt').show();
                $('#quantityAvailableTxtMultiple').hide();
            }
            else
            {
                $('#quantityAvailableTxt').hide();
                $('#quantityAvailableTxtMultiple').show();
            }
        }
    }
    else
    {
        //show the hook out of stock
        if (productAvailableForOrder == 1)
        {
            $('#oosHook').show();
            if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
                oosHookJsCode();
        }

        //hide 'last quantities' message if it was previously visible
        $('#last_quantities:visible').hide('slow');

        //hide the quantity of pieces if it was previously visible
        $('#pQuantityAvailable:visible').hide('slow');

        //hide the choice of quantities
        if (!allowBuyWhenOutOfStock)
            $('#quantity_wanted_p:visible').hide('slow');

        //display that the product is unavailable with theses attributes
        if (!selectedCombination['unavailable'])
            $('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : '')).addClass('warning_inline');
        else
        {
            $('#availability_value').text(doesntExist).addClass('warning_inline');
            $('#oosHook').hide();
        }
        if(stock_management == 1)
            $('#availability_statut:hidden').show();

        //display availability date
        if (selectedCombination.length)
        {
            var available_date = selectedCombination['available_date'];
            tab_date = available_date.split('-');
            var time_available = new Date(tab_date[2], tab_date[1], tab_date[0]);
            time_available.setMonth(time_available.getMonth()-1);
            var now = new Date();
            // date displayed only if time_available
            if (now.getTime() < time_available.getTime())
            {
                $('#availability_date_value').text(selectedCombination['available_date']);
                $('#availability_date_label').show();
                $('#availability_date_value').show();
            }
            else
            {
                $('#availability_date_label').hide();
                $('#availability_date_value').hide();
            }
        }
        //show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
        if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'] && productAvailableForOrder == 1)
        {
            $('#add_to_cart:hidden').fadeIn(600);

            if (availableLaterValue != '')
            {
                $('#availability_value').text(availableLaterValue);
                if(stock_management == 1)
                    $('#availability_statut:hidden').show('slow');
            }
            else
                $('#availability_statut:visible').hide('slow');
        }
        else
        {
            $('#add_to_cart:visible').fadeOut(600);
            if(stock_management == 1)
                $('#availability_statut:hidden').show('slow');
        }

        if (productAvailableForOrder == 0)
            $('#availability_statut:visible').hide();
    }

    if (selectedCombination['reference'] || productReference)
    {
        if (selectedCombination['reference'])
            $('#product_reference span').text(selectedCombination['reference']);
        else if (productReference)
            $('#product_reference span').text(productReference);
        $('#product_reference:hidden').show('slow');
    }
    else
        $('#product_reference:visible').hide('slow');

    //update display of the the prices in relation to tax, discount, ecotax, and currency criteria
    if (!selectedCombination['unavailable'] && productShowPrice == 1)
    {
        var priceTaxExclWithoutGroupReduction = '';

        // retrieve price without group_reduction in order to compute the group reduction after
        // the specific price discount (done in the JS in order to keep backward compatibility)
        if (!displayPrice && !noTaxForThisProduct)
        {
            priceTaxExclWithoutGroupReduction = ps_round(productPriceTaxExcluded, 6) * (1 / group_reduction);
        } else {
            priceTaxExclWithoutGroupReduction = ps_round(productPriceTaxExcluded, 6) * (1 / group_reduction);
        }
        var combination_add_price = selectedCombination['price'] * group_reduction;

        var tax = (taxRate / 100) + 1;

        var display_specific_price;
        if (selectedCombination.specific_price)
        {
            display_specific_price = selectedCombination.specific_price['price'];
            if (selectedCombination['specific_price'].reduction_type == 'percentage')
            {
                $('#reduction_amount').hide();
                $('#reduction_percent_display').html('-' + parseFloat(selectedCombination['specific_price'].reduction_percent) + '%');
                $('#reduction_percent').show();
            } else if (selectedCombination['specific_price'].reduction_type == 'amount' && selectedCombination['specific_price'].reduction_price != 0) {
                $('#reduction_amount_display').html('-' + formatCurrency(selectedCombination['specific_price'].reduction_price, currencyFormat, currencySign, currencyBlank));
                $('#reduction_percent').hide();
                $('#reduction_amount').show();
            } else {
                $('#reduction_percent').hide();
                $('#reduction_amount').hide();
            }
        }
        else
        {
            display_specific_price = product_specific_price['price'];
            if (product_specific_price['reduction_type'] == 'percentage')
                $('#reduction_percent_display').html(product_specific_price['specific_price'].reduction_percent);
        }

        if (product_specific_price['reduction_type'] != '' || selectedCombination['specific_price'].reduction_type != '')
            $('#discount_reduced_price,#old_price').show();
        else
            $('#discount_reduced_price,#old_price').hide();

        if (product_specific_price['reduction_type'] == 'percentage' || selectedCombination['specific_price'].reduction_type == 'percentage')
            $('#reduction_percent').show();
        else
            $('#reduction_percent').hide();
        if (display_specific_price)
            $('#not_impacted_by_discount').show();
        else
            $('#not_impacted_by_discount').hide();

        var taxExclPrice = (display_specific_price && display_specific_price >= 0  ? (specific_currency ? display_specific_price : display_specific_price * currencyRate) : priceTaxExclWithoutGroupReduction) + selectedCombination['price'] * currencyRate;

        if (display_specific_price)
            productPriceWithoutReduction = priceTaxExclWithoutGroupReduction + selectedCombination['price'] * currencyRate; // Need to be global => no var

        if (!displayPrice && !noTaxForThisProduct)
        {
            productPrice = taxExclPrice * tax; // Need to be global => no var
            if (display_specific_price)
                productPriceWithoutReduction = ps_round(productPriceWithoutReduction * tax, 2);
        }
        else
        {
            productPrice = ps_round(taxExclPrice, 2); // Need to be global => no var
            if (display_specific_price)
                productPriceWithoutReduction = ps_round(productPriceWithoutReduction, 2);
        }

        var reduction = 0;
        if (selectedCombination['specific_price'].reduction_price || selectedCombination['specific_price'].reduction_percent)
        {
            selectedCombination['specific_price'].reduction_price = (specific_currency ? selectedCombination['specific_price'].reduction_price : selectedCombination['specific_price'].reduction_price * currencyRate);
            reduction = productPrice * (parseFloat(selectedCombination['specific_price'].reduction_percent) / 100) + selectedCombination['specific_price'].reduction_price;
            if (selectedCombination['specific_price'].reduction_price && (displayPrice || noTaxForThisProduct))
                reduction = ps_round(reduction / tax, 6);
        }
        else if (product_specific_price.reduction_price || product_specific_price.reduction_percent)
        {
            product_specific_price.reduction_price = (specific_currency ? product_specific_price.reduction_price : product_specific_price.reduction_price * currencyRate);
            reduction = productPrice * (parseFloat(product_specific_price.reduction_percent) / 100) + product_specific_price.reduction_price;
            if (product_specific_price.reduction_price && (displayPrice || noTaxForThisProduct))
                reduction = ps_round(reduction / tax, 6);
        }
        productPriceWithoutReduction = productPrice * group_reduction;

        productPrice -= reduction;
        var tmp = productPrice * group_reduction;
        productPrice = ps_round(productPrice * group_reduction, 2);

        var ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];
        productPrice += ecotaxAmount;
        productPriceWithoutReduction += ecotaxAmount;

        //productPrice = ps_round(productPrice * currencyRate, 2);
        var our_price = '';
        if (productPrice > 0) {
            our_price = formatCurrency(productPrice, currencyFormat, currencySign, currencyBlank);
        } else {
            our_price = formatCurrency(0, currencyFormat, currencySign, currencyBlank);
        }

        if (combinationsFromController[$('#idCombination').val()] != undefined && combinationsFromController[$('#idCombination').val()]['price_modified'] != undefined) {
            our_price = formatCurrency(combinationsFromController[$('#idCombination').val()]['price_modified'], currencyFormat, currencySign, currencyBlank);
            //$('#our_price_display').text(formatCurrency(price_modified, currencyFormat, currencySign, currencyBlank));
        }

        if (combinationsFromController[$('#idCombination').val()] != undefined && combinationsFromController[$('#idCombination').val()]['old_pric'] != undefined) {
            productPriceWithoutReduction = combinationsFromController[$('#idCombination').val()]['old_price'];
        }

        $('#our_price_display').text(our_price);
        $('#old_price_display').text(formatCurrency(productPriceWithoutReduction, currencyFormat, currencySign, currencyBlank));
        if (productPriceWithoutReduction > productPrice && old_price > 0)
            $('#old_price,#old_price_display,#old_price_display_taxes').show();
        else
            $('#old_price,#old_price_display,#old_price_display_taxes').hide();
        // Special feature: "Display product price tax excluded on product page"
        var productPricePretaxed = '';
        if (!noTaxForThisProduct)
            productPricePretaxed = productPrice / tax;
        else
            productPricePretaxed = productPrice;
        $('#pretaxe_price_display').text(formatCurrency(productPricePretaxed, currencyFormat, currencySign, currencyBlank));
        // Unit price
        productUnitPriceRatio = parseFloat(productUnitPriceRatio);
        if (productUnitPriceRatio > 0 )
        {
            newUnitPrice = (productPrice / parseFloat(productUnitPriceRatio)) + selectedCombination['unit_price'];
            $('#unit_price_display').text(formatCurrency(newUnitPrice, currencyFormat, currencySign, currencyBlank));
        }

        // Ecotax
        ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];
        $('#ecotax_price_display').text(formatCurrency(ecotaxAmount, currencyFormat, currencySign, currencyBlank));
    }
}

function addComb(idCombination, price_modified, old_price)
{
    var comb = [];
    comb['idCombination'] = idCombination;
    comb['price_modified'] = price_modified;
    comb['old_price'] = old_price;
    combinationsFromController[idCombination] = comb;
}

</script>