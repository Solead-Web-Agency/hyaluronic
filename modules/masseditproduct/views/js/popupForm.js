/**
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
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2020 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

function PopupForm(popup_selector)
{
    this.popup = $(popup_selector);
    this.ajaxCombinations = [];
    var _this = this;
    this.select_products = {};
    this.products = {};
    this.init = function () {
        _this.popup.delegate('.toggleList', 'click', function () {
            if ($(this).is('.active'))
            {
                _this.popup.find('.list_products, .popup_info_template').stop(true, true).slideUp(500);
                $(this).removeClass('active');
            }
            else
            {
                _this.popup.find('.list_products, .popup_info_template').stop(true, true).slideDown(500);
                $(this).addClass('active');
            }
        });
        _this.popup.delegate('.clearAll', 'click', function () {
            _this.popup.find('.product_item').each(function () {
                var id_product = $(this).data('id');
                _this.removeProduct(id_product);
            });
        });
        _this.popup.delegate('.removeProduct', 'click', function (e) {
            e.preventDefault();
            var product = $(this).closest('.product_item');
            var id_product = product.data('id');
            _this.removeProduct(id_product);
        });
        $('[class*=mode_]').stop(true, true).hide();
        $('.' + _this.popup.find('[name=mode]:checked').val()).stop(true, true).show();
        _this.popup.find('[name=mode]').change(function () {
            $('[class*=mode_]').stop(true, true).slideUp(500);
            $('.' + $(this).val()).stop(true, true).slideDown(500);
            // if($(this).val() == 'mode_edit')
            //     createListPositionsForImageCaption(_this);
        });

        _this.popup.find('.saveTemplateProduct').live('click', function (e) {
            e.preventDefault();
            var template_product = _this.popup.find('[name="template_product"]').val();
            if (!template_product) {
                $.alert(text_template_name_empty);
                setTimeout(function() {
                    $('body').find('.jconfirm').addClass('bootstrap');
                }, 1);
                return false;
            }

            if (!Object.size(_this.products)) {
                $.alert(text_not_products);
                setTimeout(function() {
                    $('body').find('.jconfirm').addClass('bootstrap');
                }, 1);
                return false;
            }

            $.ajax({
                url: document.location.href.replace('#'+document.location.hash, ''),
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'save_template_product',
                    products: _this.products,
                    name: _this.popup.find('[name="template_product"]').val()
                },
                success: function (json) {
                    if (json.hasError) {
                        $.alert(json.errors.join('\n'));
                        setTimeout(function() {
                            $('body').find('.jconfirm').addClass('bootstrap');
                        }, 1);
                    } else {
                        var $template_products = _this.popup.find('select[name="template_products"]');
                        $template_products.html('<option value="">-----</option>');

                        $.each(json.templates_products, function (index, template) {
                            $template_products.append('<option value="'+template.id+'">'+template.name+'</option>');
                        });

                        $template_products.closest('.select-template-block').addClass('active');
                    }
                }
            });
        });

        _this.popup.find('.deleteTemplateProduct').live('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: document.location.href.replace('#'+document.location.hash, ''),
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'delete_template_product',
                    id: _this.popup.find('[name="template_products"]').val()
                },
                success: function (json) {
                    _this.popup.find('[name="template_products"] option:selected').remove();
                    if ( _this.popup.find('[name="template_products"] option').size() == 0 ) {
                        _this.popup.find('[name="template_products"]').closest('.select-template-block').removeClass('active');
                    }  else if (_this.popup.find('[name="template_products"] option').text() == '-----') {
                        _this.popup.find('[name="template_products"]').closest('.select-template-block').removeClass('active');
                    }
                }
            });
        });

        _this.popup.find('.selectTemplateProduct').live('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: document.location.href.replace('#'+document.location.hash, ''),
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'get_template_product',
                    id: _this.popup.find('[name="template_products"]').val()
                },
                success: function (json) {
                    _this.popup.find('.list_products').html(json.popup_list);
                    $('.table_selected_products table tbody').html(json.list);
                    _this.products = json.products;
                    $('.table_selected_products .selector_container').setSelectorContainer();
                    var id_tab = parseInt(tab_container.tab.find('ul li.active').data('tab').replace('tab', ''));
                    if (id_tab == 'price' || id_tab == 'quantity') {
                        $('.table_selected_products [data-combinations]').show();
                    } else {
                        $('[data-combinations]').hide();
                    }

                    if (_this.ajaxCombinations.length) {
                        $.each(_this.ajaxCombinations, function (key, item) {
                            item.abort();
                        });
                        _this.ajaxCombinations = [];
                    }
                    var $col_combinations = $('.table_selected_products td[data-combinations]');
                    if ($col_combinations.length) {
                        $col_combinations.loadCombinationsOneRequest(_this.ajaxCombinations, function () {
                            $('.table_selected_products .selector_container').setSelectorContainer();
                        });
                    }
                    $('#beginSearch').trigger('click');
                }
            });
        });

        _this.updatePopup();
    };
    this.mergeProducts = function () {
        var products = _this.select_products;
        for (var i in products)
        {
            if (typeof _this.products[i] == 'undefined')
            {
                _this.products[i] = products[i];
                var product = products[i];
                _this.popup.find('.list_products')
                    .append('<div class="product_item product_'+product.id+'" data-id="'+product.id+'">'+
                        product.id+' - '+product.name
                        +' <a class="removeProduct" href="#"><i class="icon-remove"></i></a></div>');
                $('.table_search_product .product_' + product.id).find('[name=product]').removeAttr('checked');
                $('.table_search_product .product_' + product.id).removeClass('selected stateSelected').addClass('un-selected stateUnSelected');
                $('.table_selected_products table tbody').append($('.table_search_product .product_' + product.id).remove());
                $('.table_selected_products .selector_container').setSelectorContainer();
                var id_tab = parseInt(tab_container.tab.find('ul li.active').data('tab').replace('tab', ''));
                if (id_tab == 'price' || id_tab == 'quantity')
                    $('.table_selected_products [data-combinations]').show();
                else
                    $('[data-combinations]').hide();
            }
        }
        _this.resetSelect();
        _this.updatePopup();
        if (!$('.table_search_product tbody tr').length)
        {
            window.page = 1;
            $('#beginSearch').trigger('click');
        }
    };
    this.selectProduct = function (product) {
        if (typeof _this.select_products[product.id] == 'undefined')
        {
            _this.select_products[product.id] = product;
        }
        _this.updatePopup();
    };
    this.unselectProduct = function (id) {
        if (typeof _this.select_products[id] != 'undefined')
        {
            delete _this.select_products[id];
            _this.updatePopup();
            return true;
        }
        return false;
    };
    this.resetSelect = function ()
    {
        _this.select_products = {};
        _this.updatePopup();
    };
    this.removeProduct = function(id_product)
    {
        $('.table_search_product .no_products').remove();
        if (typeof _this.products[id_product] != 'undefined')
        {
            delete _this.products[id_product];
            _this.popup.find('.list_products .product_' + id_product).remove();
            $('.table_selected_products .product_' + id_product + ' .selector_container').trigger('destroy');
            $('.table_search_product table tbody').append($('.table_selected_products .product_' + id_product).remove());
        }
        _this.updatePopup();
    };
    this.updatePopup = function ()
    {
        /*
        if (!Object.size(_this.products))
            _this.popup.find('.toggleList, .clearAll').hide();
        else
            _this.popup.find('.toggleList, .clearAll').show();
        */

        _this.popup.find('.count_selected_products').text(Object.size(_this.select_products));
        _this.popup.find('.count_products').text(Object.size(_this.products));
    };
}