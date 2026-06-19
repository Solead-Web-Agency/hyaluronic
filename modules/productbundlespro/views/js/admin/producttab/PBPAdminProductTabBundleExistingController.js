/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

PBPAdminProductTabBundleExistingController = function(wrapper) {
    self = this;
	self.wrapper = wrapper;
	self.$wrapper = $(wrapper);
	self.popupEditFormId = 'pbp-popup-existingbundle';

	self.btn_add = self.wrapper + ' #pbp-existing-bundle-add';

	self.selected_id_bundle = 0;
	self.selected_id_product = 0;

    self.productsearch = [];
    self.onClose = {};

	self.openForm = function() {
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlesexistingcontroller&action=renderform&id_product=' + id_product;
		self.popup = new MPPopup(self.popupEditFormId, self.wrapper);
		self.popup.showContent(url, null, function () {
			MPTools.waitEnd();
		});
		return false;
	};

    /**
     * Display bundles for an existing product
     * @param id_product
     */
	self.displayProductBundlesList = function(id_product) {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlesexistingcontroller&action=renderproductbundles&id_product=' + id_product;
		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_product': id_product,
                'id_shop': id_shop
			},
			success: function (result) {
				$("#pbp-bundles-existing-list").html(result);
				MPTools.waitEnd();
			}
		});
	};

    /**
     * Copy bundle tpo this product
     * @returns {boolean}
     */
	self.saveBundle = function() {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlesexistingcontroller&action=processaddbundle&id_product=' + self.selected_id_product;

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_tab' : self.$wrapper.find("#id_tab").val(),
				'id_product': http_get.id_product,
				'id_bundle' : self.selected_id_bundle,
                'id_shop' : id_shop
			},
			success: function (result) {
                self.onClose();
                self.popup.close();
                MPTools.waitEnd();
            }
		});
	};

    /**
     * Init
     */
	self.init = function() {
        //self.productsearch = new PBPProductSearchWidget("#" + self.popupEditFormId);
	};
	self.init();

    /**
     * On search result select
     */
	$("body").on("click", self.wrapper + " #search-results-table .result-item", function () {
		self.selected_id_product = $(this).attr('data-id_product');
		self.displayProductBundlesList($(this).attr('data-id_product'));
		return false;
	});

    /**
     * on bundle select
     */
	$("body").on("click", self.wrapper + " #pbp-bundles-existing-list .pbp-bundle", function () {
	    $(self.btn_add).prop('disabled', false);
		self.$wrapper.find("#pbp-bundles-existing-list .pbp-bundle").removeClass('selected');
		$(this).addClass("selected");
		self.selected_id_bundle = $(this).attr("data-id");
		return false;
	});

    /**
     * Add existing bundle to this product button click
     */
	$("body").on("click", self.btn_add, function () {
		self.saveBundle();
		return false;
	});

    /**
     * on Cancel
     */
    $("body").on("click", self.wrapper + " #pbp-edit-product-cancel", function () {
        self.popup.close();
        return false;
    });
};
