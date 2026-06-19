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
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2015-2016 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<script>
	var pbp_front_ajax_url = "{$link->getModuleLink('productbundlespro', 'ajax', array())|escape:'quotes':'UTF-8' nofilter}";
	pbp_location = "{$pbp_general.pbp_location|escape:'html':'UTF-8'}";
    pbp_slider = "{$pbp_general.pbp_slider|escape:'html':'UTF-8'}";
	pbp_disabled_addtocart = "{$pbp_general.disabled_addtocart|escape:'html':'UTF-8'}";
    id_shop = "{$id_shop|escape:'html':'UTF-8'}";

    {if ($action eq 'quickview')}
		$(document).ready(function() {
			pbp_front_ajax_url = MPTools.joinUrl(pbp_front_ajax_url, 'route=pbpfrontproductcontroller');			
			pbp_front_product_controller = new PBPFrontProductController('#pbp-product-tabs', true);
		});
	{else}
		document.addEventListener("DOMContentLoaded", function(event) {
			$(function(){
				pbp_front_ajax_url = MPTools.joinUrl(pbp_front_ajax_url, 'route=pbpfrontproductcontroller');				
				pbp_front_product_controller = new PBPFrontProductController('#pbp-product-tabs', false);
			});
		});
	{/if}
</script>