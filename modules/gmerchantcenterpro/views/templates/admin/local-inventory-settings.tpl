{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}


<div class="bootstrap">
	<form class="form-horizontal col-xs-12" action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" id="bt_local_iventorry" name="bt_local_iventorry" {if $smarty.const._GMCP_USE_JS == true}onsubmit="javascript: oGmcPro.form('bt_local_iventorry', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_local_inventory_div', 'bt_local_inventory_div', false, false, '', 'LocalIventory', 'loadingAdvancedDiv');return false;"{/if}>
		<input type="hidden" name="sAction" value="{$aQueryParams.local_inventory.action|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sType" value="{$aQueryParams.local_inventory.type|escape:'htmlall':'UTF-8'}" />

		<h3 class="subtitle"><i class="fa fa-shopping-cart"></i>&nbsp;{l s='Local Product Inventory' mod='gmerchantcenterpro'}</h3>
		<div class="clr_10"></div>
		{if !empty($bUpdate)}
			{include file="`$sConfirmInclude`"}
		{elseif !empty($aErrors)}
			{include file="`$sErrorInclude`"}
		{/if}

        <div class="alert alert-info">
            {l s='Promote the products in stock at your local store with the local product inventory feed.' mod='gmerchantcenterpro'}&nbsp;<a class="badge badge-info" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/460" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='Learn more about Local Inventory Ads' mod='gmerchantcenterpro'}</a>
			<br /><br />
			{l s='Please read' mod='gmerchantcenterpro'}&nbsp;<a class="badge badge-info" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/461" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='our FAQ' mod='gmerchantcenterpro'}</a>&nbsp;{l s='to know how to configure a local product inventory feed.' mod='gmerchantcenterpro'} 

		</div>
        <div class="form-group">
			<label class="control-label col-xs-12 col-md-3 col-lg-3">
				<span><b>{l s='Your store code:' mod='gmerchantcenterpro'}</b></span></label>
			<div class="col-xs-4 col-md-4 col-lg-2">
				<input type="text" name="bt_store_code" value="{$sStoreCode|escape:'htmlall':'UTF-8'}" />
			</div>
			<a class="badge badge-info" href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/453" target="_blank"><i class="icon icon-link"></i>&nbsp;{l s='FAQ about store code' mod='gmerchantcenterpro'}</a>
		</div>

        <div class="form-group">
			<label class="control-label col-xs-12 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Specify the store pickup option for your items.' mod='gmerchantcenterpro'}"><b>{l s='Store pickup method' mod='gmerchantcenterpro'}</b></span></label>
			<div class="col-xs-12 col-md-3 col-lg-2">
				<select name="bt_lia_pickup">';
					{foreach from=$aLiaPickup item=aPickUp}
						<option value="{$aPickUp.value|escape:'htmlall':'UTF-8'}" {if $aPickUp.value == $sLiaPikcup}selected="selected"{/if}>{$aPickUp.label|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
		</div>

        <div class="form-group">
			<label class="control-label col-xs-12 col-md-3 col-lg-3">
				<span class="label-tooltip" title="{l s='Specify the expected date that an order will be ready for pickup, relative to when the order is placed.' mod='gmerchantcenterpro'}"><b>{l s='Store pickup timeline' mod='gmerchantcenterpro'}</b></span></label>
			<div class="col-xs-12 col-md-3 col-lg-2">
				<select name="bt_lia_pickup_sla">';
					{foreach from=$aLiaPickupSla item=aPickUpSla}
						<option value="{$aPickUpSla.value|escape:'htmlall':'UTF-8'}" {if $aPickUpSla.value == $sLiaPikcupSla}selected="selected"{/if}>{$aPickUpSla.label|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
		</div>

        <div class="center">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
					<div id="{$sModuleName|escape:'htmlall':'UTF-8'}LocalIventoryError"></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
					<button  class="btn btn-info pull-right" onclick="oGmcPro.form('bt_local_iventorry', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_local_inventory_div', 'bt_local_inventory_div', false, false, '', 'LocalIventory', 'loadingAdvancedDiv', false, 1);return false;"><i class="process-icon-save"></i>{l s='Save' mod='gmerchantcenterpro'}</button>
				</div>
			</div>
		</div>

    </form>
</div>