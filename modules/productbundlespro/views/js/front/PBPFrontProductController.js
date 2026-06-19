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

PBPFrontProductController = function(wrapper, quickview) {
	var self = this;
	self.wrapper = wrapper;
	self.$wrapper = $(wrapper);
	self.module_folder = 'productbundlespro';
	self.product_info = [];
	self.pbp_location = pbp_location;
	self.pbp_slider = pbp_slider;
	self.quickview = quickview;
	self.debug = false;
	self.slider = false;
	self.is_quickview = false;

	/**
	 * Get the ID of the current product
 	 * @returns {jQuery}
	 */	
	self.getProductID = function() {
		if (!self.quickview) {
			var id_product = $("form#add-to-cart-or-refresh input[name='id_product']").val();
		} else {
			var id_product = $(".modal.quickview input[name='id_product']").val();
		}
		return id_product;
	};


	/**
	 * Render the widget
 	 */
	self.renderWidget = function() {
		var parent_product_data = $("#add-to-cart-or-refresh").serialize();
		var url = MPTools.joinUrl(pbp_front_ajax_url, 'action=renderwidget&rand=' + new Date().getTime() + '&' + parent_product_data);

        return $.ajax({
            type: 'POST',
            url: url,
            async: true,
            cache: false,
			data: {
				'id_product' : self.getProductID(),
                'id_shop': id_shop,
				'parent_product_data' : parent_product_data
			},
            success: function(html) {
                if (html === '') {
                    return false;
                }
				if (!self.quickview) {
					if (self.pbp_location == 'product-footer')
						$(html).insertAfter("section#main");
					else
						$(html).insertAfter(".product-add-to-cart");
				} else {
                    modal_width = $(".modal.quickview .modal-content").width();
					$(html).insertBefore('.modal.quickview .modal-footer');
				}
				self.$wrapper = $(self.wrapper);

				if (self.quickview) {
                    self.$wrapper.width(modal_width);
                }

                if (self.pbp_slider == 1) {
                    let $first_tab = self.$wrapper.find('.tab-content').first();
                    if (self.getBundleCount($first_tab) > 1) {
                        let tab_id = $first_tab.attr('id');
                        new Splide('#' + tab_id, {}).mount();
                    }
                }
            }
        });
	};

    /**
     * Get number of bundles by tab
     * @param $tab
     * @returns {number}
     */
	self.getBundleCount = function($tab) {
	    return $tab.find(".bundle").length;
    };

	/**
	 * Get details of parent preoduct being added to cart as part of a bundle
 	 * @param $dom_bundle
	 * @returns {Object}
	 */
	self.getMainProductCartInfo = function($dom_bundle) {
		product = new Object();
		product.id_product = id_product;

		if (typeof $dom_bundle !== 'undefined') {
			if ($dom_bundle.find("select.parent_product_ipa").length > 0) {
				product.ipa = $dom_bundle.find("select.parent_product_ipa").val();
			}
			else
				product.ipa = self.product_info.id_product_attribute;
		}
		return product;
	};

	/**
	 * COnstruct array of products to add to cart as a bundle
 	 * @param $dom_bundle
	 * @returns {Array}
	 */
	self.constructCartProductArray = function($dom_bundle) {
		pbp_cart_products = [];
		var add_child_product = true;

		$dom_bundle.find(".child-product").each(function () {
            add_child_product = true;
			if ($(this).find("select.bundle_product_ipa").length > 0) {
                var value = $(this).find("select.bundle_product_ipa").val();
            }

            // allowing selection of child products?
            if ($(this).find("input[type='checkbox']").length > 0) {
                if ($(this).find("input[type='checkbox']").is(':checked')) {
                    add_child_product = true;
                } else {
                    add_child_product = false;
                }
            }

            if (add_child_product) {
                product = new Object();
                product.id_pbp_product = $(this).attr("data-id_pbp_product");
                product.id_unique = $(this).attr("data-id_unique");
                product.id_product = $(this).attr("data-id_product");
                product.ipa = value;
                pbp_cart_products.push(product);
            }
		});
		return pbp_cart_products;
	};

	/**
	 * Update Product Information such as product price, attribute price tax etc.  This information will be used to calculate dynamic price
	 */
	self.getProductInfo = function() {
		var query = $("#add-to-cart-or-refresh").serialize();
        var url = MPTools.joinUrl(pbp_front_ajax_url, 'action=getproductinfo&rand=' + new Date().getTime() + '&' + query);

		return $.ajax({
			type: 'POST',
			url: url,
			async: true,
			cache: false,
			data: {
				'id_product' : $("input[name='id_product']").val()
			},
			dataType: 'json',
			success: function(resp) {
				self.product_info = resp;
			}
		});
	};

	/**
	 * Hide the parent product add to basket button
	 */
	self.hideParentProductAddToBasket = function() {
		$("#main .product-add-to-cart").hide();
	};


	/**
	 * Process add bundle to cart
 	 * @param $dom_bundle
	 */
	self.addPBPToCart = function ($dom_bundle) {
		var pbp_cart_products = self.constructCartProductArray($dom_bundle);
        var url = MPTools.joinUrl(pbp_front_ajax_url, 'action=processaddtocart&rand=' + new Date().getTime());
        var quantity = $dom_bundle.find("input[name='pbp_quantity']").val();

		$.ajax({
			type: 'POST',
			headers: {"cache-control": "no-cache"},
			url: url,
			cache: false,
			dataType : "json",
			data: {
				id_product: id_product,
				id_bundle : $dom_bundle.attr('data-id_bundle'),
				'pbp_cart_products' : pbp_cart_products,
				'pbp_cart_parent_product': self.getMainProductCartInfo($dom_bundle),
                'quantity' : quantity
			},
			success: function (result) {
				window.location.href = result.redirect_url;
			}
		});
	};

	/**
	 * Update all the bundle prices
	 */
	self.updateAllBundlePrices = function () {
		$("#pbp-product-tabs .bundle").each(function (i, item) {
			self.updateBundlePrices($(item));
		});
	};

	/**
	 *
 	 * @param $bundle jquery bundle element
	 */
	self.updateBundlePrices = function($bundle) {
		var pbp_cart_products = self.constructCartProductArray($bundle);
        var url = MPTools.joinUrl(pbp_front_ajax_url, 'action=getbundleprices&rand=' + new Date().getTime());

		$.ajax({
			type: 'POST',
			headers: {"cache-control": "no-cache"},
			url: url,
			cache: false,
			dataType : "json",
			data: {
				id_product: id_product,
				id_bundle : $bundle.attr('data-id_bundle'),
				'pbp_cart_products' : pbp_cart_products,
				'pbp_cart_parent_product': self.getMainProductCartInfo($bundle)
			},
			success: function (result) {
                if (typeof result.bundle_products !== 'undefined') {
                    //parent product (in bundle)
                    $bundle.find(".parent-product-price-original").html(result.parent_product.price_original_formatted);
                    $bundle.find(".parent-product-price-discounted").html(result.parent_product.price_discount_formatted);

                    // bundle totals
                    $bundle.find(".pbp_bundle_original_total").html(result.bundle_original_total_formatted);
                    $bundle.find(".pbp_bundle_total").html(result.bundle_total_formatted);
                    $bundle.find(".pbp_bundle_saving_total").html(result.bundle_total_saving_formatted);

                    // bundle child products
                    for (i = 0; i <= result.bundle_products.length - 1; i++) {
                        $product = $bundle.find(".child-product[data-id_product='" + result.bundle_products[i].id_product + "'][data-id_unique='" + result.bundle_products[i].id_unique + "']");
                        $product.find(".pbp-discount-amount").html(result.bundle_products[i].saving_formatted + ' (' + result.bundle_products[i].saving_percent + '%)');
                        $product.find(".pbp-offer-price").html(result.bundle_products[i].offer_price_formatted);
                        $product.find(".pbp-original-price").html(result.bundle_products[i].original_price_formatted);
                        $product.find(".pbp_bundle_total").html(result.bundle_total_formatted);
                        $product.parentsUntil(".bundle").find('.pbp_bundle_total').html(result.bundle_total_formatted);
                    }
                }
            }
		});
	};

    /**
     * prevent a bundle from being added to the cart
     * @param $bundle
     */
    self.enableBundle = function ($bundle) {
        $bundle.find(".bundle-action").fadeTo(100, 1);
        $bundle.find(".btn_add_bundle_cart").prop('disabled', false);
        ;
    };

    /**
     * prevent a bundle from being added to the cart
     * @param $bundle
     */
	self.disableBundle = function($bundle) {
	    $bundle.find(".bundle-action").fadeTo(100, 0.5);
	    $bundle.find(".btn_add_bundle_cart").prop('disabled', true);
    };

    /**
     * on child product selected / deselected
     */
    self.onChildProductCheckboxChanged = function ($sender) {
        $bundle = $sender.parents(".bundle");
        if ($bundle.find("input[type='checkbox']:checked").length == 0) {
            self.disableBundle($bundle);
        } else {
            self.enableBundle($bundle);
        }
        self.updateBundlePrices($bundle);
    };

    /**
     * on show more bundles button click
     * @param $sender
     */
    self.btnShowMoreBundlesClick = function($sender) {
        $sender.hide();
        self.$wrapper.find(".bundle").addClass('visible');
    };


    self.init = function() {
		if (pbp_disabled_addtocart == 1) {
			self.hideParentProductAddToBasket();
		}
		$.when(self.renderWidget()).then(self.getProductInfo);
	};
	self.init();

	/**
	 * Events
 	 */

	/**
	 * Add bundle to cart click
 	 */
	$(document).on('click', self.wrapper + ' .btn_add_bundle_cart', function() {
		self.addPBPToCart($(this).parents(".bundle"));
		return false;
	});

	/**
	 * on change of any bundle attribute drop down select
 	 */
	$("body").on("change", self.wrapper + " .bundle select", function () {
		self.updateBundlePrices($(this).parents(".bundle"));
	});

    /**
     * on select / deselect of child product
     */
    $("body").on("change", self.wrapper + " .bundle input[type='checkbox']", function () {
        self.onChildProductCheckboxChanged($(this));
    });

    /**
     * On tab click
     */
    $(document).on('click', self.wrapper + ' .tab-links a', function () {
        self.$wrapper.find(".tab-content").hide();
        self.$wrapper.find(".tab-links a").removeClass("active");
        $(this).addClass("active");
        var tab_id = $(this).attr("data-tab_id");
        self.$wrapper.find(".tab-content-" + tab_id).show();

        if (self.pbp_slider == 1) {
            new Splide(".tab-content-" + tab_id, {}).mount();
        }
        return false;
    });

    /**
     * on show more bundles button click
     */
    $("body").on("click", self.wrapper + " .btn-show-more-bundles", function () {
        self.btnShowMoreBundlesClick($(this));
    });

    /**
	 * On Attributes changed
	 */
	prestashop.on('updatedProduct', function (event) {
        if (pbp_disabled_addtocart == 1) {
            self.hideParentProductAddToBasket();
        }
        self.getProductInfo().then(function() {
			self.updateAllBundlePrices();
		});
	});


};