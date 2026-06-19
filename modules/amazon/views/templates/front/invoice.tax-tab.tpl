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

<!--  TAX DETAILS -->
{if $tax_exempt}

	{l s='Exempt of VAT according to section 259B of the General Tax Code.' mod='amazon'}

{elseif (isset($tax_breakdowns) && $tax_breakdowns)}
	<table id="tax-tab" width="100%">
		<thead>
			<tr>
				<th class="header small">{l s='Tax Detail' mod='amazon'}</th>
				<th class="header small">{l s='Tax Rate' mod='amazon'}</th>
				{if $display_tax_bases_in_breakdowns}
					<th class="header small">{l s='Base price' mod='amazon'}</th>
				{/if}
				<th class="header-right small">{l s='Total Tax' mod='amazon'}</th>
			</tr>
		</thead>
		<tbody>
		{assign var=has_line value=false}

		{foreach $tax_breakdowns as $label => $bd}
			{assign var=label_printed value=false}

			{foreach $bd as $line}
				{if $line.rate == 0}
					{continue}
				{/if}
				{assign var=has_line value=true}
				<tr>
					<td class="white">
						{if !$label_printed}
							{if $label == 'product_tax'}
								{l s='Products' mod='amazon'}
							{elseif $label == 'shipping_tax'}
								{l s='Shipping' mod='amazon'}
							{elseif $label == 'ecotax_tax'}
								{l s='Ecotax' mod='amazon'}
							{elseif $label == 'wrapping_tax'}
								{l s='Wrapping' mod='amazon'}
							{/if}
							{assign var=label_printed value=true}
						{/if}
					</td>

					<td class="center white">
						{$line.rate|escape:'htmlall':'UTF-8'} %
					</td>

					{if $display_tax_bases_in_breakdowns}
						<td class="right white">
							{if isset($is_order_slip) && $is_order_slip}- {/if}
							{displayPrice currency=$order->id_currency price=$line.total_tax_excl}
						</td>
					{/if}

					<td class="right white">
						{if isset($is_order_slip) && $is_order_slip}- {/if}
						{displayPrice currency=$order->id_currency price=$line.total_amount}
					</td>
				</tr>
			{/foreach}
		{/foreach}

		{if !$has_line}
		<tr>
			<td class="white center" colspan="{if $display_tax_bases_in_breakdowns}4{else}3{/if}">
				{l s='No taxes' mod='amazon'}
			</td>
		</tr>
		{/if}

		</tbody>
	</table>

{/if}
<!--  / TAX DETAILS -->
