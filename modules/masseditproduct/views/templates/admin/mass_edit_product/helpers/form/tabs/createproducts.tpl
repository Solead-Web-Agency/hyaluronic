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
    <script> var address_token = '{getAdminToken tab='AdminAddresses'}';</script>
    <div class="row form-group">
        <div class="col-sm-12">
            {$form_create_products|no_escape}
        </div>
        <div class="col-sm-12">
            <div id="product-prices" class="product-tab">
                <div class="col-sm-12">
                    <label class="control-label margin-right" for="unit_price">
                        <span data-toggle="tooltip" title="{l s='When selling a pack of items, you can indicate the unit price for each item of the pack. For instance, "per bottle" or "per pound".' mod='masseditproduct'}">{l s='Unit price (tax excl.)' mod='masseditproduct'}:</span>
                    </label>
                    <input id="unit_price" class="fixed-width-sm margin-right form-control" name="unit_price" type="text" value="" maxlength="27"/>
                    <label>{$currency2->sign|no_escape}</label>
                </div>
            </div>
        </div>
    </div>
{/block}
{block name="submit_id_attr"}createProducts{/block}