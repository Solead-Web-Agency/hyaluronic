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
    <div class="row form-group">
        <input checked="" type="checkbox" name="disabled[]" value="supplier" class="disable_option">
        <div class="col-sm-12 clearfix">
            <div>
                <label class="control-label margin-right float-left">{l s='Select suppliers' mod='masseditproduct'}:</label>

                <select class="d fixed-width-xxxl" multiple name="supplier[]">
                    {if is_array($suppliers) && count($suppliers)}
                        {foreach from=$suppliers item=supplier}
                            <option value="{$supplier.id_supplier|intval}">{$supplier.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <input checked="" type="checkbox" name="disabled[]" value="id_supplier_default" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right">{l s='Select supplier default' mod='masseditproduct'}:</label>
            <select class="fixed-width-xl custom-select" name="id_supplier_default"></select>
        </div>
    </div>
    <div class="row form-group">
        <input checked="" type="checkbox" name="disabled[]" value="supplier_reference" class="disable_option">
        <label class="control-label col-lg-12">{l s='Supplier reference(s)' mod='masseditproduct'}:</label>
            <table class="table-new">
                <thead>
                <tr class="table_head">
                    <th>{l s='Suppliers' mod='masseditproduct'}</th>
                    <th>{l s='Supplier reference' mod='masseditproduct'}</th>
                    <th>{l s='Unit price tax excluded' mod='masseditproduct'}</th>
                    <th>{l s='Unit price currency' mod='masseditproduct'}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select class="w-100" multiple name="suppliers_sr[]">
                            {if is_array($suppliers) && count($suppliers)}
                                {foreach from=$suppliers item=supplier}
                                    <option value="{$supplier.id_supplier|intval}">{$supplier.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </td>
                    <td>
                        <input class="form-control" type="text" value="" name="supplier_reference">
                    </td>
                    <td class="fixed-width-lg">
                        <input class="form-control " type="text" value="" name="product_price">
                    </td>
                    <td class="fixed-width-lg">
                        <select class="custom-select" name="product_price_currency">
                            {foreach $currencies AS $currency}
                                <option value="{$currency['id_currency']|intval}"
                                        {if $currency['id_currency'] == $id_default_currency}selected="selected"{/if}
                                >{$currency['name']|escape:'quotes':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

        <div class="col-sm-12 clearfix">
            <div class="alert alert-info">{l s='When setting up a supplier\'s product for products with combinations, you must mark the combinations.' mod='masseditproduct'}</div>
        </div>
    </div>
{/block}