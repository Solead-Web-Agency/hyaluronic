{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    Revolut
* @copyright Since 2020 Revolut
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<form id="revolutPayForm" method="post" action="{$formActionValidation|escape:'htmlall':'UTF-8'}">
    {if $currency_error}
        <div class="error">
            <ul>
                <li>
                    {$selected_currency|escape:'htmlall':'UTF-8'} {l s='currency is not supported, please use a different currency to check out. You can check the supported currencies in the ' mod='revolutpayment'}
                    <a target="_blank"
                       href="{$support_link|escape:'htmlall':'UTF-8'}">{l s='[following link]' mod='revolutpayment'}</a>
                </li>
            </ul>
        </div>
    {elseif $revolut_order_public_id_error}
        <div class="error">
            <ul>
                <li>
                    {l s='To receive payments using the Revolut Gateway for PrestaShop module, please configure your API key. If you are still seeing this message after the configuration of your API key, please reach out via the' mod='revolutpayment'}
                    <a target="_blank" href="{$ps_support_link|escape:'htmlall':'UTF-8'}"> {l s='support chat' mod='revolutpayment'} </a>
                    {l s='through your PrestaShop account.' mod='revolutpayment'}
                </li>
            </ul>
        </div>
    {else}
        <div class="revolut-pay-desc">
            <div class="row">
                <div class="col-md-6">
                    <div id="revolut_pay"></div>
                </div>
            </div>
            <div class="error hidden"></div>
        </div>
        <input type="hidden" name="merchant_type" id="revolutMerchantType" value="{$merchant_type|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="public_id" id="revolutPublicId" value="{$public_id|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="merchant_public_key" id="merchantPublicKey" value="{$merchant_public_key|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="is_rev_pay_v2" id="isRevPayV2" value="{$is_rev_pay_v2|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="order_total_amount" id="orderTotalAmount" value="{$order_total_amount|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="shipping_amount" id="shippingAmount" value="{$shipping_amount|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="order_currency" id="orderCurrency" value="{$selected_currency|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" id="revolutLocale" name="locale" value="{$locale|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" id="phone_number" name="phone_number" value="{$phone_number|escape:'htmlall':'UTF-8'}"/>
    {/if}
</form>
