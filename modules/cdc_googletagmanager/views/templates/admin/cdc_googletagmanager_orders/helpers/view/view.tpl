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

{if empty($gtm_order_log)}
	<div class="alert alert-danger">{l s='No GTM Order log for this order. The order is probably too old (before the installation of the module).' mod='cdc_googletagmanager'}</div>
{else}

	<div class="panel">
		<div class="panel-heading">Log Google Tag Manager</div>

		<table class="table table-responsive">
			<tbody>
			<tr>
				<th>id log</th>
				<td>{$gtm_order_log->id_cdc_gtm_order_log|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>order</th>
				<td>{$gtm_order_log->id_order|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>refund</th>
				<td>{$gtm_order_log->refund|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>sent</th>
				<td>{$gtm_order_log->sent|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>re-sent</th>
				<td>{$gtm_order_log->resent|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>date_add</th>
				<td>{$gtm_order_log->date_add|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<th>date_upd</th>
				<td>{$gtm_order_log->date_upd|escape:'htmlall':'UTF-8'}</td>
			</tr>
			</tbody>
		</table>
	</div>

	{if $force_resend}
	<div class="alert alert-info">
		<p><b>Force re-send OK</b></p>
		<p>Data will be sent to Google Analytics</p>
	</div>
	{/if}

	{if $action_created}
		<div class="alert alert-info">
			<p><b>Datalayer creation</b></p>
			<p>The datalayer will be created and sent to Google Tag Manager</p>
		</div>
	{/if}


	<div class="panel">
		<div class="panel-heading">Datalayer raw</div>
		{if !empty($gtm_order_log->datalayer)}
			<pre>{$gtm_order_log->datalayer|escape:'htmlall':'UTF-8'}</pre>
		{else}
			<p>No datalayer saved</p>
		{/if}
	</div>

	{if !empty($gtm_order_log->datalayer)}
		<div class="panel">
			<div class="panel-heading">Datalayer JS formatted</div>
			<pre id="datalayer_formatted">
			loading ...
			</pre>
		</div>
	{else}
		{* no gtm order log, action to re-create it *}
		<div class="panel">
			<div class="panel-heading">Missing datalayer</div>

			<p>{l s='Sometimes, the datalayer is not generated. You can find here the main reasons and how to fix it.' mod='cdc_googletagmanager'}</p>
			<br />

			<table class="table">
				<tr>
					<th>{l s='Why?' mod='cdc_googletagmanager'}</th>
					<th>{l s='How to fix it?' mod='cdc_googletagmanager'}</th>
				</tr>

				<tr>
					<td>{l s='Customers are not redirected to your shop right after the payment' mod='cdc_googletagmanager'}</td>
					<td>
						<ul>
							<li>{l s='Contact your payment service provider to adjust the payment module configuration in order that the redirection to your shop is automatic and with no delay' mod='cdc_googletagmanager'}</li>
						</ul>
					</td>
				</tr>

				<tr>
					<td>{l s='The order confirmation page of the payment module is not recognized by the module.' mod='cdc_googletagmanager'}</td>
					<td>
						<ul>
							<li>{l s='If it\'s possible, please configure your payment module to redirect your customers to the standard Prestashop "order confirmation" page.' mod='cdc_googletagmanager'}</li>
							<li>{l s='If it\'s not possible, contact our customer service so we can analyze how to make your payment module compatible.' mod='cdc_googletagmanager'}</li>
						</ul>
					</td>
				</tr>
			</table>

			<br /><br />
			<p>{l s='While your "missing order confirmation datalayer problem" is not solved, you can still generate and send the datalayer of this order from the backoffice.' mod='cdc_googletagmanager'}</p>
			<p>{l s='It will send to same datalayer to Google Tag Manager as the one sent on the order confirmation page, the only difference will be the user context (browsing history, time on page ...) which is handled by Google Tag Manager and Google Analytics.' mod='cdc_googletagmanager'}</p>
			<br />
			<p><a href="{$action_create|escape:'htmlall':'UTF-8'}" class="btn btn-default">{l s='Generate datalayer and send order' mod='cdc_googletagmanager'}</a></p>
			<div class="alert alert-warning">
				<p>Warning about datalayer re-creation:</p>
				<ul>
					<li>The date of the order won't be correct in GA, the current date will be used instead.</li>
					<li>The referrer will be set to null.</li>
				</ul>
			</div>
		</div>
	{/if}



	{if $gtm_order_log->id_cdc_gtm_order_log && !empty($gtm_order_log->datalayer) && !$force_resend}
	<div class="panel">
		<div class="panel-heading">Force re-send</div>
		{if !$gtm_order_log->resent}
			<p>If the order does not appear in Google Analytics after 48H, you can re-send this order.</p>
			<p><a href="{$action_resend|escape:'htmlall':'UTF-8'}" class="btn btn-default">Force re-send order</a></p>
			<div class="alert alert-warning">
				<p>Warning about force re-send:</p>
				<ul>
					<li>The date of the order won't be correct in GA, the current date will be used instead.</li>
					<li>The referrer will be set to null.</li>
					<li>If the order has already been sent to GA, it will be duplicated.</li>
				</ul>
			</div>
		{else}
			<p><b>Order already re-sent.</b></p>
			<p>If the order does not appear in Google Analytics after 48H, this order may contains some errors and cannot be exported to Google Analytics.</p>
			<p>Remember that Google Analytics is an analysis tool and some data can be slightly different from reality. If this error occurs frequently, please contact our support.</p>
		{/if}
	</div>
	{/if}

	<script data-keepinline="true">
	var dataLayer_preview = [];
	dataLayer_preview.push({$gtm_order_log->datalayer nofilter});
	console.log(dataLayer_preview);
	$("#datalayer_formatted").text(JSON.stringify(dataLayer_preview, null, 4));
	//alert(JSON.stringify(dataLayer_preview, null, 4));
	</script>

{/if}

<p><a href="{$link_cdcgtm_order_logs|escape:'htmlall':'UTF-8'}" class="btn btn-default">{l s='Return to GTM Order log list' mod='cdc_googletagmanager'}</a></p>