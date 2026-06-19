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
* @author    SeoSA <885588@bk.ru>
* @copyright 2012-2022 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{capture name='tr_count'}{counter name='tr_count'}{/capture}
<tbody>
{if count($list)}
    {foreach $list AS $index => $tr}
        <tr{if $position_identifier} id="tr_{$position_group_identifier}_{$tr.$identifier}_{if isset($tr.position['position'])}{$tr.position['position']}{else}0{/if}"{/if} class="{if isset($tr.class)}{$tr.class}{/if} {if $tr@iteration is odd by 1}odd{/if}"{if isset($tr.color) && $color_on_bg} style="background-color: {$tr.color}"{/if} >
            {if $bulk_actions && $has_bulk_actions}
                <td class="row-selector text-center">
                    {if isset($list_skip_actions.delete)}
                        {if !in_array($tr.$identifier, $list_skip_actions.delete)}
                            <input type="checkbox" name="{$list_id}Box[]" value="{$tr.$identifier}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier}, $checked_boxes)} checked="checked"{/if} class="noborder" />
                        {/if}
                    {else}
                        <input type="checkbox" name="{$list_id}Box[]" value="{$tr.$identifier}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier}, $checked_boxes)} checked="checked"{/if} class="noborder" />
                    {/if}
                </td>
            {/if}
            {foreach $fields_display AS $key => $params}
                {block name="open_td"}
                    <td
                    {if isset($params.position)}
                        id="td_{if !empty($position_group_identifier)}{$position_group_identifier}{else}0{/if}_{$tr.$identifier}{if $smarty.capture.tr_count > 1}_{($smarty.capture.tr_count - 1)|intval}{/if}"
                    {/if}
                    class="table-product_td {strip}{if !$no_link}pointer{/if}
					{if isset($params.position) && $order_by == 'position'  && $order_way != 'DESC'} dragHandle{/if}
					{if isset($params.class)} {$params.class}{/if}
					{if isset($params.align)} {$params.align}{/if}{/strip}"
                    {if (!isset($params.position) && !$no_link && !isset($params.remove_onclick))}
                        {if isset($tr.link) }
                            onclick="document.location = '{$tr.link}'">
                        {else}
                            onclick="document.location = '{$current_index|addslashes|escape:'html':'UTF-8'}&amp;{$identifier|escape:'html':'UTF-8'}={$tr.$identifier|escape:'html':'UTF-8'}{if $view}&amp;view{else}&amp;update{/if}{$table|escape:'html':'UTF-8'}{if $page > 1}&amp;page={$page|intval}{/if}&amp;token={$token|escape:'html':'UTF-8'}'">
                        {/if}
                    {else}
                        >
                    {/if}
                {/block}


                {block name="td_content"}
                {if (isset($params.table) && $params.table == 'stock_available' && $tr['depends_on_stock'] == 1 && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewAdvancedStockManagement" href="#" title="{l s='Advanced stock management' mod='dgridproducts'}">
                        <i class="icon-archive"></i>({$tr.sav_quantity|intval})
                    </a>
                    {else}
                {if isset($params.help)}
                    <div class="help_message">{$params.help|escape:'quotes':'UTF-8'}</div>
                {/if}

                    <div
                            class="{if $params.type == 'loc'}{if $tr.locale}hidden{/if}{/if} {if isset($params.image)}edit_image {/if}{if isset($params.need_edit) && $params.need_edit} edit_field {if $params.validate == 'category'}edit_category{/if} {if $ps_v < 1.6}v15{/if}
                {/if}"
                            {if isset($params.need_edit) && $params.need_edit && $params.validate == 'category'}
                                data-id="{$tr.id|intval}"
                            {/if}
                            {if isset($params.image)}
                                data-id="{$tr.id|intval}"
                            {/if}
                    >
                        {assign var="none_close_td" value=true}
                        {if !in_array($params.type, [
                        'combinations',
                        'features',
                        'meta_tags',
                        'additional_setting_product',
                        'specific_price',
                        'short_description',
                        'description',
                        'date_add',
                        'brand',
                        'visibility',
                        'condition',
                        'id_tax_rules_group',
                        'tag'
                        ]
                        )
                        }
                            {*td_content*}
                            {if isset($params.prefix)}{$params.prefix|escape:'quotes':'UTF-8'}{/if}
                            {if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}<span class="badge badge-success">{/if}
                        {if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}<span class="badge badge-warning">{/if}
                        {if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}<span class="badge badge-danger">{/if}
                        {if isset($params.color) && isset($tr[$params.color])}
                            <span class="label color_field" style="background-color:{$tr[$params.color]|escape:'quotes':'UTF-8'};color:{if Tools::getBrightness($tr[$params.color]) < 128}white{else}#383838{/if}">
                        {/if}
                            {if isset($tr.$key)}
                                {if isset($params.active)}
                                    {$tr.$key|escape:'quotes':'UTF-8'}
                                {elseif isset($params.activeVisu)}
                                    {if $tr.$key}
                                        <i class="icon-check-ok"></i> {l s='Enabled' mod='dgridproducts'}
                                    {else}
                                        <i class="icon-remove"></i> {l s='Disabled' mod='dgridproducts'}
                                    {/if}

                                {elseif isset($params.position)}
                                    {if $order_by == 'position' && $order_way != 'DESC'}
                                        <div class="dragGroup">
                                            <div class="positions">
                                                {$tr.$key.position|escape:'quotes':'UTF-8'}
                                            </div>
                                        </div>
                                    {else}
                                        {($tr.$key.position + 1)|escape:'quotes':'UTF-8'}
                                    {/if}
                                {elseif isset($params.image)}
                                    <div class="image_default">

                                        {if version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '>=')}
                                            {if $tr.$key}
                                                {$tr.$key|escape:'quotes':'UTF-8'}
                                                <img src="{$tr.image_default}">
                                            {else}
                                                <img class="imgm img-thumbnail" src="{$tr.image_default}">
{*                                                <img class="imgm img-thumbnail" src="../img/p/en.jpg">*}
                                                <img src="../img/p/en.jpg">
                                            {/if}
                                        {else}
                                            {if $tr.$key}
                                                {$tr.$key|escape:'quotes':'UTF-8'}
                                            {else}
                                                <img class="imgm img-thumbnail" src="{$tr.image_default}">
                                            {/if}
                                            <img src="{$tr.image_default}">
                                        {/if}
                                    </div>
                                {elseif isset($params.icon)}
                                    {if is_array($tr[$key])}
                                        {if isset($tr[$key]['class'])}
                                            <i class="{$tr[$key]['class']|escape:'quotes':'UTF-8'}"></i>
                                        {else}
                                            <img src="../img/admin/{$tr[$key]['src']|escape:'quotes':'UTF-8'}" alt="{$tr[$key]['alt']|escape:'quotes':'UTF-8'}" title="{$tr[$key]['alt']|escape:'quotes':'UTF-8'}" />
                                        {/if}
                                    {else}
                                        <i class="{$tr[$key]|escape:'quotes':'UTF-8'}"></i>
                                    {/if}
                                {elseif isset($params.type) && $params.type == 'price'}
                                    {displayPrice price=$tr.$key}
                                {elseif isset($params.float)}
                                    {$tr.$key|escape:'quotes':'UTF-8'}
                                {elseif isset($params.type) && $params.type == 'date'}
                                    {dateFormat date=$tr.$key full=0}
                                {elseif isset($params.type) && $params.type == 'datetime'}
                                    {dateFormat date=$tr.$key full=1}
                                {elseif isset($params.type) && $params.type == 'decimal'}
                                    {$tr.$key|string_format:"%.2f"|escape:'quotes':'UTF-8'}
                                {elseif isset($params.type) && $params.type == 'percent'}
                                    {$tr.$key|escape:'quotes':'UTF-8'} {l s='%' mod='dgridproducts'}
                                    {* If type is 'editable', an input is created *}
                                {elseif isset($params.type) && $params.type == 'editable' && isset($tr.id)}
                                    <input type="text" name="{$key|escape:'quotes':'UTF-8'}_{$tr.id|escape:'quotes':'UTF-8'}" value="{$tr.$key|escape:'html':'UTF-8'}" class="{$key|escape:'quotes':'UTF-8'}" />
                                {elseif isset($params.callback)}
                                    {if isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
                                        <span title="{$tr.$key|escape:'quotes':'UTF-8'}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'quotes':'UTF-8'}</span>
                                    {else}
                                        {$tr.$key|escape:'quotes':'UTF-8'}
                                    {/if}
                                {elseif $key == 'color'}
                                    {if !is_array($tr.$key)}
                                        <div style="background-color: {$tr.$key|escape:'quotes':'UTF-8'};" class="attributes-color-container"></div>
                                    {else} {*TEXTURE*}
                                        <img src="{$tr.$key.texture|escape:'quotes':'UTF-8'}" alt="{$tr.name|escape:'quotes':'UTF-8'}" class="attributes-color-container" />
                                    {/if}
                                {elseif isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
                                    <span title="{$tr.$key|escape:'html':'UTF-8'}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'html':'UTF-8'}</span>
                                {else}
                                    {$tr.$key|escape:'html':'UTF-8'}
                                {/if}
                            {else}
                                {block name="default_field"}--{/block}
                            {/if}
                            {if isset($params.suffix)}{$params.suffix|escape:'quotes':'UTF-8'}{/if}
                        {if isset($params.color) && isset($tr.color)}
                            </span>
                        {/if}
                        {if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}</span>{/if}
                        {if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}</span>{/if}
                            {if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}</span>{/if}
                            {*td_content*}
                        {/if}
                    </div>
                {if isset($params.need_edit) && $params.need_edit && ($params.table != 'stock_available' || $tr['depends_on_stock'] != 1 || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))}
                {if !$params.lang}
                    <div class="form_edit_field{if $ps_v < 1.6} v15{/if}">
                        <textarea  {if $params.validate == 'price'}onkeypress="return isNumberKey(event)"{/if}
                                {if isset($params.maxlength)}maxlength="{$params.maxlength|intval}"{/if}
                                   data-event-save="1"
                                {if isset($params.shop) && $params.shop && $shop_active}data-shop="true"{/if}
                                   data-criterion="id_product"
                                   data-validate="{$params.validate|escape:'quotes':'UTF-8'}"
                                   data-field-id="{$tr.id|intval}"
                                   data-field-table="{$params.table|escape:'quotes':'UTF-8'}"
                                   data-field-name="{$params.field|escape:'quotes':'UTF-8'}"
                                   data-field-lang="{$params.lang|escape:'quotes':'UTF-8'}"
                                {if isset($tr['rate']) && in_array($params.field, ['price', 'price_final'])}data-rate="{$tr['rate']|escape:'quotes':'UTF-8'}"{/if}
                                   class="{$params.type} {if $params.type == 'loc'}{if $tr.locale}hidden{/if}{/if} type_{if isset($params.validate) && $params.validate}{$params.validate|escape:'quotes':'UTF-8'}{else}{$params.type|escape:'quotes':'UTF-8'}{/if}">{if array_key_exists("`$key`_no_format", $tr)}{$tr["`$key`_no_format"]|escape:'quotes':'UTF-8'}{else}{if isset($tr.$key) && $tr.$key}{if $params.validate == price}{$tr.$key|replace:' ':''|replace:',':'.'|floatval}{else}{$tr.$key|escape:'quotes':'UTF-8'}{/if}{/if}{/if}</textarea>
                    </div>
                    {else}
                    <div class="form_edit_field{if $ps_v < 1.6} v15{/if}">
                        {foreach from=$tr["`$key`_lang"] key=kItem item=item}
                            <div class="lang_{$kItem|escape:'quotes':'UTF-8'}" {if $kItem != $default_lang->id}style="display: none;" {/if}>
                                <textarea  {if $params.validate == 'price'}onkeypress="return isNumberKey(event)"{/if}
                                        {if isset($params.maxlength)}maxlength="{$params.maxlength|intval}"{/if}
                                           data-event-save="1"
                                        {if isset($params.shop) && $params.shop && $shop_active}data-shop="true"{/if}
                                           data-criterion="id_product"
                                           data-field-id="{$tr.id|intval}"
                                           data-field-table="{$params.table|escape:'quotes':'UTF-8'}"
                                           data-field-name="{$params.field|escape:'quotes':'UTF-8'}"
                                           data-field-lang="{$params.lang|escape:'quotes':'UTF-8'}"
                                           data-field-id-lang="{$kItem|escape:'quotes':'UTF-8'}"
                                           class="type_{if isset($params.validate) && $params.validate}{$params.validate|escape:'quotes':'UTF-8'}{else}{$params.type|escape:'quotes':'UTF-8'}{/if}">{$item|escape:'quotes':'UTF-8'}</textarea>
                            </div>
                        {/foreach}
                        <div class="btn_lang">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                                {$default_lang->iso_code|escape:'quotes':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                    <li>
                                        <a data-lang-iso="{$lang.iso_code|escape:'quotes':'UTF-8'}" onclick="changeLang(this, {$lang.id_lang|intval}); return false;">{$lang.name|escape:'quotes':'UTF-8'}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                {/if}
                    {else}
                {if $params.type == 'combinations'}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewCombinations" title="{l s='Combinations' mod='dgridproducts'}" href="#"><i class="icon-list"></i></a>
                {/if}
                {if $params.type == 'features'}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewFeatures" href="#">{l s='Fe-s' mod='dgridproducts'}</a>
                {/if}
                {if $params.type == 'meta_tags'}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewMetaTags" href="#">{l s='Meta' mod='dgridproducts'}</a>
                {/if}
                {if $params.type == 'additional_setting_product'}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewAdditionalSettingProduct" href="#">{l s='More' mod='dgridproducts'}</a>
                {/if}
                {if $params.type == 'specific_price'}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" class="button btn btn-default viewSpecificPrices {if $tr.has_specific_price}has_specific_price{/if} {if $tr.has_group_specific_price}has_group_specific_price{/if}" href="#" title="{l s='Specific prices' mod='dgridproducts'}">%{if $tr.has_specific_price || $tr.has_group_specific_price}{$tr.count_specific_price|intval}{/if}</a>
                {/if}
                {if $params.type == 'short_description'}

                {if $lenght_short_desc}
                    <span class="cell_description">
                        {$tr['description_short']|strip_tags|truncate:{$lenght_short_desc|intval}:''}...
                    </span>
                <br class="hidden-xs">
                {/if}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-short="1" class="button btn btn-default viewDescription" href="#" title="{l s='Short description' mod='dgridproducts'}">Aa</a>
                {/if}
                {if $params.type == 'description'}

                {if $lenght_desc}
                    <span class="cell_description">
                        {$tr['description']|strip_tags|truncate: {$lenght_desc|intval}:''}...
                    </span>
                <br class="hidden-xs">
                {/if}
                    <a data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-short="0" class="button btn btn-default viewDescription" href="#" title="{l s='Description' mod='dgridproducts'}">Aa</a>
                {/if}
                {if $params.type == 'date_add'}

                    {* for 1.5*}
                    <script type="text/javascript">
                        $(document).ready(function() {
                            if ($(".datetimepicker").length > 0)
                                $('.datetimepicker').datetimepicker({
                                    prevText: '',
                                    nextText: '',
                                    dateFormat: 'yy-mm-dd',
                                    // Define a custom regional settings in order to use PrestaShop translation tools
                                    currentText: '{l s='Now' js=1 mod='dgridproducts'}',
                                    closeText: '{l s='Done' js=1 mod='dgridproducts'}',
                                    ampm: false,
                                    amNames: ['AM', 'A'],
                                    pmNames: ['PM', 'P'],
                                    timeFormat: 'hh:mm:ss tt',
                                    timeSuffix: '',
                                    timeOnlyTitle: '{l s='Choose Time' js=1 mod='dgridproducts'}',
                                    timeText: '{l s='Time' js=1 mod='dgridproducts'}',
                                    hourText: '{l s='Hour' js=1 mod='dgridproducts'}',
                                    minuteText: '{l s='Minute' js=1 mod='dgridproducts'}',
                                });
                        });
                    </script>

                    <div class="date_add">
                        <input class="datetimepicker-val float-left margin-right-lg fixed-width-lg id_data_add form-control"
                               type=""
                               name="date_add"
                               data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                               data-criterion="id_product"
                               data-field-table="product,product_shop"
                               data-date=" {$tr.date_add|escape:'quotes':'UTF-8'}"
                               value=" {$tr.date_add|escape:'quotes':'UTF-8'}">
                        <input class="datetimepicker float-left margin-right-lg fixed-width-lg form-control"
                               type=""
                               name="date_add"
                               data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                               data-criterion="id_product"
                               data-field-table="product,product_shop"
                               data-date=" {$tr.date_add|escape:'quotes':'UTF-8'}"
                               value=" {$tr.date_add|escape:'quotes':'UTF-8'}">
                    </div>
                {/if}

                {if $params.type == 'brand'}
                    <select name="id_manufacturer" class="id_brand custom-select" data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-field-table="{$params.table}">
                        <option value="0" data-id="0" name="brand">-</option>
                        {foreach $manufacturers as $key=>$item}
                            <option value="{$item['id_manufacturer']}"
                                    data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                                    name="brand"{if $item['id_manufacturer']|intval == $tr.brand|intval}selected="selected"{/if}>
                                {$item['name']|escape:'quotes':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}

                {if $params.type == 'visibility'}
                    <select name="visibility" class="visibility custom-select" data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-field-table="{$params.table}">
                        {foreach $visibility as $key=>$item}
                            <option value="{$item['value']}"
                                    data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                                    name="visibility" {if $item['value'] == $tr.visibility}selected="selected"{/if}>
                                {$item['name']|escape:'quotes':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                {/if}




                {if $params.type == 'tag'}
                    {foreach $languages as $language}
                  {if !isset($tr.tag_product[$language.id_lang])}
                      {assign var='id_land' value=''}
                      {else}
                    {assign var='id_land' value=','|implode: $tr.tag_product[$language.id_lang]}
                    {/if}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $default_language}style="display:none"{/if}>
                        <div class="col-lg-9">
                {/if}
                            {literal}
                                <script type="text/javascript">
                                    $().ready(function () {
                                        var input_id = '{/literal}{if isset($tr.id)}{$tr.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}{literal}';
                                        var lang_id = '{/literal}{$language.id_lang}{literal}';
                                        var id_product = '{/literal}{$tr.id}{literal}';
                                        $('#'+input_id).tagify2({delimiters: [13,44], id : input_id, data_ids: id_product, data_lang: lang_id, addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
                                        $({/literal}'#{$table}{literal}_form').submit( function() {
                                            $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
                                        });
                                    });
                                </script>
                            {/literal}

                                <input type="text2"
                                       id="{if isset($tr.id)}{$tr.id}_{$language.id_lang}{else}product_{$language.id_lang}{/if}"
                                       name="product_{$language.id_lang}"
                                       data="clear"
{*                                       class="{if isset($input.class)}{$input.class}{/if}{if $input.type == 'tags'} tagify{/if}"*}
                                       value="{if isset($id_land)}{$id_land|escape:'html':'UTF-8'}{/if}"/>
                    {if $languages|count > 1}
                        </div>
                        <div class="col-lg-2">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code}
                                <i class="icon-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=language}
                                    <li><a onclick="hideOtherLanguage2({$language.id_lang})" tabindex="-1">{$language.name}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                {/if}
{/foreach}
<script>
    function hideOtherLanguage2(id) {
        $('.translatable-field').hide();
        $('.lang-' + id).show();

        var id_old_language = id_language;
        id_language = id;

        if (id_old_language != id)
            changeEmployeeLanguage();
        $('#current_product').html($('#name_' + id_language).val());
    }
</script>
                {/if}

                {if $params.type == 'id_tax_rules_group'}
                    <select name="id_tax_rules_group" class="id_brand custom-select" data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-field-table="{$params.table}">
                        <option value="0">{l s='No Tax' mod='dgridproducts'}</option>
                        {foreach $tax_rules as $key=>$item}
                            <option value="{$item['id_tax_rules_group']}"
                                    data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                                    name="id_tax_rules_group"{if $item['id_tax_rules_group']|intval == $tr.id_tax_rules_group|intval}selected="selected"{/if}>
                                {$item['name']|escape:'quotes':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}
                {/if}
                {if $params.type == 'condition'}
                    <select name="condition" class="id_condition custom-select" data-id="{$tr.id|escape:'quotes':'UTF-8'}" data-field-table="{$params.table}">
                        {foreach ['new' => {l s='New' mod='dgridproducts'}, 'used' => {l s='Used' mod='dgridproducts'}, 'refurbished' => {l s='Refurbished' mod='dgridproducts'}] as $key=>$item}
                            <option value="{$key}"
                                    data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                                    name="condition"{if $key == $tr.condition} selected="selected"{/if}>
                                {$item|escape:'quotes':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}
                {/if}

                {if $params.type == 'loc'}
                {if !$tr.locale}
                <input class="hidden location custom-select"
                       type=""
                       name="location"
                       data-id="{$tr.id|escape:'quotes':'UTF-8'}"
                       data-criterion="id_product"
                       data-field-table="stock_available,product"
                       data-loc=" {$tr.location|escape:'quotes':'UTF-8'}"
                       value=" {$tr.location|escape:'quotes':'UTF-8'}">
                    {else}
                    <button class="com_location button btn btn-default" data-id="{$tr.id|escape:'quotes':'UTF-8'}">{l s='Combinations' mod='dgridproducts'}</button>

                {/if}
                {/if}

                    {if $ps_v < 1.6}</td>{/if}
                {/block}

                {block name="close_td"}
                    </td>
                {/block}
            {/foreach}
            {if empty($multishop_active)}
                {$multishop_active = 'false'}
                {/if}

            {if $multishop_active && $shop_link_type}
                <td title="{$tr.shop_name}">
                    {if isset($tr.shop_short_name)}
                        {$tr.shop_short_name}
                    {else}
                        {$tr.shop_name}
                    {/if}
                </td>
            {/if}


            {if $has_actions}
                <td class="text-right" style="white-space: nowrap;">

                    <a href="{$tr.url_product}" title="Edit" target="_blank" class="edit btn btn-default">
                        <i class="icon-pencil"></i>
                    </a>


                    {assign var='compiled_actions' value=array()}
                    {foreach $actions AS $key => $action}
                        {$compiled_actions[] = $tr.$action}
                    {/foreach}
                    {foreach $compiled_actions AS $key => $action}
                        {$action}
                    {/foreach}
                </td>
            {/if}




        </tr>
    {/foreach}
{else}
    <tr>
        <td class="list-empty" colspan="{count($fields_display)+1}">
            <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No records found' mod='dgridproducts'}
            </div>
        </td>
    </tr>
{/if}
</tbody>
