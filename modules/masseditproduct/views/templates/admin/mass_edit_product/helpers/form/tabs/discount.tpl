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
    <!-- discount tab -->
    <div class="row disabled_option_stage form-group">
        <input checked="" type="checkbox" name="disabled[]" value="specific_price" class="disable_option">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <label class="control-label">{l s='Action' mod='masseditproduct'}:</label>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    {*<label class="control-label apply_change_tab8 margin-right">{l s='Action' mod='masseditproduct'}:</label>*}
                    {*<span class="switch prestashop-switch fixed-width-xl margin-right">*}
                    {*{foreach [0,1] as $value}*}
                    {*<input type="radio" name="action_for_sp" value="{$value|escape:'quotes':'UTF-8'}"*}
                    {*{if $value == 1} id="change_for_sp_add" {else} id="change_for_sp_delete" {/if}*}
                    {*{if $value == 0} checked="checked" {/if} />*}
                    {*<label {if $value == 1} for="change_for_sp_add" {else} for="change_for_sp_delete" {/if}>*}
                    {*{if $value == 1}{l s='Delete' mod='masseditproduct'}{else}{l s='Add' mod='masseditproduct'}{/if}*}
                    {*</label>*}
                    {*{/foreach}*}
                    {*<a class="slide-button btn"></a>*}
                    {*</span>*}
                    <div class="btn-group btn-group-radio margin-right float-left">
                        <label for="change_for_sp_add" id="off_menu">
                            <input type="radio" checked name="action_for_sp" value="0" id="change_for_sp_add"/>
                            <span class="">{l s='Add' mod='masseditproduct'}</span>
                        </label>
                        <label for="change_for_sp_delete" id="off_menus">
                            <input type="radio" name="action_for_sp" value="1" id="change_for_sp_delete"/>
                            <span class="">{l s='Delete' mod='masseditproduct'}</span>
                        </label>
                        <label for="change_for_sp_edit" id="trigger">
                            <input type="radio" name="action_for_sp" value="2" id="change_for_sp_edit"/>
                            <span class="">{l s='Edit' mod='masseditproduct'}</span>
                        </label>
                    </div>
                    <label class="control-label apply_change_tab8 margin-right float-left">{l s='Apply change for' mod='masseditproduct'}:</label>
                    <span class="ps-switch prestashop-switch fixed-width-xl switch-product-combination float-left">
                        {foreach [0,1] as $value}
                            <input type="radio" name="change_for_sp" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="change_for_sp_product" {else} id="change_for_sp_combination" {/if}
                                    {if $value == 0} checked="checked" {/if} />
                            <label {if $value == 1} for="change_for_sp_product" {else} for="change_for_sp_combination" {/if}>
                                {if $value == 1}{l s='Combination' mod='masseditproduct'}{else}{l s='Product' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                        <a class="slide-button"></a>
                    </span>
                </div>
            </div>
            <hr />

            <div class="search-block" style="display: none">
                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label">{l s='Search' mod='masseditproduct'}:</label>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">
                        <label class="control-label apply_change2 margin-right">{l s='For' mod='masseditproduct'}:</label>
                        <select name="search_id_currency" class="custom-select fixed-width-lg margin-right">
                            <option value="0">{l s='All currencies' mod='masseditproduct'}</option>
                            {if is_array($currencies) && count($currencies)}
                                {foreach from=$currencies item=currency}
                                    <option value="{$currency.id_currency|intval}">{$currency.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <select name="search_id_country" class="custom-select fixed-width-lg margin-right">
                            <option value="0">{l s='All countries' mod='masseditproduct'}</option>
                            {if is_array($countries) && count($countries)}
                                {foreach from=$countries item=country}
                                    <option value="{$country.id_country|intval}">{$country.country|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <select name="search_id_group" class="custom-select fixed-width-lg">
                            <option value="0">{l s='All groups' mod='masseditproduct'}</option>
                            {if is_array($groups) && count($groups)}
                                {foreach from=$groups item=group}
                                    <option value="{$group.id_group|intval}">{$group.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">

                        <span class="white-space-nowrap">
                            <label class="control-label margin-right">{l s='From' mod='masseditproduct'}:</label>
                            <input class="datepicker fixed-width-lg margin-right form-control" name="search_from" type="text"/>
                        </span>
                        <span class="white-space-nowrap">
                            <label class="control-label margin-right">{l s='To' mod='masseditproduct'}:</label>
                            <input class="datepicker fixed-width-lg form-control" name="search_to" type="text"/>
                        </span>

                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-sm-12">
                        <label class="control-label margin-right">{l s='Begin from quantity' mod='masseditproduct'}:</label>
                        <input name="search_from_quantity" class="fixed-width-xs margin-right form-control" value="1" type="text"/>
                    </div>
                </div>
                <hr />
            </div>

            <div class="edit-block">
                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label">{l s='Edit' mod='masseditproduct'}:</label>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">
                        <label class="control-label apply_change2 margin-right">{l s='For' mod='masseditproduct'}:</label>
                        <select name="sp_id_currency" class="fixed-width-lg margin-right custom-select">
                            <option value="0">{l s='All currencies' mod='masseditproduct'}</option>
                            {if is_array($currencies) && count($currencies)}
                                {foreach from=$currencies item=currency}
                                    <option value="{$currency.id_currency|intval}">{$currency.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <select name="sp_id_country" class="fixed-width-lg margin-right custom-select">
                            <option value="0">{l s='All countries' mod='masseditproduct'}</option>
                            {if is_array($countries) && count($countries)}
                                {foreach from=$countries item=country}
                                    <option value="{$country.id_country|intval}">{$country.country|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <select name="sp_id_group" class="fixed-width-lg custom-select">
                            <option value="0">{l s='All groups' mod='masseditproduct'}</option>
                            {if is_array($groups) && count($groups)}
                                {foreach from=$groups item=group}
                                    <option value="{$group.id_group|intval}">{$group.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                    <input name="sp_id_product_attribute" value="0" type="hidden"/>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">

                        <span class="white-space-nowrap">
                            <label class="control-label margin-right">{l s='From' mod='masseditproduct'}:</label>
                            <input class="datepicker fixed-width-lg margin-right form-control" name="sp_from" type="text"/>
                        </span>

                        <span class="white-space-nowrap">
                            <label class="control-label margin-right">{l s='To' mod='masseditproduct'}:</label>
                            <input class="datepicker fixed-width-lg form-control" name="sp_to" type="text"/>
                        </span>

                    </div>
                </div>
                <script>
                    $('.datepicker').datetimepicker({
                        prevText: '',
                        nextText: '',
                        dateFormat: 'yy-mm-dd',
                        // Define a custom regional settings in order to use PrestaShop translation Tools
                        currentText: '{l s='Now' mod='masseditproduct' js=true}',
                        closeText: '{l s='Done' mod='masseditproduct' js=true}',
                        ampm: false,
                        amNames: ['AM', 'A'],
                        pmNames: ['PM', 'P'],
                        timeFormat: 'hh:mm:ss tt',
                        timeSuffix: '',
                        timeOnlyTitle: '{l s='Choose Time' mod='masseditproduct' js=true}',
                        timeText: '{l s='Time' mod='masseditproduct' js=true}',
                        hourText: '{l s='Hour' mod='masseditproduct' js=true}',
                        minuteText: '{l s='Minute' mod='masseditproduct' js=true}'
                    });
                </script>
                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label margin-right">{l s='Begin from quantity' mod='masseditproduct'}:</label>
                        <input name="sp_from_quantity" class="fixed-width-xs margin-right form-control" value="1" type="text"/>
                    </div>
                </div>
                <hr />

                <div class="row">
                    <div class="col-sm-12">
                        <div class="btn-group btn-group-radio edit_menu form-group" style="display: none;">
                            <label for="discount_price_disable">
                                <input type="radio" checked name="discount_price" value="-1" id="discount_price_disable"/>
                                <span class="">{l s='Keep' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_price_increase">
                                <input type="radio" name="discount_price" value="0" id="discount_price_increase"/>
                                <span class="">{l s='Increase' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_price_reduce">
                                <input type="radio" name="discount_price" value="1" id="discount_price_reduce"/>
                                <span class="">{l s='Reduce' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_price_rewrite">
                                <input type="radio" name="discount_price" value="2" id="discount_price_rewrite"/>
                                <span class="">{l s='Rewrite' mod='masseditproduct'}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">

                        <label class="control-label margin-right">{l s='Product price (tax excl.)' mod='masseditproduct'}:</label>
                        <input type="text" disabled class="specific_price_price fixed-width-xs margin-right form-control" name="price" value="">
                        <select name="discount_price_reduction_type" class="fixed-width-md margin-right custom-select" style="display:none;">
                            <option value="amount">{l s='Currency' mod='masseditproduct'}</option>
                            <option value="percentage">{l s='Percent' mod='masseditproduct'}</option>
                        </select>
                        <label class="control-label">
                            <input type="checkbox" name="leave_base_price" class="leave_base_price" checked>{l s='Leave base price' mod='masseditproduct'}
                        </label>

                    </div>
                </div>
                <hr />

                <div class="row ">
                    <div class="col-sm-12">
                        <div class="btn-group btn-group-radio edit_menu form-group" style="display: none;">
                            <label for="discount_discount_disable">
                                <input type="radio" checked name="discount_discount" value="-1" id="discount_discount_disable"/>
                                <span class="">{l s='Keep' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_discount_increase">
                                <input type="radio" name="discount_discount" value="0" id="discount_discount_increase"/>
                                <span class="">{l s='Increase' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_discount_reduce">
                                <input type="radio" name="discount_discount" value="1" id="discount_discount_reduce"/>
                                <span class="">{l s='Reduce' mod='masseditproduct'}</span>
                            </label>
                            <label for="discount_discount_rewrite">
                                <input type="radio" name="discount_discount" value="2" id="discount_discount_rewrite"/>
                                <span class="">{l s='Rewrite' mod='masseditproduct'}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label margin-right float-left">{l s='Apply discount' mod='masseditproduct'}:</label>
                        <input name="sp_reduction" class="fixed-width-xs margin-right form-control float-left" value="0" type="text"/>
                        <select name="sp_reduction_type" class="fixed-width-md custom-select float-left">
                            <option value="amount">{l s='Currency' mod='masseditproduct'}</option>
                            <option value="percentage">{l s='Percent' mod='masseditproduct'}</option>
                        </select>
                    </div>
                </div>

                <hr />
                <div class="row">
                    <div for="col-lg-12" class="col-lg-12 checkbox-delete">
                        <label class="control-label">
                            <input type="checkbox" name="delete_old_discount">{l s='Delete old discount' mod='masseditproduct'}
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row disabled_option_stage form-group">
        <input checked="" type="checkbox" name="disabled[]" value="delete_specific_price_all" class="disable_option">
        <div for="col-lg-12" class="col-lg-12 checkbox-delete">
            <label class="control-label">
                <input type="checkbox" name="delete_old_discount_all">{l s='Delete all discount' mod='masseditproduct'}
            </label>
        </div>
    </div>
{/block}