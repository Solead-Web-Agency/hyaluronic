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
var etsTransMMItemClick = null;
var etsTransMegamenu = {
    ajaxXhrTranslatePage: null,
    renderBtnTransItem: function () {
        return '<button class="btn btn-default js-ets-trans-mm-item" title="' + etsTransFunc.trans('translate') + '"><i class="fa fa-language"></i></button>';
    },
    renderBtnTransForm: function () {
        return '<button class="btn btn-default js-ets-trans-mm-form pull-right" title="' + etsTransFunc.trans('translate') + '"><i class="process-icon-language fa-language"></i> ' + etsTransFunc.trans('translate') + '</button>';
    },
    renderBtnTransAllModule: function(){
        return '<button class="btn btn-default js-ets-trans-mm-all" title="' + etsTransFunc.trans('translate') + '"><i class="fa fa-language"></i> ' + etsTransFunc.trans('translate') + '</button>';
    },
    addBtnTransToInput: function () {
        if(ETS_TRANS_ENABLE_TRANS_FIELD) {
            $('.mm_menu_form.mm_pop_up .mm_form .translatable-field').each(function () {
                if ($(this).find('input[type=text],textarea').attr('id').indexOf('url_') === -1 && !$(this).parent().find('.js-ets-trans-mm-item').length) {
                    $(this).parent().append(etsTransMegamenu.renderBtnTransItem());
                }
            });
        }
        if ($('.mm_save_wrapper').length) {
            $('.mm_save_wrapper').each(function () {
                if (!$(this).find('.js-ets-trans-mm-form').length && $(this).closest('form').attr('id') !== 'ets_mm_column_form') {
                    $(this).append(etsTransMegamenu.renderBtnTransForm());
                }
            });
        }
    },
    getParameterByName: function (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    },
    translate: function(btnClick, formData, isTransAll){
        
        isTransAll =isTransAll || 0;
        isTransAll = parseInt(isTransAll);
        etsTransMegamenu.ajaxXhrTranslatePage = $.ajax({
           url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            data: {
                etsTransMegamenu: 1,
                isTransAll: isTransAll,
                formData: formData,
            },
            dataType: 'json',
            beforeSend: function(){
                $(btnClick).addClass('loading');
                //$(btnClick).prop('disabled', true);
                if(isTransAll){
                    etsTransFunc.showPopupTranslating(0, 'megamenu', 0, formData.trans_source, formData.trans_target, formData.trans_option);
                }
                else
                    etsTransFunc.showTranslatingField();
            },
            success: function (res) {
                if(res.success){
                    if(isTransAll){
                        var transData = res.data || null;
                        if(transData){
                            etsTransFunc.updateDataTranslating(transData.nb_text, transData.nb_char);
                        }
                    }
                    else{
                        var transData = res.trans_data || null;
                        if(transData){
                            Object.keys(transData).forEach(function (idLang) {
                                Object.keys(transData[idLang]).forEach(function (key) {
                                    $('.mm_menu_form.mm_pop_up #'+key+idLang).val(transData[idLang][key]);
                                });
                            });
                        }
                    }
                    if(typeof res.no_trans !== 'undefined' && res.no_trans){
                        showSuccessMessage(etsTransFunc.trans('no_text_trans'));
                    }
                    else{
                        showSuccessMessage(res.message);
                    }
                }
                else{
                    var errorMessage = res.errors || res.message;
                    showErrorMessage(errorMessage);
                }
                $('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
                $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
            },
            complete: function(){
                $(btnClick).removeClass('loading');
                //$(btnClick).prop('disabled', false);
                if(isTransAll){
                    etsTransFunc.setTranslateDone();
                }
                else
                    etsTransFunc.hideTranslatingField();
                $('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
                $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
            }
        });
    },
    getInputData: function(idLang, fieldTrans){
        fieldTrans = fieldTrans || null;
        var transData = {};
        var boxSearch = null;
        if(!fieldTrans){
            boxSearch = $('.mm_menu_form.mm_pop_up .mm_form .translatable-field.lang-'+idLang);
        }
        else{
            boxSearch = $(fieldTrans).find('.translatable-field.lang-'+idLang);
        }
        boxSearch.each(function () {
            var input = $(this).find('input[type=text], textarea');
            if(input.attr('id').indexOf('url_'+idLang) === -1){
                var keyInput = etsTransMegamenu.getInputKey(input.attr('id'), idLang);
                transData[keyInput] = input.val();
            }
        });
        return transData;
    },
    getFormData: function(langSource, langTarget, transOption, fieldTrans){
        var transData = {};
        transData.source = etsTransMegamenu.getInputData(langSource, fieldTrans);
        transData.target = {};
        $.each(langTarget, function(i, idLang){
            transData.target[idLang] = {};
            var transLangData = etsTransMegamenu.getInputData(idLang, fieldTrans);
            switch (transOption) {
                case 'only_empty':
                    Object.keys(transLangData).forEach(function (k) {
                        if(!transLangData[k].trim()){
                            transData.target[idLang][k] = 1;
                        }
                        else{
                            transData.target[idLang][k] = 0;
                        }
                    });
                    break;
                case 'both':
                    Object.keys(transLangData).forEach(function (k) {
                        if(!transLangData[k].trim() || transLangData[k].trim().toLowerCase() == transData.source[k].trim().toLowerCase()){
                            transData.target[idLang][k] = 1;
                        }
                        else{
                            transData.target[idLang][k] = 0;
                        }
                    });
                    break;
                case 'same_source':
                    Object.keys(transLangData).forEach(function (k) {
                        if(transLangData[k].trim().toLowerCase() == transData.source[k].trim().toLowerCase()){
                            transData.target[idLang][k] = 1;
                        }
                        else{
                            transData.target[idLang][k] = 0;
                        }
                    });
                    break;
                case 'all':
                    Object.keys(transLangData).forEach(function (k) {
                        transData.target[idLang][k] = 1;
                    });
                    break;
            }
        });
        return transData;
    },
    getFormConfig: function(btnClicked, isTransAll, fieldTrans){
        $.ajax({
            url: ETS_TRANS_LINK_AJAX_MODULE,
            type: 'GET',
            dataType: 'json',
            data: {
                etsTransGetFormTranslate: 1,
                pageId: '',
                pageType: 'megamenu',
                isDetailPage: 1,
                isTransAll: isTransAll || 0,
                fieldTrans: fieldTrans || '',
                resetTrans: 0
            },
            beforeSend: function () {
                if (btnClicked) {
                    $(btnClicked).addClass('loading');
                    //$(btnClicked).prop('disabled', true);
                }
            },
            success: function (res) {
                if (res.success) {
                    $('#etsTransModalTrans').remove();
                    if($('.mm_popup_overlay .mm_pop_up').length && $(btnClicked).closest('.mm_popup_overlay').length)
                        $('.mm_popup_overlay .mm_pop_up').prepend(res.form);
                    else{
                        if($('#content.bootstrap').length){
                            $('#content.bootstrap').append(res.form);
                        }
                        else
                            $('body').append(res.form);
                    }

                    etsTransFunc.showPopupTrans();
                }
            },
            complete: function () {
                if (btnClicked) {
                    $(btnClicked).removeClass('loading');
                    //$(btnClicked).prop('disabled', false);
                }
            }
        });
    },
    getInputKey: function(inputName, idLang){
        var regex = new RegExp(idLang+'$', 'g');
        return inputName.replace(regex, '');
    },
    analysisBeforeTranslate: function(pageType, formData, offset){
        $('#etsTransPopupAnalyzing').addClass('active');
        etsTransFunc.hidePopupTrans();
        etsTransMegamenu.ajaxXhrTranslatePage = $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            dataType: 'json',
            data: {
                etsTransAnalyzing: 1,
                pageType: pageType,
                formData: formData,
                offset: offset
            },
            success: function(res){
                if(res.success){
                    var resData = res.data || {};
                    if(Object.keys(resData).length){
                        formData.nb_text = parseInt(formData.nb_text) + resData.nb_text;
                        formData.nb_char = parseInt(formData.nb_char) + resData.nb_char;
                        formData.nb_money = parseFloat(formData.nb_money) + resData.nb_money;
                        if(resData.stop != 1){
                            etsTransMegamenu.analysisBeforeTranslate(pageType, formData, resData.offset);
                        }
                        else{
                            $('#etsTransPopupAnalyzing').removeClass('active');
                            etsTransFunc.showAnalysisCompleted(pageType, formData, resData.total_item || 0);
                        }
                    }
                }
                else{
                    $('#etsTransPopupAnalyzing').removeClass('active');
                    showErrorMessage('Has error');
                }
            },
            complete: function(){

            },
            error: function () {
                $('#etsTransPopupAnalyzing').removeClass('active');
                showErrorMessage('Has error');
            }
        });
    },
    getItemType: function(mmObject){
        switch (mmObject) {
            case 'MM_Menu':
                return 'menu';
            case 'MM_Tab':
                return 'tab';
            case 'MM_Block':
                return 'block';
            default:
                return '';
        }
    },
    colData: {
        menu: {
            'title_': 'title',
            'bubble_text_': 'bubble_text',
        },
        tab: {
            'title_': 'title',
            'bubble_text_': 'bubble_text',
        },
        block: {
            'title_': 'title',
            'content_': 'content',
        }
    },
    stopTranslatePage: function () {
        if (etsTransMegamenu.ajaxXhrTranslatePage && etsTransMegamenu.ajaxXhrTranslatePage.readyState != 4) {
            etsTransMegamenu.ajaxXhrTranslatePage.abort();
        }
    },
};
$(document).ready(function () {
    $('.ets_megamenu_tool_bar .ets_megamenu_buttons').prepend(etsTransMegamenu.renderBtnTransAllModule());

    $(document).on('click', '.mm_add_menu, .mm_add_block, .mm_add_tab', function () {
        setTimeout(function () {
            etsTransMegamenu.addBtnTransToInput();
        }, 100);
    });

    $(document).ajaxSuccess(function (event, xhr, settings) {
        var url = settings.url;
        if (etsTransMegamenu.getParameterByName('configure', url) !== 'ets_megamenu') {
            return;
        }
        etsTransMegamenu.addBtnTransToInput();
    });

    $(document).on('click', '.js-ets-trans-mm-item', function () {
        if(ETS_TRANS_IS_AUTO_CONFIG){
            var langTarget = ETS_TRANS_DEFAULT_CONFIG.lang_target || '';
            var formData = {
                trans_option: ETS_TRANS_DEFAULT_CONFIG.field_option,
                trans_source: ETS_TRANS_DEFAULT_CONFIG.lang_source,
                trans_target: langTarget.split(','),
            };
            formData.trans_data = etsTransMegamenu.getFormData(formData.trans_source, formData.trans_target, formData.trans_option, $(this).parent()[0]);
            formData.page_id = $(this).closest('form').find('input[name=itemId]').val();
            formData.menu_type = etsTransMegamenu.getItemType($(this).closest('form').find('input[name=mm_object]').val());
            formData.col_data = etsTransMegamenu.colData[formData.menu_type];
            etsTransMegamenu.translate(this, formData);
        }
        else{
            var fieldTrans = $(this).parent().find('input[type=text], textarea').attr('id');
            etsTransMegamenu.getFormConfig(this, 0, fieldTrans);
        }
        return false;
    });

    $(document).on('click', '.js-ets-trans-mm-form', function () {
        if(ETS_TRANS_IS_AUTO_CONFIG){
            var langTarget = ETS_TRANS_DEFAULT_CONFIG.lang_target || '';
            var formData = {
                trans_option: ETS_TRANS_DEFAULT_CONFIG.field_option,
                trans_source: ETS_TRANS_DEFAULT_CONFIG.lang_source,
                trans_target: langTarget.split(','),
            };
            formData.trans_data = etsTransMegamenu.getFormData(formData.trans_source, formData.trans_target, formData.trans_option, $(this).closest('.panel')[0]);
            formData.page_id = $(this).closest('form').find('input[name=itemId]').val();
            formData.menu_type = etsTransMegamenu.getItemType($(this).closest('form').find('input[name=mm_object]').val());
            formData.col_data = etsTransMegamenu.colData[formData.menu_type];
            etsTransMegamenu.translate(this, formData);
        }
        else{
            etsTransMegamenu.getFormConfig(this, 0, 'panel');
        }
        return false;
    });
    $(document).on('click', '.js-ets-trans-mm-all', function () {
        etsTransMMItemClick = 'js-ets-trans-mm-all';
        etsTransMegamenu.getFormConfig(this, 1);
        return false;
    });

    $(document).on('click','.js-ets-trans-analysis-text', function () {
        var formData = $(this).closest('form').serializeArray();
        formData = etsTransFunc.formatFormData(formData);
        formData.nb_text = 0;
        formData.nb_char = 0;
        formData.nb_money = 0;
        if(typeof formData.trans_target == 'undefined' || !formData.trans_target){
            etsTransFunc.showErrorTrans(etsTransFunc.trans('target_lang_required'));
            return false;
        }
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
                    $this.parents('.ets-trans-modal').removeClass('ets_modify');
                    etsTransMegamenu.analysisBeforeTranslate('megamenu', formData, 1);
                    $('#etsTransModalTrans .js-ets-trans-analysis-text').addClass('hide');
                }
            },
            complete: function () {
                $this.prop('disabled', false);
                $this.removeClass('loading');
            }
        });

        return false;
    });

    $(document).on('click','.js-ets-trans-btn-translate-page, .js-ets-trans-analysis-accept', function () {
        var formData = $(this).closest('form').serializeArray();
        formData = etsTransFunc.formatFormData(formData);
        if(typeof formData.trans_target == 'undefined' || !formData.trans_target){
            etsTransFunc.showErrorTrans(etsTransFunc.trans('target_lang_required'));
            return false;
        }
        etsTransFunc.hideAnalysisCompleted();
        etsTransFunc.hidePopupTrans();
        var fieldTrans = $(this).attr('data-field') || '';
        var fieldEl = null;
        var btnClick = null;
        if(fieldTrans == 'panel'){
            fieldEl = $('.mm_menu_form.mm_pop_up').find('.panel')[0];
            btnClick = $('.mm_menu_form.mm_pop_up').find('.js-ets-trans-mm-form')[0];
        }
        else{
            if(fieldTrans) {
                fieldEl = $('.mm_menu_form.mm_pop_up').find('#' + fieldTrans).closest('.translatable-field').parent()[0];
                btnClick = $('.mm_menu_form.mm_pop_up').find('#' + fieldTrans).closest('.translatable-field').parent().find('.js-ets-trans-mm-item')[0];
            }
        }

        if($(this).hasClass('js-ets-trans-analysis-accept')){
            etsTransMegamenu.translate(btnClick, formData, 1);
            return false;
        }
        var isTransAll = $(this).attr('data-trans-all') || 0;
        formData.trans_data = etsTransMegamenu.getFormData(formData.trans_source, formData.trans_target, formData.trans_option, fieldEl);
        formData.page_id = $('.mm_menu_form.mm_pop_up .panel').find('input[name=itemId]').val() || 0;
        formData.menu_type = etsTransMegamenu.getItemType($('.mm_menu_form.mm_pop_up .panel').find('input[name=mm_object]').val());
        formData.col_data = etsTransMegamenu.colData[formData.menu_type];
        etsTransMegamenu.translate(btnClick, formData, isTransAll);
        $('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
        $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
        return false;
    });
    $(document).on('hidden.bs.modal','#etsTransModalTrans .close,#etsTransModalTrans .btn-group-translate-close', function (e) {
        etsTransMegamenu.stopTranslatePage();
    });
});