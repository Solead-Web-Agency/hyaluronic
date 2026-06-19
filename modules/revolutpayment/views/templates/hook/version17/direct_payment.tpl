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

<form id="revolutForm" method="post" action="{$formActionValidation|escape:'htmlall':'UTF-8'}">
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
    {elseif $card_widget_currency_error}
        <div class="error">
            <ul>
                <li>
                    {$selected_currency|escape:'htmlall':'UTF-8'} {l s='currency is not supported for card payments, please use a different currency to check out. You can check the supported currencies in the ' mod='revolutpayment'}
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
        {if $payment_description != ''}
            <p>{$payment_description|escape:'htmlall':'UTF-8'}</p>
        {/if}
        <input type="hidden" name="revolut_payment_title" id="revolutPaymentTitle" value="{$revolut_payment_title|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="merchant_type" id="revolutMerchantType" value="{$merchant_type|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="public_id" id="revolutPublicId" value="{$public_id|escape:'htmlall':'UTF-8'}"/>
        <div id="revolut_card"></div>
        <input type="hidden" name="email" value="{$customer_email|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="customer_name" value="{$customer_name|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="country" value="{$country->iso_code|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="state" value="{$state->iso_code|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="city" value="{$address->city|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="line1" value="{$address->address1|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="line2" value="{$address->address2|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="postal" value="{$address->postcode|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" class="visa_msc_logo" value="{$logo|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" id="revolutLocale" name="locale" value="{$locale|escape:'htmlall':'UTF-8'}"/>
        <div class="error hidden"></div>
    {/if}
</form>
