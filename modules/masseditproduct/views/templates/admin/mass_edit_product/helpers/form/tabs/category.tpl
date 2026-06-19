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
    <!-- category tab -->
    <div class="row">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right float-left w-100">{l s='Action with categories' mod='masseditproduct'}:</label>
            <span class="ps-switch prestashop-switch fixed-width-xxxl float-left">
                {foreach [0,1] as $value}
                    <input type="radio" name="action_with_category" value="{$value|escape:'quotes':'UTF-8'}"
                            {if $value == 1} id="action_with_category_add" {else} id="action_with_category_delete" {/if}
                            {if $value == 0} checked="checked" {/if} />
                    <label {if $value == 1} for="action_with_category_add" {else} for="action_with_category_delete" {/if}>
                        {if $value == 0}{l s='Add selected' mod='masseditproduct'}{else}{l s='Delete selected' mod='masseditproduct'}{/if}
                    </label>
                {/foreach}
                <a class="slide-button"></a>
            </span>
        </div>
    </div>
    <div class="row categories-block">
            <div class="tree_custom_categories col-sm-12">

                    <label class="control-label margin-right float-left w-100">{l s='Set categories for all products' mod='masseditproduct'}:</label>

                {renderTemplate file="admin/mass_edit_product/helpers/form/tree.tpl"
                v=[
                'categories'=>$categories,
                'id_category'=>Configuration::get('PS_ROOT_CATEGORY'),
                'root'=>true,
                'view_header'=>true,
                'multiple'=>true,
                'selected_categories'=>[],
                'name'=>'category[]'
                ]
                }
                {*<select name="category">*}
                {*{foreach from=$simple_categories item=category}*}
                {*<option value="{$category.id_category|intval}">{$category.name|escape:'quotes':'UTF-8'}</option>*}
                {*{/foreach}*}
                {*</select>*}
            </div>
    </div>
    <div class="row _action _action_add">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Set default category for all products' mod='masseditproduct'}:</label>
            <select class="fixed-width-lg margin-right custom-select" name="category_default"></select>
            <label class="control-label margin-righ">
                <input type="checkbox" name="remove_old_categories">{l s='Remove old categories' mod='masseditproduct'}
            </label>
        </div>
    </div>
{/block}