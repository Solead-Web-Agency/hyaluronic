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
    {if $advanced_stock_management}
        <div class="row form-group">
            <div class="col-lg-12 clearfix">
                <label class="control-label">{l s='Management quantity in' mod='masseditproduct'}</label>
            </div>
            <div class="col-lg-12">
                <div class="btn-group btn-group-radio">
                    <label for="change_type_quantity">
                        <input type="radio" name="change_type" value="quantity" id="change_type_quantity"/>
                        <span class="btn btn-default">{l s='Shop' mod='masseditproduct'}</span>
                    </label>
                    <label for="change_type_warehouse">
                        <input type="radio" name="change_type" value="warehouse" id="change_type_warehouse"/>
                        <span class="btn btn-default">{l s='Warehouse' mod='masseditproduct'}</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    {l s='If some of your products not uses advanced stock management, then these products will be skipped when changing the amount, If updated with the option Warehouses.' mod='masseditproduct'}
                </div>
            </div>
        </div>
    {/if}
    <!-- quantity tab -->
    <div class="row form-group">
        <div class="{if $advanced_stock_management}_type type_quantity type_warehouse{/if}">
            <div class="col-sm-12 clearfix">
                <label class="control-label margin-right float-left">{l s='Apply change for' mod='masseditproduct'}:</label>
                <span class="ps-switch prestashop-switch fixed-width-xl switch-product-combination float-left">
                        {foreach [0,1] as $value}
                            <input type="radio" name="change_for_qty" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="change_for_qty_product" {else} id="change_for_qty_combination" {/if}
                                    {if $value == 0} checked="checked" {/if}
                            />
                            <label {if $value == 1} for="change_for_qty_product" {else} for="change_for_qty_combination" {/if}>
                                {if $value == 0}{l s='Product' mod='masseditproduct'}{else}{l s='Combination' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                    <a class="slide-button"></a>
            </span>
            </div>
        </div>
        <div class="{if $advanced_stock_management}_type type_quantity{/if}">
            <div class="col-sm-12 clearfix">
                <label class="control-label margin-right float-left">{l s='What to do with quantity?' mod='masseditproduct'}:</label>
                <div class="btn-group btn-group-radio float-left">
                    <label for="action_quantity_increase">
                        <input type="radio" name="action_quantity" value="1" id="action_quantity_increase"/>
                        <span class="">{l s='Increase on value' mod='masseditproduct'}</span>
                    </label>
                    <label for="action_quantity_reduce">
                        <input type="radio" name="action_quantity" value="2" id="action_quantity_reduce"/>
                        <span class="">{l s='Reduce on value' mod='masseditproduct'}</span>
                    </label>
                    <label for="action_quantity_rewrite">
                        <input checked type="radio" name="action_quantity" value="3" id="action_quantity_rewrite"/>
                        <span class="">{l s='Rewrite' mod='masseditproduct'}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {if $advanced_stock_management}
        <div class="row form-group _type type_warehouse">
            <div class="col-lg-12 clearfix">
                <label class="control-label">{l s='Select warehouse' mod='masseditproduct'}</label>
            </div>
            <div class="col-lg-12">
                <select name="warehouse">
                    {foreach from=$warehouses item=warehouse}
                        <option value="{$warehouse.id_warehouse|intval}">{$warehouse.name|escape:'quotes':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row form-group _type type_warehouse">
            <div class="col-lg-12 clearfix">
                <label class="control-label">{l s='Action on stock' mod='masseditproduct'}</label>
            </div>
            <div class="co-lg-12">
                <select name="action_warehouse">
                    <option value="1">{l s='Increase in stock' mod='masseditproduct'}</option>
                    <option value="0">{l s='Decrease in stock' mod='masseditproduct'}</option>
                </select>
            </div>
        </div>
    {/if}
    <div class="row form-group {if $advanced_stock_management}_type type_quantity type_warehouse{/if}">
        <input checked type="checkbox" name="disabled[]" value="quantity" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Write quantity' mod='masseditproduct'}:</label>
            <input class="fixed-width-sm form-control" type="text" name="quantity" value="0"/>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="minimal_quantity" class="disable_option">
        <div class="col-sm-12">
            <label class="control-label margin-right">{l s='Minimum quantity' mod='masseditproduct'}:</label>
            <input class="fixed-width-sm form-control" type="text" name="minimal_quantity" value="0"/>
            <p class="help-block">{l s='The minimum quantity to buy this product (set to 1 to disable this feature)' mod='masseditproduct'}</p>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="available_now" class="disable_option">
        <div class="col-sm-12 clearfix form-group">
            <label class="control-label select-lang margin-right float-left">{l s='Select language' mod='masseditproduct'}:</label>
            <span class="btn-group btn-group-radio margin-right float-left">
                <label for="all_language_qty">
                    <input type="radio" checked name="language_qty" value="0" id="all_language_qty"/>
                    <span class="">{l s='For all' mod='masseditproduct'}</span>
                </label>
                {foreach from=$languages item=language}
                    <label for="{$language.id_lang|intval}_language_qty">
                        <input type="radio" name="language_qty" value="{$language.id_lang|intval}" id="{$language.id_lang|intval}_language_qty"/>
                        <span class="">{$language.name|escape:'quotes':'UTF-8'}</span>
                    </label>
                {/foreach}
            </span>
         </div>

        <div class="col-sm-12 clearfix">
            <label class="control-label desc-label margin-right">{l s='Displayed text when in-stock' mod='masseditproduct'}:</label>
            <input class="control-label fixed-width-xl form-control" type="text" name="available_now"/>
        </div>
    </div>
    <!-- --------------------------------------->
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="available_later" class="disable_option">
        <div class="col-sm-12 clearfix form-group">
            <label class="control-label select-lang margin-right float-left">{l s='Select language' mod='masseditproduct'}:</label>
            <span class="btn-group btn-group-radio margin-right float-left">
                <label for="all_language_qty2">
                    <input type="radio" checked name="language_qty2" value="0" id="all_language_qty2"/>
                    <span class="">{l s='For all' mod='masseditproduct'}</span>
                </label>
                {foreach from=$languages item=language}
                    <label for="{$language.id_lang|intval}_language_qty2">
                        <input type="radio" name="language_qty2" value="{$language.id_lang|intval}" id="{$language.id_lang|intval}_language_qty2"/>
                        <span class="">{$language.name|escape:'quotes':'UTF-8'}</span>
                    </label>
                {/foreach}
            </span>
        </div>

        <div class="col-sm-12 clearfix">
            <label class="control-label desc-label margin-right">{l s='Displayed text when backordering is allowed' mod='masseditproduct'}:</label>
            <input class="control-label fixed-width-xl form-control" type="text" name="available_later"/>
        </div>
    </div>
    <!------------------------------------------>
{*    <div class="row form-group">*}
{*        <input checked type="checkbox" name="disabled[]" value="available_later" class="disable_option">*}
{*        <div class="col-sm-12">*}
{*            <label class="control-label desc-label margin-right">{l s='Displayed text when backordering is allowed' mod='masseditproduct'}:</label>*}
{*            <input class="control-label fixed-width-xl form-control" type="text" name="available_later"/>*}
{*        </div>*}
{*    </div>*}
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="available_date" class="disable_option">
        <div class="col-sm-12 clearfix">

            <div class="float-left">
                <label class="control-label desc-label margin-right float-left">{l s='Availability date' mod='masseditproduct'}:</label>
                <span class="ps-switch prestashop-switch fixed-width-xxxl switch-product-combination margin-right float-left">
                    {foreach [0,1] as $value}
                        <input type="radio" name="change_available_date" value="{$value|escape:'quotes':'UTF-8'}"
                                {if $value == 0} id="ad_for_product" {else} id="ad_for_pa" {/if}
                                {if $value == 0} checked="checked" {/if}
                        />
                        <label {if $value == 0} for="ad_for_product" {else} for="ad_for_pa" {/if}>
                            {if $value == 0}{l s='For product' mod='masseditproduct'}{else}{l s='For combinations' mod='masseditproduct'}{/if}
                        </label>
                    {/foreach}
                    <a class="slide-button"></a>
                </span>
            </div>
            <div class="float-left">
                <input type="text" class="datepicker fixed-width-xl margin-right form-control" name="available_date"/>
            </div>

        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="out_of_stock" class="disable_option">
        <div class="col-sm-12">
            <div id="when_out_of_stock" class="form-group">
                <div class="col-sm-12">
                    <label class="control-label margin-right">{l s='When out of stock' mod='masseditproduct'}:</label>
                    <p class="radio margin-right">
                        <label id="label_out_of_stock_1" class="control-label" for="out_of_stock_1">
                            <input type="radio" id="out_of_stock_1" name="out_of_stock" checked="checked" value="0" class="out_of_stock">{l s='Deny orders' mod='masseditproduct'}
                        </label>
                    </p>
                    <p class="radio margin-right">
                        <label id="label_out_of_stock_2" class="control-label" for="out_of_stock_2">
                            <input type="radio" id="out_of_stock_2" name="out_of_stock" value="1" class="out_of_stock">{l s='Allow orders' mod='masseditproduct'}
                        </label>
                    </p>
                    <p class="radio">
                        <label id="label_out_of_stock_3" class="control-label" for="out_of_stock_3">
                            <input type="radio" id="out_of_stock_3" name="out_of_stock" value="2" class="out_of_stock">{l s='Default' mod='masseditproduct'}:
                            {if $pack_stock_type == 0}
                                {l s='Decrement pack only'  mod='masseditproduct'}
                            {elseif $pack_stock_type == 1}
                                {l s='Decrement products in pack only'  mod='masseditproduct'}
                            {else}
                                {l s='Decrement both'  mod='masseditproduct'}
                            {/if}
                            <a class="confirm_leave" href="index.php?tab=AdminPPreferences&token=&amp;token={$token_preferences|no_escape}">
                                {l s='as set in the Products Preferences page' mod='masseditproduct'}
                            </a>
                        </label>
                    </p>
                </div>
            </div>
        </div>
    </div>
{/block}