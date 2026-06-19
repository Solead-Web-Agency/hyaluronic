{*
* 2007-2016 PrestaShop
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
*  @author    SeoSA <885588@bk.ru>
*  @copyright 2012-2022 SeoSA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script>
    var allowEmployeeFormLang = 0;
    var id_language = {$default_form_language|intval};
    var languages = new Array();
    {foreach from=$languages item=language}
    languages.push({
        id_lang: {$language.id_lang|intval},
        name: "{$language.name|escape:'quotes':'UTF-8'}",
        iso_code: "{$language.iso_code|escape:'quotes':'UTF-8'}"
    });
    {/foreach}
</script>
{if isset($product->id)}

    {if $ps_version >= 1.7}
        <div class="product-featuress-temp">
            {assign var="l" value="0"}
            <div class="row product-feature hidden">
                <div class="col-lg-4">
                    <fieldset class="form-group mb-0">
                        <label class="form-control-label">{l s='Feature' mod='dgridproducts'}</label>
                        <select id="form_features_{$l}_value"
                                name="feature-{$l}"
                                class="feature-value-selector custom-select"
                                onchange="getvalfeatures(this);"
                                data-minimumresultsforsearch="7">
                            <option value="0">Choose a value</option>
                            {foreach from=$available_features item=available_feature}
                                <option value="{$available_feature.id_feature|intval}"
                                >
                                    {$available_feature.name|truncate:40|escape:'quotes':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset class="form-group mb-0">
                        <label class="form-control-label">{l s='Pre-defined value' mod='dgridproducts'}</label>
                        <select id="form_features_{$l}_value_33" name="feature_values"
                                class="feature-value-selector custom-select"
                                onchange="replace_name(this);"
                                data-minimumresultsforsearch="7"
                                disabled>
                            {foreach from=$available_features item=available_feature}
                                {*{if $prod_feature['id_feature'] == $available_feature.id_feature}*}
                                <option value="0" {if $available_feature.val}selected="selected"{/if}>
                                    Choose a value
                                </option>
                                {foreach from=$available_feature.featureValues item=value}
                                    <option value="{if $value.id_feature_value|intval}{$value.id_feature_value|intval}{else}0{/if}">
                                        {$value.value|truncate:40|escape:'quotes':'UTF-8'}
                                    </option>
                                {/foreach}
                                {*{/if}*}
                            {/foreach}
                        </select>
                    </fieldset>
                </div>
                <div class="col-lg-3">
                    <fieldset class="form-group mb-0">
                        <label class="form-control-label">{l s='OR Customized value' mod='dgridproducts'}</label>
                        <div class="translations tabbable" id="form_features_{$l}_custom_value">
                            <div class="translationsFields tab-content">
                                {foreach  from=$languages key=k item=lang}
                                    <div data-locale="{$lang.iso_code}"
                                         class="translationsFields-form_features_{$l}_custom_value_{$lang.id_lang} tab-pane custom-text
{if $k == 0} active {/if} translation-field translation-label-{$lang.iso_code}">
                                        <input
                                                type="text"
                                                id="form_features_{$l}_custom_value_{$l}"
                                                value="{if !empty($prod_feature.custom_value)}
{foreach  from=$prod_feature.custom_value key=k item=custom}
{if $custom.id_lang == $lang.id_lang}{$custom.value}{/if}
{/foreach}{/if}"
                                                name="custom_{$lang.id_lang|escape:'quotes':'UTF-8'}_{$l}"
                                                class="form-control"
                                                disabled>
                                    </div>
                                {/foreach}
                                <input type="text" name="end_"
                                       style="visibility: hidden;height: 0px;padding: 0px;margin: 0px;">
                            </div>

                    </fieldset>
                </div>
                <div class="col-lg-1 col-xl-1">
                    <a class="btn tooltip-link delete pl-0 pr-0">
                        <i class="material-icons">delete</i>
                    </a>
                </div>
            </div>
        </div>
    {/if}

    <div id="product-features" class="panel product-tab">
        <input type="hidden" name="id_product" value="{$product->id|intval}"/>
        <h3>{l s='Assign features to this product' mod='dgridproducts'}</h3>

        <div class="alert alert-info hint" style="display: block;">
            {l s='You can specify a value for each relevant feature regarding this product. Empty fields will not be displayed.' mod='dgridproducts'}
            <br/>
            {l s='You can either create a specific value, or select among the existing pre-defined values you\'ve previously added.' mod='dgridproducts'}
        </div>

        {if $ps_version >= 1.7}
            <div id="product-featuress" class="panel product-tab">
                <div id="features-content" class="content">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form_switch_language float-left">
                                <select id="form_switch_language" class="custom-select" onchange="getval(this);"
                                        name="id_lang">
                                    {foreach from=$languages key=k item=language}
                                        <option data-id="{$language.id_lang}"
                                                value="{$language.id_lang}">{$language.iso_code}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                    {if !empty($product_features)}
                        {foreach from=$product_features key=l item=prod_feature}
                            <div class="row product-feature">
                                <div class="col-lg-4">
                                    <fieldset class="form-group mb-0">

                                        <label class="form-control-label">{l s='Feature' mod='dgridproducts'}</label>
                                        <select id="form_features_{$l}_value"
                                                name="feature-{$l}"
                                                class="feature-value-selector custom-select"
                                                onchange="getvalfeatures(this);"
                                                data-minimumresultsforsearch="7">
                                            <option value="0">Choose a value</option>
                                            {foreach from=$available_features item=available_feature}
                                                <option value="{$available_feature.id_feature|intval}"
                                                        {if $prod_feature['id_feature'] == $available_feature.id_feature}selected="selected"{/if} >
                                                    {$available_feature.name|truncate:40|escape:'quotes':'UTF-8'}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </fieldset>
                                </div>
                                <div class="col-lg-4">
                                    <fieldset class="form-group mb-0">
                                        <label class="form-control-label">{l s='Pre-defined value' mod='dgridproducts'}</label>
                                        <select id="form_features_{$l}_value_2" name="feature_values"
                                                class="feature-value-selector custom-select"
                                                onchange="replace_name(this);"
                                                data-minimumresultsforsearch="7">
                                            {foreach from=$available_features item=available_feature}
                                                {if $prod_feature['id_feature'] == $available_feature.id_feature}
                                                    <option value="0"
                                                            {if $available_feature.val}selected="selected"{/if}>
                                                        Choose a value
                                                    </option>
                                                    {foreach from=$available_feature.featureValues item=value}
                                                        <option value="{if $value.id_feature_value|intval}{$value.id_feature_value|intval}{else}0{/if}"
                                                                {if $prod_feature['id_feature_value'] == $value.id_feature_value}selected="selected"{/if} >
                                                            {$value.value|truncate:40|escape:'quotes':'UTF-8'}
                                                        </option>
                                                    {/foreach}
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </fieldset>
                                </div>
                                <div class="col-lg-3">
                                    <fieldset class="form-group mb-0">
                                        <label class="form-control-label">{l s='OR Customized value' mod='dgridproducts'}</label>
                                        <div class="translations tabbable" id="form_features_{$l}_custom_value">
                                            <div class="translationsFields tab-content">
                                                {foreach  from=$languages key=k item=lang}
                                                    <div data-locale="{$lang.iso_code}"
                                                         class="translationsFields-form_features_{$l}_custom_value_{$lang.id_lang} tab-pane custom-text
{if $k == 0} active {/if} translation-field translation-label-{$lang.iso_code}">
                                                        <input type="text" id="form_features_{$l}_custom_value_{$l}"
                                                               value="{if !empty($prod_feature.custom_value)}{foreach  from=$prod_feature.custom_value key=k item=custom}{if $custom.id_lang == $lang.id_lang}{$custom.value}{/if}{/foreach}{/if}"
                                                               name="custom_{$lang.id_lang|escape:'quotes':'UTF-8'}_{$l}"
                                                               class="form-control">
                                                    </div>
                                                {/foreach}
                                                <input type="text" name="end_"
                                                       style="visibility: hidden;height: 0px;padding: 0px;margin: 0px;">
                                            </div>

                                    </fieldset>
                                </div>
                                <div class="col-lg-1 col-xl-1">
                                    <a class="btn tooltip-link delete pl-0 pr-0">
                                        <i class="material-icons">delete</i>
                                    </a>
                                </div>
                            </div>
                        {/foreach}
                    {/if}
                </div>

                <div class="row form-group">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary sensitive add" id="add_feature_buttons"><i
                                    class="material-icons">add_circle</i> {l s='Add a feature' mod='dgridproducts'}
                        </button>
                    </div>
                </div>
            </div>
        {else}
            <div class="list_features">
                <table class="table" style="width: 100%">
                    <thead>
                    <tr>
                        <th><span class="title_box">{l s='Feature' mod='dgridproducts'}</span></th>
                        <th><span class="title_box">{l s='Pre-defined value' mod='dgridproducts'}</span></th>
                        <th>
                            <span class="title_box"><u>{l s='or' mod='dgridproducts'}</u> {l s='Customized value' mod='dgridproducts'}
                            </span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$available_features item=available_feature}
                        <tr>
                            <td>{$available_feature.name|escape:'quotes':'UTF-8'}</td>
                            <td>
                                {if sizeof($available_feature.featureValues)}
                                    <select class="fixed-width-xxl custom-select"
                                            id="feature_{$available_feature.id_feature|intval}_value"
                                            name="feature_{$available_feature.id_feature|intval}_value"
                                            onchange="$('.custom_{$available_feature.id_feature|intval}_').val('');">
                                        <option value="0">---</option>
                                        {foreach from=$available_feature.featureValues item=value}
                                            <option value="{if $value.id_feature_value|intval}{$value.id_feature_value|intval}{else}0{/if}"
                                                    {if $available_feature.current_item == $value.id_feature_value}selected="selected"{/if} >
                                                {$value.value|truncate:40|escape:'quotes':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                {else}
                                    <input type="hidden" name="feature_{$available_feature.id_feature|intval}_value"
                                           value="0"/>
                                    <span>{l s='N/A' mod='dgridproducts'} -
                                        <a href="{$link->getAdminLink('AdminFeatures')|escape:'html':'UTF-8'}&amp;addfeature_value&amp;id_feature={$available_feature.id_feature|intval}"
                                           class="confirm_leave btn btn-link"><i
                                                    class="icon-plus-sign"></i> {l s='Add pre-defined values first' mod='dgridproducts'}
                                            <i
                                                    class="icon-external-link-sign"></i></a>
                                    </span>
                                {/if}
                            </td>
                            <td {if $ps_version < 1.6}class="translatable"{/if}>
                                {if $ps_version == 1.6}
                                    <div class="row lang-0" style='display: none;'>
                                        <div class="col-lg-9">
                                            <textarea
                                                    class="custom_{$available_feature.id_feature|intval}_ALL textarea-autosize"
                                                    name="custom_{$available_feature.id_feature|intval}_ALL"
                                                    cols="40" style='background-color:#CCF' rows="1"
                                                    onkeyup="{foreach from=$languages key=k item=language}$('.custom_{$available_feature.id_feature|intval}_{$language.id_lang|intval}').val($(this).val());{/foreach}">{$available_feature.val[1].value|escape:'html':'UTF-8'|default:""}</textarea>

                                        </div>
                                        {if $languages|count > 1}
                                            <div class="col-lg-3">
                                                <button type="button" class="btn btn-default dropdown-toggle"
                                                        data-toggle="dropdown">
                                                    {l s='ALL' mod='dgridproducts'}
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    {foreach from=$languages item=language}
                                                        <li>
                                                            <a href="javascript:void(0);"
                                                               onclick="restore_lng($(this),{$language.id_lang|intval});">{$language.iso_code|escape:'quotes':'UTF-8'}</a>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                    {foreach from=$languages key=k item=language}
                                        {if $languages|count > 1}
                                            <div class="row translatable-field lang-{$language.id_lang|intval}">
                                            <div class="col-lg-9">
                                        {/if}
                                        <textarea
                                                class="custom_{$available_feature.id_feature|intval}_{$language.id_lang|intval} textarea-autosize"
                                                name="custom_{$available_feature.id_feature|intval}_{$language.id_lang|intval}"
                                                cols="40"
                                                rows="1"
                                                onkeyup="if (isArrowKey(event)) return ;$('#feature_{$available_feature.id_feature|intval}_value').val(0);">{$available_feature.val[$k].value|escape:'html':'UTF-8'|default:""}</textarea>
                                        {if $languages|count > 1}
                                            </div>
                                            <div class="col-lg-3">
                                                <button type="button" class="btn btn-default dropdown-toggle"
                                                        data-toggle="dropdown">
                                                    {$language.iso_code|escape:'quotes':'UTF-8'}
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="javascript:void(0);"
                                                           onclick="all_languages($(this));">{l s='ALL' mod='dgridproducts'}</a>
                                                    </li>
                                                    {foreach from=$languages item=language}
                                                        <li>
                                                            <a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.iso_code|escape:'quotes':'UTF-8'}</a>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                            </div>
                                        {/if}
                                    {/foreach}
                                {else}
                                    {foreach from=$languages key=k item=language}
                                        <div class="lang_{$language.id_lang|intval}"
                                             style="{if $language.id_lang != $default_form_language}display:none;{/if}float: left;">
                                            <textarea class="custom_{$available_feature.id_feature|intval}_"
                                                      name="custom_{$available_feature.id_feature|intval}_{$language.id_lang|intval}"
                                                      cols="40" rows="1"
                                                      onkeyup="if (isArrowKey(event)) return ;$('#feature_{$available_feature.id_feature|intval}_value').val(0);">{$available_feature.val[$k].value|escape:'htmlall':'UTF-8'|default:""}</textarea>
                                        </div>
                                    {/foreach}
                                {/if}
                            </td>

                        </tr>
                        {foreachelse}
                        <tr>
                            <td colspan="3" style="text-align:center;"><i
                                        class="icon-warning-sign"></i> {l s='No features have been defined' mod='dgridproducts'}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <a href="{$link->getAdminLink('AdminFeatures')|escape:'html':'UTF-8'}&amp;addfeature" target="_blank"
               class="btn btn-link confirm_leave button">
                <i class="icon-plus-sign"></i> {l s='Add a new feature' mod='dgridproducts'} <i
                        class="icon-external-link-sign"></i>
            </a>
        {/if}

        <div class="panel-footer">
            <button href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}"
                    class="btn btn-default btn-lg close_form_features">
                {l s='Cancel' mod='dgridproducts'}
            </button>
            <button type="button" name="submitAddproduct" class="btn btn-primary btn-lg pull-right saveFeatures17">
                {l s='Save' mod='dgridproducts'}
            </button>
        </div>
    </div>
{/if}
{if $ps_version == 1.6}
    <script type="text/javascript">
        hideOtherLanguage({$default_form_language|intval});
        {literal}
        $(".textarea-autosize").autosize();

        function all_languages(pos) {
            {/literal}
            {foreach from=$languages key=k item=language}
            pos.parents('td').find('.lang-{$language.id_lang|intval}').addClass('nolang-{$language.id_lang|intval}').removeClass('lang-{$language.id_lang|intval}');
            {/foreach}
            pos.parents('td').find('.translatable-field').hide();
            pos.parents('td').find('.lang-0').show();
            {literal}
        }

        function restore_lng(pos, i) {
            {/literal}
            {foreach from=$languages key=k item=language}
            pos.parents('td').find('.nolang-{$language.id_lang|intval}').addClass('lang-{$language.id_lang|intval}').removeClass('nolang-{$language.id_lang|intval}');
            {/foreach}
            {literal}
            pos.parents('td').find('.lang-0').hide();
            hideOtherLanguage(i);
        }
    </script>
{/literal}
{/if}

<script>
    $(function () {
        displayFlags(languages, id_default_lang, allowEmployeeFormLang);
    });

    function replace_name(e) {
        id_value = $(e).val();
        name = "feature_values_" + id_value;
        $(e).attr('name', name);
    }

    // При загрузке меням во вторых селектах name в зависимости от значений
    $(document).ready(function () {
        $("#product-features select").each(function (index, e) {
            id_value = $(e).val();
            text_id = $(e).attr('id');
            if (text_id.indexOf('value_2') > 0) {
                name = "feature_values_" + id_value;
            } else {
                return true;
            }
            $(e).attr('name', name);
        });
        indexs();
    });

    // при смене языка меняем значения в custom полях
    function getval(sel) {
        var lang = $(sel).find('option:selected').text()
        console.log(sel);
        $('.custom-text').removeClass('active')
        $('.translation-label-' + lang).addClass('active');
    }

    function getvalfeatures(e) {
        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'get_attribut',
                id: e.value,
                id_lang: $('#form_switch_language').find(":selected").data('id'),
                id_select: e.id,
            },
            success: function (r) {
                var count = r.content.length;
                if (count > 0) {
                    $('#form_features_' + r.id_select + '_value_2').children().remove().end().append('<option selected value="0">' + "Choose a value" + '</option>');
                    for (let i = 0; i < count; i++) {
                        $('#form_features_' + r.id_select + '_value_2').append('<option  value="' + r.content[i].id_feature_value + '">' + r.content[i].value + '</option>');
                    }
                }
                indexs();
            }
        });
    }

    $('body').on('click', '.delete', function () {
        this.closest('.product-feature').remove();
        indexs();
    });
    // добавление характеристики
    $('#add_feature_buttons').on('click', function () {
        clon = $('.product-featuress-temp').find('.product-feature').clone();
        $('#features-content').append(clon);
        // $('.product-feature:last').attr('style', '');
        $('.product-feature:last').removeClass('hidden');
        $('.product-feature:last select option[value="0"]').attr("selected", "selected");
        $('.product-feature:last input').attr("value", "");
        // $('.product-feature').each(function() {
        //     if ($(this).css('visibility') === 'hidden') {
        //         $(this).remove();
        //     }
        // });
        count_fea = $('.product-feature').length;
        val = 'form_features_' + count_fea + '_value_2';
        val2 = 'form_features_' + count_fea + '_value';
        $('.product-feature:last').find('select:last').attr('id', val);
        $('.product-feature:last').find('select:first').attr('id', val2);
        indexs();
    });

    function indexs() {
        var i = 0;
        $("#product-features select").each(function (index, e) {
            if (e.name.indexOf('feature-') == 0) {
                e.name = 'feature-' + i;
                i = i + 1;
            }
        });
        // переиндексация полей custom
        var i = 0;
        var j = 0;
        var k = 0;
        var name;
        $("#product-features input").each(function (index, e) {
            if (e.name.lastIndexOf('custom') == 0) {
                position = e.name.lastIndexOf('_');
                console.log(position);
                replace_name_custom = e.name.substring(0, position);
                name = replace_name_custom + "_" + i;
                $(e).attr('name', name);
        //        j = j + 1;
        //        if (j == 2) {
                    i = i + 1;
        //            j = 0;
           //     }
            }
            if (e.name.lastIndexOf('end') == 0) {
                position = e.name.lastIndexOf('_');
                console.log(position);
                replace_name_end = e.name.substring(0, position);
                name = replace_name_end + "_" + k;
                $(e).attr('name', name);
                k = k + 1;
            }

        });
    }

    function unserialize(data) {
        data = data.split('&');
        var response = {};
        for (var k in data) {
            var newData = data[k].split('=');
            response[newData[0]] = newData[1];
        }
        return response;
    }

    $('[name*="feature-"]').live('change', function () {
        if ($(this).val() != 0 ) {
            $(this).closest('.product-feature').find('[name*="feature_values"]').prop('disabled', false);
            $(this).closest('.product-feature').find('input').prop('disabled', false);
        } else {
            $(this).closest('.product-feature').find('[name*="feature_values"]').prop('disabled', true);
            $(this).closest('.product-feature').find('input').prop('disabled', true);
        }
    });

    $('.saveFeatures17').live('click', function () {
        data_serioliz = $('#product-features :input').serialize();
        data_unserializ = unserialize(data_serioliz);
        var data = '';
        $.each(data_unserializ, function (index, value) {
            data = data + index + "=" + value + "&";
        });

        var save_features_error = false;
        $.each($('#product-features .product-feature'), function () {
            if (
                $(this).find('[name*="feature-"]').val() != 0 && (
                    $(this).find('[name*="feature_values"]').val() != 0 || $(this).find('input').val()
                )
            ) {

            } else {
                $(this).addClass('has-error');
                $(this).addClass('was-validated');
                save_features_error = true;
            }
        });

        if (!save_features_error) {
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: 'json',
                data: data + '&ajax=true&action=save_features',
                success: function (r) {
                    if (r.hasError) {
                        $.alert(r.error);

                        setTimeout(function () {
                            $('body').find('.jconfirm').addClass('bootstrap');
                        }, 1);
                    }
                    else {
                        $('.stage_features, .form_features').stop(true, true).fadeOut(500);
                        scrollTolastEditProduct();
                    }
                }
            });
        }


    });

</script>