{*
* 2007-2019 Musaffar
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
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2007-2019 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div id="form-ppat-generaL-edit" class="form-wrapper pbp-form-wrapper col-xs-12">
	<input type="hidden" name="id_product" value="{$id_product|escape:'htmlall':'UTF-8'}">
	<div class="alert alert-danger mp-errors" style="display: none"></div>

	<div class="form-group row">
		<div class="col-lg-12">
            <label class="control-label" style="padding-right: 10px;">
                {l s='Disable parent product Add to Basket?' mod='productbundlespro'}
            </label>
			<input data-toggle="switch" class="" id="pbp-disabled_addtocart" name="pbp-disabled_addtocart" data-inverse="true" type="checkbox" value="1" {if $pbp_product_option->disabled_addtocart}checked{/if} />
		</div>
	</div>

	<div class="form-group row">
		<div class="col-md-12">
			<button type="button" id="pbp-btn-general-save" class="btn btn-primary">{l s='Save' mod='productbundlespro'}</button>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		prestaShopUiKit.init();
	});
</script>