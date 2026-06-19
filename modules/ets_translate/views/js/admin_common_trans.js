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
var etsTransFunc = {
    formatFormData: function(formData){
        var data = {};
        $.each(formData, function (i, el) {
            if(el.name.endsWith('[]'))
            {
                var name = el.name.substring(0, el.name.length - 2);
                if(!(name in data)){
                    data[name] = [];
                }
                data[name].push(el.value);
            }
            else
                data[el.name] = el.value;
        });
        return data;
    },
    trans: function(key){
        return etsTransText[key] || key;
    },
    showErrorTrans: function(errors){
        var errorHtml = '';
        if(typeof errors == 'string'){
            errorHtml = '<ul><li>'+errors+'</li></ul>';
        }
        else{
            errorHtml += '<ul>';
            $.each(errors, function(i, el){
                errorHtml += '<li>'+el+'</li>';
            });
            errorHtml += '</ul>';
        }
        $('#etsTransModalTrans .form-errors').html('<div class="alert alert-danger">' +errorHtml+ '</div>');
    },
    renderBtnStopTranslate: function(){
        return '<button type="button" class="btn btn-danger js-ets-trans-btn-strop-translate">' +
            '<i class="material-icons">close</i> ' +etsTransFunc.trans('stop')+
            '</button>'
    },
    renderBtnResumeTranslate: function(dataResume){
        dataResume = dataResume || {};
        var attrBtn = 'data-page-type="'+(dataResume.pageType || '')+'"';
        attrBtn += ' data-nb-translated="'+(dataResume.nbTranslated || 0)+'"';
        attrBtn += ' data-nb-char="'+(dataResume.nbCharTranslated || 0)+'"';
        attrBtn += ' data-lang-source="'+(dataResume.langSource || '')+'"';
        attrBtn += ' data-lang-target="'+(dataResume.langTarget || '')+'"';
        attrBtn += ' data-field-option="'+(dataResume.fieldOption || '')+'"';
        attrBtn += ' data-total-path="'+(dataResume.nbPath || '')+'"';
        return '<button type="button" class="btn btn-primary js-ets-trans-btn-resume-translate" '+attrBtn+'>' +etsTransFunc.trans('resume')+'</button>'+
        '<button type="button" class="btn btn-default btn-outline-secondary btn-group-translate-close" data-close="close">'+etsTransFunc.trans('Cancel')+'</button>' ;
    },
    renderBtnCloseTranslate: function(){
        return '<button type="button" class="btn btn-default btn-outline-secondary js-ets-trans-btn-close-translate">' +etsTransFunc.trans('close')+'</button>';
    },
    renderBtnPauseTranslate: function(dataPause){
        dataPause = dataPause || {};
        var attrBtn = 'data-page-type="'+(dataPause.pageType || '')+'"';
        attrBtn += ' data-nb-translated="'+(dataPause.nbTranslated || 0)+'"';
        attrBtn += ' data-nb-char="'+(dataPause.nbCharTranslated || 0)+'"';
        attrBtn += ' data-lang-source="'+(dataPause.langSource || '')+'"';
        attrBtn += ' data-lang-target="'+(dataPause.langTarget || '')+'"';
        attrBtn += ' data-field-option="'+(dataPause.fieldOption || '')+'"';
        return '<button type="button" class="btn btn-info js-ets-tran-btn-pause-translate" '+attrBtn+'>'+etsTransFunc.trans('pause')+'</button>';
    },
    getParameterByName: function(name, url) {
        if (typeof url === 'undefined' || !url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    },
    showPopupTranslating: function(canPause, pageType, totalTranslate, langSource, langTarget, fieldOptions){
        $('#etsTransModalTrans #etsTransPopupTranslating').removeClass('hide');
        $('#etsTransModalTrans .ets-trans-content').remove();
        $('#etsTransModalTrans .close').addClass('hide');
        pageType = pageType || '';
        totalTranslate = totalTranslate || 0;
        var pageName = etsTransFunc.getPageName(pageType);
        etsTransFunc.setPageNameTranslating(pageName);
        if(totalTranslate)
            etsTransFunc.setTotalItemTranslating(totalTranslate);
        etsTransFunc.setConfigTranslating(pageType, langSource, langTarget, fieldOptions);
        if(canPause) {
            etsTransFunc.setResumeTranslate();
        }
        else{
            etsTransFunc.setBulkTranslating();
        }
        $('#etsTransPopupTrans .form-errors').html('');
        $('#etsTransPopupTranslating .form-errors').html('');

        $('body').addClass('etsTransPopupActive');
        $('#etsTransPopupTranslating').addClass('active');
        if(pageType == 'theme' || pageType == 'module' || !$('#etsTransPopupTranslating .total_translate').html() || $('#etsTransPopupTranslating .total_translate').html() == 0){
            $('#etsTransPopupTranslating .suffix_total_translate').addClass('hide');
        }
        if(pageType == 'all' || pageType == 'inter'){
            $('#etsTransPopupTranslating .for-trans-all-website').removeClass('hide');
        }
        $('#etsTransModalTrans .btn-group-trans').addClass('hide');
        $('#etsTransModalTrans .btn-group-translating').removeClass('hide');
        $('#etsTransModalTrans .js-ets-trans-analysis-text').addClass('hide');
    },
    hidePopupTranslating: function(){
        $('#etsTransPopupTranslating').removeClass('active');
    },
    getNbMoney: function(nbCharacters){
        if(ETS_TRANS_RATE_GOOGLE && ETS_TRANS_RATE_GOOGLE.length){
            var perChar = parseFloat(ETS_TRANS_RATE_GOOGLE) /1000000;
            return nbCharacters * perChar;
        }
        return 0;
    },
    updateDataTranslating: function(nbTranslated, nbCharacters, totalTranslate){
        nbTranslated = nbTranslated || 0;
        totalTranslate = totalTranslate || 0;
        nbCharacters = nbCharacters || 0;
        var nbMoney = etsTransFunc.getNbMoney(nbCharacters);

        if(totalTranslate) {
            $('#etsTransPopupTranslating .total_translate').html(totalTranslate);
        }
        $('#etsTransPopupTranslating .nb_translated').html(nbTranslated);
        $('#etsTransModalTrans .js-ets-tran-btn-pause-translate').attr('data-nb-translated', nbTranslated);
        var nbCharFormated = nbCharacters.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
        $('#etsTransPopupTranslating .nb_char').html(nbCharFormated);
        $('#etsTransModalTrans .js-ets-tran-btn-pause-translate').attr('data-nb-char', nbCharacters);
        $('#etsTransPopupTranslating .nb_money').html(nbMoney.toFixed(5)+' '+ETS_TRANS_RATE_GOOGLE_SUFFIX);
    },

    updateTotalFilePath: function(nb_path){
        $('#etsTransModalTrans .js-ets-tran-btn-pause-translate').attr('data-total-path', nb_path);
    },
    setPageNameTranslating: function(pageName){
        $('#etsTransModalTrans #etsTransPopupTranslating .page_name').html(pageName);
    },
    setConfigTranslating: function(pageType, langSource, langTarget, fieldOption){
        $('#etsTransModalTrans .js-ets-tran-btn-pause-translate').attr({
            "data-page-type": pageType,
            "data-lang-source": langSource,
            "data-lang-target": langTarget.toString(),
            "data-field-option": fieldOption
        });
    },
    setTotalItemTranslating: function(totalTranslate){
        $('#etsTransPopupTranslating .total_translate').html(totalTranslate);
    },
    getPageName: function(pageType, isSingular){
        if(typeof isSingular === 'undefined'){
            isSingular = 0;
        }
        switch (pageType) {
            case 'product':
                if(isSingular)
                    return  etsTransFunc.trans('product');
                return etsTransFunc.trans('products');
            case 'category':
                if(isSingular)
                    return  etsTransFunc.trans('category');
                return etsTransFunc.trans('categories');
            case 'cms':
                if(isSingular)
                    return  etsTransFunc.trans('CMS');
                return etsTransFunc.trans('CMSs');
            case 'cms_category':
                if(isSingular)
                    return  etsTransFunc.trans('CMS_category');
                return etsTransFunc.trans('CMSs_categories');
            case 'manufacurer':
                if(isSingular)
                    return  etsTransFunc.trans('manufacturer');
                return etsTransFunc.trans('manufacturers');
            case 'supplier':
                if(isSingular)
                    return  etsTransFunc.trans('supplier');
                return etsTransFunc.trans('suppliers');
            case 'attribute':
                if(isSingular)
                    return  etsTransFunc.trans('attribute');
                return etsTransFunc.trans('attributes');
            case 'attribute_group':
                if(isSingular)
                    return  etsTransFunc.trans('attribute_group');
                return etsTransFunc.trans('attribute_groups');
            case 'feature':
                if(isSingular)
                    return  etsTransFunc.trans('feature');
                return etsTransFunc.trans('features');
            case 'feature_value':
                if(isSingular)
                    return  etsTransFunc.trans('feature_value');
                return etsTransFunc.trans('feature_values');
            default:
                if(isSingular)
                    return  etsTransFunc.trans('text');
                return etsTransFunc.trans('texts');

        }
    },
    setPauseTranslate: function(datResume){
        $('#etsTransPopupTranslating .msg-box-info').removeClass('hide');
        $('#etsTransPopupTranslating .text-loading').addClass('hide');
        $('#etsTransPopupTranslating .text-completed').addClass('hide');
        $('#etsTransPopupTranslating .text-initialize').addClass('hide');
        $('.js-ets-tran-btn-pause-translate').remove();
        if(!$('#etsTransModalTrans').find('.js-ets-trans-btn-resume-translate').length){
            $('#etsTransModalTrans .btn-group-translating').append(etsTransFunc.renderBtnResumeTranslate(datResume));
        }
        $('#etsTransModalTrans .close').removeClass('hide');
    },
    setResumeTranslate: function(datResume){
        $('#etsTransPopupTranslating .msg-box-info').removeClass('hide');
        $('#etsTransPopupTranslating .text-loading').removeClass('hide');
        $('#etsTransPopupTranslating .text-completed').addClass('hide');
        $('#etsTransPopupTranslating .text-initialize').addClass('hide');
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').next().remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-translate-from-zero').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-translate-from-resume').remove();
        if($('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').length){
            $('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').remove();
        }
        $('#etsTransModalTrans .btn-group-translating').append(etsTransFunc.renderBtnPauseTranslate(datResume));
        $('#etsTransModalTrans .close').addClass('hide');
    },
    setTranslateDone: function(){
        $('#etsTransPopupTranslating .msg-box-info').removeClass('hide');
        $('#etsTransPopupTranslating .text-loading').addClass('hide');
        $('#etsTransPopupTranslating .text-initialize').addClass('hide');
        $('#etsTransPopupTranslating .text-completed').removeClass('hide');
        $('#etsTransModalTrans .close').removeClass('hide');

        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').next('.btn-group-translate-close').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').remove();
        if(!$('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').length){
            $('#etsTransModalTrans .btn-group-translating').append(etsTransFunc.renderBtnCloseTranslate());
        }
        /**/
        var totalTrans = $('#etsTransPopupTranslating .total_translate').text();
        if(totalTrans && totalTrans != '0'){
            $('#etsTransPopupTranslating .js-ets-tran-btn-pause-translate').attr('data-nb-translated', totalTrans);
            $('#etsTransPopupTranslating .nb_translated').html(totalTrans);
        }
        $('#etsTransModalTrans .file-data-translated').addClass('hide');
    },
    setInitTrans: function(){
        $('#etsTransPopupTranslating .msg-box-info').addClass('hide');
        $('#etsTransPopupTranslating .text-loading').addClass('hide');
        $('#etsTransPopupTranslating .text-completed').addClass('hide');
        $('#etsTransPopupTranslating .text-initialize').removeClass('hide');
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').next('.btn-group-translate-close').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').remove();
        $('#etsTransModalTrans .js-ets-trans-btn-close-x-translating').addClass('active');
    },
    setTranslateError: function(errors){
        var errorHtml = '';
        if(typeof errors == 'string'){
            errorHtml = '<ul><li>'+errors+'</li></ul>';
        }
        else{
            errorHtml += '<ul>';
            $.each(errors, function(i, el){
                errorHtml += '<li>'+el+'</li>';
            });
            errorHtml += '</ul>';
        }
        $('#etsTransPopupTranslating .form-errors').html('<div class="alert alert-danger">'+errorHtml+'</div>');
        $('#etsTransPopupTranslating .text-loading').addClass('hide');
        $('#etsTransPopupTranslating .text-completed').addClass('hide');
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').next('.btn-group-translate-close').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').remove();
        $('#etsTransModalTrans .js-ets-trans-btn-close-x-translating').removeClass('active');
        if(!$('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').length){
            $('#etsTransModalTrans .btn-group-translating').append(etsTransFunc.renderBtnCloseTranslate());
        }
    },
    setBulkTranslating: function(){
        $('#etsTransPopupTranslating .text-loading').removeClass('hide');
        $('#etsTransPopupTranslating .text-completed').addClass('hide');
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-tran-btn-pause-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').next('.btn-group-translate-close').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-resume-translate').remove();
        $('#etsTransModalTrans .btn-group-translating').find('.js-ets-trans-btn-close-translate').remove();
    },
    showTranslatingField: function(){
        /*$('body').addClass('etsTransPopupActive');
        $('#etsTransPopupTranslatingField').addClass('active');
        $('#etsTransPopupTranslatingField').removeClass('hide');
        $('#etsTransModalTrans .form-trans').remove();*/
    },
    hideTranslatingField: function(){
        //$('body').removeClass('etsTransPopupActive');
    },
    showAnalysisCompleted: function(pageType, formData, totalItem){
        $.ajax({
            url: ETS_TRANS_LINK_AJAX_MODULE,
            type: 'GET',
            dataType: 'json',
            data: {etsTransGetFormAnalysisCompleted: 1},
            success: function (res) {
                if(res.success){
                    $('#etsTransModalTrans .js-ets-trans-analysis-text').removeClass('hide');
                    $('#etsTransModalTrans .ets-trans-content').html(res.form_html);
                    $('#etsTransPopupAnalysisCompleted').addClass('active');
                    $('#etsTransModalTrans .btn-group-analysis-completed').removeClass('hide');
                    $('#etsTransModalTrans .btn-group-translate').addClass('hide');
                    if(!formData.nb_text){
                        $('#etsTransPopupAnalysisCompleted .nothing-to-translate.hide').removeClass('hide');
                        $('#etsTransPopupAnalysisCompleted .info-analysis').addClass('hide');
                        $('#etsTransModalTrans .js-ets-trans-analysis-accept').addClass('hide');
                        return false;
                    }
                    if(pageType == 'email'){
                        $('#etsTransPopupAnalysisCompleted .text_type').html(etsTransFunc.trans('file_emails'));
                    }
                    $('#etsTransPopupAnalysisCompleted .nothing-to-translate').addClass('hide');
                    $('#etsTransPopupAnalysisCompleted .info-analysis').removeClass('hide');
                    $('#etsTransModalTrans .js-ets-trans-analysis-accept').removeClass('hide');

                    $('#etsTransPopupAnalysisCompleted .nb_text').html(formData.nb_text.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
                    $('#etsTransPopupAnalysisCompleted .nb_char').html(formData.nb_char.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
                    $('#etsTransPopupAnalysisCompleted .nb_money').html(formData.nb_money.toFixed(5)+' '+ETS_TRANS_RATE_GOOGLE_SUFFIX);
                    $('#etsTransPopupAnalysisCompleted input[name=trans_source]').val(formData.trans_source);
                    $('#etsTransPopupAnalysisCompleted input[name=trans_target]').val(formData.trans_target);
                    $('#etsTransPopupAnalysisCompleted input[name=trans_option]').val(formData.trans_option);
                    if(pageType == 'inter'){
                        var wd = formData.trans_wd || '';
                        if(typeof  wd !== 'string'){
                            wd = wd.join(',');
                        }
                        $('#etsTransPopupAnalysisCompleted input[name=trans_wd]').val(wd);
                    }
                    if(pageType == 'email'){
                        var mailOption = formData.mail_option || [];
                        mailOption = mailOption.join(',');

                        $('#etsTransPopupAnalysisCompleted input[name=mail_option]').val(mailOption);
                    }
                    $('#etsTransModalTrans .js-ets-trans-analysis-accept').attr({
                        'data-page-type': pageType,
                        'data-total-item': totalItem,
                        'data-total-path': formData.nb_path || 0,
                        'data-trans-all': formData.is_trans_all || 0,
                    });
                    if(pageType == 'blog'){
                        $('#etsTransModalTrans .js-ets-trans-analysis-accept').attr({
                            'data-blog-type': formData.blog_type || '',
                        });
                    }

                }
            },
        });
    },
    showInitializing: function(){
        etsTransFunc.showPopupTrans();
        $('#etsTransModalTrans .trans-content-append').html('<div class="init-content text-loading"><span>'+etsTransFunc.trans('initializing')+'</span></div>');
    },
    hideInitializing: function(){
        $('#etsTransModalTrans .trans-content-append').html('');
        etsTransFunc.hidePopupTrans();
    },
    hideAnalysisCompleted: function(){
        $('#etsTransPopupAnalysisCompleted').removeClass('active');
    },
    formatTextContains: function(text){
        return text.replace(/\'/g, '\\\'').replace(/\"/g, '\\"');
    },
    updatePercentageTranslated: function(percent){
        $('#etsTransPopupTranslating .nb_percentage').html(percent);
    },
    updateListFileTranslated: function(listFile){
        var html = '';
        $.each(listFile, function(i, el){
            html += '<p class="file-item">'+el+'</p>';
        });
        $('#etsTransPopupTranslating .list_filepath').html(html);
    },
    updatePageTranslated: function(pageType, nbItem){
        var html = '';
        switch (pageType) {
            case 'product':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('product') : etsTransFunc.trans('products'));
                break;
            case 'category':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('category') : etsTransFunc.trans('categories'));
                break;
            case 'cms':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('CMS') : etsTransFunc.trans('CMSs'));
                break;
            case 'cms_category':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('CMS_category') : etsTransFunc.trans('CMS_categories'));
                break;
            case 'manufacturer':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('manufacture') : etsTransFunc.trans('manufactures'));
                break;
            case 'supplier':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('suppliers') : etsTransFunc.trans('supplier'));
                break;
            case 'blog_post':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('blog_posts') : etsTransFunc.trans('blog_post'));
                break;
            case 'blog_category':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('blog_categories') : etsTransFunc.trans('blog_category'));
                break;
            case 'megamemnu':
                html = etsTransFunc.trans('megamenu');
                break;
            case 'email':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('email') : etsTransFunc.trans('email'));
                break;
            case 'attribute':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('attributes') : etsTransFunc.trans('attribute'));
                break;
            case 'attribute_group':
                html = nbItem+' '+(nbItem > 1 ? etsTransFunc.trans('attribute_groups') : etsTransFunc.trans('attribute_group'));
                break;
        }
        $('#etsTransPopupTranslating .list_filepath').html(html);
    },
    hidePopupTrans: function(){
        $('body').removeClass('etsTransPopupActive');

    },
    showPopupTrans: function(){
        $('body').addClass('etsTransPopupActive');
        $('#etsTransModalTrans').modal({backdrop: 'static', keyboard: false});
        $('#etsTransModalTrans').modal('show');
    },
    showPopupMM: function () {
        $('.mm_forms.mm_popup_overlay').removeClass('hidden');
        $('.mm_forms.mm_popup_overlay .mm_menu_form.mm_pop_up').removeClass('hidden');
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
};

$(document).ready(function(){
    $(document).on('click', '.js-ets-trans-lang-source', function(){
        var idLang = $(this).attr('data-lang-id');
        var htmlLang = $(this).html();
        $('.js-ets-trans-btn-lang-source>.text-html').html(htmlLang);
        $(this).closest('form').find('input[name="trans_source"]').val(idLang);
        $('.js-ets-trans-lang-target').removeClass('hide');
        $('.js-ets-trans-lang-target.lang-'+idLang).addClass('hide');
        $('.js-ets-trans-lang-target.lang-'+idLang+' input[type="checkbox"]:checked').prop('checked', false);
        $('#etsTransFormTransPages .title_lang_source').html(htmlLang);
        var targetText = [];
        var targetShort = [];
        $('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').each(function () {
            targetText.push($(this).next('label').html());
            targetShort.push('<img src="'+$(this).next('label').find('img').attr('src')+'" /><span>'+($(this).parent().attr('data-isocode') || '')+'</span>');
        });
        $('#etsTransFormTransPages .title_lang_target').html(targetText.join(','));
        if(targetShort.length)
            $('#etsTransFormTransPages #langTargetDropdown>.text-html').html(targetShort.join(', '));
        else
            $('#etsTransFormTransPages #langTargetDropdown>.text-html').html('--');
    });

    $(document).on('click', '#etsTransModalTrans [data-close="close"]', function () {
        $('#etsTransModalTrans').modal('hide');
        if (!$('#etsTransModalTrans').hasClass('translating')) {
            etsTransFunc.hidePopupTrans();
        }
    });
    $(document).on('click', '#etsTransModalTrans .js-ets-trans-analysis-cancel-trans', function () {
        $('#etsTransModalTrans').modal('hide');
        return false;
    });

    $(document).on('click', '#etsTransSelectLangTarget_all', function (e) {
        if($(this).is(':checked')){
            $('.js-ets-trans-lang-target:not(.hide) .js-ets-trans-lang-target-input').prop('checked', true);
            var targetText = [];
            var targetShort = [];
            if($('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').length > 1){
                $('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').each(function () {
                    targetText.push($(this).next('label').html());
                    targetShort.push('<img src="'+$(this).next('label').find('img').attr('src')+'" /><span>'+($(this).parent().attr('data-isocode') || '')+'</span>');
                });
            }
            else{
                $('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').each(function () {
                    targetText.push($(this).next('label').html());
                    targetShort.push($(this).next('label').html());
                });
            }
            $('#etsTransFormTransPages .title_lang_target').html(targetText.join(','));
            if(targetShort.length) {
                $('#etsTransFormTransPages #langTargetDropdown>.text-html').html(targetShort.join(', '));
                if(targetShort.length == 1){
                    $('#etsTransFormTransPages #langTargetDropdown>.text-html').addClass('single-lang');
                }
                else{
                    $('#etsTransFormTransPages #langTargetDropdown>.text-html').removeClass('single-lang');
                }
            }
            else
                $('#etsTransFormTransPages #langTargetDropdown>.text-html').html('--');
        }
        else{
            $('.js-ets-trans-lang-target:not(.hide) .js-ets-trans-lang-target-input').prop('checked', false);
            $(this).closest('.dropdown').find('#langTargetDropdown>.text-html').html('--');
            $('#etsTransFormTransPages .title_lang_target').html('--');
        }
        if(!$('.js-ets-trans-lang-target-input:checked').length){
            $('#etsTransModalTrans #langTargetDropdown .text-html').html('--');
        }
    });
    $(document).on('change', '.js-ets-trans-lang-target', function (e) {
        e.preventDefault();
        var totalTarget = $('.js-ets-trans-lang-target:not(.hide) .js-ets-trans-lang-target-input').length;
        if($('.js-ets-trans-lang-target:not(.hide) input[type="checkbox"]:checked').length != totalTarget){
            $('.js-ets-trans-lang-target-all input[type="checkbox"]:checked').prop('checked', false);
        }
        else{
            $('.js-ets-trans-lang-target-all input[type="checkbox"]').prop('checked', true);
        }
        var targetText = [];
        var targetShort = [];
        if($('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').length > 1){
            $('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').each(function () {
                targetText.push($(this).next('label').html());
                targetShort.push('<img src="'+$(this).next('label').find('img').attr('src')+'" /><span>'+($(this).parent().attr('data-isocode') || '')+'</span>');
            });
        }
        else{
            $('.js-ets-trans-lang-target .js-ets-trans-lang-target-input:checked').each(function () {
                targetText.push($(this).next('label').html());
                targetShort.push($(this).next('label').html());
            });
        }
        $('#etsTransFormTransPages .title_lang_target').html(targetText.join(','));
        if(targetShort.length){
            $('#etsTransFormTransPages #langTargetDropdown>.text-html').html(targetShort.join(', '));
            if(targetShort.length == 1){
                $('#etsTransFormTransPages #langTargetDropdown>.text-html').addClass('single-lang');
            }
            else{
                $('#etsTransFormTransPages #langTargetDropdown>.text-html').removeClass('single-lang');
            }
        }
        else
            $('#etsTransFormTransPages #langTargetDropdown>.text-html').html('--');
        $(this).closest('.dropdown').toggleClass('open');
        if(!$('.js-ets-trans-lang-target-input:checked').length) {
            $('#etsTransModalTrans #langTargetDropdown .text-html').html('--');
            $('#etsTransFormTransPages .title_lang_target').html('--');
        }
        return false;
    });

    $(document).on('change', '#etsTransFormTransPages input[name=trans_option]', function () {
        $('#etsTransFormTransPages .field_option_text').html($(this).next('label').html());
    });

    $(document).on('click', '.js-ets-trans-modify-settings', function (e) {
        e.preventDefault();
        $(this).closest('.trans-data-info').addClass('hide');
        $('#etsTransFormTransPages .modify-setting').removeClass('hide');
        $(this).parents('.ets-trans-modal').addClass('ets_modify');
        return false;
    });
    $(document).on('click', '.js-btn-ets-trans-hide-modify-setting', function (e) {
        e.preventDefault();
        $('#etsTransFormTransPages .modify-setting').addClass('hide');
        $('#etsTransFormTransPages .trans-data-info').removeClass('hide');
        $(this).parents('.ets-trans-modal').removeClass('ets_modify');
        return false;
    });
    $(document).on('click', '.js-ets-trans-btn-close-translate', function (e) {
        e.preventDefault();
        $('#etsTransModalTrans').modal('hide');
        $('body').removeClass('etsTransPopupActive');
    });
    $(document).on('click', '.js-ets-trans-btn-close-x-translating', function (e) {
        e.preventDefault();
        etsTransFunc.hidePopupTranslating();
        $('body').removeClass('etsTransPopupActive');
    });

    $(document).on('hidden.bs.modal','#etsTransModalTrans', function (e) {
        $('body').removeClass('etsTransPopupActive');
        if($('.mm_forms.mm_popup_overlay').length){
            $('#etsTransModalTrans').remove();
        }
    });
    $(document).on('click', '.ets_dropdown button', function(e){
        if ( $(this).parents('.ets_dropdown').hasClass('open') ){
            $(this).stop().next('.dropdown-menu').removeClass('open');
            $(this).stop().parents('.ets_dropdown').removeClass('open');
        } else {
            $(this).stop().next('.dropdown-menu').addClass('open');
            $(this).stop().parents('.ets_dropdown').addClass('open');
        }

    });
    $(document).mouseup(function (e)
    {
        if (!$('.ets_dropdown.open').is(e.target)&& $('.ets_dropdown.open').has(e.target).length === 0)
        {
            $('.ets_dropdown').removeClass('open').find('.dropdown-menu').removeClass('open');
        }
    });

    window.onclick = function(event) {
        var modal = document.getElementById('etsTransModalTrans');
        if (event.target == modal && !$('#etsTransModalTrans').hasClass('translating')) {
            etsTransFunc.hidePopupTrans();
        }

    }
});