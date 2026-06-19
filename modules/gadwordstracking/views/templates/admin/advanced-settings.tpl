{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}
<form action="{$sURI|escape:'htmlall':'UTF-8'}" class="col-xs-12 form-horizontal" method="post" id="bt_advanced-form" name="bt_advanced-form" onsubmit="oGact.form('bt_advanced-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings', 'bt_advanced-settings', false, false, '', 'advanced', 'advanced');return false;">
	<input type="hidden" name="sAction" value="{$aQueryParams.advanced.action|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="sType" value="{$aQueryParams.advanced.type|escape:'htmlall':'UTF-8'}" />

	<h3><i class="icon-home"></i>&nbsp;{l s='Price configuration' mod='gadwordstracking'}</h3>

	{if !empty($bUpdate)}
		{include file="`$sConfirmInclude`"}
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}

	<div class="form-group">
		<div class="col-xs-12 col-md-5 col-lg-6">
			<div class="alert alert-info">{l s='These options below allow you to manage accurately the final price that the module will display. By default, the price is displayed with tax included and the others options are also activated.' mod='gadwordstracking'}</div>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-2">
			<b>{l s='Tax included' mod='gadwordstracking'}</b> :
		</label>
		<div class="col-xs-12 col-md-5 col-lg-6">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="bt_use-tax" id="bt_use-tax_on" value="1" {if !empty($bUseTax)}checked="checked"{/if} />
				<label for="bt_use-tax_on" class="radioCheck">
					{l s='Yes' mod='gadwordstracking'}
				</label>
				<input type="radio" name="bt_use-tax" id="bt_use-tax_off" value="0" {if empty($bUseTax)}checked="checked"{/if} />
				<label for="bt_use-tax_off" class="radioCheck">
					{l s='No' mod='gadwordstracking'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>

	<div class="clr_20"></div>

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-2">
			<b>{l s='Shipping cost included' mod='gadwordstracking'}</b> :
		</label>
		<div class="col-xs-12 col-md-5 col-lg-6">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="bt_use-shipping" id="bt_use-shipping_on" value="1" {if !empty($bUseShipping)}checked="checked"{/if} />
				<label for="bt_use-shipping_on" class="radioCheck">
					{l s='Yes' mod='gadwordstracking'}
				</label>
				<input type="radio" name="bt_use-shipping" id="bt_use-shipping_off" value="0" {if empty($bUseShipping)}checked="checked"{/if} />
				<label for="bt_use-shipping_off" class="radioCheck">
					{l s='No' mod='gadwordstracking'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>

	<div class="clr_20"></div>

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-2">
			<b>{l s='Wrapping cost included' mod='gadwordstracking'}</b> :
		</label>
		<div class="col-xs-12 col-md-5 col-lg-6">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="bt_use-wrapping" id="bt_use-wrapping_on" value="1" {if !empty($bUseWrapping)}checked="checked"{/if} />
				<label for="bt_use-wrapping_on" class="radioCheck">
					{l s='Yes' mod='gadwordstracking'}
				</label>
				<input type="radio" name="bt_use-wrapping" id="bt_use-wrapping_off" value="0" {if empty($bUseWrapping)}checked="checked"{/if} />
				<label for="bt_use-wrapping_off" class="radioCheck">
					{l s='No' mod='gadwordstracking'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>

	<div class="clr_10"></div>
	<div class="clr_hr"></div>
	<div class="clr_10"></div>

	<div class="center">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
				<div class="adminErrors" id="bt_error-advanced"></div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
				<button  class="btn btn-default pull-right" onclick="oGact.form('bt_advanced-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-settings', 'bt_advanced-settings', false, false, '', 'advanced', 'advanced');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gadwordstracking'}</button>
			</div>
		</div>
	</div>



	<div class="clr_20"></div>
</form>
<script type="text/javascript">
	//bootstrap components init
	{if !empty($bAjaxMode)}
	$('.label-tooltip, .icon-question-sign').tooltip();
	$('.dropdown-toggle').dropdown();
	{/if}
</script>