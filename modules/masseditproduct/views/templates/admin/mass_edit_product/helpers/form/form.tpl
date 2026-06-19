{*
* 2007-2016 PrestaShop
*567
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
* @author    SeoSA <885588@bk.ru>
* @copyright 2012-2020 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{include file="./translations.tpl"}
<script>
    var upload_file_dir = "{$upload_file_dir|escape:'quotes':'UTF-8'}";
    var upload_image_dir = "{$upload_image_dir|escape:'quotes':'UTF-8'}";
    var text_already_exists_attribute = "{l s='Attribute already exists!' mod='masseditproduct'}";
    var text_filename_empty = "{l s='Filename required field!' mod='masseditproduct' js=true}";
    var text_template_name_empty = "{l s='Template name empty!' mod='masseditproduct' js=true}";
    var text_not_products = "{l s='Not products!' mod='masseditproduct' js=true}";
    var change_combination= {literal}{{/literal}
        'title':'{l s='Apply change for price' mod='masseditproduct' js=true}',
        'base': '{l s='Base' mod='masseditproduct' js=true}',
        'final': '{l s='Final' mod='masseditproduct' js=true}'
        {literal}}{/literal};
    var change_product = {literal}{{/literal}
        'title':'{l s='Apply change for impact on price' mod='masseditproduct' js=true}',
        'base': '{l s='tax excl.' mod='masseditproduct' js=true}',
        'final': '{l s='tax incl.' mod='masseditproduct' js=true}'
        {literal}}{/literal};
    var allowEmployeeFormLang = {$allowEmployeeFormLang|intval};
    var languages = {$languages|json_encode};
    var id_language = {$default_form_language|intval};

    function initLanguages()
    {
        {if $smarty.const._PS_VERSION_ < 1.6}
        displayFlags(languages, id_language, allowEmployeeFormLang);
        {else}
        hideOtherLanguage({$default_form_language|intval});
        {literal}
        $(".textarea-autosize").autosize();
        {/literal}
        {/if}
    }
</script>

<div class="{if $smarty.const._PS_VERSION_ < 1.6}custom_responsive{/if} custom_bootstrap">
	<div class="popup_mep">
		<div class="popup_info_row">
			<span class="popup_info">
                {l s='Count products:' mod='masseditproduct'}
				<span class="count_products">0</span>
			</span>
			<button class="toggleList active" type="button">
				<i class="icon-list"></i>
			</button>
			<button class="clearAll" type="button">
                {l s='Clear all' mod='masseditproduct'}
			</button>
		</div>
		<div class="popup_info_template">

            {$templates_quantity = ''}
			{foreach from=$templates_products item=template}{$templates_quantity = 'active'}{/foreach}

			<div class="row select-template-block {$templates_quantity|no_escape}">
				<div class="col-xs-12">
					<div>{l s='Select template' mod='masseditproduct'}</div>
					<select class="fixed-width-xl custom-select" name="template_products">
						<option value="">-----</option>
                        {foreach from=$templates_products item=template}
							<option value="{$template.id_mep_template_products|intval}">{$template.name|escape:'quotes':'UTF-8'}</option>
                        {/foreach}
					</select>
					<button class="btn btn-default btn-sm selectTemplateProduct">
						<i class="icon-download-alt"></i>
					</button>
					<button class="btn btn-danger btn-sm deleteTemplateProduct">
						<i class="icon-trash"></i>
					</button>
				</div>
			</div>
			<div class="row margin-top">
				<div class="col-xs-12">
					<div>{l s='Save list products' mod='masseditproduct'}</div>
					<input class="fixed-width-xl form-control" type="text" name="template_product">
					<button type="button" class="btn btn-success btn-sm saveTemplateProduct">{l s='Save' mod='masseditproduct'}</button>
				</div>
			</div>
		</div>
		<div class="list_products"></div>
		<!-- list_products -->

		<span class="ps-switch prestashop-switch switch-mode fixed-width-xxxl switch-product-combination">
            {foreach [1,0] as $value}
				<input type="radio" name="mode"
                        {if $value == 1} id="mode_search" {else} id="mode_edit" {/if}
                        {if $value == 1} value="mode_search" {else} value="mode_edit" {/if}
                        {if $value == 1} checked="checked" {/if} />
				<label {if $value == 1} for="mode_search" {else} for="mode_edit" {/if}>
                    {if $value == 1}{l s='Select products' mod='masseditproduct'}{else}{l s='Begin edit' mod='masseditproduct'}{/if}
				</label>
            {/foreach}
			<a class="slide-button"></a>
		</span>

	</div>

	<!-- mode_search -->
	<div class="wrapp_content">
		<div class="panel mode_search">
			<h3 class="panel-heading panel-heading-top">{l s='Search products' mod='masseditproduct'}
			</h3>
			<div class="row">

						<div class="col-lg-6 tree_custom">
							<div class="">
								<label class="control-label float-left w-100">
                                    {l s='Select category by search' mod='masseditproduct'}
								</label>
                                {include file="./tree.tpl"
                                categories=$categories
                                id_category=Configuration::get('PS_ROOT_CATEGORY')
                                root=true
                                view_header=true
                                multiple=true
                                selected_categories=[]
                                name='categories'
                                search_view = true
                                }
							</div>
						</div>

						<!-- search-products -->
						<div class="col-lg-6 search-products">
							<div class="row form-group">
								<div class="col-sm-12">
									<label class="control-label search-prod margin-right float-left">
                                        {l s='Search product' mod='masseditproduct'}
									</label>
									<span class="search_product_name">
                                        {include file="./btn_radio.tpl" input=$input_product_name_type_search}
									</span>

								</div>
							</div>

							<div class="row">

									<div class="col-sm-9">
										<input name="search_query" class="form-control" type="text"/>
									</div>
									<div class="col-sm-3">
										<select class="custom-select" name="type_search">
											<option value="0">{l s='Name' mod='masseditproduct'}</option>
											<option value="1">{l s='Id product' mod='masseditproduct'}</option>
											<option value="2">{l s='Reference' mod='masseditproduct'}</option>
											<option value="3">{l s='EAN-13' mod='masseditproduct'}</option>
											<option value="4">{l s='UPC' mod='masseditproduct'}</option>
											<option value="5">{l s='Description' mod='masseditproduct'}</option>
											<option value="6">{l s='Description short' mod='masseditproduct'}</option>
										</select>
									</div>

							</div>
							<hr>
							<div class="row">
								<div class="col-xs-6">
									<div class="row">
										<label class="control-label col-xs-12">
                                            {l s='Search by manufacturer' mod='masseditproduct'}
										</label>
										<div class="col-xs-12">
											<select id="manufacturer" class="form-control" multiple name="manufacturer[]">
                                                {foreach from=$manufacturers item=manufacturer}
													<option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
											</select>
											<script>
                                                $(document).ready(function() {
                                                    $('#manufacturer').select2();
                                                });
											</script>

										</div>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="row">
										<label class="control-label col-lg-12">
                                            {l s='Search by supplier' mod='masseditproduct'}
										</label>
										<div class="col-lg-12">
											<select id="supplier" class="form-control" multiple name="supplier[]">
                                                {foreach from=$suppliers item=supplier}
													<option value="{$supplier.id_supplier|intval}">{$supplier.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
											</select>
											<script>
                                                $(document).ready(function() {
                                                    $('#supplier').select2();
                                                });
											</script>
										</div>
									</div>
								</div>
							</div>
							<hr>
							<div class="row">
								<label class="control-label col-lg-12">
                                    {l s='Search by carrier' mod='masseditproduct'}
								</label>
								<div class="col-lg-12">
									<select id="carrier" class="form-control" multiple name="carrier[]">
                                        {foreach from=$carriers item=carrier}
											<option value="{$carrier.id_carrier|intval}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
									</select>
									<script>
                                        $(document).ready(function() {
                                            $('#carrier').select2();
                                        });
									</script>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-12">
									<div class="float-left">
										<label class="control-label float-left margin-right">
                                            {l s='Only active products' mod='masseditproduct'}
										</label>
										<!-- Only active products -->
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="active" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="active" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm margin-right margin-right">
                                                {foreach [1,0] as $value}
													<input type="radio" name="active" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="active_on" {else} id="active_off" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="active_on" {else} for="active_off" {/if} >
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
									</div>
									<div class="float-left">
										<label class="control-label float-left margin-right">
                                            {l s='Only disabled products' mod='masseditproduct'}
										</label>
										<!-- Only disabled products -->
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="disable" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="disable" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm margin-right">
                                                {foreach [1,0] as $value}
													<input type="radio" name="disable" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="disable_on" {else} id="disable_off" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="disable_on" {else} for="disable_off" {/if}>
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
									</div>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-12">
									<div class="float-left">
										<label class="control-label float-left margin-right">
                                            {l s='Only image products' mod='masseditproduct'}
										</label>
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="no_image" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="no_image" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm margin-right">
                                                {foreach [1,0] as $value}
													<input type="radio" name="no_image" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="image_on" {else} id="image_off" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="image_on" {else} for="image_off" {/if}>
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
									</div>
									<div class="float-left">
										<label class="control-label float-left margin-right">
                                            {l s='No image products' mod='masseditproduct'}
										</label>
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="yes_image" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="no_image" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm margin-right">
                                                {foreach [1,0] as $value}
													<input type="radio" name="yes_image" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="image_yes" {else} id="image_no" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="image_yes" {else} for="image_no" {/if}>
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
									</div>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-12">
									<div class="float-left">
										<!-----------------------------------discount---------------->
										<label class="control-label float-left margin-right">
                                            {l s='Only discount' mod='masseditproduct'}
										</label>
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="yes_discount" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="yes_discount" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm margin-right">
                                                {foreach [1,0] as $value}
													<input type="radio" name="yes_discount" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="discount_on" {else} id="discount_off" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="discount_on" {else} for="discount_off" {/if}>
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
									</div>
									<div class="float-left">
										<label class="control-label float-left margin-right">
                                            {l s='No discount' mod='masseditproduct'}
										</label>
                                        {if $smarty.const._PS_VERSION_ < 1.6}
											<label class="t"><img src="../img/admin/enabled.gif"></label>
											<input name="no_discount" value="1" type="radio"/>
											<label class="t"><img src="../img/admin/disabled.gif"></label>
											<input checked name="no_discount" value="0" type="radio"/>
                                        {else}
											<span class="ps-switch prestashop-switch fixed-width-sm">
                                                {foreach [1,0] as $value}
													<input type="radio" name="no_discount" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 1} id="discount_yes" {else} id="discount_no" {/if}
                                                            {if 0 == $value}checked="checked"{/if} />
													<label {if $value == 1} for="discount_yes" {else} for="discount_no" {/if}>
                                                        {if $value == 1} {l s='Yes' mod='masseditproduct'} {else} {l s='No' mod='masseditproduct'} {/if}
													</label>
                                                {/foreach}
												<a class="slide-button"></a>
											</span>
                                        {/if}
										<!------------------------end discount---------------------->
									</div>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-12">
									<label class="control-label margin-right">
                                        {l s='Search by quantity?' mod='masseditproduct'}
									</label>
									<!-- search-quantity -->
									<span class="search-quantity margin-right">
										<label class="control-label">
                                            {l s='From' mod='masseditproduct'}
										</label>
										<input type="text" class="fixed-width-xs form-control" name="qty_from"">
									</span>
									<span class="search-quantity">
										<label class="control-label">
                                            {l s='To' mod='masseditproduct'}
										</label>
										<input type="text" class="fixed-width-xs form-control" name="qty_to">
									</span>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-lg-12">
									<!-- Search by price -->
									<label class="control-label margin-right">
                                        {l s='Search by price?' mod='masseditproduct'}
									</label>
									<select name="type_price" class="fixed-width-lg margin-right custom-select">
										<option value="0">{l s='Base price' mod='masseditproduct'}</option>
										<option value="1">{l s='Final price' mod='masseditproduct'}</option>
									</select>

									<span class="search-quantity margin-right white-space-nowrap">
										<label class="control-label">
                                            {l s='From' mod='masseditproduct'}
										</label>
										<input type="text" class="fixed-width-xs form-control" name="price_from">
									</span>

									<span class="search-quantity white-space-nowrap">
										<label class="control-label">
                                            {l s='To' mod='masseditproduct'}
										</label>
										<input type="text" class="fixed-width-xs form-control" name="price_to">
									</span>

								</div>
							</div>
							<hr>
							<!-- search by visible -->

							<div class="row">
								<div class="col-lg-12">
									<label class="control-label margin-right">
										{l s='Search by visible?' mod='masseditproduct'}
									</label>
									<select name="type_visible" class="fixed-width-lg margin-right custom-select">
										<option selected value="-1">{l s='Do nothing' mod='masseditproduct'}</option>
										<option value="both">{l s='Both' mod='masseditproduct'}</option>
										<option value="catalog">{l s='Only catalog' mod='masseditproduct'}</option>
										<option value="search">{l s='Only search' mod='masseditproduct'}</option>
										<option value="none">{l s='Nothing' mod='masseditproduct'}</option>
									</select>
								</div>
							</div>
							<hr>
							<!-- end by visible -->
							<div class="row">
								<!-- Search by creation date -->
								<div class="col-lg-12">

									<label class="control-label margin-right">
                                        {l s='Search by creation date?' mod='masseditproduct'}
									</label>

									<span class="white-space-nowrap">
										<label class="control-label">
                                            {l s='From' mod='masseditproduct'}
										</label>
										<input class="datetimepicker fixed-width-lg margin-right form-control" type="text" name="date_from" value="" autocomplete="off">
									</span>

									<span class="white-space-nowrap">
										<label class="control-label">
                                            {l s='To' mod='masseditproduct'}
										</label>
										<input class="datetimepicker fixed-width-lg form-control" type="text" name="date_to" value="" autocomplete="off">
									</span>

								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-12">
									<div class="clearfix">
										<div class="float-left margin-right">
											<label class="control-label margin-right">{l s='Select feature' mod='masseditproduct'}</label>
											<select name="feature_group" class="fixed-width-lg custom-select" id="select_feature">
                                                {foreach from=$features item=feature}
													<option class="option_feature {if !count($feature.values)|intval == 0}active{/if}" value="{$feature.id_feature|escape:'htmlall':'UTF-8'}">{$feature.name|escape:htmlall} ({count($feature.values)|intval})</option>
                                                {/foreach}
											</select>
											<button id="btn_list" class="btn btn-success btn-sm">

												<i class="icon-caret-down"></i>
												<i class="icon-caret-up"></i>
												<span class="view-block">{l s='View' mod='masseditproduct'}</span>
												<span class="hidden-block">{l s='Hidden' mod='masseditproduct'}</span>
											</button>
										</div>

										<span class="ps-switch prestashop-switch fixed-width-sm margin-right float-left">
                                                {foreach [1,0] as $value}
													<input type="radio" name="mode_or" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 0} id="mode_or" {else} id="mode_and" {/if}
															{if 1 == $value}checked="checked"{/if} />
													<label {if $value == 0} for="mode_or" {else} for="mode_and" {/if}>
                                                        {if $value == 0} {l s='AND' mod='masseditproduct'} {else} {l s='OR' mod='masseditproduct'} {/if}
													</label>
												{/foreach}
												<a class="slide-button"></a>
											</span>
									</div>
									<hr>
									<div class="row">
										<label class="control-label margin-right">{l s='Input custom feature' mod='masseditproduct'}</label>
										<input type="text" name="custom_feature">
									</div>
                                    {foreach from=$features item=feature}
										<div data-feature-values="{$feature.id_feature|escape:'quotes':'UTF-8'}" style="display: none" class="form-group clearfix"></div>
                                    {/foreach}
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-12">
									<div class="clearfix">
										<div class="float-left margin-right">
											<label class="control-label margin-right">{l s='Select attribute' mod='masseditproduct'}</label>
											<select name="attribute_group" class="fixed-width-lg custom-select" id="select_attribute">
												{foreach from=$attribures_groups item=attribute}
													<option class="option_attribute {if !count($attribute.attributes)|intval == 0}active{/if}" value="{$attribute.id_attribute_group|escape:'htmlall':'UTF-8'}">{$attribute.public_name|escape:htmlall:'UTF-8'} ({count($attribute.attributes)|intval})</option>
												{/foreach}
											</select>
											<button id="btn_list_at" class="btn btn-success btn-sm">
												<i class="icon-caret-down"></i>
												<i class="icon-caret-up"></i>
												<span class="view-block">{l s='View' mod='masseditproduct'}</span>
												<span class="hidden-block">{l s='Hidden' mod='masseditproduct'}</span>
											</button>
									</div>

										<span class="ps-switch prestashop-switch fixed-width-sm margin-right float-left">
                                                {foreach [1,0] as $value}
													<input type="radio" name="mode_or_at" value="{$value|escape:'quotes':'UTF-8'}"
                                                            {if $value == 0} id="mode_or_at" {else} id="mode_and_at" {/if}
															{if 1 == $value}checked="checked"{/if} />
													<label {if $value == 0} for="mode_or_at" {else} for="mode_and_at" {/if}>
                                                        {if $value == 0} {l s='AND' mod='masseditproduct'} {else} {l s='OR' mod='masseditproduct'} {/if}
													</label>
												{/foreach}
												<a class="slide-button"></a>
											</span>
									</div>
										{foreach from=$attribures_groups key=k item=attribute}
											<div data-attribute-values="{$attribute.id_attribute_group|escape:'quotes':'UTF-8'}" style="display: none" class="form-group clearfix"></div>
									{/foreach}
								</div>
							</div>
						</div>
						<div class="col-lg-12 control_btn">
							<button id="beginSearch" class="btn btn-success">
                                {l s='Search product' mod='masseditproduct'}
							</button>
							<div class="float-right">
								<label class="control-label margin-right">
                                    {l s='How many to show products?' mod='masseditproduct'}
								</label>
								<select class="form-control fixed-width-sm" name="how_many_show">
									<option selected value="20">20</option>
									<option value="50">50</option>
									<option value="100">100</option>
									<option value="300">300</option>
									<option value="500">500</option>
									<option value="1000">1000</option>
								</select>
							</div>
						</div>

			</div>
		</div>
		<div class="panel mode_search">
			<h3 class="panel-heading">{l s='Result search product' mod='masseditproduct'}</h3>
			<div class="table_search_product">
				<div class="alert alert-warning">{l s='Need begin search' mod='masseditproduct'}</div>
			</div>
			<div class="row_select_all clearfix">
				<button class="btn btn-default selectAll">
                    {l s='Select all' mod='masseditproduct'}
				</button>
			</div>
		</div>
		<div class="panel mode_edit panel-container row">
			<div class="tn-box mv_succes">
				<p class="message_mv_content">{l s='Update successfully!' mod='masseditproduct'}</p>
				<div class="tn-progress"></div>
			</div>
			<div class="tn-box mv_error">
				<p class="message_mv_content">error</p>
				<div class="tn-progress"></div>
			</div>
			<div class="panel">
				<div class="panel-heading clearfix">
					<button class="change_date_button">
						<i class="icon-plus"></i>
					</button>
					<span>{l s='Global settings' mod='masseditproduct'}
					</span> /
					<a class="masseditdoc" href="{$link_on_tab_module|escape:'quotes':'UTF-8'}">{l s='Documentation' mod='masseditproduct'}</a> /
					<a class="" id="seosa_manager_btn" href="#">{l s='Our modules' mod='masseditproduct'}</a>
				</div>
				<div class="form-group change_date_container clearfix">
					<label class="control-label col-lg-12">{l s='Change date update in product after apply changes?' mod='masseditproduct'}</label>
					<div class="col-lg-12">
						<div class="btn-group btn-group-radio">
							<label for="change_date_upd_yes">
								<input type="radio" checked="checked" name="change_date_upd" value="1" id="change_date_upd_yes">
								<span class="btn btn-default">{l s='Yes' mod='masseditproduct'}</span>
							</label>
							<label for="change_date_upd_no">
								<input type="radio" name="change_date_upd" value="0" id="change_date_upd_no">
								<span class="btn btn-default">{l s='No' mod='masseditproduct'}</span>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group change_date_container clearfix">
					<label class="control-label col-lg-12">{l s='Reindex products after change?' mod='masseditproduct'}</label>
					<div class="col-lg-12">
						<div class="btn-group btn-group-radio">
							<label for="reindex_products_yes">
								<input type="radio" checked name="reindex_products" value="1" id="reindex_products_yes">
								<span class="btn btn-default">{l s='Yes' mod='masseditproduct'}</span>
							</label>
							<label for="reindex_products_no">
								<input type="radio" name="reindex_products" value="0" id="reindex_products_no">
								<span class="btn btn-default">{l s='No' mod='masseditproduct'}</span>
							</label>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="tab_container">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-2">
							<button class="tab-menu">{l s='Menu' mod='masseditproduct'}<i class="icon-chevron-down"></i></button>
							<ul class="tabs clearfix">
                                {foreach from=$tabs item=tab}
									<li data-tab="{$tab->getTabName()|escape:'quotes':'UTF-8'}"
                                    {foreach from=$tab->getAttributes() key=attribute_name item=attribute}
                                        {$attribute_name|escape:'quotes':'UTF-8'}="{$attribute|escape:'quotes':'UTF-8'}"
                                    {/foreach}
									>{$tab->getTitle()|escape:'quotes':'UTF-8'}</li>
                                {/foreach}
							</ul>
						</div>
						<div class="col-md-10">
							<div class="tabs_content clearfix ">
								<h3 id="title_edit_tabs" class="panel-heading">{l s='Begin work with selected products' mod='masseditproduct'}</h3>
								<h3 id="title_create_tabs" class="panel-heading">{l s='Begin create products' mod='masseditproduct'}</h3>
                                {foreach from=$tabs item=tab}
									<div class="tab_content ajax_load_tab loading" id="{$tab->getTabName()|escape:'quotes':'UTF-8'}">
                                        {*{$tab->renderTabForm()|no_escape}*}
									</div>
                                {/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel mode_edit clearfix">
			<h3 class="panel-heading">{l s='Selected products' mod='masseditproduct'}</h3>
			<div class="table_selected_products">
                {include file="./products.tpl" without_product=true}
			</div>
		</div>
	</div>
</div>

<script id="image_row" type="text/html">
	<div class="row">
		<div class="col-lg-12">
			<input name="image[]" type="file">
		</div>
	</div>
</script>
<style>
	@media (max-width: 991px) {
		.table td:nth-of-type(1):before {
			content: "{l s='ID' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(2):before {
			content: "{l s='Image' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(3):before {
			content: "{l s='Name' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(4):before {
			content: "{l s='Reference' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(5):before {
			content: "{l s='Category default' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(6):before {
			content: "{l s='Price' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(7):before {
			content: "{l s='Price final' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(8):before {
			content: "{l s='Manufacture' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(9):before {
			content: "{l s='Supplier' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(10):before {
			content: "{l s='Quantity' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(11):before {
			content: "{l s='Stock management' mod='masseditproduct' js=true}";
		}
		.table td:nth-of-type(12):before {
			content: "{l s='Active' mod='masseditproduct' js=true}";
		}

		#supplier .table td:nth-of-type(1):before {
			content: "{l s='Suppliers' mod='masseditproduct' js=true}";
		}
		#supplier .table td:nth-of-type(2):before {
			content: "{l s='Supplier reference' mod='masseditproduct' js=true}";
		}
		#supplier .table td:nth-of-type(3):before {
			content: "{l s='Unit price tax excluded' mod='masseditproduct' js=true}";
		}
		#supplier .table td:nth-of-type(4):before {
			content: "{l s='Unit price currency' mod='masseditproduct' js=true}";
		}
	}
</style>