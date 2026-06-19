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

PBPAdminProductTabBundlesController = function(canvas) {

	var self = this;
	self.canvas = canvas;
	self.$canvas = $(canvas);

	self.list = "pbp-bundles";
	self.productList = "pbp-bundle-products";
	self.popupBundleId = 'pbp-popup-editbundle';
	self.addProductID = 'panel2';
	self.popup; //instance of modal popup
	self.id_bundle = ''; //id of bundle being edited
    self.bundle_existing_controller = [];

    self.select_discount_scope = self.canvas + ' select[name="pbp-discount-scope"]';
    self.input_discount_amount = self.canvas + ' #pbp-discount-amount';
    self.div_bundle_discount = self.canvas + ' #bundle-discount-wrapper';
    self.div_parent_product_discount = self.canvas + ' #parent-product-discount';

	self.render = function() {
		MPTools.waitStart();

		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=renderlist&rand=' + new Date().getTime();

		var post_data = {
			'id_product': id_product,
            'id_shop': id_shop
		};

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: post_data,
			success: function (html_result) {
				self.$canvas.html(html_result);

				$("#pbp-bundles-list-table tbody").sortable({
					update: function (event, ui) {
						self.processBundleListPositions();
					}
				}).disableSelection();

				MPTools.waitEnd();
			}
		});
	};

	self.openBundlesForm = function(id_bundle) {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=renderbundleform&id_product=' + id_product + 'rand=' + new Date().getTime();

		if (typeof (id_bundle) !== 'undefined') {
			url = url + '&id_bundle=' + id_bundle;
			self.id_bundle = id_bundle;
		} else
			self.id_bundle = '';

		self.popup = new MPPopup(self.popupBundleId, self.canvas);

		self.popup.showContent(url, null, function () {
			self.refreshProductList();
			MPTools.waitEnd();
		});
		return false;
	};

    /**
     * close the bundles form
     */
	self.closeBundlesForm = function() {
	    self.popup.close();
    };

	/**
	 * save the general options
	 */
	self.processForm = function() {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabgeneralcontroller&action=processform&id_shop=' + id_shop;

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: self.$canvas.find(" :input, select").serialize(),
			success: function (result) {
				MPTools.waitEnd();
			}
		});
	};

	/**
	 * save theposition of the bundles after a drag drop event
	 */
	self.processBundleListPositions = function() {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=processbundlepositions&id_product=' + id_product;

		var bundle_positons = [];
		$("#pbp-bundles-list-table tbody tr").each(function(index) {
			bundle_positons[index] = $(this).attr("data-id");
		});

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_product': id_product,
				'bundle_positions' : bundle_positons
			},
			success: function (result) {
				MPTools.waitEnd();
			}
		});
	};

	/**
	 * Delete a bundle from a product
 	 */
	self.processDeleteBundle = function(id_bundle) {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=processdeletebundle';

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_product' : id_product,
				'id_bundle': id_bundle
			},
			success: function (result) {
				self.render();
			}
		});
	};

    /**
     * on discount cope select change
     */
    self.onSelectDiscountScopeChange = function ($sender) {
        if ($sender.val() == 'bundle') {
            $(self.div_bundle_discount).removeClass('pbp-disabled');
            $(self.div_parent_product_discount).hide();
        } else {
            $(self.div_bundle_discount).addClass('pbp-disabled');
            $(self.div_parent_product_discount).show();
        }
    };

    self.init = function() {
		//productsearch = new PBPProductSearchWidget("#" + self.popupBundleId);
        self.bundle_existing_controller = new PBPAdminProductTabBundleExistingController(self.canvas);
        self.bundle_existing_controller.onClose = function () {
            self.render();
        };
        self.render();
	};
	self.init();

    /**
     * Add products button click
     */
    $("body").on("change", self.select_discount_scope, function () {
        self.onSelectDiscountScopeChange($(this));
        return false;
    });


    /**
	 * Create Bundle Button Click
	 */
	$("body").on("click", "#pbp-btn-bundles-add", function() {
		self.openBundlesForm();
		return false;
	});

    /**
     * Create Bundle Button Click
     */
    $("body").on("click", "#pbp-btn-bundles-existing", function () {
        self.bundle_existing_controller.openForm();
        return false;
    });


    /**
	 * Edit bundle click
 	 */
	$("body").on("click", self.canvas + " .pbp-bundle-edit", function () {
		self.openBundlesForm($(this).attr("data-id"));
		return false;
	});

	/**
	 * Delete bundle click
 	 */
	$("body").on("click", self.canvas + " .pbp-bundle-delete", function () {
		self.processDeleteBundle($(this).attr("data-id"));
		return false;
	});



	/**
	 * **
	 * Events and Methods for the popup form *****************************************************************************
	 * **
	 */


	/**
	 * refresh the list of products in the bundle
	 * @returns {boolean}
	 */
	self.refreshProductList = function() {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=renderproductlist&id_bundle=' + self.id_bundle;

		$("#" + self.productList).load(
			url,
			{
				'is_ajax': 1
			},
			function () {
				$("#" + self.productList + ' tbody').sortable({
					update: function (event, ui) {
						self.saveBundleProductListPositions();
					}
				}).disableSelection();

				MPTools.waitEnd();
			}
		);
		return false;
	};


	/**
	 * Get list of products as json array
 	 */
	self.getProductsListAsJson = function() {
		var products = [];

		$products_table = $('#' + self.popupBundleId).find("#productsTable");
		$products_table.find("tr").each(function (i, obj) {
			if (typeof($(obj).attr("data-id_product")) !== 'undefined' && $(obj).attr("data-id_product") != '')
				products.push({
					'id_product' : $(obj).attr("data-id_product"),
					'name' : $(obj).attr("data-name"),
					'discount_type': $(obj).attr("data-discount_type"),
					'discount_amount': $(obj).attr("data-discount_amount"),
					'discount_tax': $(obj).attr("data-discount_tax"),
					'allow_oos': $(obj).attr("data-allow_oos"),
					'qty': $(obj).attr("data-qty")
				});
		});
		return products;
	};

	/**
	 * Save the entire bundle
 	 */
	self.processSaveBundle = function() {
		var bundle_products = self.getProductsListAsJson();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=processform';
        var enabled = $('#' + self.popupBundleId).find("input#enabled").is(":checked") | 0;
        var allow_selection = $('#' + self.popupBundleId).find("input#allow_selection").is(":checked") | 0;
        var parent_product_discount_amount = $('#' + self.popupBundleId).find("input#parent_product_discount_amount").val();
        var parent_product_discount_type = $('#' + self.popupBundleId).find("select#parent_product_discount_type").val();

        if ($(self.select_discount_scope).val() == 'bundle') {
            discount_enabled = 1;
            discount_amount = $(self.input_discount_amount).val();
        } else {
            discount_enabled = 0;
            discount_amount = 0;
        }

        let data = {
            'id_tab': $('#' + self.popupBundleId).find("select[name='id_tab']").val(),
            'id_bundle': self.id_bundle,
            'id_product': id_product,
            'id_shop': id_shop,
            'enabled': enabled,
            'allow_selection': allow_selection,
            'discount_enabled': discount_enabled,
            'discount_amount': discount_amount,
            'bundle_products': bundle_products,
            'parent_product_discount_amount' : parent_product_discount_amount,
            'parent_product_discount_type': parent_product_discount_type,

        };

        $('input.pbp-name').each(function() {
            data[$(this).attr('name')] = $(this).val();
        });

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: data,
			dataType : 'json',
			success: function (result) {
                if (!MPTools.handleAjaxResponse(result, self.$canvas.find(".alert"))) {
                    return false;
                }
				self.id_bundle = result.id_bundle;
				self.render();
			}
		});
		return false;
	};

	/**
	 * Save the positions of the products in a bundle
 	 */
	self.saveBundleProductListPositions = function() {
		MPTools.waitStart();
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=processbundleproductpositions&id_product=' + id_product;

		var bundle_product_positons = [];
		$("#" + self.productList + " tbody tr").each(function (index) {
			var id_pbp_product = $(this).attr("data-id_pbp_product");
			if (typeof (id_pbp_product) !== 'undefined')
				bundle_product_positons[index] = $(this).attr("data-id_pbp_product");
		});

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_product': id_product,
				'bundle_product_positions': bundle_product_positons
			},
			success: function (result) {
				MPTools.waitEnd();
			}
		});
	};

	/**
	 * Delete a product from a bundle process
 	 * @param id_product
	 */
	self.processDeleteProduct = function(id_pbp_product) {
		var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=processdeleteproduct';
		MPTools.waitStart();

		$.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
			    'id_pbp_product' : id_pbp_product,
				'id_bundle': self.id_bundle
			},
			success: function (result) {
				self.refreshProductList();
				MPTools.waitEnd();
			}
		});
	};

    /**
     * Load Add product, prepoulate with existing product information
     * @param id_product
     */
    self.editProduct = function (id_product) {
        var $panel2 = $('#' + self.popupBundleId);
        var url = module_config_url_pbp + '&section=adminproducttab&route=pbpadminproducttabbundlescontroller&action=getproductbundledata';

        $.ajax({
            type: 'POST',
            url: url,
            async: true,
            cache: false,
            dataType: "json",
            data: {
                'id_product': id_product,
                'id_bundle': self.id_bundle
            },
            success: function (result) {
				$panel2.find("input#id_product").val(result.id_product);
				$panel2.find("input#id_pbp_product").val(result.id_pbp_product);
                $panel2.find("input[name='product_search']").val(result.product_name);
				$panel2.find("input#discount_amount").val(result.discount_amount);

				if (result.allow_oos == "1") {
					$panel2.find("input#allow_oos").prop('checked', true);
				} else {
					$panel2.find("input#allow_oos").prop('checked', false);
				}

				$panel2.find("input#qty").val(result.qty);
                if (result.discount_type == 'percentage') {
                    $panel2.find("select#discount_type").val('percentage');
                } else {
                    $panel2.find("select#discount_type").val('money');
                }
                $panel2.find("input#discount_type").val(result.discount_amount);
                self.popup.showSubPanel('panel2');
            }
        });
        return false;
    };


	/**
	 * Add product to Bundles Product List
 	 */
	self.applyProductEditChanges = function() {
		$panel2 = $("#" + self.popupBundleId);

        var id_pbp_product = $panel2.find("input[name='id_pbp_product']").val();
		var id_product = $panel2.find("input[name='id_product']").val();
		var id_product_search = $panel2.find("#id_product_pbpproducts1").val();
		var name = $panel2.find("input[name='product_search']").val();
		var discount_type = $panel2.find("select[name='discount_type']").val();
		var discount_amount = $panel2.find("input[name='discount_amount']").val();
		var discount_tax = $panel2.find("select[name='discount_tax']").val();
		var allow_oos = '0';
		var qty = $panel2.find("input[name='qty']").val();

		if (id_product === '') {
			id_product = id_product_search;
		}

		if ($panel2.find("input[name='allow_oos']").is(":checked")) {
			allow_oos = '1';
		} else {
			allow_oos = '0';
		}
		$products_table = $('#' + self.popupBundleId).find("#productsTable");

        // is the product being edited (does it already exist in the product list table?)
        if (parseInt(id_pbp_product) > 0) {
            if ($products_table.find("tr[data-id_pbp_product='" + id_pbp_product + "']").length > 0) {
				$row = $products_table.find("tr[data-id_pbp_product='" + id_pbp_product + "']");
				$row.attr("data-id_product", id_product);
				$row.attr("data-id_pbp_product", id_pbp_product);
				$row.attr("data-name", name);
				$row.attr("data-discount_type", discount_type);
				$row.attr("data-discount_amount", discount_amount);
				$row.attr("data-qty", qty);
				$row.attr("data-allow_oos", allow_oos);

				$row.find("td.id_product").html(id_product);
				$row.find("td.name").html(name);
				$row.find("td.discount_type").html(discount_type);
				$row.find("td.discount_amount").html(discount_amount);
				$row.find("td.qty").html(qty);
				return false;
            }
        }
        
		var $cloned = $products_table.find("tr.cloneable").clone();
		$cloned.removeClass("cloneable");
		$cloned.removeClass("hidden");
		$cloned.addClass("result-item");

		/* set data */
		$cloned.find("td.id_product").html(id_product);
		$cloned.attr("data-id_product", id_product);

		$cloned.find("td.name").html(name);
		$cloned.attr("data-name", name);

		$cloned.find("td.discount_type").html(discount_type);
		$cloned.attr("data-discount_type", discount_type);

		$cloned.find("td.discount_amount").html(discount_amount);
		$cloned.attr("data-discount_amount", discount_amount);

		$cloned.find("td.discount_tax").html(discount_tax);
		$cloned.attr("data-discount_tax", discount_tax);

		$cloned.find("td.qty").html(qty);
		$cloned.attr("data-qty", qty);

		$cloned.appendTo($products_table.find("tbody"));
	};

    /**
	 * Add products button click
	 */
	$("body").on("click", "#" + self.popupBundleId + " #pbp-btn-bundle-addproduct", function () {
		self.popup.showSubPanel('panel2');
		return false;
	});

    /**
     * Edit icon for bundle product
     */
    $("body").on("click", "#" + self.popupBundleId + " .pbp-bundle-product-edit", function () {
        self.editProduct($(this).attr("data-id_product"));
    });

	/**
	 * Delete a product from bundle click
 	 */
	$("body").on("click", "#" + self.popupBundleId + " .pbp-bundle-product-delete", function () {
		self.processDeleteProduct($(this).attr("data-id_pbp_product"));
		return false;
	});


	/**
	 * Add product button Done Click
 	 */
	$("body").on("click", "#" + self.popupBundleId + " #pbp-btn-bundle-addproduct-done", function () {
		self.applyProductEditChanges();
		self.popup.hideSubPanel('panel2');
		return false;
	});

	$("body").on("click", "#" + self.popupBundleId + " #pbp-btn-bundle-addproduct-cancel", function () {
		self.popup.hideSubPanel('panel2');
		return false;
	});

	/**
	 * Save entire bundle click
 	 */
	$("body").on("click", "#pbp-popup-bundle-save", function() {
		self.processSaveBundle();
		return false;
	});

    /**
     * Save entire bundle click
     */
    $("body").on("click", "#pbp-popup-close", function () {
        self.closeBundlesForm();
        return false;
    });
};

