{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
<form novalidate="" enctype="multipart/form-data" method="post" action="#" class="defaultForm form-horizontal config_carrier_modules" id="module_form ">
<style>
	{literal}
		.form_input{
			background: #f5f8f9 none repeat scroll 0 0;
			border: 1px solid #c7d6db;
			border-radius: 3px;
			height: 89px;
			width: 186px;
			overflow: auto;
		}
		.carrier_area{
			border-bottom: 1px solid #c4c4c4;
			display: inline-block;
			margin: 0 !important;
			padding: 5px 0 5px 10px;
			width: 100%;
		}
		.warning{
			width:100%;
			text-align:center;
			font-size:16px;
			color:green;
			margin:10px 0;
		}
		.message_infor_box input, .message_infor_box select, .message_infor_box .carrier_area {
			color: #777;
			font-weight: 500;
		}
		.message_infor .form-group {
			margin-bottom:0px;
			display:inline-block;
			width:100%;
		}
	{/literal}
</style>

<div class='message_infor_box bootstrap ' style='display:block; overflow:auto'>
	<div class="message_infor" style='color:#555'>
		<input type="hidden" value="{$url_root|escape:'htmlall':'UTF-8'}" name="url_root" />
		<input type="hidden" value="{$token|escape:'htmlall':'UTF-8'}" name="token" />
		<input type="hidden" value="{$url_backend|escape:'htmlall':'UTF-8'}" name="url_backend" />
		<div class="panel">
			<div class="panel-heading" style="padding: 0px 0px 8px; border-bottom: 1px solid #dedede;">
				<div style="display:inline-block">
					<img class="img-responsive" src="{$logo|escape:'htmlall':'UTF-8'}" style="float:left">
					<span style="position: absolute; top: 17px; left: 49px;">{if isset($carrier)}{$carrier->name|escape:'htmlall':'UTF-8'}({$carrier->id_carrier|escape:'htmlall':'UTF-8'}){/if}</span>
				</div>
				<span class="panel-heading-act" style="width:43%;">

					<a class="btn btn-default close_popup pull-right" style="margin:0 5px;" name="close_inet">X</a>
				</span>
			</div>
			<div class="warning"></div>
			<div class="form-group" data-tab-id="export">

			</div>
			{if count($carrier_emb_conf) > 0 && $carrier_emb_conf[0].infor}
			<label class="alert alert-info draft col-lg-12 col-xs-12 col-sm-12">
				{$carrier_emb_conf[0].infor|escape:'htmlall':'UTF-8'}
			</label>
			{/if}
			{assign var=carrier_emb_count value=$carrier_emb_conf|count - 1}
			{foreach $carrier_emb_conf as $k => $emb}

				<div class="form-group carrier_emb_conf " data-tab-id="export" {if $embedded_carrier.checked == 0} style="display:none;"{/if}>
				{if isset($emb.label)}
					<label class="control-label col-lg-2">
						{$emb.label|escape:'htmlall':'UTF-8'}
					</label>
					<div class="col-lg-9 ">
						<input type="{if $k == $carrier_emb_count}text{else}text{/if}" name="{$emb.name|escape:'htmlall':'UTF-8'}" class="emb_carrier_{$k|escape:'htmlall':'UTF-8'}" value="{$emb.val|escape:'htmlall':'UTF-8'}" />
					</div>
				{/if}
				</div>
			{/foreach}

			<input type="hidden" name="carrier_emb_count" value="{$carrier_emb_conf|@count|escape:'htmlall':'UTF-8'}">

			<div id="content">
				<a class="btn btn-default save_carrier pull-right" name="save_carrier" >
					<i class="process-icon-save" ></i>
					{l s='Save' mod='deliveryorderautoupdate'}
				</a>
			</div>
		</div>
	</div>
<script src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/js/module_conf.js"></script>
<script src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/js/copy.js"></script>
</div>
</form>