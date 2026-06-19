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

<div class="row _row_copy">
    <div class="col-sm-12">
        <label class="delete_old_discount control-label margin-right">
            {l s='Copy from product:' mod='masseditproduct'}
        </label>
    </div>
    <div class="_row_copy_main-block col-sm-12">
        <span class="search_input">
            <input class="form-control _search fixed-width-xl" type="text">
            <input type="hidden" class="_id_product fixed-width-xl">
        </span>
        <span class="small_text">
            {l s='Begin write name or id product' mod='masseditproduct'}
        </span>
        <select class="form-control _lang fixed-width-sm margin-right">
            {if is_array($languages) && count($languages)}
                {foreach from=$languages item=l}
                    <option {if $l.id_lang == $default_form_language}selected{/if} value="{$l.id_lang|escape:'quotes':'UTF-8'}">{$l.iso_code|escape:'quotes':'UTF-8'}</option>
                {/foreach}
            {/if}
        </select>
        <button type="button" class="btn btn-default _submit" data-field="{$field|escape:'quotes':'UTF-8'}">
            {l s='Copy' mod='masseditproduct'}
        </button>
    </div>
    <div class="col-sm-12 selected_product"></div>
</div>
