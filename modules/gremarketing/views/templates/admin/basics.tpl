{*
*
* Google Dynamic Remarketing
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

<form action="{$sURI|escape:'htmlall':'UTF-8'}" method="post" class="form-horizontal col-xs-12" id="bt_basics-form" name="bt_basics-form" onsubmit="oGr.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, null, 'basics', 'basics');return false;">
	<input type="hidden" name="sAction" value="{$aQueryParams.basic.action|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="sType" value="{$aQueryParams.basic.type|escape:'htmlall':'UTF-8'}" />

	<h3><i class="icon-heart"></i>&nbsp;{l s='Google Remarketing Settings' mod='gremarketing'}</h3>

	{if empty($bCompare16)}<div class="clr_20"></div>{/if}

	{if !empty($bUpdate)}
		{include file="`$sConfirmInclude`"}
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}

	<div class="form-group">
		<label class="control-label col-xs-12 col-md-3 col-lg-2"><span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='You will find this in the e-mail with the JavaScript code sent by Google. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gremarketing'}"><b>{l s='Add your Google Conversion ID' mod='gremarketing'}</b></span> :</label>
		<div class="col-xs-12 col-lg-5">
			<input type="text" id ="bt_google-id" name="bt_google-id" value="{if !empty($iGoogleId)}{$iGoogleId}{/if}" placeholder="AW-123456789" />
		</div>
		<span class="label-tooltip" data-toggle="tooltip" title data-original-title="{l s='You will find this in the e-mail with the JavaScript code sent by Google. Please refer to the documentation for details (visit the Help / FAQ tab).' mod='gremarketing'}">&nbsp;<span class="icon-question-sign"></span></span>
		<a class="badge badge-info pulse pulse2" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/167"><span class="icon icon-link"></span>&nbsp;&nbsp;{l s='Where can I find my conversion ID' mod='gremarketing'}</a>
	</div>

	<div class="clr_20"></div>

	<div class="form-group" id="bootstrap-bouton">
		<label class="control-label col-xs-12 col-md-5 col-lg-2"><b>{l s='Activate user_id ?' mod='gremarketing'}</b> :</label>
		<div class="col-xs-10">
			<div class="fixed-width-md {if empty($bCompare16)} col-lg-10{/if}">
				<span class="switch prestashop-switch fixed-width-md">
					<input type="radio" name="bt_user_id" id="bt_user_id_on" value="1" {if !empty($bUserId|intval)}checked="checked"{/if} />
					<label for="bt_user_id_on" class="radioCheck btn-yes">
						{l s='Yes' mod='gremarketing'}
					</label>
					<input type="radio" name="bt_user_id" id="bt_user_id_off" value="0" {if empty($bUserId|intval)}checked="checked"{/if} />
					<label for="bt_user_id_off" class="radioCheck btn-no">
						{l s='No' mod='gremarketing'}
					</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
			&nbsp;<a class="badge badge-info pulse pulse2" target="_blank" href="{$smarty.const._GR_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/226"><span class="icon icon-link"></span>&nbsp;&nbsp;{l s='Do I have to include the user_id tag ?' mod='gremarketing'}</a>
		</div>
	</div>

	<div class="clr_10"></div>
	<div class="clr_hr"></div>
	<div class="clr_10"></div>

	<div class="center">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-11 col-lg-11">
				<div class="adminErrors" id="bt_error-basics"></div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
				<button  class="btn btn-info pull-right" onclick="oGr.form('bt_basics-form', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_basics-settings', 'bt_basics-settings', false, false, null, 'basics', 'basics');return false;"><i class="process-icon-save"></i>{l s='Save' mod='gremarketing'}</button>
			</div>
		</div>
	</div>

</form>

<script type="text/javascript">
	//bootstrap components init
	{if !empty($bAjaxMode)}
		$('.label-tooltip, .help-tooltip').tooltip();
		$('.dropdown-toggle').dropdown();
	{/if}
</script>