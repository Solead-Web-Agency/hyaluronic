/**
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
 * @copyright 2012-2022 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

var Pack_row = new function() {
    var self = this;

    this.bindPackEvents = function () {

        $('.delPackItem').on('click', function() {
            delPackItem($(this).data('delete'), $(this).data('delete-attr'));
        });

        function productFormatResult(item) {
            itemTemplate = "<div class='media'>";
            itemTemplate += "<div class='pull-left'>";
            itemTemplate += "<img class='media-object' width='40' src='" + item.image + "' alt='" + item.name + "'>";
            itemTemplate += "</div>";
            itemTemplate += "<div class='media-body'>";
            itemTemplate += "<h4 class='media-heading'>" + item.name + "</h4>";
            itemTemplate += "<span>REF: " + item.ref + "</span>";
            itemTemplate += "</div>";
            itemTemplate += "</div>";
            return itemTemplate;
        }

        function productFormatSelection(item) {
            return item.name;
        }

        var selectedProduct;
        $('#curPackItemName').select2({
            placeholder: search_product_msg,
            minimumInputLength: 2,
            width: '100%',
            dropdownCssClass: "bootstrap",
            ajax: {
                url: "ajax_products_list.php",
                dataType: 'json',
                data: function (term) {
                    return {
                        q: term
                    };
                },
                results: function (data) {
                    var excludeIds = getSelectedIds();
                    var returnIds = new Array();
                    if (data) {
                        for (var i = data.length - 1; i >= 0; i--) {
                            var is_in = 0;
                            for (var j = 0; j < excludeIds.length; j ++) {
                                if (data[i].id == excludeIds[j][0] && (typeof data[i].id_product_attribute == 'undefined' || data[i].id_product_attribute == excludeIds[j][1]))
                                    is_in = 1;
                            }
                            if (!is_in)
                                returnIds.push(data[i]);
                        }
                        return {
                            results: returnIds
                        }
                    } else {
                        return {
                            results: []
                        }
                    }
                }
            },
            formatResult: productFormatResult,
            formatSelection: productFormatSelection,
        })
            .on("select2-selecting", function(e) {
                selectedProduct = e.object
            });

        $('#add_pack_item').on('click', addPackItem);

        function addPackItem() {

            if (selectedProduct) {
                selectedProduct.qty = $('#curPackItemQty').val();
                if (selectedProduct.id == '' || selectedProduct.name == '') {
                    error_modal(error_heading_msg, msg_select_one);
                    return false;
                } else if (selectedProduct.qty == '') {
                    error_modal(error_heading_msg, msg_set_quantity);
                    return false;
                }

                if (typeof selectedProduct.id_product_attribute === 'undefined')
                    selectedProduct.id_product_attribute = 0;

                var divContent = $('#divPackItems').html();
                divContent += '<li class="product-pack-item media-product-pack" data-product-name="' + selectedProduct.name + '" data-product-qty="' + selectedProduct.qty + '" data-product-id="' + selectedProduct.id + '" data-product-id-attribute="' + selectedProduct.id_product_attribute + '">';
                divContent += '<img class="media-product-pack-img" src="' + selectedProduct.image +'"/>';
                divContent += '<span class="media-product-pack-title">' + selectedProduct.name + '</span>';
                divContent += '<span class="media-product-pack-ref">REF: ' + selectedProduct.ref + '</span>';
                divContent += '<span class="media-product-pack-quantity"><span class="text-muted">x</span> ' + selectedProduct.qty + '</span>';
                divContent += '<button type="button" class="btn btn-default delPackItem media-product-pack-action" data-delete="' + selectedProduct.id + '" data-delete-attr="' + selectedProduct.id_product_attribute + '"><i class="icon-trash"></i></button>';
                divContent += '</li>';

                // QTYxID-QTYxID
                // @todo : it should be better to create input for each items and each qty
                // instead of only one separated by x, - and ¤
                var line = selectedProduct.qty + 'x' + selectedProduct.id + 'x' + selectedProduct.id_product_attribute;
                var lineDisplay = selectedProduct.qty + 'x ' + selectedProduct.name;

                $('#divPackItems').html(divContent);
                $('#inputPackItems').val($('#inputPackItems').val() + line  + '-');
                $('#namePackItems').val($('#namePackItems').val() + lineDisplay + '¤');

                $('.delPackItem').on('click', function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    delPackItem($(this).data('delete'), $(this).data('delete-attr'));
                })
                selectedProduct = null;
                $('#curPackItemName').select2("val", "");
                $('.pack-empty-warning').hide();
            } else {
                error_modal(error_heading_msg, msg_select_one);
                return false;
            }
        }

        function delPackItem(id, id_attribute) {

            var reg = new RegExp('-', 'g');
            var regx = new RegExp('x', 'g');

            var input = $('#inputPackItems');
            var name = $('#namePackItems');

            var inputCut = input.val().split(reg);
            var nameCut = name.val().split(new RegExp('¤', 'g'));

            input.val(null);
            name.val(null);
            for (var i = 0; i < inputCut.length; ++i)
                if (inputCut[i]) {
                    var inputQty = inputCut[i].split(regx);
                    if (inputQty[1] != id || inputQty[2] != id_attribute) {
                        input.val( input.val() + inputCut[i] + '-' );
                        name.val( name.val() + nameCut[i] + '¤');
                    }
                }

            var elem = $('.product-pack-item[data-product-id="' + id + '"][data-product-id-attribute="' + id_attribute + '"]');
            elem.remove();

            if ($('.product-pack-item').length === 0){
                $('.pack-empty-warning').show();
            }
        }

        function getSelectedIds()
        {
            var reg = new RegExp('-', 'g');
            var regx = new RegExp('x', 'g');

            var input = $('#inputPackItems');

            if (input.val() === undefined)
                return '';

            var inputCut = input.val().split(reg);

            var ints = new Array();

            for (var i = 0; i < inputCut.length; ++i)
            {
                var in_ints = new Array();
                if (inputCut[i]) {
                    var inputQty = inputCut[i].split(regx);
                    in_ints[0] = inputQty[1];
                    in_ints[1] = inputQty[2];
                }
                ints[i] = in_ints;
            }

            return ints;
        }
    };

    this.onReady = function(){
        self.bindPackEvents();
    }
};

$(document).ready(function () {

});