{**
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
<style>
	.mt-0 {
		margin-top: 0 !important;
	}
	.section-title {
		font-size: 140%;
		font-weight: bold;
		background: #eff1f2;
		padding: 10px;
		margin-top: 50px;
	}

	.cdc-info {
		background: #d9edf7;
		color: #1b809e;
		padding: 7px;
		/*border-left: solid 3px #1b809e;*/
		margin-top: 50px;
		font-weight: normal;
	}

	.cdc-warning-box {
		background: #FFF3D7;
		color: #D2A63C;
		padding: 10px;
		font-weight: bold;
		font-size: 1.1em;
		border: solid 1px #fcc94f;
		margin: 30px 0;
		text-align: center;
	}

	#tagmanager .form-control {
		width: 100%;
	}

	.excludePaymentName-added {
		text-decoration: line-through !important;
		color: #888888 !important;
	}
</style>

<div class="bootstrap">

	<div class="panel text-center">
		<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" >
		<h1>
			Google Tag Manager Enhanced E-commerce
			<br /><small>GTM integration + Enhanced E-commerce + Google Customer Reviews</small>
		</h1>
	</div>

	<div>

	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#tagmanager" role="tab" data-toggle="tab">{l s='Google Tag Manager' mod='cdc_googletagmanager'}</a>
		</li>
		<li role="presentation">
			<a href="#customerreviews" role="tab" data-toggle="tab">{l s='Google Customer Reviews' mod='cdc_googletagmanager'}</a>
		</li>
	  </ul>

		<!-- Tab panes -->
		<form id="configuration_form" class="defaultForm form-horizontal cdc_googletagmanager" method="post" action="{$form_action|escape:'htmlall':'UTF-8'}">
			<div class="tab-content">

				<!-- GENERAL GTM SETTINGS -->
				<div role="tabpanel" class="tab-pane active panel" id="tagmanager">
					<div class="panel-body">

						{* SECTION BASE CONFIGURATION *}
						<div class="form-group section-title mt-0">{l s='GTM configuration' mod='cdc_googletagmanager'}</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable Google Tag Manager' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_ENABLE" id="ENABLE_ON"  value="1" {if $CDC_GTM_ENABLE}checked{/if} /><label for="ENABLE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_ENABLE" id="ENABLE_OFF" value="0" {if !$CDC_GTM_ENABLE}checked{/if} /><label for="ENABLE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_GTMID">Google Tag Manager ID</label>
							<div class="margin-form col-lg-3">
								<input type="text" class="form-control" id="CDC_GTM_GTMID" placeholder="GTM-XXXXXX" name="CDC_GTM_GTMID" value="{$CDC_GTM_GTMID|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-6"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_DATA_LANGUAGE">{l s='Language used for the datalayer' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								<select class="form-control" id="CDC_GTM_DATA_LANGUAGE" name="CDC_GTM_DATA_LANGUAGE">
									{foreach from=$CDC_GTM_DATA_LANGUAGES key=k item=v}
										<option value="{$k|escape:'htmlall':'UTF-8'}" {if $k == $CDC_GTM_DATA_LANGUAGE}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6"></div>
						</div>


						{* SECTION DATA FORMAT *}
						<div class="form-group section-title">{l s='Data format' mod='cdc_googletagmanager'}</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_PRODUCT_ID_FIELD">{l s='Product id' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								<select class="form-control" id="CDC_GTM_PRODUCT_ID_FIELD" name="CDC_GTM_PRODUCT_ID_FIELD">
									{foreach from=$CDC_GTM_ID_TYPES item='product_id_field'}
										<option value="{$product_id_field|escape:'htmlall':'UTF-8'}" {if $product_id_field == $CDC_GTM_PRODUCT_ID_FIELD}selected="selected"{/if}>{$product_id_field|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_PRODUCT_ID_FORMAT">{l s='Custom product ID format' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="text" class="form-control" id="CDC_GTM_PRODUCT_ID_FORMAT" placeholder="{l s='Leave empty to use default' mod='cdc_googletagmanager'}" name="CDC_GTM_PRODUCT_ID_FORMAT" value="{$CDC_GTM_PRODUCT_ID_FORMAT|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">
									{l s='Customise the format of the product identifier. Leave empty to keep the default format.' mod='cdc_googletagmanager'}
									<br /><b> - {literal}{ID}{/literal}</b> : {l s='Product identifier (numeric ID / ean / ref)' mod='cdc_googletagmanager'}
									<br /><b> - {literal}{lang}{/literal}</b> : {l s='iso code for the current language (en, fr...)' mod='cdc_googletagmanager'}
									<br /><b> - {literal}{LANG}{/literal}</b> : {l s='ISO code for the current language (EN, FR...)' mod='cdc_googletagmanager'}
								</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_DISPLAY_VARIANT_ID">{l s='How to display id for product with variant' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								<select class="form-control" id="CDC_GTM_DISPLAY_VARIANT_ID" name="CDC_GTM_DISPLAY_VARIANT_ID">
									{foreach from=$CDC_GTM_DISPLAY_VARIANT_ID_LIST key=k item=v}
										<option value="{$k|escape:'htmlall':'UTF-8'}" {if $k == $CDC_GTM_DISPLAY_VARIANT_ID}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Always display variant id with product id (PRODUCT_ID-VARIANT_ID)' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_VARIANT_ID_SEPARATOR">{l s='Variant ID separator' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="text" class="form-control" id="CDC_GTM_VARIANT_ID_SEPARATOR" placeholder="-" name="CDC_GTM_VARIANT_ID_SEPARATOR" value="{$CDC_GTM_VARIANT_ID_SEPARATOR|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Separator between product id and variant id (default: -)' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_PRODUCT_NAME_FIELD">{l s='Product name' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								{$product_name_fields = ['name','link_rewrite', 'id']}
								<select class="form-control" id="CDC_GTM_PRODUCT_NAME_FIELD" name="CDC_GTM_PRODUCT_NAME_FIELD">
									{foreach from=$product_name_fields item='product_name_field'}
									<option value="{$product_name_field|escape:'htmlall':'UTF-8'}" {if $product_name_field == $CDC_GTM_PRODUCT_NAME_FIELD}selected="selected"{/if}>{$product_name_field|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_CATEGORY_NAME_FIELD">{l s='Category name' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								{$category_name_fields = ['name','link_rewrite', 'id']}
								<select class="form-control" id="CDC_GTM_CATEGORY_NAME_FIELD" name="CDC_GTM_CATEGORY_NAME_FIELD">
									{foreach from=$category_name_fields item='category_name_field'}
									<option value="{$category_name_field|escape:'htmlall':'UTF-8'}" {if $category_name_field == $CDC_GTM_CATEGORY_NAME_FIELD}selected="selected"{/if}>{$category_name_field|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6"></div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Display category hierarchy' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_CATEGORY_HIERARCHY" id="CATEGORY_HIERARCHY_ON"  value="1" {if $CDC_GTM_CATEGORY_HIERARCHY}checked{/if} /><label for="CATEGORY_HIERARCHY_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_CATEGORY_HIERARCHY" id="CATEGORY_HIERARCHY_OFF" value="0" {if !$CDC_GTM_CATEGORY_HIERARCHY}checked{/if} /><label for="CATEGORY_HIERARCHY_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Display category with all parents categories: "/cat1/cat2/cat3" instead of "cat3"' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Add wholesale price to datalayer' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_DISPLAY_WHOLESALE_PRICE" id="DISPLAY_WHOLESALE_PRICE_ON"  value="1" {if $CDC_GTM_DISPLAY_WHOLESALE_PRICE}checked{/if} /><label for="DISPLAY_WHOLESALE_PRICE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_DISPLAY_WHOLESALE_PRICE" id="DISPLAY_WHOLESALE_PRICE_OFF" value="0" {if !$CDC_GTM_DISPLAY_WHOLESALE_PRICE}checked{/if} /><label for="DISPLAY_WHOLESALE_PRICE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Add product wholesale price in datalayer' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Display main price with tax' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_MAIN_PRICE_WITH_TAX" id="MAIN_PRICE_WITH_TAX_ON"  value="1" {if $CDC_GTM_MAIN_PRICE_WITH_TAX}checked{/if} /><label for="MAIN_PRICE_WITH_TAX_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_MAIN_PRICE_WITH_TAX" id="MAIN_PRICE_WITH_TAX_OFF" value="0" {if !$CDC_GTM_MAIN_PRICE_WITH_TAX}checked{/if} /><label for="MAIN_PRICE_WITH_TAX_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Main price includes tax (variables in the datalayer: price, value)' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Display product price with tax detail' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_DISPLAY_PROD_TAX_DETAIL" id="DISPLAY_PROD_TAX_DETAIL_ON"  value="1" {if $CDC_GTM_DISPLAY_PROD_TAX_DETAIL}checked{/if} /><label for="DISPLAY_PROD_TAX_DETAIL_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_DISPLAY_PROD_TAX_DETAIL" id="DISPLAY_PROD_TAX_DETAIL_OFF" value="0" {if !$CDC_GTM_DISPLAY_PROD_TAX_DETAIL}checked{/if} /><label for="DISPLAY_PROD_TAX_DETAIL_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Add "price_tax_exc" and "price_tax_inc" in the product datalayer' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Display product stock in datalayer' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_DISPLAY_PRODUCT_STOCK" id="DISPLAY_PRODUCT_STOCK_ON"  value="1" {if $CDC_GTM_DISPLAY_PRODUCT_STOCK}checked{/if} /><label for="DISPLAY_PRODUCT_STOCK_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_DISPLAY_PRODUCT_STOCK" id="DISPLAY_PRODUCT_STOCK_OFF" value="0" {if !$CDC_GTM_DISPLAY_PRODUCT_STOCK}checked{/if} /><label for="DISPLAY_PRODUCT_STOCK_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Display product stock in the datalayer. If set to false, a quantity of 1 will be defined in the datalayer for each product' mod='cdc_googletagmanager'}</p>
							</div>
						</div>



						{* SECTION USER ID *}
						<div class="form-group section-title">{l s='Customer informations & Google Analytics User ID feature' mod='cdc_googletagmanager'}</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Add User ID in datalayer' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_ENABLE_USERID" id="ENABLE_USERID_ON"  value="1" {if $CDC_GTM_ENABLE_USERID}checked{/if} /><label for="ENABLE_USERID_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_ENABLE_USERID" id="ENABLE_USERID_OFF" value="0" {if !$CDC_GTM_ENABLE_USERID}checked{/if} /><label for="ENABLE_USERID_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Add variables "userId" and "userLogged" in datalayer' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Add User ID in datalayer for guests' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_ENABLE_GUESTID" id="ENABLE_GUESTID_ON"  value="1" {if $CDC_GTM_ENABLE_GUESTID}checked{/if} /><label for="ENABLE_GUESTID_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_ENABLE_GUESTID" id="ENABLE_GUESTID_OFF" value="0" {if !$CDC_GTM_ENABLE_GUESTID}checked{/if} /><label for="ENABLE_GUESTID_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Variable "userId" is set with guest_[GUEST_ID] when user is guest. This option allows tracking of user not loggued accross multiple sessions' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>



						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_CUSTOMER_INFORMATIONS">{l s='Add customer informations' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								<select class="form-control" id="CDC_GTM_CUSTOMER_INFORMATIONS" name="CDC_GTM_CUSTOMER_INFORMATIONS">
									{foreach from=$CDC_GTM_CUSTOMER_INFORMATIONS_ID_LIST key=k item=v}
										<option value="{$k|escape:'htmlall':'UTF-8'}" {if $k == $CDC_GTM_CUSTOMER_INFORMATIONS}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Display customer informations on the datalayer (email, past orders, is new ...)' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						{* SECTION REMARKETING *}
						<div class="form-group section-title">{l s='Remarketing' mod='cdc_googletagmanager'}</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable Remarketing Parameters' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_REMARKETING_ENABLE" id="REMARKETING_ENABLE_ON"  value="1" {if $CDC_GTM_REMARKETING_ENABLE}checked{/if} /><label for="REMARKETING_ENABLE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_REMARKETING_ENABLE" id="REMARKETING_ENABLE_OFF" value="0" {if !$CDC_GTM_REMARKETING_ENABLE}checked{/if} /><label for="REMARKETING_ENABLE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Add the object "google_tag_params" in the datalayer, useful for setting up Dynamic Remarketing' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>



						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_REMARKETING_PRODUCTID">{l s='Product ID in Merchant Center' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3 form-inline">
								<select class="form-control" id="CDC_GTM_REMARKETING_PRODUCTID" name="CDC_GTM_REMARKETING_PRODUCTID">
									{foreach from=$CDC_GTM_ID_TYPES item='product_identifier'}
									<option value="{$product_identifier|escape:'htmlall':'UTF-8'}" {if $product_identifier == $CDC_GTM_REMARKETING_PRODUCTID}selected="selected"{/if}>{$product_identifier|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-6"></div>
						</div>

						{* SECTION ADVANCED PARAMETERS *}
						<div class="form-group section-title">{l s='Advanced parameters' mod='cdc_googletagmanager'}</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Load GTM script' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_LOAD_GTM_SCRIPT" id="LOAD_GTM_SCRIPT_ON"  value="1" {if $CDC_GTM_LOAD_GTM_SCRIPT}checked{/if} />
									<label for="LOAD_GTM_SCRIPT_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_LOAD_GTM_SCRIPT" id="LOAD_GTM_SCRIPT_OFF" value="0" {if !$CDC_GTM_LOAD_GTM_SCRIPT}checked{/if} />
									<label for="LOAD_GTM_SCRIPT_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='When an order couldn\'t be sent in the first place, the module tries to re-send it later. However, the date of the order shown in Analytics will be the date when the order is sent and not the real date of the order.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_OVERRIDE_GTM_TAG">{l s='Override Google Tag Manager script' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<textarea id="CDC_GTM_OVERRIDE_GTM_TAG" name="CDC_GTM_OVERRIDE_GTM_TAG" rows="4">{$CDC_GTM_OVERRIDE_GTM_TAG|escape:'htmlall':'UTF-8'}</textarea>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Replace the default Google Tag Manager script with this one. Leave the field empty to use the default value.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						
						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_OVERRIDE_GTM_TAG_NOSCRIPT">{l s='Override Google Tag Manager noscript tag' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<textarea id="CDC_GTM_OVERRIDE_GTM_TAG_NOSCRIPT" name="CDC_GTM_OVERRIDE_GTM_TAG_NOSCRIPT" rows="4">{$CDC_GTM_OVERRIDE_GTM_TAG_NOSCRIPT nofilter}</textarea>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Replace Google Tag Manager\'s default noscript tag with this one. Leave the field empty to use the default value' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						
						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_MAX_CAT_ITEMS">Maximum category items to send in datalayer</label>
							<div class="margin-form col-lg-3">
								<input type="number" class="form-control" id="CDC_GTM_MAX_CAT_ITEMS" name="CDC_GTM_MAX_CAT_ITEMS" value="{$CDC_GTM_MAX_CAT_ITEMS|escape:'htmlall':'UTF-8'}" min="1" step="1">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Maximum number of items sent to datalayer in category pages. If ou have big product name, please lower this value so the datalayer does not exceed size limit.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Asynchronous loading of User Info' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_ASYNC_USER_INFO" id="ASYNC_USER_INFO_ON"  value="1" {if $CDC_GTM_ASYNC_USER_INFO}checked{/if} /><label for="ASYNC_USER_INFO_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_ASYNC_USER_INFO" id="ASYNC_USER_INFO_OFF" value="0" {if !$CDC_GTM_ASYNC_USER_INFO}checked{/if} /><label for="ASYNC_USER_INFO_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='If you have a full page cache system, which serves static versions of pages, you will need to load user informations asynchronously. Use it with caution, this configuration can change the behavior of your GTM events.' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3">{l s='Track shipping selection' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_SHIPPING_EVENT" id="SHIPPING_EVENT_ON"  value="1" {if $CDC_GTM_SHIPPING_EVENT}checked{/if} /><label for="SHIPPING_EVENT_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_SHIPPING_EVENT" id="SHIPPING_EVENT_OFF" value="0" {if !$CDC_GTM_SHIPPING_EVENT}checked{/if} /><label for="SHIPPING_EVENT_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Disable shipping selection tracking if you encounter issue at shipping selection step during checkout.' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3">{l s='Track payment selection' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_PAYMENT_EVENT" id="PAYMENT_EVENT_ON"  value="1" {if $CDC_GTM_PAYMENT_EVENT}checked{/if} /><label for="PAYMENT_EVENT_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_PAYMENT_EVENT" id="PAYMENT_EVENT_OFF" value="0" {if !$CDC_GTM_PAYMENT_EVENT}checked{/if} /><label for="PAYMENT_EVENT_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Disable payment selection tracking if you encounter issue at payment selection step during checkout.' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>


						{* SECTION ORDER RESEND *}
						<div class="form-group section-title">{l s='Orders resend' mod='cdc_googletagmanager'}</div>

						<div class="cdc-warning-box">
							<p>{l s='The order re-send function lets you send "purchase" events from your Prestashop back office.' mod='cdc_googletagmanager'}</p>
							<p>{l s='If you have too many missing orders, please consult the FAQ in the' mod='cdc_googletagmanager'} <a href="https://comptoirducode.com/prestashop/modules/google-tag-manager/documentation-google-tag-manager-prestashop/" target="_blank">documentation</a>.</p>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable Automatic Re-send Orders' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_ENABLE_RESEND" id="ENABLE_RESEND_ON"  value="1" {if $CDC_GTM_ENABLE_RESEND}checked{/if} />
									<label for="ENABLE_RESEND_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_ENABLE_RESEND" id="ENABLE_RESEND_OFF" value="0" {if !$CDC_GTM_ENABLE_RESEND}checked{/if} />
									<label for="ENABLE_RESEND_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='When an order couldn\'t be sent in the first place, the module tries to re-send it later. However, the date of the order shown in Analytics will be the date when the order is sent and not the real date of the order.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable automatic recreation of datalayer' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_DATALAYER_AUTO_CREATE" id="DATALAYER_AUTO_CREATE_ON"  value="1" {if $CDC_GTM_DATALAYER_AUTO_CREATE}checked{/if} />
									<label for="DATALAYER_AUTO_CREATE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_DATALAYER_AUTO_CREATE" id="DATALAYER_AUTO_CREATE_OFF" value="0" {if !$CDC_GTM_DATALAYER_AUTO_CREATE}checked{/if} />
									<label for="DATALAYER_AUTO_CREATE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='If the datalayer was not created when the order was placed (no redirection on the payment confirmation page, for example), the datalayer can be automatically re-created. Remember to exclude payment methods for external orders (marketplaces, for example) to prevent them from being tracked in your statistics.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_RESEND_DAYS">{l s='Maximum days to re-send orders' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="number" class="form-control" id="CDC_GTM_RESEND_DAYS" name="CDC_GTM_RESEND_DAYS" value="{$CDC_GTM_RESEND_DAYS|escape:'htmlall':'UTF-8'}" min="1" step="1">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Maximum number of days after the order has been placed to re-send it. If this number is too small, some orders won\'t be re-sent. If the number is too big, re-sent orders may be out of sync (date too long after the real date).' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_RESEND_EXCLUDE_PAYMENT">{l s='Exclude payment methods from order re-send' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<textarea rows="2" class="form-control" id="CDC_GTM_RESEND_EXCLUDE_PAYMENT" name="CDC_GTM_RESEND_EXCLUDE_PAYMENT">{$CDC_GTM_RESEND_EXCLUDE_PAYMENT|escape:'htmlall':'UTF-8'}</textarea>
								<small>Click to exclude:
									{foreach from=$paymentNames key=k item=paymentName}
										<a href="#" class="excludePaymentName" data-value="{$paymentName|escape:'htmlall':'UTF-8'}">{$paymentName|escape:'htmlall':'UTF-8'}</a>,
									{/foreach}
								</small>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='If orders come from sources external to your site (marketplaces, feed managers), you must exclude them from re-sending. To do this, please enter the exact names of the payment methods to be excluded, separated by commas.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


						{* SECTION ACTIONS *}
						<div class="form-group section-title">{l s='Monitoring of orders tracking' mod='cdc_googletagmanager'}</div>
						<a href="{$gtm_order_logs_url|escape:'htmlall':'UTF-8'}" class="btn btn-default">{l s='Orders sent to GTM (GTM Orders)' mod='cdc_googletagmanager'}</a>

						<div style="margin: 30px 0 6px 0;">
							<b>{l s='Resend queue' mod='cdc_googletagmanager'}</b>
							{if empty({$ORDER_RESEND_Q})}
								: {l s='Empty' mod='cdc_googletagmanager'}
							{else}
								- <a href="{$form_action|escape:'htmlall':'UTF-8'}&empty_resend_queue">{l s='Empty resend queue' mod='cdc_googletagmanager'}</a>
								<pre>{$ORDER_RESEND_Q|escape:'htmlall':'UTF-8'}</pre>
							{/if}
						</div>

						<div style="margin: 15px 0 6px 0;">
							<b>{l s='Refund queue' mod='cdc_googletagmanager'}</b>
							{if empty({$REFUNDS_Q})}
								: {l s='Empty' mod='cdc_googletagmanager'}
							{else}
								- <a href="{$form_action|escape:'htmlall':'UTF-8'}&empty_refund_queue">{l s='Empty refund queue' mod='cdc_googletagmanager'}</a>
								<pre>{$REFUNDS_Q|escape:'htmlall':'UTF-8'}</pre>
							{/if}
						</div>

						{* SECTION DEBUG DATALAYER *}
						<div class="form-group section-title">{l s='Datalayer debugging' mod='cdc_googletagmanager'}</div>

						<div class="cdc-warning-box">
							<p>{l s='Backup of the datalayer during the following events.' mod='cdc_googletagmanager'}</p>
							<p>{l s='Only for debugging purpose.' mod='cdc_googletagmanager'}</p>
							<a href="{$gtm_datalayer_logs_url|escape:'htmlall':'UTF-8'}" class="btn btn-default">{l s='View datalayer backups' mod='cdc_googletagmanager'}</a>
						</div>


						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_DATALAYER_DEBUG_EVENTS">{l s='Events name separated by a comma' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="text" class="form-control" id="CDC_GTM_DATALAYER_DEBUG_EVENTS" name="CDC_GTM_DATALAYER_DEBUG_EVENTS" value="{$CDC_GTM_DATALAYER_DEBUG_EVENTS|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Example: purchase,refund,view_cart' mod='cdc_googletagmanager'}</p>
							</div>
						</div>


					</div>
				</div>


				<!-- GOOGLE CUSTOMER REVIEWS SETTINGS -->
				<div role="tabpanel" class="tab-pane panel" id="customerreviews" {if (version_compare($CDC_PS_VERSION, '1.6', '<'))}style="display: block;"{/if}>
					<div class="panel-body">
						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable Google Customer Reviews' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_GCR_ENABLE" id="GCR_ENABLE_ON"  value="1" {if $CDC_GTM_GCR_ENABLE}checked{/if} /><label for="GCR_ENABLE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_GCR_ENABLE" id="GCR_ENABLE_OFF" value="0" {if !$CDC_GTM_GCR_ENABLE}checked{/if} /><label for="GCR_ENABLE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6"></div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Add the Customer Reviews badge code' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_GCR_BADGE_CODE" id="GCR_BADGE_CODE_ON"  value="1" {if $CDC_GTM_GCR_BADGE_CODE}checked{/if} /><label for="GCR_BADGE_CODE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_GCR_BADGE_CODE" id="GCR_BADGE_CODE_OFF" value="0" {if !$CDC_GTM_GCR_BADGE_CODE}checked{/if} /><label for="GCR_BADGE_CODE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='You must set this option to YES, unless you have already added the badge code in your source file or with Google Tag Manager' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_GCR_MERCHANT_ID">{l s='Merchant ID' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="text" class="form-control" id="CDC_GTM_GCR_MERCHANT_ID" placeholder="000000" name="CDC_GTM_GCR_MERCHANT_ID" value="{$CDC_GTM_GCR_MERCHANT_ID|escape:'htmlall':'UTF-8'}">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='You can find your Google Merchant ID ' mod='cdc_googletagmanager'}<a href="https://merchants.google.com/mc/customerreviews/configuration" target="_blank">{l s='in your Google merchants center' mod='cdc_googletagmanager'}</a>.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_GCR_BADGE_POSITION">{l s='Badge position' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<select class="form-control" id="CDC_GTM_GCR_BADGE_POSITION" name="CDC_GTM_GCR_BADGE_POSITION">
									<option value="BOTTOM_RIGHT" {if $CDC_GTM_GCR_BADGE_POSITION == "BOTTOM_RIGHT"}selected="selected"{/if}>
										BOTTOM_RIGHT
									</option>
									<option value="BOTTOM_LEFT" {if $CDC_GTM_GCR_BADGE_POSITION == "BOTTOM_LEFT"}selected="selected"{/if}>
										BOTTOM_LEFT
									</option>
								</select>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='Position of the Google Customer Reviews badge.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

						<hr />

						<div class="form-group">
							<label class="control-label col-lg-3" rel="only_map">{l s='Enable the order confirmation module' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" name="CDC_GTM_GCR_ORDER_CODE" id="GCR_ORDER_CODE_ON"  value="1" {if $CDC_GTM_GCR_ORDER_CODE}checked{/if} /><label for="GCR_ORDER_CODE_ON" class="label-checkbox">{l s='Yes' mod='cdc_googletagmanager'}</label>
									<input type="radio" name="CDC_GTM_GCR_ORDER_CODE" id="GCR_ORDER_CODE_OFF" value="0" {if !$CDC_GTM_GCR_ORDER_CODE}checked{/if} /><label for="GCR_ORDER_CODE_OFF" class="label-checkbox">{l s='No' mod='cdc_googletagmanager'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='You must set this option to YES, unless you have already added the order confirmation code in your order confirmation page' mod='cdc_googletagmanager'}.</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-3" for="CDC_GTM_GCR_DELIVERY_DAYS">{l s='Delivery delay (days)' mod='cdc_googletagmanager'}</label>
							<div class="margin-form col-lg-3">
								<input type="number" class="form-control" id="CDC_GTM_GCR_DELIVERY_DAYS" placeholder="X" name="CDC_GTM_GCR_DELIVERY_DAYS" value="{$CDC_GTM_GCR_DELIVERY_DAYS|escape:'htmlall':'UTF-8'}" min="0" step="1">
							</div>
							<div class="col-lg-6">
								<p class="cdc-info">{l s='The estimated number of days before an order is delivered.' mod='cdc_googletagmanager'}</p>
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="text-right">
				<button type="submit" value="1" id="configuration_form_submit_btn" name="submitcdc_googletagmanager" class="button btn btn-default">
					<i class="process-icon-save"></i> {l s='Save All' mod='cdc_googletagmanager'}
				</button>
			</div>
		</form>


		<div style="margin-top: 10px;">
			<p>
				<a href="https://comptoirducode.com/prestashop/modules/google-tag-manager/documentation-google-tag-manager-prestashop/" target="_blank" class="btn btn-default">{l s='Read the module documentation' mod='cdc_googletagmanager'}</a>

				<a href="{$form_action|escape:'htmlall':'UTF-8'}&force_check_hooks" class="btn btn-default">{l s='Check hooks installation' mod='cdc_googletagmanager'}</a>

				<a href="http://addons.prestashop.com/ratings.php?id_product=23806" class="btn btn-default">{l s='Rate the module' mod='cdc_googletagmanager'}</a>

				<a href="https://addons.prestashop.com/contact-community.php?id_product=23806" class="btn btn-default">{l s='Contact support' mod='cdc_googletagmanager'}</a>
			</p>

		</div>

		<div style="margin-top: 15px; border-top: 1px dotted #999;padding-top: 15px;">
			<p>
				<b>{l s='This module fits your needs?' mod='cdc_googletagmanager'}</b><br>
				<a href="http://addons.prestashop.com/ratings.php?id_product=23806" target="_blank">{l s='Thanks to rate-us on Prestashop marketplace' mod='cdc_googletagmanager'}</a>. {l s='The more we have ratings and satisfied customers, the more we enjoy to develop new features for you!' mod='cdc_googletagmanager'}
			</p>
		</div>

	</div>
</div>

<script>
$(document).ready(function() {

	$('#CDC_GTM_GTS_BADGE_POSITION').change(function() {
		if($(this).val() == 'USER_DEFINED') {
			$('#wrapper_CDC_GTM_GTS_CONTAINER').show(400).highlight();
		} else {
			$('#wrapper_CDC_GTM_GTS_CONTAINER').hide(400);
		}
	}).change();

	function updateLinksPaymentExclusion() {
		$('.excludePaymentName').each(function() {
			var value = $(this).data('value');
			var currentValues = $('#CDC_GTM_RESEND_EXCLUDE_PAYMENT').val().split(',');
			if ($.inArray(value, currentValues) !== -1) {
				$(this).addClass('excludePaymentName-added');
			} else {
				$(this).removeClass('excludePaymentName-added');
			}
		});
	}
	updateLinksPaymentExclusion();
	$('#CDC_GTM_RESEND_EXCLUDE_PAYMENT').on('input',function(e){
		updateLinksPaymentExclusion();
	});

	// resend order payment exclusion
	$('.excludePaymentName').on('click', function(e) {
		e.preventDefault();

		var valueToAdd = $(this).data('value');
		var input = $('#CDC_GTM_RESEND_EXCLUDE_PAYMENT');
		var currentValues = input.val().split(',');

		// Checks if the value is already present in the input
		if ($.inArray(valueToAdd, currentValues) === -1) {
			// If not present, adds the value to the end with a comma
			input.val(input.val() + (input.val() ? ',' : '') + valueToAdd);
		}
		updateLinksPaymentExclusion();
	});
});
</script>
