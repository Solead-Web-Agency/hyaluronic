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
{if !empty($aTmpRules)}
	<div id="gmc">

		<div class="clr_10"></div>
		<div class="clr_hr"></div>
		<div class="clr_10"></div>

		{assign var='nbItemsPerLine' value=3}

		<div class="panel-group">
			<div class="panel panel-info">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapse1"><i class="fa fa-cogs"></i>&nbsp;<span class="badge badge-success">{$aTmpRules|@count}</span>  <b>{l s='exclusion rule(s) based on the element type(s) below - (Click to see detail)' mod='gmerchantcenterpro'}</b> </a>
					</h4>
				</div>
				<div id="collapse1" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="row">
							{foreach from=$aTmpRules item=sTmpRules key=sKey name=Rules}
								{math equation="(iteration%perLine)" iteration=$smarty.foreach.Rules.iteration perLine=$nbItemsPerLine assign=totModulo}
								<div class="col-md-3">
									<div class="panel panel-btRules">
										<div class="panel-heading">
											<h3 class="panel-title">
												<i class="fa fa-thumb-tack text-success pull-left"></i>{$sTmpRules.data}
											</h3>
											<div class="clr_5"></div>
											<div class="clr_hr"></div>
											<div class="clr_5"></div>
											<h5 class="text-center">
												{foreach from=$sTmpRules.filter item=sDetail key=sKey name=RulesDetail}
													{if $sKey != 'iNumberOfProducts' && $sKey != 'iCheckedTreeElem' && $sKey != 'iCatId'
														&& $sKey != 'iManufacturerId' && $sKey != 'iSupplierId'  && $sKey != 'aCatName'
														&& $sKey != 'aManufacturerName' && $sKey != 'aSupplierName'}
														{$sDetail}
													{/if}
												{/foreach}
											</h5>
											<div class="row">
												<div class="col-xs-6">
													{foreach from=$sTmpRules.filter item=sDetail key=sKey name=RulesDetail}
														{if $sKey == 'filter_2'}
															<span class="badge badge-success label-tooltip" title="{l s='Number of elements affected by this exclusion rule' mod='gmerchantcenterpro'}"><i class="fa fa-check-square-o"></i> {$sDetail|@count}</span>
														{/if}
														{*Use case for tree element*}
														{if $sKey == 'iCheckedTreeElem'}
															<span class="badge badge-success label-tooltip" title="{l s='Number of elements affected by this exclusion rule' mod='gmerchantcenterpro'}"><i class="fa fa-check-square-o"></i> {$sDetail|escape:'htmlall':'UTF-8'}</span>
														{/if}
													{/foreach}
												</div>
												<div class="col-xs-6">
													{foreach from=$sTmpRules.filter item=sDetail key=sKey name=RulesDetail}
														{if $sKey == 'iNumberOfProducts'}
															<span class="badge badge-info pulse pulse2 pull-right label-tooltip" title="{l s='Number of products affected by this exclusion rule' mod='gmerchantcenterpro'}"><i class="icon icon-AdminCatalog"></i> {$aProducts|@count} </span>
														{/if}
													{/foreach}
												</div>
											</div>

											<div class="col-xs-12">
												{if !empty($sTmpRules.filter.aCatName) &&  !empty($sTmpRules.filter.iCatId)}
													<div class="clr_5"></div>
													<table class="table table-responsive">
														<tr>
															<td><b>{l s='Categories for this rule'  mod='gmerchantcenterpro'}</b></td>
														</tr>
														{foreach from=$sTmpRules.filter.aCatName item=sCatName key=sKey name=RulesDetail}
																<tr>
																	<td>{$sCatName|escape:'htmlall':'UTF-8'}</td>
																</tr>
														{/foreach}
													</table>
												{/if}

												{if !empty($sTmpRules.filter.aManufacturerName) &&  !empty($sTmpRules.filter.iManufacturerId)}
													<div class="clr_5"></div>
													<table class="table table-responsive">
														<tr>
															<td><b>{l s='Manufacturers for this rule'  mod='gmerchantcenterpro'}</b></td>
														</tr>
														{foreach from=$sTmpRules.filter.aManufacturerName item=sManufacturerName key=sKey name=RulesDetail}
																<tr>
																	<td>{$sManufacturerName|escape:'htmlall':'UTF-8'}</td>
																</tr>
														{/foreach}
													</table>
												{/if}

												{if !empty($sTmpRules.filter.aSupplierName) &&  !empty($sTmpRules.filter.iSupplierId)}
													<div class="clr_5"></div>
													<table class="table table-responsive">
														<tr>
															<td><b>{l s='Suppliers for this rule' mod='gmerchantcenterpro'}</b></td>
														</tr>
														{foreach from=$sTmpRules.filter.aSupplierName item=sSupplierName key=sKey name=RulesDetail}
																<tr>
																	<td>{$sSupplierName|escape:'htmlall':'UTF-8'}</td>
																</tr>
														{/foreach}
													</table>
												{/if}
											</div>

											<div class="clr_5"></div>
											<div class="clr_hr"></div>
											<div class="clr_5"></div>

											<div class="row">
												<a  class="btn btn-mini btn-success pull-left" onclick=" $('#bt-exclusion-type').val('{$sTmpRules.sType}').trigger('onchange')"><i class="fa fa-edit"></i> </a>
												<a class="btn btn-mini btn-danger pull-right" onclick="$('#loadingGoogleRulesDiv').show();var iRuleId = {$sTmpRules.id};oGmcPro.ajax('{$sURI|escape:'htmlall':'UTF-8'}', '{$sCtrlParamName|escape:'htmlall':'UTF-8'}={$sController|escape:'htmlall':'UTF-8'}&sAction={$aQueryParams.rulesSummary.action|escape:'htmlall':'UTF-8'}&sType={$aQueryParams.rulesSummary.type|escape:'htmlall':'UTF-8'}&sTmpRules=true&sDelete=true&iRuleId='+ iRuleId, 'rules', 'rules', null, null, 'loadingGoogleRulesDiv');" id="btn-add-condition"><i class="fa fa-trash"></i> </a>
											</div>
										</div>
									</div>
								</div>
								{if $nbItemsPerLine > 1 && !$smarty.foreach.Rules.last && $totModulo != 0}
									<div class="col-xs-1 text-center">
										<div class="clr_30"></div>
										<i class=" icon text-success icon-2x icon-plus-circle"></i>
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>

		{if !empty($aProducts)}
			<div class="panel-group">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" href="#collapse2"><i class="icon icon-AdminCatalog"></i>&nbsp;<span class="badge badge-success">{$aProducts|@count}</span>&nbsp;<b>{l s='of distinct product(s) affected - (Click to see detail)' mod='gmerchantcenterpro'}</b></a>
						</h4>
					</div>
					<div id="collapse2" class="panel-collapse collapse">
						<div class="panel-body">
							<table class="table table-bordered">
								<thead>
								<th class="text-center"><b>{l s='id' mod='gmerchantcenterpro'}</b></th>
								<th class="text-center"><b>{l s='Product name' mod='gmerchantcenterpro'}</b></th>
								</thead>
								<tbody>
								{foreach from=$aProducts item=aProdcut key=sKey}
									<tr class="text-center">
										<td> {$aProdcut.id|escape:'htmlall':'UTF-8'} </td>
										<td> {$aProdcut.name|escape:'htmlall':'UTF-8'}</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		{/if}
{/if}

<div class="clr_10"></div>
<div class="clr_hr"></div>
<div class="clr_10"></div>

<div class="row">
	<div class="col-xs-6">
		<div id="{$sModuleName|escape:'htmlall':'UTF-8'}ExclusionRulesError"></div>
	</div>
	<div class="col-xs-6">
		<input {if empty($aTmpRules)} disabled="disabled" {/if}  type="button" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="pull-left btn btn-success btn-lg" value="{if !empty($iRuleId)}{l s='Modify' mod='gmerchantcenterpro'}{else}{l s='Validate' mod='gmerchantcenterpro'}{/if}" onclick="oGmcPro.form('bt_form-exclusion-rules', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_feed-exclusion-form', 'bt_feed-exclusion-form', false, true, oCustomCallBack, 'ExclusionRules', 'loadingCustomTagDiv');return false;" />
	</div>
</div>

{literal}
<script type="text/javascript">
	{/literal}{if !empty($bAjaxMode)}{literal}
	$('.label-tooltip, .help-tooltip').tooltip();
	{/literal}{/if}{literal}
</script>
{/literal}