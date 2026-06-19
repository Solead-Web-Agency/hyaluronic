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

$.fn.rowCopy = function () {
    $.each(this, function (index, item) {
        var elem = $(item);
        var _ajax = null;
        /**
         *
         * @type {string[]}
         */
        var form_elements = ['_search', '_id_product', '_lang', '_submit', 'search_input', 'selected_product'];

        if (elem.is('._row_copy')
            && elem.find('.' + form_elements.join(', .')).length == form_elements.length)
        {
            elem.find('._search').live('keyup focus', function () {
                elem.find('.search_result').remove();
                if (!elem.find('._search').val())
                    return false;
                sendAjax({
                    ajax: true,
                    action: 'row_copy_search_product',
                    query: elem.find('._search').val()
                }, function (r)
                {
                    /**
                     *
                     * @type {jQuery}
                     */
                    var html = $('<div class="search_result"></div>');
                    $.each(r, function (index, item) {
                        html.append('<div data-id-product="'+item.id_product+'" class="">'+item.name+'</div>');
                    });
                    elem.find('._search').after(html);
                });
            });

            elem.find('._submit').live('click', function () {
                var self = $(this);
                sendAjax({
                    ajax: true,
                    action: 'copy_field_' + self.data('field'),
                    id_product: elem.find('._id_product').val(),
                    id_lang: elem.find('._lang').val()
                }, function (r) {
                    if (typeof r.response != 'undefined')
                    {
                        /**
                         *
                         * @type {Redactor}
                         */
                        var redactor = $('[name="'+self.data('field')+'"].editor_html').data('redactor');
                        redactor.setCode(r.response);
                    }
                });
            });

            elem.find('.search_input').live('click', function (e) {
                if ($(e.target).is('[data-id-product]'))
                {
                    elem.find('._id_product').val($(e.target).data('id-product'));
                    elem.find('.selected_product').text($(e.target).text());
                    elem.find('._search').val('');
                    elem.find('.search_result').remove();
                }
            });
            var body = $('body');
            var data_search_input = body.data('search_input');
            if (!data_search_input)
            {
                body.data('search_input', true);
                body.live('click', function (e) {
                    if (!$(e.target).closest('.search_input').length)
                        $('.search_result').remove();
                });
            }
        }

        if (elem.find('.' + form_elements.join(', .')).length != form_elements.length)
            console.log('Can not load row copy plugin!');

        function sendAjax(data, success, error)
        {
            if (_ajax != null)
            {
                _ajax.abort();
                _ajax = null;
            }

            _ajax = $.ajax({
                url: document.location.href.replace(document.location.hash, ''),
                type: 'POST',
                dataType: 'json',
                data: data,
                success: success,
                error: error
            });
        }
    });
};

$.fn.loadCombinations = function (ajax_array, after_load_func) {
    var counter_load_combinations = 0;
    var length = $(this).length;
    $(this).each(function () {
        var id_product = $(this).data('combinations');
        var $this = $(this);
        $this.addClass('loading');
        ajax_array.push($.ajax({
            url: document.location.href.replace(document.location.hash, ''),
            type: 'POST',
            dataType: 'json',
            asinc: false,
            data: {
                ajax: true,
                action: 'load_combinations',
                id_product: id_product
            },
            success: function (json) {
                $this.html(json.combinations);
                $this.removeClass('loading');

                counter_load_combinations++;

                if (counter_load_combinations == length) {
                    if (typeof after_load_func != 'undefined') {
                        after_load_func();
                    }
                }
            }
        }));
    });
};

//instead loadCombinations() for one ajax request
$.fn.loadCombinationsOneRequest = function (ajax_array, after_load_func) {
    var $this = $(this);
    var ids_product = [];
    $(this).each(function(){
        ids_product.push($(this).data('combinations'));
    });

    $.ajax({
        url: document.location.href.replace(document.location.hash, ''),
        type: 'POST',
        dataType: 'json',
        asinc: false,
        data: {
            ajax: true,
            action: 'load_combinations_one_request',
            ids_product: ids_product
        },
        success: function (json) {
            $this.each(function(){
                $(this).html(json[$(this).data('combinations')]);
            });

            if (typeof after_load_func != 'undefined') {
                after_load_func();
            }
        }
    });
};