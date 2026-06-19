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
* Show reassurance image and text in the template email
*/
showReassuranceInTemplate = (elem) => {
    let reassuranceText = $(elem).val();
    let reassuranceId = $(elem).attr('data-reassurance');

    if (reassuranceText.length == 0 ) {
        $('.cap-email_reassurance_'+reassuranceId).hide();
        return true;
    } else {
        $('.cap-email_reassurance_'+reassuranceId).css('display', 'inline-block');
    }

    let reassuranceImg = $(elem).parent().parent().find('img').attr('src');

    $('.cap-email_reassurance_'+reassuranceId+':visible').parent().show('fast', () => {
        addaptReassuranceBlockWidth();
    });
    $('.cap-email_reassurance_'+reassuranceId+':visible img').attr('src', reassuranceImg);
    // $('input[name="email_reassurance_img'+reassuranceId+'"]:visible').val(reassuranceImg);
    $('#reassurance:visible .reassurance_'+reassuranceId+' input').val(reassuranceImg);
    $('.cap-email_reassurance_'+reassuranceId+':visible .cap-email_reassurance_text').text(reassuranceText);
    
};

addaptReassuranceBlockWidth = () => {
    let blockNumber = $('.cap-email_reassurance:visible').length;
    let width = '100%';

    if (blockNumber == 3)
        width = '33%';
    else if (blockNumber == 2)
        width = '50%';

    $('.cap-email_reassurance').parent().css('width', width);
};

/*
* Show social network logo and url
*/
showSocialNetworkInTemplate = (elem) => {
    let socialNetwork = $(elem).data('social');
    let url = $(elem).val();
    let pattern_for_url = /(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
    let pattern_for_http = /(http(s)?:\/\/)/g

    $('.cap-social_'+socialNetwork).hide();

    // Check if url is ok
    if (pattern_for_url.test(url)) {
        if (!pattern_for_http.test(url)) {
            $(elem).val('https://'+url);
        }
        $('.cap-social_'+socialNetwork).attr('href', url).css('display','inline-table');
    }	
};

/*
* Show specific Unsubscribe Text in live edit
*/
showSpecificUnsubscribeText = (elem) => {
    let newText = $(elem).val();

    if (newText === undefined) {
        $('.unsubscribe_link').text(cap_template_demo_unsubscribe_default_text);
        return true;
    }

    if (newText.length > 0) {
        $('.unsubscribe_link').text(newText);
        return true;
    }

    $('.unsubscribe_link').text(cap_template_demo_unsubscribe_default_text);
    return true;
}

/**
 * Load template datas on load & load social network URL and logo
 */
loadReassuranceAndSocialDatas = () => {
    $('.content_by_lang').each((i,e) => {
        if ($(e).attr('style').length == 0) {
            $('.cap-email-reassurance input', e).each((x, elem) => {
                showReassuranceInTemplate(elem);
            });

            $('.cap-email-socials input', e).each((x, elem) => {
                showSocialNetworkInTemplate(elem);
            });

            showSpecificUnsubscribeText($('#email_unsubscribe_text:visible input', e));
        }
    });
};


/**
 * Wait for document being ready
 */
$(document).ready(function() {

    /**
     * Load template datas on load & load social network URL and logo
     */
    loadReassuranceAndSocialDatas();

    /**
     * Change language need to reload all datas
     */
    $(document).on('change', 'select[name="cap-email-language"]', (e) => {
       

        // Waiting for the ckeditor to be initialize
        setTimeout(function() {
            // Load current language content ckeditor
            let nameCkeditorContent = $('.content_by_lang:visible #email_content textarea').attr('name');
            getDataAndWrite(nameCkeditorContent);

            // Load current language content ckeditor
            let nameCkeditorDiscount = $('.content_by_lang:visible #email_discount textarea').attr('name');
            getDataAndWrite(nameCkeditorDiscount);

            // Load current language unsubscribe ckeditor
            let nameCkeditorUnsubscribe = $('.content_by_lang:visible #email_unsubscribe textarea').attr('name');
            getDataAndWrite(nameCkeditorUnsubscribe);
            
            // Load unsubscribeText
            let unsubscribeText = $('#email_unsubscribe_text:visible input');
            showSpecificUnsubscribeText(unsubscribeText);

            $('.cap-email-reassurance:visible input').trigger('keyup');
            $('.cap-email-socials:visible input').trigger('keyup');
        }, 50);
    });  

    /* 
    * Show or Hide Appearance or Text & content panel
    */
    $(document).on('click', '.inner-template-section', (e) => {
        let tagSource = $(e.target).prop("tagName");
        let elem = '#'+$(e.target).parent().attr('id');

        if (tagSource == 'I') {
            elem = '#'+$(e.target).parent().parent().attr('id');
            $(e.target).text('keyboard_arrow_up');
        } else {
            $('i', e.target).html('keyboard_arrow_up');
        }

        $('.inner-template-section i').html('keyboard_arrow_down');
        $('#cap_appearance_conf, #cap_content_conf').removeClass('selected');
        $('.cap_content_conf_elems').slideUp();
        
        $(elem).addClass('selected');
        $(elem+' .cap_content_conf_elems').slideDown();
    });

    /*
    * change template
    */
    $(document).on('click', '.cap_content_conf_elems .customradiodesign input', (e) => {
        let template = '#'+$(e.target).val();

        $('.show_template').hide();
        $(template).show();
    });

    /*
    * Change template color 
    */
    liveEditColor = (type, color) => {
        if (type == 'primary') {
            $('.show_template .cap-email_reassurance img').css('background-color', color);
            $('.show_template .primary_color-bordercolor').css('border-color', color);
            $('.show_template .primary_color-backgroundcolor').css('background-color', color);
            $('.show_template .primary_color-textcolor').css('color', color);
        } else if (type == 'secondary') {
            $('.show_template a').css('color', color);
            $('.show_template .columns-price').css('color', color);
            $('.show_template .cap-email_preview_unsubscribe a').css('color', color);
        }
    };

    /* 
    * Update email template Title
    */
    $(document).on('keyup', '#email_subject:visible input', (e) => {
        let subject = $(e.target).val();

        $('.show_template title').html(subject);
    });

    /*
    * Open popup for responsive 
    */
    openTemplateInDeviceWindow = (device) => {
        let width = '900';
        let height = '1000';

        if (device == 'smartphone') {
            width = '420';
            height = '600';
        } else if (device == 'tablet') {
            width = '700';
            height = '1000';
        } 

        // Prepare popup
        let divText = $('.show_template:visible')[0].outerHTML;
        let myWindow = window.open('', '', 'width='+width+',height='+height);
        myWindow.onblur = myWindow.close;
        let doc = myWindow.document;
        doc.open();
        doc.write(divText);
        doc.close();
    };

    /*
    * Adding Social Networks images and links
    */
    $(document).on('keyup', '.cap-email-socials input:visible', (e) => {
        showSocialNetworkInTemplate(e.target);
        addaptReassuranceBlockWidth();
    });

    /*
    * Adding specific unsubscribe text
    */
    $(document).on('keyup', '#email_unsubscribe_text input:visible', (e) => {
        showSpecificUnsubscribeText(e.target);
    });


    /*
    * Adding reassurance images and text by updating the text
    */
    $(document).on('keyup', '.cap-email-reassurance input:visible', (e) => {
        showReassuranceInTemplate(e.target);
        addaptReassuranceBlockWidth();
    });

    /**
     *  Close popin Reassurance if click outside
    */ 
    $(document).on('click', 'body', (e) => {
        let isInside = $(e.target).closest('.reassurance_selectimg').length;
        let isPopin = $(e.target).closest('#reassurance_block').length;

        if (!isInside && !isPopin) {
            $("#reassurance_block").fadeOut(300);
        }
    });

    /**
     *  Show popin Reassurance and move it into the right place
     */
    $(document).on('click', '.reassurance_selectimg', (e) => {
        let reassuranceId = $(e.target).closest('.reassurance_selectimg').attr('data-id');
        let position = $(e.target).closest('.reassurance_section').position();
        let offsetLeft = 24;
        let offsetTop = 105;

        $('#reassurance_block').show();
        $('#reassurance_block').css('top', position.top+offsetTop+'px');
        $('#reassurance_block').css('left', position.left+offsetLeft+'px');
        $('#reassurance_block').attr('data-id', reassuranceId); 
    });

    /*
    * Count reassurance and unsubscribe characters number
    */
    $(document).on('keyup', '#email_subject input, #reassurance input, #email_unsubscribe_text input', (e) => {
        let amount = $(e.target).val().length;
        let value = $(e.target).val();
        let newValue = value;

        // if characters > 100, we must delete the lastest char (the 101th) and return
        if (amount > 100) {
            newValue = value.substring(0, value.length - 1);
            $(e.target).val(newValue);
            return false;
        }

        $(e.target).parent().find('.caract-count .amount').html(amount);
    });

    /*
    * CTA characters number and update live edit with CTA keyup
    */
    $(document).on('keyup', '#email_cta input', (e) => {
        let amount = $(e.target).val().length;
        let value = $(e.target).val();
        let newValue = value;

        // Hide or show for live edit
        if (amount == 0) {
            $('.cap-email_cta').hide();
        } else {
            $('.cap-email_cta').show();
        }

        // if characters > 100, we must delete the lastest char (the 101th) and return
        if (amount > 25) {
            newValue = value.substring(0, value.length - 1);
            $(e.target).val(newValue);
            return false;
        }

        // update live edit value
        $('.cta_content a').text(newValue);        

        $(e.target).parent().find('.caract-count .amount').html(amount);
    });

    /*
    *   Reassurance Block select category
    */
    $(document).on('click', '#reassurance_block .category_select div i', (e) => {
        let category = $(e.target).attr('data-id');

        $('#reassurance_block .category_select div').removeClass('active');
        $(e.target).parent().addClass('active');

        $('#reassurance_block .category_reassurance').removeClass('active');
        $('#reassurance_block .cat_'+category).addClass('active');
    });

    /*
    *   Reassurance Block select picto
    */
    $(document).on('click', '#reassurance_block .category_reassurance img', (e) => {
        let icon = $(e.target).attr('src');
        let id = $('#reassurance_block').attr('data-id');

        $('#reassurance_block .category_reassurance img').removeClass('selected');
        $(e.target).addClass('selected');
        $('#reassurance:visible .reassurance_'+id+' img').attr('src', icon);
        $('#reassurance_block').fadeOut(300);
        $('.cap-email_reassurance_'+id+' img').attr('src', icon);
        $('#reassurance:visible .reassurance_'+id+' input').val(icon);
    });
});

