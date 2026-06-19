/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

/*
* Show or Hide panel multiple
*/
$(document).on('click', '#cart_discount_specific', (e) => {
    let isSpecific = $('#cart_discount_specific_yes').prop('checked');

    if (isSpecific) {
        setSpecificCurrencyValues();
        setRangeCurrencyValue();
        $('#discount_is_specific').show();
        $('#discount_not_specific').hide();
    } else {
        $('#discount_not_specific').show();
        $('#discount_is_specific').hide();
    }
});

/*
* If freeshipping, show/hide discount value input
*/
$(document).on('change', '.discount select', (e) => {
    let valueType = $(e.target).val();

    $(e.target).parent().parent().parent().find('.discount_validity').show();
    $(e.target).parent().parent().parent().find('.discount_validity .input-group').show();

    if (valueType == 'freeshipping') {
        $(e.target).parent().parent().parent().find('.discount_value div').hide();
        $(e.target).parent().parent().parent().parent().find('.discount_ttc').hide();
    } else {
        $(e.target).parent().parent().parent().find('.discount_value div').show();
        $(e.target).parent().parent().parent().parent().find('.discount_ttc').show();
    }

    if (valueType == 'percentage') {
        $(e.target).parent().parent().parent().find('.discount_value .input-group-addon .percentage').show();
        $(e.target).parent().parent().parent().find('.discount_value .input-group-addon .currency').hide();
    } else {
        $(e.target).parent().parent().parent().find('.discount_value .input-group-addon .percentage').hide();
        $(e.target).parent().parent().parent().find('.discount_value .input-group-addon .currency').show();
    }

    if (valueType == 'no_discount') {
        $(e.target).parent().parent().parent().find('.discount_value').hide();
        $(e.target).parent().parent().parent().find('.discount_validity').hide();
        $(e.target).parent().parent().parent().parent().find('.discount_ttc').hide();
        $(e.target).parent().parent().parent().parent().find('.discount_cumulate').hide();
    } else {
        $(e.target).parent().parent().parent().find('.discount_value').show();
        $(e.target).parent().parent().parent().parent().find('.discount_cumulate').show();
    }
});

/*
* data control on inputs from / to
*/
$(document).on('keyup', '.discount-from-to input.discount_from', (e) => {
    let reg = /^\d+$/g;
    let value = $(e.target).val();
    let newValue = value;
    let isNumber = value.match(reg);

    if (!isNumber) {
        newValue = value.substring(0, value.length - 1);
        $(e.target).val(newValue);
        return false;
    }

    setRangeCurrencyValue();
});

/*
* Add new discount 
*/
$(document).on('click', '.add_more button', (e) => {

    // We must wait the slideDown to end, else it break the CSS and the step won't pass the tests !
    if ($(e.target).hasClass('wait'))
        return true;

    $(e.target).addClass('wait');

    let addNewDiscount = $('#discount_is_specific .discount').last().clone();
    let copiedDiscountType = $('select[name="discount_value_type"]', addNewDiscount).find(":selected").val();
    let range = parseInt($('.range', addNewDiscount).html());
    let fromValue = parseInt($('.discount_from', addNewDiscount).val());
    let toValue = parseInt($('.discount_to', addNewDiscount).val());

    $(addNewDiscount).hide();

    // We force discount_value to be visible only if the copied line is not a "No Discount"
    if (copiedDiscountType !== 'no_discount') {
        $('.discount_validity, .discount_value, .discount_value div, .discount_ttc', addNewDiscount).show();
        $('.intern-section .input-group', addNewDiscount).css('display', 'table');
    } else {
        $('.discount_validity, .discount_value, .discount_value div, .discount_ttc', addNewDiscount).hide();
        $('.intern-section .input-group', addNewDiscount).css('display', 'none');
    }

    $('.range', addNewDiscount).html(range+1);
    $('.discount_from', addNewDiscount).val(fromValue+1);
    $('.discount_to', addNewDiscount).val(toValue+1);
    $('#discount_is_specific .discount_full_list').append(addNewDiscount);

    hideOrShowDeleteButton(0);

    $(addNewDiscount).slideDown('fast', function(){
        // We must wait the slideDown to end, else it break the CSS and the step won't pass the tests !
        $(e.target).removeClass('wait');
    });

    $('.discount_from', addNewDiscount).focus();
});

/*
* Remove discount line
*/
$(document).on('click', '.remove_line i', (e) => {
    hideOrShowDeleteButton(1);

    $(e.target).parent().parent().slideUp("normal", function() { 
        $(this).remove(); 
        $('.range-number .range').each((index,elem) => {
            $(elem).html(index+1);
        });
        setRangeCurrencyValue();
    });    
});

hideOrShowDeleteButton = (offset) => {
    let nbLineLeft = $('.remove_line i').length - offset;

    if (nbLineLeft == 1) {
        $('.remove_line i').slideUp();
    } else {
        $('.remove_line i').slideDown();
    }
}

setRangeCurrencyValue = () => {
    let fromValues = [];
    $('input.discount_from').each((index,elem) => {
        fromValues[index] = parseInt($(elem).val());
        
        if (fromValues[index] <= fromValues[index - 1]) {
            $(elem).addClass('wrong_range');
        } else {
            $(elem).removeClass('wrong_range');
        }
    });

    $('input.discount_to').each((index,elem) => {
        let toValue = parseInt(fromValues[index+1] - 1);

        if (isNaN(toValue)) {
            toValue = parseInt(fromValues[index] + 1);
        }

        $(elem).val(toValue);
    });
}

setSpecificCurrencyValues = () => {
    let firstVal = parseInt($('.discount_from:first').val());
    let lastVal = firstVal + 29;
    let lastCurrentVal = $('.discount_from:last').val();

    if (lastCurrentVal == 1) {
        $('.discount_from:last').val(lastVal);
    }
}