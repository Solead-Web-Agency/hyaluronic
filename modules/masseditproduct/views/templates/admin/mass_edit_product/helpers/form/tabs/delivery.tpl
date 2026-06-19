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
* @copyright 2012-2020 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{extends file="../tab_layout.tpl"}

{block name="form"}
    <!-- delivery tab -->
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="width" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Package width' mod='masseditproduct'}:</label>
            <input class="fixed-width-xs form-control" maxlength="14" name="width" value="0" type="text" onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');"/>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="height" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Package height' mod='masseditproduct'}:</label>
            <input class="fixed-width-xs form-control" maxlength="14" name="height" value="0" type="text" onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');"/>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="depth" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Package depth' mod='masseditproduct'}:</label>
            <input class="fixed-width-xs form-control" maxlength="14" name="depth" value="0" type="text" onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');"/>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="weight" class="disable_option">
        <div class="col-xs-12">

            <div class="float-left">
                <label class="control-label apply_change_tab10 margin-right">{l s='Package weight' mod='masseditproduct'}:</label>
                <input class="fixed-width-xs margin-right form-control" maxlength="14" name="weight" value="0" type="text"
                       onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.');" />
            </div>

            <div class="float-left">
                <span class="white-space-nowrap">
                    <label class="control-label margin-right float-left">{l s='apply change for' mod='masseditproduct'}:</label>
                    <span class="ps-switch prestashop-switch fixed-width-xl switch-product-combination float-left">
                        {foreach [0,1] as $value}
                            <input type="radio" name="weight_change_for_combination" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="weight_change_for_product" {else} id="weight_change_for_combination" {/if}
                                    {if $value == 0} checked="checked" {/if}
                            />
                            <label {if $value == 1} for="weight_change_for_product" {else} for="weight_change_for_combination" {/if}>
                                {if $value == 0}{l s='Product' mod='masseditproduct'}{else}{l s='Combination' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                        <a class="slide-button"></a>
                    </span>
                </span>
            </div>

        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="additional_shipping_cost" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Additional shipping fees (for a single item)' mod='masseditproduct'}:</label>
            <input class="fixed-width-xs form-control" name="additional_shipping_cost" value="0" type="text" onchange="this.value = this.value.replace(/,/g, '.');"/>
        </div>
    </div>
    {if version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '>=')}
        <div class="row form-group">
            <input checked type="checkbox" name="disabled[]" value="shipping_time" class="disable_option">
            <div class="col-sm-12 clearfix">
                <label class="control-label margin-right">{l s='Delivery time' mod='masseditproduct'}:</label>
                <p class="radio margin-right">
                    <label class="control-label" for="additional_delivery_times_0">
                        <input type="radio" id="additional_delivery_times_0" name="additional_delivery_times" class="additional_delivery_times" value="0" checked="checked">{l s='None' mod='masseditproduct'}
                    </label>
                </p>
                <p class="radio margin-right">
                    <label class="control-label" for="additional_delivery_times_1">
                        <input type="radio" id="additional_delivery_times_1" name="additional_delivery_times" class="additional_delivery_times" value="1">
                        {l s='Default delivery time' mod='masseditproduct'}
                        {*<a href="{$link_preferences}" class="btn sensitive px-0" target="_blank">*}
                            {*<i style="height: 1em;" class="material-icons">{l s='open_in_new' mod='masseditproduct'}</i>*}
                            {*<span>{l s='Edit' mod='masseditproduct'}</span>*}
                        {*</a>*}
                    </label>
                </p>
                <p class="radio">
                    <label class="control-label" for="additional_delivery_times_2">
                        <input type="radio" id="additional_delivery_times_2" name="additional_delivery_times" class="additional_delivery_times" value="2">{l s='Specific delivery time to this product' mod='masseditproduct'}
                    </label>
                </p>
            </div>
        </div>
        <div class="row form-group">
            <input checked type="checkbox" name="disabled[]" value="shipping_time" class="disable_option">
            <div class="col-sm-12 clearfix">
                <label class="control-label margin-right">{l s='Delivery time of in-stock products:' mod='masseditproduct'}</label>
                {foreach $languages as $language}
                    {if $languages|count > 1}
                        <div class="translatable-field lang-{$language.id_lang|no_escape}" {if $language.id_lang != $default_form_language}style="display:none"{/if}>
                    {/if}
                    <input type="text"
                           id="delivery_in_stock_{$language.id_lang|no_escape}"
                           name="delivery_in_stock_{$language.id_lang|no_escape}"
                           class="fixed-width-xl form-control"
                           value=""
                           onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();"/>
                    {if $languages|count > 1}
                        <span class="btn-languages">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|no_escape}
                                <i class="icon-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=language}
                                    <li><a href="javascript:hideOtherLanguage({$language.id_lang|no_escape});" tabindex="-1">{$language.name|no_escape}</a></li>
                                {/foreach}
                            </ul>
                        </span>

                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
        <div class="row form-group">
            <input checked type="checkbox" name="disabled[]" value="shipping_time" class="disable_option">
            <div class="col-sm-12 clearfix">

                <label class="control-label margin-right">{l s='Delivery time of out-of-stock products with allowed orders:' mod='masseditproduct'}</label>
                {foreach $languages as $language}
                    {if $languages|count > 1}
                        <div class="translatable-field lang-{$language.id_lang|no_escape}" {if $language.id_lang != $default_form_language}style="display:none"{/if}>
                    {/if}
                    <input type="text"
                           id="delivery_out_stock_{$language.id_lang|no_escape}"
                           name="delivery_out_stock_{$language.id_lang|no_escape}"
                           class="fixed-width-xl form-control"
                           value=""
                           onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();"/>
                    {if $languages|count > 1}
                        <span class="btn-languages">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|no_escape}
                                <i class="icon-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=language}
                                    <li><a href="javascript:hideOtherLanguage({$language.id_lang|no_escape});" tabindex="-1">{$language.name|no_escape}</a></li>
                                {/foreach}
                            </ul>
                        </span>
                        </div>
                    {/if}
                {/foreach}

            </div>
        </div>
        </div>
    {/if}
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="id_carrier" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Available carriers' mod='masseditproduct'}:</label>
            <ul class="available_carrier">
                {if is_array($carriers) && count($carriers)}
                    {foreach from=$carriers item=carrier}
                        <li class="margin-right">
                            <label class="control-label">
                                <input type="checkbox" name="id_carrier[{$carrier.id_reference|intval}]" value="{$carrier.id_reference|intval}"> {$carrier.name|escape:'htmlall':'UTF-8'}
                            </label>
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right float-left">{l s='Delete install carrier' mod='masseditproduct'}:</label>
            <span class="ps-switch prestashop-switch fixed-width-xl switch-product-combination float-left">
                        {foreach [1,0] as $value}
                            <input type="radio" name="on_delete" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="on_delete"  {else} id="off_delete" {/if}
                                    {if $value == 0} checked="checked" {/if}/>
                            <label {if $value == 1}for="on_delete"   {else} for="off_delete"{/if}>
                                {if $value == 0}{l s='Off' mod='masseditproduct'}{else}{l s='On' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                <a class="slide-button"></a>
            </span>
        </div>
    </div>
{/block}