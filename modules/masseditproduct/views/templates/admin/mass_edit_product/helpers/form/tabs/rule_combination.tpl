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
    <div class="row hidden form-group">
        <div class="col-sm-12 clearfix ">
            <label class="control-label margin-right ">{l s='Apply change for' mod='masseditproduct'}:</label>
            <span class="ps-switch prestashop-switch fixed-width-xl switch-product-combination margin-right">
                        {foreach [0,1] as $value}
                            <input type="radio" name="rc_apply_change_for" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="rc_apply_change_for_product" {else} id="rc_apply_change_for_combination" {/if}
                                    {if $value == 0} checked="checked" {/if}
                            />
                            <label {if $value == 1} for="rc_apply_change_for_product" {else} for="rc_apply_change_for_combination" {/if}>
                                {if $value == 0}{l s='Product' mod='masseditproduct'}{else}{l s='Combination' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                <a class="slide-button"></a>
            </span>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="selected_attributes" class="disable_option">
        <div class="col-xs-12">
            <label class="control-label ">{l s='Delete combinations, which match attributes' mod='masseditproduct'}:</label>
        </div>
        <div class="col-xs-12">
            <div class="col-xs-12">
                <div class="row row_attributes">
                    {if is_array($attribute_groups) && count($attribute_groups)}

                        <select class="fixed-width-xl margin-right custom-select" name="attribute_group">
                            {foreach from=$attribute_groups item=attribute_group}
                                <option value="{$attribute_group.id_attribute_group|escape:'quotes':'UTF-8'}">{$attribute_group.name|escape:htmlall}</option>
                            {/foreach}
                        </select>

                        {foreach from=$attribute_groups item=attribute_group}
                            {if isset($attribute_group.attributes) && count($attribute_group.attributes)}
                                <span id="attribute_group_{$attribute_group.id_attribute_group|intval}">
                                <select name="attributes" class="fixed-width-xl margin-right custom-select">
                                    {foreach from=$attribute_group.attributes item=attribute}
                                        <option value="{$attribute.id_attribute|intval}">{$attribute.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </span>
                            {/if}
                        {/foreach}

                        <button class="btn btn-success addAttribute">
                            <i class="icon-plus"></i>
                            {l s='Add attribute' mod='masseditproduct'}
                        </button>
                        <input type="hidden" name="selected_attributes" value="">

                    {/if}
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="selected_attributes">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    <label class="control-label">
                        <input type="checkbox" name="exact_match">{l s='Exact Match' mod='masseditproduct'}:
                    </label>
                </div>
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        {l s='Search exact match. In combinations of products in this case must be the same set of attributes that you have chosen' mod='masseditproduct'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="delete_attribute" class="disable_option">
        <div class="col-xs-12">
            <label class="control-label">{l s='Delete attribute from combinations' mod='masseditproduct'}:</label>
        </div>
        <div class="col-xs-12">
            <div class="row_attributes">
                {if is_array($attribute_groups) && count($attribute_groups)}

                    <select class="fixed-width-xl margin-right custom-select" name="attribute_group">
                        {foreach from=$attribute_groups item=attribute_group}
                            <option value="{$attribute_group.id_attribute_group|escape:'quotes':'UTF-8'}">{$attribute_group.name|escape:htmlall}</option>
                        {/foreach}
                    </select>

                    {foreach from=$attribute_groups item=attribute_group}
                        {if isset($attribute_group.attributes) && count($attribute_group.attributes)}
                            <span class="delete_attribute margin-right" id="attribute_group_{$attribute_group.id_attribute_group|intval}">
                                <select class="fixed-width-xl custom-select" name="delete_attribute">
                                    {foreach from=$attribute_group.attributes item=attribute}
                                        <option value="{$attribute.id_attribute|intval}">{$attribute.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </span>
                        {/if}
                    {/foreach}
                {/if}

                <label class="control-label">
                    <input type="checkbox" name="force_delete_attribute" value="1">{l s='Force delete attribute from combinations' mod='masseditproduct'}
                </label>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="add_attribute" class="disable_option">
        <div class="col-xs-12">
            <div class="row_attributes">
                <label class="control-label margin-right">{l s='Add attribute in combinations' mod='masseditproduct'}:</label>
                {if is_array($attribute_groups) && count($attribute_groups)}
                    <span>
                        <select class="fixed-width-xl margin-right custom-select" name="attribute_group">
                            {foreach from=$attribute_groups item=attribute_group}
                                <option value="{$attribute_group.id_attribute_group|escape:'quotes':'UTF-8'}">{$attribute_group.name|escape:htmlall}</option>
                            {/foreach}
                        </select>
                    </span>
                    {foreach from=$attribute_groups item=attribute_group}
                        {if isset($attribute_group.attributes) && count($attribute_group.attributes)}
                            <span class="add_attribute" id="attribute_group_{$attribute_group.id_attribute_group|intval}">
                                <select class="fixed-width-xl custom-select" name="add_attribute">
                                    {foreach from=$attribute_group.attributes item=attribute}
                                        <option value="{$attribute.id_attribute|intval}">{$attribute.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </span>
                        {/if}
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>
{/block}