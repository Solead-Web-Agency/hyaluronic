{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*}

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Block products by country' mod='blockproductsbycountry'}</h3>
	<a class="btn btn-default btn-basic bpbc-info_link" href="#"">{l s='Click for info' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-info" href="{$links.configure|escape:'quotes':'utf-8'}">{l s='Configure' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-danger bpbc-block_products_link" href="#">{l s='Block products' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-success" href="{$links.block_categories|escape:'quotes':'utf-8'}">{l s='Block categories' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-warning" href="{$links.block_manufacturers|escape:'quotes':'utf-8'}">{l s='Block manufacturers' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-info" href="{$links.block_suppliers|escape:'quotes':'utf-8'}">{l s='Block suppliers' mod='blockproductsbycountry'}</a>
	<a class="btn btn-default btn-basic" href="{$links.block_countries|escape:'quotes':'utf-8'}">{l s='Block by country' mod='blockproductsbycountry'}</a>
	<div class="bpbc-info-html" style="display: none;" data-title="{l s='Info' mod='blockproductsbycountry'}">
		<h3>{l s='The modules allows you to block products based on customer\'s country.' mod='blockproductsbycountry'}</h3>
		<p>{l s='You can build rules based on: Individual product, category, manufacturer, supplier.' mod='blockproductsbycountry'}</p>
		<p>{l s='For guests, we recommend you to activate geoIP from Prestashop, otherwise their country will be detected as Prestashop\'s default country, set in "Localization".' mod='blockproductsbycountry'}</p>
	</div>
</div>
