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
    <!-- accessories tab -->
    <div class="row">
        <div class="col-sm-12">
            <div class="select_products form-group">
                <div class="search_row">
                    <div class="col-sm-12">

                        <div class="float-left">
                            <label class="control-label margin-right">{l s='Write for search' mod='masseditproduct'}:</label>
                            <input class="search_product fixed-width-xl margin-right form-control" type="text"/>
                        </div>

                        <div class="float-left">
                            <span class="ps-switch prestashop-switch fixed-width-400 switch-product-combination">
                                {foreach [1,0] as $value}
                                    <input type="radio" name="search_by" value="{$value|escape:'quotes':'UTF-8'}"
                                            {if $value == 1} id="search_by_name" {else} id="search_by_reference" {/if}
                                            {if $value == 1} checked="checked" {/if}
                                    />
                                    <label {if $value == 1} for="search_by_name" {else} for="search_by_reference" {/if}>
                                        {if $value == 1}{l s='Search by name' mod='masseditproduct'}{else}{l s='Search by reference' mod='masseditproduct'}{/if}
                                    </label>
                                {/foreach}
                                <a class="slide-button"></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="search_row">
                    <div class="left_column col-sm-6">
                        <label class="control-label">{l s='Select from list' mod='masseditproduct'}:</label>
                        <select class="no_selected_product" multiple></select>
                        <input class="add_select_product btn-default btn" value="{l s='Add in select products' mod='masseditproduct'}" type="button"/>
                    </div>
                    <div class="right_column col-sm-6">
                        <label class="control-label">{l s='Selected' mod='masseditproduct'}:</label>
                        <select name="accessories[]" class="selected_product" multiple></select>
                        <input class="remove_select_product btn-default btn" value="{l s='Remove from select products' mod='masseditproduct'}" type="button"/>
                    </div>
                </div>
            </div>
            <script>
                $(function () {
                    $('.select_products').selectProducts({
                        path_ajax: document.location.href.replace(document.location.hash, '')
                    });
                });
            </script>

            <div class="remove-old">
                <!-- Remove old? -->
                <label class="control-label margin-right float-left">{l s='Remove old?' mod='masseditproduct'}:</label>
                <span class="ps-switch prestashop-switch fixed-width-lg float-left">
                    {foreach [1,0] as $value}
                        <input type="radio" name="remove_old" value="{$value|escape:'quotes':'UTF-8'}"
                                {if $value == 1} id="remove_old_yes" {else} id="remove_old_no" {/if}
                                {if $value == 1} checked="checked" {/if} />
                        <label {if $value == 1} for="remove_old_yes" {else} for="remove_old_no" {/if}>
                            {if $value == 1}{l s='Yes' mod='masseditproduct'}{else}{l s='No' mod='masseditproduct'}{/if}
                        </label>
                    {/foreach}
                    <a class="slide-button"></a>
                </span>
            </div>
        </div>
    </div>
{/block}