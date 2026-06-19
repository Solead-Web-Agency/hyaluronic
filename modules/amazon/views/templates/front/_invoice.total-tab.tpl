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
<table id="total-tab" width="100%">

	<tr>
		<td class="grey" width="50%">
			{l s='Total Products' mod='amazon'}
		</td>
		<td class="white" width="50%">
			{displayPrice currency=$order->id_currency price=$footer.products_before_discounts_tax_excl}
		</td>
	</tr>

	{if $footer.product_discounts_tax_excl > 0}

		<tr>
			<td class="grey" width="50%">
				{l s='Total Discounts' mod='amazon'}
			</td>
			<td class="white" width="50%">
				- {displayPrice currency=$order->id_currency price=$footer.product_discounts_tax_excl}
			</td>
		</tr>

	{/if}
	{if !$order->isVirtual()}
	<tr>
		<td class="grey" width="50%">
			{l s='Shipping Costs' mod='amazon'}
		</td>
		<td class="white" width="50%">
			{if $footer.shipping_tax_excl > 0}
				{displayPrice currency=$order->id_currency price=$footer.shipping_tax_excl}
			{else}
				{l s='Free Shipping' mod='amazon'}
			{/if}
		</td>
	</tr>
	{/if}

	{if $footer.wrapping_tax_excl > 0}
		<tr>
			<td class="grey">
				{l s='Wrapping Costs' mod='amazon'}
			</td>
			<td class="white">{displayPrice currency=$order->id_currency price=$footer.wrapping_tax_excl}</td>
		</tr>
	{/if}

	<tr class="bold">
		<td class="grey">
			{l s='Total (Tax excl.)' mod='amazon'}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_excl}
		</td>
	</tr>
	{if $footer.total_taxes > 0}
	<tr class="bold">
		<td class="grey">
			{l s='Total Tax' mod='amazon'}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_taxes}
		</td>
	</tr>
	{/if}
	<tr class="bold big">
		<td class="grey">
			{l s='Total' mod='amazon'}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}
		</td>
	</tr>
</table>
