/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

$(document).ready(function(){
    if (ps16) {
        let price_container = $('p.our_price_display').html();
        let price_text = $('div p.our_price_display').first().contents().eq(2).text();
        let fix_content = price_container.replace(price_text, '<br />'+price_label);
        $('p.our_price_display').html(fix_content);
    }
    else {
        var custom_content = $('.tax-shipping-delivery-label').children();
        $('.tax-shipping-delivery-label').html(price_label).append(custom_content);
    }
});