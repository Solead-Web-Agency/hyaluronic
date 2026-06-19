/*
* 2007-2015 PrestaShop
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
*  @copyright  2015-2019 Musaffar
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*/

PBPAdminConfigTabEditController = function(wrapper) {
	var self = this;
	self.wrapper = wrapper;
	self.$wrapper = $(wrapper);

	/* function render main form into the tab canvas */
	self.render = function(id_tab) {
		var url = module_config_url + '&route=pbpadminconfigtabscontroller&action=rendereditform';
		breadcrumb.add('Add / Edit Tab', url);

		if (typeof id_tab === 'undefined')
			id_tab = 0;

		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				id_tab : id_tab
			},
			success: function (html_result) {
				self.$wrapper.html(html_result);
				MPTools.waitEnd();
			}
		});
	};

	self.processForm = function() {
		var url = module_config_url + '&route=pbpadminconfigtabscontroller&action=processeditform';
		var form_data = self.$wrapper.find("#form-pbp-tab-edit :input").serialize();

		MPTools.waitStart();

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: form_data,
			dataType: "json",
			success: function (result) {
				MPTools.waitEnd();
				if (!MPTools.handleAjaxResponse(result, self.$wrapper.find(".alert"))) return false;
				breadcrumb.cancel();
			}
		});
	};

	self.init = function() {
	};
	self.init();

	/**
	 * Events
 	 */

	/**
	 * Cancel edit unit button click
	 */
	$("body").on("click", self.wrapper + ' #btn-pbp-edit-save', function () {
		self.processForm();
	});

	/**
	 * Cancel edit unit button click
	 */
	$("body").on("click", self.wrapper + ' #btn-pbp-edit-cancel', function () {
		breadcrumb.cancel();
	});

};