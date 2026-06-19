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

PBPAdminConfigMassAssignController = function (wrapper) {
    var self = this;
    self.wrapper = wrapper;
    self.$wrapper = $(wrapper);

    self.div_categorytree = self.wrapper + ' ul#category';
    self.div_categoryproducts = self.wrapper + ' #category-products';
    self.input_product_checked_all = self.div_categoryproducts + ' input.id_category_product_all';
    self.input_id_product = self.wrapper + ' input[name="id_product"]';
    self.button_mass_assign_apply = self.wrapper + ' button#pbp-mass-assign-apply';
    self.button_mass_assign_delete = self.wrapper + ' button#pbp-mass-assign-delete';

    self.route = 'pbpadminconfigmassassigncontroller';

    /**
     * Render the equation templates list
     */
    self.render = function () {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=render',
            async: true,
            cache: false,
            data: {},
            success: function (html_content) {
                self.$wrapper.html(html_content);

                $(self.div_categorytree).parents(".form-group").addClass('disabled');
                $(self.div_categoryproducts).parents(".form-group").addClass('disabled');
                $(self.button_mass_assign_apply).prop('disabled', true);
                $(self.button_mass_assign_delete).prop('disabled', true);

                search_widget.events.onResultSelect = function () {
                    self.onSearchResultSelect();
                };

                MPTools.waitEnd();
            }
        });
    };

    /**
     * on product search result select
     */
    self.onSearchResultSelect = function () {
        $(self.div_categorytree).parents(".form-group").removeClass('disabled');
        $(self.div_categoryproducts).parents(".form-group").removeClass('disabled');
        $(self.button_mass_assign_apply).prop('disabled', false);
    };

    /**
     * Render products in a category
     * @param id_category
     */
    self.renderProducts = function (id_category) {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=renderproducts',
            async: true,
            cache: false,
            data: {
                id_category: id_category,
                id_product: $(self.input_id_product).val()
            },
            success: function (html_content) {
                self.setCategoryProducts(html_content);
                MPTools.waitEnd();
            }
        });
    };

    /**
     * mass assign to list of products
     * @param arr_selected_products
     */
    self.processMassAssignProducts = function(arr_selected_products) {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=processmassassignproducts',
            async: true,
            cache: false,
            data: {
                id_product: $(self.input_id_product).val(),
                id_product_new: arr_selected_products
            },
            success: function (result) {
                $.growl.notice({title: "", message: 'Mass Assign Completed'});
                MPTools.waitEnd();
            },
            error: function (request, status, error) {
            }
        });
    };

    /**
     * Mass assign category mass assignment
     * @param arr_id_categories
     */
    self.processMassAssignCategories = function(arr_id_categories) {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=processmassassigncategories',
            async: true,
            cache: false,
            data: {
                id_product: $(self.input_id_product).val(),
                id_categories: arr_id_categories
            },
            success: function (result) {
                MPTools.waitEnd();
                $.growl.notice({title: "", message: 'Mass Assign Completed'});
            },
            error: function (request, status, error) {
            }
        });
    }

    /**
     * mass assign to list of products
     * @param arr_selected_products
     */
    self.processMassAssignDeleteProducts = function (arr_selected_products) {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=processmassassigndeleteproducts',
            async: true,
            cache: false,
            data: {
                id_products: arr_selected_products
            },
            success: function (result) {
                MPTools.waitEnd();
                $.growl.notice({title: "", message: 'Mass Assign Completed'});
            },
            error: function (request, status, error) {
            }
        });
    };

    /**
     * Mass assign category mass assignment
     * @param arr_id_categories
     */
    self.processMassAssignDeleteCategories = function (arr_id_categories) {
        MPTools.waitStart();
        $.ajax({
            type: 'POST',
            url: module_config_url + '&route=' + self.route + '&action=processmassassigndeletecategories',
            async: true,
            cache: false,
            data: {
                id_categories: arr_id_categories[i]
            },
            success: function (result) {
                MPTools.waitEnd();
                $.growl.notice({title: "", message: 'Mass Assign Completed'});
            },
            error: function (request, status, error) {
            }
        });
    }

    /**
     * Delete all existing bundles from product selection
     */
    self.processMassAssignDelete = function () {
        let arr_selected_categories = self.getSelectedCategories();
        let arr_selected_products = self.getSelectedProducts();

        if (arr_selected_products.length > 0) {
            self.processMassAssignDeleteProducts(arr_selected_products);
            return false;
        }

        if (arr_selected_categories.length > 0) {
            self.processMassAssignDeleteCategories(arr_selected_categories);
            return false;
        }
    };


    /**
     * process mass assign
     */
    self.processMassAssign = function () {
        let arr_selected_categories = self.getSelectedCategories();
        let arr_selected_products = self.getSelectedProducts();

        if (arr_selected_products.length > 0) {
            self.processMassAssignProducts(arr_selected_products);
            return false;
        }

        if (arr_selected_categories.length > 0) {
            self.processMassAssignCategories(arr_selected_categories);
            return false;
        }
    };

    /**
     * set html for the category products div
     * @param html_content
     */
    self.setCategoryProducts = function (html_content) {
        $(self.div_categoryproducts).html(html_content);
    };

    /**
     * get selected categories array
     * @returns {jQuery}
     */
    self.getSelectedCategories = function () {
        return $(self.div_categorytree + ' input[type=checkbox]:checked').map(function (_, el) {
            return $(el).val();
        }).get();
    };

    /**
     * get selected categories array
     * @returns {jQuery}
     */
    self.getSelectedProducts = function () {
        return $(self.div_categoryproducts + ' input[type=checkbox]:checked').map(function (_, el) {
            return $(el).val();
        }).get();
    };

    /**
     * on product search result select
     */
    self.onSearchResultSelect = function () {
        $(self.div_categorytree).parents(".form-group").removeClass('disabled');
        $(self.div_categoryproducts).parents(".form-group").removeClass('disabled');
        $(self.button_mass_assign_apply).prop('disabled', false);
    };

    /**
     * On category tree item select
     * @param $sender
     */
    self.onCategorySelect = function ($sender) {
        let selected_categories = self.getSelectedCategories();
        self.setCategoryProducts('');
        if (selected_categories.length == 1) {
            self.renderProducts(selected_categories[0]);
        }

        if (selected_categories.length > 0) {
            $(self.button_mass_assign_delete).prop('disabled', false);
        } else {
            $(self.button_mass_assign_delete).prop('disabled', true);
        }
    };

    /**
     * set checkbox for all products to checked state supplied as parameter
     * @param state
     */
    self.checkAllProducts = function (state) {
        $(self.div_categoryproducts).find("input[type='checkbox']").prop('checked', state);
    };

    /**
     * on product checkbox checked / unchecked
     */
    self.onProductCheckChange = function () {
        let check_count = $(self.div_categoryproducts).find("input.id_category_product").length;
        let checked_count = $(self.div_categoryproducts).find("input.id_category_product:checked").length;

        $(self.input_product_checked_all).prop('indeterminate', false);
        if (checked_count == 0) {
            $(self.input_product_checked_all).prop('checked', false);
        } else if (checked_count == check_count) {
            $(self.input_product_checked_all).prop('checked', true);
        } else if (checked_count != check_count) {
            $(self.input_product_checked_all).prop('indeterminate', true);
        }
    };

    /**
     * Init
     */
    self.init = function () {
        self.render();
    };
    self.init();

    /**
     * Events
     */

    /**
     * On category item select
     */
    $("body").on("change", self.div_categorytree + " input[type='checkbox']", function () {
        self.onCategorySelect($(this));
        return false;
    });

    /**
     * select all products checkbox change
     */
    $("body").on("change", self.input_product_checked_all, function () {
        if ($(this).is(':checked')) {
            self.checkAllProducts(true);
        } else {
            self.checkAllProducts(false);
        }
        return false;
    });

    /**
     * select all products checkbox change
     */
    $("body").on("change", self.div_categoryproducts + " input.id_category_product", function () {
        self.onProductCheckChange();
        return false;
    });


    /**
     * on form submit
     */
    $("body").on("click", self.button_mass_assign_apply, function () {
        self.processMassAssign();
        return false;
    });

    /**
     * on form submit
     */
    $("body").on("click", self.button_mass_assign_delete, function () {
        if (confirm('Are you sure you want to remove all bundles from the selected products / categories')) {
            self.processMassAssignDelete();
        }
        return false;
    });

};
