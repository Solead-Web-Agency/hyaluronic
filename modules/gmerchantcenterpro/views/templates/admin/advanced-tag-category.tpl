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
{if !empty($aErrors)}
{include file="`$sErrorInclude`"}
{* USE CASE - edition review mode *}
{else}
<div id="{$sModuleName|escape:'htmlall':'UTF-8'}" class="bootstrap">
	<div id="bt_advanced-tag" class="col-xs-12 bt_adwords">
		<h3 class="text-center">{l s='Tags assignation for each products category' mod='gmerchantcenterpro'}</h3>
		<div class="clr_hr"></div>
		<div class="clr_20"></div>
		<div class="alert alert-info col-xs-12">
			<p>{l s='WARNING : before starting, please note that the categories displayed below are the DEFAULT categories of your products. The product default category is the one you indicate in the \"Associations\" tab of the back office product sheet (in the \"Default category\" field). So, make sure that your products are correctly assigned to the right default category.' mod='gmerchantcenterpro'}</p>
			<br/>
			<br/>
			<div class="form-group">
			<label class="col-xs-4">
			<b>{l s='Select which type of tags you want to set :' mod='gmerchantcenterpro'}</b>
			</label>
			<div class="col-xs-3">
				<select class="set_tag" name="set_tag" id="set_tag">
					<option value="0">---</option>
					{if !empty($bMaterial)}
						<option value="material">{l s='Set product material tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bPattern)}
						<option value="pattern">{l s='Set product pattern tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bGender)}
						<option value="gender">{l s='Set product gender tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bAgeGroup)}
						<option value="agegroup">{l s='Set product age group tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bTagAdult)}
						<option value="adult">{l s='Set product for adults only tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bSizeType)}
						<option value="sizeType">{l s='Set product size type tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bSizeSystem)}
						<option value="sizeSystem">{l s='Set product size system tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bEnergy)}
						<option value="energy">{l s='Set product energy efficiency class tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bShippingLabel)}
						<option value="shipping_label">{l s='Set product shipping label tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bUnitpricingMeasure)}
						<option value="unit_pricing_measure">{l s='Set unit pricing measure tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bUnitBasepricingMeasure)}
						<option value="base_unit_pricing_measure">{l s='Set unit pricing base measure tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bExcludedDest)}
						<option value="excluded_destination">{l s='Set excluded destination tags' mod='gmerchantcenterpro'}</option>
					{/if}
					{if !empty($bExcludedCountry)}
						<option value="excluded_country">{l s='Set excluded country tags' mod='gmerchantcenterpro'}</option>
					{/if}					
				</select>
			</div>
				<div class="clr_10"></div>
			<div class="bulk-actions">
				<table class="table bg-info">
					<tr id="bulk_action_material">
						<td class="label_tag_categories_value col-xs-6 feature_cat_tag">{l s='Set MATERIAL tags : for each product default category, if available, you will have to indicate the feature that defines the material of the products that are in this category.' mod='gmerchantcenterpro'}</td>
						<td>
							<select name="set_material_bulk_action" class="set_material_bulk_action">
								{foreach from=$aFeatures item=feature}
									<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.material', $('.set_material_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.material', 0);">{l s='Reset' mod='gmerchantcenterpro'}</td>
					</tr>
					<tr id="bulk_action_pattern">
						<td class="label_tag_categories_value col-xs-6">{l s='Set PATTERN tags : for each product default category, if available, you will have to indicate the feature that defines the pattern of the products that are in this category.' mod='gmerchantcenterpro'}</td>
						<td>
							<select name="set_pattern_bulk_action" class="set_pattern_bulk_action">
								{foreach from=$aFeatures item=feature}
									<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.pattern', $('.set_pattern_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.pattern', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>
					<tr id="bulk_action_adult">
						<td class="label_tag_categories_value col-xs-6">{l s='Set AGE GROUP tags : for each product default category, if available, you will have to select, in the drop and down menu, which Google predefined \"age group\" value defines the age group for which the products of this category are reserved. To assign the same tag to all categories, click on one of the opposite buttons  -------->' mod='gmerchantcenterpro'}</td>
						<td>
							<span class="btn btn-default" onclick="oGmcPro.doSet('.agegroup', 'adult');">{l s='Adults (>13y.o)' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.agegroup', 'kids');">{l s='Kids (5-13y.o)' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.agegroup', 'toddler');">{l s='Toddlers (1-5y.o)' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.agegroup', 'infant');">{l s='Infants (3-12m.o)' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default btn-special" onclick="oGmcPro.doSet('.agegroup', 'newborn');">{l s='Newborns (<3m.o) ' mod='gmerchantcenterpro'}</span>

							- <span class="btn btn-default" onclick="oGmcPro.doSet('.agegroup', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span>
						</td>
					</tr>
					<tr id="bulk_action_gender">
						<td class="label_tag_categories_value col-xs-6"> {l s='Set GENDER tags : for each product default category, if available, you will have to select, in the drop and down menu, which Google predefined \"gender\" value defines the gender for which the products of this category are reserved. To assign the same tag to all categories, click on one of the opposite buttons  -------->' mod='gmerchantcenterpro'}</td>
						<td>
							<span class="btn btn-default" onclick="oGmcPro.doSet('.gender', 'male');">{l s='Men (male)' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.gender', 'female');">{l s='Women (female)' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.gender', 'unisex');">{l s='Unisex' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.gender', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>
					<tr id="bulk_action_tagadult">
						<td class="label_tag_categories_value col-xs-6" >{l s='Set ADULT tags : for each product default category, if the products of the category are for adult only, select the \"true\" value in the drop and down menu.' mod='gmerchantcenterpro'}</td>
						<td>
							<span class="btn btn-default" onclick="oGmcPro.doSet('.adult', 'true');">{l s='Set for all categories' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.adult', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>
					<tr id="bulk_action_sizeType">
						<td class="label_tag_categories_value col-xs-6">{l s='Set SIZE TYPE tags : for each product default category, if available, you will have to select, in the drop and down menu, which Google predefined \"size type\" value defines the size type of the products that are in this category. To assign the same tag to all categories, click on one of the opposite buttons  -------->' mod='gmerchantcenterpro'}</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.sizeType', 'maternity');">{l s='Maternity' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeType', 'oversize');">{l s='Oversize' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeType', 'petite');">{l s='Petite' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeType', 'regular');">{l s='Regular' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeType', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span>
						</td>
					</tr>
					<tr id="bulk_action_sizeSystem">
						<td class="label_tag_categories_value col-xs-6">{l s='Set SIZE SYSTEM tags : for each product default category, if available, you will have to select, in the drop and down menu, which Google predefined \"size system\" value defines the size system of the products that are in this category. To assign the same tag to all categories, click on one of the opposite buttons  -------->' mod='gmerchantcenterpro'}</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'US');">{l s='US' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'UK');">{l s='UK' mod='gmerchantcenterpro'} </span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'EU');">{l s='EU' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'DE');">{l s='DE' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'FR');">{l s='FR' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'JP');">{l s='JP' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'CN');">{l s='CN' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'IT');">{l s='IT' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'BR');">{l s='BR' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'MEX');">{l s='MEX' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 'AU');">{l s='AU' mod='gmerchantcenterpro'}</span>
							- <span class="btn btn-default" onclick="oGmcPro.doSet('.sizeSystem', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span>
						</td>
					</tr>

					<tr id="bulk_action_energy">
						<td class="label_tag_categories_value col-xs-6">{l s='Set ENERGY EFFICIENCY CLASS tags : for each product default category, if available, you will have to indicate the feature that defines the energy efficiency class of the products that are in this category. You will also have to indicate the feature that defines the min energy efficiency class and the one that defines the max energy efficiency class of your catalog products.' mod='gmerchantcenterpro'}</td>
						<td>
							<div class="row">
								<div class="col-xs-2">
									<label>{l s='Energy efficiency class' mod='gmerchantcenterpro'}</label>
								</div>
								<div class="col-xs-2">
									<select name="set_energy_bulk_action" class="set_energy_bulk_action">
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
								<div class="col-xs-8">
									<span class="btn btn-default" onclick="oGmcPro.doSet('.energy', $('.set_energy_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <div class="btn btn-default" onclick="oGmcPro.doSet('.energy', 0);">{l s='Reset' mod='gmerchantcenterpro'}</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-2">
									<label>{l s='Min energy efficiency class' mod='gmerchantcenterpro'}</label>
								</div>
								<div class="col-xs-2">
									<select name="set_energy_min_bulk_action" class="set_energy_min_bulk_action">
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
								<div class="col-xs-8">
									<span class="btn btn-default" onclick="oGmcPro.doSet('.energy_min', $('.set_energy_min_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <div class="btn btn-default" onclick="oGmcPro.doSet('.energy_min', 0);">{l s='Reset' mod='gmerchantcenterpro'}</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-2">
									<label>{l s='Max energy efficiency class' mod='gmerchantcenterpro'}</label>
								</div>
								<div class="col-xs-2">
									<select name="set_energy_max_bulk_action" class="set_energy_max_bulk_action">
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
								<div class="col-xs-8">
									<span class="btn btn-default" onclick="oGmcPro.doSet('.energy_max', $('.set_energy_max_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <div class="btn btn-default" onclick="oGmcPro.doSet('.energy_max', 0);">{l s='Reset' mod='gmerchantcenterpro'}</div>
								</div>
							</div>
						</td>

					</tr>
					<tr id="bulk_action_shipping_label">
						<td class="label_tag_categories_value col-xs-6">{l s='Set SHIPPING LABEL tags : for each product default category, if available, you will have to indicate the feature that defines the shipping label of the products that are in this category.' mod='gmerchantcenterpro'}</td>
						<td>
							<select name="set_shipping_label_bulk_action" class="set_shipping_label_bulk_action">
								{foreach from=$aFeatures item=feature}
									<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.shipping_label', $('.set_shipping_label_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.shipping_label', 0);">{l s='Reset' mod='gmerchantcenterpro'}</td>
					</tr>

					<tr id="bulk_action_unit_pricing_measure">
						<td class="label_tag_categories_value col-xs-6">{l s='Set UNIT PRICING MEASURE tags : for each product default category, if available, you will have to indicate the feature that defines the unit pricing measure of the products that are in this category.' mod='gmerchantcenterpro'}</td>
						<td>
							<select name="set_unit_pricing_measure_bulk_action" class="set_unit_pricing_measure_bulk_action">
								{foreach from=$aFeatures item=feature}
									<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.unit_pricing_measure', $('.set_unit_pricing_measure_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.unit_pricing_measure', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>

					<tr id="bulk_action_base_unit_pricing_measure">
						<td class="label_tag_categories_value col-xs-6">{l s='Set UNIT PRICING BASE MEASURE tags : for each product default category, if available, you will have to indicate the feature that defines the unit pricing base measure of the products that are in this category.' mod='gmerchantcenterpro'}</td>
						<td>
							<select name="set_base_unit_pricing_measure_bulk_action" class="set_base_unit_pricing_measure_bulk_action">
								{foreach from=$aFeatures item=feature}
									<option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.base_unit_pricing_measure', $('.set_base_unit_pricing_measure_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.base_unit_pricing_measure', 0);">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>

					<tr id="bulk_action_excluded_destination">
						<td class="label_tag_categories_value col-xs-6">{l s='Set EXCLUDED DESTINATION tags: for each product default category, select in the drop and down menu the advertising channel on which you DO NOT want products of this category to be displayed. You can select several channels by holding down the CTRL (or CMD) key. To assign the same tag to all categories, click on one of the opposite buttons  -------->' mod='gmerchantcenterpro'}</td>			
							<td>
								<select multiple name="set_excluded_destination_bulk_action" class="set_excluded_destination_bulk_action">
									<option value="">{l s='--' mod='gmerchantcenterpro'}</option>
									<option value="shopping">{l s='Shopping Ads' mod='gmerchantcenterpro'}</option>
									<option value="display">{l s='Display Ads' mod='gmerchantcenterpro'}</option>
									<option value="local">{l s='Local inventory ads' mod='gmerchantcenterpro'}</option>
									<option value="free-listing">{l s='Free listings' mod='gmerchantcenterpro'}</option>
									<option value="free-local-listing">{l s='Free local listings' mod='gmerchantcenterpro'}</option>
								</select>
							<td>
						<td><span class="btn btn-default" onclick="oGmcPro.doSet('.excluded_destination', $('.set_excluded_destination_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.excluded_destination', '');">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
					</tr>

					<tr id="bulk_action_excluded_country">
					<td class="label_tag_categories_value col-xs-6">{l s='Set EXCLUDED COUNTRY tags: for each product default category, select in the drop and down menu the country in which you DO NOT want products of this category to be displayed. You can select several countries by holding down the CTRL (or CMD) key. To assign the same tag to all categories, click on one of the opposite buttons -------->' mod='gmerchantcenterpro'}</td>			
						<td>
							<select multiple name="set_excluded_country_bulk_action" class="set_excluded_country_bulk_action">
								{foreach from=$aCountries item=country}
									<option value="{$country|escape:'htmlall':'UTF-8'}">{$country|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						<td>
					<td><span class="btn btn-default" onclick="oGmcPro.doSet('.excluded_country', $('.set_excluded_country_bulk_action').val());">{l s='Set for all categories' mod='gmerchantcenterpro'}</span> - <span class="btn btn-default" onclick="oGmcPro.doSet('.excluded_destination', '');">{l s='Reset' mod='gmerchantcenterpro'}</span></td>
				</tr>

				</table>
			</div>
		</div>
		<div class="clr_5"></div>
		</div>

		<form class="form-horizontal" method="post" id="bt_form-advanced-tag" name="bt_form-advanced-tag" {if $smarty.const._GSR_USE_JS == true}onsubmit="oGmcPro.form('bt_form-advanced-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-tag', 'bt_advanced-tag', false, true, null, 'AdvancedTag', 'loadingAdvancedTagDiv');return false;"{/if}>
			<input type="hidden" name="{$sCtrlParamName|escape:'htmlall':'UTF-8'}" value="{$sController|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sAction" value="{$aQueryParams.tagUpdate.action|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sType" value="{$aQueryParams.tagUpdate.type|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="sUseTag" value="{$sUseTag|escape:'htmlall':'UTF-8'}" id="default_tag" />
			<table class="table table-responsive">
				<thead>
				<th class="bt_tr_header text-center"><b>{l s='Shop category' mod='gmerchantcenterpro'}</b></th>
				<th class="bt_tr_header text-center"><b>{l s='Tag' mod='gmerchantcenterpro'}</b></th>
				</thead>
				{foreach from=$aShopCategories item=cat}
					<tr>
						<td class="label_tag_categories_value">{$cat.path}</td>
						<td>
							<div class="value_material">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Material:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select name="material[{$cat.id_category|intval}]" class="material" >
										<option value="0">-----</option>
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}" {if $cat.material == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="value_pattern">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Pattern:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select name="pattern[{$cat.id_category|intval}]" class="pattern" >
										<option value="0">-----</option>
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}" {if $cat.pattern == $feature.id_feature} selected {/if}>{$feature.name|escape:'html':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="value_agegroup">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Age group:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select class="agegroup" name="agegroup[{$cat.id_category|intval}]" id="agegroup{$cat.id_category|intval}">
										<option value="0"{if $cat.agegroup=="0"} selected{/if}>--</option>
										<option value="adult"{if $cat.agegroup=="adult"} selected{/if}>{l s='Adults (>13y.o)' mod='gmerchantcenterpro'}</option>
										<option value="kids"{if $cat.agegroup=="kids"} selected{/if}>{l s='Kids (5-13y.o)' mod='gmerchantcenterpro'}</option>
										<option value="toddler"{if $cat.agegroup=="toddler"} selected{/if}>{l s='Toddlers (1-5y.o)' mod='gmerchantcenterpro'}</option>
										<option value="infant"{if $cat.agegroup=="infant"} selected{/if}>{l s='Infants (3-12m.o)' mod='gmerchantcenterpro'}</option>
										<option value="newborn"{if $cat.agegroup=="newborn"} selected{/if}>{l s='Newborns (<3m.o) ' mod='gmerchantcenterpro'}</option>
									</select>
								</div>
							</div>
							<div class="value_gender">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Gender:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select class="gender" name="gender[{$cat.id_category|intval}]" id="gender{$cat.id_category|intval}">
										<option value="0"{if $cat.gender=="0"} selected{/if}>--</option>
										<option value="male"{if $cat.gender=="male"} selected{/if}>{l s='Men' mod='gmerchantcenterpro'}</option>
										<option value="female"{if $cat.gender=="female"} selected{/if}>{l s='Women' mod='gmerchantcenterpro'}</option>
										<option value="unisex"{if $cat.gender=="unisex"} selected{/if}>{l s='Unisex' mod='gmerchantcenterpro'}</option>
									</select>
								</div>
							</div>
							<div class="value_tagadult">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Tag product for adults only :' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select class="adult" name="adult[{$cat.id_category|intval}]" id="adult{$cat.id_category|intval}">
										<option value="0"{if $cat.adult=="0"} selected{/if}>--</option>
										<option value="true"{if $cat.adult=="true"} selected{/if}>{l s='True' mod='gmerchantcenterpro'}</option>
									</select>
								</div>
							</div>
							<div class="value_sizeType">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Size type:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select class="sizeType" name="sizeType[{$cat.id_category|intval}]" id="sizeType{$cat.id_category|intval}">
										<option value="0"{if $cat.sizeType=="0"} selected{/if}>--</option>
										<option value="regular"{if $cat.sizeType=="regular"} selected{/if}>{l s='Regular' mod='gmerchantcenterpro'}</option>
										<option value="petite"{if $cat.sizeType=="petite"} selected{/if}>{l s='Petite' mod='gmerchantcenterpro'}</option>
										<option value="oversize"{if $cat.sizeType=="oversize"} selected{/if}>{l s='Oversize' mod='gmerchantcenterpro'}</option>
										<option value="maternity"{if $cat.sizeType=="maternity"} selected{/if}>{l s='Maternity' mod='gmerchantcenterpro'}</option>
									</select>
								</div>
							</div>
							<div class="value_sizeSystem">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Size system:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select class="sizeSystem" name="sizeSystem[{$cat.id_category|intval}]" id="sizeSystem{$cat.id_category|intval}">
										<option value="0"{if $cat.sizeSystem=="0"} selected{/if}>--</option>
										<option value="US"{if $cat.sizeSystem=="US"} selected{/if}>US</option>
										<option value="UK"{if $cat.sizeSystem=="UK"} selected{/if}>UK</option>
										<option value="EU"{if $cat.sizeSystem=="EU"} selected{/if}>EU</option>
										<option value="DE"{if $cat.sizeSystem=="DE"} selected{/if}>DE</option>
										<option value="FR"{if $cat.sizeSystem=="FR"} selected{/if}>FR</option>
										<option value="JP"{if $cat.sizeSystem=="JP"} selected{/if}>JP</option>
										<option value="CN"{if $cat.sizeSystem=="CN"} selected{/if}>CN</option>
										<option value="IT"{if $cat.sizeSystem=="IT"} selected{/if}>IT</option>
										<option value="BR"{if $cat.sizeSystem=="BR"} selected{/if}>BR</option>
										<option value="MEX"{if $cat.sizeSystem=="MEX"} selected{/if}>MEX</option>
										<option value="AU"{if $cat.sizeSystem=="AU"} selected{/if}>AU</option>
									</select>
								</div>
							</div>

							<div class="value_energy">
								<div class="row">
									<div class="col-xs-2">
										<p class="label_tag_categories_value">{l s='Energy efficiency class:' mod='gmerchantcenterpro'}</p>
									</div>
									<div class="col-xs-5">
										<select name="energy[{$cat.id_category|intval}]" class="energy" >
											<option value="0">-----</option>
											{foreach from=$aFeatures item=feature}
												<option value="{$feature.id_feature|intval}" {if $cat.energy == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</div>
								</div>

								<div class="row">
									<div class="col-xs-2">
										<p class="label_tag_categories_value">{l s='Min energy efficiency class:' mod='gmerchantcenterpro'}</p>
									</div>
									<div class="col-xs-5">
										<select name="energy_min[{$cat.id_category|intval}]" class="energy_min" >
											<option value="0">-----</option>
											{foreach from=$aFeatures item=feature}
												<option value="{$feature.id_feature|intval}" {if $cat.energy_min == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</div>
								</div>


								<div class="row">
									<div class="col-xs-2">
										<p class="label_tag_categories_value">{l s='Max energy efficiency class:' mod='gmerchantcenterpro'}</p>
									</div>
									<div class="col-xs-5">
										<select name="energy_max[{$cat.id_category|intval}]" class="energy_max" >
											<option value="0">-----</option>
											{foreach from=$aFeatures item=feature}
												<option value="{$feature.id_feature|intval}" {if $cat.energy_max == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>

							<div class="value_shipping_label">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Shipping label:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select name="shipping_label[{$cat.id_category|intval}]" class="shipping_label" >
										<option value="0">-----</option>
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}" {if $cat.shipping_label == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>

							<div class="value_unit_pricing_measure">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Unit pricing measure:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select name="unit_pricing_measure[{$cat.id_category|intval}]" class="unit_pricing_measure" >
										<option value="0">-----</option>
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}" {if $cat.unit_pricing_measure == $feature.id_feature} selected {/if}>{$feature.name|escape:'html':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>

							<div class="value_base_unit_pricing_measure">
								<div class="col-xs-4">
									<p class="label_tag_categories_value">{l s='Unit pricing base measure:' mod='gmerchantcenterpro'}</p>
								</div>
								<div class="col-xs-4">
									<select name="base_unit_pricing_measure[{$cat.id_category|intval}]" class="base_unit_pricing_measure" >
										<option value="0">-----</option>
										{foreach from=$aFeatures item=feature}
											<option value="{$feature.id_feature|intval}" {if $cat.base_unit_pricing_measure == $feature.id_feature} selected {/if}>{$feature.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
								</div>
							</div>

							<div class="value_excluded_destination">
								<select multiple name="excluded_destination[{$cat.id_category|intval}][]" class="excluded_destination">
									<option value="">{l s='--' mod='gmerchantcenterpro'}</option>
									<option {if in_array('shopping', $cat.excluded_destination)} selected {/if} value="shopping">{l s='Shopping Ads' mod='gmerchantcenterpro'}</option>
									<option {if in_array('actions', $cat.excluded_destination)} selected {/if} value="actions">{l s='Buy on Google listing' mod='gmerchantcenterpro'}</option>
									<option {if in_array('display', $cat.excluded_destination)} selected {/if} value="display">{l s='Display Ads' mod='gmerchantcenterpro'}</option>
									<option {if in_array('local', $cat.excluded_destination)} selected {/if} value="local">{l s='Local inventory ads' mod='gmerchantcenterpro'}</option>
									<option {if in_array('free-listing', $cat.excluded_destination)} selected {/if} value="free-listing">{l s='Free listings' mod='gmerchantcenterpro'}</option>
									<option {if in_array('free-local-listing', $cat.excluded_destination)} selected {/if} value="free-local-listing">{l s='Free local listings' mod='gmerchantcenterpro'}</option>
								</select>
							</div>

							<div class="value_excluded_country">
								<select multiple name="excluded_country[{$cat.id_category|intval}][]" class="excluded_country">
									{foreach from=$aCountries item=country}
										<option value="{$country|escape:'htmlall':'UTF-8'}" {if in_array($country, $cat.excluded_country)} selected {/if}> {$country|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							<div>

						</td>
					</tr>
				{/foreach}
			</table>
			<p style="text-align: center !important;">
				{if $smarty.const._GMCP_USE_JS == true}
					<script type="text/javascript">
						{literal}
						var oAdvancedCallback = [{}];
						{/literal}
					</script>
					<input type="button" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenterpro'}" onclick="oGmcPro.form('bt_form-advanced-tag', '{$sURI|escape:'htmlall':'UTF-8'}', null, 'bt_advanced-tag', 'bt_advanced-tag', false, true, oAdvancedCallback, 'AdvancedTag', 'loadingAdvancedTagDiv');return false;" />
				{else}
					<input type="submit" name="{$sModuleName|escape:'htmlall':'UTF-8'}CommentButton" class="btn btn-success btn-lg" value="{l s='Modify' mod='gmerchantcenterpro'}" />
				{/if}
				<button class="btn btn-danger btn-lg" value="{l s='Cancel' mod='gmerchantcenterpro'}"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='gmerchantcenterpro'}</button>
			</p>
		</form>
		{literal}
		<script type="text/javascript">
			// execute management of options
			oGmcProFeatureByCat.handleOptionToDisplay($("#default_tag").val());
			$("#set_tag").change(function () {
				oGmcProFeatureByCat.handleOptionToDisplay($(this).val());
			});
		</script>
		{/literal}
	</div>
</div>
<div id="loadingAdvancedTagDiv" style="display: none;">
	<div class="alert alert-info">
		<p style="text-align: center !important;"><img src="{$sLoadingImg|escape:'htmlall':'UTF-8'}" alt="Loading" /></p><div class="clr_20"></div>
		<p style="text-align: center !important;">{l s='Your configuration updating is in progress...' mod='gmerchantcenterpro'}</p>
	</div>
</div>
{/if}

<div class="clr_hr"></div>
<div class="clr_20"></div>