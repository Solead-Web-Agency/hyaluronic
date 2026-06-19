{*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

{if empty($gtm_datalayer)}
	<div class="alert alert-danger">{l s='No datalayer found' mod='cdc_googletagmanager'}</div>
{else}

	<div class="panel">
		<div class="panel-heading">Log DataLayer</div>

		<table class="table table-responsive">
			<tbody>
			<tr>
				<th>id log</th>
				<td>{$gtm_datalayer->id_cdc_gtm_datalayer|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>Event</th>
				<td>{$gtm_datalayer->event|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>URI</th>
				<td>{$gtm_datalayer->uri|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>Shop</th>
				<td>{$gtm_datalayer->id_shop|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>Date</th>
				<td>{$gtm_datalayer->date_add|escape:'htmlall':'UTF-8'}</td>
			</tr>
			</tbody>
		</table>
	</div>

	<div class="panel">
		<div class="panel-heading">Datalayer raw</div>
		{if !empty($gtm_datalayer->datalayer)}
			<pre>{$gtm_datalayer->datalayer|escape:'htmlall':'UTF-8'}</pre>
		{else}
			<p>No datalayer</p>
		{/if}
	</div>

	{if !empty($gtm_datalayer->datalayer)}
		<div class="panel">
			<div class="panel-heading">Datalayer JS formatted</div>
			<pre id="datalayer_formatted">
			loading ...
			</pre>
		</div>
	{/if}


	<script data-keepinline="true">
	var dataLayer_preview = [];
	dataLayer_preview.push({$gtm_datalayer->datalayer nofilter});
	console.log(dataLayer_preview);
	$("#datalayer_formatted").text(JSON.stringify(dataLayer_preview, null, 4));
	//alert(JSON.stringify(dataLayer_preview, null, 4));
	</script>

{/if}

<p><a href="{$link_cdcgtm_datalayer_logs|escape:'htmlall':'UTF-8'}" class="btn btn-default">{l s='Return to GTM Datalayer log list' mod='cdc_googletagmanager'}</a></p>