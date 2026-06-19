/**
 * 2007-2018 PrestaShop
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

var tabsMEP = {
    'category': function () {
        window.tree_categories = new TreeCustom(
            '.tree_custom_categories .tree_categories',
            '.tree_custom_categories .tree_categories_header'
        );
        window.tree_categories.init();
        window.tree_categories.afterChange = function () {
            var categories = this.getListSelectedCategories();
            var category_default = $('[name=category_default]');
            category_default.html('');
            category_default.append('<option value="0">-</option>');
            $.each(categories, function (index, value) {
                category_default.append('<option value="'+value.id+'">'+value.name+'</option>');
            });
        };
        window.tree_categories.checkAssociatedCategory(2);

        $('[name=action_with_category]').live('change', function () {
            $('._action').hide();
            if (!parseInt($(this).val()))
                $('._action_add').show();
        });

        $('[name=action_with_category]:checked').trigger('change');

        $('#setCategoryAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            var categories = tree_categories.getListSelectedCategories();
            data['category'] = [];
            data['id_category_default'] = parseInt(tab.find('[name=category_default]').val());
            data['action_with_category'] = parseInt(tab.find('[name=action_with_category]:checked').val());
            data['remove_old_categories'] = (tab.find('[name=remove_old_categories]').length
            && tab.find('[name=remove_old_categories]').is(':checked') ? 1 : 0);
            $.each(categories, function (index, category) {
                data['category'].push(category.id);
            });

            setAllProducts(data, 'category');
        });
    },
    'price': function () {
        $('#setPriceAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['type_price'] = tab.find('[name="type_price"]:checked').val();
            data['action_price'] = tab.find('[name="action_price"]:checked').val();
            data['change_for'] = tab.find('[name="change_for"]:checked').val();
            data['combinations'] = $.getAllValues();
            data['price_value'] = tab.find('[name="price_value"]').val();
            data['id_tax_rules_group'] = tab.find('[name="id_tax_rules_group"]').val();
            data['not_change_final_price'] = (tab.find('[name="not_change_final_price"]').prop('checked') ? 1 : 0);
            data['unity'] = tab.find('[name="unity"]').val();
            data['unity_price'] = tab.find('[name="unity_price"]').val();
            data['type_price_r'] = tab.find('[name="type_price_r"]:checked').val();
            data['action_direction'] = tab.find('[name="action_direction"]:checked').val();
            data['action_rounding_value'] = tab.find('[name="action_rounding_value"]:checked').val();
            // data['unity_price'] = tab.find('[name="unity_price"]').val();
            setAllProducts(data, 'price');
        });

        $('[value=price]').live('change', function () {
            var checkbox = $('[value=tax_rule_group]');
            var checkbox_price_round = $('[value=price_round]');
            if ($(this).prop('checked')) {
                checkbox.attr('disabled', false);
                checkbox_price_round.attr('disabled', false);
            } else {
                checkbox.attr('disabled', true);
                checkbox_price_round.attr('disabled', true);
            }
        });

        $('[value=tax_rule_group]').live('change', function () {
            var checkbox = $('[value=price]');
            var checkbox_price_round = $('[value=price_round]');
            if ($(this).prop('checked')) {
                checkbox.attr('disabled', false);
                checkbox_price_round.attr('disabled', false);
            } else {
                checkbox.attr('disabled', true);
                checkbox_price_round.attr('disabled', true);
            }
        });

        $('[value=unity]').live('change', function () {
            var checkbox_price_round = $('[value=price_round]');
            if ($(this).prop('checked')) {
                checkbox_price_round.attr('disabled', false);
            } else {
                checkbox_price_round.attr('disabled', true);
            }
        });

        $('[value=specific_price]').live('change', function () {
            var checkbox = $('[value=delete_specific_price_all]');
            if ($(this).prop('checked')) {
                checkbox.attr('disabled', false);
            } else {
                checkbox.attr('disabled', true);
            }
        });

        $('[value=delete_specific_price_all]').live('change', function () {
            var checkbox = $('[value=specific_price]');
            if ($(this).prop('checked')) {
                checkbox.attr('disabled', false);
            } else {
                checkbox.attr('disabled', true);
            }
        });

        $('#action_thousand').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_thousand').addClass('active');
        });

        $('#action_hundred').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_hundred').addClass('active');
        });

        $('#action_ten').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_ten').addClass('active');
        });

        $('#action_one').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_one').addClass('active');
        });

        $('#action_one_tenth').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_one_tenth').addClass('active');
        });

        $('#action_one_cell').live('change', function () {
            $('.rounding-table tr').removeClass('active');
            $('.rounding-table tr.tr_one_cell').addClass('active');
        });


    },
    'active': function () {
        $('[name=available_for_order]').live('change', function () {
            if ($(this).is(':checked'))
                $('[name=show_price]').attr('checked', 'checked').attr('disabled', 'disabled');
            else
                $('[name=show_price]').removeAttr('disabled');
        });

        $('#setActiveAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['active'] = parseInt(tab.find('[name="is_active"]:checked').val());
            data['on_sale'] = (tab.find('[name="on_sale"]').is(':checked') ? 1 : 0);
            data['visibility'] = tab.find('[name="visibility"]').val();
            data['condition'] = tab.find('[name="condition"]').val();
            data['available_for_order'] = (tab.find('[name="available_for_order"]:checked').length ? 1 : 0);
            data['show_price'] = (tab.find('[name="show_price"]:checked').length ? 1 : 0);
            data['online_only'] = (tab.find('[name="online_only"]:checked').length ? 1 : 0);
            data['delete_product'] = (tab.find('[name="delete_product"]:checked').length ? 1 : 0);
            data['show_condition'] = (tab.find('[name="show_condition"]').is(':checked') ? 1 : 0);
            data['delete_type'] = (tab.find('[name="delete_type"]:checked').val() == 1 ? 1 : 0);
            data['combinations'] = $.getAllValues();
            setAllProducts(data, 'active');
        });
    },
    'manufacturer': function () {
        $('#setManufacturerAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['id_manufacturer'] = parseInt(tab.find('[name="id_manufacturer"]').val());
            setAllProducts(data, 'manufacturer');
        });
    },
    'accessories': function () {
        $('#setAccessoriesAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['accessories'] = [];
            tab.find('[name="accessories[]"] option').each(function () {
                data['accessories'].push({
                    id: parseInt($(this).attr('value'))
                });
            });
            data['remove_old'] = parseInt(tab.find('[name=remove_old]:checked').val());
            setAllProducts(data, 'accessories');
        });
    },
    'supplier': function () {
        $('#setSupplierAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['supplier'] = [];
            tab.find('[name="supplier[]"] option:selected').each(function () {
                data['supplier'].push(parseInt($(this).attr('value')));
            });
            data['id_supplier_default'] = tab.find('[name="id_supplier_default"]').val();
            data['suppliers_sr[]'] = tab.find('[name="suppliers_sr[]"]').val();
            data['supplier_reference'] = tab.find('[name="supplier_reference"]').val();
            data['product_price'] = tab.find('[name="product_price"]').val();
            data['product_price_currency'] = tab.find('[name="product_price_currency"]').val();
            data['combinations'] = $.getAllValues();
            setAllProducts(data, 'supplier');
        });
    },
    'carrier': function () {
        $('#setCarrierAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['carrier'] = [];
            tab.find('[name="carrier[]"] option:selected').each(function () {
                data['carrier'].push(parseInt($(this).attr('value')));
            });
            data['id_carrier_default'] = tab.find('[name="id_carrier_default"]').val();
            data['carriers_sr[]'] = tab.find('[name="carriers_sr[]"]').val();
            data['carrier_reference'] = tab.find('[name="carrier_reference"]').val();
            data['product_price'] = tab.find('[name="product_price"]').val();
            data['product_price_currency'] = tab.find('[name="product_price_currency"]').val();
            data['combinations'] = $.getAllValues();
            setAllProducts(data, 'carrier');
        });
    },
    'discount': function () {
        $('#setDiscountAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            $priceD = tab.find('[name="price"]').val();
            if($priceD == ''){
                $priceD = -1;
            }
            data['action_for_sp'] = tab.find('[name="action_for_sp"]:checked').val();
            data['sp_from_quantity'] = tab.find('[name="sp_from_quantity"]').val();
            data['sp_id_currency'] = tab.find('[name="sp_id_currency"]').val();
            data['sp_id_country'] = tab.find('[name="sp_id_country"]').val();
            data['sp_id_group'] = tab.find('[name="sp_id_group"]').val();
            data['sp_from'] = tab.find('[name="sp_from"]').val();
            data['sp_to'] = tab.find('[name="sp_to"]').val();
            data['sp_reduction'] = tab.find('[name="sp_reduction"]').val();
            data['sp_reduction_type'] = tab.find('[name="sp_reduction_type"]').val();
            data['change_for'] = tab.find('[name="change_for_sp"]:checked').val();
            data['price'] = $priceD;
            data['delete_old_discount'] = (tab.find('[name="delete_old_discount"]').is(':checked') ? 1 : 0);
            data['delete_old_discount_all'] = (tab.find('[name="delete_old_discount_all"]').is(':checked') ? 1 : 0);
            data['leave_base_price'] = (tab.find('[name="leave_base_price"]').is(':checked') ? 1 : 0);
            data['action_discount'] = tab.find('[name="action_discount"]:checked').val();
            data['search_id_currency'] = tab.find('[name=search_id_currency]').val();
            data['search_id_country'] = tab.find('[name=search_id_country]').val();
            data['search_id_group'] = tab.find('[name=search_id_group]').val();
            data['search_from'] = tab.find('[name=search_from]').val();
            data['search_to'] = tab.find('[name=search_to]').val();
            data['search_from_quantity'] = tab.find('[name=search_from_quantity]').val();
            data['discount_price_reduction_type'] = tab.find('[name=discount_price_reduction_type]').val();
            data['discount_price'] = tab.find('[name=discount_price]:checked').val();
            data['discount_discount'] = tab.find('[name=discount_discount]:checked').val();
            data['combinations'] = $.getAllValues();

            setAllProducts(data, 'discount');
        });

        $('#off_menu').click(function () {
            $('.edit_menu').fadeOut();
            $('.search-block').fadeOut();
            $('.edit-block').fadeIn();
            return true;
        });

        $('#off_menus').click(function () {
            $('.edit_menu').fadeOut();
            $('.search-block').fadeIn();
            $('.edit-block').fadeOut();
            return true;
        });

        $('#trigger').click(function () {
            $('.edit_menu').fadeIn();
            $('.search-block').fadeIn();
            $('.edit-block').fadeIn();
            return true;
        });

        $('.leave_base_price').live('change', function () {
            if ($(this).is(':checked')) {
                $('.specific_price_price').attr('disabled', true).val('');
                $('#discount_price_disable').trigger('click');
            } else {
                $('.specific_price_price').removeAttr('disabled').val(0);
                $('#discount_price_rewrite').trigger('click');
            }
        });

        $('[name="discount_price"]').live('change', function () {
            $('.leave_base_price').prop('checked', false);

            $('.specific_price_price').removeAttr('disabled').val(0);
            if ($('[name="discount_price"]:checked').val() == -1) {
                $('.leave_base_price').prop('checked', true);
                $('.specific_price_price').attr('disabled', true).val('');
            }

            if ($('[name="discount_price"]:checked').val() == 0 || $('[name="discount_price"]:checked').val() == 1) {
                $('[name="discount_price_reduction_type"]').fadeIn();
            } else {
                $('[name="discount_price_reduction_type"]').fadeOut();
            }
        });
    },
    'features': function () {
        $('.view_more_features').live('click', function (e) {
            e.preventDefault();
            var self = $(this);
            if (self.is('.off'))
                return false;
            var page = feature_pages.shift();
            self.addClass('off');
            $.ajax({
                url: document.location.href.replace(document.location.hash, ''),
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'load_features',
                    p: page
                },
                success: function (r) {
                    self.removeClass('off');
                    if (!r.hasError)
                    {
                        $('.list_features').append(r.features_list);
                        initLanguages();
                        $('.disable_option').trigger('change');
                        var counter = self.find('.counter');
                        counter.text(parseInt(counter.text()) - count_feature_view);
                    }
                    else
                    {
                        feature_pages.unshift(page);
                    }
                    if (!feature_pages.length)
                        self.remove();
                },
                error: function ()
                {
                    self.removeClass('off');
                    feature_pages.unshift(page);
                }
            });
        });

        $('#setFeaturesAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};

            if ($('[name="old_feature_delete"]:checked').length > 0) {
                data.delete_old = 1;
            } else {
                data.delete_old = 0;
            }

            if (typeof window.parent.frames['seosaextendedfeatures'] != 'undefined') {
                var contentWindow = window.parent.frames['seosaextendedfeatures'].contentWindow;

                var table_features = $('.table-features', contentWindow.document);
                table_features.trigger('change');
                table_features.find(':input:not(button)').each(function () {
                    var original_name = $(this).attr('name');
                    if (original_name.match(/\[custom\]/)) {
                        var values = contentWindow.angular.element($('[name="'+original_name+'"]', contentWindow.document)).scope().values;
                        var match = original_name.match(/\[[a-zA-Z]+\]$/);
                        var name = original_name.replace(match[0], '');
                        $.each(languages, function (index, l) {
                            data[name+'['+l.iso_code+']'] = (typeof values[l.id_lang] != 'undefined' ? values[l.id_lang] : '');
                        });
                    } else {
                        data[$(this).attr('name')] = $(this).val();
                    }
                });
            } else {
                $('.table-features').trigger('change');
                tab.find(':input:not(button)').each(function () {
                    data[$(this).attr('name')] = $(this).val();
                });
                tab.find('[name*="delete_form_features"]').each(function () {
                    if ($(this).prop('checked')) {
                        data[$(this).attr('name')] = 1;
                    } else {
                        data[$(this).attr('name')] = 0;
                    }
                });
                // fix enabled feature TODO
                data.enabled_feature_fix = [];
                $('[id^="enable_feature_"]').each(function () {
                    if ($(this).prop('checked') == false) {
                        data.enabled_feature_fix.push($(this).val());
                    }
                });
            }
            setAllProducts(data, 'features');
        });
    },
    'delivery': function () {
        $('#setDeliveryAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            tab.find('input[type=text], input[type=checkbox]:checked').not('button').each(function () {
                data[$(this).attr('name')] = $(this).val();
            });
            data['combinations'] = $.getAllValues();
            data['change_for'] = tab.find('[name="weight_change_for_combination"]:checked').val();
            data['del_carrier'] = tab.find('[name="on_delete"]:checked').val();
            data['additional_delivery_times'] = tab.find('[name="additional_delivery_times"]:checked').val();
            setAllProducts(data, 'delivery');
        });
    },
    'image': function () {

        $('#setImageAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            tab.addClass('loading');
            var images = new FormData();
            images.append('action', 'upload_images');
            images.append('ajax', true);
            tab.find('input[name="image[]"]').each(function (index, value) {
                images.append('image['+index+']', $(this).get(0).files[0]);
            });
            $.ajax({
                url: document.location.href,
                type: 'POST',
                processData: false,
                contentType: false,
                dataType: 'json',
                data: images,
                success: function (r)
                {
                    var data = {};
                    data['responseImages'] = r.responseImages;
                    data['combinations'] = $.getAllValues();
                    data['change_for'] = parseInt(tab.find('[name="change_for_img"]:checked').val());
                    data['delete_images'] = (tab.find('[name=delete_images]').is(':checked') ? 1 : 0);
                    tab.find('[name^=legend_]').each(function() {
                        data['legend_' + $(this).data('lang')] = $(this).val();
                    });
                    data['position'] = tab.find('[name="id_caption"]').val();
                    data['delete_captions'] = (tab.find('[name=delete_captions]').is(':checked') ? 1 : 0);

                    setAllProducts(data, 'image', function () {
                        tab.removeClass('loading');
                    });
                },
                error: function ()
                {
                    tab.removeClass('loading');
                }
            });
        });
    },
    'description': function () {
        $('#setDescriptionAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['description'] = tab.find('[name=description]').val();
            data['description_short'] = tab.find('[name=description_short]').val();
            data['language'] = tab.find('[name=language]:checked').val();
            data['product_name'] = tab.find('[name=name]').val();
            data['location_description_short'] = tab.find('[name=location_description_short]:checked').val();
            data['location_description'] = tab.find('[name=location_description]:checked').val();
            setAllProducts(data, 'description');
        });
        $('.editor_html').redactor({
            buttonSource: true,
            imageUpload: upload_image_dir,
            fileUpload: upload_file_dir,
            plugins: ['table', 'video']
        });
    },
    'rule_combination': function () {
        $('[name="attribute_group"]').live('change', function () {
            var row = $(this).closest('.row_attributes');
            row.find('[id^="attribute_group_"]').hide();
            row.find('[id="attribute_group_'+$(this).val()+'"]').show();
        }).trigger('change');

        $('.removeRowAttributes').live('click', function () {
            var selected_attributes = $('[name=selected_attributes]').val();
            selected_attributes = (selected_attributes ? selected_attributes.split('|') : []);
            var attr = $(this).closest('').data('key');
            var key = $.inArray(attr, selected_attributes)
            if (key == -1)
            {
                selected_attributes.splice(key, 1);
                $('[name=selected_attributes]').val(selected_attributes.join('|'));
                $(this).closest('.selected_attribute').remove();
            }
            else
            {
                $.alert('Not exists key!');
                setTimeout(function() {
                    $('body').find('.jconfirm').addClass('bootstrap');
                }, 1);
            }
        });

        $('.addAttribute').live('click', function (e) {
            e.preventDefault();
            var selected_attributes = $('[name=selected_attributes]').val();
            selected_attributes = (selected_attributes ? selected_attributes.split('|') : []);
            var row_attributes = $(this).closest('.row_attributes');

            var id_attribute_group = row_attributes.find('[name=attribute_group]').val();
            var id_attribute = row_attributes.find('#attribute_group_'+id_attribute_group+' [name=attributes]').val();

            var group_name = row_attributes.find('[name=attribute_group] option:selected').text();
            var attr_name = row_attributes.find('#attribute_group_'+id_attribute_group+' [name=attributes] option:selected').text();

            var attr = id_attribute_group+'_'+id_attribute;
            if ($.inArray(attr, selected_attributes) != -1 || $('[data-key^="'+id_attribute_group+'_"]').length)
            {
                $.alert(text_already_exists_attribute);
                setTimeout(function() {
                    $('body').find('.jconfirm').addClass('bootstrap');
                }, 1);
                return false;
            }
            selected_attributes.push(attr);
            $('[name=selected_attributes]').val(selected_attributes.join('|'));
            $('.selected_attributes').append('<div class="selected_attribute row fixed-width-xl">' +
                '<div class="col-xs-10">'
                +group_name
                +':'+attr_name
                + '</div><div class="col-xs-2"><a data-key="'+attr+'" class="removeRowAttributes"><i class="icon-remove"></a></div>'
                +'</div>');
        });

        $('#setRuleCombinationAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};

            var selected_attributes = $('[name=selected_attributes]').val();
            selected_attributes = (selected_attributes ? selected_attributes.split('|') : []);
            var attrs = [];
            $.each(selected_attributes, function (index, value) {
                var value_arr = value.split('_');
                attrs.push(value_arr[1]);
            });

            data['exact_match'] = (tab.find('[name=exact_match]:checked').length ? 1 : 0);
            data['combinations'] = $.getAllValues();
            data['selected_attributes'] = attrs;
            data['delete_attribute'] = $('.delete_attribute:visible [name="delete_attribute"]').val();
            data['add_attribute'] = $('.add_attribute:visible [name="add_attribute"]').val();
            data['force_delete_attribute'] = ($('[name="force_delete_attribute"]:checked').length ? 1 : 0);
            data['rc_apply_change_for'] = $('[name="rc_apply_change_for"]:checked').val();
            setAllProducts(data, 'rule_combination');
        });
    },
    'attachment': function () {
        $('[data-attachment-file] input').live('change', function () {
            $('.message_error').html('').hide();
            var self = $(this);
            var tab = $(this).closest('.tab_content');

            var filename = tab.find('[name="filename_'+id_language+'"]').val();

            if (!filename)
            {
                $.alert(text_filename_empty);
                setTimeout(function() {
                    $('body').find('.jconfirm').addClass('bootstrap');
                }, 1);
                clearInput();
                return false;
            }
            var file = self.get(0).files[0];

            var data = new FormData();
            data.append('file', file);

            $.each(languages, function (id_lang, lang) {
                data.append('filename_' + lang.id_lang, tab.find('[name="filename_'+lang.id_lang+'"]').val());
                data.append('description_' + lang.id_lang, tab.find('[name="description_'+lang.id_lang+'"]').val());
            });

            data.append('action', 'download_attachment');
            data.append('ajax', true);

            $.ajax({
                url: document.location.href.replace(document.location.hash, ''),
                data: data,
                dataType: 'json',
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    if (typeof data.error != 'undefined' && data.error.length)
                    {
                        $('.message_error').html(data.error.join('<br>')).slideDown(500);
                    }
                    else
                    {
                        tab.find('[name^="filename_"], [name^="description_"]').val("");
                        $('.select_attachments .no_selected_product')
                            .append('<option value="' + data.id_attachment + '">' + data.filename + '</option>');
                    }
                }
            });

            function clearInput()
            {
                self.replaceWith(self.clone());
            }
        });

        $('#setAttachmentAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['attachments'] = [];
            tab.find('[name="attachments[]"] option').each(function () {
                data['attachments'].push($(this).attr('value'));
            });
            data['old_attachment'] = (tab.find('[name="old_attachment"]').is(':checked') ? 1 : 0);
            setAllProducts(data, 'attachment');
        });
    },
    'advanced_stock_management': function () {
        $('#advanced_stock_management_che').live('click', function()
        {
            var val = 0;
            if ($(this).prop('checked'))
                val = 1;

            // self.ajaxCall({actionQty: 'advanced_stock_management', value: val});
            if (val == 1)
            {
                $(this).val(1);
                $('#depends_on_stock_1').attr('disabled', false);
            }
            else
            {
                $(this).val(0);
                $('#depends_on_stock_1').attr('disabled', true);
                $('#depends_on_stock_0').attr('checked', true);
                // self.ajaxCall({actionQty: 'depends_on_stock', value: 0});
                // self.refreshQtyAvailabilityForm();
            }
            // self.refreshQtyAvailabilityForm();
        });

        $('#setAdvancedStockManagementAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['advanced_stock_management'] = (tab.find('[name=advanced_stock_management]').prop('checked')) ? 1 : 0;
            data['depends_on_stock'] = tab.find('[name=depends_on_stock]:checked').val();
            setAllProducts(data, 'advanced_stock_management');
        });
    },
    'meta': function () {
        initLanguages();

        var input_id = 'tags';
        $('#'+input_id).tagify({ delimiters: [13,44], addTagPrompt: Translator().l('Add tag', 'mep') });

        $('#setMetaAllProduct').live('click', function () {
            $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
            var self = $(this);
            setTimeout(function () {
                var tab = self.closest('.tab_content');
                var tags = tab.find('[name=tags]').val();
                var tag_no_enter = $('.tagify-container').find('input').val();
                if(tags && tag_no_enter) {
                    tags += ','+tag_no_enter;
                } else if (tag_no_enter) {
                    tags = tag_no_enter;
                }
                var data = {};
                data['meta_title'] = tab.find('[name=meta_title]').val();
                data['meta_description'] = tab.find('[name=meta_description]').val();
                data['meta_keywords'] = tab.find('[name=meta_keywords]').val();
                data['meta_chpu'] = tab.find('[name=meta_chpu]').val();
                data['meta_redirect'] =$('[name=redirection_page] option:selected').text();
                data['category_redirect'] = $('.category_redirect option:selected').data("id");
                data['product_redirect'] = $('.product_redirect option:selected').data("id");
                data['tags'] = tags;
                data['edit_tags'] = tab.find('[name=edit_tags]:checked').val();
                data['language'] = tab.find('[name=language_meta]:checked').val();
                setAllProducts(data, 'meta');
            }, 300)
        });
    },
    'reference': function () {
        $('#setReferenceAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['reference']= tab.find('[name="reference"]').val();
            data['ean13']= tab.find('[name="ean13"]').val();
            data['upc'] = tab.find('[name="upc"]').val();
            data['change_for_property']= tab.find('[name="change_for_property"]:checked').val();
            data['combinations'] = $.getAllValues();
            setAllProducts(data, 'reference');
        });
    },
    'createproducts': function () {
        $('#createProducts').live('click', function (){
            var tab = $(this).closest('.tab_content');
            var data = {};
            for(var i in languages) {
                data['name_'+languages[i].id_lang] = tab.find('[name="name_'+languages[i].id_lang+'"]').val();
            }
            data.attribute = tab.find('[name="attribute"]').val();
            var categories = [];
            tab.find('[name="categoryBox[]"]:checked').each(function () {
                categories.push($(this).val());
            });
            data.categoryBox = categories;
            data.id_category_default = tab.find('[name="id_category_default"]').val();
            data.unit_price = tab.find('[name="unit_price"]').val();
            setAllProducts(data, 'createProducts');
        });
    },
    'customization': function () {
        function addCustomizationField(type)
        {
            var counter = $('[data-customization-field="'+type+'"]').length;
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'add_customization_field',
                    type: type,
                    counter: counter
                },
                success: function (json) {
                    $('#customization_fields_'+type).append(json.html);
                    $.triggerChangeLang();
                }
            });
        }

        $('.addFileLabel').live('click', function () {
            addCustomizationField(0);
        });
        $('.addTextLabel').live('click', function () {
            addCustomizationField(1);
        });

        $('#setCustomizationAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};

            tab.find('[name^="label_"]').each(function () {
                var elem = $(this);
                var input_name = elem.attr('name').replace('_', '[').split('_').join('][')+']';

                if (elem.is('[type=text]')) {
                    data[input_name] = elem.val();
                }
                if (elem.is('[type=checkbox]')
                    && elem.is(':checked')) {
                    data[input_name] = elem.val();
                }
            });

            data['delete_customization_fields'] = (tab.find('[name="delete_customization_fields"]:checked').length ? 1 : 0);

            setAllProducts(data, 'customization');
        });
        $.triggerChangeLang();
    },
    'quantity': function () {
        $('#setQuantityAllProduct').live('click', function () {
            var tab = $(this).closest('.tab_content');
            var data = {};
            data['quantity'] = parseInt(tab.find('[name="quantity"]').val());
            data['change_for'] = tab.find('[name="change_for_qty"]:checked').val();
            data['change_type'] = tab.find('[name="change_type"]:checked').val();
            data['combinations'] = $.getAllValues();
            data['action_quantity'] = parseInt(tab.find('[name="action_quantity"]:checked').val());
            data['warehouse'] = parseInt(tab.find('[name=warehouse]').val());
            data['action_warehouse'] = parseInt(tab.find('[name=action_warehouse]').val());

            data['language'] = tab.find('[name=language_qty]:checked').val();
            data['available_now'] = tab.find('[name=available_now]').val();
            data['language2'] = tab.find('[name=language_qty2]:checked').val();
            data['available_later'] = tab.find('[name=available_later]').val();

            data['change_available_date'] = tab.find('[name=change_available_date]:checked').val();
            data['available_date'] = tab.find('[name=available_date]').val();
            data['out_of_stock'] = tab.find('[name=out_of_stock]:checked').val();
            data['minimal_quantity'] = tab.find('[name=minimal_quantity]').val();
            setAllProducts(data, 'quantity');
        });
    }
};

/**
 * Feature collection management
 */
var featuresCollection = (function() {

    var collectionHolder;
    var maxCollectionChildren;

    /** Add a feature */
    function add() {
        var newForm = collectionHolder.attr('data-prototype').replace(/__name__/g, maxCollectionChildren);
        collectionHolder.append(newForm);
        maxCollectionChildren += 1;
        switchLanguage($('#form_switch_language').val());
    }

    function switchLanguage(iso_code) {
        $('div.translations.tabbable > div > div.translation-field:not(.translation-label-' + iso_code + ')').removeClass('show active');

        const langueTabSelector = 'div.translations.tabbable > div > div.translation-field.translation-label-' + iso_code;
        $(langueTabSelector).addClass('show active');
    }

    return {
        'init': function() {
            collectionHolder = $('.feature-collection');
            maxCollectionChildren = collectionHolder.children('.row').length;
            /** Click event on the add button */
            $('#features .add').on('click', function(e) {
                e.preventDefault();
                add();
                $('#features-content').removeClass('hide');
            });

            /** Click event on the remove button */
            $(document).on('click', '.feature-collection .delete', function(e) {
                e.preventDefault();

                $(this).closest('.product-feature').remove();
            });

            function replaceEndingIdFromUrl(url, newId)
            {
                return url.replace(/\/\d+(?!.*\/\d+)((?=\?.*))?/, '/' + newId);
            }

            /** On feature selector event change, refresh possible values list */
            $(document).on('change', '.feature-collection select.feature-selector', function(event) {
                var that = event.currentTarget;
                var $row = $($(that).parents('.row')[0]);
                var $selector = $row.find('.feature-value-selector');

                if('' !== $(this).val()) {
                    $.ajax({
                        url: replaceEndingIdFromUrl($(this).attr('data-action'), $(this).val()),
                        success: function(response) {
                            $selector.prop('disabled', response.length === 0);
                            $selector.empty();
                            $.each(response, function(index, elt) {
                                // the placeholder shouldn't be posted.
                                if ('0' == elt.id) {
                                    elt.id = '';
                                }
                                $selector.append($('<option></option>').attr('value', elt.id).text(elt.value));
                            });
                        }
                    });
                }
            });

            var $featuresContainer = $('#features-content');

            $featuresContainer.on('change', '.row select, .row input[type="text"]', function onChange(event){
                var that = event.currentTarget;
                var $row = $($(that).parents('.row')[0]);
                var $definedValueSelector = $row.find('.feature-value-selector');
                var $customValueSelector = $row.find('input[type=text]');

                // if feature has changed we need to reset values
                if ($(that).hasClass('feature-selector')) {
                    $customValueSelector.val('');
                    $definedValueSelector.val('');
                }
            });

            $featuresContainer.find('#form_switch_language').change(function(event) {
                event.preventDefault();
                switchLanguage(event.target.value);
            });
        }
    };
})();
