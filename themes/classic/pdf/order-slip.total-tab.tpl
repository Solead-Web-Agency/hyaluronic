{**
 * [HFM] Totaux de l'avoir calculés sur l'AVOIR (montants remboursés), pas sur la commande entière
 * comme le modèle d'origine (faux sur un remboursement partiel). HT, TVA et TTC, comme la facture.
 *}
{assign var=slip_ht value=$order_slip->total_products_tax_excl + $order_slip->total_shipping_tax_excl}
{assign var=slip_ttc value=$order_slip->total_products_tax_incl + $order_slip->total_shipping_tax_incl}
<table id="total-tab" width="100%">
	<tr>
		<td class="grey" width="60%">{l s='Total Products' d='Shop.Pdf' pdf='true'}</td>
		<td class="white" width="40%">- {displayPrice currency=$order->id_currency price=$order_slip->total_products_tax_excl}</td>
	</tr>
	{if $order_slip->total_shipping_tax_excl > 0}
		<tr>
			<td class="grey" width="60%">{l s='Shipping Costs' d='Shop.Pdf' pdf='true'}</td>
			<td class="white" width="40%">- {displayPrice currency=$order->id_currency price=$order_slip->total_shipping_tax_excl}</td>
		</tr>
	{/if}
	<tr class="bold">
		<td class="grey" width="60%">{l s='Total (Tax Excl.)' d='Shop.Pdf' pdf='true'}</td>
		<td class="white" width="40%">- {displayPrice currency=$order->id_currency price=$slip_ht}</td>
	</tr>
	{if ($slip_ttc - $slip_ht) > 0.004}
		<tr class="bold">
			<td class="grey" width="60%">{l s='Total Tax' d='Shop.Pdf' pdf='true'}</td>
			<td class="white" width="40%">- {displayPrice currency=$order->id_currency price=($slip_ttc - $slip_ht)}</td>
		</tr>
	{/if}
	<tr class="bold big">
		<td class="grey" width="60%">{l s='Total (Tax Incl.)' d='Shop.Pdf' pdf='true'}</td>
		<td class="white" width="40%">- {displayPrice currency=$order->id_currency price=$slip_ttc}</td>
	</tr>
</table>
