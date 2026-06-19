{*
* 2007-2017 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !empty($data)}
    <form method="post" name="atos_payment" id="atos_payment" action="{$connector_url|escape:'htmlall':'UTF-8'}">
        <input type="hidden" name="Data" value="{$data|escape:'htmlall':'UTF-8'}">
        <input type="hidden" name="InterfaceVersion" value="HP_2.9">
        <input type="hidden" name="Seal" value="{$seal|escape:'htmlall':'UTF-8'}">
    </form>
{/if}
<p class="payment_module">
    {if !empty($data)}
        <a class="banwire" title="{l s='Pay with credit card' mod='worldline'}" href="javascript:document.atos_payment.submit();">
            <img width="150px" src="{$logoPath|escape:'htmlall':'UTF-8'}" alt="Worldline {l s='mastercard payment' mod='worldline'}">
            <!-- <img src="{$module_path|escape:'htmlall':'UTF-8'}views/img/picto_logos_cb.png" alt="Worldline {l s='mastercard visa cb payment' mod='worldline'}"> -->
      {l s='Pay by credit card with ' mod='worldline'}{$psp_brand|escape:'htmlall':'UTF-8'}
            <img class="arrow pull-right" src="{$module_path|escape:'htmlall':'UTF-8'}views/img/arrow.png">
        </a>
    {else}
        {l s='Your order total must be greater than' mod='worldline'} {displayPrice price=1} {l s='in order to pay by credit card.' mod='worldline'}
    {/if}
</p>
{if !empty($data2x)}
<form method="post" name="atos_payment2x" id="atos_payment2x" action="{$connector_url|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="Data" value="{$data2x|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="InterfaceVersion" value="HP_2.9">
    <input type="hidden" name="Seal" value="{$seal2x|escape:'htmlall':'UTF-8'}">
</form>
<p class="payment_module">
    <a class="banwire" title="{l s='Pay with credit card' mod='worldline'}" href="javascript:document.atos_payment2x.submit();">
        <img width="150px" src="{$logoPath|escape:'htmlall':'UTF-8'}" alt="Worldline {l s='mastercard payment' mod='worldline'}">
        <!-- <img src="{$module_path|escape:'htmlall':'UTF-8'}views/img/picto_logos_cb.png" alt="Worldline {l s='mastercard visa cb payment' mod='worldline'}"> -->
        {l s='Pay with credit card with ' mod='worldline'}{$psp_brand|escape:'htmlall':'UTF-8'}
        <span>({l s='2x Payment' mod='worldline'})</span>
        <img class="arrow pull-right" src="{$module_path|escape:'htmlall':'UTF-8'}views/img/arrow.png">
    </a>
</p>
{/if}
{if !empty($data3x)}
<form method="post" name="atos_payment3x" id="atos_payment3x" action="{$connector_url|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="Data" value="{$data3x|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="InterfaceVersion" value="HP_2.9">
    <input type="hidden" name="Seal" value="{$seal3x|escape:'htmlall':'UTF-8'}">
</form>
<p class="payment_module">
    <a class="banwire" title="{l s='Pay with credit card' mod='worldline'}" href="javascript:document.atos_payment3x.submit();">
        <img width="150px" src="{$logoPath|escape:'htmlall':'UTF-8'}" alt="Worldline {l s='mastercard payment' mod='worldline'}">
        <!-- <img src="{$module_path|escape:'htmlall':'UTF-8'}views/img/picto_logos_cb.png" alt="Worldline {l s='mastercard visa cb payment' mod='worldline'}"> -->
        {l s='Pay by credit card with ' mod='worldline'}{$psp_brand|escape:'htmlall':'UTF-8'}
        <span>({l s='3x Payment' mod='worldline'})</span>
        <img class="arrow pull-right" src="{$module_path|escape:'htmlall':'UTF-8'}views/img/arrow.png">
    </a>
</p>
{/if}
{if !empty($data4x)}
<form method="post" name="atos_payment4x" id="atos_payment4x" action="{$connector_url|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="Data" value="{$data4x|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="InterfaceVersion" value="HP_2.9">
    <input type="hidden" name="Seal" value="{$seal4x|escape:'htmlall':'UTF-8'}">
</form>
<p class="payment_module">
    <a class="banwire" title="{l s='Pay with credit card' mod='worldline'}" href="javascript:document.atos_payment4x.submit();">
        <img width="150px" src="{$logoPath|escape:'htmlall':'UTF-8'}" alt="Worldline {l s='mastercard payment' mod='worldline'}">
        <!-- <img src="{$module_path|escape:'htmlall':'UTF-8'}views/img/picto_logos_cb.png" alt="Worldline {l s='mastercard visa cb payment' mod='worldline'}"> -->
        {l s='Pay by credit card with ' mod='worldline'}{$psp_brand|escape:'htmlall':'UTF-8'}
        <span>({l s='4x Payment' mod='worldline'})</span>
        <img class="arrow pull-right" src="{$module_path|escape:'htmlall':'UTF-8'}views/img/arrow.png">
    </a>
</p>
{/if}
