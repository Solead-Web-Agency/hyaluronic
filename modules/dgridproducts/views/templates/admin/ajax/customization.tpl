{*
* 2007-2016 PrestaShop
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
*  @author    SeoSA <885588@bk.ru>
*  @copyright 2012-2022 SeoSA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<h3>{l s='Customization' mod='dgridproducts'}</h3>
<div class="row">
	<div class="form-group clearfix">
		<div class="col-xs-12 col-lg-1"></div>
		<label class="control-label col-xs-12 col-lg-2">{l s='File fields' mod='dgridproducts'}</label>
		<div class="col-xs-2 col-lg-1 fixed-width-xxl">
			<input type="text" name="uploadable_files" value="{$product->uploadable_files|intval}">
		</div>
	</div>
	<div class="form-group clearfix">
		<div class="col-xs-12 col-lg-1"></div>
		<label class="control-label col-xs-12 col-lg-2">{l s='Text fields' mod='dgridproducts'}</label>
		<div class="col-xs-2 col-lg-1 fixed-width-xxl">
			<input type="text" name="text_fields" value="{$product->text_fields|intval}">
		</div>
	</div>
	<div class="clearfix">
		<div class="col-xs-12 col-lg-1"></div>
		<label class="control-label col-xs-12 col-lg-2"></label>
		<div class="col-xs-12 col-lg-1 ">
			<button id="getCustomizationBlock" class="btn btn-inverse">{l s='Update' mod='dgridproducts'}</button>
		</div>
	</div>
    {if is_array($file_fields) && count($file_fields)}
		<hr>
        {foreach from=$file_fields key=key item=file_field}
			<div class="form-group clearfix">
				<div class="col-lg-1">
					<input type="hidden" name="file_fields_arr[]" value="{$key|intval}">
					<input type="hidden" name="file_field_id_{$key|intval}" value="{$file_field->id|intval}">
					<input type="hidden" name="file_field_type_{$key|intval}" value="{$file_field->type|intval}">
				</div>
				<label class="control-label col-lg-2">
                    {l s='Label file field' mod='dgridproducts'}
				</label>
				<div class="col-lg-5">
                    {include file="./fields/{$smarty.const._PS_VERSION_|floatval}/input_text_lang.tpl"
                    languages=$languages
                    input_value=$file_field->name
                    input_name="file_field_name_{$key|intval}"}
				</div>
				<div class="col-lg-4">
                    {l s='Required?' mod='dgridproducts'}
					<input type="checkbox" name="file_field_required_{$key|intval}" {if $file_field->required}checked{/if} value="1">
				</div>
			</div>
        {/foreach}
    {/if}
    {if is_array($text_fields) && count($text_fields)}
        {foreach from=$text_fields key=key item=text_field}
			<div class="form-group clearfix">
				<div class="col-lg-1">
					<input type="hidden" name="text_fields_arr[]" value="{$key|intval}">
					<input type="hidden" name="text_field_id_{$key|intval}" value="{$text_field->id|intval}">
					<input type="hidden" name="text_field_type_{$key|intval}" value="{$text_field->type|intval}">
				</div>
				<label class="control-label col-lg-2">
                    {l s='Label text field' mod='dgridproducts'}
				</label>
				<div class="col-lg-5">
                    {include file="./fields/{$smarty.const._PS_VERSION_|floatval}/input_text_lang.tpl"
                    languages=$languages
                    input_value=$text_field->name
                    input_name="text_field_name_{$key|intval}"}
				</div>
				<div class="col-lg-4">
                    {l s='Required?' mod='dgridproducts'}
					<input type="checkbox" name="text_field_required_{$key|intval}" {if $text_field->required}checked{/if} value="1">
				</div>
			</div>
        {/foreach}
    {/if}
</div>
