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
{extends file='page.tpl'}
{block name='page_content'}

<div class="col-md-12">
	<h1 class="page-heading">{$revolut_payment_title|escape:'htmlall':'UTF-8'}</h1>
</div>

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Shopping cart is empty.' mod='revolutpayment'}</p>
{else}
	<div class="row">
		<div class="col-md-6">
			{if $payment_description != ''}
				<div class="col-md-12">
					<p>{$payment_description|escape:'htmlall':'UTF-8'}</p>
				</div>
			{/if}
			<div class="col-md-10">
				<div id="revolut_card"></div>
			</div>
			<div class="col-md-2">
				<button id="revolutPayButton"  type="button" class="btn btn-primary center-block" data-widget-type="deticated-page">
					<span>{l s='Pay' mod='revolutpayment'} <i class="icon-chevron-right right"></i></span>
				</button>
			</div>
			<div class="col-md-10">
				<div class="revolutPaymentPageErrors error hidden"></div>
			</div>
		</div>
	</div>

	<form id="revolutForm" method="post" action="{$controller_link|escape:'htmlall':'UTF-8'}">
		<input type="hidden" name="revolut_payment_title" id="revolutPaymentTitle" value="{$revolut_payment_title|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="merchant_type" id="revolutMerchantType" value="{$merchant_type|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="public_id" id="revolutPublicId" value="{$public_id|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="email" value="{$customer_email|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="customer_name" value="{$customer_name|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="country" value="{$country->iso_code|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="state" value="{$state->iso_code|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="city" value="{$address->city|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="line1" value="{$address->address1|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="line2" value="{$address->address2|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="postal" value="{$address->postcode|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" id="revolutLocale" name="locale" value="{$locale|escape:'htmlall':'UTF-8'}"/>

	</form>
{/if}
{/block}