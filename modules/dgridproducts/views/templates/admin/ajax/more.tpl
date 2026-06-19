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
    var iso = '{$iso|addslashes|escape:'quotes':'UTF-8'}';
    var pathCSS = '{$smarty.const._THEME_CSS_DIR_|addslashes|escape:'quotes':'UTF-8'}';
    var ad = '{$ad|addslashes|escape:'quotes':'UTF-8'}';
    var id_product = {$product->id|intval}
        $(document).ready(function () {
            tinymce.remove();
            tinySetup({
                editor_selector: "autoload_rte"
            });
        });
    var tax_rate = {$tax_rate|floatval};
</script>
<div class="form_more col-lg-12 content_form p_{$ps_version|replace:'.':''|escape:'quotes':'UTF-8'}">
    <div class="col-lg-12">

        <div class="panel-footer">
            <button class="button btn btn-default btn-lg close_form_popup" href="#">
                {l s='Cancel' mod='dgridproducts'}
            </button>

            <button class="saveAdditionalSettingProduct btn btn-primary btn-lg pull-right">
                {l s='Save' mod='dgridproducts'}
            </button>
        </div>

        {*<h2 class="text-center">{l s='Additional settings product' mod='dgridproducts'}</h2>*}
        <h3>{l s='Information' mod='dgridproducts'}</h3>
        <input type="hidden" name="id_product" value="{$id_product|intval}"/>
        {if $pack_row.can_pack}
            {include file="./pack.tpl"}
        {/if}
        <div class="row">
            {if isset($display_multishop_checkboxes) && $display_multishop_checkboxes}
                {include file="./checkbox.tpl" only_checkbox="true" field="available_for_order" type="default"}
                {include file="./checkbox.tpl" only_checkbox="true" field="show_price" type="show_price"}
                {include file="./checkbox.tpl" only_checkbox="true" field="online_only" type="default"}
            {/if}
            <label class="control-label col-xs-12 col-lg-3" for="available_for_order">
                {l s='Options' mod='dgridproducts'}
            </label>
            <div class="col-xs-12 col-lg-9">
                <div>
                    <label class="control-label" for="available_for_order">
                        <input type="checkbox" name="available_for_order" id="available_for_order" value="1"
                               {if $product->available_for_order}checked="checked"{/if} >
                        {l s='Available for order' mod='dgridproducts'}</label>
                </div>

                <div>
                    <label class="control-label" for="show_price">
                        <input type="checkbox" name="show_price" id="show_price" value="1"
                               {if $product->show_price}checked="checked"{/if} {if $product->available_for_order}disabled="disabled"{/if} >
                        {l s='Show price' mod='dgridproducts'}</label>
                </div>

                <div>
                    <label class="control-label" for="online_only">
                        <input type="checkbox" name="online_only" id="online_only" value="1"
                               {if $product->online_only}checked="checked"{/if} >
                        {l s='Online only (not sold in your retail store)' mod='dgridproducts'}</label>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            {include file="./checkbox.tpl" field="condition" type="default"}
            <label class="control-label col-lg-3" for="condition">
                {l s='Condition' mod='dgridproducts'}
            </label>
            <div class="col-lg-3">
                <select class="custom-select fixed-width-xxl" name="condition" id="condition">
                    <option value="new"
                            {if $product->condition == 'new'}selected="selected"{/if} >{l s='New' mod='dgridproducts'}</option>
                    <option value="used"
                            {if $product->condition == 'used'}selected="selected"{/if} >{l s='Used' mod='dgridproducts'}</option>
                    <option value="refurbished"
                            {if $product->condition == 'refurbished'}selected="selected"{/if}>{l s='Refurbished' mod='dgridproducts'}</option>
                </select>
            </div>
        </div>
        <br>
        <div class="row">
            {include file="./checkbox.tpl" field="description_short" type="tinymce" multilang="true"}
            <label title="{l s='Appears in the product list(s), and at the top of the product page.' mod='dgridproducts'}" class="control-label col-lg-3" for="description_short_{$id_lang|escape:'quotes':'UTF-8'}">
                {l s='Short description' mod='dgridproducts'}
            </label>
            <div class="col-lg-9">
                {include
                file="./fields/{$smarty.const._PS_VERSION_|floatval}/textarea_lang.tpl"
                languages=$languages
                input_name='description_short'
                class="autoload_rte"
                input_value=$product->description_short
                max=$PS_PRODUCT_SHORT_DESC_LIMIT}
            </div>
        </div>
        <br>
        <div class="row">
            {include file="./checkbox.tpl" field="description" type="tinymce" multilang="true"}
            <label class="control-label col-lg-3" for="description_{$id_lang|escape:'quotes':'UTF-8'}" title="{l s='Appears in the body of the product page.' mod='dgridproducts'}">
                {l s='Description' mod='dgridproducts'}
            </label>
            <div class="col-lg-9">
                {include
                file="./fields/{$smarty.const._PS_VERSION_|floatval}/textarea_lang.tpl"
                languages=$languages input_name='description'
                class="autoload_rte"
                input_value=$product->description}
            </div>
        </div>
        <br>
        <!--       start     -->
        <div class="row form-group">
            {include file="./checkbox.tpl" field="friend_url" type="default" multilang="true"}
            <label class="control-label col-xs-12 col-lg-3 label-special"
                   for="friend_url_{$default_language|escape:'quotes':'UTF-8'}" title="{l s='Friendly URL:' mod='dgridproducts'} &#60;&#62;;&#61;#&#123;&#125;">
                {l s='Friendly URL' mod='dgridproducts'}
            </label>
            <div class="col-xs-12 col-lg-8">
                <div class="fixed-width-fluid">
                    {include file="./fields/{$smarty.const._PS_VERSION_|floatval}/input_text_lang.tpl"
                    languages=$languages
                    input_value=$product->link_rewrite
                    input_name='friend_url'}
                </div>
            </div>
        </div>
        <br>
        <!--             end   -->
        <div class="row">
            <div class="col-xs-12 col-lg-1"></div>
            <label class="control-label col-lg-2" for="tags_{$id_lang|escape:'quotes':'UTF-8'}" title="{l s='Each tag has to be followed by a comma. The following characters are forbidden: %s' mod='dgridproducts' sprintf='!&lt;;&gt;;?=+#&quot;&deg;{}_$%'}">
                {l s='Tags' mod='dgridproducts'}
            </label>
            <div class="col-xs-12 col-lg-9 ">
                <div class="row {if $ps_version == 1.5}translatable{/if}">
                    {foreach from=$languages item=language}
                    {literal}
                        <script type="text/javascript">
                            $().ready(function () {
                                window.input_id = '{/literal}tags_{literal}';
                                $('#' + window.input_id{/literal}+ '{$language.id_lang|escape:'quotes':'UTF-8'}'{literal}).tagify({
                                    delimiters: [13, 44],
                                    addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'
                                });
                            });
                        </script>
                    {/literal}
                        <div class="translatable-field lang-{$language.id_lang|escape:'quotes':'UTF-8'} {if $ps_version == 1.5}lang_{$language.id_lang|escape:'quotes':'UTF-8'}{/if}">
                            <div class="col-xs-9">
                                <input type="text" id="tags_{$language.id_lang|escape:'quotes':'UTF-8'}"
                                       class="tagify updateCurrentText"
                                       name="tags_{$language.id_lang|escape:'quotes':'UTF-8'}"
                                       value="{$product->getTags($language.id_lang, true)|htmlentitiesUTF8|escape:'quotes':'UTF-8'}"/>
                            </div>
                            {if $ps_version >= 1.6}
                                <div class="col-xs-2">
                                    <button type="button" class="btn btn-default dropdown-toggle"
                                            data-toggle="dropdown">
                                        {$language.iso_code|escape:'quotes':'UTF-8'}
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        {foreach from=$languages item=language}
                                            <li>
                                                <a href="javascript:hideOtherLanguage({$language.id_lang|escape:'quotes':'UTF-8'});">{$language.name|escape:'quotes':'UTF-8'}</a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <hr>
        <h3>{l s='Prices' mod='dgridproducts'}</h3>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Pre-tax wholesale price' mod='dgridproducts'}</label>
            <div class="col-lg-9 ">
                <div class="input-group fixed-width-xxl">
                    <span class="input-group-addon"> {$currency->sign|escape:'quotes':'UTF-8'}</span>
                    <input id="wholesale_price" name="wholesale_price" type="text"
                           value="{$product->wholesale_price|floatval}"
                           onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');">
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Tax rule' mod='dgridproducts'}</label>

            <div class="col-lg-9">
                <div class="fixed-width-xxl mr-1 float-left">
                    <select class="custom-select" name="id_tax_rules_group" {if $tax_exclude_taxe_option}disabled="disabled"{/if} >
                        <option value="0">{l s='No Tax' mod='dgridproducts'}</option>
                        {foreach from=$tax_rules_groups item=tax_rules_group}
                            <option value="{$tax_rules_group.id_tax_rules_group|escape:'htmlall':'UTF-8'}"
                                    {if $product->getIdTaxRulesGroup() == $tax_rules_group.id_tax_rules_group}selected="selected"{/if} >
                                {$tax_rules_group['name']|htmlentitiesUTF8|escape:'quotes':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="float-left">
                    <a target="_blank" class="btn btn-link confirm_leave"
                       href="{$link->getAdminLink('AdminTaxRulesGroup')|escape:'html':'UTF-8'}&addtax_rules_group&id_product={$product->id|intval}"{if $tax_exclude_taxe_option} disabled="disabled"{/if}>
                        <i class="icon-plus-sign"></i> {l s='Create new tax' mod='dgridproducts'} <i
                                class="icon-external-link-sign"></i>
                    </a>
                </div>
            </div>

        </div>
        <br>
        <div class="row">
            <label class="control-label col-lg-3">{if $ps_tax}{l s='Unit price (tax excl.)' mod='dgridproducts'}{else}{l s='Unit price' mod='dgridproducts'}{/if}</label>
            <div class="col-lg-9">
                <div class="input-group fixed-width-xxl float-left mr-1">
                    <span class="input-group-addon"> {$currency->sign|escape:'quotes':'UTF-8'}</span>
                    <input id="unit_price" name="unit_price" type="text" value="{$unit_price_with_tax|floatval}"
                           maxlength="27"
                           onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.'); updateUnitPriceWithTax(this);">
                </div>

                <div class="input-group fixed-width-xxl float-left">
                    <span class="input-group-addon">{l s='for' mod='dgridproducts'}</span>
                    <input id="unity" name="unity" type="text" value="{$product->unity|escape:'quotes':'UTF-8'}"
                           maxlength="10" onkeyup="if (isArrowKey(event)) return ;updateUnitySecond(this);"
                           onchange="updateUnitySecond(this);">
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-lg-3"><span
                        class="pull-right">{include file="./checkbox.tpl" field="condition" type="default"}</span></div>
            <div class="col-lg-9">
                <div class="checkbox">
                    <label class="control-label" for="on_sale">
                        <input type="checkbox" name="on_sale" id="on_sale" {if $product->on_sale}checked{/if} value="1">
                        {l s='Display the "on sale" icon on the product page, and in the text found within the product listing.' mod='dgridproducts'}
                    </label>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-lg-4 col-lg-offset-3">
                <div class="alert alert-warning">
                    <span>{l s='or' mod='dgridproducts'}
                        <span id="unit_price_with_tax">{$unit_price_with_tax|string_format:'%.6f'}</span> {$currency->sign|escape:'quotes':'UTF-8'}
                        {l s='for' mod='dgridproducts'} <span
                                id="unity_second">{$product->unity|escape:'quotes':'UTF-8'}</span> {if $ps_tax}{l s='(incl tax)' mod='dgridproducts'}{/if}
                    </span>
                </div>
            </div>
        </div>
        <hr/>

        <h3>{l s='Associations' mod='dgridproducts'}</h3>
        <div class="row">
            <label class="control-label col-lg-3" for="product_autocomplete_input" title="{l s='You can indicate existing products as accessories for this product.' mod='dgridproducts'}{l s='Start by typing the first letters of the product\'s name, then select the product from the drop-down list.' mod='dgridproducts'}{l s='Do not forget to save the product afterwards!' mod='dgridproducts'}">
                {l s='Accessories' mod='dgridproducts'}
            </label>
            <div class="col-lg-9">
                <input type="hidden" name="inputAccessories" id="inputAccessories"
                       value="{foreach from=$accessories item=accessory}{$accessory.id_product|escape:'quotes':'UTF-8'}-{/foreach}"/>
                <input type="hidden" name="nameAccessories" id="nameAccessories"
                       value="{foreach from=$accessories item=accessory}{$accessory.name|escape:'html':'UTF-8'}¤{/foreach}"/>
                <div id="ajax_choose_product">
                    <div class="input-group fixed-width-xxl">
                        <input type="text" id="product_autocomplete_input" name="product_autocomplete_input"/>
                        <span class="input-group-addon"><i class="icon-search"></i></span>
                    </div>
                </div>

                <div id="divAccessories">
                    {foreach from=$accessories item=accessory}
                        <div class="form-control-static">
                            <button type="button" class="btn btn-default delAccessory"
                                    name="{$accessory.id_product|intval}">
                                <i class="icon-remove text-danger"></i>
                            </button>
                            {$accessory.name|escape:'html':'UTF-8'}{if !empty($accessory.reference)}{$accessory.reference|escape:'quotes':'UTF-8'}{/if}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <label class="control-label col-lg-3" for="id_manufacturer">{l s='Manufacturer' mod='dgridproducts'}</label>
            <div class="col-lg-9">

                <div class="custom-select float-left mr-1">
                    <select class="custom-select" name="id_manufacturer" id="id_manufacturer">
                        <option value="0">- {l s='Choose (optional)' mod='dgridproducts'} -</option>
                        {if $product->id_manufacturer}
                            <option value="{$product->id_manufacturer|intval}"
                                    selected="selected">{$product->manufacturer_name|escape:'quotes':'UTF-8'}</option>
                        {/if}
                        <option disabled="disabled">-</option>
                    </select>
                </div>

                <a target="_blank" class="btn btn-link bt-icon confirm_leave" style="margin-bottom:0"
                   href="{$link->getAdminLink('AdminManufacturers')|escape:'html':'UTF-8'}&amp;addmanufacturer">
                    <i class="icon-plus-sign"></i> {l s='Create new manufacturer' mod='dgridproducts'} <i
                            class="icon-external-link-sign"></i>
                </a>
            </div>
        </div>
        <br>
        {if !$has_attribute}
            <div class="row">
                {include file="./checkbox.tpl" field="condition" type="default"}
                <label class="control-label col-lg-3" for="minimal_quantity">
                    {l s='Minimum quantity' mod='dgridproducts'}
                </label>
                <div class="col-lg-9">
                    <input class="fixed-width-xxl" type="text" id="minimal_quantity" maxlength="6"
                           value="{$product->minimal_quantity|default:1|escape:'quotes':'UTF-8'}"
                           name="minimal_quantity"/>
                </div>
            </div>
        {/if}
        <hr>

        <h3>{l s='Shipping' mod='dgridproducts'}</h3>

        <div id="product-shipping" class="panel product-tab clearfix">
            <input type="hidden" name="submitted_tabs[]" value="Shipping">

                <div class="row form-group ">
                    <label class="control-label col-lg-3" for="width">{l s='Package width' mod='dgridproducts'}</label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">{$short_distance|escape:'quotes':'UTF-8'}</span>
                            <input maxlength="14" id="width" name="width" type="text" value="{$product->width|floatval}"
                                   onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');">
                        </div>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="control-label col-lg-3" for="height">{l s='Package height' mod='dgridproducts'}</label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">{$short_distance|escape:'quotes':'UTF-8'}</span>
                            <input maxlength="14" id="height" name="height" type="text" value="{$product->height|floatval}"
                                   onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');">
                        </div>
                    </div>
                </div>

                <div class="row form-group ">
                    <label class="control-label col-lg-3" for="depth">{l s='Package depth' mod='dgridproducts'}</label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">{$short_distance|escape:'quotes':'UTF-8'}</span>
                            <input maxlength="14" id="depth" name="depth" type="text" value="{$product->depth|floatval}"
                                   onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');">
                        </div>
                    </div>
                </div>

                <div class="row form-group ">
                    <label class="control-label col-lg-3" for="weight">{l s='Package weight' mod='dgridproducts'}</label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">{$weight|escape:'quotes':'UTF-8'}</span>
                            <input maxlength="14" id="weight" name="weight" type="text" value="{$product->weight|floatval}"
                                   onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');">
                        </div>
                    </div>
                </div>
                <div class="row form-group ">
                    <label class="control-label col-lg-3" for="additional_shipping_cost" title="{l s='If a carrier has a tax, it will be added to the shipping fees.' mod='dgridproducts'}">
                        {l s='Additional shipping fees (for a single item)' mod='dgridproducts'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">{$currency->prefix|escape:'htmlall':'UTF-8'}{$currency->suffix|escape:'htmlall':'UTF-8'} {if $country_display_tax_label}({l s='tax excl.' mod='dgridproducts'}){/if}</span>
                            <input type="text" id="additional_shipping_cost" name="additional_shipping_cost"
                                   onchange="this.value = this.value.replace(/,/g, '.');"
                                   value="{$product->additional_shipping_cost|escape:'htmlall':'UTF-8'}"/>
                        </div>
                    </div>
                </div>
                <div class="row form-group float-left w-100">
                    <label class="control-label col-lg-3" for="availableCarriers">
                        {l s='Carriers' mod='dgridproducts'}
                    </label>
                    <div class="col-lg-9">
                        <div class="drag_option">
                            <select class="available_options" multiple="multiple">
                                {if is_array($carriers) && count($carriers)}
                                    {foreach from=$carriers item=carrier}
                                        {if in_array($carrier.id_reference, $selected_carriers)}{continue}{/if}
                                        <option value="{$carrier.id_reference|intval}">{$carrier.name|escape:'quotes':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            </select>
                            <div class="drag_option_control">
                                <button class="select_options" type="button"></button>
                                <button class="unselect_options" type="button"></button>
                            </div>
                            <select class="selected_options" name="carriers[]" multiple="multiple">
                                {if is_array($carriers) && count($carriers)}
                                    {foreach from=$carriers item=carrier}
                                        {if !in_array($carrier.id_reference, $selected_carriers)}{continue}{/if}
                                        <option value="{$carrier.id_reference|intval}">{$carrier.name|escape:'quotes':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                        <script>
                            $('.drag_option').dragOption();
                        </script>
                    </div>
                </div>


        </div>
        {if $ps_stock_management}
            <h3>{l s='Availability settings' mod='dgridproducts'}</h3>
            <div class="row form-group">
                {include file="./checkbox.tpl" field="available_now" type="default" multilang="true"}
                <label class="control-label col-xs-12 col-lg-3 label-special"
                       for="available_now_{$default_language|escape:'quotes':'UTF-8'}" title="{l s='Forbidden characters:' mod='dgridproducts'} &#60;&#62;;&#61;#&#123;&#125;">
                    {l s='Displayed text when in-stock' mod='dgridproducts'}
                </label>
                <div class="col-xs-12 col-lg-9">
                    <div class="fixed-width-xxl">
                        {include file="./fields/{$smarty.const._PS_VERSION_|floatval}/input_text_lang.tpl"
                        languages=$languages
                        input_value=$product->available_now
                        input_name='available_now'}
                    </div>
                </div>
            </div>
            <div class="row ">
                {include file="./checkbox.tpl" field="available_later" type="default" multilang="true"}
                <label class="control-label col-lg-3 label-special"
                       for="available_later_{$default_language|escape:'quotes':'UTF-8'}" title="{l s='If empty, the message "in stock" will be displayed.' mod='dgridproducts'} {l s='Forbidden characters:' mod='dgridproducts'} &#60;&#62;;&#61;#&#123;&#125;">
                    {l s='Displayed text when backordering is allowed' mod='dgridproducts'}
                </label>
                <div class="col-lg-9">
                    <div class="fixed-width-xxl">
                        {include file="./fields/{$smarty.const._PS_VERSION_|floatval}/input_text_lang.tpl"
                        languages=$languages
                        input_value=$product->available_later
                        input_name='available_later'}
                    </div>
                </div>
            </div>
            {if !$countAttributes}
                <div class="row">
                    <div class="col-lg-1"><span
                                class="pull-right">{include file="./checkbox.tpl" field="available_date" type="default"}</span>
                    </div>
                    <label class="control-label col-lg-2" for="available_date">
                        {l s='Available date' mod='dgridproducts'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <input id="available_date" name="available_date"
                                   value="{$product->available_date|escape:'quotes':'UTF-8'}" class="datepicker"
                                   type="text"/>
                            <div class="input-group-addon">
                                <i class="icon-calendar-empty"></i>
                            </div>

                        </div>
                        <p class="help-block">{l s='The next date of availability for this product when it is out of stock.' mod='dgridproducts'}</p>
                    </div>
                </div>
            {/if}
            <div class="row">
                <div class="col-lg-3"><span class="pull-right"></span></div>
                <div class="col-lg-9 pull-right">
                    <div class="checkbox">
                        <label for="show_condition" class="control-label" >
                            <input type="checkbox" name="show_condition" id="show_condition" value="1" {if
                            $product->show_condition}checked="checked"{/if} >{l s='Show condition' mod='dgridproducts'}
                        </label>
                    </div>
                </div>
            </div>
        {/if}
        <hr>
        {if Configuration::get('PS_CUSTOMIZATION_FEATURE_ACTIVE')}
            <div id="customization">
                {include file="./customization.tpl"}
            </div>
        {/if}
        <hr>
        {include file="./suppliers.tpl"}
        {if Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')}
            <hr>
            <h3>{l s='Advanced quantities' mod='dgridproducts'}</h3>
            <div class="row">
                <div class="form-group" {if $product->is_virtual}style="display:none;"{/if}
                     class="row stockForVirtualProduct">
                    <div class="col-lg-9 col-lg-offset-3">
                        <p class="checkbox">
                            <label for="advanced_stock_management">
                                <input type="checkbox" id="advanced_stock_management" name="advanced_stock_management"
                                       class="advanced_stock_management"
                                        {if $product->advanced_stock_management == 1}
                                            value="1" checked="checked"
                                        {else}
                                            value="0"
                                        {/if}
                                />
                                {l s='I want to use the advanced stock management system for this product.'  mod='dgridproducts'}
                            </label>
                        </p>
                        {if $stock_management_active == 0 && !$product->cache_is_pack}
                            <p class="help-block"><i
                                        class="icon-warning-sign"></i>&nbsp;{l s='This requires you to enable advanced stock management.'  mod='dgridproducts'}
                            </p>
                        {elseif $product->cache_is_pack}
                            <p class="help-block">{l s='When enabling advanced stock management for a pack, please make sure it is also enabled for its product(s) – if you choose to decrement product quantities.'  mod='dgridproducts'}</p>
                        {/if}
                    </div>
                </div>

                <div {if $product->is_virtual}style="display:none;"{/if} class="form-group stockForVirtualProduct">
                    <label class="control-label col-lg-3"
                           for="depends_on_stock_1">{l s='Available stock management'  mod='dgridproducts'}</label>
                    <div class="col-lg-9">
                        <p class="radio">
                            <label for="depends_on_stock_1">
                                <input type="radio" id="depends_on_stock_1" name="depends_on_stock"
                                       class="depends_on_stock" value="1"
                                        {if $product->depends_on_stock == 1 && $stock_management_active == 1}
                                            checked="checked"
                                        {/if}
                                        {if $stock_management_active == 0 || $product->advanced_stock_management == 0}
                                            disabled="disabled"
                                        {/if}
                                />
                                {l s='The available quantities for the current product and its combinations are based on the stock in your warehouse (using the advanced stock management system). '  mod='dgridproducts'}
                                {if ($stock_management_active == 0 || $product->advanced_stock_management == 0) && !$product->cache_is_pack} &nbsp;-&nbsp;{l s='This requires you to enable advanced stock management globally or for this product.'  mod='dgridproducts'}
                                {/if}
                            </label>
                        </p>
                        {if $product->cache_is_pack}
                            <p class="help-block">
                                {l s='You cannot use advanced stock management for this pack if'  mod='dgridproducts'}
                                <br/>
                                {l s='- advanced stock management is not enabled for these products'  mod='dgridproducts'}
                                <br/>
                                {l s='- you have chosen to decrement products quantities.'  mod='dgridproducts'}
                            </p>
                        {/if}
                        <p class="radio">
                            <label for="depends_on_stock_0" for="depends_on_stock_0">
                                <input type="radio" id="depends_on_stock_0" name="depends_on_stock"
                                       class="depends_on_stock" value="0"
                                        {if $product->depends_on_stock == 0 || $stock_management_active == 0}
                                            checked="checked"
                                        {/if}
                                />
                                {l s='I want to specify available quantities manually.'  mod='dgridproducts'}
                            </label>
                        </p>
                    </div>
                </div>
            </div>
            <br/>
        {/if}
        <hr>
        <h3>{l s='Quantities' mod='dgridproducts'}</h3>
        <div class="row">
            <div id="when_out_of_stock" class="form-group">
                <label class="control-label col-xs-3">{l s='When out of stock'  mod='dgridproducts'}</label>
                <div class="col-xs-9">
                    <div class="radio clearfix">
                        <label class="control-label" id="label_out_of_stock_1" for="out_of_stock_1">
                            <input type="radio" id="out_of_stock_1" name="out_of_stock" checked="checked" value="0"
                                   class="out_of_stock" {if $product->out_of_stock == 0} checked="checked" {/if}>
                            {l s='Deny orders'  mod='dgridproducts'}
                        </label>
                    </div>
                    <div class="radio clearfix">
                        <label class="control-label" id="label_out_of_stock_2" for="out_of_stock_2">
                            <input type="radio" id="out_of_stock_2" name="out_of_stock" value="1"
                                   class="out_of_stock" {if $product->out_of_stock == 1} checked="checked" {/if}>
                            {l s='Allow orders'  mod='dgridproducts'}
                        </label>
                    </div>
                    <div class="radio clearfix">
                        <label class="control-label" id="label_out_of_stock_3" for="out_of_stock_3">
                            <input type="radio" id="out_of_stock_3" name="out_of_stock" value="2"
                                   class="out_of_stock" {if $product->out_of_stock == 2} checked="checked" {/if}>
                            {l s='Default'  mod='dgridproducts'}:
                            {if $order_out_of_stock == 1}
                                {l s='Allow orders'  mod='dgridproducts'}
                            {else}
                                {l s='Deny orders'  mod='dgridproducts'}
                            {/if}
                            <a class="confirm_leave"
                               href="index.php?tab=AdminPPreferences&amp;token={$token_preferences}" target="_blank">
                                {l s='as set in the Products Preferences page'  mod='dgridproducts'}
                            </a>
                        </label>
                    </div>
                    {if $countAttributes == false}
                        <div class="radio clearfix">
                            <label class="control-label" id="low_stock_threshold" for="low_stock_threshold">
                                <input type="radio" id="low_stock_threshold" name="out_of_stock" value="2"
                                       class="out_of_stock margin-top" {if $product->out_of_stock == 2} checked="checked" {/if}>
                                <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="{$product->low_stock_threshold}"
                                       class="low_stock_threshold float-left margin-right">
                                <span class="confirm_leave float-left margin-top">{l s='Low stock level'  mod='dgridproducts'}</span>
                            </label>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        {if $ps_version >= 1.7}
            <hr>
            <h3>{l s='Seo' mod='dgridproducts'}</h3>
            <!-- redirect -->
            <div class="row form-group">
                <label class="col-lg-3 control-label form-control-label">
                    {l s='Redirection when offline' mod='dgridproducts'}
                </label>
                <div class="col-lg-9">
                    <select id="redirect" class="custom-select fixed-width-xxl">
                        <option>-</option>
                        <option value="category"
                                data-type="301-category"
                                {if $item_type[0]['redirect_type'] == "301-category"}selected{/if}
                                class="category_redirect">{l s='Permanent redirection to a category (301)' mod='dgridproducts'}
                        </option>
                        <option value="category"
                                data-type="302-category"
                                {if $item_type[0]['redirect_type'] == "302-category"}selected{/if}
                                class="category_redirect">{l s='Temporary redirection to a category (302)' mod='dgridproducts'}
                        </option>
                        <option value="product"
                                data-type="301-product"
                                {if $item_type[0]['redirect_type'] == "301-product"}selected{/if}
                                class="product_redirect">{l s='Permanent redirection to a product (301)' mod='dgridproducts'}
                        </option>
                        <option value="product"
                                data-type="302-product"
                                {if $item_type[0]['redirect_type'] == "302-product"}selected{/if}
                                class="product_redirect">{l s='Temporary redirection to a product (302)' mod='dgridproducts'}
                        </option>
                        <option value="404"
                                {if $item_type[0]['redirect_type'] == "404"}selected{/if}
                                data-type="404">{l s='No redirection (404)' mod='dgridproducts'}
                        </option>
                    </select>
                </div>
            </div>

            <div class="row form-group">
                <label class="col-lg-3 control-label form-control-label">
                    {l s='Target category' mod='dgridproducts'}
                </label>
                <div class="col-lg-9">
                    <select class="custom-select fixed-width-xxl mr-1 d-block" name="redirect_category" id="id_category">
                        {foreach from=$category_mini key=k item=cat}
                            <option value="{$cat['id_category']|intval}"
                                    {if  ($item_type[0]['redirect_type'] == "301-category" || $item_type[0]['redirect_type'] ==
                                    "302-category") &&
                                    ($item_type[0]['id_type_redirected']==$cat['id_category'])}selected{/if}
                            >{$cat['name']|escape:'quotes':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <small class="form-text text-muted text-right typeahead-hint">
                        {l s='If no category is selected, the Main Category is used' mod='dgridproducts'}
                    </small>
                </div>
            </div>

            <div class="row redirect product">

                <label class="col-lg-3 control-label form-control-label">{l s='Target product' mod='dgridproducts'}</label>

                <div class="block_redirect col-md-9">
                    <div class=" " id="id-product-redirected">
                        <select class="custom-select fixed-width-xxl d-block" name="redirect_product" id="id_product">
                            <option value=""></option>
                            {foreach from=$product_mini key=p item=pro}
                                <option value="{$pro['id_product']|intval}"
                                        {if  ($item_type[0]['redirect_type'] == "301-product" || $item_type[0]['redirect_type'] ==
                                        "302-product") && ($item_type[0]['id_type_redirected']==$pro['id_product'])}selected{/if}>
                                    {$pro['name']|escape:'quotes':'UTF-8'}</option>
                            {/foreach}
                        </select>
                        <small class="form-text text-muted text-right typeahead-hint">
                            {l s='If no product is selected, the Main Product is used' mod='dgridproducts'}
                        </small>
                    </div>
                </div>
            </div>
            <!-- redirect -->

            <!-- Delivery time-->
            <hr>
            <h3>{l s='Delivery time' mod='dgridproducts'}</h3>
            <!--- блок радио кнопок          ----->
            <div class="row form-group">
                <label class="col-lg-3 control-label form-control-label">
                    {l s='Delivery time of in-stock products' mod='dgridproducts'}
                </label>

                <div class="col-lg-9">
                    <div class="translations tabbable fixed-width-xxl" id="form_step4_delivery_in_stock">
                        <div class="translationsFields tab-content">
                            {foreach $languages as $iso_lang}
                                {if $id_lang == $iso_lang['id_lang']}
                                    <input type="hidden" name="id_lang_delivery_time"
                                           value="{$iso_lang['id_lang']}">
                                    <div data-locale="{$iso_lang['iso_code']}"
                                         class="translation-label-{$iso_lang['iso_code']}">
                                        <input type="text" id="form_step4_delivery_in_stock_1"
                                               name="delivery_in_stock" placeholder="Delivered within 3-4 days"
                                               class="form-control"
                                               value="{$product->delivery_in_stock[$iso_lang['id_lang']]}">
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                    <small class="form-text text-muted text-right typeahead-hint">
                        {l s='Leave empty to disable.' mod='dgridproducts'}
                    </small>
                </div>
            </div>

            <div class="row form-group">
                <label class="col-lg-3 control-label form-control-label">
                    {l s='Delivery time of out-of-stock products with allowed orders' mod='dgridproducts'}
                </label>

                <div class="col-lg-9">
                    <div class="translations tabbable fixed-width-xxl" id="form_step4_delivery_out_stock">
                        <div class="translationsFields tab-content">
                            {foreach $languages as $iso_lang}
                                {if $id_lang == $iso_lang['id_lang']}
                                    <div data-locale="{$iso_lang['iso_code']}"
                                         class="translation-label-{$iso_lang['iso_code']}">
                                        <input type="text" id="form_step4_delivery_out_stock"
                                               name="delivery_out_stock" placeholder="Delivered within 3-4 days"
                                               class="form-control"
                                               value="{$product->delivery_out_stock[$iso_lang['id_lang']]}">
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                    <small class="form-text text-muted text-right typeahead-hint">
                        {l s='Leave empty to disable.' mod='dgridproducts'}
                    </small>
                </div>
            </div>
            <!--- блок input                 ----->

            <!--- конец блока Delivery time  ----->
        {/if}

        <div class="panel-footer">
            <button class="button btn btn-default btn-lg close_form_popup" href="#">
                {l s='Cancel' mod='dgridproducts'}
            </button>

            <button class="saveAdditionalSettingProduct btn btn-primary btn-lg pull-right">
                {l s='Save' mod='dgridproducts'}
            </button>
        </div>
    </div>
</div>
    <script>
        // redirect
        $('#id_category').select2({
            placeholder: 'Select an option',
            width: '100%'
        }).on('select2:opening', function (e) {
            $(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', 'My Placeholder')
        })

        $('#id_product').select2({
            placeholder: 'Select an option',
            width: '100%'
        }).on('select2:opening', function (e) {
            $(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', 'My Placeholder')
        })
        $('.redirect').hide();
        $('#redirect').change(function () {
            $('.redirect').hide();
            if ($(this).val() != '404') {
                $('.' + $(this).val()).show();
            }
        });
        var sel_type = $("#redirect option:selected").val();
        if (sel_type == '') {
            $('.redirect').hide();
        }
        if (sel_type == "category") {
            $("#id-category-redirected").show();
        }
        if (sel_type == "product") {
            $("#id-product-redirected").show();
        }
        // end redirect

        var module_dir = '{$smarty.const._MODULE_DIR_|escape:'quotes':'UTF-8'}';
        var id_language = {$defaultFormLanguage|intval};
        var languages = new Array();
        var vat_number = {if $vat_number}1{else}0{/if};
        // Multilang field setup must happen before document is ready so that calls to displayFlags() to avoid
        // precedence conflicts with other document.ready() blocks
        {foreach $languages as $k => $language}
        languages[{$k|escape:'quotes':'UTF-8'}] = {
            id_lang: {$language.id_lang|escape:'quotes':'UTF-8'},
            iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
            name: '{$language.name|escape:'quotes':'UTF-8'}',
            is_default: '{$language.is_default|escape:'quotes':'UTF-8'}'
        };
        {/foreach}
        // we need allowEmployeeFormLang var in ajax request
        allowEmployeeFormLang = {$allowEmployeeFormLang|intval};
        displayFlags(languages, id_language, allowEmployeeFormLang);
        {if $ps_version >= 1.6}
        hideOtherLanguage(id_language);
        {else}
        changeFormLanguage(id_language, iso);
        {/if}
        $('[name=available_for_order]').change(function () {
            if ($(this).is(':checked'))
                $('[name=show_price]').attr({
                    disabled: 'disabled',
                    checked: 'checked'
                });
            else
                $('[name=show_price]').removeAttr('disabled');
        });

        function initAccessoriesAutocomplete() {
            $('#product_autocomplete_input')
                .autocomplete('ajax_products_list.php', {
                    source: '',
                    minChars: 1,
                    autoFill: true,
                    max: 20,
                    matchContains: true,
                    mustMatch: true,
                    scroll: false,
                    cacheLength: 0,
                    formatItem: function (item) {
                        return item[1] + ' - ' + item[0];
                    }
                }).result(addAccessory);

            $('#product_autocomplete_input').setOptions({
                extraParams: {
                    excludeIds: getAccessoriesIds()
                }
            });
        }
        {if $ps_version < 1.6}
        function getAccessoriesIds() {
            if ($('#inputAccessories').val() === undefined)
                return '';
            return $('#inputAccessories').val().replace(/\-/g, ',');
        }
        {else}
        function getAccessoriesIds() {
            if ($('#inputAccessories').val() === undefined)
                return id_product;
            return id_product + ',' + $('#inputAccessories').val().replace(/\-/g, ',');
        }
        {/if}

        function addAccessory(event, data, formatted) {
            if (data == null)
                return false;
            var productId = data[1];
            var productName = data[0];

            var $divAccessories = $('#divAccessories');
            var $inputAccessories = $('#inputAccessories');
            var $nameAccessories = $('#nameAccessories');

            /* delete product from select + add product line to the div, input_name, input_ids elements */
            $divAccessories.html($divAccessories.html() + '<div class="form-control-static"><button type="button" class="delAccessory btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + productName + '</div>');
            $nameAccessories.val($nameAccessories.val() + productName + '¤');
            $inputAccessories.val($inputAccessories.val() + productId + '-');
            $('#product_autocomplete_input').val('');
            $('#product_autocomplete_input').setOptions({
                extraParams: {literal}{excludeIds: getAccessoriesIds()}{/literal}
            });
        };

        function delAccessory(id) {
            var div = getE('divAccessories');
            var input = getE('inputAccessories');
            var name = getE('nameAccessories');

            // Cut hidden fields in array
            var inputCut = input.value.split('-');
            var nameCut = name.value.split('¤');

            if (inputCut.length != nameCut.length)
                return jAlert('Bad size');

            // Reset all hidden fields
            input.value = '';
            name.value = '';
            div.innerHTML = '';
            for (i in inputCut) {
                // If empty, error, next
                if (!inputCut[i] || !nameCut[i])
                    continue;

                // Add to hidden fields no selected products OR add to select field selected product
                if (inputCut[i] != id) {
                    input.value += inputCut[i] + '-';
                    name.value += nameCut[i] + '¤';
                    div.innerHTML += '<div class="form-control-static"><button type="button" class="delAccessory btn btn-default" name="' + inputCut[i] + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
                }
                else
                    $('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
            }

            $('#product_autocomplete_input').setOptions({
                extraParams: {literal}{excludeIds: getAccessoriesIds()}{/literal}
            });
        };

        function getManufacturers() {
            $.ajax({
                url: 'ajax-tab.php',
                cache: false,
                dataType: 'json',
                data: {
                    ajaxProductManufacturers: "1",
                    ajax: '1',
                    token: "{Tools::getAdminTokenLite('AdminProducts')|escape:'quotes':'UTF-8'}",
                    controller: 'AdminProducts',
                    action: 'productManufacturers'
                },
                success: function (j) {
                    var options = '';
                    if (j) {
                        for (var i = 0; i < j.length; i++) {
                            options += '<option ' + ({$product->id_manufacturer|intval} == j[i].optionValue ? ' selected="selected" ' : ''
                        )
                            +' value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                        }
                    }
                    $('select#id_manufacturer').chosen({literal}{width: '250px'}{/literal}).append(options).trigger("chosen:updated").trigger("liszt:updated");
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $("select#id_manufacturer").replaceWith("<p id=\"id_manufacturer\">[TECHNICAL ERROR] ajaxProductManufacturers: " + textStatus + "</p>");
                }
            });
        };

        initAccessoriesAutocomplete();
        getManufacturers();
        $('#divAccessories').delegate('.delAccessory', 'click', function () {
            delAccessory($(this).attr('name'));
        });
        $('#available_date').datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });
    </script>