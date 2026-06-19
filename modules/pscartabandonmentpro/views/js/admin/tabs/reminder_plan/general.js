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

/**
 *  Language management
 */

$(document).on('change', 'select[name="cap-email-language"]', (e) => {
    let lang = $(e.target).val();
    
    $('.content_by_lang').hide();
    $('.content_by_lang.lang_'+lang).show();
});

/**
 *  Radio button input
 */

$(document).on('change', '.btn-radio input', (e) => {
    let radioName = $(e.target).attr('name');

    $('input[name="'+radioName+'"]').closest('.btn-radio').removeClass('selected');
    $(e.target).closest('.btn-radio').addClass('selected');
});

/**
 *  New and cancel step management
 */

$(document).on('click', '#createNewReminder', (e) => {
    ajaxLoadStepTemplate('target_frequency', 0);
    $('#reminder_configuration_step').attr('data-action', 'add').attr('data-reminder_id', '0');
    $('.reminder_listing').hide();
    $('#reminder_configuration').show();
});

$(document).on('click', '#cancelConfiguration', (e) => {
    $('#cancel_confirm').fadeIn(300);
});

$(document).on('click', '#confirm_cancel', (e) => {
    ajaxLoadStepTemplate('listing', 0);
    resetReminderSteps();
});

$(document).on('click', '#cancel_cancel', (e) => {
    $('#cancel_confirm').fadeOut(300);
});

resetReminderSteps = () => {
    if (isEmailTemplateloaded()) {
        destroyCkEditors($('.cap-editor'));
    }
    $('#cancel_confirm').hide();
    $('#reminder_configuration').hide();
    manageStepPanel('target_frequency', 1);
    $('#reminder_configuration_step').html('');
}

isEmailTemplateloaded = () => {
    let loaded = $('#reminder_email_template').length;

    if (loaded === 1) {
        return true;
    } else {
        return false;
    }
}

/**
 *  Edit reminder
 */

$(document).on('click', '.listing-edit i', (e) => {
    let reminderId = $(e.target).parent().attr('data-id');
    ajaxLoadStepTemplate('target_frequency', reminderId);
    $('#reminder_configuration_step').attr('data-action', 'update').attr('data-reminder_id', reminderId);
    $('.reminder_listing').hide();
    $('#reminder_configuration').show();
});


/**
 *  Step management
 */

 $(document).on('click', '#nextConfiguration, #previousConfiguration', (e) => {
    let currentStep = $('#reminder_configuration').attr('data-step');
    let action = $(e.target).attr('name');
    let nextStep = currentStep;
    let templateName = 'target_frequency';
    let reminderId = $('#reminder_configuration_step').attr('data-reminder_id');

    if (reminderId === undefined ) {
        reminderId = 0;
    }

    if (action === 'next') {
        nextStep = parseInt(currentStep) + 1;
    } else {
        nextStep = parseInt(currentStep) - 1;
    }

    switch (nextStep) {
        case 1:
            templateName = 'target_frequency';
        break;
        case 2:
            templateName = 'discount';
        break;
        case 3:
            templateName = 'email_template';
        break;
    }

    // To see how the router works :
    // console.log(currentStep, action, nextStep, templateName);

    let elemAlreadyExist = $('#reminder_'+templateName).length;

    if (elemAlreadyExist == 0) {
        ajaxLoadStepTemplate(templateName, reminderId);
    }

    manageStepPanel(templateName, nextStep);
 });

 /**
  * Save all steps
 */

 $(document).on('click', '#saveConfiguration, #updateConfiguration', (e) => {

    /*
    *    Target datas
    */
    let oTargetDatas = $('#reminder_target_frequency').find('select, input').serialize();

    /*
    *   Discount datas
    */
    let isSpecific = $('#cart_discount_specific_yes').prop('checked');
    let discount_type = '#discount_not_specific';

    if (isSpecific) {
        discount_type = '#discount_is_specific';
    }

    let oDiscountDatas = [];
    $('.discount', discount_type).each( function( index ) {
        oDiscountDatas[index] = $(this).find('select, input, input:not(:checked)').serialize();
        
        // get unchecked values
        $('.discount input[type=checkbox]:not(:checked)').each(function() {     
            oDiscountDatas[index] += '&'+this.name+'=0';
        });
    });

    /*
    *    Template datas
    */
    let oTemplateDatas = [];
    let langDatas = [];
    
    oTemplateDatas[0] = $('#cap_appearance_conf').find('select, input').serialize();

    $('#cap_content_conf .content_by_lang').each( function( index, elem ) {
        let id_lang = $('input[name="id_lang"]',elem).val();
        let hasFieldSubject = $(elem).find('input[name="email_subject"]').val();

        // get only datas with subject field
        if (hasFieldSubject.length > 0) {
            langDatas[id_lang] = '';        

            // get all datas from CKEDITORS
            $('textarea', elem).each( function(i,el) {
                let name = $(el).attr('name');
                let ckeditor_data = CKEDITOR.instances[name].getData();
                let content = name+'='+ckeditor_data+'&';

                langDatas[id_lang] += decodeHTML(content);
            });

            // get the rest of datas
            langDatas[id_lang] += $(this).find('select, input').serialize();
        }
    });
    
    oTemplateDatas[1] = langDatas.filter((el) => {
        return el != null;
    });

    /*
    *    Save datas
    */
    saveDatasByStep(oTargetDatas, oDiscountDatas, oTemplateDatas);
 });

// Get real HTML Tags
decodeHTML = (html) => {
	var txt = document.createElement('textarea');
	txt.innerHTML = html;
	return txt.value;
};

/** 
 * Functions about template
 */

manageStepPanel = (template, step) => {

    switch (template) {
        case 'target_frequency':
            $('#previousConfiguration').hide();
            $('#saveConfiguration').hide();
            $('#updateConfiguration').hide();
            $('#nextConfiguration').show();
            break;
        case 'discount':
            $('#previousConfiguration').show();
            $('#saveConfiguration').hide();
            $('#updateConfiguration').hide();
            $('#nextConfiguration').show();
            break;
        case 'email_template':
            let actionReminder = $('#reminder_configuration_step').attr('data-action');

            if (actionReminder == 'update') {
	            initializeCkEditors($('.cap-editor.has_content'));
                $('#updateConfiguration').show();
            } else {
                $('#saveConfiguration').show();
            }
            $('#previousConfiguration').show();
            $('#nextConfiguration').hide();
            break;
    }

    $('#reminder_configuration').attr('data-step', step); 
    $('.panel-summary-heading li').removeClass('active');
    $('.panel-summary-heading li:nth-child('+step+')').addClass('active');
    $('.steps_panel').hide();
    $('#reminder_'+template).show();
}


/**
 * Ajax load Template / 
 * Ajax Save Datas /
 * Ajax Update Datas /
 */

ajaxLoadStepTemplate = (template, reminderId) => {
    let cap_controller_url = getControllerUrl(template);
    let cap_controller = getControllerName(template);
    $.ajax({
        type: 'POST',
        url: cap_controller_url,
        dataType: 'html',
        async: false,
        data: {
            controller : cap_controller,
            action : 'loadTemplate',
            ajax : true,
            reminder_id : reminderId
        },
        success : (data) => {
            if (template == 'email_template') {
                preloadCart();
            }
            if (template == 'listing') {
                $('.tab_cap_listing').html(data);
            } else {
                $('#reminder_configuration_step').append(data);
            }
        }
    });
}

saveDatasByStep = (oTargetDatas, oDiscountDatas, oTemplateDatas) => {
    let actionReminder = $('#reminder_configuration_step').attr('data-action');
    let reminderId = $('#reminder_configuration_step').attr('data-reminder_id');

    $.ajax({
        type: 'POST',
        url: cap_controller_reminder_url,
        dataType: 'json',
        async: false,
        data: {
            controller : cap_controller_reminder,
            action : 'save',
            ajax : true,
            saveAction : actionReminder, // add or update
            reminder_id : reminderId,
            targetData : oTargetDatas,
            discountData : oDiscountDatas,
            templateData : oTemplateDatas,
        },
        success : (data) => {
            if (data.result) {
                ajaxLoadStepTemplate('listing', 0);
                $.growl.notice({
                    title: "",
                    size: "large",
                    message: reminder_updated
                });
                resetReminderSteps();
            } else {
                $('#save_reminder .target_error_list ul, #save_reminder .discount_error_list ul, #save_reminder .template_error_list ul').html('');
                $('#save_reminder .target_error_list, #save_reminder .discount_error_list, #save_reminder .template_error_list').hide();
                showStepsError(data.errors);
            }
        }
    });
}

/**
 * Show all steps errors
 */
$(document).on('click', '#save_reminder button', (e) => {
    $('#save_reminder').fadeOut(300);
});

showStepsError = (data) => {
    for (values in data) {
        if (values == 'target') {
            showTargetErrors(data.target);
        }
        if (values == 'discount') {
            showDiscountErrors(data.discount);
        }
        if (values == 'template') {
            showTemplateErrors(data.template);
        }
    }
    $('#save_reminder').show();
}

showTargetErrors = (data) => {
    for (x in data) {
        $('#save_reminder .target_error_list ul').append('<li>'+data[x]+'</li>');
    }
    $('#save_reminder .target_error_list').show();
}

showDiscountErrors = (data) => {
    for (array in data) {
        for (x in array) {
            $('#save_reminder .discount_error_list ul').append('<li>'+data[array][x]+'</li>');
        }
    }
    $('#save_reminder .discount_error_list').show();
}

showTemplateErrors = (data) => {
    for (section in data) {
        if (section == 'appearance') {
            for (x in section) {
                if (typeof data[section][x] !== 'undefined') {
                    $('#save_reminder .template_error_list ul').append('<li>'+data[section][x]+'</li>');
                }
            }
        } else {
            if (data[section].length == 1) {
                $('#save_reminder .template_error_list ul').append('<li>'+data[section][0]+'</li>');
            } else {
                for (value in section) {
                    for (x in data[section][value]) {
                        if (typeof data[section][value] !== 'undefined') {
                            $('#save_reminder .template_error_list ul').append('<li>'+data[section][value][x]+'</li>');
                        }
                    }
                }
            }
        }
    }

    $('#save_reminder .template_error_list').show();
}
