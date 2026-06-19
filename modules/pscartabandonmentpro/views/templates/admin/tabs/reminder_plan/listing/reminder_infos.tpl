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

<div class="col-lg-12 col-xs-12 reminder_info">
    <div class="col-lg-2 col-xs-2 align_right">
        <img src="{$module_url}/views/img/templates/{$sEmailTemplateName}.jpg"/>
    </div>
    <div class="col-lg-10 col-xs-10">
        <section class="col-lg-12 col-xs-12">
            <div class="col-lg-2 col-xs-2 align_right">
                {l s='Email\'s template' mod='pscartabandonmentpro'} :
            </div>
            <div class="col-lg-10 col-xs-10 align_left">
                {$sEmailTemplateName}
            </div>
        </section>

        <section class="col-lg-12 col-xs-12">
            <div class="col-lg-2 col-xs-2 align_right">
                {l s='Target' mod='pscartabandonmentpro'} :
            </div>
            <div class="col-lg-10 col-xs-10 align_left">
                {$sFinalTargetInfos}
            </div>
        </section>

        <section class="col-lg-12 col-xs-12">
            <div class="col-lg-2 col-xs-2 align_right">
                {l s='Discount' mod='pscartabandonmentpro'} :
            </div>
            <div class="col-lg-10 col-xs-10 align_left">
                {foreach from=$aFinalDiscountInfos item=item key=key}
                    <p>{$item}</p>
                {/foreach}
            </div>
        </section>
    </div>
</div>