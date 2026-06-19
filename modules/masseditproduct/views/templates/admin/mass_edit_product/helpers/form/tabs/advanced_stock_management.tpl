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
    <div class="" {if $product->is_virtual}style="display:none;"{/if} class="row stockForVirtualProduct">
        <div class="col-sm-10 col-sm-offset-2">
            <p class="checkbox">
                <label class="control-label" for="advanced_stock_management">
                    <input type="checkbox" id="advanced_stock_management_che" name="advanced_stock_management" class="advanced_stock_management"/>{l s='I want to use the advanced stock management system for this product.' mod='masseditproduct'}
                </label>
            </p>
        </div>
    </div>

    <div class="stockForVirtualProduct">
        <div class="col-sm-2">
            <label class="control-label" for="depends_on_stock_1">{l s='Available quantities' mod='masseditproduct'}:</label>
        </div>
        <div class="col-sm-10">
            <p class="radio">
                <label class="control-label" for="depends_on_stock_1">
                    <input type="radio" id="depends_on_stock_1" name="depends_on_stock" class="depends_on_stock"  value="1"
                           checked="checked"
                           disabled="disabled"
                    />{l s='The available quantities for the current product and its combinations are based on the stock in your warehouse (using the advanced stock management system). ' mod='masseditproduct'}
                </label>
            </p>
            <p class="radio">
                <label class="control-label" for="depends_on_stock_0">
                    <input type="radio"  id="depends_on_stock_0" name="depends_on_stock" class="depends_on_stock" value="0"
                           checked="checked"
                    />{l s='I want to specify available quantities manually.' mod='masseditproduct'}
                </label>
            </p>
        </div>
    </div>
{/block}