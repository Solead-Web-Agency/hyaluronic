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

var etsTranslateDefine = {
    ajaxXhrTranslatePage: null,
    stopTranslate: false,
    nbCharactersUsed: 0,
    psMainMenuTransListItem: false,
    renderBtnTransToolbar: function (className, hasLi, classIcon) {
        className = className || '';
        hasLi = hasLi || '';
        classIcon = classIcon || '';
        var btnText = etsTranslateDefine.trans('translate');
        var btn =  '<a class="ets-trans-button toolbar_btn pointer btn-sales ets-trans-btn-trans-toolbar js-ets-trans-btn-trans-toolbar ' + className + '" href="javascript:void(0)" title="' + etsTranslateDefine.trans('translate') + '">' +
            '<i class="process-icon-language" aria-hidden="true"></i>' +
                '<div>' +
                    btnText +
                '</div>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }
        return btn;
    },
    renderBtnTransToolbar2: function (className, hasLi, classIcon) {
        className = className || '';
        hasLi = hasLi || '';
        classIcon = classIcon || '';
        var btnText = etsTranslateDefine.trans('translate');
        var btn =  '<a class="ets-trans-button toolbar_btn pointer btn-sales ets-trans-btn-trans-toolbar js-ets-trans-btn-trans-toolbar ' + className + '" href="javascript:void(0)" title="' + etsTranslateDefine.trans('translate') + '">' +
            '<i class="fa fa-language" aria-hidden="true"></i> ' + btnText +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }
        return btn;
    },
    renderBtnTransToolbarProduct: function (className, hasLi) {
        className = className || '';
        hasLi = hasLi || '';
        var btn =  '<a class="ets-trans-button toolbar-button btn-sales ets-trans-btn-trans-toolbar js-ets-trans-btn-trans-toolbar ' + className + '" href="javascript:void(0)" title="' + etsTranslateDefine.trans('translate') + '">' +
            '<i class="ets-trans-icon-translate fa fa-language"></i>' + '<span class="title">' + etsTranslateDefine.trans('translate') + '</span>' +
            '</a>';
        if(hasLi){
            return '<li>'+btn+'</li>';
        }
        return btn;
    },
    renderBtnTransListItem: function (className, hasLi) {
        className = className || '';
        hasLi = hasLi || false;
        var btn = '<a class="ets-trans-button dropdown-item product-edit list-item-trans ' + className + '" href="javascript:void(0)" onclick="etsTranslateDefine.onClickTransListItem(this);">' +
            '<i class="fa fa-language" aria-hidden="true"></i> <span>' +
            etsTranslateDefine.trans('translate') +
            '</span></a>';
        if (hasLi) {
            return '<li class="divider"></li><li>' + btn + '</li>';
        }
        return btn;
    },
    renderBtnTransListItemButton: function (className, hasLi) {
        className = className || '';
        hasLi = hasLi || false;
        var btn = '<a class="ets-trans-button btn btn-default product-edit list-item-trans' + className + '" href="javascript:void(0)" onclick="etsTranslateDefine.onClickTransListItem(this);">' +
            '<i class="fa fa-language" aria-hidden="true"></i> ' +
            etsTranslateDefine.trans('translate') +
            '</a>';
        if (hasLi) {
            return '<li class="divider"></li><li>' + btn + '</li>';
        }
        return btn;
    },
    renderBtnBulkTransList: function (className, isBtn) {
        className = className || '';
        isBtn = isBtn || 0;
        if(isBtn){
            return '<button type="button" class="ets-trans-button ets-trans-bulk-trans js-ets-trans-bulk-trans ' + className + '">' +
                '<i class="fa fa-language" aria-hidden="true"></i>' +
                etsTranslateDefine.trans('bulk_translate') +
                '</button>';
        }
        return '<a href="#" class="ets-trans-bulk-trans js-ets-trans-bulk-trans ' + className + '">' +
            '<i class="fa fa-language" aria-hidden="true"></i>' +
            etsTranslateDefine.trans('bulk_translate') +
            '</a>';
    },
    renderBtnTransAll: function (className, hasLi, btnText) {
        className = className || '';
        hasLi = hasLi || '';
        btnText = btnText || etsTranslateDefine.trans('translate');
        if(hasLi){
            return '<li>' +
                '<a href="javascript:void(0)" title="'+etsTranslateDefine.trans('translate')+'" class="ets-trans-button ets-trans-trans-all js-ets-trans-trans-all on-header toolbar_btn pointer' + className + '">' +
                '<i class="process-icon-language" aria-hidden="true"></i><div>' +
                btnText +
                '</div></a>' +
                '</li>'
        }
        return '<button type="button" title="'+etsTranslateDefine.trans('translate')+'" class="ets-trans-button btn btn-outline-secondary ets-trans-trans-all js-ets-trans-trans-all ' + className + '">' +
            '<i class="fa fa-language" aria-hidden="true"></i>' +
            btnText +
            '</button>';
    },
    renderBtnTransAll2: function (className, hasLi, btnText) {
        className = className || '';
        hasLi = hasLi || '';
        btnText = btnText || etsTranslateDefine.trans('translate');
        if(hasLi){
            return '<li>' +
                '<a href="javascript:void(0)" title="'+etsTranslateDefine.trans('translate')+'" class="ets-trans-button ets-trans-trans-all js-ets-trans-trans-all on-header toolbar_btn pointer' + className + '">' +
                '<i class="fa fa-language" aria-hidden="true"></i>' +
                btnText +
                '</a>' +
                '</li>'
        }
        return '<button type="button" title="'+etsTranslateDefine.trans('translate')+'" class="ets-trans-button btn btn-outline-secondary ets-trans-trans-all js-ets-trans-trans-all ' + className + '">' +
            '<i class="fa fa-language" aria-hidden="true"></i>' +
            btnText +
            '</button>';
    },
    renderBtnStopTranslate: function () {
        return '<button type="button" class="ets-trans-button btn btn-danger js-ets-trans-btn-strop-translate">' +
            '<i class="material-icons">close</i> ' + etsTranslateDefine.trans('stop') +
            '</button>'
    },
    renderBtnTransFieldItem: function (className,field) {
        className = className || '';
        field = field || '';
        return '<button type="button" class="ets-trans-button btn btn-sm btn-outline-secondary ets-trans-btn-trans-field-item js-ets-trans-btn-trans-field-item" title="'+ etsTransFunc.trans('translate') +'" data-field="' + field + '">'
            + '<i class="fa fa-language" aria-hidden="true"></i> '
            + etsTransFunc.trans('translate') +
            '<span class="ets_tooltip">'+etsTransFunc.trans('translate')+'</span>'
            + '</button>';
    },
    showErrorTrans: function (errors) {
        etsTransFunc.showErrorTrans(errors);
    },
    getFormSelectTranslate: function (ids, pageType, isTransAll, fieldTrans, btnClicked, resetTrans) {
        
        btnClicked = btnClicked || null;
        etsTranslateDefine.stopTranslate = false;
        if(pageType == 'ps_mainmenu' && $(btnClicked).hasClass('list-item-trans')){
            etsTranslateDefine.psMainMenuTransListItem = true;
        }
        $.ajax({
            url: ETS_TRANS_LINK_AJAX_MODULE,
            type: 'GET',
            dataType: 'json',
            data: {
                etsTransGetFormTranslate: 1,
                pageId: ids,
                pageType: pageType || etsTransPageType,
                isDetailPage: etsTransIsDetailPage,
                isTransAll: isTransAll || 0,
                fieldTrans: fieldTrans || '',
                resetTrans: resetTrans || 0,
                idFeature: etsTransFunc.getParameterByName('id_feature'),
                idAttributeGroup: etsTransFunc.getParameterByName('id_attribute_group'),
                isNewBlockreassurance: pageType == 'blockreassurance' && etsTranslateDefine.blockreassurance.isNewVersion(),
                hideDataToTrans: $(btnClicked).hasClass('list-item-trans') ? 1 : 0
            },
            beforeSend: function () {
                if (btnClicked) {
                    $(btnClicked).addClass('loading');
                    $(btnClicked).prop('disabled', true);
                }
            },
            success: function (res) {
                if (res.success) {
                    $('#etsTransModalTrans').remove();
                    if($('#content.bootstrap').length){
                        $('#content.bootstrap').append(res.form);
                    }
                    else{
                        $('body').append(res.form);
                    }
                    if($(btnClicked).hasClass('list-item-trans')){
                        $('.js-ets-trans-btn-translate-page').addClass('list-item-trans');
                    }
                    etsTransFunc.showPopupTrans();
                }
                else{
                    var msg = res.message || 'Error unknown';
                    showErrorMessage(msg);
                }
            },
            complete: function () {
                if (btnClicked) {
                    $(btnClicked).removeClass('loading');
                    $(btnClicked).prop('disabled', false);
                }
            }
        });
    },
    translatePage: function (formData, buttonTrans, pageType) {
        if (etsTranslateDefine.stopTranslate) {
            return;
        }
        var isDetailPage = etsTransIsDetailPage;
        if((pageType == 'ps_mainmenu' || pageType == 'ets_extraproducttabs' || (pageType == 'blockreassurance' && etsTranslateDefine.blockreassurance.isNewVersion())) && (formData.trans_all == 1 || buttonTrans.hasClass('list-item-trans'))){
            isDetailPage = 0;
        }

        etsTranslateDefine.beforeTranslate(buttonTrans);
        etsTranslateDefine.ajaxXhrTranslatePage = $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            dataType: 'json',
            data: {
                etsTransTranslatePage: 1,
                pageType: pageType || etsTransPageType,
                isDetailPage: isDetailPage,
                formData: formData,
                idAttributeGroup: etsTransFunc.getParameterByName('id_attribute_group'),
                idFeature: etsTransFunc.getParameterByName('id_feature'),
                isNewBlockreassurance: pageType == 'blockreassurance' && etsTranslateDefine.blockreassurance.isNewVersion()
            },
            beforeSend: function () {
                if (etsTransIsDetailPage){
                    buttonTrans.addClass('loading');
                }
            },
            success: function (res) {
                
                var requestDone = true;
                if (res.success) {
                    if (isDetailPage) {
                        var transResult = res.trans_data || {};
                        Object.keys(transResult).forEach(function (idLang) {
                            Object.keys(transResult[idLang]).forEach(function (key) {
                                if(pageType == 'attribute_group' || pageType == 'attribute' || pageType == 'feature'){
                                    $('input[name="'+ key + idLang+'"]').val(transResult[idLang][key]);
                                    if(ETS_TRANS_AUTO_GENERATE_LINK_REWRITE && key == 'name_'){
                                        $('input[name="url_name_'+ idLang+'"]').val(str2url(transResult[idLang][key]));
                                    }
                                }
                                else if(etsTranslateDefine.blockreassurance.isNewVersion()){
                                    $('#blockDisplay .show-rea-block.active input[name="'+ key + idLang+'"],#blockDisplay .show-rea-block.active textarea[name="'+ key + idLang+'"]').val(transResult[idLang][key]);
                                }
                                else if(pageType == 'ps_linklist'){
                                    if(key.indexOf('form_link_block_custom_') !== -1) {
                                        var idField = key.replace(/([0-9]+)([_title|_url]+)$/, idLang+'$2');
                                        $('#'+idField).val(transResult[idLang][key]);
                                        if(ETS_TRANS_AUTO_GENERATE_LINK_REWRITE && key.indexOf('_title') !== -1){
                                            $('#'+idField.replace('_title', '_url')).val(str2url(transResult[idLang][key]));
                                        }
                                    }
                                }
                                else if(pageType == 'ets_extraproducttabs'){
                                    $('#'+ key + idLang).val(transResult[idLang][key]);
                                    if(key == 'content_'){
                                        $('#content_mce_'+ idLang).val(transResult[idLang][key]);
                                        if(typeof tinyMCE !== "undefined")
                                            tinyMCE.get('content_mce_' + idLang).setContent(transResult[idLang][key]);
                                    }
                                }
                                else{
                                    if ($('#' + key + idLang).hasClass('js-taggable-field') || ($('#' + key + idLang).length && $('#' + key + idLang).attr('name').indexOf('keywords') !== -1)) {
                                        $('#' + key + idLang).val(transResult[idLang][key].replace(/\|/g, ','));
                                        if(!$('#' + key + idLang).parent().hasClass('ets-trans-field-translated-success')){
                                            $('#' + key + idLang).parent().addClass('ets-trans-field-translated-success');
                                        }
                                        $('#' + key + idLang).change();
                                        if ($('#' + key + idLang).hasClass('tagify')) {
                                            etsTranslateDefine.addKeywords('#' + key + idLang);
                                        }
                                    } else {
                                        $('#' + key + idLang).val(transResult[idLang][key]);
                                        if(ETS_TRANS_AUTO_GENERATE_LINK_REWRITE && typeof etsTranslateDefine[pageType].rewriteData !== 'undefined'){
                                            if(etsTranslateDefine[pageType].rewriteData.name.indexOf(key) !== -1){
                                                $.each(etsTranslateDefine[pageType].rewriteData.rewrite, function (i, elRewrite) {
                                                    $('#' + elRewrite + idLang).val(str2url(transResult[idLang][key]));
                                                });
                                            }
                                        }
                                        if(!$('#' + key + idLang).hasClass('ets-trans-field-translated-success')){
                                            $('#' + key + idLang).addClass('ets-trans-field-translated-success');
                                        }
                                    }
                                    if (typeof tinyMCE !== "undefined" && $('#' + key + idLang).hasClass('autoload_rte')) {
                                        $('#' + key + idLang).prev().find('iframe').next('div').hide();
                                        tinyMCE.get(key + idLang).setContent(transResult[idLang][key]);
                                        if(!$('#' + key + idLang).parent().hasClass('ets-trans-field-translated-success')){
                                            $('#' + key + idLang).parent().addClass('ets-trans-field-translated-success');
                                        }
                                    }
                                }

                            });
                        });
                        etsTransFunc.hideTranslatingField();
                    }
                    else {
                        var stopTrans = true;
                        if (typeof res.trans_data.stop_translate !== 'undefined') {
                            stopTrans = res.trans_data.stop_translate;
                        }
                        var nbTranslated = res.trans_data.nb_translated || 0;
                        var nbChar = res.trans_data.translated_length || 0;
                        formData.nb_char_translated = parseInt(formData.nb_char_translated) + parseInt(nbChar);
                        etsTransFunc.updateDataTranslating(nbTranslated, formData.nb_char_translated);
                        if (nbTranslated && stopTrans === false) {
                            requestDone = false;
                            formData.nb_translated = nbTranslated;
                            if (formData.trans_all != 1 && res.trans_data.page_id) {
                                formData.page_id = res.trans_data.page_id;
                                etsTranslateDefine.translatePage(formData, buttonTrans, pageType);
                            } else {
                                etsTranslateDefine.translatePage(formData, buttonTrans, pageType);
                            }
                        }
                    }
                    if (requestDone) {
                        etsTranslateDefine.afterTranslate(buttonTrans);
                        etsTransFunc.setTranslateDone();
                        showSuccessMessage(res.message);
                        if(buttonTrans.hasClass('js-ets-trans-btn-translate-page') && isDetailPage){
                            $('#etsTransModalTrans').modal('hide');
                        }
                        //$('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
                        $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
                    }
                } else {
                    if(res.message){
                        showErrorMessage(res.message);
                        etsTransFunc.setTranslateError(res.message);
                    }
                    else if (res.errors) {
                        if(etsTransIsDetailPage){
                            etsTransFunc.hideTranslatingField();
                            etsTranslateDefine.showErrorTrans(res.errors);
                            showErrorMessage(res.errors);
                        }
                        else{
                            etsTransFunc.setTranslateError(res.errors);
                            showErrorMessage(res.errors);
                        }
                        etsTranslateDefine.afterTranslate(buttonTrans, false);
                    }
                }
            },
            complete: function () {
                if(etsTransIsDetailPage){
                    buttonTrans.removeClass('loading');
                    etsTranslateDefine.afterTranslate(buttonTrans);
                }
                etsTransFunc.hideTranslatingField();
                
            },
            error: function (xhr) {
                etsTranslateDefine.afterTranslate(buttonTrans);
            }
        });
    },
    analysisBeforeTranslate: function(pageType, formData, offset){
        $('#etsTransPopupAnalyzing').addClass('active');
        etsTransFunc.hidePopupTrans();
        etsTranslateDefine.ajaxXhrTranslatePage = $.ajax({
            url: ETS_TRANS_LINK_AJAX,
            type: 'POST',
            dataType: 'json',
            data: {
                etsTransAnalyzing: 1,
                pageType: pageType,
                formData: formData,
                offset: offset,
                idAttributeGroup: etsTransFunc.getParameterByName('id_attribute_group'),
                idFeature: etsTransFunc.getParameterByName('id_feature'),
                isNewBlockreassurance: pageType == 'blockreassurance' && etsTranslateDefine.blockreassurance.isNewVersion()
            },
            success: function(res){
                if(res.success){
                    var resData = res.data || {};
                    if(Object.keys(resData).length){
                        formData.nb_text = parseInt(formData.nb_text) + resData.nb_text;
                        formData.nb_char = parseInt(formData.nb_char) + resData.nb_char;
                        formData.nb_money = parseFloat(formData.nb_money) + resData.nb_money;
                        if(resData.stop != 1){
                            etsTranslateDefine.analysisBeforeTranslate(pageType, formData, resData.offset);
                        }
                        else{
                            $('#etsTransPopupAnalyzing').removeClass('active');
                            etsTransFunc.showAnalysisCompleted(pageType, formData, resData.total_item || 0);
                        }
                    }
                }
                else{
                    $('#etsTransPopupAnalyzing').removeClass('active');
                    $('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
                    $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
                    showErrorMessage('Has error');
                }
            },
            complete: function(){
            },
            error: function () {
                $('#etsTransPopupAnalyzing').removeClass('active');
                $('.ets-trans-modal.show,.modal-backdrop.show,.modal-backdrop.in,.ets-trans-modal.in').remove();
                $('body').removeClass('etsTransPopupActive').removeClass('modal-open');
                showErrorMessage('Has error');
                
            }
        });
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
                else{
                    var errorMessage = res.errors || res.message;
                    showErrorMessage(errorMessage);
                }
            },
            complete: function () {
                btn.removeClass('loading');
                btn.prop('disabled', false);
            }
        });
    },
    beforeTranslate: function (buttonTrans) {
        buttonTrans.addClass('active');
        buttonTrans.addClass('loading');
        buttonTrans.prop('disabled', true);
        buttonTrans.find('.text-btn-translate').html(etsTranslateDefine.trans('translating') + '...');
        $('#etsTransModalTrans').addClass('translating');
        // $('#etsTransModalTrans .append-btn-stop').html(etsTranslateDefine.renderBtnStopTranslate());
    },
    afterTranslate: function (buttonTrans, closeModal) {
        if (typeof closeModal === 'undefined') {
            closeModal = true;
        }
        buttonTrans.removeClass('active');
        buttonTrans.prop('disabled', false);
        buttonTrans.find('.text-btn-translate').html(etsTranslateDefine.trans('translate'));

        $('#etsTransModalTrans').removeClass('translating');
        $('#etsTransModalTrans .js-ets-trans-btn-strop-translate').remove();
        if (closeModal) {
            etsTransFunc.hidePopupTrans();
        }
    },
    trans: function (key) {
        return etsTransText[key] || key;
    },
    formatFormData: function (formData) {
        return etsTransFunc.formatFormData(formData);
    },
    getTransDataPage: function (lang_source, langs_target, trans_option, pageType, fieldTrans) {
        var transData = {};
        pageType = pageType || etsTransPageType;
        if(etsTransIsDetailPage){
            transData = etsTranslateDefine.getTransData(pageType, lang_source, langs_target, trans_option, fieldTrans);
        }
        return transData;
    },

    getTransData: function (type, lang_source, langs_target, trans_option, fieldTrans) {
        var transData = {};
        if (typeof tinyMCE !== 'undefined') {
            try{
                tinyMCE.triggerSave();
            }
            catch (e) {

            }
        }
        var dataTransSource = etsTranslateDefine[type].getFormData(lang_source, fieldTrans);
        if (!dataTransSource) {
            return;
        }
        transData.source = dataTransSource;
        transData.target = {};
        $.each(langs_target, function (i, id_lang) {
            if(type == 'ps_linklist'){
                var dataTarget = etsTranslateDefine[type].getFormData(id_lang, fieldTrans, lang_source);
            }
            else
                var dataTarget = etsTranslateDefine[type].getFormData(id_lang, fieldTrans);
            var targetOption = {};
            if (trans_option == 'both') {
                Object.keys(dataTarget).forEach(function (key) {
                    if ((dataTarget[key].trim().toLowerCase() == dataTransSource[key].trim().toLowerCase() || dataTarget[key].trim() == '') && dataTransSource[key].trim()) {
                        targetOption[key] = 1;
                    } else {
                        targetOption[key] = 0;
                    }
                });
            } else if (trans_option == 'only_empty') {
                Object.keys(dataTarget).forEach(function (key) {
                    if (dataTarget[key].trim() == '') {
                        targetOption[key] = 1;
                    } else {
                        targetOption[key] = 0;
                    }
                });
            } else if (trans_option == 'same_source') {
                Object.keys(dataTarget).forEach(function (key) {
                    if (dataTarget[key].trim().toLowerCase() == dataTransSource[key].trim().toLowerCase()) {
                        targetOption[key] = 1;
                    } else {
                        targetOption[key] = 0;
                    }
                });
            } else if (trans_option == 'all') {
                Object.keys(dataTarget).forEach(function (key) {
                    targetOption[key] = 1;
                });
            }
            transData.target[id_lang] = targetOption;
        });
        return transData;
    },

    addKeywords: function (el) {
        var keywords = $(el).val();
        var splitKeywords = keywords.split(',');
        var oldKeywords = $(el).tagify('serialize');
        $.each(oldKeywords.split(','), function (i, item) {
            $(el).tagify('remove');
        });
        $.each(splitKeywords, function (i, item) {
            $(el).tagify('add', item);
        });
    },
    getColData: function(pageType){
        switch (pageType) {
            case 'product':
                return etsTranslateDefine.product.colData.ps17;
            case 'category':
                if($('[id*=category_name_]').length){
                    return etsTranslateDefine.category.colData.ps176;
                }
                else{
                    return etsTranslateDefine.category.colData.ps175;
                }
            case 'cms':
                if($('[id*=cms_page_title_]').length){
                    return etsTranslateDefine.cms.colData.ps176;
                }
                else{
                    return etsTranslateDefine.cms.colData.ps175;
                }
            case 'cms_category':
                if($('[id*=cms_page_category_name_]').length){
                    return etsTranslateDefine.cms_category.colData.ps176;
                }
                else{
                    return etsTranslateDefine.cms_category.colData.ps175;
                }
            case 'manufacturer':
                if($('[id*=manufacturer_short_description_]').length){
                    return etsTranslateDefine.manufacturer.colData.ps176;
                }
                else{
                    return etsTranslateDefine.manufacturer.colData.ps175;
                }
            case 'supplier':
                if($('[id*=supplier_description_]').length){
                    return etsTranslateDefine.supplier.colData.ps176;
                }
                else{
                    return etsTranslateDefine.supplier.colData.ps175;
                }
            case 'attribute_group':
                return etsTranslateDefine.attribute_group.colData.ps176;
            case 'attribute':
                return etsTranslateDefine.attribute.colData.ps176;
            case 'feature':
                return etsTranslateDefine.feature.colData.ps176;
            case 'feature_value':
                return etsTranslateDefine.feature_value.colData.ps176;
            case 'blockreassurance':
                if(etsTranslateDefine.blockreassurance.isNewVersion())
                    return etsTranslateDefine.blockreassurance.colData.ps177;
                return etsTranslateDefine.blockreassurance.colData.ps176;
            case 'ps_linklist':
                return etsTranslateDefine.ps_linklist.colData.ps176;
            case 'ps_mainmenu':
                return etsTranslateDefine.ps_mainmenu.colData.ps176;
            case 'ps_customtext':
                return etsTranslateDefine.ps_customtext.colData.ps176;
            case 'ps_imageslider':
                return etsTranslateDefine.ps_imageslider.colData.ps176;
            case 'ets_extraproducttabs':
                return etsTranslateDefine.ets_extraproducttabs.colData.ps176;
            default:
                return {};
        }
    },
    product: {
        fields: {
            "ps17": [
                'form_step1_name_',
                'form_step1_description_short_',
                'form_step1_description_',
                'form_step3_available_now_',
                'form_step3_available_later_',
                'form_step4_delivery_in_stock_',
                'form_step4_delivery_out_stock_',
                'form_step5_meta_title_',
                'form_step5_meta_description_',
            ],
        },
        colData: {
            "ps17": {
                'form_step1_name_': 'name',
                'form_step1_description_short_': 'description_short',
                'form_step1_description_': 'description',
                'form_step3_available_now_': 'available_now',
                'form_step3_available_later_': 'available_later',
                'form_step4_delivery_in_stock_': 'delivery_in_stock',
                'form_step4_delivery_out_stock_': 'delivery_out_stock',
                'form_step5_meta_title_': 'meta_title',
                'form_step5_meta_description_': 'meta_description',
            }
        },
        rewriteData: {
            name: ['form_step1_name_'],
            rewrite: ['form_step5_link_rewrite_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    dataTrans[fieldTrans] = ($('#' + fieldTrans + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            $.each(etsTranslateDefine.product.fields.ps17, function (i, item) {
                if (item.indexOf('keywords') !== -1) {
                    dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[item] = $('#' + item + id_lang).val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            $('#product_catalog_list input[name="bulk_action_selected_products[]"]:checked').each(function () {
                ids.push($(this).val());
            });
            return ids;
        }
    },
    category: {
        fields: {
            "ps176": [
                'category_name_',
                'category_description_',
                'category_meta_title_',
                'category_meta_description_',
                'category_meta_keyword_',
            ],
            "ps175": [
                'name_',
                'description_',
                'meta_title_',
                'meta_description_',
                'meta_keywords_',
            ],
        },
        colData: {
            "ps176": {
                'category_name_': 'name',
                'category_description_': 'description',
                'category_meta_title_': 'meta_title',
                'category_meta_description_': 'meta_description',
                'category_meta_keyword_': 'meta_keywords',
            },
            "ps175": {
                'name_': 'name',
                'description_': 'description',
                'meta_title_': 'meta_title',
                'meta_description_': 'meta_description',
                'meta_keywords_': 'meta_keywords',
            },
        },
        rewriteData: {
            name: ['category_name_', 'name_'],
            rewrite: ['category_link_rewrite_', 'link_rewrite_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    dataTrans[fieldTrans] = ($('#' + fieldTrans + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }

            if ($('#category_name_' + id_lang).length) {
                $.each(etsTranslateDefine.category.fields.ps176, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            } else if ($('#name_' + id_lang).length) {
                $.each(etsTranslateDefine.category.fields.ps175, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }
            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#category_grid_table').length) {
                $('#category_grid_table input[name="category_id_category[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            if ($('#table-category').length) {
                $('#table-category input[name="categoryBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            return ids;
        }
    },
    cms: {
        fields: {
            "ps176": [
                'cms_page_title_',
                'cms_page_meta_title_',
                'cms_page_meta_description_',
                'cms_page_meta_keyword_',
                'cms_page_content_',
            ],
            "ps175": [
                'name_',
                'head_seo_title_',
                'meta_description_',
                'meta_keywords_',
                'content_',
            ],
        },
        colData: {
            "ps176": {
                'cms_page_title_': 'meta_title',
                'cms_page_meta_title_': 'head_seo_title',
                'cms_page_meta_description_': 'meta_description',
                'cms_page_meta_keyword_': 'meta_keywords',
                'cms_page_content_': 'content',
            },
            "ps175": {
                'name_': 'meta_title',
                'head_seo_title_': 'head_seo_title',
                'meta_description_': 'meta_description',
                'meta_keywords_': 'meta_keywords',
                'content_': 'content',
            },
        },
        rewriteData: {
            name: ['cms_page_title_', 'name_'],
            rewrite: ['cms_page_category_friendly_url_', 'link_rewrite_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || '';
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    dataTrans[fieldTrans] = ($('#' + fieldTrans + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            if ($('#cms_page_title_' + id_lang).length) {
                $.each(etsTranslateDefine.cms.fields.ps176, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            } else if ($('#name_' + id_lang).length) {
                $.each(etsTranslateDefine.cms.fields.ps175, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }
            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#cms_page_grid_table').length) {
                $('#cms_page_grid_table input[name="cms_page_bulk[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            if ($('#table-cms').length) {
                $('#table-cms input[name="cmsBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            return ids;
        }
    },
    cms_category: {
        fields: {
            "ps176": [
                'cms_page_category_name_',
                'cms_page_category_description_',
                'cms_page_category_meta_title_',
                'cms_page_category_meta_description_',
                'cms_page_category_meta_keywords_'
            ],
            "ps175": [
                'name_',
                'description_',
                'meta_title_',
                'meta_description_',
                'meta_keywords_'
            ],
        },
        colData: {
            "ps176": {
                'cms_page_category_name_': 'name',
                'cms_page_category_description_': 'meta_title',
                'cms_page_category_meta_title_': 'description',
                'cms_page_category_meta_description_': 'meta_description',
                'cms_page_category_meta_keywords_': 'meta_keywords',
            },
            "ps175": {
                'name_': 'name',
                'description_': 'meta_title',
                'meta_title_': 'description',
                'meta_description_': 'meta_description',
                'meta_keywords_': 'meta_keywords',
            },
        },
        rewriteData: {
            name: ['cms_page_category_name_', 'name_'],
            rewrite: ['cms_page_category_friendly_url_', 'link_rewrite_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            var dataTrans = {};
            fieldTrans = fieldTrans || '';
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    dataTrans[fieldTrans] = ($('#' + fieldTrans + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            if ($('#cms_page_category_name_' + id_lang).length) {
                $.each(etsTranslateDefine.cms_category.fields.ps176, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            } else if ($('#name_' + id_lang).length) {
                $.each(etsTranslateDefine.cms_category.fields.ps175, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }
            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#cms_page_category_grid_table').length) {
                $('#cms_page_category_grid_table input[name="cms_page_category_bulk[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            } else if ($('#table-cms_category').length) {
                $('#table-cms_category input[name="cms_categoryBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            return ids;
        }
    },
    manufacturer: {
        fields: {
            "ps176": [
                'manufacturer_short_description_',
                'manufacturer_description_',
                'manufacturer_meta_title_',
                'manufacturer_meta_description_',
                'manufacturer_meta_keyword_',
            ],
            "ps175": [
                'short_description_',
                'description_',
                'meta_title_',
                'meta_description_',
                'meta_keywords_',
            ],
        },
        colData: {
            "ps176": {
                'manufacturer_short_description_': 'description',
                'manufacturer_description_': 'short_description',
                'manufacturer_meta_title_': 'meta_title',
                'manufacturer_meta_description_': 'meta_description',
                'manufacturer_meta_keyword_': 'meta_keywords',
            },
            "ps175": {
                'short_description_': 'description',
                'description_': 'short_description',
                'meta_title_': 'meta_title',
                'meta_description_': 'meta_description',
                'meta_keywords_': 'meta_keywords',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || '';
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    dataTrans[fieldTrans] = ($('#' + fieldTrans + id_lang).val() || '').replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            if ($('#manufacturer_description_' + id_lang).length) {
                $.each(etsTranslateDefine.manufacturer.fields.ps176, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            } else if ($('#short_description_' + id_lang).length) {
                $.each(etsTranslateDefine.manufacturer.fields.ps175, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        dataTrans[item] = ($('#' + item + id_lang).val() || '').replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }
            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#manufacturer_grid_table').length) {
                $('#manufacturer_grid_table input[name="manufacturer_bulk[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            } else if ($('#table-manufacturer').length) {
                $('#table-manufacturer input[name="manufacturerBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            return ids;
        }
    },
    supplier: {
        fields: {
            "ps175": [
                'description_',
                'meta_title_',
                'meta_description_',
                'meta_keywords_',
            ],
            "ps176": [
                'supplier_description_',
                'supplier_meta_title_',
                'supplier_meta_description_',
                'supplier_meta_keyword_',
            ],
        },
        colData: {
            "ps175": {
                'description_': 'description',
                'meta_title_': 'meta_title',
                'meta_description_': 'meta_description',
                'meta_keywords_': 'meta_keywords',
            },
            "ps176": {
                'supplier_description_': 'description',
                'supplier_meta_title_': 'meta_title',
                'supplier_meta_description_': 'meta_description',
                'supplier_meta_keyword_': 'meta_keywords',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    var keywords = $('#' + fieldTrans + id_lang).val() || '';
                    if ($('#' + fieldTrans + id_lang).hasClass('tagify')) {
                        keywords = $('#' + fieldTrans + id_lang).tagify('serialize');
                    }
                    dataTrans[fieldTrans] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            if ($('#supplier_description_' + id_lang).length){
                $.each(etsTranslateDefine.supplier.fields.ps176, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        var keywords = $('#' + item + id_lang).val() || '';
                        if ($('#' + item + id_lang).hasClass('tagify')) {
                            keywords = $('#' + item + id_lang).tagify('serialize');
                        }
                        dataTrans[item] = keywords.replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }
            else{
                $.each(etsTranslateDefine.supplier.fields.ps175, function (i, item) {
                    if (item.indexOf('keywords') !== -1) {
                        var keywords = $('#' + item + id_lang).val() || '';
                        if ($('#' + item + id_lang).hasClass('tagify')) {
                            keywords = $('#' + item + id_lang).tagify('serialize');
                        }
                        dataTrans[item] = keywords.replace(/,/g, '|');
                    } else
                        dataTrans[item] = $('#' + item + id_lang).val() || '';
                });
            }


            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#supplier_grid_table').length) {
                $('#supplier_grid_table input[name="supplier_bulk[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }
            else{
                $('#table-supplier input[name="supplierBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    attribute_group: {
        fields: {
            "ps175": [
                'name_',
                'public_name_',
                'meta_title_',
            ],
            "ps176": [
                'name_',
                'public_name_',
                'meta_title_',
            ],
        },
        colData: {
            "ps175": {
                'name_': 'name',
                'public_name_': 'public_name',
                'meta_title_': 'meta_title',
            },
            "ps176": {
                'name_': 'name',
                'public_name_': 'public_name',
                'meta_title_': 'meta_title',
            },
        },
        rewriteData: {
            name: ['name'],
            rewrite: ['url_name_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    var keywords = $('#' + fieldTrans + id_lang).val() || '';
                    if ($('#' + fieldTrans + id_lang).hasClass('tagify')) {
                        keywords = $('#' + fieldTrans + id_lang).tagify('serialize');
                    }
                    dataTrans[fieldTrans] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('[name="' + fieldTrans + id_lang+'"]').val() || '';
                return dataTrans;
            }

            $.each(etsTranslateDefine.attribute_group.fields.ps176, function (i, item) {
                if (item.indexOf('keywords') !== -1) {
                    var keywords = $('#' + item + id_lang).val() || '';
                    if ($('#' + item + id_lang).hasClass('tagify')) {
                        keywords = $('#' + item + id_lang).tagify('serialize');
                    }
                    dataTrans[item] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[item] = $('input[name="' + item + id_lang+'"]').val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-attribute_group').length) {
                $('#table-attribute_group input[name="attribute_groupBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    attribute: {
        fields: {
            "ps175": [
                'name_',
                'meta_title_',
            ],
            "ps176": [
                'name_',
                'meta_title_',
            ],
        },
        colData: {
            "ps175": {
                'name_': 'name',
                'meta_title_': 'meta_title',
            },
            "ps176": {
                'name_': 'name',
                'meta_title_': 'meta_title',
            },
        },
        rewriteData: {
            name: ['name'],
            rewrite: ['url_name_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    var keywords = $('#' + fieldTrans + id_lang).val() || '';
                    if ($('#' + fieldTrans + id_lang).hasClass('tagify')) {
                        keywords = $('#' + fieldTrans + id_lang).tagify('serialize');
                    }
                    dataTrans[fieldTrans] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('[name="' + fieldTrans + id_lang+'"]').val() || '';
                return dataTrans;
            }

            $.each(etsTranslateDefine.attribute.fields.ps176, function (i, item) {
                if (item.indexOf('keywords') !== -1) {
                    var keywords = $('#' + item + id_lang).val() || '';
                    if ($('#' + item + id_lang).hasClass('tagify')) {
                        keywords = $('#' + item + id_lang).tagify('serialize');
                    }
                    dataTrans[item] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[item] = $('input[name="' + item + id_lang+'"]').val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-attribute').length) {
                $('#table-attribute input[name="attribute_valuesBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    feature: {
        fields: {
            "ps175": [
                'name_',
                'meta_title_',
            ],
            "ps176": [
                'name_',
                'meta_title_',
            ],
        },
        colData: {
            "ps175": {
                'name_': 'name',
                'meta_title_': 'meta_title',
            },
            "ps176": {
                'name_': 'name',
                'meta_title_': 'meta_title',
            },
        },
        rewriteData: {
            name: ['name'],
            rewrite: ['url_name_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    var keywords = $('#' + fieldTrans + id_lang).val() || '';
                    if ($('#' + fieldTrans + id_lang).hasClass('tagify')) {
                        keywords = $('#' + fieldTrans + id_lang).tagify('serialize');
                    }
                    dataTrans[fieldTrans] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('[name="' + fieldTrans + id_lang+'"]').val() || '';
                return dataTrans;
            }

            $.each(etsTranslateDefine.feature.fields.ps176, function (i, item) {
                if (item.indexOf('keywords') !== -1) {
                    var keywords = $('#' + item + id_lang).val() || '';
                    if ($('#' + item + id_lang).hasClass('tagify')) {
                        keywords = $('#' + item + id_lang).tagify('serialize');
                    }
                    dataTrans[item] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[item] = $('input[name="' + item + id_lang+'"]').val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-feature').length) {
                $('#table-feature input[name="featureBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    feature_value: {
        fields: {
            "ps175": [
                'value_',
                'meta_title_',
            ],
            "ps176": [
                'value_',
                'meta_title_',
            ],
        },
        colData: {
            "ps175": {
                'value_': 'value',
                'meta_title_': 'meta_title',
            },
            "ps176": {
                'value_': 'value',
                'meta_title_': 'meta_title',
            },
        },
        rewriteData: {
            name: ['name'],
            rewrite: ['url_name_']
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if (fieldTrans.indexOf('keywords') !== -1) {
                    var keywords = $('#' + fieldTrans + id_lang).val() || '';
                    if ($('#' + fieldTrans + id_lang).hasClass('tagify')) {
                        keywords = $('#' + fieldTrans + id_lang).tagify('serialize');
                    }
                    dataTrans[fieldTrans] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[fieldTrans] = $('[name="' + fieldTrans + id_lang+'"]').val() || '';
                return dataTrans;
            }

            $.each(etsTranslateDefine.feature_value.fields.ps176, function (i, item) {
                if (item.indexOf('keywords') !== -1) {
                    var keywords = $('#' + item + id_lang).val() || '';
                    if ($('#' + item + id_lang).hasClass('tagify')) {
                        keywords = $('#' + item + id_lang).tagify('serialize');
                    }
                    dataTrans[item] = keywords.replace(/,/g, '|');
                } else
                    dataTrans[item] = $('input[name="' + item + id_lang+'"]').val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-feature_value').length) {
                $('#table-feature_value input[name="feature_valueBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    blockreassurance: {
        isNewVersion: function () {
            return etsTransPageType == 'blockreassurance' && $('#blockDisplay').length;
        },
        fields: {
            "ps176": [
                'text_',
            ],
            "ps177": [
                'title-',
                'description-',
            ],
        },
        colData: {
            "ps176": {
                'text_': 'text',
            },
            "ps177": {
                'title-': 'title',
                'description-': 'description',
            },
        },

        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if(etsTranslateDefine.blockreassurance.isNewVersion()){
                    dataTrans[fieldTrans] = $('#blockDisplay .show-rea-block.active input[name="'+fieldTrans + id_lang+'"],#blockDisplay .show-rea-block.active textarea[name="'+fieldTrans + id_lang+'"]').val() || '';
                }
                else
                    dataTrans[fieldTrans] = $('#'+fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            if(etsTranslateDefine.blockreassurance.isNewVersion()){
                $.each(etsTranslateDefine.blockreassurance.fields.ps177, function (i, item) {
                    dataTrans[item] = $('#blockDisplay .show-rea-block.active input[name="'+item + id_lang+'"],#blockDisplay .show-rea-block.active textarea[name="'+item + id_lang+'"]').val() || '';
                });
            }
            else{
                $.each(etsTranslateDefine.blockreassurance.fields.ps176, function (i, item) {
                    dataTrans[item] = $('#'+item + id_lang).val() || '';
                });
            }

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-feature_value').length) {
                $('#table-feature_value input[name="feature_valueBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    ps_linklist: {
        fields: {
            "ps176": [
                'form_link_block_block_name_',
                'form_link_block_custom_',
            ],
        },
        colData: {
            "ps176": {
                'form_link_block_block_name_': 'text',
            },
        },

        getFormData: function (id_lang, fieldTrans, id_lang_source) {
            if (!id_lang) {
                return null;
            }
            id_lang_source = id_lang_source || null;
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                if(fieldTrans.indexOf('form_link_block_custom_') !== -1) {
                    var idField = fieldTrans.replace(/([0-9]+)([_title|_url]+)$/, id_lang+'$2');
                    dataTrans[fieldTrans] = $('#' + idField).val() || '';
                }
                else
                    dataTrans[fieldTrans] = $('#'+fieldTrans + id_lang).val() || '';
                return dataTrans;
            }

            $.each(etsTranslateDefine.ps_linklist.fields.ps176, function (i, item) {
                if(item == 'form_link_block_custom_'){
                    $('[id*="form_link_block_custom_"]').each(function () {
                        var idEl = $(this).attr('id');
                        if(idEl.indexOf(id_lang+'_title') !== -1){
                            if(id_lang_source)
                                dataTrans[idEl.replace(/([0-9]+)([_title|_url]+)$/, id_lang_source+'$2')] = $(this).val();
                            else
                                dataTrans[idEl] = $(this).val();
                        }
                    });
                }
                else
                    dataTrans[item] = $('#'+item + id_lang).val() || '';
            });

            return dataTrans;
        },
        getBulkTransIds: function () {
            var ids = [];
            if ($('#table-feature_value').length) {
                $('#table-feature_value input[name="feature_valueBox[]"]:checked').each(function () {
                    ids.push($(this).val());
                });
            }

            return ids;
        }
    },
    ps_mainmenu: {
        fields: {
            "ps176": [
                'label_',
            ],
        },
        colData: {
            "ps176": {
                'label_': 'label',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            $.each(etsTranslateDefine.ps_mainmenu.fields.ps176, function (i, item) {
                dataTrans[item] = $('#' + item + id_lang).val() || '';
            });
            return dataTrans;
        },
    },
    ps_customtext: {
        fields: {
            "ps176": [
                'text_',
            ],
        },
        colData: {
            "ps176": {
                'text_': 'text',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            $.each(etsTranslateDefine.ps_customtext.fields.ps176, function (i, item) {
                dataTrans[item] = $('#' + item + id_lang).val() || '';
            });
            return dataTrans;
        },
    },
    ps_imageslider: {
        fields: {
            "ps176": [
                'title_',
                'legend_',
                'description_',
            ],
        },
        colData: {
            "ps176": {
                'title_': 'title',
                'legend_': 'legend',
                'description_': 'description',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            $.each(etsTranslateDefine.ps_imageslider.fields.ps176, function (i, item) {
                dataTrans[item] = $('#' + item + id_lang).val() || '';
            });
            return dataTrans;
        },
    },
    ets_extraproducttabs: {
        fields: {
            "ps176": [
                'name_',
                'content_',
            ],
        },
        colData: {
            "ps176": {
                'name_': 'name',
                'content_': 'content',
            },
        },
        getFormData: function (id_lang, fieldTrans) {
            if (!id_lang) {
                return null;
            }
            fieldTrans = fieldTrans || null;
            var dataTrans = {};
            if (fieldTrans) {
                dataTrans[fieldTrans] = $('#' + fieldTrans + id_lang).val() || '';
                return dataTrans;
            }
            $.each(etsTranslateDefine.ets_extraproducttabs.fields.ps176, function (i, item) {
                dataTrans[item] = $('#' + item + id_lang).val() || '';
            });
            return dataTrans;
        },
    },
    onClickTransListItem: function (el) {
        var idItem = 0;
        var pageType = null;
        var idKey = '';
        if (etsTransPageType == 'product') {
            idItem = $(el).closest('tr').attr('data-product-id') || 0;
        } else if (etsTransPageType == 'category') {
            if ($(el).parent().find('a[data-category-id]').length)
                idItem = $(el).parent().find('a[data-category-id]').first().attr('data-category-id') || 0;
            else {
                var idTr = $(el).closest('tr').attr('id') || '';
                var trSplit = idTr.split('_');
                if (trSplit.length > 2) {
                    idItem = trSplit[2];
                }
            }
            idKey = 'id_category';
        } else if (etsTransPageType == 'cms') {
            var idTable = $(el).closest('table').attr('id');
            if (idTable == 'cms_page_grid_table' || idTable == 'table-cms') {
                pageType = 'cms';
                var trId = $(el).closest('tr').attr('id') || '';
                var splitTr = trId.split('_');
                if (idTable == 'table-cms') {
                    if (splitTr.length > 2) {
                        idItem = splitTr[2];
                    }
                } else {
                    if (splitTr.length > 1) {
                        idItem = splitTr[1];
                    }
                }
                idKey = 'id_cms';
            } else if (idTable == 'cms_page_category_grid_table' || idTable == 'table-cms_category') {
                pageType = 'cms_category';
                var trId = $(el).closest('tr').attr('id') || '';
                var splitTr = trId.split('_');
                if (idTable == 'table-cms_category') {
                    if (splitTr.length > 2) {
                        idItem = splitTr[2];
                    }
                } else {
                    if (splitTr.length > 1) {
                        idItem = splitTr[1];
                    }
                }
                idKey = 'id_cms_category';
            }
        } else if (etsTransPageType == 'cms_category') {
            pageType = 'cms_category';
            var trId = $(el).closest('tr').attr('id') || '';
            var splitTr = trId.split('_');
            if (splitTr.length > 1) {
                idItem = splitTr[1];
            }
            idKey = 'id_cms_category';
        } else if (etsTransPageType == 'manufacturer') {
            pageType = 'manufacturer';
            if ($(el).closest('tr').find('input[name*="manufacturer_bulk"]').length) {
                idItem = $(el).closest('tr').find('input[name*="manufacturer_bulk"]').val() || 0;
            } else if ($(el).closest('tr').find('input[name*="manufacturerBox"]').length) {
                idItem = $(el).closest('tr').find('input[name*="manufacturerBox"]').val() || 0;
            }
            if (!idItem) {
                var urlItem = $(el).closest('.dropdown-menu').find('a').first().attr('href') || '';
                if (urlItem) {
                    var matchesUrl = urlItem.match(/\/brands\/(\d+)\//);
                    if (matchesUrl !== null && typeof matchesUrl[1] !== 'undefined') {
                        idItem = matchesUrl[1];
                    } else
                        idItem = etsTranslateDefine.getParameterByName('id_manufacturer', urlItem) || 0;
                }
            }
            idKey = 'id_manufacturer';
        }
        else if (etsTransPageType == 'supplier') {
            pageType = 'supplier';
            if($(el).closest('tr').find('input[name="supplier_bulk[]"]').length){
                idItem = $(el).closest('tr').find('input[name="supplier_bulk[]"]').val() || 0;
            }
            else
                idItem = $(el).closest('tr').find('input[name*="supplierBox"]').val() || 0;
            idKey = 'id_supplier';
        }
        else if (etsTransPageType == 'attribute_group') {
            pageType = 'attribute_group';
            if($(el).closest('tr').find('input[name="attribute_groupBox[]"]').length){
                idItem = $(el).closest('tr').find('input[name="attribute_groupBox[]"]').val() || 0;
            }
            else
                idItem = $(el).closest('tr').find('input[name*="attribute_groupBox"]').val() || 0;
            idKey = 'id_attribute_group';
        }
        else if (etsTransPageType == 'attribute') {
            pageType = 'attribute';
            if($(el).closest('tr').find('input[name="attribute_valuesBox[]"]').length){
                idItem = $(el).closest('tr').find('input[name="attribute_valuesBox[]"]').val() || 0;
            }
            else
                idItem = $(el).closest('tr').find('input[name*="attribute_valuesBox"]').val() || 0;
            idKey = 'id_attribute';
        }
        else if (etsTransPageType == 'feature') {
            pageType = 'feature';
            if($(el).closest('tr').find('input[name="featureBox[]"]').length){
                idItem = $(el).closest('tr').find('input[name="featureBox[]"]').val() || 0;
            }
            else
                idItem = $(el).closest('tr').find('input[name*="featureBox"]').val() || 0;
            idKey = 'id_feature';
        }
        else if (etsTransPageType == 'feature_value') {
            pageType = 'feature_value';
            if($(el).closest('tr').find('input[name="feature_valueBox[]"]').length){
                idItem = $(el).closest('tr').find('input[name="feature_valueBox[]"]').val() || 0;
            }
            else
                idItem = $(el).closest('tr').find('input[name*="feature_valueBox"]').val() || 0;
            idKey = 'id_feature_value';
        }
        else if (etsTransPageType == 'blockreassurance') {
            pageType = 'blockreassurance';
            idItem = 0;
            if(etsTranslateDefine.blockreassurance.isNewVersion()){
                idItem = $(el).parent().find('.psre-edit').attr('data-id') || 0;
            }
            else{
                idKey = 'id_reassurance';
                var urlItemBlock = $(el).closest('.btn-group-action').find('a').first().attr('href') || '';

                if (urlItemBlock) {
                    idItem = etsTranslateDefine.getParameterByName(idKey, urlItemBlock) || 0;
                }
            }

        }
        else if (etsTransPageType == 'ps_linklist') {
            pageType = 'ps_linklist';
            idItem = $(el).closest('tr').attr('id').split('_')[1];
            idKey = 'id_link_block';
        }
        else if (etsTransPageType == 'ps_mainmenu') {
            pageType = 'ps_mainmenu';
            idItem = 0;
            idKey = 'id_linksmenutop';
            var urlItemBlock = $(el).closest('.btn-group-action').find('a').first().attr('href') || '';
            if (urlItemBlock) {
                idItem = etsTranslateDefine.getParameterByName(idKey, urlItemBlock) || 0;
            }
        }
        else if (etsTransPageType == 'ps_imageslider') {
            pageType = 'ps_imageslider';
            idItem = 0;
            idKey = 'id_slide';
            var urlItemBlock = $(el).closest('.btn-group-action').find('a').first().attr('href') || '';
            if (urlItemBlock) {
                idItem = etsTranslateDefine.getParameterByName(idKey, urlItemBlock) || 0;
            }
        }
        else if (etsTransPageType == 'ets_extraproducttabs') {
            pageType = 'ets_extraproducttabs';
            idItem = $(el).closest('.extra-tab-item').attr('data-tab-id');
        }

        if (!idItem) {
            var urlItem = $(el).closest('ul.dropdown-menu').find('a').first().attr('href') || '';
            if (urlItem) {
                idItem = etsTranslateDefine.getParameterByName(idKey, urlItem) || 0;
            }
        }
        
        if (!idItem) {
            alert(etsTranslateDefine.trans('can_not_trans_item'));
            return;
        }
        var ids = [];
        ids.push(idItem);
        if(ETS_TRANS_IS_AUTO_CONFIG){
            var defaultConfig = {};
            if(typeof ETS_TRANS_DEFAULT_CONFIG !== 'undefined'){
                defaultConfig = ETS_TRANS_DEFAULT_CONFIG;
            }
            var formData = {
                trans_all: 0,
                trans_source: defaultConfig.lang_source || '',
                trans_target: (defaultConfig.lang_target|| '').split(','),
                trans_option: defaultConfig.field_option || '',
                page_id: ids
            };
            formData.trans_data = etsTranslateDefine.getTransDataPage(formData.trans_source, formData.trans_target, formData.trans_option, pageType);
            etsTransFunc.showTranslatingField();
            etsTranslateDefine.translatePage(formData, $(el), pageType);
        }
        else{
            etsTranslateDefine.getFormSelectTranslate(ids, pageType, 0, null, el);
        }
        return false;
    },
    getParameterByName: function (name, url) {
        return etsTransFunc.getParameterByName(name, url);
    },
    setLinkRewriteTrans: function (id_lang) {
        switch (etsTransPageType) {
            case 'product':
                var name = $('#form_step1_name_' + id_lang).val() || '';
                if (name) {
                    var linkRewrite = str2url(name);
                    $('#form_step5_link_rewrite_' + id_lang).val(linkRewrite);
                }
                break;
            case 'category':
                var name = $('#category_name_' + id_lang).val() || '';
                if (name) {
                    var linkRewrite = str2url(name);
                    $('#category_link_rewrite_' + id_lang).val(linkRewrite);
                }
                break;
            case 'cms':
                var name = $('#cms_page_title_' + id_lang).val() || '';
                if (name) {
                    var linkRewrite = str2url(name);
                    $('#cms_page_friendly_url_' + id_lang).val(linkRewrite);
                }
                break;
            case 'cms_category':
                var name = $('#cms_page_category_name_' + id_lang).val() || '';
                if (name) {
                    var linkRewrite = str2url(name);
                    $('#cms_page_category_friendly_url_' + id_lang).val(linkRewrite);
                }
                break;
            default:
                break;
        }
    },
    stopTranslatePage: function () {
        if (etsTranslateDefine.ajaxXhrTranslatePage && etsTranslateDefine.ajaxXhrTranslatePage.readyState != 4) {
            etsTranslateDefine.ajaxXhrTranslatePage.abort();
        }
        etsTranslateDefine.stopTranslate = true;
    },

    initBtnTranslate: function () {
        if(etsTransPageType == 'blockreassurance' && $('#blockDisplay').length){
            etsTransIsDetailPage = 1;
        }
        if (etsTransIsDetailPage) {
            switch (etsTransPageType) {
                case 'product':
                    $('#product_form_go_to_sales').before(etsTranslateDefine.renderBtnTransToolbarProduct());
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.product.fields.ps17, function (i, el) {
                            $('[id*=' + el + ']').each(function () {
                                if (!$(this).closest('.translations').find('.js-ets-trans-btn-trans-field-item ').length) {
                                    
                                    $(this).closest('.translations').addClass('ets-trans-field-boundary').append(etsTranslateDefine.renderBtnTransFieldItem(el)).parent().addClass('ets_button_top');
                                    
                                    if ($(this).is('textarea')) {
                                        $(this).closest('.translations').addClass('form-helper-editor');
                                    }
                                }
                            });
                        });
                    }
                    break;
                case 'category':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    if ($('[id*="category_name_"]').length){
                        $('.toolbar-icons .wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('btn btn-outline-secondary'));
                    }
                    else
                        $('form.form-horizontal').prepend(etsTranslateDefine.renderBtnTransToolbar());
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if ($('[id*="category_name_"]').length) {
                            $.each(etsTranslateDefine.category.fields.ps176, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                            if ($(this).is('textarea')) {
                                                if ($(this).closest('.form-group').find('.translations.tabbable').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_button_top table_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                }
                                            } else {
                                                if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                }
                                            }
                                        }
                                    }

                                });
                            });
                        } else {
                            $.each(etsTranslateDefine.category.fields.ps175, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0 || $(this).closest('.form-group').parent().closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets_has_multi_field');
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.form-group').addClass('ets_has_multi_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                    }
                                });
                            });
                        }
                    }

                    break;
                case 'cms':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    if ($('[id*="cms_page_title_"]').length){
                        $('.toolbar-icons .wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('btn btn-outline-secondary'));
                    }
                    else
                        $('form.form-horizontal').prepend(etsTranslateDefine.renderBtnTransToolbar());
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if ($('[id*="cms_page_title_"]').length) {
                            $.each(etsTranslateDefine.cms.fields.ps176, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    
                                    if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                        if ($(this).is('textarea')) {
                                            if ($(this).closest('.form-group').find('.translations.tabbable').length > 0) {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_button_top table_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                $(this).closest('.form-group').addClass('form-helper-editor');
                                            } else {
                                                $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                $(this).closest('.form-group').addClass('form-helper-editor');
                                            }
                                        } else {
                                            if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            }
                                        }
                                    }
                                    
                                });
                            });
                        } else {
                            $.each(etsTranslateDefine.cms.fields.ps175, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets_has_multi_field');
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.form-group').addClass('ets_has_multi_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                            var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                            $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                        }
                                    }
                                });
                            });
                        }
                    }
                    break;
                case 'cms_category':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    if ($('[id*="cms_page_category_name_"]').length){
                        $('.toolbar-icons .wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('btn btn-outline-secondary'));
                    }
                    else
                        $('form.form-horizontal').prepend(etsTranslateDefine.renderBtnTransToolbar());
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if ($('[id*="cms_page_category_name_"]').length) {
                            $.each(etsTranslateDefine.cms_category.fields.ps176, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                            if ($(this).is('textarea')) {
                                                if ($(this).closest('.form-group').find('.translations.tabbable').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_button_top table_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                }
                                            } else {
                                                if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                }
                                            }
                                        }
                                });
                            });
                        } else {
                            $.each(etsTranslateDefine.cms_category.fields.ps175, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets_has_multi_field');
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.form-group').addClass('ets_has_multi_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                            var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                            $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                        }
                                    }
                                });
                            });
                        }
                    }
                    break;
                case 'manufacturer':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    var className = ETS_TRANS_HAS_MODULE_SEO == 1 ? ' has-module-seo' : '';
                    if ($('[id*="manufacturer_short_description_"]').length){
                        $('.toolbar-icons .wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('brand-page btn btn-outline-secondary' + className));
                    }
                    else
                        $('form.form-horizontal').prepend(etsTranslateDefine.renderBtnTransToolbar2('brand-page btn btn-outline-secondary' + className));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if ($('[id*="manufacturer_short_description_"]').length) {
                            $.each(etsTranslateDefine.manufacturer.fields.ps176, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    /*if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets_has_multi_field');
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.form-group').addClass('ets_has_multi_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').addClass('form-helper-editor')
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                            var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                            $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                        }
                                    }*/
                                    if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                            if ($(this).is('textarea')) {
                                                if ($(this).closest('.form-group').find('.translations.tabbable').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_button_top table_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    $(this).closest('.form-group').addClass('form-helper-editor');
                                                }
                                            } else {
                                                if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                }
                                            }
                                        }
                                });
                            });
                        } else {
                            $.each(etsTranslateDefine.manufacturer.fields.ps175, function (i, el) {
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets_has_multi_field');
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.form-group').addClass('ets_has_multi_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                            var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                            $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                        }
                                    }
                                });
                            });
                        }
                    }
                    break;
                case 'supplier':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    var className = ETS_TRANS_HAS_MODULE_SEO == 1 ? ' has-module-seo' : '';
                    if ($('[id*="supplier_description_"]').length){
                        $('.toolbar-icons .wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('supplier-page btn btn-outline-secondary' + className));
                    }
                    else
                        $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransToolbar('supplier-page btn btn-outline-secondary' + className, true));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if ($('.js-ets-trans-btn-trans-toolbar.supplier-page ~ .panel').length > 0) {
                            $('.js-ets-trans-btn-trans-toolbar.supplier-page').addClass('pos_with_panel');
                        }
                        if ($('.js-ets-trans-btn-trans-toolbar.supplier-page ~ .card').length > 0) {
                            $('.js-ets-trans-btn-trans-toolbar.supplier-page').addClass('pos_with_card');
                        }
                        if ($('[id*="supplier_description_"]').length) {
                            $.each(etsTranslateDefine.supplier.fields.ps176, function (i, el) {
                                
                                $('[id*=' + el + ']').each(function () {
                                    if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                            if ($(this).is('textarea')) {
                                                if ($(this).closest('.form-group').find('.translations.tabbable').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_button_top table_lang').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    //$(this).closest('.form-group').addClass('form-helper-editor');
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.col-lg-2').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                    //$(this).closest('.form-group').addClass('form-helper-editor');
                                                }
                                            } else {
                                                if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                    $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').find('.dropdown').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                } else {
                                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                }
                                            }
                                        }
                                });
                            });
                        } else {
                            $.each(etsTranslateDefine.supplier.fields.ps175, function (i, el) {
                                
                                $('[id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        
                                        if ($(this).closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                        }
                                        
                                    }
                                });
                            });
                        }
                    }
                    break;
                case 'attribute_group':
                    $('form.form-horizontal').addClass('ets-trans-form-page sad');
                    $('.toolbarBox .btn-toolbar #toolbar-nav').prepend(etsTranslateDefine.renderBtnTransToolbar('attribute_group-page toolbar-module ', true));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.attribute_group.fields.ps176, function (i, el) {
                            $('form.form-horizontal [name*=' + el + ']').each(function () {
                                if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            
                                        } else if (el == 'meta_title_') {
                                            if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ) {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary sa').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            }
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                        if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                            $(this).closest('.form-group').addClass('ets_has_multi_locale');
                                            var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                            $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                        }
                                    }
                                }
                                
                            });
                        });
                    }
                    break;
                case 'attribute':
                    $('form.form-horizontal').addClass('ets-trans-form-page sda');
                    $('.toolbarBox .btn-toolbar #toolbar-nav').prepend(etsTranslateDefine.renderBtnTransToolbar('attribute-page toolbar-module ', true));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.attribute.fields.ps176, function (i, el) {
                            $('form.form-horizontal [name*=' + el + ']').each(function () {
                                if ( $(this).closest('.form-group').find('.ets-trans-button').length <= 0 ){
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            
                                        } else if (el == 'meta_title_') {
                                            if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ) {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary sa').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            }
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    }
                    break;
                case 'feature':
                    $('form.form-horizontal').addClass('ets-trans-form-page asd');
                    $('.toolbarBox .btn-toolbar #toolbar-nav').prepend(etsTranslateDefine.renderBtnTransToolbar('feature-page toolbar-module ', true));
                    
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.attribute.fields.ps176, function (i, el) {
                            $('form.form-horizontal [name*=' + el + ']').each(function () {
                                if ($(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length <= 0 ) {
                                    if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                        $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else if (el == 'meta_title_') {
                                        if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ) {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary sa').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        }
                                    } else {
                                        $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        if ($(this).is('textarea')) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                        }
                                    }
                                }
                            });
                        });
                    }
                    break;
                case 'feature_value':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    $('.toolbarBox .btn-toolbar #toolbar-nav').prepend(etsTranslateDefine.renderBtnTransToolbar('feature_value-page toolbar-module ', true));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.feature_value.fields.ps176, function (i, el) {
                            $('form.form-horizontal [name*=' + el + ']').each(function () {
                                if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                    if ( $(this).closest('.form-group').find('.translatable-field .col-lg-2 .ets-trans-button').length <= 0 ){
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else if (el == 'meta_title_') {
                                            if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ) {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary sa').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            }
                                        } else {
                                            $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    }
                    break;
                case 'blockreassurance':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    $('.toolbarBox>.btn-toolbar>ul.nav').prepend(etsTranslateDefine.renderBtnTransToolbar('', true));
                    $('#blockDisplay .panel-heading').append(etsTranslateDefine.renderBtnTransToolbar2('blockreassurance btn btn-outline-secondary pull-right')); 
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        if($('#blockDisplay').length){
                            $.each(etsTranslateDefine.blockreassurance.fields.ps177, function (i, el) {
                                $('[name*="' + el + '"]').each(function () {
                                    if ( $(this).closest('.form-group').find('.col-md-7.col-lg-4').length > 0 ){
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_trans_button_t2').find('.col-md-7.col-lg-4').append(etsTranslateDefine.renderBtnTransFieldItem(el))
                                    } else {
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    }
                                    
                                });
                            });
                            $('#reminder_listing').find('.psre-edit').after(etsTranslateDefine.renderBtnTransListItem(' trans_reassurance_field'));
                        }
                        else{
                            $.each(etsTranslateDefine.blockreassurance.fields.ps176, function (i, el) {
                                $('form.form-horizontal [id*=' + el + ']').each(function () {
                                    if (!$(this).closest('.form-group').parent().closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                        if ($(this).closest('.form-group').parent().closest('.form-group').find('.translatable-field').length > 0) {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').find('.translatable-field').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else if (el == 'meta_title_') {
                                            $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        } else {
                                            if ( $(this).closest('.form-group').hasClass('.content_by_lang').length > 0 ){
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_trans_button_t2').find('.col-lg-4').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            
                                            } else {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            
                                            }
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').parent().closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                    }
                                    
                                });
                            });
                        }
                    }
                    break;
                case 'ps_linklist':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransToolbar2('btn btn-outline-secondary'));
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.ps_linklist.fields.ps176, function (i, el) {
                            $('form.form-horizontal [id*=' + el + ']').each(function () {
                                if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length && $(this).attr('id').indexOf('_url') === -1) {
                                    var fieldTrans = el;
                                    if(el == 'form_link_block_custom_'){
                                        fieldTrans = $(this).attr('id');
                                    }
                                    
                                    $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper linklist_field_button').append(etsTranslateDefine.renderBtnTransFieldItem(fieldTrans));
                                    
                                    if ($(this).is('textarea')) {
                                        $(this).closest('.form-group').addClass('form-helper-editor');
                                    }
                                }
                            });
                        });
                    }
                    break;
                case 'ps_mainmenu':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.ps_mainmenu.fields.ps176, function (i, el) {
                            $('form.form-horizontal [id*=' + el + ']').each(function () {
                                if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length ) {
                                    //$(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper sss').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper ets_has_multi_locale').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                        $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                    } else if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ){
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary ets_has_multi_locale').find('.translatable-field .col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else {
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper sss').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    }
                                    if ($(this).is('textarea')) {
                                        $(this).closest('.form-group').addClass('form-helper-editor');
                                    }
                                }
                            });
                        });
                    }
                    $('#module_form_1 .panel .panel-heading').append(etsTranslateDefine.renderBtnTransToolbar2('btn ps_mainmenu pull-right'));
                    $('table#table-linksmenutop tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransAll2('', true));
                    $('#form-linksmenutop .panel h3').append(etsTranslateDefine.renderBtnTransToolbar2('ps_mainmenu pull-right mt_3'));
                    
                case 'ps_customtext':
                    $('form.form-horizontal').addClass('ets-trans-form-page ssps_customtext');
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.ps_customtext.fields.ps176, function (i, el) {
                            $('form.form-horizontal [id*=' + el + ']').each(function () {
                                if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                    //$(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                        $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                        $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                    } else if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ){
                                        $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary').find('.translatable-field .col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else if ( $(this).closest('.form-group.translatable-field').length > 0 ){
                                        $(this).closest('.form-group.translatable-field').addClass('ets-trans-field-boundary ets_has_multi_locale').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else {
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper sss').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    }
                                    if ($(this).is('textarea')) {
                                        $(this).closest('.form-group').addClass('form-helper-editor');
                                    }
                                }
                            });
                        });
                    }
                    $('.toolbarBox>.btn-toolbar>ul').prepend(etsTranslateDefine.renderBtnTransToolbar('ps_customtext', true));
                    break;
                case 'ps_imageslider':
                    $('form.form-horizontal').addClass('ets-trans-form-page');
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $.each(etsTranslateDefine.ps_imageslider.fields.ps176, function (i, el) {
                            $('form.form-horizontal [id*=' + el + ']').each(function () {
                                if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                    //$(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                        $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                        var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                        $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                    } else if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ){
                                        $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary').find('.translatable-field .col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else if ( $(this).closest('.form-group.translatable-field').length > 0 ){
                                        $(this).closest('.form-group.translatable-field').addClass('ets-trans-field-boundary ets_has_multi_locale').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    } else {
                                        $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper sss').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                    }
                                    if ($(this).is('textarea')) {
                                        $(this).closest('.form-group').addClass('form-helper-editor');
                                    }
                                }
                            });
                        });
                    }
                    $('.toolbarBox>.btn-toolbar>ul').prepend(etsTranslateDefine.renderBtnTransToolbar('ps_imageslider', true));
                    break;
                case 'ets_extraproducttabs':
                    if(ETS_TRANS_ENABLE_TRANS_FIELD) {
                        $( document ).ajaxSuccess(function( event, xhr, settings ) {
                            var module = etsTransFunc.getParameterByName('configure', settings.url);
                            var isGetForm = etsTransFunc.getParameterByName('getFormFieldTab', settings.url);
                            var idTab = etsTransFunc.getParameterByName('id_tab', settings.url);
                            if(idTab && idTab != '0'){
                                etsTransPageId = idTab;
                            }
                            if ( module == 'ets_extraproducttabs' && isGetForm == 1 ) {
                                $('form.form-horizontal').addClass('ets-trans-form-page');
                                $.each(etsTranslateDefine.ets_extraproducttabs.fields.ps176, function (i, el) {
                                    $('form.form-horizontal [id*=' + el + ']').each(function () {
                                        if (!$(this).closest('.form-group').find('.js-ets-trans-btn-trans-field-item').length) {
                                            if ($(this).closest('.form-group').find('.locale-input-group').length > 0) {
                                                $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                                var space = $(this).closest('.form-group').find('.locale-input-group').find('.dropdown').width();
                                                $(this).closest('.form-group').find('.ets-trans-btn-trans-field-item').css('margin-right', space);
                                            } else if ( $(this).closest('.form-group').find('.translatable-field').length > 0 ){
                                                $(this).closest('.form-group').addClass('ets_has_multi_locale ets-trans-field-boundary').find('.translatable-field .col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else if ( $(this).closest('.form-group.translatable-field').length > 0 ){
                                                $(this).closest('.form-group.translatable-field').addClass('ets-trans-field-boundary ets_has_multi_locale').find('.col-lg-2').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            } else {
                                                $(this).closest('.form-group').addClass('ets-trans-field-boundary form-helper').append(etsTranslateDefine.renderBtnTransFieldItem(el));
                                            }
                                            if ($(this).is('textarea')) {
                                                $(this).closest('.form-group').addClass('form-helper-editor');
                                            }
                                        }
                                    });
                                });

                                if(!$('#etsEptModalDataTab .js-ets-trans-btn-trans-toolbar').length)
                                    $('#etsEptModalDataTab .js-ets-ept-btn-save-tab').before(etsTranslateDefine.renderBtnTransToolbar2('btn-default ets_uppercase btn ets_extraproducttabs'));
                            }
                        });

                    }
                    $('.toolbarBox>.btn-toolbar>ul').prepend(etsTranslateDefine.renderBtnTransAll('ets_extraproducttabs', true));
                    $('.extra-tab-item .extra-tab-actions').append(etsTranslateDefine.renderBtnTransListItem('ets_extraproducttabs'));
                    break;
            }
        } else {
            switch (etsTransPageType) {
                case 'product':
                    $('#product_catalog_list table tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('#product_bulk_menu').next('.dropdown-menu').append(etsTranslateDefine.renderBtnBulkTransList());
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    break;
                case 'category':
                    $('#category_grid_table tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#category_grid_bulk_action_delete_selection').after(etsTranslateDefine.renderBtnBulkTransList('', 1));
                    // <=1751
                    $('#table-category tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    $('.bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    break;
                case 'cms':
                    $('#cms_page_filter_form tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('#cms_page_grid_bulk_action_delete_bulk').after(etsTranslateDefine.renderBtnBulkTransList('', 1));
                    $('#cms_page_grid_bulk_action_delete_selection').after(etsTranslateDefine.renderBtnBulkTransList('', 1));

                    $('#cms_page_category_filter_form tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('is_cms_category'));
                    $('#cms_page_category_grid_bulk_action_delete_bulk').after(etsTranslateDefine.renderBtnBulkTransList('is_cms_category', 1));
                    $('#cms_page_category_grid_bulk_action_delete_selection').after(etsTranslateDefine.renderBtnBulkTransList('is_cms_category', 1));
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll('', false, etsTransFunc.trans('translate_pages')));
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll('is_cms_category', false, etsTransFunc.trans('translate_category_pages')));

                    // <=1751
                    $('#table-cms_category tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('is_cms_category', true));
                    $('#form-cms_category .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll('is_cms_category'));
                    $('#table-cms tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#form-cms .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#form-cms_category .bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList('is_cms_category')+'</li>');
                    $('#form-cms .bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');

                    $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true, etsTransFunc.trans('translate_pages')));
                    $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('is_cms_category', true, etsTransFunc.trans('translate_category_pages')));

                    break;
                case 'cms_category':
                    $('#cms_page_filter_form tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('#cms_page-grid-actions-button').before(etsTranslateDefine.renderBtnBulkTransList());
                    $('#cms_page_category_filter_form tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('is_cms_category'));
                    $('#cms_page_category-grid-actions-button').before(etsTranslateDefine.renderBtnBulkTransList('is_cms_category'));
                    // <=1751
                    $('#table-cms_category tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('is_cms_category', true));
                    $('#table-cms tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#form-cms_category .bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList('is_cms_category')+'</li>');
                    $('#form-cms .bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll('is_cms_category'));
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    break;
                case 'manufacturer':
                    $('#manufacturer_grid_table tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('#manufacturer_grid_bulk_action_delete_selection').after(etsTranslateDefine.renderBtnBulkTransList('', 1));
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    // <=1751
                    $('#table-manufacturer tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#form-manufacturer .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('.bulk-actions ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    break;
                case 'supplier':
                    $('#table-supplier tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#bulk_action_menu_supplier').next('ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    // => 1770
                    $('#supplier_grid_bulk_action_delete_selection').after(etsTranslateDefine.renderBtnBulkTransList('', 1));
                    $('#supplier_grid_table tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    break;
                case 'attribute_group':
                    if($('#toolbar-nav').length)
                        $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    else
                        $('#form-attribute_group .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#table-attribute_group tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    $('#bulk_action_menu_attribute_group').next('ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    break;
                case 'attribute':
                    $('#table-attribute tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    if($('#toolbar-nav').length)
                        $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    else
                        $('#form-attribute_values .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#bulk_action_menu_attribute').next('ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    break;
                case 'feature':
                    $('#table-feature tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    if($('#toolbar-nav').length)
                        $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    else
                        $('#form-feature .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#bulk_action_menu_feature').next('ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');
                    break;
                case 'feature_value':
                    $('#table-feature_value tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    if($('#toolbar-nav').length)
                        $('#toolbar-nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    else
                        $('#form-feature_value .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());
                    $('#bulk_action_menu_feature_value').next('ul').append('<li>'+etsTranslateDefine.renderBtnBulkTransList()+'</li>');

                    break;
                case 'blockreassurance':
                    $('#table-blockreassurance tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem('', true));
                    if( $('.toolbarBox>.btn-toolbar>ul.nav').length)
                        $('.toolbarBox>.btn-toolbar>ul.nav').prepend(etsTranslateDefine.renderBtnTransAll('', true));
                    else
                        $('#form-feature_value .panel-heading-action').prepend(etsTranslateDefine.renderBtnTransAll());

                    break;
                case 'ps_linklist':
                    $('table[id^="link_widget_grid"] tr td .btn-group-action .dropdown-menu').append(etsTranslateDefine.renderBtnTransListItem());
                    $('.toolbar-icons>.wrapper').prepend(etsTranslateDefine.renderBtnTransAll());
                    break;
                case 'ps_imageslider':
                    $('#slidesContent .btn-group-action').append(etsTranslateDefine.renderBtnTransListItemButton(' ps_imageslider'));
                    $('.toolbarBox>.btn-toolbar>ul').prepend(etsTranslateDefine.renderBtnTransAll('', true));

                    break;
            }
        }
    }

};