{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 *}
<table class="product" width="100%" cellpadding="4" cellspacing="0">

	<thead>
	<tr>
		<th class="product header small" width="{$layout.reference.width|escape:'htmlall':'UTF-8'}%">{l s='Reference' mod='amazon'}</th>
		<th class="product header small" >{l s='Qty' mod='amazon'}</th>
		<th class="product header small" >{l s='Item' mod='amazon'}</th>
		<th class="product header small" >{l s='Promotion' mod='amazon'}</th>
		<th class="product header small" >{l s='Gift' mod='amazon'}</th>
		<th class="product header small" >{l s='Promotion' mod='amazon'}</th>
		<th class="product header small" >{l s='Shipping' mod='amazon'}</th>
		<th class="product header small" >{l s='Promotion' mod='amazon'}</th>
		
	</tr>
	</thead>

	<tbody>

	<!-- PRODUCTS -->
	{foreach $order_details as $order_detail}
		{cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
		<tr class="product {$bgcolor_class|escape:'htmlall':'UTF-8'}">
            <td class="product center">
                {$order_detail.product_reference|escape:'htmlall':'UTF-8'}
            </td>

            <td class="product center">
                {$order_detail.product_quantity|escape:'htmlall':'UTF-8'}
            </td>

            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.item_vat}
            </td>

            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.item_promo_vat}
            </td>

            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.gift_vat}
            </td>

            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.gift_promo_vat}
            </td>
            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.shipping_vat}
            </td>

            <td class="product center">
                {displayPrice currency=$order->id_currency price=$order_detail.vat.shipping_promo_vat}
            </td>
		</tr>

	{/foreach}
	<!-- END PRODUCTS -->

	</tbody>

</table>
