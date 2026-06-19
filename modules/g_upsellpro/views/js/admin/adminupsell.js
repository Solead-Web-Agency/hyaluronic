/**
 * This is main js file. Don't edit the file if you want to update module in future.
 * 
 * @author    Globo Software Solution JSC <contact@globosoftware.net>
 * @copyright 2020 GreenWeb Team
 * @link	     http://www.globosoftware.net
 * @license   please read license in file license.txt
 */

$(document).ready(function() {
    if ($('.gupdell-table-listsetting').length > 0)
        Upsellpro.listupsell('.gupdell-table-listsetting', 0, 20);
    if ($('.gupdell-table-listsetting-analytic').length > 0)
        Upsellpro.listupsellAnalytic('.gupdell-table-listsetting-analytic', 0, 20);

    if ($('.AdminGupsellanalytics').length > 0) {
        Upsellpro.anytic();
    }
    window.onload = function() {
        if ($('.mColorPickerTrigger').length > 0) {
            $('.mColorPickerTrigger').each(function() {
                $(this).find('img').attr('src', '../img/admin/color.png');
            });
        }
        if ($('.box_setting_preview').length > 0) {
            $('.box_setting_preview').each(function() {
                var input_name = $(this).data('item-show');
                if ($('input[name="' + input_name + '"]:checked').val() == '1') {
                    $(this).trigger('click');
                    return false;
                }
            });
        }
    };
    $(document).on('change', "#gupsell_static_products .mostpopular_label input:checkbox", function() {
        var $box = $(this);
        if ($box.is(":checked")) {
            var group = "input:checkbox[name='" + $box.attr("name") + "']";
            $(group).prop("checked", false);
            $box.prop("checked", true);
        } else {
            $box.prop("checked", false);
        }
        if ($('.box_setting_preview.active').length > 0) {
            $('.box_setting_preview.active').trigger('click');
        }
    });
    $(document).on('change', "input.volumes_extra_mostpopular:checkbox", function() {
        var $box = $(this);
        if ($box.is(":checked")) {
            var group = "input:checkbox[class='" + $box.attr("class") + "']";
            $(group).prop("checked", false);
            $box.prop("checked", true);
        } else {
            $box.prop("checked", false);
        }
        if ($('.box_setting_preview.active').length > 0) {
            $('.box_setting_preview.active').trigger('click');
        }
    });
    $(document).on('click', ".remove_volume_row", function() {
        $(this).closest('tr').remove();
    });
    if ($('.gupsell-analytics-box .dashboard-analytics-box-content-box').length > 0) {
        $('.gupsell-analytics-box .dashboard-analytics-box-content-box').each(function() {
            $(this).addClass('active');
            return false;
        });
    }
    $("input[name='GSELL_LIMIT_VIEWS']").change(function() {
        if ($(this).filter(':checked').val() == '1') {
            $('.view-setting-number').addClass('active');
        } else {
            $('.view-setting-number').removeClass('active');
        }
    });
    $('.gupsell-analytics-box .dashboard-analytics-box-content-box').click(function() {
        $('.gupsell-analytics-box .dashboard-analytics-box-content-box').removeClass('active');
        Upsellpro.changeanalytic($(this).data('item'));
        $(this).addClass('active');
    });
    $(document).on('change', '.discounttype_active_val', function() {
        if ($(this).val() == '0') {
            $(this).closest('tr').find('.discounttype_active').addClass('active');
        } else {
            $(this).closest('tr').find('.discounttype_active').removeClass('active');
        }
    });
    $(document).on('click', '.addnew_upsell_row', function() {
        var show_in = $(this).closest('.form-group-showextra-volume').data('showpage');
        Upsellpro.ajaxaddVolume(show_in, 'addnew');
    });
    /*search product*/

    $(document).on('keyup', 'input#gupsell_search_product, input#gupsell_productforsearch',Upsellpro.Delay(function (e) {
        var id_check = $(this).attr('id');
        var extratype = '';
        var search_product = '#gupsell_search_product';
        if (id_check == 'gupsell_productforsearch') {
            extratype = 'displayfor';
            search_product = '#gupsell_productforsearch';
        }
        $('.itempage_' + extratype + 'product_search').val(0);
        Upsellpro.searchProductItems(search_product, extratype, 0);
    }, 1500));
    $(document).on('keyup', 'input#gupsell_collectionsearch', Upsellpro.Delay(function (e) {
        $('.itempage_collection_search').val(0);
        Upsellpro.searchCollectionItems('#gupsell_collectionsearch', '', 0);
    }, 1500));
    $(document).on('click', '.gupsell-close-box', function() {
        var gupsellremoveid = $(this).attr("data-id");
        var gupsellremovetype = $(this).attr("data-type");
        $('.gupsell_atributerow' + gupsellremovetype + '_' + gupsellremoveid).removeClass('active-contents');
        $('.gupsell_atributerow' + gupsellremovetype + '_' + gupsellremoveid).addClass('gnone');
    });
    $(document).on('click', '.gupsell-cancel-fancybox', function() {
        $.fancybox.close();
        if (typeof tinymce != "undefined") {
            tinymce.remove();
            setTimeout(function() { tinySetup({ editor_selector: 'gupsellautoload_rte' }); }, 500);
        }
    });
    $("input[name='gupsell_applydiscount']").change(function() {
        $('.gupsell-applydiscount').toggleClass('active');
    });
    $(".gupsell_item_wp").click(function() {
        $('.gupsell_item_wp').removeClass('active');
        $(this).addClass('active');
        if ($(this).data('showtoltip') == 'volume') {
            alert($('.title_volume_discount_setting').val());
            $('.volume_select_show').addClass('active');
            Upsellpro.ajaxaddVolume($(this).data('show-page'), 'showall');
        } else {
            $('.volume_select_show').removeClass('active');
            $(".form-group-showextra-volume table tbody tr").remove();
        }
    });
    $(".gupsell_item_template").click(function() {
        $('.gupsell_item_template').removeClass('active');
        if ($(this).data('showtoltip') == 'floating') {
            $(".gupsellposition_collecttion_extra").addClass('active');
        } else
            $(".gupsellposition_collecttion_extra").removeClass('active');
        $(this).addClass('active');
    });
    $(".icon-refresh-extra").click(function() {
        $('.box_setting_preview.active').trigger('click');
    });
    $("input[name='gupsell_display']").change(function() {
        if ($(this).val() == 'all_product') {
            $('#specific_product_display').removeClass('active');
            $('#collections_product_display').removeClass('active');
            $('#collections_producttable_display').removeClass('active');
        } else if ($(this).val() == 'specific_product') {
            $('#specific_product_display').addClass('active');
            $('#collections_product_display').removeClass('active');
            $('#collections_producttable_display').removeClass('active');
        } else {
            $('#specific_product_display').removeClass('active');
            $('#collections_product_display').addClass('active');
            $('#collections_producttable_display').addClass('active');
        }
    });
    $("input[name='gupsell_specified_range']").change(function() {
        $('.gupsell-specified-range').toggleClass('active');
    });
    $("input[name='gupsell_applydiscounttype']").change(function() {
        if ($(this).val() == "0") {
            $(".gupsell-applydiscount-amount").addClass('active');
        } else {
            $(".gupsell-applydiscount-amount").removeClass('active');
        }
    });
    $("input[name='gupsell_upsellremoved']").change(function() {
        if ($(this).filter(':checked').val() == '1') {
            $("input[name='gupsell_productremoved_upsell']").prop('checked', false);
            $("input[name='gupsell_productremoved_upsell']").prop('disabled', true);
        } else {
            $("input[name='gupsell_productremoved_upsell']").prop('disabled', false);
        }
    });
    $('button[name="gupsellSubmitSave"]').on('click', function() {
        if ($('input.settings-val').length > 0) {
            $('input.settings-val').each(function() {
                if ($(this).val().trim() == '' || $(this).val().trim() == '0') {
                    $(this).addClass('form-error');
                    $(this).closest('div.form-group').find('p').addClass('active');
                } else {
                    $(this).removeClass('form-error');
                    $(this).closest('div.form-group').find('p').removeClass('active');
                }
            });
        }
        if ($('#gupsell-product-ids').val() == '') {
            $('.gupsell-checkrequired-input').find('p').addClass('active');
            $('.gupsell-checkrequired-input').find('input').addClass('form-error');
        } else {
            $('.gupsell-checkrequired-input').find('p').removeClass('active');
            $('.gupsell-checkrequired-input').find('input').removeClass('form-error');
        }
        if ($("input[name='gupsell_display']").filter(':checked').val() == 'specific_product') {
            if ($('#gupsell-displayforproduct-ids').val() == '') {
                $('.gupsell-checkrequired-input-display').find('p').addClass('active');
                $('.gupsell-checkrequired-input-display').find('input').addClass('form-error');
            } else {
                $('.gupsell-checkrequired-input-display').find('p').removeClass('active');
                $('.gupsell-checkrequired-input-display').find('input').removeClass('form-error');
            }
        } else if ($("input[name='gupsell_display']").filter(':checked').val() == 'collections_product') {
            if ($('#gupsell-collections-ids').val() == '' || $('#gupsell-collections-ids').val() == null) {
                $('.gupsell-checkrequired-chosen-display').find('p').addClass('active');
                $('.gupsell-checkrequired-chosen-display').find('.chosen-choices').addClass('form-error');
            } else {
                $('.gupsell-checkrequired-chosen-display').find('p').removeClass('active');
                $('.gupsell-checkrequired-chosen-display').find('.chosen-choices').removeClass('form-error');
            }
        }
        var form = $('form.AdminGupselloffers')[0];
        if (window.FormData !== undefined) {
            var name_submit = $(this).data("save");
            var formData = new FormData(form);
            formData.append('SubmitSave', '1');
            formData.append('Submittype', name_submit);
            $.ajax({
                type: "POST",
                url: currentIndex + "&token=" + token,
                crossDomain: true,
                data: formData,
                mimeType: "multipart/form-data",
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(datas, nextstep, jqXHR) {
                    if (!datas.error) {
                        window.location.href = datas.url;
                    } else {
                        showErrorMessage(datas.warrning);
                    }
                },
                error: function(jqXHR, nextstep, errorThrown) {
                    showErrorMessage(jqXHR);
                    return false;
                }
            });
        };
    });
    $('.gupsellpopup_customproduct').fancybox({
        'closeBtn': false,
        'beforeLoad': function() {
            $('#gupsellpopup_customproduct').addClass('pendding bootstrap');
        },
        afterLoad: function() {
            if (typeof tinymce != "undefined") {
                tinymce.remove();
                setTimeout(function() { tinySetup({ editor_selector: 'gupsellautoload_rte' }); }, 500);
            }
        },
        'width': '700px',
        'padding': 0,
        'wrapCSS': 'fancybox_gupsell_style',
        helpers: {
            overlay: { closeClick: false, locked: false }
        }
    });

    if ($('.gupsellautoload_rte').length > 0) {
        tinySetup({
            editor_selector: "gupsellautoload_rte",
        });
    }

    $('.upsell-tabs-list-page-buttoncheck').click(function() {
        if (!$(this).hasClass('active')) {
            $('.upsell-tabs-list-page-buttoncheck').removeClass('active');
            $(this).addClass('active');
            Upsellpro.antTabs();
        }
    });
    $('.gupsell-clickimg .icon-default-content').click(function() {
        $(this).closest('.gupsell-clickimg').find('input[type="file"]').click();
    });

    $('#submitAddproductUpsell').click(function() {
        Upsellpro.submitAddproductUpsell();
    });
    $('#gupsellSubmitDelete').click(function() {
        Upsellpro.ConfirmDelete(this, 'ok');
    });
    $("#image_file").change(function() {
        Upsellpro.readURL(this, '.gupsell-clickimg .icon-default-custom span img');
    });
    /*pagination*/
    $('.pagination-items-page').on('click', function(e) {
        $('span.page_show_items_active').text($(this).data("items"));
        $('span.page_show_items').text($(this).data("items"));
        $('#' + $(this).data("list-id") + '-pagination-items-page').val($(this).data("items"));
        if ($('.pagination-active ul.pagination li').length > 0) {
            $('.pagination-active ul.pagination li').each(function() {
                if ($(this).find('a').attr('data-page') == '1') {
                    $(this).addClass('active');
                    return false;
                }
            });
        }
        Upsellpro.listupsell('.gupdell-table-listsetting', 0, 20);
    });
    $('.Analytic-pagination-items-page').on('click', function(e) {
        $('span.page_show_items_active').text($(this).data("items"));
        $('span.page_show_items').text($(this).data("items"));
        $('#' + $(this).data("list-id") + '-pagination-items-page').val($(this).data("items"));
        if ($('.pagination-active ul.pagination li').length > 0) {
            $('.pagination-active ul.pagination li').each(function() {
                if ($(this).find('a').attr('data-page') == '1') {
                    $(this).addClass('active');
                    return false;
                }
            });
        }
        Upsellpro.listupsellAnalytic('.gupdell-table-listsetting-analytic', 0, 20);
    });

    $(document).on('click', '.pagination-active ul.pagination li', function() {
        if (!$(this).hasClass('active') && !$(this).hasClass('disabled')) {
            $('.pagination-active ul.pagination li').removeClass('active');
            $(this).addClass('active');
            if ($('.gupdell-table-listsetting').length > 0)
                Upsellpro.listupsell('.gupdell-table-listsetting', 0, 20);
            if ($('.gupdell-table-listsetting-analytic').length > 0)
                Upsellpro.listupsellAnalytic('.gupdell-table-listsetting-analytic', 0, 20);
        }
        return false;
    });

    $(document).on('click', '.gupsell-dropdown .gupsell-dropdown-item', function() {
        $('.upsell-tabs-list-page-live-action button[data-show="' + $(this).attr('id') + '"]').trigger('click');
        $(".gupsell-dropdown").hide();
        setTimeout(function() {
            $(".gupsell-dropdown").css('cssText', '');
        }, 500);
    });
    $('.Analytic-time-show').on('click', function(e) {
        $('.Analytic-time-show').removeClass('active');
        $(this).addClass('active');
        $('.Analytic-time-show-text span').text($(this).data('items'));

        $.ajax({
            type: "POST",
            url: currentIndex + "&token=" + token,
            data: "&updateTime=1&time=" + $(this).data('items'),
            dataType: 'json',
            async: true,
            success: function(datas) {
                if (!datas.error) {
                    action123 = $('.dashboard-analytics-box-content-box.active').data('item');
                    Upsellpro.changeanalytic(action123);
                } else {
                    showErrorMessage(datas.warrning);
                }
            },
            error: function(datas) {
                alert & ("Error occurred!");
            }
        });
    });

    $(document).on('click', '.copy_link', function() {
        Upsellpro.copyToClipboard($(this));
        return false;
    });
    $(window).scroll(function(event) {
        if ($('.gupsell-formrelacted .panel').length > 0) {
            var height = $(this).scrollTop();
            var width = $(window).width();
            var top = height / 2;
            if (width > 981)
                if (top >= 0 || top < 100) {
                    $('.gupsell-formrelacted .panel').css('top', top);
                } else {
                    $('.gupsell-formrelacted .panel').css('top', 0);
                }
            else
                $('.gupsell-formrelacted .panel').css('top', 0);
        }
    });
    Upsellpro.antTabs();
    Upsellpro.upsellchosen('#gupsell_search_collections');
    Upsellpro.upsellchosen('#gupsell_search_collectionsdisplay');

    /*base 18-11-2020*/
    $(document).on('click', '.btn.box_setting_showin', function() {
        var show_page = $(this).data('item-show');
        var item_type = $(this).closest('li').find('input[name=' + show_page + '_type]:checked').val();
        var item_stype = $(this).closest('li').find('input[name=' + show_page + '_stype]:checked').val();
        var item_position = $(this).closest('li').find('input[name=' + show_page + '_position]').val();
        $('.btn.box_setting_showin').removeClass('active');
        $('.gupsell_item_wp').removeClass('active');
        $('.gupsell_item_template').removeClass('active');
        if (item_position != '')
            $('#gupsellposition_collecttion').val(item_position);
        else
            $('#gupsellposition_collecttion').val('bottom-right');
        $(this).addClass('active');
        if (show_page == 'showin_cart_popup') {
            $('.none_cartpopup').hide();
        } else {
            $('.none_cartpopup').show();
        }
        if ($('.setuplable_for label').length > 0) {
            $('.setuplable_for label').each(function() {
                if (item_type == $(this).data('name')) {
                    $(this).find('.gupsell_item_wp').addClass('active');
                    if (item_type == "volume") {
                        $('.volume_select_show').addClass('active');
                        Upsellpro.ajaxaddVolume(show_page, 'showall');
                    } else {
                        $('.volume_select_show').removeClass('active');
                    }
                }
                if (item_stype == $(this).data('name')) {
                    $(this).find('.gupsell_item_template').addClass('active');
                }
                if (item_stype == 'floating') {
                    $(".gupsellposition_collecttion_extra").addClass('active');
                } else
                    $(".gupsellposition_collecttion_extra").removeClass('active');
                $(this).find('.gupsell_item_wp').attr('data-show-page', show_page);
                $(this).find('.gupsell_item_template').attr('data-show-page', show_page);
                $('#gupsellposition_collecttion').attr('data-show-page', show_page);
                $('#box_setting_showinsave').attr('data-show-page', show_page);
                $(this).attr('for', show_page + '_' + $(this).data('name'));
            });
        }
        $('.box_setting_showin_box').toggleClass('active');
        $('.form-group-showextra-volume').attr('data-showpage', show_page);
    });
    $(document).on('click', '.btn.box_setting_preview', function() {
        $('.btn.box_setting_preview').removeClass('active');
        $(this).addClass('active');
        var show_page = $(this).data('item-show');
        Upsellpro.gethtmlPreview(show_page);

    });
    $(document).on('click', '.box_setting_showinclose', function() {
        $('.box_setting_showin_box').toggleClass('active');
        $('.btn.box_setting_showin').removeClass('active');
    });
    $(document).on('click', '#box_setting_showinsave', function() {
        $('#' + $(this).data('show-page') + '_' + $('.gupsell_item_wp.active').data('showtoltip')).trigger('change');
        $('#' + $(this).data('show-page') + '_' + $('.gupsell_item_template.active').data('showtoltip')).trigger('change');
        $('#' + $(this).data('show-page') + '_position').val($('#gupsellposition_collecttion').val());
        $('.box_setting_showin_box').toggleClass('active');
        $('.btn.box_setting_showin').removeClass('active');
        if ($('.gupsell_item_wp.active').data('showtoltip') == 'volume')
            Upsellpro.ajaxaddVolumeValue($(this).data('show-page'));

        if ($('.box_setting_preview.active').length > 0) {
            $('.box_setting_preview.active').trigger('click');
        }
        $(".form-group-showextra-volume table tbody").html();
    });
    if ($('.glabel-tooltip').length > 0)
        $('.glabel-tooltip').tooltip();
    $(".gupsell_item_wp").hover(function() {
        $('.totip_extra_content_arrow_inner_toltip gnone').removeClass('active');
        $('.' + $(this).data('showtoltip') + '-totip').addClass('active');
        var offset_px = $(this).offset();
        var offsetright_px = offset_px.left - $("#box_setting_showin").offset().left;
        var offsettop_px = offset_px.top - $("#box_setting_showin").offset().top + 64;
        if ($(this).data('showtoltip') == 'bundle' || $(this).data('showtoltip') == 'frequently') {
            offsetright_px = 0;
        } else {
            if ($(this).data('showtoltip') == 'normal') {
                offsetright_px = $('.checktoltipwidth1').outerWidth() - ($('.checktoltipwidth1').outerWidth() / 2);
            } else {

                offsetright_px = $('.checktoltipwidth2').outerWidth() - ($('.checktoltipwidth2').outerWidth() / 2);
            }
        }
        $('.totip_extra_upsell').css('cssText', 'right: ' + offsetright_px + 'px;top: ' + offsettop_px + 'px;transform-origin: 50% -4px;');
        $('.totip_extra_upsell').addClass('active');
    }, function() {
        $('.totip_extra_content_arrow_inner_toltip gnone').removeClass('active');
        $('.' + $(this).data('showtoltip') + '-totip').removeClass('active');
        $('.totip_extra_upsell').removeClass('active');
    });

    $(document).on('focus', 'input.gsearch_collections', function() {
        $(this).blur();
        $('.box_collections').toggleClass('active');
        $('.show_htmlcollection div').remove();
        $('.show_selectcollection div').remove();
        $('#drop_collections #gupsell_collectionsearch').val('');

        $('.itempage_collection_search').val(0);
        var number_search = $('.itempage_collection_search').val();
        Upsellpro.searchCollectionItems('#gupsell_collectionsearch', '', number_search);
    });
    $(document).on('focus', 'input.gsearch_product, input.gsearch_displayproduct', function() {
        var extratype = '';
        var search_product = '#gupsell_search_product';
        var product_collection = '.box_setting_product_collection';
        if ($(this).hasClass('gsearch_displayproduct')) {
            extratype = 'displayfor';
            search_product = '#gupsell_productforsearch';
            product_collection = '.box_setting_displayforproduct';
        }
        $(this).blur();
        $(product_collection).toggleClass('active');
        $('.show_' + extratype + 'htmlsearch div').remove();
        $('.show_' + extratype + 'selectproduct div').remove();
        if ($(this).hasClass('gsearch_displayproduct'))
            $('#drop_' + extratype + 'conten #gupsell_productforsearch').val('');
        else
            $('#drop_conten_display #gupsell_search_product').val('');

        $('.itempage_' + extratype + 'product_search').val(0);
        var number_search = $('.itempage_' + extratype + 'product_search').val();
        Upsellpro.searchProductItems(search_product, extratype, number_search);
    });

    $(document).on('click', '.gupselledit_product, .gupselledit_displayforproduct', function() {
        var extratype = '';
        var search_product = '#gupsell_search_product';
        var product_collection = '.box_setting_product_collection';
        if ($(this).hasClass('gupselledit_displayforproduct')) {
            extratype = 'displayfor';
            search_product = '#gupsell_productforsearch';
            product_collection = '.box_setting_displayforproduct';
        }
        $(product_collection).toggleClass('active');
        $('.show_' + extratype + 'htmlsearch div').remove();
        $('.show_' + extratype + 'selectproduct div').remove();
        if ($(this).hasClass('gupselledit_displayforproduct'))
            $('#drop_' + extratype + 'conten #gupsell_productforsearch').val($(this).data('id'));
        else
            $('#drop_conten_display #gupsell_search_product').val($(this).data('id'));
        $('.itempage_' + extratype + 'product_search').val(0);
        var number_search = $('.itempage_' + extratype + 'product_search').val();
        Upsellpro.searchProductItems(search_product, extratype, number_search);
    });

    $(document).on('click', '.gupselledit_collection', function() {
        $('.box_collections').toggleClass('active');
        $('.show_htmlcollection div').remove();
        $('.show_selectcollection div').remove();
        $('#drop_collections #gupsell_collectionsearch').val($(this).data('id'));
        $('.itempage_collection_search').val(0);
        var number_search = $('.itempage_collection_search').val();
        Upsellpro.searchCollectionItems('#gupsell_collectionsearch', '', number_search);
    });

    $(document).on('click', '.box_setting_displayclose, .box_setting_displayforclose', function() {
        var extratype = '';
        var product_collection = '.box_setting_product_collection';
        if ($(this).hasClass('box_setting_displayforclose')) {
            extratype = 'displayfor';
            product_collection = '.box_setting_displayforproduct';
        }
        $(product_collection).toggleClass('active');
        $('.show_' + extratype + 'htmlsearch div').remove();
        $('.show_' + extratype + 'selectproduct div').remove();
        $('#gupsell-' + extratype + 'combin-ids-new').val('');
        $('#gupsell-' + extratype + 'product-ids-new').val('');
    });

    $(document).on('click', '.box_collections_close', function() {
        $('.box_collections').toggleClass('active');
    });

    $(document).on('click', '.productbox-show-col', function() {
        $(this).closest('.productbox-show-row').find('.combination_show_search').toggleClass('active');
        if ($(this).closest('.productbox-show-row').find('.combination_show_search.active').length > 0) {
            $(this).closest('.productbox-show-row').find('.productbox-show-col a').text(hide_variants);
        } else {
            $(this).closest('.productbox-show-row').find('.productbox-show-col a').text(hide_showvariants);
        }
    });

    $(document).on('click', '.productbox-displayforshow-col', function() {
        $(this).closest('.productbox-show-row').find('.combination_displayforshow_search').toggleClass('active');
        if ($(this).closest('.productbox-show-row').find('.combination_displayforshow_search.active').length > 0) {
            $(this).closest('.productbox-show-row').find('.productbox-displayforshow-col a').text(hide_variants);
        } else {
            $(this).closest('.productbox-show-row').find('.productbox-displayforshow-col a').text(hide_showvariants);
        }
    });

    $(document).on('click', '#box_setting_showinsavedisplay, #box_displayforsetting_showinsave', function() {
        var check_id = $(this).attr('id');
        var extratype = '';
        if (check_id == 'box_displayforsetting_showinsave') {
            extratype = 'displayfor';
            $('.box_setting_displayforproduct').toggleClass('active');
        } else {
            $('.box_setting_product_collection').toggleClass('active');
        }
        data_valueproduct = $('#gupsell-' + extratype + 'product-ids-new').val();
        data_valuecombins = $('#gupsell-' + extratype + 'combin-ids-new').val();
        $('#gupsell-' + extratype + 'combin-ids').val(data_valuecombins);
        $('#gupsell-' + extratype + 'product-ids').val(data_valueproduct);
        $('#gupsell-' + extratype + 'combin-ids-new').val('');
        $('#gupsell-' + extratype + 'product-ids-new').val('');
        $('.show_' + extratype + 'htmlsearch div').remove();
        Upsellpro.showProducttable('', extratype);
        if ($('.box_setting_preview.active').length > 0) {
            $('.box_setting_preview.active').trigger('click');
        }

    });

    $(document).on('change', '.combination_show_search.active input, .combination_displayforshow_search.active input', function() {
        var extratype = '';
        if ($(this).closest('.combination_displayforshow_search').length > 0) {
            extratype = 'displayfor';
        }
        if ($(this).closest('.combination_' + extratype + 'show_search .gupsell_variant-list').find('li').length > 0) {
            var number_select = 0;
            $(this).closest('.combination_' + extratype + 'show_search .gupsell_variant-list').find('li').each(function() {
                if ($(this).find('input').is(':checked') == true) {
                    number_select = number_select + 1;
                }
            });
            if (number_select > 0) {
                $(this).closest('.productbox-show-row').find('a.ufe-' + extratype + 'variants-selected span').text(number_select);
                Upsellpro.addProductSelected('#checkbox_' + extratype + 'shows_' + $(this).closest('.productbox-show-row').data('id'), extratype);
            } else {
                $(this).closest('.productbox-show-row').find('input.productbox-show-checkbox-input').prop('checked', false);
                Upsellpro.removeProductSelected('#checkbox_' + extratype + 'shows_' + $(this).closest('.productbox-show-row').data('id'), extratype, false, '-new');
            }
        }
    });

    $(document).on('change', 'input.productbox-show-checkbox-input , input.productbox-displayforshow-checkbox-input', function() {
        var extratype = '';
        if ($(this).hasClass('productbox-displayforshow-checkbox-input')) {
            extratype = 'displayfor';
        }
        if ($(this).is(':checked') == true) {
            if ($(this).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search input').length > 0) {
                var number_select = 0;
                $(this).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search input').prop('checked', true);
                $(this).closest('.productbox-show-row').find('.ufe-' + extratype + 'variants-selected').show();
                $(this).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search .gupsell_variant-list li').each(function() {
                    if ($(this).find('input').is(':checked') == true) {
                        number_select = number_select + 1;
                    }
                });
                if (number_select > 0) {
                    $(this).closest('.productbox-show-row').find('a.ufe-' + extratype + 'variants-selected span').text(number_select);
                } else {
                    $(this).closest('.productbox-show-row').find('input.productbox-' + extratype + 'show-checkbox-input').prop('checked', false);
                }
            }
            Upsellpro.addProductSelected(this, extratype);
            Upsellpro.addspanproductselected($(this).closest('.productbox-show-row').data('id'), $(this).closest('.productbox-show-row-info').find('.productbox-show-ui-item-fill > span').text(), extratype);
        } else {
            Upsellpro.removeProductSelected(this, extratype, false, '-new');
            Upsellpro.removespanproductselected($(this).closest('.productbox-show-row').data('id'), extratype);
            if ($(this).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search input').length > 0) {
                $(this).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search input').prop('checked', false);
                $(this).closest('.productbox-show-row').find('.ufe-' + extratype + 'variants-selected').hide();
            }
        }
    });

    $('.show_htmlsearch').on('scroll', function() {
        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            var number_search = parseInt($('.itempage_product_search').val());
            Upsellpro.searchProductItems("#gupsell_search_product", "", number_search + 15, true);
        }
    });
    $('.show_displayforhtmlsearch').on('scroll', function() {
        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            var number_search = parseInt($('.itempage_displayfor_search').val());
            Upsellpro.searchProductItems("#gupsell_productforsearch", "displayfor", number_search + 15, true);
        }
    });
    $('.show_htmlcollection').on('scroll', function() {
        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            var number_search = parseInt($('.itempage_collection_search').val());
            Upsellpro.searchCollectionItems("#gupsell_collectionsearch", number_search + 15, true);
        }
    });
    $(document).on('click', '.producttag-close, .producttag-displayforclose', function() {
        var extratype = '';
        if ($(this).hasClass('producttag-displayforclose')) {
            extratype = 'displayfor';
        }
        if ($('#checkbox_' + extratype + 'shows_' + $(this).data('id')).length > 0) {
            $('#checkbox_' + extratype + 'shows_' + $(this).data('id')).trigger('click');
            Upsellpro.removespanproductselected($(this).data('id'), extratype);
        } else {
            Upsellpro.removeProductSelected($(this).data('id'), extratype, true, '-new');
        }
    });
    $(document).on('click', '.gupselldelete_product, .gupselldelete_displayforproduct', function() {
        $confirm = confirm(gupselldelete_product_text_conf);
        if ($confirm) {
            var extratype = '';
            if ($(this).hasClass('gupselldelete_displayforproduct')) {
                extratype = 'displayfor';
            }
            if ($('#checkbox_' + extratype + 'shows_' + $(this).data('id')).length > 0) {
                $('#checkbox_' + extratype + 'shows_' + $(this).data('id')).trigger('click');
                Upsellpro.removespanproductselected($(this).data('id'), extratype);
                if ($('tr.gupsell_row' + extratype + '_' + $(this).data('id')).length > 0) {
                    $('tr.gupsell_row' + extratype + '_' + $(this).data('id')).remove();
                }
                $('.box_setting_preview.active').trigger('click');
            } else {
                Upsellpro.removeProductSelected($(this).data('id'), extratype, true, '');
                if ($('tr.gupsell_row' + extratype + '_' + $(this).data('id')).length > 0) {
                    $('tr.gupsell_row' + extratype + '_' + $(this).data('id')).remove();
                }
                $('.box_setting_preview.active').trigger('click');
            }
        }
    });
    $(document).on('click', '.gupselldelete_collection', function() {
        $confirm = confirm(gupselldelete_product_text_conf);
        if ($confirm) {
            if ($('#collectionbox-show-checkbox-input' + $(this).data('id')).length > 0) {
                $('#collectionbox-show-checkbox-input' + $(this).data('id')).trigger('click');
                Upsellpro.addspancollectionselected($(this).data('id'));
                if ($('tr.gupsell_collectionrow_' + $(this).data('id')).length > 0) {
                    $('tr.gupsell_collectionrow_' + $(this).data('id')).remove();
                }
            } else {
                Upsellpro.removecollectionselect($(this).data('id'), true, '');
                if ($('tr.gupsell_collectionrow_' + $(this).data('id')).length > 0) {
                    $('tr.gupsell_collectionrow_' + $(this).data('id')).remove();
                }
            }
        }
    });

    $(document).on('click', '.collectionctag-lose', function() {
        if ($('#checkbox_shows_' + $(this).data('id')).length > 0) {
            $('#checkbox_shows_' + $(this).data('id')).trigger('click');
            Upsellpro.removespancollectionselected($(this).data('id'));
        } else {
            Upsellpro.removecollectionselect($(this).data('id'), true, '-new');
        }
    });
    $(document).on('change', 'input.collectionbox-show-checkbox-input', function() {
        if ($(this).is(':checked') == true) {
            Upsellpro.addcollectionselect(this);
            Upsellpro.addspancollectionselected($(this).closest('.collection-show-row').data('id'), $(this).closest('.collectionbox-show-row-info').find('.collectionbox-show-ui-item-fill > span').text());
        } else {
            Upsellpro.removespancollectionselected($(this).closest('.collection-show-row').data('id'));
            Upsellpro.removecollectionselect($(this).closest('.collection-show-row').data('id'), true, '-new');
        }
    });
    $(document).on('click', '#box_collection_showinsave', function() {
        data_valueproduct = $('#gupsell-collections-ids-new').val();
        $('#gupsell-collections-ids').val(data_valueproduct);
        $('#gupsell-collections-ids-new').val('');
        $('.show_htmlcollection div').remove();
        Upsellpro.showCollectiontable();
        $('.box_collections').toggleClass('active');

    });
    $(document).on(' mouseleave', '.boxshow_checkshow', function() {
        var id_box = $(this).data('href');
        $('' + id_box + '').hide();
    });
    $(document).on('mouseenter', '.boxshow_checkshow', function() {
        var id_box = $(this).data('href');
        $('' + id_box + '').show('500');
    });
    $(document).on('change', 'input[name="gupsell_customqty"], input[name="gupsell_upsellremoved"], input[name="gupsell_productremoved_upsell"], input[name="gupsell_applydiscount"], input[name="gupsell_applydiscounttype"], input[name="amount_discount"]', function() {
        if ($('.box_setting_preview.active').length > 0) {
            $('.box_setting_preview.active').trigger('click');
        }
    });

});

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode == 59 || charCode == 46)
        return true;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}

function hideOtherCurreny(id) {
    $('.currencie-field').hide();
    $('.curen-' + id).show();
}
var Upsellpro = {
    /*search collection */
    searchCollectionItems: function(el, search, none_remove) {
        if (!none_remove)
            $('.show_htmlcollection div').remove();
        else $('.show_htmlcollection div.gupsell_overlay').remove();
        var url = currentIndex + "&token=" + token + '&searchCollection';
        $('.show_htmlcollection').append('<div class="gupsell_overlay"><div class="container"><div class="content"><div class="circle"></div></div></div></div>');
        $.ajax({
            type: "POST",
            url: url,
            data: "&q=" + $(el).val() + '&search_number=' + search,
            dataType: 'json',
            async: true,
            success: function(datas) {
                if (datas) {
                    const cat_shows = datas;
                    if (cat_shows.length > 0) {
                        $('.show_htmlcollection div.gupsell_overlay').remove();
                        /**for item*/

                        data_valuecollection = $('#gupsell-collections-ids').val();
                        arr = $.unique(data_valuecollection.split(','));

                        data_valuecollection_new = $('#gupsell-collections-ids-new').val();
                        arr_new = $.unique(data_valuecollection_new.split(','));
                        for (const [key, value] of Object.entries(cat_shows)) {

                            var checked = '';
                            if (arr.includes(value.id_category) || arr_new.includes(value.id_category)) {
                                checked = 'checked=checked';
                                if ($('#collectionctag-slide-up-' + value.id_category).length <= 0)
                                    Upsellpro.addspancollectionselected(value.id_category, value.id_category + '-' + value.name);
                            }
                            var html = '';
                            html += '<div class="collection-show-row" id="' + value.id_category + '" data-id="' + value.id_category + '">';
                            html += '<div class="collectionbox-show-row-info">';
                            html += '<label class="collectionbox-show-checkbox-wrapper-checked" for="checkbox_shows_' + value.id_category + '">';
                            html += '<span class="collectionbox-show-checkbox-checked"><input type="checkbox" ' + checked + ' class="collectionbox-show-checkbox-input" value="' + value.id_category + '" id="checkbox_shows_' + value.id_category + '"><span class="collectionbox-show-checkbox-inner"></span></span>';
                            html += '</label>';
                            html += '<div class="title">';
                            html += '<div class="collectionbox-show-ui-item collectionbox-show-img">';
                            html += '<span class="collectionbox-show-pro-img" style="background-image: url(' + value.url_image + ');"></span>';
                            html += '</div>';
                            html += '<div class="collectionbox-show-ui-item collectionbox-show-row-title collectionbox-show-ui-item-fill">';
                            html += '<span>' + value.id_category + '-' + value.name + '</span>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('.show_htmlcollection').append(html);
                        }

                        if (data_valuecollection != '' && data_valuecollection_new == '') {
                            const arr_new = $.unique(data_valuecollection.split(','));
                            if (data_valuecollection_new == '') {
                                if (arr_new.length > 0) {
                                    for (const [key, value] of Object.entries(arr_new)) {
                                        if (data_valuecollection_new != '') {
                                            data_valuecollection_new = value + ',' + data_valuecollection_new;
                                            arr = $.unique(data_valuecollection_new.split(','));
                                            data_valuecollection_new = arr.join(",");
                                        } else data_valuecollection_new = value;
                                    }
                                }
                            }
                            $('#gupsell-collections-ids-new').val(data_valuecollection_new);
                        }
                    } else {
                        $('.show_htmlcollection div.gupsell_overlay').remove();
                        $('.show_htmlcollection').append('<div>' + nocollection_search + '</div>');
                    }
                    $('.itempage_collection_search').val(search);
                }
            }
        });
    },
    addcollectionselect: function(el) {
        data_valuecollection = $('#gupsell-collections-ids-new').val();
        data_valuecollection_old = $('#gupsell-collections-ids').val();
        const arr_new = $.unique(data_valuecollection_old.split(','));
        if (data_valuecollection == '') {
            if (arr_new.length > 0) {
                for (const [key, value] of Object.entries(arr_new)) {
                    if (data_valuecollection != '') {
                        data_valuecollection = value + ',' + data_valuecollection;
                        arr_old = $.unique(data_valuecollection.split(','));
                        data_valuecollection = arr_old.join(",");
                    } else data_valuecollection = value;
                }
            }
        }
        arr = $.unique(data_valuecollection.split(','));

        //console.log(el);
        if (!arr.includes($(el).val())) {
            if (data_valuecollection != '') {
                data_valuecollection = $(el).val() + ',' + data_valuecollection;
                arr = $.unique(data_valuecollection.split(','));
                data_valuecollection = arr.join(",");
            } else data_valuecollection = $(el).val();
            $('#gupsell-collections-ids-new').val(data_valuecollection);
        }
    },
    addspancollectionselected: function(id, name) {
        var modal_tags = '';
        modal_tags += '<div class="collectionctag-slide-up glabel-tooltip" data-toggle="tooltip" data-original-title="' + name + '" data-id="' + id + '" id="collectionctag-slide-up-' + id + '"><span>' + name + '</span><b class="collectionctag-lose" data-id="' + id + '" id="collectionctag-close' + id + '">x</b></div>';
        $('.show_selectcollection').append(modal_tags);

        if ($('.glabel-tooltip').length > 0)
            $('.glabel-tooltip').tooltip();
    },
    removespancollectionselected: function(id) {
        $('.show_selectcollection .tooltip.fade.top.in').remove();
        $('.show_selectcollection').find('#collectionctag-slide-up-' + id).remove();
    },
    removecollectionselect: function(el, noneid, new_class) {
        var gupsellremoveid = el;
        if (!noneid) {
            var gupsellremoveid = $(el).val();
        }
        datas = $('#gupsell-collections-ids' + new_class).val();
        newdatas = $.grep(datas.split(','), function(val) {
            return gupsellremoveid != val;
        });
        $('#gupsell-collections-ids' + new_class).val(newdatas.join());
        return false;
    },
    /*search product*/
    searchProductItems: function(el, extratype, search, none_remove) {
        if (!none_remove)
            $('.show_' + extratype + 'htmlsearch div').remove();
        else $('.show_' + extratype + 'htmlsearch div.gupsell_overlay').remove();
        var url = currentIndex + "&token=" + token + '&searchProduct' + extratype;
        $('.show_' + extratype + 'htmlsearch').append('<div class="gupsell_overlay"><div class="container"><div class="content"><div class="circle"></div></div></div></div>');
        $.ajax({
            type: "POST",
            url: url,
            data: "&q=" + $(el).val() + '&search_number=' + search,
            dataType: 'json',
            async: true,
            success: function(datas) {
                var html = '';
                if (datas) {
                    const products_shows = datas;
                    if (products_shows.length > 0) {

                        if (!none_remove) $('.show_' + extratype + 'htmlsearch div').remove();
                        else $('.show_' + extratype + 'htmlsearch div.gupsell_overlay').remove();

                        //$('.show_' + extratype + 'selectproduct div').remove();

                        data_valueproduct = $('#gupsell-' + extratype + 'product-ids').val();
                        arr = $.unique(data_valueproduct.split(','));
                        data_valueproduct_new = $('#gupsell-' + extratype + 'product-ids-new').val();
                        arr = $.unique(data_valueproduct.split(','));
                        arr_new = $.unique(data_valueproduct_new.split(','));
                        data_valuecombins = $('#gupsell-' + extratype + 'combin-ids').val();
                        if (data_valuecombins != '')
                            arrs = JSON.parse(data_valuecombins);
                        else
                            arrs = new Array();
                        data_valuecombins_new = $('#gupsell-' + extratype + 'combin-ids-new').val();
                        if (data_valuecombins_new != '')
                            arrs_new = JSON.parse(data_valuecombins_new);
                        else
                            arrs_new = new Array();
                        /**for item*/
                        for (const [key, value] of Object.entries(products_shows)) {
                            var formatitem = value.split("|");
                            var checked = '';
                            if (arr.includes(formatitem[1]) || arr_new.includes(formatitem[1])) {
                                checked = 'checked=checked';
                                if ($('#producttag-slide-up-' + formatitem[1]).length <= 0)
                                    Upsellpro.addspanproductselected(formatitem[1], formatitem[0], extratype);
                            }

                            $('.show_' + extratype + 'htmlsearch').append(Upsellpro.showproductSearch(formatitem, checked, extratype));
                            var number = 0;
                            if ((typeof arrs[formatitem[1]] !== "undefined" && arrs[formatitem[1]] != '' && arrs[formatitem[1]] != null) || (typeof arrs_new[formatitem[1]] !== "undefined" && arrs_new[formatitem[1]] != '' && arrs_new[formatitem[1]] != null)) {
                                if (typeof arrs_new[formatitem[1]] !== "undefined" && arrs_new[formatitem[1]] != '' && arrs_new[formatitem[1]] != null) {
                                    const combin_check = $.unique(arrs_new[formatitem[1]].split(','));
                                    if (combin_check.length > 0) {
                                        for (const [key, value] of Object.entries(combin_check)) {
                                            if ($('#gupsell_attribute' + extratype + '_check_' + value).length > 0) {
                                                $('#gupsell_attribute' + extratype + '_check_' + value).prop('checked', true);
                                                number = parseInt(number) + 1;
                                            }
                                        }
                                        if ($('#shows_' + extratype + 'row_' + formatitem[1]).length > 0) {
                                            $('#shows_' + extratype + 'row_' + formatitem[1]).find('.ufe-' + extratype + 'variants-selected').show();
                                            $('#shows_' + extratype + 'row_' + formatitem[1]).find('.ufe-' + extratype + 'variants-selected span').text(number);
                                        }
                                    }
                                } else {
                                    const combin_check = $.unique(arrs[formatitem[1]].split(','));
                                    if (combin_check.length > 0) {
                                        for (const [key, value] of Object.entries(combin_check)) {
                                            if ($('#gupsell_attribute' + extratype + '_check_' + value).length > 0) {
                                                $('#gupsell_attribute' + extratype + '_check_' + value).prop('checked', true);
                                                number = parseInt(number) + 1;
                                            }
                                        }
                                        if ($('#shows_' + extratype + 'row_' + formatitem[1]).length > 0) {
                                            $('#shows_' + extratype + 'row_' + formatitem[1]).find('.ufe-' + extratype + 'variants-selected').show();
                                            $('#shows_' + extratype + 'row_' + formatitem[1]).find('.ufe-' + extratype + 'variants-selected span').text(number);
                                        }
                                    }
                                }
                            }
                        }
                        if (data_valueproduct != '' && data_valueproduct_new == '') {
                            const arr_new = $.unique(data_valueproduct.split(','));
                            if (data_valueproduct_new == '') {
                                if (arr_new.length > 0) {
                                    for (const [key, value] of Object.entries(arr_new)) {
                                        if (data_valueproduct_new != '') {
                                            data_valueproduct_new = value + ',' + data_valueproduct_new;
                                            arr = $.unique(data_valueproduct_new.split(','));
                                            data_valueproduct_new = arr.join(",");
                                        } else data_valueproduct_new = value;

                                        if (arrs.length > 0 && arrs[value] != '' && arrs[value] != null && arrs[value].length > 0) {
                                            arrs_new[value] = arrs[value];
                                        }
                                    }
                                }
                            }
                            $('#gupsell-' + extratype + 'combin-ids-new').val(JSON.stringify(arrs_new));
                            $('#gupsell-' + extratype + 'product-ids-new').val(data_valueproduct_new);
                        }
                    }
                    $('.itempage_product_search').val(search);
                    $('.itempage_displayfor_search').val(search);
                } else {
                    if (!none_remove)
                        $('.show_' + extratype + 'htmlsearch div').remove();
                    else $('.show_' + extratype + 'htmlsearch div.gupsell_overlay').remove();
                    $('.show_' + extratype + 'htmlsearch').append('<div>' + noproduct_search + '</div>');
                }
            },
            error: function(datas) {
                $('.show_' + extratype + 'htmlsearch div.gupsell_overlay').remove();
            },
        });
    },
    showproductSearch: function(data, checked, extratype) {
        var GUPSELL = '';
        GUPSELL += '<div class="productbox-show-row" id="shows_' + extratype + 'row_' + data[1] + '" data-id="' + data[1] + '">';
        GUPSELL += '<div class="productbox-show-row-info">';
        GUPSELL += '<label class="productbox-show-checkbox-wrapper-checked" for="checkbox_' + extratype + 'shows_' + data[1] + '">';
        GUPSELL += '<span class="productbox-show-checkbox-checked">';
        GUPSELL += '<input type="checkbox" class="productbox-' + extratype + 'show-checkbox-input" ' + checked + ' value="' + data[1] + '" id="checkbox_' + extratype + 'shows_' + data[1] + '"><span class="productbox-' + extratype + 'show-checkbox-inner"></span>';
        GUPSELL += '</span></label>';
        GUPSELL += '<div class="title"><div class="productbox-show-ui-item productbox-show-img">';
        GUPSELL += '<span class="productbox-show-pro-img" style="background-image: url(' + data[2] + ');"></span></div>';
        GUPSELL += '<div class="productbox-show-ui-item productbox-show-row-title productbox-show-ui-item-fill"><span>' + data[1] + '-' + data[0] + '</span>';
        if (data[6] !== null && data[6] !== '' && data[6].trim().length !== 0) {
            GUPSELL += '<a class="ufe-' + extratype + 'variants-selected" style="display: none"><span>1</span> ' + variants_selected + '</a>';
        }
        GUPSELL += '</div></div>';
        if (data[6] !== null && data[6] !== '' && data[6].trim().length !== 0) {
            GUPSELL += '<div class="productbox-' + extratype + 'show-col"><a>' + hide_showvariants + '</a></div>';
        }
        GUPSELL += '<div class="price-box productbox-show-ui-item"><span id="price">' + data[3] + '</span></div>';
        GUPSELL += '</div>';
        GUPSELL += '<div class="combination_' + extratype + 'show_search">' + data[6] + '</div>';
        GUPSELL += '</div>';
        return GUPSELL;
    },
    addProductSelected: function(el, extratype) {
        data_valueproduct = $('#gupsell-' + extratype + 'product-ids-new').val();
        data_valuecombins = $('#gupsell-' + extratype + 'combin-ids-new').val();
        if (data_valuecombins != '')
            arrs = JSON.parse(data_valuecombins);
        else
            arrs = new Array();

        data_valueproduct_old = $('#gupsell-' + extratype + 'product-ids').val();
        data_valuecombins_old = $('#gupsell-' + extratype + 'combin-ids').val();

        if (data_valuecombins_old != '')
            arrs_old = JSON.parse(data_valuecombins_old);
        else
            arrs_old = new Array();
        const arr_new = $.unique(data_valueproduct_old.split(','));
        if (data_valueproduct == '') {
            if (arr_new.length > 0) {
                for (const [key, value] of Object.entries(arr_new)) {
                    if (data_valueproduct != '') {
                        data_valueproduct = value + ',' + data_valueproduct;
                        arr_old = $.unique(data_valueproduct.split(','));
                        data_valueproduct = arr_old.join(",");
                    } else data_valueproduct = value;

                    if (arrs_old.length > 0 && arrs_old[value] != '' && arrs_old[value] != null && arrs_old[value].length > 0) {
                        arrs[value] = arrs_old[value];
                    }
                }
            }
        }
        arr = $.unique(data_valueproduct.split(','));

        //console.log(el);
        if (!arr.includes($(el).val())) {
            if (data_valueproduct != '') {
                data_valueproduct = $(el).val() + ',' + data_valueproduct;
                arr = $.unique(data_valueproduct.split(','));
                data_valueproduct = arr.join(",");
            } else data_valueproduct = $(el).val();
            arrs[$(el).val()] = '';
            if ($(el).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search .gupsell_variant-list li').length > 0) {
                $(el).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search .gupsell_variant-list li').each(function() {
                    if ($(this).find('input').is(':checked') == true) {
                        var input_val_check = $(this).find('input').val();
                        if (arrs[$(el).val()].length > 0) {
                            if (!arrs[$(el).val()].includes(input_val_check)) {
                                arrs[$(el).val()] = input_val_check + ',' + arrs[$(el).val()];
                                arrs[$(el).val()] = $.unique(arrs[$(el).val()].split(','));
                                arrs[$(el).val()] = arrs[$(el).val()].join(",");
                            }
                        } else {
                            arrs[$(el).val()] = input_val_check;
                        }
                    }
                });
            }
            $('#gupsell-' + extratype + 'combin-ids-new').val(JSON.stringify(arrs));
            $('#gupsell-' + extratype + 'product-ids-new').val(data_valueproduct);
        } else {
            arrs[$(el).val()] = '';
            if ($(el).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search .gupsell_variant-list li').length > 0) {
                $(el).closest('.productbox-show-row').find('.combination_' + extratype + 'show_search .gupsell_variant-list li').each(function() {
                    if ($(this).find('input').is(':checked') == true) {
                        var input_val_check = $(this).find('input').val();
                        if (arrs[$(el).val()].length > 0) {
                            if (!arrs[$(el).val()].includes(input_val_check)) {
                                arrs[$(el).val()] = input_val_check + ',' + arrs[$(el).val()];
                                arrs[$(el).val()] = $.unique(arrs[$(el).val()].split(','));
                                arrs[$(el).val()] = arrs[$(el).val()].join(",");
                            }
                        } else {
                            arrs[$(el).val()] = input_val_check;
                        }
                    }
                });
            }

            $('#gupsell-' + extratype + 'combin-ids-new').val(JSON.stringify(arrs));
        }
    },
    removeProductSelected: function(el, type, noneid, new_class) {
        var gupsellremoveid = el;
        var gupsellremovetype = type;
        if (!noneid) {
            var gupsellremoveid = $(el).val();
            var gupsellremovetype = type;
        }
        datas = $('#gupsell-' + gupsellremovetype + 'product-ids' + new_class).val();
        newdatas = $.grep(datas.split(','), function(val) {
            return gupsellremoveid != val;
        });

        $('#gupsell-' + gupsellremovetype + 'product-ids' + new_class).val(newdatas.join());

        data_valuecombins = $('#gupsell-' + gupsellremovetype + 'combin-ids' + new_class).val();
        if (data_valuecombins != '') {
            arrs = JSON.parse(data_valuecombins);
            if (typeof arrs[gupsellremoveid] !== "undefined" && arrs[gupsellremoveid] != '' && arrs[gupsellremoveid] != null) {
                arrs[gupsellremoveid] = null;
            }
        }
        $('#gupsell-' + gupsellremovetype + 'combin-ids' + new_class).val(JSON.stringify(arrs));
        return false;
    },
    addspanproductselected: function(id, name, extratype) {
        var modal_tags = '';
        modal_tags += '<div class="producttag-slide-up glabel-tooltip" data-toggle="tooltip" data-original-title="' + name + '" data-id="' + id + '" id="producttag-slide-up-' + id + '"><span>' + name + '</span><b class="producttag-' + extratype + 'close" data-id="' + id + '" id="producttag-close' + id + '">x</b></div>';
        $('.show_' + extratype + 'selectproduct').append(modal_tags);

        if ($('.glabel-tooltip').length > 0)
            $('.glabel-tooltip').tooltip();
    },
    removespanproductselected: function(id, extratype) {
        $('.show_' + extratype + 'selectproduct .tooltip.fade.top.in').remove();
        $('.show_' + extratype + 'selectproduct').find('#producttag-slide-up-' + id).remove();
    },
    upsellchosen: function(el) {
        $(el).chosen({ search_contains: true, width: "100%" });
    },
    listupsell: function(table, start, end, page_active) {
        $(table + ' .gupsell_overlay').show();
        page_active = parseInt($('.gupdell-table-listsetting ul li.active a').attr('data-page'));
        end = parseInt($('#gupsell-pagination-items-page').val());
        $.ajax({
            type: "POST",
            url: currentIndex + "&token=" + token,
            data: "&listupsell=1&number_start=" + start + "&number_end=" + end + "&page_active=" + page_active,
            dataType: 'json',
            async: true,
            success: function(datas) {
                if (!datas.error) {
                    $(table + ' .gupsell_overlay').hide();
                    var html = "";
                    $('.gupdell-table-listsetting .page_show_items').text(datas.count);
                    Upsellpro.paginationNumber(datas.for_pages, datas.page_active, 'gupdell-table-listsetting', 'gupdell');
                    const listupsells = datas.listupsells;
                    if (listupsells.length > 0) {
                        for (const [key, value] of Object.entries(listupsells)) {
                            var upsellstyle = gupselltype1;
                            html += '<tr>';
                            html += '<td>' + value.id_g_upsellrule + '</td>';
                            html += '<td>' + value.name + '</td>';
                            html += '<td style="position: relative;" class="boxshow_checkshow"  data-href="#showbox-' + value.id_g_upsellrule + '">';
                            html += '<div class="product_display_imgs_show">';
                            html += '<div class="product_display_imgs_show_center">';
                            if (value.products_array) {
                                $.each(value.products_array, function(key1, value1) {
                                    if (key1 < 3)
                                        html += '<span class="product_display__image" style="background-image: url(' + value1.image + ');"></span>';
                                    else return;
                                });
                            }
                            html += '</div>';
                            if (value.products_array.length > 3)
                                html += '<div class="product_displaylabel"> +' + parseInt(parseInt(value.products_array.length) - 3) + ' item</div>';
                            html += '</div>';
                            html += '<div class="show_itemboxproduct" id="showbox-' + value.id_g_upsellrule + '">';
                            html += '<div class="show_itemboxproduct_content">';
                            html += '<div class="show_itemboxproduct_arrow">';
                            html += '</div>';
                            html += '<div class="show_itemboxproduct_inner">';
                            html += '<div class="show_itemboxproduct_title">' + title_selectitem + '</div>';
                            html += '<div class="show_itemboxproduct_inner_conten">';
                            html += '<div class="show_itemboxproduct_inner_conten_row">';
                            if (value.products_array) {
                                $.each(value.products_array, function(key1, value1) {
                                    html += '<div class="ufe-vertical-center show_itemboxproduct_item">';
                                    html += '<div class="show_itemboxproduct_item_title">';
                                    html += '<span class="product_display__image" style="background-image: url(' + value1.image + ');"></span>';
                                    html += '<span class="product_display__name">' + value1.name + '</span>';
                                    html += '</div>';
                                    html += '<span><a target="_blank" href="' + value1.url + '"><i class="icon-eye"></i></a></span>';
                                    html += '</div>';
                                });
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</td>';
                            html += '<td style="position: relative;" class="boxshow_checkshow"  data-href="#showboxshowin-' + value.id_g_upsellrule + '">';
                            if (value.showin_product != 0 || value.showin_cart != 0 || value.showin_cart_popup != 0 || value.showin_home != 0 || value.showin_collection != 0) {
                                html += '<div class="product_display_imgs_show">';
                                html += '<div class="product_display_imgs_show_center">';
                                if (value.extra_setting_shows) {
                                    var i_number = 0;
                                    $.each(value.extra_setting_shows, function(key2, value2) {
                                        if ((key2 == 'showin_product' && value.showin_product != 0) || (key2 == 'showin_cart' && value.showin_cart != 0) || (key2 == 'showin_cart_popup' && value.showin_cart_popup != 0) || (key2 == 'showin_home' && value.showin_home != 0) || (key2 == 'showin_collection' && value.showin_collection != 0)) {
                                            if (i_number < 3) {
                                                html += '<span class="product_display__image">' + value2.charAt(0) + '</span>';
                                            } else return;
                                            i_number++;
                                        }
                                    });
                                }
                                html += '</div>';
                                if (value.extra_setting_shows.length > 3)
                                    html += '<div class="product_displaylabel">  +' + parseInt(parseInt(value.extra_setting_shows.length) - 3) + ' item</div>';
                                html += '</div>';
                                html += '<div class="show_itemboxproduct" id="showboxshowin-' + value.id_g_upsellrule + '">';
                                html += '<div class="show_itemboxproduct_content">';
                                html += '<div class="show_itemboxproduct_arrow">';
                                html += '</div>';
                                html += '<div class="show_itemboxproduct_inner">';
                                html += '<div class="show_itemboxproduct_title">' + title_selectitem + '</div>';
                                html += '<div class="show_itemboxproduct_inner_conten">';
                                html += '<div class="show_itemboxproduct_inner_conten_row">';
                                if (value.extra_setting_shows) {
                                    $.each(value.extra_setting_shows, function(key2, value2) {
                                        if ((key2 == 'showin_product' && value.showin_product != 0) || (key2 == 'showin_cart' && value.showin_cart != 0) || (key2 == 'showin_cart_popup' && value.showin_cart_popup != 0) || (key2 == 'showin_home' && value.showin_home != 0) || (key2 == 'showin_collection' && value.showin_collection != 0)) {
                                            html += '<div class="ufe-vertical-center show_itemboxproduct_item">';
                                            html += '<div class="show_itemboxproduct_item_title">';
                                            html += '<span class="product_display__image">' + value2.charAt(0) + '</span>';
                                            html += '<span class="product_display__name">' + value2 + '</span>';
                                            html += '</div>';
                                            html += '<span> </span>';
                                            html += '</div>';
                                        }
                                    });
                                }
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                            }
                            html += '</td>';
                            html += '<td style="position: relative;" class="boxshow_checkshow"  data-href="#showboxspecific-' + value.id_g_upsellrule + '">';
                            if (value.display_product == 'all_product') {
                                html += title_allproduct;
                            } else {
                                if (value.display_product == 'specific_product') {
                                    html += '<div class="product_display_imgs_show">';
                                    html += '<div class="product_display_imgs_show_center">';
                                    if (value.productsdisplay_array) {
                                        $.each(value.productsdisplay_array, function(key2, value2) {
                                            if (key2 < 3)
                                                html += '<span class="product_display__image" style="background-image: url(' + value2.image + ');"></span>';
                                            else return;
                                        });
                                    }
                                    html += '</div>';
                                    if (value.productsdisplay_array.length > 3)
                                        html += '<div class="product_displaylabel">  +' + parseInt(parseInt(value.productsdisplay_array.length) - 3) + ' item</div>';
                                    html += '</div>';
                                    html += '<div class="show_itemboxproduct" id="showboxspecific-' + value.id_g_upsellrule + '">';
                                    html += '<div class="show_itemboxproduct_content">';
                                    html += '<div class="show_itemboxproduct_arrow">';
                                    html += '</div>';
                                    html += '<div class="show_itemboxproduct_inner">';
                                    html += '<div class="show_itemboxproduct_title">' + title_productitem + '</div>';
                                    html += '<div class="show_itemboxproduct_inner_conten">';
                                    html += '<div class="show_itemboxproduct_inner_conten_row">';
                                    if (value.productsdisplay_array) {
                                        $.each(value.productsdisplay_array, function(key3, value3) {
                                            html += '<div class="ufe-vertical-center show_itemboxproduct_item">';
                                            html += '<div class="show_itemboxproduct_item_title">';
                                            html += '<span class="product_display__image" style="background-image: url(' + value3.image + ');"></span>';
                                            html += '<span class="product_display__name">' + value3.name + '</span>';
                                            html += '</div>';
                                            html += '<span><a target="_blank" href="' + value3.url + '"><i class="icon-eye"></i></a></span>';
                                            html += '</div>';
                                        });
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                } else {
                                    html += '<div class="product_display_imgs_show">';
                                    html += '<div class="product_display_imgs_show_center">';
                                    if (value.cat_array) {
                                        $.each(value.cat_array, function(key3, value3) {
                                            if (key3 < 3)
                                                html += '<span class="product_display__image" style="background-image: url(' + value3.image + ');"></span>';
                                            else return;
                                        });
                                    }
                                    html += '</div>';
                                    if (value.cat_array.length > 3)
                                        html += '<div class="product_displaylabel">  +' + parseInt(parseInt(value.cat_array.length) - 3) + ' item</div>';
                                    html += '</div>';
                                    html += '<div class="show_itemboxproduct" id="showboxspecific-' + value.id_g_upsellrule + '">';
                                    html += '<div class="show_itemboxproduct_content">';
                                    html += '<div class="show_itemboxproduct_arrow">';
                                    html += '</div>';
                                    html += '<div class="show_itemboxproduct_inner">';
                                    html += '<div class="show_itemboxproduct_title">' + title_collectionitem + '</div>';
                                    html += '<div class="show_itemboxproduct_inner_conten">';
                                    html += '<div class="show_itemboxproduct_inner_conten_row">';
                                    if (value.cat_array) {
                                        $.each(value.cat_array, function(key3, value3) {
                                            html += '<div class="ufe-vertical-center show_itemboxproduct_item">';
                                            html += '<div class="show_itemboxproduct_item_title">';
                                            html += '<span class="product_display__image" style="background-image: url(' + value3.image + ');"></span>';
                                            html += '<span class="product_display__name">' + value3.name + '</span>';
                                            html += '</div>';
                                            html += '<span><a target="_blank" href="' + value3.url + '"><i class="icon-eye"></i></a></span>';
                                            html += '</div>';
                                        });
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                    html += '</div>';
                                }
                            }
                            html += '</td>';
                            html += '<td>' + value.dateup + '</td>';
                            html += '<td class="text-right">';
                            html += '<div class="btn-group-action">';
                            html += '<div class="btn-group pull-right">';
                            html += '<a href="' + gupsellurlpage + '&management=helperform&updateoffers=1&id_upselloffers=' + value.id_g_upsellrule + '&addnewid=' + value.fill_upsetting + '" title="Edit" class="edit btn btn-default"><i class="icon-pencil"></i> ' + gupsellstautsedit + '</a>';
                            html += '<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-caret-down"></i></button>';
                            html += '<ul class="dropdown-menu">';
                            html += '<li>';
                            html += '<a data-id="' + value.id_g_upsellrule + '" data-href="' + gupsellurlpage + '&id_upselloffers=' + value.id_g_upsellrule + '&deleteUpsell=1" onclick="Upsellpro.ConfirmDelete(this);" data-confirmname="' + gupsellstautsdeleteitemlabel + '" class="delete"><i class="icon-trash"></i> ' + gupsellstautsdelete + '';
                            html += '</a>';
                            html += '</li>';
                            html += '</ul>';
                            html += '</div>';
                            html += '</div>';
                            html += '</td>';
                            html += '</tr>';
                        }
                        $(table + ' tbody').html(html);
                    } else {

                        html += '<td class="list-empty" colspan="7">';
                        html += '<div class="list-empty-msg">';
                        html += '<i class="icon-warning-sign list-empty-icon"></i>';
                        html += '</div>';
                        html += '</td>';
                        $(table + ' tbody').html(html);
                    }
                } else {

                    html += '<td class="list-empty" colspan="7">';
                    html += '<div class="list-empty-msg">';
                    html += '<i class="icon-warning-sign list-empty-icon"></i>';
                    html += '</div>';
                    html += '</td>';
                    $(table + ' tbody').html(html);
                }
            },
            error: function(datas) {
                alert & ("Error occurred!");
            }
        });
    },
    paginationNumber: function(number, numberactive, tableclass, oldclass) {
        var page_old1 = numberactive - 1;
        var page_old2 = numberactive + 1;
        var old_disabled = 'disabled';
        var next_disabled = 'disabled';
        var number_old = numberactive - 3;
        var number_new = numberactive + 3;
        if (numberactive > 1) old_disabled = '';
        if (number > 3) next_disabled = '';
        var html = '<ul class="pagination pull-right">';
        html += '<li class="' + old_disabled + '"><a href="javascript:void(0);" class="pagination-link" data-page="1" data-list-id="gupsell"><i class="icon-double-angle-left"></i></a></li>';
        html += '<li class="' + old_disabled + '"><a href="javascript:void(0);" class="pagination-link" data-page="' + page_old1 + '" data-list-id="gupsell"><i class="icon-double-angle-left"></i></a></li>';

        if (number_old >= 2)
            html += "<li class='disabled'><a href='javascript:void(0);'>…</a></li>";

        for (i = 1; i <= number; i++) {
            if (number_old > i || number_new < i) {
                continue;
            }
            var active = "";
            if (i == numberactive)
                active = "active";

            html += '<li class="' + active + '"><a href="javascript:void(0);" class="pagination-link" data-page="' + i + '" data-list-id="' + oldclass + '">' + i + '</a></li>';

        }
        if (number_new < number)
            html += "<li class='disabled'><a href='javascript:void(0);'>…</a></li>";

        html += '<li class="' + next_disabled + '"><a href="javascript:void(0);" class="pagination-link" data-page="' + page_old2 + '" data-list-id="' + oldclass + '"><i class="icon-double-angle-right"></i></a></li>';
        html += '<li class="' + next_disabled + '"><a href="javascript:void(0);" class="pagination-link" data-page="' + number + '" data-list-id="' + oldclass + '"><i class="icon-double-angle-right"></i></a></li>';
        html += '</ul>'
        $(tableclass + ' .pagination-active').html(html);
    },
    showProducttable: function(el, extratype) {
        $('#gupsell_static_products' + extratype + ' tbody tr').remove();
        var data_valueproduct_old = $('#gupsell-' + extratype + 'product-ids').val();
        var data_valuecombins_old = $('#gupsell-' + extratype + 'combin-ids').val();
        if (data_valuecombins_old != '')
            arrs_old = JSON.parse(data_valuecombins_old);
        else
            arrs_old = new Array();
        const arr_new = $.unique(data_valueproduct_old.split(','));
        if (data_valueproduct_old != '') {
            if (arr_new.length > 0) {
                for (const [key, value] of Object.entries(arr_new)) {
                    if (value != '' && value != "undefined") {

                        if (typeof arrs_old[value] !== "undefined" && arrs_old[value] != '' && arrs_old[value] != null) {
                            id_combins = '';
                        } else {
                            id_combins = arrs_old[value];
                        }
                        Upsellpro.adProductGifHTML(value, id_combins, extratype);
                    }
                }
            }
        }
    },
    showCollectiontable: function(el, extratype) {
        $('#gupsell_static_collections tbody tr').remove();
        var data_valueproduct_old = $('#gupsell-collections-ids').val();
        const arr_new = $.unique(data_valueproduct_old.split(','));
        if (data_valueproduct_old != '') {
            if (arr_new.length > 0) {
                for (const [key, value] of Object.entries(arr_new)) {
                    if (value != '' && value != "undefined") {
                        Upsellpro.adCollectionGifHTML(value);
                    }
                }
            }
        }
    },
    adCollectionGifHTML: function(id_cat) {
        $.ajax({
            type: "POST",
            dataType: "html",
            url: currentIndex + "&token=" + token + "&adCollectionGifHTML=1&id_cat=" + id_cat,
            data: '',
            success: function(html) {
                if (html) {
                    $('#gupsell_static_collections tbody').prepend(html);
                }
            }
        });
        return false;
    },
    ConfirmDelete: function(el, updatelist) {
        if (confirm($(el).attr("data-Confirmname"))) {
            var id_upselloffers = $(el).data("id");
            $.ajax({
                type: "POST",
                url: currentIndex + "&token=" + token,
                data: "&deleteUpsell=1&id_upselloffers=" + id_upselloffers,
                dataType: 'json',
                async: true,
                success: function(datas) {
                    if (!datas.error) {
                        Upsellpro.listupsell('.gupdell-table-listsetting', 0, 20);
                        showSuccessMessage(datas.warrning);
                    } else {
                        showErrorMessage(datas.warrning);
                    }
                },
                error: function(datas) {
                    alert & ("Error occurred!");
                }
            });
        }
        return false;
    },
    submitAddproductUpsell: function() {
        if ($('#gupsellpopup_customproduct input.settings-val').length > 0) {
            $('#gupsellpopup_customproduct input.settings-val').each(function() {
                if ($(this).val().trim() == '' || $(this).val().trim() == '0') {
                    $(this).addClass('form-error');
                    $(this).closest('div.form-group').find('p').addClass('active');
                } else {
                    $(this).removeClass('form-error');
                    $(this).closest('div.form-group').find('p').removeClass('active');
                }
            });
        }
        $('#submitAddproductUpsell i').css('cssText', 'display:inline-block;');
        if (typeof tinymce != "undefined") {
            tinyMCE.triggerSave();
        }
        var form = $('form.AdminGupselloffers')[0];
        if (window.FormData !== undefined) {
            var formData = new FormData(form);
            formData.append('submitAddproductUpsell', '1');
            formData.append('image_product', $('input[type=file]')[0].files[0]);
            if ($('#gupsellpopup_customproduct .formgupsellpopup_customproduct').length > 0) {
                $('#gupsellpopup_customproduct .formgupsellpopup_customproduct').each(function() {
                    formData.append($(this).attr('name'), $(this).val());
                });
            }
            $.ajax({
                type: "POST",
                url: currentIndex + "&token=" + token,
                crossDomain: true,
                data: formData,
                mimeType: "multipart/form-data",
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(datas, nextstep, jqXHR) {
                    if (!datas.error) {
                        $('#submitAddproductUpsell i').css('cssText', 'display:none;');
                        $('.gupsell-cancel-fancybox').click();
                        showSuccessMessage(datas.warrning);
                        data_valueproduct = $('#gupsell-product-ids').val();
                        arr = $.unique(data_valueproduct.split(','));
                        if (!arr.includes(datas.id_product)) {
                            Upsellpro.adProductGifHTML(datas.id_product);

                            if (data_valueproduct != '') {
                                data_valueproduct = datas.id_product + ',' + data_valueproduct;
                                arr = $.unique(data_valueproduct.split(','));
                                data_valueproduct = arr.join(",");
                            } else data_valueproduct = datas.id_product;
                            $('#gupsell-product-ids').val(data_valueproduct);
                        }
                    } else {
                        showErrorMessage(datas.warrning);
                        $('#submitAddproductUpsell i').css('cssText', 'display:none;');
                    }
                },
                error: function(jqXHR, nextstep, errorThrown) {
                    $('#submitAddproductUpsell i').css('cssText', 'display:none;');
                    showErrorMessage(jqXHR);
                    return false;
                }
            });
        };
    },
    adProductGifHTML: function(id_product, id_combins, display) {
        $.ajax({
            type: "POST",
            dataType: "html",
            url: currentIndex + "&token=" + token + "&adProductGif=1&id_product=" + id_product + '&id_combins=' + id_combins + '&display=' + display,
            data: '',
            success: function(html) {
                if (html) {
                    $('#gupsell_static_products' + display + ' tbody').prepend(html);
                }
            }
        });
        return false;
    },
    readURL: function(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(previewId).attr('src', e.target.result);
                $(previewId).hide();
                $(previewId).fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(previewId).attr('src', '');
        }
    },
    copyToClipboard: function(input) {
        var $temp = $("<input>");
        $("body").append($temp);
        if (input.closest('.copy_group').find('.copy_data').hasClass('copy_link')) {
            $temp.val(input.closest('.copy_group').find('.copy_data').text()).select();
        } else {
            $temp.val(input.closest('.copy_group').find('.copy_data').val()).select();
        }

        document.execCommand("copy");
        $temp.remove();
        showSuccessMessage(copyToClipboard_success);
    },
    anytic: function() {
        if ($('#mainchart2').length > 0) {
            if (typeof Date.prototype.format == "undefined") {
                Date.prototype.format = function(format) {
                    if (format === undefined)
                        return this.toString();

                    var formatSeparator = format.match(/[.\/\-\s].*?/);
                    var formatParts = format.split(/\W+/);
                    var result = '';

                    for (var i = 0; i <= formatParts.length; i++) {
                        switch (formatParts[i]) {
                            case 'd':
                            case 'j':
                                result += this.getDate() + formatSeparator;
                                break;

                            case 'dd':
                                result += (this.getDate() < 10 ? '0' : '') + this.getDate() + formatSeparator;
                                break;

                            case 'm':
                                result += (this.getMonth() + 1) + formatSeparator;
                                break;

                            case 'mm':
                                result += (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1) + formatSeparator;
                                break;

                            case 'yy':
                            case 'y':
                                result += this.getFullYear() + formatSeparator;
                                break;

                            case 'yyyy':
                            case 'Y':
                                result += this.getFullYear() + formatSeparator;
                                break;
                        }
                    }

                    return result.slice(0, -1);
                }
            }
            var mainchart2_width2 = $('#mainchart2').width();
            var mainchart2_height2 = 550;
            if ($('.admingformdashboard').length > 0) mainchart2_height2 = 345;
            nv.addGraph(function() {
                chart2 = nv.models.lineChart()
                    .options({
                        duration: 300,
                        useInteractiveGuideline: true
                    })
                    .x(function(d) {
                        return d.key
                    })
                    .y(function(d) { return d.y })
                    .height(mainchart2_height2);
                chart2.xAxis
                    .axisLabel('')
                    .tickFormat(function(d) {
                        var date = new Date(d * 1000);
                        return date.format(gchart_date_format);
                    })
                    .staggerLabels(true);
                chart2.yAxis
                    .axisLabel('')
                    .tickFormat(function(d) {
                        if (d == null) {
                            return 'N/A';
                        }
                        return d3.format(',.2d')(d);
                    });
                d3.select('#mainchart2 svg')
                    .datum(mainchartdatas)
                    .attr('height', mainchart2_height2)
                    .attr('width', mainchart2_width2)
                    .call(chart2);
                nv.utils.windowResize(chart2.update);
                return chart2;
            });
        }
    },
    changeanalytic: function(action) {
        $.ajax({
            type: "POST",
            url: currentIndex + "&token=" + token,
            data: '&Changeanalytic=1&getaction=' + action,
            dataType: 'json',
            async: true,
            type: "POST",
            success: function(datas, nextstep, jqXHR) {
                chart2.yAxis.tickFormat(d3.format(',.2d'));

                if (action == 'sales')
                    chart2.yAxis.tickFormat(function(d) {
                        return formatCurrency(parseFloat(d), currency_format, currency_sign, currency_blank);
                    });
                if (action == 'take_rate')
                    chart2.yAxis.tickFormat(function(d) {
                        return d3.round(d * 100, 2) + ' %';
                    });
                d3.select('#mainchart2 svg')
                    .datum(datas)
                    .call(chart2);
            },
            error: function(jqXHR, nextstep, errorThrown) {
                showErrorMessage(jqXHR);
                return false;
            }
        });
    },
    listupsellAnalytic: function(table, start, end, page_active) {
        $(table + ' .gupsell_overlay').show();
        page_active = parseInt($('.gupdell-table-listsetting-analytic ul li.active a').attr('data-page'));
        end = parseInt($('#Analytic-pagination-items-page').val());
        $.ajax({
            type: "POST",
            url: currentIndex + "&token=" + token,
            data: "&listupsellAnalytic=1&number_start=" + start + "&number_end=" + end + "&page_active=" + page_active,
            dataType: 'json',
            async: true,
            success: function(datas) {
                if (!datas.error) {
                    $(table + ' .gupsell_overlay').hide();
                    var html = "";
                    $('.gupdell-table-listsetting-analytic .page_show_items').text(datas.count);
                    Upsellpro.paginationNumber(datas.for_pages, datas.page_active, '.gupdell-table-listsetting-analytic', 'Analytic');
                    const listupsellAnalytics = datas.listupsellAnalytics;
                    if (listupsellAnalytics.length > 0) {
                        for (const [key, value] of Object.entries(listupsellAnalytics)) {
                            html += '<tr class="min-height-td">';
                            html += '<td>' + value.name + '</td>';
                            html += '<td>' + value.views + '</td>';
                            html += '<td>' + value.addcarts + '</td>';
                            html += '<td>' + value.transactions + '</td>';
                            html += '<td>' + value.sales + '</td>';
                            html += '<td class="text-right">' + value.take_rate + '</td>';
                            html += '</tr>';
                        }
                        $(table + ' tbody').html(html);
                    } else {
                        html += '<td class="list-empty" colspan="6">';
                        html += '<div class="list-empty-msg">';
                        html += '<i class="icon-warning-sign list-empty-icon"></i>';
                        html += '</div>';
                        html += '</td>';
                        $(table + ' tbody').html(html);
                    }
                }
            },
            error: function(datas) {
                alert & ("Error occurred!");
            }
        });
    },
    gethtmlPreview: function(option) {
        var form = $('.AdminGupselloffers');
        var serializedForm = form.serialize();
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: currentIndex + "&token=" + token,
            data: serializedForm + "&GethtmlPreview=1&option=" + option,
            success: function(html) {
                if (html) {
                    $('.gupsell-formrelacted .panel .preview-html').empty();
                    $('.gupsell-formrelacted .panel .preview-html').append(html);
                }
            }
        });
        return false;
    },
    antTabs: function() {
        if ($('.AdminGupselloffers').length > 0) {
            var width_animated = $('.upsell-tabs-list-page-right').width();
            var check_width = 0;
            if ($('.upsell-tabs-list-page-buttoncheck').length > 0) {
                $('.upsell-tabs-list-page-buttoncheck').each(function() {
                    if (!$(this).hasClass('active')) {
                        check_width = parseFloat(check_width) + parseFloat($(this).outerWidth(true));
                    } else {
                        check_width = parseFloat(check_width) + parseFloat($(this).outerWidth(true));
                        return false;
                    }
                });
            }
            if ((width_animated - check_width) > 0) {
                $('.upsell-tabs-list-page-live-action').css('cssText', 'transform: translate(0px, 0px);');
            } else {
                var left_translate = width_animated - check_width - parseFloat($('.upsell-tabs-list-page-left').outerWidth(true));
                $('.upsell-tabs-list-page-live-action').css('cssText', 'transform: translate(' + left_translate + 'px, 0px);');
            }
        }
    },
    ajaxaddVolume: function(show_page, type) {
        var additions = $("#" + show_page + "_addition").val();
        var id = $("#id_upselloffers").val();
        var number = parseInt($(".form-group-showextra-volume table tbody tr:last-child").data('number'));
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: currentIndex + "&token=" + token,
            data: "&GethtmlAddition=1&additions=" + additions + "&id=" + id + "&showpage=" + show_page + "&number=" + number + "&type=" + type,
            success: function(html) {
                if (html) {
                    if (type == 'showall')
                        $('.form-group-showextra-volume table tbody').html(html);
                    else
                        $('.form-group-showextra-volume table tbody').append(html);
                }
            }
        });
    },
    ajaxaddVolumeValue: function(show_page) {
        var form = $('.AdminGupselloffers');
        var serializedForm = form.serialize();
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: currentIndex + "&token=" + token,
            data: serializedForm + "&jSonvalue=1&showpage=" + show_page,
            success: function(_value) {
                $("#" + show_page + "_addition").val(_value);
                if ($('.box_setting_preview.active').length > 0) {
                    $('.box_setting_preview.active').trigger('click');
                }
            }
        });
    },
    Delay: function(callback, ms) {
        var timer = 0;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
            callback.apply(context, args);
            }, ms || 0);
        };
    }
}