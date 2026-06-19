/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <contact@etssoft.net>
 *  @copyright  2007-2020 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

var etsAdminInterTrans = {
    ajaxXhrTranslatePage: null,
    oldDataTrans: {},
    dataTransThemeNotSave: {},
    keyDataTransForm: null,
    moduleFieldActive: null,
    emailItemActive: null,
    itemTranslateQueue: [],
    mailQueueTranslating: [],
    stepTrans: 2,
    limitTransModule: 30,
    moduleQueueTrans: [],
    formSaved: true,
    translateThemeSaved: true,
    stopTranslate: false,
    renderBtnTranslate: function(className, hasLi, btnText){
        className = className || '';
        hasLi = hasLi || false;
        btnText = btnText || etsAdminInterTrans.trans('translate');
        var btn =  '<a class="btn btn-primary ets-trans-btn-trans-toolbar js-ets-trans-btn-inter-trans-toolbar '+className+'" href="javascript:void(0)" title="'+etsAdminInterTrans.trans('translate')+'">' +
            '<i class="ets-trans-icon-translate fa fa-language"></i>' +
            '<span class="btn-title">'+btnText+'</span>' +
            '<span class="ets_tooltip">'+btnText+'</span>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }

        return btn;
    },
    renderBtnTranslate2: function(className, hasLi, btnText){
        className = className || '';
        hasLi = hasLi || false;
        btnText = btnText || etsAdminInterTrans.trans('translate');
        var btn =  '<a class="js-ets-trans-btn-inter-trans-toolbar '+className+'" href="javascript:void(0)" title="'+etsAdminInterTrans.trans('translate')+'">' +
            '<i class="process-icon-language"></i>' +
            '<div>'+btnText+'</div>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }

        return btn;
    },
    renderBtnTranslateItem: function(className, hasLi){
        className = className || '';
        hasLi = hasLi || false;
        var btn =  '<button class="btn btn-default ets-trans-btn-trans-item js-ets-trans-btn-inter-trans-item '+className+'" title="'+etsAdminInterTrans.trans('translate')+'">' +
            '<i class="ets-trans-icon-translate fa fa-language"></i>' +
            '<span class="btn-title">'+etsAdminInterTrans.trans('translate')+'</span>' +
            '<span class="ets_tooltip">'+etsAdminInterTrans.trans('translate')+'</span>' +
            '</button>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }

        return btn;
    },
    renderBtnResetAllTrans: function(className, hasLi){
        className = className || '';
        hasLi = hasLi || false;
        var btn = '<a class="btn btn-outline-secondary ets-trans-btn-trans-toolbar js-ets-trans-btn-inter-trans-reset-all hide '+className+'" href="javascript:void(0)" title="'+etsAdminInterTrans.trans('reset_all_trans')+'">' +
            '<i class="ets-trans-icon-translate fa fa-refresh"></i>' +
            '<span class="btn-title">'+etsAdminInterTrans.trans('reset_all_trans')+'</span>' +
            '<span class="ets_tooltip">'+etsAdminInterTrans.trans('reset_all_trans')+'</span>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }
        return btn;
    },
    renderBtnTransFieldItem: function(field , className, tooltip){
        field = field || '';
        className = className || '';
        tooltip = tooltip || 0;
        var attr = '';
        if(tooltip){
            //attr += 'data-toggle="tooltip" data-placement="top"';
        }
        return '<button type="button" '+attr+' title="'+ etsTransFunc.trans('translate')+'" class="btn btn-outline-secondary float-sm-right ets-ets-trans-btn-inter-trans-field-item js-ets-trans-btn-inter-trans-field-item '+className+'" data-field="'+field+'">'
            + '<i class="fa fa-language" aria-hidden="true"></i> '
            + etsTransFunc.trans('translate') +
            '<span class="ets_tooltip">'+etsTransFunc.trans('translate')+'</span>'
            +'</button>';
    },

    renderBtnResetTransItem: function(field , className){
        field = field || '';
        className = className || '';
        return '<button type="button" title="'+ etsTransFunc.trans('reset_trans')+'" class="btn btn-default float-sm-right ets-ets-trans-btn-inter-reset-field-item js-ets-trans-btn-inter-reset-field-item hide '+className+'" data-field="'+field+'">'
            + '<i class="ets-trans-icon-translate fa fa-refresh"></i> '
            + etsTransFunc.trans('reset_trans') +
            '<span class="ets_tooltip">'+etsTransFunc.trans('reset_trans')+'</span>'
            +'</button>';
    },
    renderBtnResetTransBoxItem: function(boxId , className){
        boxId = boxId || '';
        className = className || '';
        return '<button type="button" title="'+ etsTransFunc.trans('reset_trans') +'" class="btn btn-default float-sm-right ets-ets-trans-btn-inter-reset-box-item js-ets-trans-btn-inter-reset-box-item hide '+className+'" data-box="'+boxId+'">'
            + '<i class="ets-trans-icon-translate fa fa-refresh"></i> '
            + etsTransFunc.trans('reset_trans') +
            '<span class="ets_tooltip">'+etsTransFunc.trans('reset_trans')+'</span>'
            +'</button>';
    },

    renderBtnTransNewSystemAll: function( className, hasLi, btnText){
        className = className || '';
        hasLi = hasLi || false;
        btnText = btnText || etsAdminInterTrans.trans('translate_all');
        var btn =  '<a class="btn btn-primary ets-trans-btn-trans-toolbar js-ets-trans-btn-inter-trans-all '+className+'" href="javascript:void(0)" title="'+etsAdminInterTrans.trans('translate')+'">' +
            '<i class="ets-trans-icon-translate fa fa-language"></i>' +
            '<span class="btn-title">'+btnText+'</span>' +
            '<span class="ets_tooltip">'+btnText+'</span>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }
        return btn;
    },
    theme: {

        addBtnTranslate: function(){
            if($('.toolbar-icons a').length){
                if(!$('form[isedited]').find('.js-ets-trans-btn-inter-trans-toolbar').length)
                    $('form[isedited]').find('button[type=submit]').last().after(etsAdminInterTrans.renderBtnTranslate('', false, etsTransFunc.trans('translate_fields'))).addClass('trans_right');
                $('.toolbar-icons a').first().before(etsAdminInterTrans.renderBtnTransNewSystemAll('', false, etsAdminInterTrans.trans('translate')));
                $('.toolbar-icons a').first().before(etsAdminInterTrans.renderBtnResetAllTrans('hide'));
            }
        },
        getFormData: function(type, isTransAll, options, fieldTrans){
            var formData = {};
            fieldTrans = fieldTrans || null;
            if(fieldTrans){

                if(type == 'source')
                    formData[fieldTrans] = fieldTrans;
                else{
                    $('form[isedited] label:contains("'+etsTransFunc.formatTextContains(fieldTrans)+'")').each(function () {
                        if($(this).text().trim() == fieldTrans){
                            formData[fieldTrans] =$(this).next('textarea').val() || '';
                        }
                    });
                }

                return formData;
            }
            $('form[isedited] textarea').each(function(){
                var keyTrans = $(this).prev('label').text();
                if(type == 'source')
                    formData[keyTrans] = $(this).prev('label').text();
                else
                    formData[keyTrans] = $(this).val();
            });
            return formData;
        },
        getDataNotSave: function(){
            etsAdminInterTrans.dataTransThemeNotSave = {
                url: $('form[isedited]').attr('action'),
                domain: $('form[isedited]').closest('.translations-catalog').find('.domain-info>span:first-child').text(),
                data: {}
            };
            var dataSave = {};
            $('form[isedited] textarea').each(function () {
                var defaultText = $(this).prev('label').text();
                var editedText = $(this).val();
                dataSave[defaultText] = editedText;
            });

            etsAdminInterTrans.dataTransThemeNotSave.data = dataSave;
        },
        saveDataTranslated: function () {
            var dataSend = {
                translations: []
            };
            var dataTranslated = etsAdminInterTrans.dataTransThemeNotSave;
            Object.keys(dataTranslated.data).forEach(function (key) {
                var item = {
                    default: key,
                    edited: dataTranslated.data[key],
                    domain:dataTranslated.domain,
                    locale: etsTransFunc.getParameterByName('locale'),
                };
                var type = etsTransFunc.getParameterByName('type');
                item.theme =  "";
                if(type == 'themes'){
                    item.theme =  etsTransFunc.getParameterByName('selected');
                }
                dataSend.translations.push(item);
            });
            $.ajax({
                url: dataTranslated.url,
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify(dataSend),
                contentType: "application/json",
                beforeSend: function(){},
                success: function(res){
                    showSuccessMessage(etsTransFunc.trans('translate_updated'));
                    $('.ets-trans-translated-input').removeClass('ets-trans-translated-input');
                },
                complete: function(){}
            });
        },

        addBtnTranslateItem: function(){
            if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                $('form[isedited] textarea').each(function () {
                    if (!$(this).parent().find('.js-ets-trans-btn-inter-trans-field-item').length) {
                        var dataField = $(this).prev('label').text().replace(/"/g, '"');
                        $(this).after(etsAdminInterTrans.renderBtnTransFieldItem(dataField, 'ml-2 mt-3', 1));
                        etsAdminInterTrans.theme.hideBtnResetItem(this);
                        etsAdminInterTrans.theme.setResetBtn(this);
                    }
                    $("[data-toggle=tooltip]").bstooltip();
                });
            }
            var iterval = setInterval(function () {
                if($('form[isedited]').find('button[type=submit]').length){
                    clearInterval(iterval);
                    if($('.toolbar-icons a').length){
                        if(!$('form[isedited]').find('.js-ets-trans-btn-inter-trans-toolbar').length)
                            $('form[isedited]').find('button[type=submit]').last().after(etsAdminInterTrans.renderBtnTranslate('', false, etsTransFunc.trans('translate_fields'))).addClass('trans_right');
                        $('.translations-header h1').append(etsAdminInterTrans.renderBtnTransNewSystemAll('', false, etsAdminInterTrans.trans('translate')));
                        $('.translations-header h1').append(etsAdminInterTrans.renderBtnResetAllTrans('hide'));
                    }
                }

            }, 500);
            etsAdminInterTrans.theme.getDataNotSave();
        },
        hideBtnResetItem: function(textarea){
            textarea = textarea || null;
            if(textarea){
                $(textarea).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').addClass('hide');
                if(!$('.ets-trans-btn-reset-trans-item-theme:not(.hide)').length){
                    if(!$('.js-ets-trans-btn-inter-trans-reset-all').hasClass('hide')){
                        $('.js-ets-trans-btn-inter-trans-reset-all').addClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').removeClass('show_reset');
                    }
                }
                return false;
            }
            $('form[isedited] textarea').each(function(){
                $(this).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').addClass('hide');
            });
            if(!$('.ets-trans-btn-reset-trans-item-theme:not(.hide)').length){
                if(!$('.js-ets-trans-btn-inter-trans-reset-all').hasClass('hide')){
                    $('.js-ets-trans-btn-inter-trans-reset-all').addClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').removeClass('show_reset');
                }
            }
        },

        setResetBtn: function(textarea){
            textarea = textarea || null;
            if(textarea){
                $(textarea).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').addClass('ets-trans-btn-reset-trans-item-theme').attr({
                    'data-title': etsTransFunc.trans('reset_trans'),
                    'title': etsTransFunc.trans('reset_trans'),
                }).html('<i class="ets-trans-icon-translate fa fa-refresh"></i>');
                return false;
            }
            $('form[isedited] textarea').each(function(){
                $(this).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').addClass('ets-trans-btn-reset-trans-item-theme').attr({
                    'data-title': etsTransFunc.trans('reset_trans'),
                    'title': etsTransFunc.trans('reset_trans'),
                }).html('<i class="ets-trans-icon-translate fa fa-refresh"></i>');
            });
        },
        showBtnResetItem: function(textarea){
            return false;
            textarea = textarea || null;
            if($('form[isedited] textarea').length > 1){
                $('.js-ets-trans-btn-inter-trans-reset-all').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
            }
            if(textarea){
                $(textarea).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
                return false;
            }
            $('form[isedited] textarea').each(function(){
                $(this).parent().find('button:not(.js-ets-trans-btn-inter-trans-field-item)').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
            });
        },
        onChangeForm: function(){
            setTimeout(function(){
                var checkFormTranslateLoaded = setInterval(function(){
                    if($('form[isedited]').length){
                        etsAdminInterTrans.saveOldDataTrans();
                        etsAdminInterTrans.theme.addBtnTranslateItem();
                        clearInterval(checkFormTranslateLoaded);
                    }
                }, 400);
            }, 1000);
        }
    },
    email: {
        addBtnTranslate: function(){
            $('#toolbar-nav').prepend(etsAdminInterTrans.renderBtnTranslate2('trans-email toolbar_btn', true));
            $('#toolbar-nav').prepend(etsAdminInterTrans.renderBtnResetAllTrans('trans-email toolbar_btn', true));
            //
            if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                $('#translations_form .panel.translations-email-panel .panel-title').after(etsAdminInterTrans.renderBtnTranslateItem('trans-email', false));
            }
            setTimeout(function(){
                etsAdminInterTrans.email.saveDataReset();
            }, 500);
        },
        getFormData: function(type, isTransAll, mailOptions, fieldTrans){
            if(typeof tinyMCE !== 'undefined'){
                tinyMCE.triggerSave();
            }

            var formData = {};
            mailOptions = mailOptions || null;
            fieldTrans = fieldTrans || null;
            if(isTransAll == 1){

                var limitItem = etsAdminInterTrans.stepTrans;
                etsAdminInterTrans.itemTranslateQueue = [];
                if(mailOptions && mailOptions.length){
                    $(mailOptions).each(function(index, item){
                        var itemKey = item;
                        var itemName = item.replace('core_mail[', 'core_mail[ext][');
                            itemName = itemName.replace('module_mail[', 'module_mail[ext][');
                        var itemVal = itemKey.replace(/\]/g,'').replace(/\[/g, '|');
                        if(index < (limitItem)){
                            if(type == 'source') {
                                formData[encodeURI(itemKey)] = itemVal;
                            }
                            else{
                                formData[encodeURI(itemKey)] = {
                                    txt: $('textarea[name="'+itemName.replace('[ext]', '[txt]')+'"]').val(),
                                    html: $('textarea[name="'+itemName.replace('[ext]', '[html]')+'"]').val(),
                                };
                            }
                        }
                        else{
                            etsAdminInterTrans.itemTranslateQueue.push({
                                el: itemKey,
                                key: encodeURI(itemKey),
                                item: itemVal
                            });
                        }
                    });
                }
                else{

                    $('#translations_form textarea[class*="rte"]:not(.ets-trans-translated)').each(function(index){
                        var itemKey = $(this).attr('name');
                        var itemVal = itemKey.replace(/\]/g,'').replace(/\[/g, '|');
                        if(index < (limitItem)){
                            if(type == 'source') {
                                formData[encodeURI(itemKey)] = itemVal;
                            }
                            else{
                                formData[encodeURI(itemKey)] = $(this).val();
                            }
                        }
                        else {
                            etsAdminInterTrans.itemTranslateQueue.push({
                                el: 'textarea[name="' + itemKey + '"]',
                                key: encodeURI(itemKey),
                                item: itemVal
                            });
                        }
                    });
                }
            }
            else{
                if(etsAdminInterTrans.emailItemActive){
                    etsAdminInterTrans.emailItemActive.find('textarea[class*="rte"]').each(function () {
                        if(type == 'source') {
                            formData[encodeURI($(this).attr('name'))] = $(this).attr('name').replace(/\]/g,'').replace(/\[/g, '|');
                        }
                        else{
                            formData[encodeURI($(this).attr('name'))] = $(this).val();
                        }
                    });
                }
                else{
                    if($('#translation_mails-control-actions').closest('.email-collapse').find('textarea[class*="rte"]').length){
                        $('#translation_mails-control-actions').closest('.email-collapse').find('textarea[class*="rte"]').each(function(){
                            if(type == 'source') {
                                formData[encodeURI($(this).attr('name'))] = $(this).attr('name').replace(/\]/g,'').replace(/\[/g, '|');
                            }
                            else{
                                formData[encodeURI($(this).attr('name'))] = $(this).val();
                            }
                        });
                    }
                }
            }

            return formData;
        },
        saveDataReset: function(){
            etsAdminInterTrans.oldDataTrans = {};
            $('form#translations_form').find('input[type=text], textarea').each(function(){
                etsAdminInterTrans.oldDataTrans[$(this).attr('name')] = $(this).val();
            });
        },
        resetTransFieldItem: function(field){
            var fields = field.trim().split(' ');
            $.each(fields, function(i, el){
                if(typeof etsAdminInterTrans.oldDataTrans[el] !== 'undefined'){
                    var emailEl = $('#translations_form').find('textarea[name="'+el+'"]');
                    emailEl.val(etsAdminInterTrans.oldDataTrans[el]);
                    etsAdminInterTrans.email.setContentIframe(el, etsAdminInterTrans.oldDataTrans[el]);
                    if(typeof tinyMCE !== 'undefined' && emailEl.hasClass('rte-mail')){
                        var mceItem = tinyMCE.get(el) || null;
                        if(mceItem){
                            mceItem.setContent(etsAdminInterTrans.oldDataTrans[el]);
                        }
                    }
                }
            });
        },
        setContentIframe: function(key, mailContent){
            var dstFrame = $('textarea[name="'+key+'"]').closest('.tab-content').find('.email-html-frame > .email-frame')[0];
            if(!dstFrame)
            {
                return;
            }
            var dstDoc = dstFrame.contentDocument || dstFrame.contentWindow.document;
            dstDoc.write(mailContent);
            dstDoc.close();
        },
        getTotalEmail: function(){
            return $('.translations-email-panel').length;
        },
        getMailOption: function(offset){
            offset = offset || 0;
            var options = [];
            $('#translations_form textarea[class*="rte"]:not(.ets-trans-translated)').each(function(index){
                if(index >= offset){
                    options.push($(this).attr('name'));
                }
            });
        },
        showBtnReset: function(itemClick){
            return false;
            $(itemClick).parent().find('.js-ets-trans-btn-inter-reset-field-item').removeClass('hide');
            $('.js-ets-trans-btn-inter-trans-reset-all').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
        },
        hideBtnReset: function(itemClick){
            $(itemClick).addClass('hide');
            $('.js-ets-trans-btn-inter-trans-reset-all').addClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').removeClass('show_reset');
        },
    },
    module: {
        addBtnTranslate: function(){
            $('#toolbar-nav').prepend(etsAdminInterTrans.renderBtnTranslate2('trans-module toolbar_btn', true));
            $('#toolbar-nav').prepend(etsAdminInterTrans.renderBtnResetAllTrans('trans-module toolbar_btn', true));

            $('#translations_form .panel-footer').each(function(){
                if(!$(this).find('.js-ets-trans-btn-inter-trans-item').length && !$(this).find('#buttonall').length){
                    $(this).append(etsAdminInterTrans.renderBtnTranslateItem('trans-module pull-right'));
                    var boxId = $(this).closest('.panel').find('div[name="modules_div"]').attr('id');
                    $(this).append(etsAdminInterTrans.renderBtnResetTransBoxItem(boxId, 'trans-module pull-right'));
                }
            });
            if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                $('#translations_form input[type=text], #translations_form textarea').each(function () {
                    $(this).after(etsAdminInterTrans.renderBtnResetTransItem($(this).attr('name'), 'ml-1 trans-module'));
                    $(this).after(etsAdminInterTrans.renderBtnTransFieldItem($(this).attr('name'), 'ml-1 trans-module btn-default'));
                    $(this).parent().addClass('ets-trans-d-flex');
                });
            }
            etsAdminInterTrans.module.saveDataReset();
        },
        getFormData: function(type, isTransAll, options, fieldTrans){
            var formData = {};
            fieldTrans = fieldTrans || null;
            if(fieldTrans){
                var itemTrans = $('#translations_form').find('[name="'+fieldTrans+'"]');
                if(type == 'source'){
                    var sourceVal = itemTrans.closest('tr').find('td:first-child').text();
                    formData[fieldTrans] = sourceVal.replace(/(\[\w+\])/g, '<span class="notranslate">$1</span>');
                }
                else{
                    formData[fieldTrans] = itemTrans.val();
                }

                return formData;
            }
            if(isTransAll != 1 && etsAdminInterTrans.moduleFieldActive){
                var fieldActive = etsAdminInterTrans.moduleFieldActive;
                etsAdminInterTrans.moduleQueueTrans = [];
                fieldActive.find('input[type=text], textarea').each(function(index){

                    if(index >= (etsAdminInterTrans.limitTransModule-1)){
                        etsAdminInterTrans.moduleQueueTrans.push($(this).attr('name'));
                    }
                    else{
                        if(type == 'source') {
                            var sourceVal = $(this).closest('tr').find('td:first-child').text();
                            formData[$(this).attr('name')] = sourceVal.replace(/(\[\w+\])/g, '<span class="notranslate">$1</span>');
                        }
                        else{
                            formData[$(this).attr('name')] = $(this).val();
                        }
                    }

                });
            }
            else if(isTransAll == 1){
                $('#translations_form').find('input[type=text], textarea').each(function(index){

                    if(index >= (etsAdminInterTrans.limitTransModule-1)){
                        etsAdminInterTrans.moduleQueueTrans.push($(this).attr('name'));
                    }
                    else{
                        if(type == 'source') {
                            var sourceVal = $(this).closest('tr').find('td:first-child').text();

                            formData[$(this).attr('name')] = sourceVal.replace(/(\[\w+\])/g, '<span class="notranslate">$1</span>');
                        }
                        else{
                            formData[$(this).attr('name')] = $(this).val();
                        }
                    }
                });
            }
            return formData;
        },

        saveDataReset: function(){
            etsAdminInterTrans.oldDataTrans = {};
            $('form#translations_form').find('input[type=text], textarea').each(function(){
                etsAdminInterTrans.oldDataTrans[$(this).attr('name')] = $(this).val();
            })
        },
        resetTransFieldItem: function(field){
            if(typeof etsAdminInterTrans.oldDataTrans[field] !== "undefined")
            $('form#translations_form').find('[name="'+field+'"]').val(etsAdminInterTrans.oldDataTrans[field]);
        },
        continueTrans: function(formData){
            var queueData = etsAdminInterTrans.moduleQueueTrans.slice();
            var transSource = {};
            var transTarget = {};

            $.each(queueData, function(i, el){
                if(i < etsAdminInterTrans.limitTransModule){
                    transSource[el] = $('[name="'+el+'"]').closest('tr').find('td:first-child').text();
                    transTarget[el] = $('[name="'+el+'"]').val();
                    etsAdminInterTrans.moduleQueueTrans.shift();
                }
                else{
                    return false;
                }
            });
            var dataTarget = etsAdminInterTrans.formatTransData(transSource, transTarget, formData.trans_option);
            formData.trans_data.source = transSource;
            Object.keys(formData.trans_data.target).forEach(function(idLang){
                formData.trans_data.target[idLang] = dataTarget;
            });
            return formData;
        },
        getTotalText: function() {
            return $('form#translations_form').find('input[type=text], textarea').length;
        },
        setTextTranslated: function(moduleName, fileName, textTranslated, textKey){
            moduleName = moduleName || null;
            fileName = fileName || null;
            textTranslated  = textTranslated || null;
            var langTarget = null;
            if(typeof etsTransLangTargetInterTrans !== 'undefined' && etsTransLangTargetInterTrans){
                langTarget = etsTransLangTargetInterTrans;
            }
            if(!moduleName || !fileName || !textTranslated || !langTarget){
                return;
            }
            Object.keys(textTranslated).forEach(function(key){
                var setTextSuccess = false;
                $('#_'+moduleName+'_'+fileName).find('table tr>td:contains("'+etsTransFunc.formatTextContains(textKey[key])+'")').each(function(){
                    if($(this).text() == textKey[key]){
                        setTextSuccess = true;
                        $(this).closest('tr').find('input[type="text"], textarea').val(textTranslated[key][langTarget]);
                        $(this).closest('tr').find('input[type="text"], textarea').addClass('input-translated');
                    }
                });
                if(!setTextSuccess){
                    var textFind = textKey[key].replace(/\\\'/g, '\'');

                    $('#_'+moduleName+'_'+fileName).find('table tr>td:contains("'+textFind+'")').each(function(){
                        if($(this).text() == textFind){
                            setTextSuccess = true;
                            $(this).closest('tr').find('input[type="text"], textarea').val(textTranslated[key][langTarget]);
                            $(this).closest('tr').find('input[type="text"], textarea').addClass('input-translated');
                        }
                    });
                }
            });
        },
        showBtnReset: function(itemClick){
            return false;
            $(itemClick).parent().find('.js-ets-trans-btn-inter-reset-field-item').removeClass('hide');
            $(itemClick).closest('.panel').find('.js-ets-trans-btn-inter-reset-box-item').removeClass('hide');
            $('.js-ets-trans-btn-inter-trans-reset-all').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
        },
        hideBtnReset: function(itemClick){
            $(itemClick).addClass('hide');
            if(!$('.js-ets-trans-btn-inter-reset-field-item:not(.hide)').length){
                $('.js-ets-trans-btn-inter-trans-reset-all').addClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').removeClass('show_reset');
                $('input[type=text],textarea').removeClass('input-translated');
            }

            if($(itemClick).hasClass('js-ets-trans-btn-inter-reset-field-item')){
                $(itemClick).parent().find('input[type=text],textarea').removeClass('input-translated');
                if(!$(itemClick).closest('.panel').find('.js-ets-trans-btn-inter-reset-field-item:not(.hide)').length){
                    $(itemClick).closest('.panel').find('.js-ets-trans-btn-inter-reset-box-item').addClass('hide');
                    $(itemClick).closest('.panel').find('input[type=text],textarea').removeClass('input-translated');
                }
            }

        },
        showAllBtnReset: function(){
            return false;
            $('.js-ets-trans-btn-inter-reset-box-item').removeClass('hide');
            $('.js-ets-trans-btn-inter-trans-reset-all').removeClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').addClass('show_reset');
        },
        hideAllBtnReset: function(){
            $('.js-ets-trans-btn-inter-reset-box-item').addClass('hide');
            $('.js-ets-trans-btn-inter-trans-reset-all').addClass('hide').prev('.js-ets-trans-btn-inter-trans-field-item').removeClass('show_reset');
            $('.js-ets-trans-btn-inter-reset-field-item').addClass('hide');
        },
    },
    getFormSelectTranslate: function(type, isTransAll, fieldTrans, btnClicked, resetTrans)
    {
        if(etsTransFunc.getParameterByName('lang') == 'en'){
            showSuccessMessage(etsTransFunc.trans('not_need_translate'));
            return false;
        }
        btnClicked = btnClicked || null;
        etsAdminInterTrans.stopTranslate = false;
        var totalItems = 0;
        if(type == 'email'){
            totalItems = etsAdminInterTrans.email.getTotalEmail();
        }
        else if(type == 'module'){
            totalItems = etsAdminInterTrans.module.getTotalText();
        }
        var selectedTheme = '';
        if(type == 'email'){
            selectedTheme = etsTransFunc.getParameterByName('selected-theme');
        }
        else{
            selectedTheme = etsTransFunc.getParameterByName('selected');
        }
        $.ajax({
            url: ETS_TRANS_LINK_AJAX_MODULE,
            type: 'GET',
            dataType: 'json',
            data: {
                etsTransGetFormInterTrans: 1,
                pageType: type || '',
                isTransAll: isTransAll || 0,
                selectedTheme: selectedTheme,
                fieldTrans: fieldTrans || '',
                totalItems: totalItems,
                resetTrans: resetTrans || 0,
                sfType: etsTransFunc.getParameterByName('type'),
                langCodeTarget: etsTransFunc.getParameterByName('lang'),
                moduleName: etsTransFunc.getParameterByName('module')
            },
            beforeSend: function(){
                if(btnClicked){
                    $(btnClicked).addClass('loading');
                    $(btnClicked).prop('disabled', true);
                }
            },
            success: function(res){
                if(res.success){
                    $('#etsTransModalTrans').remove();
                    if($('#content.bootstrap').length){
                        $('#content.bootstrap').append(res.form);
                    }
                    else{
                        $('body').append(res.form);
                    }
                    etsTransFunc.showPopupTrans();
                    if(type == 'module'){
                        $('#etsTransModalTrans .total_items').html(etsAdminInterTrans.module.getTotalText());
                    }
                }
            },
            complete: function(){
                if(btnClicked){
                    $(btnClicked).removeClass('loading');
                    $(btnClicked).prop('disabled', false);
                }
            }
        });
    },
    trans: function(key){
        return etsTransText[key] || key;
    },
    getTransDataPage: function(lang_source, langs_target, transOption, type, isTransAll, mailOptions, fieldTrans){

        var transData = {};
        var dataTransSource;
        fieldTrans = fieldTrans || null;
        if(type == 'email'){
            dataTransSource = etsAdminInterTrans[type].getFormData('source', isTransAll, mailOptions, fieldTrans);
        }
        else{
            dataTransSource = etsAdminInterTrans[type].getFormData('source', isTransAll, [], fieldTrans);
        }
        if(!dataTransSource)
        {
            return transData;
        }
        transData.source = dataTransSource;
        transData.target = {};
        if(type !== 'email')
        {
            $.each(langs_target, function(i, id_lang){
                var dataTarget = etsAdminInterTrans[type].getFormData('target', isTransAll, [], fieldTrans);
                var targetOption = etsAdminInterTrans.formatTransData(dataTransSource, dataTarget, transOption);

                transData.target[id_lang] = targetOption;
            });
        }
        else{
            $.each(langs_target, function(i, id_lang){
                transData.target[id_lang] = etsAdminInterTrans[type].getFormData('target', isTransAll, mailOptions, fieldTrans);
            });
        }

        return transData;
    },
    formatTransData: function(dataTransSource, dataTarget, transOption){
        var targetOption = {};
        if(transOption == 'both'){
            Object.keys(dataTarget).forEach(function(key){
                if((dataTarget[key].trim().toLowerCase() == dataTransSource[key].trim().toLowerCase() || dataTarget[key].trim() == '') && dataTransSource[key].trim()){
                    targetOption[key] = 1;
                }
                else{
                    targetOption[key] = 0;
                }
            });
        }
        else if (transOption == 'only_empty'){
            Object.keys(dataTarget).forEach(function(key){
                if(dataTarget[key].trim() == ''){
                    targetOption[key] = 1;
                }
                else{
                    targetOption[key] = 0;
                }
            });
        }
        else if (transOption == 'same_source'){
            Object.keys(dataTarget).forEach(function(key){
                if(dataTarget[key].trim().toLowerCase() == dataTransSource[key].trim().toLowerCase()){
                    targetOption[key] = 1;
                }
                else{
                    targetOption[key] = 0;
                }
            });
        }
        else if (transOption == 'all'){
            Object.keys(dataTarget).forEach(function(key){
                targetOption[key] = 1;
            });
        }
        return targetOption;
    },
    getKeyTrans: function(el){
        var keyTrans = null;
        $.each(el.attributes, function() {
            if(this.specified && this.name.indexOf('data-v') !== -1) {
                keyTrans = this.name;
                return false;
            }
        });
        return keyTrans;
    },
    stopTranslatePage: function(){
        if(etsAdminInterTrans.ajaxXhrTranslatePage && etsAdminInterTrans.ajaxXhrTranslatePage.readyState != 4){
            etsAdminInterTrans.ajaxXhrTranslatePage.abort();
        }
        etsAdminInterTrans.stopTranslate = true;
    },
    closeTranslate: function(){
        $('#etsTransModalTrans').removeClass('translating');
        etsTransFunc.hidePopupTrans();
        $('#etsTransModalTrans .js-ets-trans-btn-strop-translate').remove();
    },
    translatePage: function(formData, buttonTrans, pageType, initLoad){
        if(etsTransFunc.getParameterByName('lang') == 'en'){
            showSuccessMessage(etsTransFunc.trans('not_need_translate'));
            return false;
        }

        initLoad = initLoad || 0;
        var originalText = etsTransFunc.trans('translate');
        etsAdminInterTrans.ajaxXhrTranslatePage = $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            dataType: 'json',
            data: {
                etsTransTranslatePage: 1,
                pageType: pageType || 'theme',
                isDetailPage: 1,
                formData: formData,
                initTranslate: initLoad,
                sfType: etsTransFunc.getParameterByName('type'),
                adminFD: ETS_ADMIN_FD,
            },
            beforeSend: function(){
                //
                if(pageType == 'email' && formData.trans_all != 1 && !buttonTrans.hasClass('trans-email')){
                    etsTransFunc.showTranslatingField();
                }
                else if(pageType == 'theme' || (pageType == 'module' && formData.trans_all != 1) || buttonTrans.hasClass('trans-email')){
                    buttonTrans.addClass('loading');
                    buttonTrans.prop('disabled', true);
                }
            },
            success: function(res){
                if(res.success){
                    var transResult = res.trans_data || {};
                    Object.keys(transResult).forEach(function(idLang){
                        Object.keys(transResult[idLang]).forEach(function(key){
                            if(pageType == 'email'){
                                var mailContent = transResult[idLang][key] || '';
                                if(!$('textarea[name="'+key+'"]').hasClass('rte-mail')){
                                    mailContent = mailContent.replace(/<br\/>/g, "\n");
                                }
                                key = decodeURI(key);
                                $('textarea[name="'+key+'"]').val(mailContent);
                                if($('textarea[name="'+key+'"]').hasClass('rte-mail')){
                                    etsAdminInterTrans.email.setContentIframe(key, mailContent);
                                    if(typeof tinyMCE !== 'undefined' && typeof tinyMCE.get(key) !== 'undefined'){
                                        $('textarea[name="'+key+'"]').prev().find('iframe').next('div').hide();
                                        tinyMCE.get(key).setContent(mailContent);
                                    }
                                }
                                etsAdminInterTrans.email.showBtnReset($('#translation_mails-control-actions .js-ets-trans-btn-inter-trans-item')[0]);
                            }
                            else if(pageType == 'module' ){
                                if(formData.trans_all != 1){
                                    $('input[name="'+key+'"]').val(transResult[idLang][key].replace(/(<span class="notranslate">)(\[\w+\])(<\/span>)/g, '$2'));
                                    $('textarea[name="'+key+'"]').val(transResult[idLang][key].replace(/(<span class="notranslate">)(\[\w+\])(<\/span>)/g, '$2'));
                                    $('input[name="'+key+'"]').addClass('input-translated');
                                    $('textarea[name="'+key+'"]').addClass('input-translated');
                                    etsAdminInterTrans.module.showBtnReset($('input[name="'+key+'"],textarea[name="'+key+'"]').closest('tr').find('.js-ets-trans-btn-inter-trans-field-item')[0]);
                                }
                            }
                            else{
                                var textInput = null;
                                $('form[isedited] label:contains("'+etsTransFunc.formatTextContains(key)+'")').each(function(){
                                    if($(this).text().trim() == key){
                                        textInput = $(this).next('textarea');
                                    }
                                });
                                if(textInput){
                                    textInput.val( transResult[idLang][key]);
                                    etsAdminInterTrans.makeHighlightInput( textInput);
                                    etsAdminInterTrans.theme.showBtnResetItem(textInput[0]);
                                }
                            }
                        });
                    });

                    if(pageType == 'theme' && formData.trans_all != 1){
                        etsAdminInterTrans.theme.getDataNotSave();
                        etsAdminInterTrans.theme.saveDataTranslated();
                    }

                    var showMessage = true;
                    var nbTranslated = res.trans_data.nb_translated || 0;
                    var nbCharTranslated = res.trans_data.nb_char_translated || 0;
                    if(pageType == 'email'){
                        if(etsAdminInterTrans.itemTranslateQueue.length){
                            var nbTranslated = res.trans_data.nb_translated || 0;
                            formData = etsAdminInterTrans.getNextDataTrans(formData);
                            formData.nb_char_translated = parseInt(formData.nb_char_translated) + nbCharTranslated;
                            formData.nb_translated = parseInt(formData.nb_translated) + parseInt(nbTranslated);
                            etsTransFunc.updateDataTranslating(formData.nb_translated, formData.nb_char_translated);
                            return etsAdminInterTrans.translatePage(formData, buttonTrans, pageType);
                        }
                    }
                    else if(pageType == 'module' || pageType == 'theme'){
                        showMessage = false;
                        if(formData.trans_all == 1){
                            var afterInit = res.trans_data.after_init || 0;
                            var initStep = res.trans_data.step || 1;
                            var doneInit = 1;
                            if(typeof res.trans_data.done !== 'undefined'){
                                doneInit = res.trans_data.done;
                            }
                            formData.nb_translated = nbTranslated + parseInt(formData.nb_translated);
                            formData.nb_char_translated = nbCharTranslated + parseInt(formData.nb_char_translated);
                            if(afterInit){
                                if(doneInit){
                                    var dataResume = {
                                        pageType: pageType,
                                        nbTranslated: 0,
                                        nbCharTranslated: 0,
                                        langSource: formData.trans_source,
                                        langTarget: formData.trans_target,
                                        fieldOption: formData.trans_option
                                    };
                                    var totalText = 0;
                                    if(pageType == 'module'){
                                        totalText = etsAdminInterTrans.module.getTotalText();
                                    }
                                    etsTransFunc.setResumeTranslate(dataResume);
                                    etsTransFunc.updateDataTranslating(formData.nb_translated, formData.nb_char_translated, totalText);
                                    etsAdminInterTrans.translatePage(formData, buttonTrans, pageType, 0);
                                }
                                else{
                                    formData.init_step = parseInt(initStep) + 1;
                                    etsAdminInterTrans.translatePage(formData, buttonTrans, pageType, 1);
                                }
                            }
                            else{
                                if(pageType == 'module') {
                                    etsAdminInterTrans.module.setTextTranslated(formData.module_name, res.trans_data.file_name, res.trans_data.text_translated, res.trans_data.text_key);
                                }
                                etsTransFunc.updateDataTranslating(formData.nb_translated, formData.nb_char_translated);
                                var stopTrans = res.trans_data.stop_translate || 0;

                                if(stopTrans == 1){
                                    showMessage = true;
                                    etsTransFunc.setTranslateDone();
                                }
                                else{
                                    etsAdminInterTrans.translatePage(formData, buttonTrans, pageType, 0);
                                }
                            }
                            etsAdminInterTrans.formSaved = true;
                        }
                        else{
                            if(pageType == 'module'){
                                if(!etsAdminInterTrans.moduleQueueTrans.length){
                                    showMessage = true;
                                    etsAdminInterTrans.formSaved = false;
                                }
                                else{

                                    formData = etsAdminInterTrans.module.continueTrans(formData);
                                    etsAdminInterTrans.translatePage(formData,buttonTrans, pageType);
                                }
                            }
                            else {
                                showMessage = true;
                                etsAdminInterTrans.translateThemeSaved = false;
                            }
                        }
                    }
                    if(showMessage) {
                        if(pageType == 'email'){

                            var nbTranslated = res.trans_data.nb_translated || 0;
                            var nbCharTranslated = res.trans_data.nb_char_translated || 0;

                            etsAdminInterTrans.deleteDataPause('email');
                            etsTransFunc.updateDataTranslating(parseInt(formData.nb_translated) + parseInt(nbTranslated), parseInt(formData.nb_char_translated) + parseInt(nbCharTranslated));
                            etsTransFunc.setTranslateDone();
                        }
                        if(pageType == 'theme' || (pageType == 'module' && formData.trans_all != 1) || buttonTrans.hasClass('trans-email')){
                            buttonTrans.removeClass('loading');
                            buttonTrans.prop('disabled', false);
                        }
                        showSuccessMessage(res.message);
                        etsAdminInterTrans.closeTranslate();

                    }
                }
                else{
                    if(res.errors){
                        var sMsg = 0;
                        if(pageType == 'theme' || (pageType == 'module' && formData.trans_all != 1)){
                            buttonTrans.removeClass('loading');
                            buttonTrans.prop('disabled', false);
                            showErrorMessage(res.errors);
                            sMsg = 1;
                        }
                        etsTransFunc.showErrorTrans(res.errors);
                        etsAdminInterTrans.closeTranslate();
                        if(pageType == 'theme' || formData.trans_all == 1){
                            etsTransFunc.setTranslateError(res.errors);
                            if(!sMsg){
                                showErrorMessage(res.errors);
                            }
                        }
                    }
                }
            },
            complete: function(){
                if(formData.trans_all != 1){
                    etsTransFunc.hideTranslatingField();
                }
            },
            error: function(){
                if(pageType == 'email' && formData.trans_all != 1){
                    etsTransFunc.showTranslatingField();
                }
                etsAdminInterTrans.closeTranslate();
                buttonTrans.removeClass('active');
                buttonTrans.prop('disabled', false);
                buttonTrans.find('.text-btn-translate').html(originalText);
                etsAdminInterTrans.formSaved = false;
            }
        });
        return etsAdminInterTrans.ajaxXhrTranslatePage;
    },
    getNextDataTrans: function(formData){
        var itemTrans = {};
        var currentVal = {};
        var tmpQueue = etsAdminInterTrans.itemTranslateQueue.slice();
        etsAdminInterTrans.mailQueueTranslating = [];
        $(tmpQueue).each(function (index, item) {
            if(index < etsAdminInterTrans.stepTrans){
                itemTrans[item.key] = item.item;
                var txtName = item.el;
                if(item.el.indexOf('core_mail[txt]') === -1)
                    txtName = item.el.replace('core_mail[', 'core_mail[txt][');
                if(item.el.indexOf('module_mail[txt]') === -1)
                    txtName = item.el.replace('module_mail[', 'module_mail[txt][');

                var htmlName = item.el;
                if(item.el.indexOf('core_mail[txt]') === -1)
                    txtName = item.el.replace('core_mail[', 'core_mail[html][');
                if(item.el.indexOf('module_mail[txt]') === -1)
                    txtName = item.el.replace('module_mail[', 'module_mail[html][');

                currentVal[item.key] = {
                    txt: $('textarea[name="'+txtName+'"]').val() || '',
                    html: $('textarea[name="'+htmlName+'"]').val() || '',
                };
                etsAdminInterTrans.mailQueueTranslating.push(item);
                etsAdminInterTrans.itemTranslateQueue.shift();
            }
            else{
                return false;
            }
        });
        formData.trans_data.source = itemTrans;
        Object.keys(formData.trans_data.target).forEach(function(idLang){
            formData.trans_data.target[idLang] = currentVal;
        });
        return formData;
    },
    makeHighlightInput: function(el){
        el.addClass('ets-trans-translated-input');
    },
    removeHighlightInput: function(el){
        el.removeClass('ets-trans-translated-input');
    },
    saveOldDataTrans: function(){
        etsAdminInterTrans.oldDataTrans = {};
        $('form[isedited] textarea').each(function(){
           var keyTrans = $(this).prev('label').text();
           etsAdminInterTrans.oldDataTrans[keyTrans] = $(this).val();
        });
    },
    initInterTrans: function(){
      var checkFormTranslateLoaded = setInterval(function(){
          if($('form[isedited]').length){
              etsAdminInterTrans.saveOldDataTrans();
              etsAdminInterTrans.theme.addBtnTranslateItem();
              clearInterval(checkFormTranslateLoaded);
          }
      }, 400);

      // For email

    },
    resetAllTrans: function(type){

        var countText = 0;
        var dataTrans = etsAdminInterTrans.oldDataTrans;
        Object.keys(dataTrans).forEach(function(key){
            if(type == 'theme'){
                var inputTxt = null;
                $('form[isedited] label:contains("'+etsTransFunc.formatTextContains(key)+'")').each(function(){
                    if($(this).text().trim() == key){
                        inputTxt = $(this).next('textarea');
                    }
                });
                if(inputTxt){
                    inputTxt.val(dataTrans[key]);
                    etsAdminInterTrans.theme.hideBtnResetItem(inputTxt[0]);
                }
            }
            else if(type == 'module'){
                $('form#translations_form').find('[name="'+key+'"]').val(dataTrans[key]);
            }else if(type == 'email'){
                var emailEl = $('form#translations_form').find('textarea[name="'+key+'"]');

                emailEl.val(dataTrans[key]);
                etsAdminInterTrans.email.setContentIframe(key, dataTrans[key]);
                if(typeof tinyMCE !== "undefined" && emailEl.hasClass('rte-mail')){
                    var textEditor = tinyMCE.get(key) || null;
                    if(textEditor){
                        textEditor.setContent(dataTrans[key]);
                    }
                }
            }
            countText++;
        });
        $('.ets-trans-translated-input').removeClass('ets-trans-translated-input');
        showSuccessMessage(etsTransFunc.trans('reset_all_trans_success'));
    },

    deleteDataPause: function(type){
        var selected = 0;
        if(type == 'email'){
            selected = etsTransFunc.getParameterByName('selected-theme');
        }
        else if(type == 'module'){
            selected = etsTransFunc.getParameterByName('module');
        }
      $.ajax({
          url: ETS_TRANS_LINK_AJAX,
          type: 'POST',
          data: {
              etsTransDeleteDataPause: 1,
              pageType: type,
              selected_theme: selected
          },
          dataType: 'json',
          success: function(res){}
      })
    },

    actionTranslate: function(btnClick, type){
        btnClick = btnClick || null;
        var afterAnalysis = 0;
        if($(btnClick).hasClass('js-ets-trans-analysis-accept')){
            afterAnalysis = 1;
        }
        type = type || null;
        var isTransAll = 0;
        if(btnClick){
            isTransAll = $(btnClick).attr('data-trans-all') || 0;
        }
        var formData = {};
        if(btnClick) {
            formData = $(btnClick).closest('form').serializeArray();
            formData = etsTransFunc.formatFormData(formData);
        }
        formData.trans_source = etsTransLangSourceDefault || 0;
        if(typeof formData.trans_target == 'string'){
            formData.trans_target = formData.trans_target.split(',');
        }
        var transSource = formData.trans_source || null;
        var transTarget = formData.trans_target || [];
        var transOption = formData.trans_option || 'all';
        var pageType = type;
        if(btnClick && !type) {
            pageType = $(btnClick).attr('data-page-type') || null;
        }
        if(!transTarget.length){
            var idLangTarget = etsTransLangTargetInterTrans || 0;
            if(idLangTarget){
                transTarget.push(idLangTarget);
                formData.trans_target = [idLangTarget];
            }
        }
        $('#etsTransModalTrans .form-errors').html('');
        if(!transTarget || !transTarget.length){
            etsTransFunc.showErrorTrans(etsTransFunc.trans('target_lang_required'));
        }
        var mailOptions = formData.mail_option || [];
        if(afterAnalysis && typeof mailOptions === 'string'){
            mailOptions = mailOptions.split(',');
        }
        var fieldTrans = $(btnClick).attr('data-field') || null;
        formData.mail_option = mailOptions;

        var transData = etsAdminInterTrans.getTransDataPage(transSource, formData.trans_target, transOption, pageType, isTransAll, mailOptions, fieldTrans);

        if((pageType == 'module' || pageType == 'theme') && isTransAll==1){
            transData = {};
        }
        var totalItem = $(btnClick).attr('data-total-item') || 0;
        var selectedTheme = '';
        if(pageType == 'email'){
            selectedTheme = etsTransFunc.getParameterByName('selected-theme') || '';
        }
        else{
            selectedTheme = etsTransFunc.getParameterByName('selected') || '';
        }
        formData.selected_theme = selectedTheme;
        formData.module_name = etsTransFunc.getParameterByName('module') || 0;
        formData.trans_data = transData;
        formData.is_trans_all = isTransAll;
        formData.trans_all = isTransAll;
        formData.nb_char_translated = 0;
        formData.nb_translated = 0;
        formData.nb_money = 0;
        formData.nb_text = 0;
        if(isTransAll ==1){
            if(pageType == 'email' || $(btnClick).hasClass('js-ets-trans-analysis-accept')){
                //totalItem = formData.mail_option.length;
                etsTransFunc.showPopupTranslating(1, pageType, totalItem, transSource, transTarget, transOption);
                var dataResume = {
                    pageType: type,
                    nbTranslated: 0,
                    nbCharTranslated: 0,
                    langSource: transSource,
                    langTarget: transTarget,
                    fieldOption: transOption
                };
                etsTransFunc.setResumeTranslate(dataResume);
            }
            else if (pageType == 'module' || pageType == 'theme'){
                etsTransFunc.showPopupTranslating(1, pageType, totalItem, transSource, transTarget, transOption);
                if(!afterAnalysis) {
                    etsTransFunc.setInitTrans();
                }
            }
        }
        else{
            if(pageType == 'module' && etsAdminInterTrans.moduleFieldActive){
                formData.file_trans = etsAdminInterTrans.moduleFieldActive.find('div[name=modules_div]').attr('id').replace('_'+formData.module_name+'_', '');
            }
            etsTransFunc.showTranslatingField();
        }
        etsTransFunc.hidePopupTrans();
        $('#etsTransPopupAnalysisCompleted').removeClass('active');
        var initLoad = 1;
        if(afterAnalysis && pageType !== 'email'){
            initLoad = 0;
        }

        etsAdminInterTrans.translatePage(formData, $(btnClick), pageType, initLoad);
        return false;
    },
    pauseTranslate: function (btn, data) {
        $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            data: {
                etsTransPauseTranslate: 1,
                transInfo: data
            },
            dataType: 'json',
            beforeSend: function () {
                btn.addClass('loading');
                btn.prop('disabled', true);
            },
            success: function (res) {
                if(res.success){
                    etsTransFunc.setPauseTranslate(data);
                    showSuccessMessage(etsTransFunc.trans('pause_success'));
                }
            },
            complete: function () {
                btn.removeClass('loading');
                btn.prop('disabled', false);
            }
        });
    },
    analysisBeforeTranslate: function(pageType, formData, step, isLoadFile, resetData){
        resetData = resetData || 0;
        $('#etsTransPopupAnalyzing').addClass('active');
        etsTransFunc.hidePopupTrans();
        var selectedItem = etsTransFunc.getParameterByName('selected');
        if(!selectedItem){
            selectedItem = etsTransFunc.getParameterByName('selected-theme');
        }
        if(pageType == 'module'){
            selectedItem = etsTransFunc.getParameterByName('module');
        }
        var sfType = etsTransFunc.getParameterByName('type');;
        etsAdminInterTrans.ajaxXhrTranslatePage = $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            dataType: 'json',
            data: {
                etsTransAnalyzing: 1,
                isLocalization: 1,
                pageType: pageType,
                formData: formData,
                sfType: sfType,
                selected: selectedItem,
                step: step,
                isLoadFile: isLoadFile,
                resetData: resetData
            },
            success: function(res){
                if(res.success){
                    var resData = res.data || {};
                    if(isLoadFile==1){
                        if(resData.load_file_done == 1){
                            isLoadFile = 0;
                            etsAdminInterTrans.analysisBeforeTranslate(pageType, formData, 1, isLoadFile);
                        }
                        else{
                            etsAdminInterTrans.analysisBeforeTranslate(pageType, formData, resData.next_step, isLoadFile);
                        }
                        return;
                    }
                    if(Object.keys(resData).length){
                        formData.nb_text = parseInt(formData.nb_text) + resData.nb_text;
                        formData.nb_char = parseInt(formData.nb_char) + resData.nb_char;
                        formData.nb_money = parseFloat(formData.nb_money) + resData.nb_money;

                        if(pageType == 'email'){
                            $.each(resData.mail_checked, function(i, item){
                                formData.mail_option.splice(formData.mail_option.indexOf(item), 1);
                            });
                            if(resData.mail_valid){
                                $.each(resData.mail_valid, function(i, item){
                                    formData.mailValid.push(item);
                                });
                            }
                        }
                        if(resData.stop != 1){
                            etsAdminInterTrans.analysisBeforeTranslate(pageType, formData, 1, 0);
                        }
                        else{
                            $('#etsTransPopupAnalyzing').removeClass('active');
                            if(pageType == 'email'){
                                formData.mail_option = formData.mailValid;
                            }
                            etsTransFunc.showAnalysisCompleted(pageType, formData, formData.nb_text || 0);
                        }
                    }
                }
                else{
                    $('#etsTransPopupAnalyzing').removeClass('active');
                    if(res.errors){
                        showErrorMessage(res.errors);
                    }
                    else
                        showErrorMessage('Has error');
                }
            },
            complete: function(){

            },
            error: function () {
                $('#etsTransPopupAnalyzing').removeClass('active');
            }
        });
    },
};

$(document).ready(function(){
    jQuery.fn.bstooltip = jQuery.fn.tooltip;
    etsAdminInterTrans.initInterTrans();
    etsAdminInterTrans.theme.addBtnTranslate();
    if(etsTransFunc.getParameterByName('type') == 'mails' || $('.translations-email-panel').length) {
        etsAdminInterTrans.email.addBtnTranslate();
    }
    else if(etsTransFunc.getParameterByName('type') == 'modules') {
        etsAdminInterTrans.module.addBtnTranslate();
    }

    $(document).on('click', '.js-ets-trans-btn-inter-trans-toolbar', function(e){
        e.preventDefault();
        etsAdminInterTrans.stopTranslate = false;
        etsAdminInterTrans.itemTranslateQueue = [];
        etsAdminInterTrans.moduleQueueTrans = [];
        if($(this).hasClass('trans-email')) {
            etsAdminInterTrans.getFormSelectTranslate('email', 1, null, this);
        }
        else if($(this).hasClass('trans-module')) {
            etsAdminInterTrans.getFormSelectTranslate('module', 1, null, this);
        }
        else {
            var formData = {};
            if(ETS_TRANS_IS_AUTO_CONFIG){
                formData.trans_source = etsTransLangSourceDefault || 0;
                var transTarget = etsTransLangTargetInterTrans || 0;
                formData.trans_target = [];
                formData.trans_target.push(transTarget);
                formData.trans_all = 0;
                formData.trans_option = ETS_TRANS_DEFAULT_CONFIG.field_option || [];
                formData.selected_theme = etsTransFunc.getParameterByName('selected-theme');
                formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, formData.trans_option, 'theme', 0);
                etsAdminInterTrans.stopTranslate = false;
                etsAdminInterTrans.translatePage(formData, $(this), 'theme');
            }
            else{
                etsAdminInterTrans.getFormSelectTranslate('theme', 0, null, this);
            }
        }
        return false;
    });

    $(document).on('click', '.js-ets-trans-btn-inter-trans, .js-ets-trans-analysis-accept', function(){

        $('.input-translated').removeClass('input-translated');
        var pageType = $(this).attr('data-page-type') || null;
        etsAdminInterTrans.actionTranslate(this, pageType);
        if($(this).hasClass('js-ets-trans-btn-inter-trans'))
            $('#etsTransModalTrans').modal('hide');
        return false;
    });
    $(document).on('click', '.js-ets-trans-btn-inter-trans-item', function(){
        etsAdminInterTrans.stopTranslate = false;
        etsAdminInterTrans.itemTranslateQueue = [];
        etsAdminInterTrans.moduleQueueTrans = [];
        var formData = {};
        if(ETS_TRANS_IS_AUTO_CONFIG){
            formData.trans_source = etsTransLangSourceDefault || 0;
            var transTarget = etsTransLangTargetInterTrans || 0;
            formData.trans_target = [];
            formData.trans_target.push(transTarget);
            formData.trans_all = 0;
            formData.trans_option = ETS_TRANS_DEFAULT_CONFIG.field_option || [];
            formData.selected_theme = etsTransFunc.getParameterByName('selected-theme');
        }
        if($(this).hasClass('trans-email'))
        {
            etsAdminInterTrans.emailItemActive = $(this).closest('.translations-email-panel');
            if(ETS_TRANS_IS_AUTO_CONFIG){
                formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, ETS_TRANS_DEFAULT_CONFIG.field_option, 'email', 0);
                etsAdminInterTrans.translatePage(formData, $(this), 'email');
            }
            else{
                etsAdminInterTrans.getFormSelectTranslate('email', 0, null, this);
            }
        }
        else if($(this).hasClass('trans-module')){
            etsAdminInterTrans.moduleFieldActive = $(this).closest('.panel');
            if(ETS_TRANS_IS_AUTO_CONFIG){
                formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, ETS_TRANS_DEFAULT_CONFIG.field_option, 'module', 0);
                etsAdminInterTrans.translatePage(formData, $(this), 'module');
            }
            else{
                etsAdminInterTrans.getFormSelectTranslate('module', 0, null, this);
            }
        }
        else {
            etsAdminInterTrans.getFormSelectTranslate('theme', 0, null, this);
        }
        return false;
    });

    $(document).on('click', '.js-ets-trans-btn-inter-trans-reset-all', function () {
        if($(this).hasClass('trans-module')) {
            etsAdminInterTrans.resetAllTrans('module');
            etsAdminInterTrans.module.hideAllBtnReset();
        }
        else if($(this).hasClass('trans-email')) {
            etsAdminInterTrans.resetAllTrans('email');
        }
        else{
            etsAdminInterTrans.resetAllTrans('theme');
        }
        $('.input-translated').removeClass('input-translated');
    });

    $(document).on('click', '.js-ets-trans-btn-inter-reset-field-item', function () {
        var dataField = $(this).attr('data-field');
        if($(this).hasClass('trans-module')) {
            etsAdminInterTrans.module.resetTransFieldItem(dataField);
            etsAdminInterTrans.module.hideBtnReset(this);
        }
        else if($(this).hasClass('trans-email')) {
            etsAdminInterTrans.email.resetTransFieldItem(dataField);
            etsAdminInterTrans.email.hideBtnReset(this);
        }
        showSuccessMessage(etsTransFunc.trans('reset_all_trans_success'));
    });

    $(document).on('click', '.js-ets-trans-btn-inter-reset-box-item', function () {
        if($(this).hasClass('trans-module')) {
            var boxId = $(this).attr('data-box');
            $('#'+boxId).find('input[type=text], textarea').each(function(){
                var fieldName = $(this).attr('name');
                etsAdminInterTrans.module.resetTransFieldItem(fieldName);
                etsAdminInterTrans.module.hideBtnReset($(this).parent().find('.js-ets-trans-btn-inter-reset-field-item')[0]);
            });
            etsAdminInterTrans.module.hideBtnReset(this);
        }
        showSuccessMessage(etsTransFunc.trans('reset_all_trans_success'));
        return false;
    });

    $(document).on('click', '#ps-modal .btn-primary', function(){
        etsAdminInterTrans.theme.saveDataTranslated();
        etsAdminInterTrans.translateThemeSaved = true;
    });
    $(document).on('click', '#ps-modal button', function(){
        etsAdminInterTrans.translateThemeSaved = true;
        etsAdminInterTrans.theme.onChangeForm();
    });
    $(document).on('click', 'form[isedited] button[type=submit]', function(){
        etsAdminInterTrans.translateThemeSaved = true;
        etsAdminInterTrans.theme.hideBtnResetItem($(this).parent().find('textarea')[0]);
        etsAdminInterTrans.theme.getDataNotSave();
        etsAdminInterTrans.theme.saveDataTranslated();
    });

    $(document).on('click', '.translationTree .tree-name', function () {
        if(!etsAdminInterTrans.translateThemeSaved){
            etsAdminInterTrans.theme.getDataNotSave();
            $('#ps-modal').modal('show');
        }
        etsAdminInterTrans.theme.onChangeForm();
    });
    $(document).on('click', '.translations-app .pagination .page-link', function(){
        etsAdminInterTrans.theme.onChangeForm();
    });

    $(document).on('click', '.ets-trans-btn-reset-trans-item-theme', function(){
        $(this).prev().removeClass('show_reset');
    });

    $(document).on('click', '.js-ets-trans-btn-inter-trans-field-item', function(){
        etsAdminInterTrans.stopTranslate = false;
        etsAdminInterTrans.itemTranslateQueue = [];
        etsAdminInterTrans.moduleQueueTrans = [];
        var dataField = $(this).attr('data-field') || '';
        var formData = {};
        formData.trans_option = 'all';
        formData.trans_source = etsTransLangSourceDefault || '';
        formData.trans_target = [etsTransLangTargetInterTrans];
        formData.trans_all = 0;
        formData.nb_char_translated = 0;
        formData.nb_translated = 0;
        formData.selected_theme = etsTransFunc.getParameterByName('selected') || '';
        if($(this).hasClass('trans-module')){
            formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, formData.trans_option, 'module', 0, [],dataField);
            formData.module_name = etsTransFunc.getParameterByName('module') || '';
            formData.file_trans = $(this).closest('div[name=modules_div]').attr('id').replace('_'+formData.module_name+'_', '');
            etsAdminInterTrans.translatePage(formData, $(this), 'module');
        }
        else{
            var fieldTrans =  $(this).parent().find('label').text();
            formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, formData.trans_option, 'theme', 0, [],fieldTrans);
            etsAdminInterTrans.translatePage(formData, $(this), 'theme');
        }
    });

    $(document).on('click', '.js-ets-trans-btn-strop-translate', function () {
        etsAdminInterTrans.stopTranslatePage();
        etsAdminInterTrans.closeTranslate();
    });

    $(document).on('click', '#etsTransMailOption_all', function(){
        if($(this).is(':checked')){
            $('.js-ets-trans-mail-option-item').prop('checked', true);
        }
        else{
            $('.js-ets-trans-mail-option-item').prop('checked', false);
        }

    });

    $(document).on('change', '.js-ets-trans-mail-option-item', function(){
        if($(this).is(':checked')){
            var totalMailOption = $('.js-ets-trans-mail-option-item').length;
            var totalMailOptionChecked = $('.js-ets-trans-mail-option-item:checked').length;
            if(totalMailOption == totalMailOptionChecked){
                $('#etsTransMailOption_all').prop('checked', true);
            }
            else{
                $('#etsTransMailOption_all').prop('checked', false);
            }
        }
        else{
            $('#etsTransMailOption_all').prop('checked', false);
        }
        $('#etsTransFormTransPages .total_items').html($('.js-ets-trans-mail-option-item:checked').length);
    });

    $(document).on('click', '.js-ets-trans-analysis-text', function(){
        var pageType = $(this).attr('data-page-type') || '';
        var formData = $(this).closest('form').serializeArray();
        formData = etsTransFunc.formatFormData(formData);
        formData.trans_source = etsTransLangSourceDefault;
        formData.trans_target = [etsTransLangTargetInterTrans];
        formData.nb_text = 0;
        formData.nb_char = 0;
        formData.nb_money = 0;
        formData.is_trans_all = $(this).attr('data-trans-all') || 0;
        if(typeof formData.trans_target == 'undefined' || !formData.trans_target){
            etsTransFunc.showErrorTrans(etsTransFunc.trans('target_lang_required'));
            return false;
        }
        var isLoadFile = 1;
        if(pageType == 'email') {
            isLoadFile = 0;
        }
        formData.mailValid = [];
        var $this = $(this);
        $.ajax({
            url: ETS_TRANS_LINK_AJAX_MODULE,
            type: 'POST',
            data: {
                etsTransGetFormAnalysis: 1,
            },
            dataType: 'json',
            beforeSend: function () {
                $this.prop('disabled', true);
                $this.addClass('loading');
            },
            success: function (res) {
                if(res.success){
                    $('#etsTransModalTrans').find('.ets-trans-content').html(res.form_html);
                    $('#etsTransModalTrans').find('.js-ets-trans-analysis-text').remove();
                    etsAdminInterTrans.analysisBeforeTranslate(pageType, formData, 1, isLoadFile, 1);
                    $('#etsTransModalTrans .js-ets-trans-analysis-text').addClass('hide');
                }
            },
            complete: function () {
                $this.prop('disabled', false);
                $this.removeClass('loading');
            }
        });
        $(this).parents('.ets-trans-modal').removeClass('ets_modify');
    });

    $(document).on('click', '.js-ets-tran-btn-pause-translate', function () {
        etsAdminInterTrans.stopTranslatePage();
        var dataPause = {};
        dataPause.pageType = $(this).attr('data-page-type') || '';
        dataPause.nbTranslated = $(this).attr('data-nb-translated');
        dataPause.nbCharTranslated = $(this).attr('data-nb-char');
        dataPause.langSource = $(this).attr('data-lang-source');
        dataPause.langTarget = $(this).attr('data-lang-target');
        dataPause.fieldOption = $(this).attr('data-field-option');
        dataPause.mailOption = [];
        if(dataPause.pageType == 'email')
        {
            $.each(etsAdminInterTrans.itemTranslateQueue, function(i, item){
                dataPause.mailOption.push(item.el);
            });
            dataPause.selected_theme = etsTransFunc.getParameterByName('selected-theme');
        }
        else if(dataPause.pageType == 'theme'){
            dataPause.selected_theme = etsTransFunc.getParameterByName('selected');
            dataPause.sfType = etsTransFunc.getParameterByName('type');
        }
        else if(dataPause.pageType == 'module'){
            dataPause.selected_theme = etsTransFunc.getParameterByName('module');
        }

        var itemTranslating = etsAdminInterTrans.mailQueueTranslating;
        if(itemTranslating && itemTranslating.length){
            $.each(itemTranslating, function(i, el){
                dataPause.mailOption.push(el.el);
                etsAdminInterTrans.itemTranslateQueue.push(el);
            });
        }

        etsAdminInterTrans.pauseTranslate($(this), dataPause);
    });

    $(document).on('click', '.js-ets-trans-translate-from-resume', function (e) {
        e.preventDefault();
        etsAdminInterTrans.stopTranslate = false;
        var formData = $(this).closest('form').serializeArray();
        formData = etsTransFunc.formatFormData(formData);
        var selected = etsTransFunc.getParameterByName('selected-theme');
        if(!selected){
            selected = etsTransFunc.getParameterByName('selected');
        }
        formData.trans_target = formData.trans_target.split(',');
        formData.trans_all = 1;
        formData.isDetailPage = 0;
        formData.trans_data = '';
        formData.selected_theme = formData.selected_theme || selected;
        formData.module_name = etsTransFunc.getParameterByName('module') || '';
        if(formData.pageType == 'email'){
            var mailOption = (formData.mail_option || '').split(',');
            if(mailOption && mailOption.length){
                formData.trans_data = etsAdminInterTrans.getTransDataPage(formData.trans_source, formData.trans_target, formData.trans_option, formData.pageType, 1, mailOption);
            }
            else{
                formData.trans_data = {source: {}, target:{}};
            }
        }
        etsTransFunc.showPopupTranslating(1, formData.pageType, formData.total_item, formData.trans_source, formData.trans_target, formData.trans_option);
        etsTransFunc.setResumeTranslate({
            pageType: formData.pageType,
            nbTranslated: formData.nb_translated,
            nbCharTranslated: formData.nb_char_translated,
            langSource: formData.trans_source,
            langTarget: formData.trans_target,
            fieldOption: formData.trans_option,
        });
        etsTransFunc.updateDataTranslating(formData.nb_translated, formData.nb_char_translated, formData.total_item);
        etsAdminInterTrans.translatePage(formData, $(this), formData.pageType);
    });

    $(document).on('click', '.js-ets-trans-translate-from-zero', function (e) {
        e.preventDefault();
        var formData = $(this).closest('form').serializeArray();
        formData = etsTransFunc.formatFormData(formData);
        $('#etsTransModalTrans').modal('hide');
        etsTransFunc.hidePopupTrans();
        etsAdminInterTrans.getFormSelectTranslate(formData.pageType, 1, null, this, 1);
    });

    $(document).on('click', '.js-ets-trans-btn-resume-translate', function () {

        var dataResume = {};
        dataResume.pageType = $(this).attr('data-page-type');
        dataResume.nbTranslated = $(this).attr('data-nb-translated');
        dataResume.nbCharTranslated = $(this).attr('data-nb-char');
        dataResume.langSource = $(this).attr('data-lang-source');
        dataResume.langTarget = $(this).attr('data-lang-target');
        dataResume.fieldOption = $(this).attr('data-field-option');
        etsTransFunc.setResumeTranslate(dataResume);
        var formData = {};
        formData.pageType = dataResume.pageType;
        formData.isDetailPage = 0;
        formData.page_id = '';
        formData.trans_source = dataResume.langSource;
        formData.trans_target = dataResume.langTarget.split(',');
        formData.trans_option = dataResume.fieldOption;
        formData.trans_all = 1;
        formData.nb_translated = dataResume.nbTranslated;
        formData.nb_char_translated = dataResume.nbCharTranslated;
        formData.trans_data = {source: {}, target: {}};
        formData.trans_data.target[formData.trans_target[0]] = {};
        formData.module_name = etsTransFunc.getParameterByName('module') || '';
        if(dataResume.pageType == 'email' || dataResume.pageType == 'theme'){
            var selected = etsTransFunc.getParameterByName('selected-theme');
            if(!selected){
                selected = etsTransFunc.getParameterByName('selected');
            }
            formData.selected_theme = selected;
        }

        etsAdminInterTrans.stopTranslate = false;
        formData = etsAdminInterTrans.getNextDataTrans(formData);
        etsAdminInterTrans.translatePage(formData, $(this) ,dataResume.pageType);
    });

    $(document).on('click', '.js-ets-trans-btn-inter-trans-all', function(){

        etsAdminInterTrans.getFormSelectTranslate('theme', 1, null, this);
    });

    $('#translations_form').submit(function(){
        etsAdminInterTrans.formSaved = true;
        return true;
    });
    $(document).on('keyup', 'form[isedited] textarea', function(){
       etsAdminInterTrans.theme.showBtnResetItem(this);
    });

    $(document).on('click', 'form[isedited] .ets-trans-btn-reset-trans-item-theme', function(){
       etsAdminInterTrans.theme.hideBtnResetItem(this);
       etsAdminInterTrans.removeHighlightInput($(this).parent().find('textarea'));
    });

    $(document).on('hidden.bs.modal','#etsTransModalTrans', function (e) {
        etsAdminInterTrans.stopTranslatePage();
    });
    $(document).on('click','#etsTransModalTrans .close,#etsTransModalTrans .btn-group-translate-close', function (e) {
        etsAdminInterTrans.stopTranslatePage();
    });

});
