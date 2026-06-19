/**
 * 2007-2021 ETS-Soft
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
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

 var etsSeoAdmin = {
    listControllersOverride: [
        'AdminCmsContent',
        'AdminMeta',
        'AdminCategories',
        'AdminManufacturers',
        'AdminSuppliers',
        'AdminProducts'
    ],
    getMetaCodeTemplate: function(is_title, is_snippet){
        if(etsSeoAdmin.listControllersOverride.indexOf(ETS_SEO_CONTROLLER) < 0)
        {
            return '';
        }
        var html = '';
        if (typeof ETS_SEO_META_CODES !== 'undefined') {
            var meta_codes = is_title ? ETS_SEO_META_CODES.title : ETS_SEO_META_CODES.desc;
            html = '<div class="ets_seo_meta_code '+(is_snippet ? 'meta_code_snippet' : '')+'">';
                Object.keys(meta_codes).forEach(function(key){
                    html += '<button type="button" class="btn btn-default btn-add-met-code js-ets-seo-add-meta-code" data-code="' + meta_codes[key].code + '">';
                    html += '<i class="fa fa-plus-circle"></i> ' + meta_codes[key].title;
                    html += '</button>';
                });
            html += '</div>';
        }
        return html;
    },
    ajaxExportData: function(offset, type, count){
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            data: {
                etsSeoExportData: 1,
                offset: offset,
                count: count,
                type: type
            },
            dataType: 'json',
            beforeSend: function(){
                $('.js-ets-seo-export').addClass('active');
                $('.js-ets-seo-export').prop('disabled', true);
            },
            success: function (res) {
                if (res.success && res.continue_process)
                {
                    etsSeoAdmin.ajaxExportData(res.offset, res.type, res.count);
                    return false;
                }
                $('#configuration_form').append('<input type="hidden" name="etsSeoSubmitExport" value="1">');
                $('#configuration_form').submit();
            },
            complete: function(){
                $('.js-ets-seo-export').removeClass('active');
                $('.js-ets-seo-export').prop('disabled', false);
            },
            error: function(xhr){
               // console.log(xhr);
            }
        });
        return false;
    },
    changeDescRewriteRule: function ($this) {
        if(ETS_SEO_LINK_REWRITE_RULES && ETS_SEO_DEFINED)
        {
            Object.keys(ETS_SEO_LINK_REWRITE_RULES).forEach(function(key){
                var desc = parseInt($this.val()) == 1 ? ETS_SEO_LINK_REWRITE_RULES[key]['desc_new_rule'] : ETS_SEO_LINK_REWRITE_RULES[key]['desc_rule'];
                if(ETS_SEO_DEFINED.is175)
                {
                    $('#meta_settings_form_url_schema_'+key).closest('.form-group').find('.form-text').html(desc);
                }
                else{
                    $('input[name="PS_ROUTE_'+key+'"]').closest('.form-group').find('.help-block').html(desc);
                }
            });

        }
    },
    changeSiteOriginalOrPersonal: function(el)
    {
        if($(el).val() == 'COMPANY')
        {
            $('#conf_id_ETS_SEO_SITE_PERSON_NAME').parent('.form-group').addClass('hide');
            $('#conf_id_ETS_SEO_SITE_PERSON_AVATAR').parent('.form-group').addClass('hide');

            $('#conf_id_ETS_SEO_SITE_ORIG_NAME').parent('.form-group').removeClass('hide');
            $('#conf_id_ETS_SEO_SITE_ORIG_LOGO').parent('.form-group').removeClass('hide');
        }
        else{
            $('#conf_id_ETS_SEO_SITE_PERSON_NAME').parent('.form-group').removeClass('hide');
            $('#conf_id_ETS_SEO_SITE_PERSON_AVATAR').parent('.form-group').removeClass('hide');

            $('#conf_id_ETS_SEO_SITE_ORIG_NAME').parent('.form-group').addClass('hide');
            $('#conf_id_ETS_SEO_SITE_ORIG_LOGO').parent('.form-group').addClass('hide');
        }
    },

    onChangeSitemapOptions: function(){
        var count = 0;
        var input = $('input[name="ETS_SEO_SITEMAP_OPTION[]"]');
        input.each(function(){
            if($(this).is(':checked') && $(this).val() != 'all'){
                count++;
            }
        });
        if(count == (input.length - 1)){
            //input.prop('disabled', true);
            $('input[name="ETS_SEO_SITEMAP_OPTION[]"][value="all"]').prop('checked', true);
            //$('input[name="ETS_SEO_SITEMAP_OPTION[]"][value="all"]').prop('disabled', false);

        }
        else{
            $('input[name="ETS_SEO_SITEMAP_OPTION[]"][value="all"]').prop('checked', false);
        }
    },
    onChangeEnableRating: function(){
        var input = $('#ETS_SEO_RATING_ENABLED');
        if(input.val() == 2 || input.val() == 0)
        {
            $('.js-ets-seo-rating-field').closest('.form-group').addClass('hide');
        }
        else{
            $('.js-ets-seo-rating-field').closest('.form-group').removeClass('hide');
        }
    },
    onChangeRatingConfig: function(){
        var input = $('select[name=ets_seo_rating_enable]');
        if(input.val() == 2 || input.val() == 0)
        {
            $('.js-ets-seo-rating-config').addClass('hide');
        }
        else{
            $('.js-ets-seo-rating-config').removeClass('hide');
        }
    },
    showErrorRating: function(input, message){
        if(input.next('.ets-rating-error').length)
        {
            input.next('.ets-rating-error').remove();
        }
        input.after('<p class="ets-rating-error">'+message+'</p>');
    },
    isFloat: function(str){
        return !isNaN(str) && str.toString().indexOf('.') != -1;
    },
    isInt: function(str){
        /*var n = Math.floor(Number(str));
        return n !== Infinity && String(n) === str && n >= 0;*/
        return !isNaN(str) && /^[0-9]+$/.test(str);
    },
    numberWithCommas: function(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    changeRatingStar: function(){
        var ratingEnable = $('select[name=ets_seo_rating_enable]').val();
        var avgRating = $('input[name=ets_seo_rating_average]').val();
        var ratingCount = $('input[name=ets_seo_rating_count]').val();
        var bgWidth = 0;
        if(ratingEnable == 1)
        {
            if($('.snippet-preview--rating').hasClass('hide'))
            {
                $('.snippet-preview--rating').removeClass('hide');
            }
            if(!avgRating || avgRating == '0' || isNaN(avgRating) || parseFloat(avgRating) <= 0 || parseFloat(avgRating) > 5 || !ratingCount || ratingCount == '0' || isNaN(ratingCount) || !etsSeoAdmin.isInt(ratingCount) || parseInt(ratingCount) <= 0)
            {
                $('.snippet-preview--rating').addClass('hide');
                return;
            }
            if($('.snippet-preview--rating .rating-info').hasClass('hide'))
            {
                $('.snippet-preview--rating .rating-info').removeClass('hide');
            }
            var textVote = ETS_SEO_MESSAGE.votes;
            if(etsSeoAdmin.isInt(ratingCount) && parseInt(ratingCount) < 2)
            {
                textVote = ETS_SEO_MESSAGE.vote;
            }
            $('.snippet-preview--rating .rating-info .text-vote').html(textVote);
            if(avgRating && !isNaN(avgRating))
            {
                bgWidth = avgRating / 5 * 100;
                avgRating = parseFloat(avgRating).toFixed(1);
            }
            $('.snippet-preview--rating .rating-info .avg-rating').html(avgRating);
            $('.snippet-preview--rating .rating-info .rating-count').html(ratingCount ? etsSeoAdmin.numberWithCommas(ratingCount) : 0);

            $('.snippet-preview--rating .bg-star').css('width', bgWidth+'%');
        }
        else{
            if(!$('.snippet-preview--rating').hasClass('hide'))
            {
                $('.snippet-preview--rating').addClass('hide');
            }
            if(!$('.snippet-preview--rating .rating-info').hasClass('hide'))
            {
                $('.snippet-preview--rating .rating-info').addClass('hide');
            }
        }
    },
    validateFriendlyUrl: function(inputLinkRewrite){

        var linkRewrites = [];
        Object.keys(ETS_SEO_LANGUAGES).forEach(function(key){
            linkRewrites.push({id_lang: ETS_SEO_LANGUAGES[key], value: $(inputLinkRewrite+ETS_SEO_LANGUAGES[key]).val()});
        });
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            data: {validateLinkRewrite: 1,
                type: ETS_SEO_CONTROLLER,
                link_rewrites: linkRewrites,
                is_cms_category: ETS_SEO_IS_CMS_CATEGORY,
                id: ETS_SEO_DEFINED.id_current_page
            },
            dataType: 'json',
            success: function(res){
                if(!res.success)
                {
                    $('#ajax_confirmation').next('.alert-danger').remove();
                    $('#ajax_confirmation').after('<div class="alert alert-danger">'+res.error+'</div>');
                    $(window).scrollTop(0);
                }
            }
        })
    },
    setTextCount: function(el, count){
        el.find('.js-ets-seo-current-length').html(count);
    },
    changeTooltipContentProduct: function () {
        var content = $('#form_step5_meta_description').prev('label').attr('popover');
        if(content)
        {
            content = content.replace('160', '156');
            $('#form_step5_meta_description').prev('label').attr('popover', content);
            $('#form_step5_meta_description').prev('label').find('.help-box').attr('data-content', content);
        }

    },
    getTextCounter: function(text){
        return '<small class="js-text-count form-text text-muted text-right"><em><span class="js-ets-seo-current-length">0</span> '+text+'</em></small>';
    },
    addTextCounter: function() {
        if(ETS_SEO_CONTROLLER == 'AdminProducts')
        {
            return;
        }
        $('input[id*="meta_title_"], input[id*="meta_page_title_"]').each(function(){
            var id = $(this).attr('id');
            if(id.indexOf('ets_seo_social') < 0)
            {
                var id_lang = etsSEO.getIdLang(id);
                if(!$(this).parent().find('.js-text-count').length && !$(this).parent().find('[id*="recommended_length_counter"]').length)
                {
                    if($(this).parent('.input-group').length)
                    {
                        $(this).parent('.input-group').after(etsSeoAdmin.getTextCounter(ETS_SEO_MESSAGE.meta_title_recommended));
                        etsSeoAdmin.setTextCount($(this).parent('.input-group').next('.js-text-count'), $(this).val().length);
                    }
                    else{
                        $(this).after(etsSeoAdmin.getTextCounter(ETS_SEO_MESSAGE.meta_title_recommended));
                        etsSeoAdmin.setTextCount($(this).next('.js-text-count'), $(this).val().length);
                    }
                }
            }
        });

        $('textarea[id*="meta_description_"], form input[id*="meta_description_"]').each(function(){
            var id = $(this).attr('id');
            if(id.indexOf('ets_seo_social') < 0)
            {
                var id_lang = etsSEO.getIdLang(id);
                if(!$(this).parent().find('.js-text-count').length && !$(this).parent().find('[id*="recommended_length_counter"]').length)
                {
                    if(!$(this).parent().find('.js-text-count').length)
                    {
                        if($(this).parent('.input-group').length)
                        {
                            $(this).parent('.input-group').after(etsSeoAdmin.getTextCounter(ETS_SEO_MESSAGE.meta_title_recommended));
                            etsSeoAdmin.setTextCount($(this).parent('.input-group').next('.js-text-count'), $(this).val().length);
                        }
                        else {
                            $(this).after(etsSeoAdmin.getTextCounter(ETS_SEO_MESSAGE.meta_desc_recommended));
                            etsSeoAdmin.setTextCount($(this).next('.js-text-count'), $(this).val().length);
                        }
                    }
                }

            }
        });
    },
    initModalExplain: function(){
        var etsModalHtml = $('#boxModalEtsSeoTransExplain').html();
        $('body').append(etsModalHtml);
        $('#boxModalEtsSeoTransExplain').remove();

        $(document).on('click', '.js-ets-seo-show-explain-rule', function(){
            var rule = $(this).attr('data-rule') || '';
            var text = $(this).attr('data-text') || '';
            var content = $('#modalEtsSeoTransExplain .rule-msg.'+rule).html();
            content = content.replace(/\[page_title\]/gi, $('#modalEtsSeoTransExplain .rule-msg.'+rule).parent().attr('data-page-title'));
            $('#modalEtsSeoTransExplain .rule-msg.'+rule).html(content);
            $('#modalEtsSeoTransExplain .rule-msg:not(.hide)').addClass('hide');
            $('#modalEtsSeoTransExplain .rule-msg.'+rule).removeClass('hide');
            $('#modalEtsSeoTransExplain .ets-modal-title').html(text);
            $('#modalEtsSeoTransExplain:not(.ets-seo-d-flex)').addClass('show');
            return false;
        });
        $(document).on('click', '#modalEtsSeoTransExplain .ets-modal-close', function(){
            $('#modalEtsSeoTransExplain').removeClass('show');
        });
        $(document).keyup( function(e){
            if(e.keyCode == 27)
            {
                $('#modalEtsSeoTransExplain').removeClass('show');
            }

        });

        window.onclick = function(event) {
            var etsModal = document.getElementById("modalEtsSeoTransExplain");
            if (event.target == etsModal) {
                $('#modalEtsSeoTransExplain').removeClass('show');
            }
        }
    }
 };

(function($){
    $(document).on('change', 'input[name^="ets_seo_social_input_img"]', function(){
    
        var formData = new FormData();
        var id_lang = $(this).attr('data-idlang');

        formData.append('image', $(this)[0].files[0]);
        formData.append('etsSeoUploadSocialImage', 1);
        formData.append('id', ETS_SEO_DEFINED.id_current_page);
        formData.append('page_type', ETS_SEO_CONTROLLER);
        formData.append('id_lang', id_lang);
        formData.append('old_image', $(this).next('input[name="ets_seo_social_img[' + id_lang + ']"]').val());

        var $this = $(this);
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(res){
                if(res.success){
                    $('.ets-seo-social-tab .img-preview-'+id_lang).removeClass('hide');
                    $('.ets-seo-social-tab .img-preview-'+id_lang+' img').attr('src', res.image);
                    $('input[name="ets_seo_social_img[' + id_lang + ']"]').val(res.image);
                }
            }
        })
    });

    $(document).on('click', '.ets-seo-social-tab .remove-img', function(){
        if(!confirm(ets_seo_confirm_delete_image))
        {
            return;
        }
        if($(this).hasClass('active')){
            return;
        }
        var img_path = $(this).next('img').attr('src');
        $(this).addClass('active');
        var $this = $(this);
        var id_lang = $(this).attr('data-idlang');
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            data: {
                etsSeoDeleteSocialImg: 1,
                img_path: img_path,
                id: ETS_SEO_DEFINED.id_current_page,
                controller_type: ETS_SEO_CONTROLLER,
                is_cms_category: ETS_SEO_IS_CMS_CATEGORY
            },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    $this.parent('.img-preview').addClass('hide');
                    $this.next('img').attr('src', '');
                    $('input[name="ets_seo_social_input_img_'+id_lang+'"]').val('');
                }

            },
            complete: function(){
                $this.removeClass('active');
            }
        })
    });
    $(document).on('click', '.ets-seo-img-logo .remove-logo', function(){

        if(!confirm(ets_seo_confirm_delete_image))
        {
            return;
        }

        if ($(this).hasClass('active')) {
            return;
        }
        var img_path = $(this).next('img').attr('src');
        var config_name = $(this).attr('data-name');
        var id = $(this).attr('data-name');
        if($(this).attr('data-preview'))
        {
            $(this).parent('.ets-seo-img-logo').html('');
            $('#'+id+'-name').val('');
            return;
        }
        $(this).addClass('active');
        var $this = $(this);
        $.ajax({
            url: ETS_SEO_LINK_AJAX_BO,
            type: 'POST',
            data: {
                etsSeoDeleteLogoImg: 1,
                img_path: img_path,
                config_name: config_name
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $this.parent('.ets-seo-img-logo').html('');
                    $('#'+id+'-name').val('');
                }
                else {
                    alert(res.message);
                }
            },
            complete: function () {
                $this.removeClass('active');
            }
        });
    });

    $(document).on('change', '.js-ets-seo-checkall', function(){
       var inputName = $(this).attr('name');
       if($(this).is(':checked'))
       {
           $('input[name="'+inputName+'"]').prop('checked', true);
           //$('input[name="'+inputName+'"]').prop('disabled', true);
           //$(this).prop('disabled', false);
       }
       else {
           $('input[name="'+inputName+'"]').prop('checked', false);
           //$('input[name="'+inputName+'"]').prop('disabled', false);
       }
    });

    $(document).on('change', 'input[name="ETS_SEO_SITEMAP_OPTION[]"]', function(){
        etsSeoAdmin.onChangeSitemapOptions();
    });

    $(document).on('click', '.js-ets-seo-tab-customize', function(){
        var itemActive = $(this).attr('data-tab');
        $('.js-ets-seo-customize-item').removeClass('active');
        $('.'+itemActive).addClass('active');
    });

    if(ETS_SEO_CONTROLLER == 'AdminProducts')
    {
        $(document).on('click', '#form-nav a[href="#step1"]', function(e){
            $('#form_content .summary-description-container ul.nav-tabs .nav-link').each(function(){
                $(this).removeClass('active');
            });
            $('#form_content .summary-description-container .tab-content .tab-pane.panel-default').each(function(){
                $(this).removeClass('active');
            });
            e.preventDefault();
        });
    }
    $(document).on('change', 'input[name=ETS_SEO_ENABLE_REMOVE_ID_IN_URL]', function(){
        etsSeoAdmin.changeDescRewriteRule($(this));
        if (typeof ETS_SEO_LINK_REWRITE_RULES !== 'undefined' && ETS_SEO_CONTROLLER == 'AdminMeta')
        {
            var inputRuleDefine = {};
            if(typeof ets_seo_is178 != 'undefined' && ets_seo_is178){
                inputRuleDefine = {
                    product: $('#meta_settings_url_schema_form_product_rule'),
                    category: $('#meta_settings_url_schema_form_category_rule'),
                    layered: $('#meta_settings_form_url_schema_layered_rule'),
                    supplier: $('#meta_settings_url_schema_form_supplier_rule'),
                    manufacturer: $('#meta_settings_url_schema_form_manufacturer_rule'),
                    cms: $('#meta_settings_url_schema_form_cms_rule'),
                    cms_category: $('#meta_settings_url_schema_form_cms_category_rule'),
                    module: $('#meta_settings_url_schema_form_module'),
                };
            }
            else if(ETS_SEO_DEFINED.is175)
            {
                inputRuleDefine = {
                    product: $('#meta_settings_form_url_schema_product_rule'),
                    category: $('#meta_settings_form_url_schema_category_rule'),
                    layered: $('#meta_settings_form_url_schema_layered_rule'),
                    supplier: $('#meta_settings_form_url_schema_supplier_rule'),
                    manufacturer: $('#meta_settings_form_url_schema_manufacturer_rule'),
                    cms: $('#meta_settings_form_url_schema_cms_rule'),
                    cms_category: $('#meta_settings_form_url_schema_cms_category_rule'),
                    module: $('#meta_settings_form_url_schema_module'),
                };
            }
            else{
                inputRuleDefine = {
                    product: $('input[name=PS_ROUTE_product_rule]'),
                    category: $('input[name=PS_ROUTE_category_rule]'),
                    layered: $('input[name=PS_ROUTE_layered_rule]'),
                    supplier: $('input[name=PS_ROUTE_supplier_rule]'),
                    manufacturer: $('input[name=PS_ROUTE_manufacturer_rule]'),
                    cms: $('input[name=PS_ROUTE_cms_rule]'),
                    cms_category: $('input[name=PS_ROUTE_cms_category_rule]'),
                    module: $('input[name=PS_ROUTE_module]'),
                };
            }
            if ($(this).val() == 1) {
                Object.keys(inputRuleDefine).forEach(function(key){
                    var keyRule = key == 'module' ? key : key + '_rule';
                    
                    inputRuleDefine[key].val(ETS_SEO_LINK_REWRITE_RULES[keyRule].new_rule);
                });
            }
            else{
                Object.keys(inputRuleDefine).forEach(function(key){
                    var keyRule = key == 'module' ? key : key + '_rule';
                    inputRuleDefine[key].val(ETS_SEO_LINK_REWRITE_RULES[keyRule].rule);
                    inputRuleDefine[key].val(ETS_SEO_LINK_REWRITE_RULES[keyRule].rule);
                });
            }
        }
        
    });

    $(document).ready(function(){
        etsSeoAdmin.onChangeSitemapOptions();
        setTimeout(function(){
            $('.bootstrap>.alert.alert-success').hide();
            $('#ajax_confirmation').next('.alert.alert-success').hide();
        }, 3000);
        if(ETS_SEO_CONTROLLER == 'AdminCmsContent'){
            $('label[for="cms_page_seo_preview"]').parent('.form-group').hide();
        }
    });

    $(window).on('load', function(){

        if($('.js-current-length').length)
        {
            $('.js-current-length').each(function(){
                if($(this).next('span').html() == '70')
                {
                    $(this).next('span').html('60');
                }
                if($(this).next('span').html() == '160')
                {
                    $(this).next('span').html('156');
                }
            });

        }
        $('.maxLength .currentLength').each(function(){
            if($(this).next('span').html() == '70')
            {
                $(this).next('span').html('60');
            }
            if($(this).next('span').html() == '160')
            {
                $(this).next('span').html('156');
            }
        });
        if($('#form-ets_seo_redirect .panel-heading .badge').html() == '0')
        {
            $('#form-ets_seo_redirect .panel-heading .badge').hide();
        }

        etsSeoAdmin.changeTooltipContentProduct();
        $('input[id*="meta_keyword_"], input[id*="meta_keywords_"]').each(function(){
           $(this).attr('placeholder', ETS_SEO_MESSAGE.add_keyword);
           var idInput = $(this).attr('id');
           $('#'+idInput+'-tokenfield').attr('placeholder', ETS_SEO_MESSAGE.add_keyword);
           if($(this).next('.tagify-container').length)
           {
               $(this).next('.tagify-container').find('input').attr('placeholder', ETS_SEO_MESSAGE.add_keyword);
           }
        });

        setTimeout(function(){
            etsSeoAdmin.changeDescRewriteRule($('input[name=ETS_SEO_ENABLE_REMOVE_ID_IN_URL]:checked'));

            if (jQuery().select2){

                $('.js-ets-seo-select2').select2();
            }
            $('input[id*="meta_title_"], input[id*="meta_page_title_"], textarea[id*="meta_description_"], form input[id*="meta_description_"], form#meta_form input[id*="title_"], form#meta_form input[id*="description_"], input[id*="ets_seo_meta_title_"], textarea[id*="ets_seo_meta_description_"], input[name^="ets_seo_social_title"], textarea[name^="ets_seo_social_desc"]').each(function (_i, el) {
                var isSnippet = $(this).attr('id').indexOf('ets_seo_') < 0 ? false : true;
                var meta_codes = $(this).attr('id').indexOf('title') < 0 ? etsSeoAdmin.getMetaCodeTemplate(false, isSnippet) : etsSeoAdmin.getMetaCodeTemplate(true, isSnippet);
                if($(this).closest('.input-group').length)
                {
                    $(this).closest('.input-group').next('.ets_seo_meta_code').remove();
                    $(this).closest('.input-group').after(meta_codes);
                    
                }
                else{
                    $(this).next('.ets_seo_meta_code').remove();
                    $(this).after(meta_codes);
                }
            });

            $('input[id^=ets_seo_meta_title], textarea[id^=ets_seo_meta_description]').each(function(){
                var count = $(this).val().length;
                etsSeoAdmin.setTextCount($(this).next('.js-text-count'), count);
            });
            etsSeoAdmin.addTextCounter();
        }, 500);
    });

    $(document).on('change', '#ETS_SEO_SITE_OF_PERSON_OR_COMP', function(){
       etsSeoAdmin.changeSiteOriginalOrPersonal(this);
    });

    $(document).on('change', '#ETS_SEO_RATING_ENABLED', function(){
        etsSeoAdmin.onChangeEnableRating();
    });
    $(document).on('change', 'select[name=ets_seo_rating_enable]', function(){
        etsSeoAdmin.onChangeRatingConfig();
        etsSeoAdmin.changeRatingStar();
    });

    $(document).on('change', 'input[name=ets_seo_rating_average]', function(){
        etsSeoAdmin.changeRatingStar();
    });
    $(document).on('change', 'input[name=ets_seo_rating_count]', function(){
        etsSeoAdmin.changeRatingStar();
    });

    $(document).on('keyup', 'input[name^=ets_seo_meta_title], [id*="meta_title_"]', function(){
        var count = $(this).val().length;
        if($(this).next('.js-text-count').length) {
            etsSeoAdmin.setTextCount($(this).next('.js-text-count'), count);
        }
    });
    $(document).on('keyup', 'textarea[name^=ets_seo_meta_desc], input[id*="meta_description_"]', function(){
        var count = $(this).val().length;
        if($(this).next('.js-text-count').length) {
            etsSeoAdmin.setTextCount($(this).next('.js-text-count'), count);
        }
    });

    $(document).on('change', '#ETS_SEO_SITE_PERSON_AVATAR, #ETS_SEO_SITE_ORIG_LOGO, #ETS_SEO_FACEBOOK_DEFULT_IMG_URL', function(){
        var id = $(this).attr('id');
        var reader = new FileReader();
        reader.onload = function (e) {
            var imgHtml = '<span class="remove-logo" data-preview="1" data-name="'+id+'" title="Delete"><i class="fa fa-close"></i></span>' + '<img src="'+e.target.result+'">';
            $('#conf_id_'+id+' .ets-seo-img-logo').html(imgHtml);
        };

        // read the image file as a data URL.
        reader.readAsDataURL(this.files[0]);
    });

    if(ETS_SEO_CONTROLLER == 'AdminProducts')
    {
        $(document).on('click', 'form input[type=submit]', function(){
            //etsSeoAdmin.validateFriendlyUrl('#form_step5_link_rewrite_');
        });

    }


    $(document).ready(function(){
        if(!$('.ets_seo_extra_tabs').next('#fieldset_0').children('.panel-heading').length)
        {
            $('.ets_seo_extra_tabs').next('#fieldset_0').prepend('<div class="panel-heading">&nbsp;</div>');
        }
        $('.ets-seo-file-input-bo').each(function(){
           var value = $(this).attr('data-value');
           if(value){
               $(this).find('input[type="text"]').val(value);
           }
        });
        etsSeoAdmin.initModalExplain();
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();
        etsSeoAdmin.changeSiteOriginalOrPersonal('#ETS_SEO_SITE_OF_PERSON_OR_COMP');
        etsSeoAdmin.onChangeEnableRating();
        etsSeoAdmin.onChangeRatingConfig();
        menuheaderheight();
        $(window).resize(function(){
            menuheaderheight();
        });
        $(window).on('load', function(){
            menuheaderheight();
        });

        if(ETS_SEO_CONTROLLER == 'AdminProducts' || ETS_SEO_CONTROLLER == 'AdminMeta' || ETS_SEO_CONTROLLER == 'AdminCmsContent')
        {
            $(document).on('click', 'form.product-page input[type=submit], button[type=submit], form[name=meta] button:last-child', function(){

                $error = false;
                $('.ets-rating-error').remove();
                if($('select[name=ets_seo_rating_enable]').length)
                {
                    if($('select[name=ets_seo_rating_enable]').val() == 1)
                    {
                        var ratingAvg = $('input[name=ets_seo_rating_average]');
                        var ratingCount = $('input[name=ets_seo_rating_count]');
                        var bestRating = $('input[name=ets_seo_rating_best]');
                        var worstRating = $('input[name=ets_seo_rating_worst]');
                        if(!ratingAvg.val())
                        {
                            etsSeoAdmin.showErrorRating(ratingAvg, ETS_SEO_MESSAGE['rating_avg_required']);
                            $error = true;
                        }
                        else if(isNaN(ratingAvg.val()) || parseFloat(ratingAvg.val()) <= 0 || parseFloat(ratingAvg.val()) > 5)
                        {
                            etsSeoAdmin.showErrorRating(ratingAvg, ETS_SEO_MESSAGE['rating_avg_decimal']);
                            $error = true;
                        }
                        else{
                            if(bestRating.val() != '')
                            {

                                if(!etsSeoAdmin.isInt(bestRating.val()) || parseInt(bestRating.val()) < parseFloat(ratingAvg.val()) || parseInt(bestRating.val()) > 5)
                                {
                                    etsSeoAdmin.showErrorRating(bestRating, ETS_SEO_MESSAGE['best_rating_int']);
                                    $error = true;
                                }
                            }
                            if(worstRating.val() != '')
                            {
                                if(!etsSeoAdmin.isInt(worstRating.val()) || parseInt(worstRating.val()) > parseFloat(worstRating.val()) || parseInt(worstRating.val()) <= 0)
                                {
                                    etsSeoAdmin.showErrorRating(worstRating, ETS_SEO_MESSAGE['worst_rating_int']);
                                    $error = true;
                                }
                            }
                        }

                        if(!ratingCount.val())
                        {
                            etsSeoAdmin.showErrorRating(ratingCount, ETS_SEO_MESSAGE['rating_count_required']);
                            $error = true;
                        }
                        else if(!etsSeoAdmin.isInt(ratingCount.val()) || parseInt(ratingCount.val()) <= 0)
                        {
                            etsSeoAdmin.showErrorRating(ratingCount, ETS_SEO_MESSAGE['rating_count_invalid']);
                            $error = true;
                        }
                    }
                }
                return !$error;
            });
        }
    });
    function menuheaderheight(){
        var menuheight = $('.ets_seo_menu').height();
        $('.ets_menu_height').css('height',menuheight);
    }

})(jQuery);
function etsSeoLogoError(img){
    $(img).parent().html('');
    var name = $(img).attr('data-name');
    $('#'+name+'-name').val('');
}
