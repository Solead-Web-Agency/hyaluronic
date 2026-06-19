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
<table id="summary-tab" width="100%">
	<tr>
        <th class="header small" valign="middle">
            {if $is_credit_note}
                {l s='Credit Note Number' mod='amazon'}
            {else}
                {l s='Invoice Number' mod='amazon'}
            {/if}
        </th>
        <th class="header small" valign="middle">
            {if $is_credit_note}
                {l s='Credit Note Date' mod='amazon'}
            {else}
                {l s='Invoice Date' mod='amazon'}
            {/if}
        </th>
        {* VIDR: Replace Order Reference / Order data by Shipping Reference / Shipping date *}
		<th class="header small" valign="middle">{l s='Order Reference' mod='amazon'}</th>
		<th class="header small" valign="middle">{l s='Shipping date' mod='amazon'}</th>
	</tr>
	<tr>
		<td class="center small white">{$title|escape:'html':'UTF-8'}</td>
        <td class="center small white">{dateFormat date=$now full=0}</td>
		<td class="center small white">{$vidr_order_refs|escape:'htmlall':'UTF-8'}</td>
		<td class="center small white">{dateFormat date=$vidr_shipment_date full=0}</td>
	</tr>
</table>
