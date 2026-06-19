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
*  @copyright  2007-2019 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<ul class="nav nav-tabs" id="myTab" role="tablist">
	<li class="nav-item active">
		<a class="nav-link" data-toggle="tab" href="#pbp-general-tab" role="tab">{l s='General' mod='productbundlespro'}</a>
	</li>
	<li class="nav-item">
		<a class="nav-link" data-toggle="tab" href="#pbp-tabs-tab" role="tab">{l s='Bundle Tabs' mod='productbundlespro'}</a>
	</li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#pbp-mass-assign-tab" role="tab">{l s='Mass Assign' mod='productpricebysize'}</a>
    </li>
</ul>

<div class="tab-content">
	<div class="pbp-breadcrumb"></div>
	<div class="tab-pane active" id="pbp-general-tab" role="tabpanel"></div>
	<div class="tab-pane" id="pbp-tabs-tab" role="tabpanel"></div>
    <div class="tab-pane" id="pbp-mass-assign-tab" role="tabpanel"></div>
</div>

<script>
	$(document).ready(function () {
		breadcrumb = new Breadcrumb(".pbp-breadcrumb", "#pbp-tabs-tab");
		module_config_url = '{$module_config_url|escape:'quotes':'UTF-8'}';
        module_ajax_url_pbp = "{$module_ajax_url_pbp|escape:'quotes':'UTF-8' nofilter}";
		pbp_general_controller = new PBPAdminConfigGeneralController('#pbp-general-tab');
		pbp_tabs_controller = new PBPAdminConfigTabsController('#pbp-tabs-tab');
        pbp_mass_assign_controller = new PBPAdminConfigMassAssignController('#pbp-mass-assign-tab');
	});
</script>