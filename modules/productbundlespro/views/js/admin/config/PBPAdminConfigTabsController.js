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

PBPAdminConfigTabsController = function(wrapper) {
	var self = this;
	self.wrapper = wrapper;
	self.$wrapper = $(wrapper);

	/* sub controllers */
	self.tab_edit_controller = {}; //FPGAdminProductTabEditGiftController();

	/* function render main form into the tab canvas */
	self.renderList = function() {
		var url = module_config_url + '&route=pbpadminconfigtabscontroller&action=rendertabeditform';
		var post_data = {};
		breadcrumb.add('Tabs', url, post_data);

		MPTools.waitStart();

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {},
			success: function (html_content) {
				self.$wrapper.html(html_content);
				MPTools.waitEnd();
			}
		});
	};

	self.processForm = function() {
		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=pbpadminconfiggeneralcontroller&action=processform',
			async: true,
			cache: false,
			//dataType: "json",
			data: self.$wrapper.find(" :input, select").serialize(),
			success: function (jsonData) {
				//self.render();
				MPTools.waitEnd();
			}
		});
	};

	/**
	 * Commit delete tab action
 	 */
	self.processDelete = function(id_tab) {
		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=pbpadminconfigtabscontroller&action=processdelete',
			async: true,
			cache: false,
			data: {
				id_tab : id_tab
			},
			success: function (jsonData) {
				MPTools.waitEnd();
				self.renderList();
			}
		});
	};

	self.init = function() {
		self.tab_edit_controller = new PBPAdminConfigTabEditController(self.wrapper);
		self.renderList();
	};
	self.init();

	/* Events */


	/**
	 * Add nwe tab button click
 	 */
	$("body").on("click", self.wrapper + '  #pbp-btn-tab-add', function () {
		self.tab_edit_controller.render();
		return false;
	});


	/**
	 * Edit a tab click
 	 */
	$("body").on("click", self.wrapper + '  .pbp-tab-edit', function () {
		self.tab_edit_controller.render($(this).attr("data-id_tab"));
		return false;
	});

	/**
	 * Delete a tab click
 	 */
	$("body").on("click", self.wrapper + '  .pbp-tab-delete', function () {
		self.processDelete($(this).attr("data-id_tab"));
		return false;
	});


};

