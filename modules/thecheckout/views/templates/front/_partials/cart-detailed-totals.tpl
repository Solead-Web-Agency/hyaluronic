{**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='cart_detailed_totals'}
  {if (isset($shipping_block_wait_for_address) && $shipping_block_wait_for_address|count) || ($forceToChooseCarrier && !(isset($customerSelectedDeliveryOption) && $carrierSelected == $customerSelectedDeliveryOption|intval))}
    {assign var='waitForShippingCls' value=' wait-for-shipping'}
  {else}
    {assign var='waitForShippingCls' value=''}
  {/if}
  <div class="cart-detailed-totals">
    <div class="card-block">
      {foreach from=$cart.subtotals item="subtotal"}
        {if isset($subtotal.value) && $subtotal.value && $subtotal.type !== 'tax'}
          <div class="cart-summary-line{if $waitForShippingCls && 'products' != $subtotal.type}{$waitForShippingCls|escape:'javascript':'UTF-8'}{/if}{if !$subtotal.amount} free{/if}" id="cart-subtotal-{$subtotal.type|escape:'javascript':'UTF-8'}">
          <span class="label{if 'products' === $subtotal.type} js-subtotal{/if}">
            {if 'products' == $subtotal.type}
              {$cart.summary_string|escape:'htmlall':'UTF-8'}
            {else}
              {$subtotal.label|escape:'htmlall':'UTF-8'}
            {/if}
          </span>
          {if $subtotal.type|escape:'javascript':'UTF-8' === 'shipping'}
            <span class="label summary-carrier-name" style="display: none;">
              {if isset($carrierSelectedObject) && $carrierSelectedObject}
                {$carrierSelectedObject->name}
              {/if}
            </span>
          {/if}
            <span class="value">{$subtotal.value|escape:'htmlall':'UTF-8'}</span>
            {if $subtotal.type === 'shipping'}
              <div>
                <small class="value">{hook h='displayCheckoutSubtotalDetails' subtotal=$subtotal}</small>
              </div>
            {/if}
          </div>
        {/if}
      {/foreach}
      {hook h="displayPaymentRuleCartSummary"}
    </div>

    {block name='cart_voucher'}
      {include file='module:thecheckout/views/templates/front/_partials/cart-voucher.tpl'}
    {/block}

    {*<hr class="separator">*}

    <div class="card-block">
        {if (isset($cart.subtotals.tax) && $cart.subtotals.tax.amount > 0) || (!$configuration.display_prices_tax_incl && $configuration.taxes_enabled) }
            {* tax displayed in cart summary, we show Total (tax excl.), Tax and Total (tax incl.) *}
          <div class="cart-summary-line cart-total-tax-excluded{$waitForShippingCls|escape:'javascript':'UTF-8'}">
            <span class="label">{$cart.totals.total_excluding_tax.label|escape:'htmlall':'UTF-8'}</span>
            <span class="value">{$cart.totals.total_excluding_tax.value|escape:'htmlall':'UTF-8'}</span>
          </div>
            {if isset($cart.subtotals.tax)}
              <div class="cart-summary-line cart-total-tax{$waitForShippingCls|escape:'javascript':'UTF-8'}">

                <div class="label" style="display: inline">{$cart.subtotals.tax.label|escape:'javascript':'UTF-8'}
                  {if $cart.totals.total_excluding_tax.amount > 0}
                    {math equation='(a/b)*100' a=$cart.subtotals.tax.amount b=$cart.totals.total_excluding_tax.amount assign='effective_tax_rate'}
                    {math equation='abs(round(a)-a)' a=$effective_tax_rate assign='rounding_delta'}
                    {if $rounding_delta < 0.09}
                        {math equation='round(a)' a=$effective_tax_rate assign='effective_tax_rate_rounded'}
                    {else}
                        {math equation='a' a=$effective_tax_rate assign='effective_tax_rate_rounded' format="%.1f"}
                    {/if}
                    <span class="effective-tax-rate">({$effective_tax_rate_rounded|escape:'javascript':'UTF-8'}%)</span>
                  {/if}
                </div>
                <span class="value">{$cart.subtotals.tax.value|escape:'javascript':'UTF-8'}</span>
              </div>
            {/if}
            {* tax is set and non-zero cart summary, we show Total (tax incl.) *}
          <div class="cart-summary-line cart-total cart-total-tax-included{$waitForShippingCls|escape:'javascript':'UTF-8'}">
            <span class="label">{$cart.totals.total_including_tax.label|escape:'htmlall':'UTF-8'}</span>
            <span class="value">{$cart.totals.total_including_tax.value|escape:'htmlall':'UTF-8'}</span>
          </div>
        {else}
            {* tax is zero or not used in cart summary, we show Total (tax_label) *}
          <div class="cart-summary-line cart-total cart-total-auto-tax{$waitForShippingCls|escape:'javascript':'UTF-8'}">
            <span class="label">{$cart.totals.total.label|escape:'htmlall':'UTF-8'}{if isset($configuration) && $configuration.taxes_enabled}<span class="tax-lbl"> {$cart.labels.tax_short|escape:'htmlall':'UTF-8'}</span>{/if}</span>
            <span class="value">{$cart.totals.total.value|escape:'htmlall':'UTF-8'}</span>
          </div>
            {if isset($cart.subtotals.tax)}
              <div class="cart-summary-line cart-total-tax{$waitForShippingCls|escape:'javascript':'UTF-8'}">
                <div class="label" style="display: inline">{$cart.subtotals.tax.label|escape:'javascript':'UTF-8'}
                  <span class="effective-tax-rate">(0%)</span>
                </div>
                <span class="value">{$cart.subtotals.tax.value|escape:'javascript':'UTF-8'}</span>
              </div>
            {/if}
        {/if}
        {if $cart.totals.total_excluding_tax.amount == $cart.totals.total_including_tax.amount && $configuration.taxes_enabled}
          <div style="display: none;" class="cart-summary-line vat-exempt">
            <span class="label">{l s='Your order is now 0% VAT' mod='thecheckout'}</span>
          </div>
        {/if}


        {assign var='ps_freeshipping_price' value=Configuration::get('PS_SHIPPING_FREE_PRICE')}
        {if $ps_freeshipping_price}
            {assign var=currency value=Context::getContext()->currency}
            {assign var=ps_freeshipping_price value=$currency->getConversionRate()*$ps_freeshipping_price}

            {* If user has tax excluded prices turned on, adjust ps_freeshipping_price by effective_tax_rate *}
            {* It's still not perfect, if carrier has different tax rate than products, but it's close enough *}
            {if !$configuration.display_prices_tax_incl && $cart.totals.total_excluding_tax.amount > 0}
                {math equation='(a-c*(a/b))/(b-c)' a=$cart.totals.total_including_tax.amount b=$cart.totals.total_excluding_tax.amount c=$cart.subtotals.shipping.amount assign='product_tax_rate'}
                {if $product_tax_rate > 0.1}
                    {assign var=ps_freeshipping_price value=$ps_freeshipping_price/$product_tax_rate}
                {/if}
            {/if}

            {math equation='a-b' a=$cart.totals.total.amount b=$cart.subtotals['payment-fee'].amount|default:0 assign='total_without_payment_fee'}
            {math equation='a-b' a=$total_without_payment_fee b=$cart.subtotals.shipping.amount assign='total_without_shipping'}
            {math equation='a-b' a=$ps_freeshipping_price b=$total_without_shipping assign='remaining_to_spend'}
            {math equation='(100*a)/b' a=$total_without_shipping b=$ps_freeshipping_price assign='completed_percentage'}
            {if $remaining_to_spend > 0}
              <div class="remaining-amount-to-free-shipping-container{if isset($cart.subtotals) && isset($cart.subtotals.shipping) && isset($cart.subtotals.shipping.amount) && $cart.subtotals.shipping.amount == 0} free{/if}">
                <div class="remaining-amount-msg">{l s='Remaining amount to get free shipping: ' mod='thecheckout'} <span class="remaining-amount">{Tools::displayPrice($remaining_to_spend,$currency)}</span></div>
                <div class="remaining-amount-progress">
                  <div class="inside-bar" style="width: {$completed_percentage|escape:'javascript':'UTF-8'}%"></div>
                </div>
              </div>
            {/if}
        {/if}

        {if $waitForShippingCls}
        <div class="cart-summary-line please-select-shipping">
          <span class="label">{l s='Please select a shipping method' mod='thecheckout'}</span>
        </div>
      {/if}
      <div class="cart-summary-line cart-total-weight hidden">
        <span class="label">{l s='Total weight' mod='thecheckout'}</span>
        <span class="value"></span>
      </div>

    </div>

    {*<hr class="separator">*}
  </div>
{/block}
