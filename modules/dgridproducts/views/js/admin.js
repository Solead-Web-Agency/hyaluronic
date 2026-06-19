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

$(function () {
    window.tree = new TreeCustom('.tree_custom .block_category_tree', '.tree_custom .tree_categories_header');
    window.tree.init();

    $('#beginSearch').live('click', function (e, orderby, orderway) {
        test = location.href;
        test = test.replace('&conf=2', '');
        test = test.replace('#product','');
        test = test.replace('&submitBulkenableSelectionproduct', '');
        test = test.replace('&submitBulkdisableSelectionproduct','');
        var url = addSearchParam(test);
        if (typeof orderby != 'undefined' && typeof orderway != 'undefined') {
            url = url + '&order_by=' + orderby + '&order_way=' + orderway;
        }
        location.href = url;
    });

    window.addSearchParam = function(url) {
        var categories = '';
        $('[name="categories[]"]:checked').each(function (i) {
            categories += ($(this).val());
            if($('[name="categories[]"]:checked').length > i+1)
                categories += ',';
        });
        var attributes = '';
        $('[name="attributes[]"]:checked').each(function (i) {
            attributes += ($(this).val());
            if($('[name="attributes[]"]:checked').length > i+1)
                attributes += ',';
        });
        if($('[name="categoryBox[]"]:checked').length)
        {
            $('[name="categoryBox[]"]:checked').each(function (i) {
                categories += ($(this).val());
                if($('[name="categoryBox[]"]:checked').length > i+1)
                    categories += ',';
            });
        }
        var manufacturers = '';
        $('[name="manufacturer[]"] option:selected').each(function (i) {
            manufacturers += ($(this).val());
            if($('[name="manufacturer[]"] option:selected').length > i+1)
                manufacturers += ',';
        });
        var suppliers = '';
        $('[name="supplier[]"] option:selected').each(function (i) {
            suppliers += ($(this).val());
            if($('[name="supplier[]"] option:selected').length > i+1)
                suppliers += ',';
        });
        var carriers = '';
        $('[name="carrier[]"] option:selected').each(function (i) {
            carriers += ($(this).val());
            if($('[name="carrier[]"] option:selected').length > i+1)
                carriers += ',';
        });
        if(typeof orderby !== 'undefined' && typeof orderway !== 'undefined')
            var sort = '&order_by='+orderby+'&order_way='+orderway;
        else
            var sort = '';

        var url = url.match(/^.+token[^&]+/i);
        return url
            + '&categories=' + categories
            + '&search_default_categories=' + ($('#search_default_categories').prop('checked') ? 1 : 0)
            + '&search_query=' + $('[name="search_query"]').val()
            + '&type_search=' + $('[name="type_search"]').val()
            + '&manufacturers=' + manufacturers
            + '&suppliers=' + suppliers
            + '&carriers=' + carriers
            + '&how_many_show=' + $('[name="how_many_show"]').val()
            + '&active=' + parseInt($('[name="active"]:checked').val())
            + '&with_images=' + parseInt($('[name="with_images"]:checked').val())
            + '&no_images=' + parseInt($('[name="no_images"]:checked').val())
            + '&carrier_and=' + parseInt($('[name="carrier_and"]:checked').val())
            + '&carrier_pre=' + parseInt($('[name="carrier_pre"]:checked').val())
            + '&low=' + parseInt($('[name="low"]:checked').val())
            + '&disable=' + parseInt($('[name="disable"]:checked').val())
            + '&product_name_type_search=' + $('[name="product_name_type_search"]:checked').val()
            + '&qty_from=' + ($('[name="qty_from"]').val() != '' ? parseInt($('[name="qty_from"]').val()) : '')
            + '&qty_to=' + ($('[name="qty_to"]').val() != '' ? parseInt($('[name="qty_to"]').val()) : '')
            + '&price_from=' + ($('[name="price_from"]').val() != '' ? parseFloat($('[name="price_from"]').val()) : '')
            + '&price_to=' + ($('[name="price_to"]').val() != '' ? parseFloat($('[name="price_to"]').val()) : '')
            + '&date_from=' + ($('[name="date_from"]').val() != '' ? $('[name="date_from"]').val() : '')
            + '&date_to=' + ($('[name="date_to"]').val() != '' ? $('[name="date_to"]').val() : '')
            + '&custom_feature=' + ($('[name="custom_feature"]').val() != '' ? $('[name="custom_feature"]').val() : '')
            + '&visibility=' + ($('[name="visibility"]').val() != '' ? $('[name="visibility"]').val() : '')
            + '&refilters=' + '1'
            + '&attributes=' + attributes
            + '&yes_discount=' + parseInt($('[name="yes_discount"]:checked').val())
            + '&no_discount=' + parseInt($('[name="no_discount"]:checked').val())
            + '&percent_discount=' +  ($('[name="percent_discount"]').val() != '' ? $('[name="percent_discount"]').val() : 0)
            + '&value_discount=' +  ($('[name="value_discount"]').val() != '' ? $('[name="value_discount"]').val() : 0)
            + sort;
    }

    $('#resetSearch').live('click', function () {
        location.href = location.href.match(/^[^&]+&[^&]+/i);
    });

    $('.pagination-link').off().click(function () {
        var url = location.href.replace(/&submitFilterproduct=\d+/i, '');
        url = location.href.replace(/#product/i, '');
        location.href = url + '&submitFilterproduct=' + $(this).data('page');
    });

    $(".tabs a[data-toggle='tab']").click(function(e){
        e.preventDefault();
        $(this).tab('show');
    });

    $(".panel-heading-top, .hidden_filter").click(function () {
        $(".mode_search > .row").slideToggle();
        $(".hidden_filter i").toggleClass('icon-chevron-up').toggleClass('icon-chevron-down');
    });

    $('input.search_category:first').focus({el:$('.block_category_tree.tree_root:first input.tree_input')}, function(eventObj){
        eventObj.data.el.each(function(){
            $(this).attr('data-search', $(this).data('name').toLowerCase());
        });
    });

    $('table.product .title_box a').on('click', function(){
        var $_GET = {};
        var __GET = $(this).attr('href').split("&");
        for(var i=0; i<__GET.length; i++) {
            var getVar = __GET[i].split("=");
            $_GET[getVar[0]] = typeof(getVar[1])=='undefined' ? '' : getVar[1];
        }
        var default_filter = false;
        $('[name^="productFilter_"]').each(function () {
            if ($(this).val()) {
                default_filter = true;
            }
        });

        if(!default_filter) {
            $('#beginSearch').trigger('click', [$_GET.productOrderby, $_GET.productOrderway]);
            return false;
        }
    });

    $('[name="attribute_group"]').live('change', function () {
        $('[data-attribute-values]').hide();
        $('#btn_list').trigger( "click" );
        $("#btn_list").removeClass("btn-success").addClass("open btn-danger");
    });

    $('[data-attribute-values]').find('input[type=checkbox]').on('change', function () {
        var data_attribute_values = $(this).attr("value");
        var data_attribute_group = $(this).closest('[data-attribute-values]').attr('data-attribute-values');
        var checkBoxes = $($('[data-attribute-values-view]').find('[value='+data_attribute_values+']'));
        checkBoxes.prop("checked", !checkBoxes.prop("checked"));
        $('[data-attribute-values-view='+data_attribute_group+']').find('[value='+data_attribute_values+']').parent().parent().toggle();

        var data_attribute_values_view_label = false;
        $('[data-attribute-values-view]').each(function(index) {
            $(this).prev().hide();
            $(this).find('[type=checkbox]').each(function(index) {
                if ($(this).prop('checked')) {
                    $(this).closest('[data-attribute-values-view]').prev().show();
                    data_attribute_values_view_label = true;
                }
            });
        });

        if (data_attribute_values_view_label) {
            $('.data-attribute-values-view-label').show();
            $('.data-attribute-values-view-hr').show();
        } else {
            $('.data-attribute-values-view-label').hide();
            $('.data-attribute-values-view-hr').hide();
        }

    });

    $('[data-attribute-values-view]').find('input[type=checkbox]').on('change', function () {
        var data_attribute_values = $(this).attr("value");
        var data_attribute_group = $(this).closest('[data-attribute-values-view]').attr('data-attribute-values-view');
        var checkBoxes = $('[data-attribute-values]').find('[value='+data_attribute_values+']');
        checkBoxes.prop("checked", !checkBoxes.prop("checked"));
        $('[data-attribute-values-view='+data_attribute_group+']').find('[value='+data_attribute_values+']').parent().parent().toggle();

        var data_attribute_values_view_label = false;
        $('[data-attribute-values-view]').each(function(index) {
            $(this).prev().hide();
            $(this).find('[type=checkbox]').each(function(index) {
                if ($(this).prop('checked')) {
                    $(this).closest('[data-attribute-values-view]').prev().show();
                    data_attribute_values_view_label = true;
                }
            });
        });

        if (data_attribute_values_view_label) {
            $('.data-attribute-values-view-label').show();
            $('.data-attribute-values-view-hr').show();
        } else {
            $('.data-attribute-values-view-label').hide();
            $('.data-attribute-values-view-hr').hide();
        }
    });

    $("#btn_list").click(function () {
        $(this).toggleClass("open btn-success btn-danger");
        var temp = $("#select_attribute").val();
        $('[data-attribute-values="' + temp + '"]').toggle();
    });

    // $('#btn_list').trigger( "click" );

    $("body").on('click', '.datetimepicker-val', function () {
        $(".datetimepicker").removeClass("active");
        $(this).next('.datetimepicker').addClass("active");
        $(this).next('.datetimepicker').trigger('focus');
    });

    $("body").on('click', '.datetimepicker', function () {
        $(".datetimepicker").removeClass("active");
        $(this).addClass("active");
    });

    $("body").on('click', '.ui-datepicker-close', function () {
        var datetimepicker_val = $(".datetimepicker.active").val();
        $(".datetimepicker.active").prev(".datetimepicker-val").val(datetimepicker_val);
        $(".datetimepicker").removeClass("active");
    });

    $(".jump-to-page").keyup(function() {
        var data_page = $(this).val();
        $(".jump-to-page-link").attr("data-page", data_page);
    });

    $('.jump-to-page').keyup(function(e){
        if(e.keyCode == 13)
        {
            $(".jump-to-page-link").trigger( "click" );
        }
    });


});
// $(document).on('click', '#yes_discount_on, #yes_discount_off', function (e) {
//     var check = $("#yes_discount_on").prop('checked');
//     if (check) {
//         $('#hidden_no_dicsount').hide();
//     } else {
//         $('#hidden_no_dicsount').show();
//     }
// })
// $(document).on('click', '#no_discount_on, #no_discount_off', function (e) {
//     var check = $("#no_discount_on").prop('checked');
//     if (check) {
//         $('.hidden_yes_dicsount').hide();
//     } else {
//         $('.hidden_yes_dicsount').show();
//     }
// })