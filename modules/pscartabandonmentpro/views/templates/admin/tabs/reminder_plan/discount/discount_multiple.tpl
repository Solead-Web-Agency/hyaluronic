{*
* 2007-2019 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="discount">
    <div class="row nopadding-left col-lg-10 col-xs-10 col-lg-offset-1 col-xs-offset-1 nopadding-left">

        <div class="discount-from-to nopadding-left col-lg-2 col-xs-2">
            <div class="input-group">
                <input type="text" name="discount_from" class="discount_from" value="{if isset($discount['discount_from'])}{$discount['discount_from']}{else}{$key}{/if}"/>
                <span class="input-group-addon" id="discount_from">{$currency}</span>
            </div>
            <input type="hidden" name="discount_to" class="discount_to" value="{if isset($discount['discount_to'])}{$discount['discount_to']}{else}{$key+1}{/if}"/>
       </div>

        <div class="nopadding-left col-lg-12 col-xs-12">
            <div class="intern-section nopadding-left col-lg-2 col-xs-2">
                <p class="range-number">
                    <i class="material-icons">arrow_downward</i>{l s='Range' mod='pscartabandonmentpro'} <span class="range">{$key}</span>
                </p>
            </div>
            <div class="col-lg-9 col-xs-9 col-lg-offset-1 col-xs-offset-1">
                {include file="./discount_template.tpl"}
            </div>
        </div>
    </div>
    <div class="col-lg-1 col-xs-1 remove_line">
        <i class="material-icons">delete</i>
    </div>
</div>