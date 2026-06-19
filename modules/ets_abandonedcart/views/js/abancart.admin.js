/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
if (typeof PS_ALLOW_ACCENTED_CHARS_URL === 'undefined') {
    PS_ALLOW_ACCENTED_CHARS_URL = false;
}
var etsACListShoppingShortcode = ['product_list', 'total_cart', 'total_products_cost', 'total_shipping_cost', 'total_tax', 'checkout_button', 'money_saved', 'total_payment_after_discount'];
if (!id_language)
    var id_language = Number(1);
if (!default_language)
    var default_language = id_language;
var ETS_ABANCART_AJAX_LINK = ETS_ABANCART_AJAX_LINK || false,
    ets_abancart_img_dir = ets_abancart_img_dir || false,
    ets_abancart_textarea_changed = false,
    ets_abancart_tab_message_active = false,
    ets_abancart_btn_finish_name = ets_abancart_btn_finish || 'Finish',
    ets_abancart_btn_continue_name = ets_abancart_btn_continue || 'Continue',
    ets_abancart_btn_sendmail_name = ets_abancart_btn_sendmail || 'Send email',
    ETS_ABANCART_PS_VERSION_17 = ETS_ABANCART_PS_VERSION_17 || 0
;
var ETS_ABANCART_LINE_CHART,
    ETS_ABANCART_LC_DATA_AXESX = ETS_ABANCART_LC_DATA_AXESX || {},
    ETS_ABANCART_LC_DATASET = ETS_ABANCART_LC_DATASET || {},
    ETS_ABANCART_LC_TITLE = ETS_ABANCART_LC_TITLE || 'Line chart',
    ETS_ABANCART_LC_AXESX = ETS_ABANCART_LC_AXESX || '',
    ETS_ABANCART_LC_AXESY = ETS_ABANCART_LC_AXESY || '',
    ETS_ABANCART_LC_MINY = ETS_ABANCART_LC_MINY || 0,
    ETS_ABANCART_LC_MAXY = ETS_ABANCART_LC_MAXY || 1
;

var ETS_AC_ADMIN_CONTROLLER = ETS_AC_ADMIN_CONTROLLER || 'AdminEtsACDashboard',
    ETS_AC_LINK_REMINDER_ADMIN = ETS_AC_LINK_REMINDER_ADMIN || '',
    ETS_ABANCART_DELETE_TITLE = ETS_ABANCART_DELETE_TITLE || 'Delete',
    ETS_ABANCART_CLEAN_LOG_CONFIRM = ETS_ABANCART_CLEAN_LOG_CONFIRM || 'Do you want to clear all mail logs?';
;

var ets_ab_fn = {
    campaign_filter: '',
    reminder_filter: '',
    time_series_range: [],
    init: function () {
        ets_ab_cr.groupTabs();
        ets_ab_fn.groupCheck();
        ets_ab_fn.groupTabs();
        ets_ab_fn.previewLanguage();
        ets_ab_fn.discountOption();
        ets_ab_fn.mailService();
        ets_ab_fn.drawColor();
        if ($('#ets_abancart_chart1').length > 0 && Object.keys(ETS_ABANCART_LC_DATASET).length > 0) {
            ETS_ABANCART_LINE_CHART = ets_ab_chart.addLineChart($('#ets_abancart_chart1'), {
                title: ETS_ABANCART_LC_TITLE,
                labels: ETS_ABANCART_LC_DATA_AXESX,
                datasets: ETS_ABANCART_LC_DATASET,
                labelX: ETS_ABANCART_LC_AXESX,
                labelY: ETS_ABANCART_LC_AXESY,
                minY: ETS_ABANCART_LC_MINY,
                maxY: ETS_ABANCART_LC_MAXY,
            });
        }
        ets_ab_chart.timeSeriesOption();
        this.templateType();
        if ($('#view_email_content').length > 0) {
            $('.ets_abancart_preview.view_email_template').html(ets_ab_fn.doShortCode($('#view_email_content').val(), ''));
        }
        ets_ab_fn.pvIconBrowser();
        ets_ab_fn.selectMultiple($('select[id^=countries], select[id^=languages]'));

        //Search has purchased product:
        ets_ab_autocomplete.product('#search_purchased_product'
            , '#purchased_product'
            , 'ets_abancart_purchased_product'
            , '.ets_abancart_product_list.purchased_product'
            , '#not_purchased_product'
        );
        //Search has not purchased product:
        ets_ab_autocomplete.product('#search_not_purchased_product'
            , '#not_purchased_product'
            , 'ets_abancart_not_purchased_product'
            , '.ets_abancart_product_list.not_purchased_product'
            , '#purchased_product'
        );
        ets_ab_fn.hasPlacedOrder();
    },
    groupCheck: function () {
        if ($('input[type=checkbox].abancart_group:checked').length == $('input[type=checkbox].abancart_group').length) {
            $('input[type=checkbox].all_abancart_group:not(:checked)').prop('checked', true);
        } else {
            $('input[type=checkbox].all_abancart_group:checked').prop('checked', false);
        }
    },
    groupTabs: function (el) {
        var _el = el || (parseInt($('#id_ets_abancart_reminder').val()) > 0 ? $('.ets_abancart_tab_item').last() : $('.ets_abancart_tab_item').first());
        if (_el.length <= 0)
            return false;


        if (_el.attr('data-tab') == 'confirm_information' && !$('.ets_abancart_tab_item[data-tab="confirm_information"]').length) {
            _el = $('.ets_abancart_tab_item[data-tab="message"]');
        }
        $('.ets_abancart_tab_item.active, .form-group.abancart.active').removeClass('active');

        _el.addClass('active');
        $('.form-group.abancart.form_' + _el.data('tab')).addClass('active');
        if (!$('#discount_option_no').is(':checked')) {
            $('.ets_abancart_short_code.group_discount').show();
        } else {
            $('.ets_abancart_short_code.group_discount').hide();
        }

        ets_ab_fn.countDownOption();
        ets_abancart_tab_message_active = _el.data('tab') !== 'message' ? false : true;
        ets_abancart_textarea_changed = parseInt($('#id_ets_abancart_email_template').val()) >= 0 ? true : false;

        /*---next & prev---*/
        ets_ab_fn.prevNext();

        /*---Re-build Language---*/
        ets_ab_fn.previewLanguage();
        ets_ab_fn.drawColor();

        if (_el.data('tab').trim() === 'discount') {
            ets_ab_fn.discountOpt();
        }
        if (_el.data('tab') == 'confirm_information') {
            $('.ets_ac_btn_step_continue').addClass('hide');
            $('.ets_ac_btn_step_save').removeClass('hide');
            ets_ab_fn.saveAndSend(false);
        }
    },
    countDownOption: function () {
        if ($('#enable_count_down_clock_on').length) {
            var _el = $('#enable_count_down_clock_on'),
                _propEl = $('.ets_abancart_short_code.group_discount.count_down_clock')
            ;
            if (!$('#discount_option_yes').is(':checked') && _el.is(':checked') && !$('#discount_option_no').is(':checked')) {
                _propEl.show();
            } else {
                _propEl.hide();
            }
        } else {
            if ($('input[name="ETS_ABANCART_ENABLE_COUNTDOWN_CLOCK"]:checked').val() == 1) {
                $('.ets_abancart_short_code.discount_count_down_clock').removeClass('hide');
            } else {
                $('.ets_abancart_short_code.discount_count_down_clock').addClass('hide');
            }
        }
    },
    discountOpt: function (el) {
        var _el = el || $('input[name=discount_option]:checked').val();
        if (_el) {
            $('.form-group.abancart.form_discount.discount_option:not(.is_parent1).active').removeClass('active');
            $('.form-group.abancart.form_discount.discount_option.' + _el).addClass('active');
            if (_el === 'auto') {
                ets_ab_fn.discountType();
            }
        }
        ets_ab_fn.displayEmailTemplate(el);
        if ($('input[name="free_gift"]:checked').val() == '1') {
            $('.ets_ac_gift_product_filter_group').removeClass('hide');
        } else {
            $('.ets_ac_gift_product_filter_group').addClass('hide');
        }
        if ($('input[name=discount_option]:checked').val() == 'auto') {
            $('.ets_ac_discount_qty').removeClass('hide');
        } else {
            $('.ets_ac_discount_qty').addClass('hide');
        }

        $('.ets_ac_selected_product_group').addClass('hide');
        $('.ets_ac_specific_product_group').addClass('hide');

        if ($('.ets_ac_apply_discount').hasClass('active')) {

            if ($('input[name="apply_discount_to"]:checked').val() == 'selection') {
                $('.ets_ac_selected_product_group').removeClass('hide');
            } else if ($('input[name="apply_discount_to"]:checked').val() == 'specific') {
                $('.ets_ac_specific_product_group').removeClass('hide');
            }
        }

        if ($('input[name="apply_discount"]:checked').val() == 'amount') {
            $('#apply_discount_to_cheapest').closest('li').addClass('hide');
            $('#apply_discount_to_selection').closest('li').addClass('hide');
            if ($('#apply_discount_to_cheapest').is(':checked') || $('#apply_discount_to_selection').is(':checked')) {
                $('#apply_discount_to_cheapest').prop('checked', false);
                $('#apply_discount_to_selection').prop('checked', false);
                $('#apply_discount_to_order').prop('checked', true);
            }
        } else if ($('input[name="apply_discount"]:checked').val() == 'percent') {
            $('#apply_discount_to_cheapest').closest('li').removeClass('hide');
            $('#apply_discount_to_selection').closest('li').removeClass('hide');
        }
    },
    discountType: function (el) {
        var _el = el || $('input[name=apply_discount]:checked').val();
        if (_el) {
            $('.form-group.abancart.form_discount.apply_discount:not(.is_parent2).active').removeClass('active');
            $('.form-group.abancart.form_discount.apply_discount.' + _el).addClass('active');
        }
    },
    resetReduction: function () {
        //reset default if is invalid
        var option = $('discount_option').val();
        if (!/^(\d+(\.?)\d*)$/.test($('#reduction_amount').val()) || option === 'percent')
            $('#reduction_amount').val(0);
        if (!/^([0-9]|[1-9][0-9]|100)$/.test($('#reduction_percent').val()) || option === 'amount')
            $('#reduction_percent').val(0);
    },
    displayEmailTemplate: function (dom) {
        var option = dom || $('input[name=discount_option]:checked').val();
        $('.ets_abancart_template_li:not([data-type-of=both])').hide();
        if (option !== 'no') {
            $('.ets_abancart_template_li[data-type-of=with_discount]').show();
        } else {
            $('.ets_abancart_template_li[data-type-of=without_discount]').show();
        }
        if (!ets_abancart_textarea_changed)
            ets_ab_fn.selectTemplates();
    },
    discountOption: function (el) {
        var _el = el || $('input[name=ETS_ABANCART_DISCOUNT_OPTION]:checked').val();
        if (_el) {
            $('.form-group.leave.discount_option:not(.is_parent1)').hide();
            if (parseInt($('#ETS_ABANCART_HAS_PRODUCT_IN_CART').val()) !== 1) {
                if ($('#ETS_ABANCART_DISCOUNT_OPTION_auto').is(':checked'))
                    $('#ETS_ABANCART_DISCOUNT_OPTION_no').prop('checked', true);
                _el = $('input[name=ETS_ABANCART_DISCOUNT_OPTION]:checked').val();
                $('#ETS_ABANCART_DISCOUNT_OPTION_auto').parent('li').hide();
            } else {
                $('.ets_abancart_ETS_ABANCART_DISCOUNT_OPTION').show();
            }
            $('.form-group.leave.discount_option.' + _el).show();
            if (_el === 'auto') {
                ets_ab_fn.applyDiscount();
            }
        }
        if ($('#ETS_ABANCART_DISCOUNT_OPTION_no').is(':checked'))
            $('.ets_abancart_short_code.group_discount').hide();
        else
            $('.ets_abancart_short_code.group_discount').show();
        //reset default if is invalid.
        if (!/^(\d)+$/.test($('#ETS_ABANCART_APPLY_DISCOUNT_IN').val()))
            $('#ETS_ABANCART_APPLY_DISCOUNT_IN').val(1);
        if (!/^([a-zA-Z0-9-_])*$/.test($('#discount_code').val())) {
            $('#ETS_ABANCART_DISCOUNT_CODE').val('');
        }
    },
    applyDiscount: function (el) {
        var _el = el || $('input[name=ETS_ABANCART_APPLY_DISCOUNT]:checked').val();
        if (_el) {
            $('.form-group.leave.apply_discount:not(.is_parent2)').hide();
            $('.form-group.leave.apply_discount.' + _el).show();
        }
        //reset default if is invalid
        if (!/^(\d+(\.?)\d*)$/.test($('#ETS_ABANCART_REDUCTION_AMOUNT').val()))
            $('#ETS_ABANCART_REDUCTION_AMOUNT').val(0);
        if (!/^([0-9]|[1-9][0-9]|100)$/.test($('#ETS_ABANCART_REDUCTION_PERCENT').val()))
            $('#ETS_ABANCART_REDUCTION_PERCENT').val(0);
    },
    formSubmit: function (form, submitReset) {
        // Defines.
        var _sbr = submitReset || false,
            _form = form || false
        ;

        //Submit forms.
        if (_form.length > 0 && !_form.hasClass('active') && _form.attr('action') && _form.attr('action') !== '#') {
            _form.addClass('active');

            var _fd = new FormData(form.get(0));
            //_fd.append('getLists', 1);
            _fd.append('ajax', 1);
            _fd.append('action', 'renderList');
            if (_sbr)
                _fd.append('submitReset' + _form.attr('id').replace(/^form-/i, ''), 1);

            // Do request ajax.
            $.ajax({
                type: 'post',
                url: _form.attr('action'),
                data: _fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (json) {
                    _form.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            $('.ets_abancart_reminder').html(json.html);
                        }
                    }
                },
                error: function () {
                    _form.removeClass('active');
                }
            });
        }
    },
    doShortCode: function (html, type) {
        var date = new Date(),
            dateFrom = new Date(date.getFullYear(), date.getMonth() + 1, date.getDay()),
            _ct = type || 'email'
        ;
        var buttons = [
            {
                short_code: 'checkout_button',
                text: '<a style="background-color: #2FB5DB;border: medium none;text-decoration: none;border-radius: 4px;color: #fff;cursor: pointer;font-size: 14px;margin: 0 auto;padding: 7px 15px;text-align: center;" href="#" class="ets_abancart_checkout">Go to checkout</a>',
                selector: ''
            },
            {
                short_code: 'button_no_thanks',
                text: '<a class="ets_abancart_no_thanks" style="text-decoration: underline!important" href="#">No, I don\'t like it. Thanks</a>',
                selector: ''
            },
            {
                short_code: 'show_discount_box',
                text: '<span class="discount-box" style="display: inline-block;"><i class=""><svg style="width:32px;height:32px;fill:#999999;vertical-align: -8px;margin-right: 10px;" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 896q26 0 45 19t19 45-19 45-45 19-45-19-19-45 19-45 45-19zm300 64l507 398q28 20 25 56-5 35-35 51l-128 64q-13 7-29 7-17 0-31-8l-690-387-110 66q-8 4-12 5 14 49 10 97-7 77-56 147.5t-132 123.5q-132 84-277 84-136 0-222-78-90-84-79-207 7-76 56-147t131-124q132-84 278-84 83 0 151 31 9-13 22-22l122-73-122-73q-13-9-22-22-68 31-151 31-146 0-278-84-82-53-131-124t-56-147q-5-59 15.5-113t63.5-93q85-79 222-79 145 0 277 84 83 52 132 123t56 148q4 48-10 97 4 1 12 5l110 66 690-387q14-8 31-8 16 0 29 7l128 64q30 16 35 51 3 36-25 56zm-681-260q46-42 21-108t-106-117q-92-59-192-59-74 0-113 36-46 42-21 108t106 117q92 59 192 59 74 0 113-36zm-85 745q81-51 106-117t-21-108q-39-36-113-36-100 0-192 59-81 51-106 117t21 108q39 36 113 36 100 0 192-59zm178-613l96 58v-11q0-36 33-56l14-8-79-47-26 26q-3 3-10 11t-12 12q-2 2-4 3.5t-3 2.5zm224 224l96 32 736-576-128-64-768 431v113l-160 96 9 8q2 2 7 6 4 4 11 12t11 12l26 26zm704 416l128-64-520-408-177 138q-2 3-13 7z"/></svg></i><span class="ets_abancart_discount_box" style="background: #f4ffef;color:#7a7a7a;padding: 8px 25px;border: 1px dashed #333;border-radius: 0;font-size: 16px;display: inline-block;">XQBT49WR</span></span>',
                selector: ''
            },
            {
                short_code: 'button_add_discount',
                text: '<a class="ets_abancart_add_discount" href="#" style="background-color: #2FB5DB;border: medium none;text-decoration: none;border-radius: 4px;color: #fff;cursor: pointer;font-size: 14px;margin: 0 auto;padding: 7px 15px;text-align: center;display: inline-block;">' + (_ct !== 'bar' ? 'Apply code and checkout' : 'Click here to checkout and get $11.384 off.') + '</a>',
                selector: ''
            },
            {
                short_code: 'shop_button',
                text: '<a class="ets_abancart_shop_button" href="#" style="background-color: rgb(55, 71, 79);display: inline-block;text-transform: uppercase;border: none;text-decoration: none;border-radius: 7px;color: rgb(255, 255, 255);cursor: pointer;font-size: 22px;margin: 0px;padding: 8px 25px;text-align: center;">Go to shop</a>',
                selector: ''
            },
        ];

        buttons.forEach(function (btn) {
            html = ets_ab_fn.regexColor(
                btn.short_code
                , html
                , btn.text
                , btn.selector
            );
        });

        var shortCodeTexts = [
            {
                short_code: 'total_cart',
                text: '$56.92'
            },
            {
                short_code: 'firstname',
                text: 'John'
            },
            {
                short_code: 'lastname',
                text: 'DOE'
            },
            {
                short_code: 'shop_name',
                text: 'My Store'
            },
            {
                short_code: 'logo',
                text: '<img src="' + ETS_AC_LOGO_LINK + '" class="ets_abancart_short_code_logo" title="Logo" alt="Logo" />',
                is_html: true
            },
            {
                short_code: 'discount_code',
                text: 'XQBT49WR'
            },
            {
                short_code: 'discount_from',
                text: '03-07-2019'
            },
            {
                short_code: 'discount_to',
                text: '03-08-2019'
            },
            {
                short_code: 'reduction',
                text: '20%'
            },
            {
                short_code: 'total_products_cost',
                text: '$45.38'
            },
            {
                short_code: 'total_shipping_cost',
                text: '$7'
            },
            {
                short_code: 'total_tax',
                text: '$4.54'
            },
            {
                short_code: 'total_payment_after_discount',
                text: '$45.54'
            },
            {
                short_code: 'money_saved',
                text: '$11.38'
            },
            {
                short_code: 'unsubscribe',
                text: '<a class="ets_abancart_link_unsubscribe" href="#">Unsubscribe</a>',
                is_html: true
            },
            {
                short_code: 'discount_count_down_clock',
                text: '<span class="ets_abancart_count_down_clock" data-style="" data-date="' + dateFrom.getTime() + '"></span>'
            },
            {
                short_code: 'countdown_clock',
                text: '<span class="ets_ac_evt_countdown2" data-style="" data-date=""></span>',
                is_html: true
            },
            {
                short_code: 'registration_date',
                text: '05-11-2020'
            },
            {
                short_code: 'last_order_id',
                text: '121'
            },
            {
                short_code: 'last_order_reference',
                text: 'XKBKNABJK'
            },
            {
                short_code: 'last_order_total',
                text: '$121.68'
            },
            {
                short_code: 'order_total',
                text: '$52.00'
            },
            {
                short_code: 'last_time_login_date',
                text: '09-11-2020'
            },
            {
                short_code: 'shop_button',
                text: '<a class="ets_abancart_shop_button" href="#" style="background-color: rgb(55, 71, 79);display: inline-block;border: none;text-decoration: none;border-radius: 7px;color: rgb(255, 255, 255);cursor: pointer;font-size: 22px;margin: 0px;padding: 8px 25px;text-align: center;text-transform: uppercase;">Go to shop</a>',
                is_html: true
            },
            {
                short_code: 'lead_form',
                text: '<div class="ets_abancart_leadform"><div class="ets_abancart_leadform_items"><span>Name</span><input placeholder="name" /></div><div class="ets_abancart_leadform_items"><span>Email</span><input placeholder="Email" /></div><div class="ets_abancart_leadform_items"><span>Phone Number</span><input placeholder="phone" /></div></div>',
                is_html: true
            },
            {
                short_code: 'custom_button',
                text: '',
            },
            {
                short_code: 'product_list',
                text: '<div class="ets_abancart_product_list_table shortcode-prd-list"><table class="product_list" style="width: 100%;border: 1px solid #ddd;border-collapse: collapse;"><tr class="product-item b_1_ddd fs_14"><td class="image" style="max-width: 100px;width:20%;border-bottom:1px solid #ddd;"> ' + (ETS_AC_IMG_MODULE_LINK ? '<img src="' + ETS_AC_IMG_MODULE_LINK + '3-small_default.jpg" alt="The best is yet to come\' Framed poster" style="padding: 5px;min-width:60px;max-width:100%;box-sizing:border-box;"/>' : '') + ' </td><td class="description" style="padding: 5px;vertical-align: middle;border-bottom:1px solid #ddd;"><div class="product-line-grid-body"> <div class="product-line-info"><a class="label" href="javascript:void(0);" data-id_customization="-1" style="text-align:left;padding-left:0;color:#333;display:block;font-weight:600;margin-bottom:5px;text-decoration:none;">The best is yet to come\' Framed poster</a></div><div class="product_combination" style="font-size:11px;"> Size-S, Color-White</div><div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;padding-top: 5px;"><span class="product-discount" style="display:inline-block;margin-right:12px;"><span class="regular-price" style="text-decoration:line-through;color:#999;margin-right:5px;font-size:90%;">$20.50</span><span class="price" style="color:#2fb5d2;font-weight:600;">$16.40</span></span><span class="quantity" style="margin-right:12px;color:#999;display:inline-block;">x <span class="cart-quantity" data-title="Qty">1</span></span><span class="total" style="float:right;"><span class="product-price" style="color:#2fb5d2;font-weight:600;"><strong>$16.40</strong></span></span></div></div></td></tr><tr class="product-item b_1_ddd fs_14"><td class="image" style="max-width:100px;width:20%;border-bottom:1px solid #ddd;"> ' + (ETS_AC_IMG_MODULE_LINK ? '<img src="' + ETS_AC_IMG_MODULE_LINK + '6-small_default.jpg" alt="Mug The best is yet to come" style="padding:5px;min-width:60px;max-width:100%;box-sizing: border-box;"/>' : '') + '</td><td class="description" style="border-bottom:1px solid #ddd;padding:5px;vertical-align:middle;"><div class="product-line-grid-body"> <div class="product-line-info"><a class="label" href="javascript:void(0);" data-id_customization="-1" style="text-align:left;padding-left:0;color:#333;display:block;font-weight:600;margin-bottom:5px;text-decoration:none;">Mug The best is yet to come</a></div><div class="product_combination" style="font-size:11px;"> Size-S, Color-White</div><div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;padding-top: 5px;"><span class="product-discount" style="display:inline-block;margin-right:12px;"><span class="regular-price" style="text-decoration:line-through;color:#999;margin-right:5px;font-size:90%;">$20.50</span><span class="price" style="color:#2fb5d2;font-weight:600;">$16.40</span></span><span class="quantity" style="margin-right:12px;color:#999;display:inline-block;">x <span class="cart-quantity" data-title="Qty">1</span></span><span class="total" style="float:right;"><span class="product-price" style="color:#2fb5d2;font-weight:600;"><strong>$16.40</strong></span></span></div></div></td></tr></table></div>',
                is_html: true
            },
            {
                short_code: 'product_grid',
                text: '<div class="ets_abancart_product_grid"> <div class="product_grid" style="width: 100%;border: 1px solid #ddd;"> <div class="product-item"> <div class="product-wrapper"> ' + (ETS_AC_IMG_MODULE_LINK ? '<img src="' + ETS_AC_IMG_MODULE_LINK + '3-small_default.jpg" alt="The best is yet to come\' Framed poster" style="padding: 5px;min-width:60px;max-width:100%;box-sizing:border-box;"/>' : '') + ' <div class="ets_abancart_product_info"> <div class="product-line-info"><a class="label" href="javascript:void(0);" data-id_customization="-1" style="text-align:left;padding-left:0;color:#333;display:block;font-weight:600;margin-bottom:5px;text-decoration:none;">The best is yet to come\' Framed poster</a></div> <div class="product_combination" style="font-size:11px;"> Size-S, Color-White</div> <div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;padding-top:5px;"><span class="product-discount" style="display:inline-block;margin-right:12px;"><span class="regular-price" style="text-decoration:line-through;color:#999;margin-right:5px;font-size:90%;">$20.50</span><span class="price" style="color:#2fb5d2;font-weight:600;">$16.40</span></span><span class="quantity" style="margin-right:12px;color:#999;display:inline-block;">x <span class="cart-quantity" data-title="Qty">1</span></span><span class="total" style="float:right;"><span class="product-price" style="color:#2fb5d2;font-weight:600;"><strong>$16.40</strong></span></span> </div> </div> </div> </div> <div class="product-item"> ' + (ETS_AC_IMG_MODULE_LINK ? '<img src="' + ETS_AC_IMG_MODULE_LINK + '6-small_default.jpg" alt="Mug The best is yet to come" style="padding:5px;min-width:60px;max-width:100%;box-sizing: border-box;"/>' : '') + ' <div class="ets_abancart_product_info"> <div class="product-line-info"> <a class="label" href="javascript:void(0);" data-id_customization="-1" style="text-align:left;padding-left:0;color:#333;display:block;font-weight:600;margin-bottom:5px;text-decoration:none;">Mug The best is yet to come</a></div> <div class="product_combination" style="font-size:11px;"> Size-S, Color-White</div> <div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;padding-top: 5px;"><span class="product-discount" style="display:inline-block;margin-right:12px;"><span class="regular-price" style="text-decoration:line-through;color:#999;margin-right:5px;font-size:90%;">$20.50</span><span class="price" style="color:#2fb5d2;font-weight:600;">$16.40</span></span><span class="quantity" style="margin-right:12px;color:#999;display:inline-block;">x <span class="cart-quantity" data-title="Qty">1</span></span><span class="total" style="float:right;"><span class="product-price" style="color:#2fb5d2;font-weight:600;"><strong>$16.40</strong></span></span> </div> </div> </div> <div class="product-item"> <div class="product-wrapper"> ' + (ETS_AC_IMG_MODULE_LINK ? '<img src="' + ETS_AC_IMG_MODULE_LINK + '3-small_default.jpg" alt="The best is yet to come\' Framed poster" style="padding: 5px;min-width:60px;max-width:100%;box-sizing:border-box;"/>' : '') + ' <div class="ets_abancart_product_info"> <div class="product-line-info"><a class="label" href="javascript:void(0);" data-id_customization="-1" style="text-align:left;padding-left:0;color:#333;display:block;font-weight:600;margin-bottom:5px;text-decoration:none;">The best is yet to come\' Framed poster</a></div> <div class="product_combination" style="font-size:11px;"> Size-S, Color-White</div> <div class="product-line-info product-price has-discount" style="display:block;padding-right:10px;padding-top: 5px;"><span class="product-discount" style="display:inline-block;margin-right:12px;"><span class="regular-price" style="text-decoration:line-through;color:#999;margin-right:5px;font-size:90%;">$20.50</span><span class="price" style="color:#2fb5d2;font-weight:600;">$16.40</span></span><span class="quantity" style="margin-right:12px;color:#999;display:inline-block;">x <span class="cart-quantity" data-title="Qty">1</span></span><span class="total"><span class="product-price" style="color:#2fb5d2;font-weight:600;"><strong>$16.40</strong></span></span> </div> </div> </div> </div> </div> </div>',
                is_html: true
            }
        ];

        $.each(shortCodeTexts, function (i, el) {
            if (typeof el.is_html !== 'undefined' && el.is_html) {
                if (el.short_code == 'lead_form') {
                    var matchesLeadForm = html.match(/\[lead_form\s+id=(\d+)\]/g);
                    if (matchesLeadForm) {
                        $.each(matchesLeadForm, function (index, item) {
                            var matchItem = /\[lead_form\s+id=(\d+)\]/g.exec(item);
                            if (matchItem && matchItem.length && typeof matchItem[1] !== "undefined") {
                                html = ets_ab_fn.regexColor(el.short_code, html, ets_ab_fn.getLeadForm(type, matchItem[1]), false, matchItem[1]);
                            }
                        });
                    }
                } else if (el.short_code == 'product_grid') {
                    var matchesLeadForm = html.match(/\[product_grid\s+id="([0-9,]+)"(\s+[^\]]*)?\]/g);
                    if (matchesLeadForm) {
                        $.each(matchesLeadForm, function (index, item) {
                            var matchItem = /\[product_grid\s+id="([0-9,]+)"(\s+[^\]]*)?\]/g.exec(item);
                            if (matchItem && matchItem.length && typeof matchItem[1] !== "undefined") {
                                var id = matchItem[1].trim().replace(/,$/g, '').replace(/^,/g, '');
                                var prdGrid = $('<div id="ets_ac_tpm_prd_grid">' + el.text + '</div>');
                                if (id) {
                                    var ids = id.split(',');
                                    if (ids.length == 2) {
                                        prdGrid.find('.product-item').last().remove();
                                    } else if (ids.length == 1) {
                                        prdGrid.find('.product-item').last().remove();
                                        prdGrid.find('.product-item').first().remove();
                                    } else if (ids.length > 3) {
                                        for (var it = 0; it < ids.length - 3; it++) {
                                            prdGrid.find('.ets_abancart_product_grid .product_grid .product-item').first().clone().appendTo(prdGrid.find('.ets_abancart_product_grid .product_grid'));
                                        }
                                    }
                                }

                                html = ets_ab_fn.regexColor('product_grid\\s+id="' + matchItem[1] + '"', html, prdGrid.html());
                            }
                        });
                    } else if (html.match(/\[product_grid\s+id=""(\s+[^\]]*)?\]/g)) {
                        html = ets_ab_fn.regexColor('product_grid', html, el.text);
                    } else {
                        html = ets_ab_fn.regexColor('product_grid', html, '');
                    }
                } else if (el.short_code == 'countdown_clock') {
                    var matchesCountdown = html.match(/\[countdown_clock\s+endtime="(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})"([^\]]*)?\]/g);
                    if (matchesCountdown) {
                        $.each(matchesCountdown, function (index, item) {
                            var matchItem = /\[countdown_clock\s+endtime="(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})"([^\]]*)?\]/g.exec(item);
                            if (matchItem && matchItem.length && typeof matchItem[1] !== "undefined") {
                                var tmp = $('<div class="ets_ac_tmp">' + el.text + '</div>');
                                var time = '';
                                try {
                                    var dateItem = new Date(matchItem[1]);
                                    time = dateItem.getTime();
                                } catch (e) {
                                    time = '';
                                }
                                if (time) {
                                    tmp.find('.ets_ac_evt_countdown2').attr('data-date', time);
                                    html = ets_ab_fn.regexColor(el.short_code, html, tmp.html(), false, false, matchItem[1]);
                                } else {
                                    html = ets_ab_fn.regexColor(el.short_code, html, '', false);
                                }
                            }
                        });
                    }
                } else
                    html = ets_ab_fn.regexColor(el.short_code, html, el.text);
            } else {
                if (el.short_code == 'custom_button') {
                    var matchesCB = html.match(/\[custom_button href="([^"]*)"\s+text="([^"]*)"([^\]]*)?\]/g);
                    if (matchesCB) {
                        $.each(matchesCB, function (index, item) {
                            var matchItem = /\[custom_button href="([^"]*)"\s+text="([^"]*)"([^\]]*)?\]/g.exec(item);
                            if (matchItem && matchItem.length && typeof matchItem[1] !== "undefined" && typeof matchItem[2] !== "undefined") {
                                html = ets_ab_fn.regexColor(el.short_code, html, '<a class="ets_ac_custom_button" href="' + matchItem[1] + '">' + matchItem[2] + '</a>', false, false, false);
                            }
                        });
                    }
                } else
                    html = ets_ab_fn.regexColor(el.short_code, html, '<span class="ets_abancart_shot_code_content ' + el.short_code + '">' + el.text + '</span>');
            }
        });

        //html = html.replace(/\{shop_url\}/g, ETS_AC_FULL_BASE_URL);
        return html;
    },
    getLeadForm: function (type, id) {
        var leadForm = '';
        if (typeof id !== 'undefined' && id) {
            leadForm = id
        }
        if (leadForm && leadForm != '0') {

            if (!$('.ets-ac-lead-form-field-item[data-id="' + leadForm + '"]').length) {
                return '<span class="ets-ac-msg-lead-form-not-found">' + ETS_AC_TRANS.lead_form_not_found + '</span>';
            }
            if ($('.ets-ac-lead-form-field-item[data-id="' + leadForm + '"]').attr('data-enable') != '1') {
                return '<span class="ets-ac-msg-lead-form-disabled">' + ETS_AC_TRANS.lead_form_disabled + '</span>';
            }
            return (type != 'email' && type != 'customer' ? '<form>' : '') + $('.ets-ac-lead-form-field-item[data-id="' + leadForm + '"]').html() + (type != 'email' && type != 'customer' ? '</form>' : '');
        }
        return '';
    },
    regexColor: function (shortcode, html, str, selector, lead_form_id, endtime_countdown) {
        lead_form_id = lead_form_id || false;
        endtime_countdown = endtime_countdown || false;
        var pattern = '\\[' + shortcode + '([^\\]]*)?\\]';
        if (lead_form_id) {
            pattern = '\\[' + shortcode + '\\s+id=' + lead_form_id + '(\\s+[^\\]]*)?\\]';
        } else if (endtime_countdown) {
            pattern = '\\[' + shortcode + '\\s+endtime="' + endtime_countdown + '"(\\s+[^\\]]*)?\\]';
        } else if (shortcode == 'custom_button') {
            pattern = '\\[' + shortcode + '\\s+href="[^"]*"\\s+text="[^"]*"(\\s+[^\\]]*)?\\]';
        }
        var found = html.match(new RegExp(pattern, 'ig'));

        if (found) {
            found.forEach(function (attr) {
                str = str.replace(/\$/g, '%symbol%');
                var attrs = attr.match(/(?:color|font|background|border|padding|margin)[0-9a-z\-]*\:\s*[a-zA-Z0-9#]+/g),
                    temp = $(str);

                if (selector)
                    temp = temp.find(selector);
                if (attrs) {
                    if (shortcode == 'discount_count_down_clock') {

                        var styles = '';
                        if (attrs && attrs.length) {
                            styles = attrs.join(';') + ';';
                        }
                        if (temp.find('.ets_abancart_count_down_clock').length) {
                            temp.find('.ets_abancart_count_down_clock').attr('data-style', styles);
                        }
                        temp.attr('data-style', styles);
                    } else if (shortcode == 'countdown_clock') {
                        var styles = '';

                        if (attrs && attrs.length) {
                            styles = attrs.join(';') + ';';
                        }
                        temp.attr('data-style', styles);
                    } else {
                        attrs.forEach(function (item) {
                            var prop = item.split(':');
                            if (typeof prop[0] !== "undefined" && prop[0].trim() !== '' && typeof prop[1] !== "undefined" && prop[1].trim() !== '') {
                                if (shortcode == 'product_list' || shortcode.indexOf('product_grid') !== -1) {
                                    temp.find('a,p,td,div').css(prop[0].trim(), prop[1].trim());
                                }
                                temp.css(prop[0].trim(), prop[1].trim());
                            }
                        });
                    }

                }
                html = html.replace(new RegExp(pattern, 'i'), $('<span></span>').append(temp).html());
            });
        }
        html = html.replace(/%symbol%/g, '$');
        return html;
    },
    selectTemplates: function (el) {
        var onselect = el || false;
        var hasIdReminder = false;
        if ($('.ets_abancart_popup_content input[name="id_ets_abancart_reminder"]').val()) {
            hasIdReminder = true;
        }
        var toSelect = true;
        if (onselect && hasIdReminder && !confirm(ets_abancart_changed_confirm)) {
            toSelect = false;
        }

        var temp = el || $('li.ets_abancart_template_li:not(:hidden)').first();
        if (!temp.hasClass('loading') && ETS_ABANCART_AJAX_LINK && typeof tinyMCE !== "undefined" && (!ets_abancart_textarea_changed || toSelect)) {
            $('#id_ets_abancart_email_template').val(parseInt(temp.data('id')));
            $('.ets_abancart_template_li.active').removeClass('active');
            temp.addClass('active loading');
            ets_abancart_textarea_changed = false;

            if (parseInt(temp.data('id')) > 0) {
                $.ajax({
                    type: 'post',
                    url: ETS_AC_LINK_REMINDER_ADMIN,
                    data: 'ajax=1&action=selectTemplate&id_ets_abancart_email_template=' + parseInt(temp.data('id')),
                    dataType: 'json',
                    success: function (json) {
                        temp.removeClass('loading');
                        if (json) {
                            if ($('#ETS_ABANCART_LANG_DEFAULT').length > 0) {
                                var content = $('.form-group.abancart.content textarea[name=content]'),
                                    idLangDefault = parseInt($('#ETS_ABANCART_LANG_DEFAULT').val())
                                ;
                                if (content.length > 0 && content.attr('id') && typeof tinyMCE.get(content.attr('id')) !== "undefined") {
                                    var textContent = typeof json.html[idLangDefault] !== "undefined" ? json.html[idLangDefault] : json.html[$('#PS_LANG_DEFAULT').val()]
                                    tinyMCE.get(content.attr('id')).setContent(textContent);
                                    tinyMCE.triggerSave();
                                }
                            } else {
                                $.each(json.html, function (i, item) {
                                    var content = $('.form-group.abancart.content textarea[name=content_' + i + ']');
                                    if (content.length > 0 && content.attr('id') && typeof tinyMCE.get(content.attr('id')) !== "undefined") {
                                        tinyMCE.get(content.attr('id')).setContent(item);
                                        tinyMCE.triggerSave();
                                    }
                                });
                                $.each(json.subject, function (i, item) {
                                    var subject = $('.form-group.abancart.isMailSubject input[name=title_' + i + ']');
                                    if (subject.length > 0 && subject.attr('id')) {
                                        $('#' + subject.attr('id')).val(item);
                                    }
                                });
                            }
                            ets_ab_fn.previewLanguage();
                        }
                    },
                    error: function () {
                        temp.removeClass('loading');
                    }
                });
            } else {
                temp.removeClass('loading');
                $('.form-group.abancart.content .translatable-field:first a[href^="javascript:hideOtherLanguage"]').each(function () {
                    var matches = $(this).attr('href').match(/^(?:javascript:hideOtherLanguage\()(\d+)\)(.*)$/);
                    if (matches) {
                        var content = $('.form-group.abancart.content textarea[id*=content_' + matches[1] + ']');
                        if (typeof tinyMCE !== "undefined" && content.length > 0 && content.attr('id') && typeof tinyMCE.get(content.attr('id')) !== "undefined" && tinyMCE.get(content.attr('id'))) {
                            tinyMCE.get(content.attr('id')).setContent('');
                            tinyMCE.triggerSave();
                        }
                    }
                });
                ets_ab_fn.previewLanguage();
            }
        }
    },
    previewLanguage: function () {
        var _ct = $('#ets_abancart_campaign_type').length > 0 ? $('#ets_abancart_campaign_type').data('type') : '';

        var linkCountDownCss = '<link rel="stylesheet" href="' + ETS_AC_FULL_BASE_URL + 'modules/ets_abandonedcart/views/css/countdown.css' + '" />';
        // Email template.
        if ($('textarea[name=email_content_' + id_language + ']').length > 0) {
            if (!$('.ets_abancart_form_preview .ets_abancart_preview .iframe-email-content-preview').length) {
                var iframeEl = $('<iframe class="iframe-email-content-preview" onload="etsAcResizeIframe(this)"></iframe>');
                $('.ets_abancart_form_preview .ets_abancart_preview').append(iframeEl);
            } else {
                var iframeEl = $('.ets_abancart_form_preview .ets_abancart_preview .iframe-email-content-preview');
            }

            var iFrameEmail = iframeEl[0].contentDocument || iframeEl[0].contentWindow.document;
            iFrameEmail.write(linkCountDownCss + ets_ab_fn.doShortCode($('textarea[name=email_content_' + id_language + ']').val(), _ct));
            iFrameEmail.close();
        }

        // Content all.
        if ($('textarea[name=content_' + id_language + '], textarea[name=content], input[name=content_' + id_language + ']').length > 0) {
            if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderPopup' || ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderBar')
                $('.ets_abancart_form_group_right .ets_abancart_preview').html(linkCountDownCss + ets_ab_fn.doShortCode($('textarea[name=content_' + id_language + '], input[name=content_' + id_language + ']').val(), _ct));
            else {
                if (!$('.ets_abancart_preview_content_view .ets_abancart_preview .iframe-email-content-preview').length) {
                    var iframeEl = $('<iframe class="iframe-email-content-preview" onload="etsAcResizeIframe(this)"></iframe>');
                    $('.ets_abancart_preview_content_view .ets_abancart_preview').append(iframeEl);
                } else {
                    var iframeEl = $('.ets_abancart_preview_content_view .ets_abancart_preview .iframe-email-content-preview');
                }

                var body = linkCountDownCss + ets_ab_fn.doShortCode($('textarea[name=content_' + id_language + '], textarea[name=content], input[name=content_' + id_language + ']').val(), _ct);

                var iFrameEmail = iframeEl[0].contentDocument || iframeEl[0].contentWindow.document;
                iFrameEmail.write(body);
                iFrameEmail.close();

                // Cart:
                if ($('.ets_abancart_tab_item[data-tab=confirm_information].active').length > 0 && $('.abancart.ets_abancart_display_confirm.active').length > 0) {
                    if ($('.ets_abancart_display_confirm .content-confirm .iframe-confirm').length < 1) {
                        var iframe = $('<iframe class="iframe-confirm" onload="etsAcResizeIframe(this)"></iframe>');
                        $('.ets_abancart_display_confirm .content-confirm').append(iframe);
                    } else {
                        var iframe = $('.ets_abancart_display_confirm .content-confirm .iframe-confirm');
                    }
                    var iFrameConfirm = iframe[0].contentDocument || iframe[0].contentWindow.document;
                    iFrameConfirm.write(body);
                    iFrameConfirm.close();
                }
            }
        }

        // Popup content.
        if ($('input[name=title_' + id_language + ']').length > 0) {
            $('.ets_abancart_preview_title').html($('input[name=title_' + id_language + ']').val());
        }

        // Leave website.
        if ($('textarea[name=ETS_ABANCART_CONTENT_' + id_language + ']').length > 0) {
            //$('.ets_abancart_form_preview .ets_abancart_preview').html(ets_ab_fn.doShortCode($('textarea[name=ETS_ABANCART_CONTENT_' + id_language + ']').val(), _ct));

            if (!$('.ets_abancart_form_preview .ets_abancart_preview .iframe-leaving-content-preview').length) {
                var iframeEl = $('<iframe class="iframe-leaving-content-preview" onload="etsAcResizeIframe(this)"></iframe>');
                $('.ets_abancart_form_preview .ets_abancart_preview').append(iframeEl);
            } else {
                var iframeEl = $('.ets_abancart_form_preview .ets_abancart_preview .iframe-leaving-content-preview');
            }
            var iFrameEmail = iframeEl[0].contentDocument || iframeEl[0].contentWindow.document;
            iFrameEmail.write(linkCountDownCss + ets_ab_fn.doShortCode($('textarea[name="ETS_ABANCART_CONTENT_' + id_language + '"]').val(), _ct));
            iFrameEmail.close();
            if ($('.ets_abancart_form_preview .ets_abancart_preview .iframe-leaving-content-preview').length) {
                etsAcResizeIframe($('.ets_abancart_form_preview .ets_abancart_preview .iframe-leaving-content-preview')[0]);
            }
        }

        if ($('select[name="vertical_align"]').length) {
            $('select[name="vertical_align"]').change();
        } else if ($('select[name="ETS_ABANCART_VERTICLE_ALIGN"]').length) {
            $('select[name="ETS_ABANCART_VERTICLE_ALIGN"]').change();
        }

        $('input.range:not(.for-target-name)').change();
        // Popup or Highlight Bar.
        ets_ab_fn.countdown();
        ets_ab_fn.countdown2();
    },
    insertAtCaret: function (areaId, text) {
        var txtarea = document.getElementById(areaId);
        if (!txtarea) {
            return;
        }
        var scrollPos = txtarea.scrollTop;
        var strPos = 0;
        var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false));
        if (br == "ie") {
            txtarea.focus();
            if (document.selection && document.selection.createRange) {
                var range = document.selection.createRange();
            } else {
                var range = document.selection.createRange;
            }

            range.moveStart('character', -txtarea.value.length);
            strPos = range.text.length;
        } else if (br == "ff") {
            strPos = txtarea.selectionStart;
        }
        var front = (txtarea.value).substring(0, strPos);
        var back = (txtarea.value).substring(strPos, txtarea.value.length);
        txtarea.value = front + text + back;
        strPos = strPos + text.length;
        if (br == "ie") {
            txtarea.focus();
            var ieRange = document.selection.createRange();
            ieRange.moveStart('character', -txtarea.value.length);
            ieRange.moveStart('character', strPos);
            ieRange.moveEnd('character', 0);
            ieRange.select();
        } else if (br == "ff") {
            txtarea.selectionStart = strPos;
            txtarea.selectionEnd = strPos;
            txtarea.focus();
        }
        txtarea.scrollTop = scrollPos;
    },
    prevNext: function () {
        var _continue = $('button[name*=continue]'),
            _lastEl = $('.ets_abancart_tab_item:last').hasClass('active');

        if (_lastEl) {
            _continue
                .addClass('finish')
                .html(_continue.parents('form[id^=ets_abancart_cart]').length > 0 ? ets_abancart_btn_sendmail_name : ets_abancart_btn_finish_name);
        } else {
            _continue
                .removeClass('finish')
                .html(ets_abancart_btn_continue_name + ' <i class="icon-long-arrow-right"></i>');
        }
        //_continue.prop('disabled', _lastEl && (($('#title_' + default_language).length > 0 && !$('#title_' + default_language).val()) || ($('#content_' + default_language).length > 0 && !$('#content_' + default_language).val())));
        var _prev = $('button[name*=back]'),
            _firstEl = $('.ets_abancart_tab_item:first').hasClass('active');
        if (_firstEl) {
            _prev
                .prop('disabled', true)
                .find('i').hide();
        } else {
            _prev
                .prop('disabled', false)
                .find('i').show();
        }
    },
    countdown: function () {
        var clock = $('.ets_abancart_count_down_clock');
        if (!clock.length) {
            clock = $('.ets_abancart_preview iframe').contents().find('.ets_abancart_count_down_clock');
        }
        var style = clock.attr('data-style') || '';
        if (clock.length) {
            var timer = clock.data('date') ? parseInt(clock.data('date')) : 0;
            if (timer > 0) {
                clock.countdown(parseInt(clock.data('date'))).on('update.countdown', function (event) {
                    $(this).html(event.strftime(''
                        + '<span class="ets_abancart_countdown weeks" style="' + style + '"><span style="' + style + '">%-w</span> week%!w </span>'
                        + '<span class="ets_abancart_countdown days" style="' + style + '"><span style="' + style + '">%-d</span> day%!d </span>'
                        + '<span class="ets_abancart_countdown hours" style="' + style + '"><span style="' + style + '">%H</span> hr </span>'
                        + '<span class="ets_abancart_countdown minutes" style="' + style + '"><span style="' + style + '">%M</span> min </span>'
                        + '<span class="ets_abancart_countdown seconds" style="' + style + '"><span style="' + style + '">%S</span> sec </span>'));
                });
            }
        }
    },
    countdown2: function () {
        var clock = $('.ets_ac_evt_countdown2');
        if (!clock.length) {
            clock = $('.ets_abancart_preview iframe').contents().find('.ets_ac_evt_countdown2');
        }
        var style = clock.attr('data-style') || '';
        if (clock.length) {
            var timer = clock.data('date') ? parseInt(clock.data('date')) : 0;
            if (timer > 0) {
                clock.countdown(parseInt(clock.data('date'))).on('update.countdown', function (event) {
                    $(this).html(event.strftime(''
                        + (event.offset.weeks > 0 ? '<span class="ets_ac_countdown2 weeks" style="' + style + '"><span style="' + style + '">%-w</span> week%!w </span>' : '')
                        + (event.offset.days > 0 ? '<span class="ets_ac_countdown2 days" style="' + style + '"><span style="' + style + '">%-d</span> day%!d </span>' : '')
                        + '<span class="ets_ac_countdown2 hours" style="' + style + '"><span style="' + style + '">%H</span> hr </span>'
                        + '<span class="ets_ac_countdown2 minutes" style="' + style + '"><span style="' + style + '">%M</span> min </span>'
                        + '<span class="ets_ac_countdown2 seconds" style="' + style + '"><span style="' + style + '">%S</span> sec </span>'));
                });
            }
        }
    },
    bindMailData: function (el) {
        var _el = el || $('input[name=ETS_ABANCART_MAIL_SERVICE]').val(),
            _opts = $('.form-group.mail_service.' + _el + ':visible')
        ;
        if (_opts.length > 0) {
            _opts.each(function () {
                var item = $(this).find('input, select'),
                    id = item.attr('id'),
                    type = _el.toUpperCase(),
                    matches = id.match(/^(ETS_ABANCART_.*?)_(GMAIL|YAHOOMAIL|HOTMAIL|SENDGRID|SENDINBLUE|MAILJET|CUSTOM)$/i)
                ;
                if (matches)
                    id = matches[1];

                // Set Fields Data.
                var attrVal = id + '_' + type,
                    setVal = eval((attrVal))
                ;
                if (!id.match(/^(ETS_ABANCART_.*?)_(API_KEY|SECRET_KEY)$/i) && (setVal.trim() === '' && !id.match(/^(ETS_ABANCART_.*?)_(USER|PASSWD)$/i) || id.match(/^(ETS_ABANCART_.*?)_(SMTP_ENCRYPTION)$/i) && setVal.trim() === 'off')) {
                    try {
                        setVal = eval((attrVal + '_DEFAULT'));
                    } catch (e) {
                        setVal = '';
                    }
                }
                item.attr({
                    id: attrVal,
                    name: attrVal
                }).val(setVal);
                if (!id.match(/^(ETS_ABANCART_.*?)_(API_KEY|SECRET_KEY)$/i) && !id.match(/^(ETS_ABANCART_.*?)_(SMTP_PORT|SMTP_ENCRYPTION)$/i)) {
                    var placeholderVal = '';
                    try {
                        placeholderVal = eval((attrVal + '_PLACEHOLDER'));
                    } catch (e) {
                        placeholderVal = '';
                    }
                    item.attr('placeholder', placeholderVal);
                }
                $('.ets-ab-config-mail').hide();
                $('.ets-ab-config-mail.' + _el.toLowerCase()).show();
            });
        }
    },
    mailService: function (option) {
        var option = option || $('input[name=ETS_ABANCART_MAIL_SERVICE]:checked');
        $('.form-group.mail_service').hide();
        $('.form-group.mail_service.' + option.val()).show();
        ets_ab_fn.bindMailData(option.val());
    },
    templateType: function (element) {

        var element = element || $('input[name=template_type]:checked');
        if (element.length > 0 && element.val() !== 'email') {
            $('.ets_abancart_short_code.group_payment,.ets_abancart_short_code.group_button').hide();
        } else {
            $('.ets_abancart_short_code.group_payment,.ets_abancart_short_code.group_button').show();
        }
        if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderLeave') {
            $('.ets_abancart_short_code.checkout_button').show();
            $('.ets_abancart_short_code.lead_form').show();
            $('.ets_abancart_short_code.custom_button').show();
        }
        if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderBrowser') {
            $('.ets_abancart_short_code.lead_form').show();
            $('.ets_abancart_short_code.custom_button').show();
        }
        if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACEmailTemplate') {
            $('.ets_abancart_short_code.shop_button').show();
            $('.ets_abancart_short_code.lead_form').show();
            $('.ets_abancart_short_code.custom_button').show();
        }
    },
    drawColor: function () {
        var _pv = $('.ets_abancart_preview'),
            _color = $('input[name=text_color]'), _bg = $('input[name=background_color]');
        if (_color.length > 0 && _pv.length > 0) {
            _pv.css('color', _color.attr('value'));
        }
        if (_color.length > 0 && _pv.length > 0) {
            _pv.css('background-color', _bg.attr('value'));
        }
    },
    pvIconBrowser: function (img) {
        var _img = img || $('[id$=-images-thumbnails] img'),
            _pbx = $('.ets_abancart_preview_info.browser'),
            _im = _pbx.find('img.ets_abancart_image')
        ;
        if (_pbx.length > 0 && _img.length > 0) {
            if (_im.length > 0) {
                _im.attr('src', _img.attr('src'));
            } else {
                _pbx.find('.ets_abancart_preview_content_view').prepend('<img class="ets_abancart_image" src="' + _img.attr('src') + '"/>');
            }
        }
    },
    validateForm: function (doms) {
        var DOMs = doms || $('.form-group.abancart.active:visible'),
            _errors = ''
        ;
        if (DOMs.length > 0) {
            if (typeof tinyMCE !== "undefined")
                tinyMCE.triggerSave();
            DOMs.each(function () {
                var
                    titleEl = $(this).find('label').text(),
                    parentEl = $(this),
                    validate_msg = titleEl + ' ' + (typeof ets_abancart_validate !== "undefined" ? ets_abancart_validate : 'is validate') + '<br/>',
                    required_msg = titleEl + ' ' + (typeof ets_abancart_required !== "undefined" ? ets_abancart_required : 'is validate') + '<br/>'
                ;
                var _input = parentEl.find('input, select, textarea');
                if (_input.length > 0) {
                    _input.each(function () {
                        if (_input.parents('.translatable-field', parentEl).length > 0) {
                            _input = $(_input.prop('tagName').toLowerCase() + '[name=' + _input[0].name.replace(/^([a-zA-Z-0-9]+\_)(\d+)$/, '$1' + default_language) + ']', parentEl);
                            return false;
                        }
                    });
                }
                if (_input.length > 0) {
                    var $value = _input.val().trim();
                    if ($value !== '') {
                        if (parentEl.hasClass('isUnsignedFloat')) {
                            if (!ets_ab_validates.isUnsignedFloat($value) || _input[0].name === 'reduction_amount' && parseFloat($value) === 0) {
                                if (_input.attr('name') == 'reduction_amount' && $('input[name="apply_discount"]:checked').val() != 'amount') {
                                    //Do nothing
                                } else
                                    _errors += validate_msg;
                            }
                        } else if (parentEl.hasClass('isUnsignedInt')) {
                            if (!ets_ab_validates.isUnsignedInt($value)) {
                                _errors += validate_msg;
                            }
                        } else if (parentEl.hasClass('isCleanHtml')) {
                            if (!ets_ab_validates.isCleanHtml($value)) {
                                _errors += validate_msg;
                            }
                        } else if (parentEl.hasClass('isMailSubject')) {
                            if (!ets_ab_validates.isMailSubject($value)) {
                                _errors += validate_msg;
                            }
                        } else if (parentEl.hasClass('isPercentage')) {
                            if (!ets_ab_validates.isPercentage($value)) {
                                _errors += validate_msg;
                            }
                        } else if (parentEl.hasClass('isEmail')) {
                            if (!ets_ab_validates.isEmail($value)) {
                                _errors += validate_msg;
                            }
                        }
                    } else if ($(this).hasClass('required')) {
                        if (_input.attr('name') == 'discount_code' && $('input[name="discount_option"]:checked').val() != 'fixed') {
                            //Do nothing
                        } else {
                            _errors += required_msg;
                        }
                    }
                } else if (parentEl.hasClass('isSelectedTemp')) {
                    if (parentEl.find('.ets_abancart_template_li.active').length <= 0) {
                        _errors += (typeof ets_abancart_temp_required !== "undefined" ? ets_abancart_temp_required : 'Email template is required');
                    }
                }
            });
        }
        if (!_errors && DOMs.find('#day, #hour, #minute, #second').length > 0) {
            var day = parseFloat(DOMs.find('#day').val()),
                hour = parseFloat(DOMs.find('#hour').val()),
                minute = parseFloat(DOMs.find('#minute').val()),
                second = parseInt(DOMs.find('#second').val())
            ;
            /*if (!day && !hour && !minute && !second) {
                if ($('.ets_abancart_email_timing_option').length && ['1', '2', '6'].indexOf($('input[name=email_timing_option]').val()) == -1) {
                    //Do nothing
                } else
                    _errors += ets_abancart_frequency_configured;
            }*/
        }
        if (_errors !== '') {
            showErrorMessage(_errors);
            return false;
        }
        return true;
    },
    selectMultiple: function (select) {
        var DOMs = select || $('select[multiple=multiple]');
        if (DOMs.length > 0) {
            DOMs.each(function () {
                if ($(this).attr('multiple').trim() === 'multiple') {
                    if ($(this).find('option[value=0]:selected').length > 0) {
                        $(this).find('option:not(:selected)').prop('selected', true);
                    } else if ($(this).find('option:selected').length === $(this).find('option[value!=0]').length) {
                        $(this).find('option[value=0]').prop('selected', true);
                    }
                }
            });
        }
    },
    hasPlacedOrder: function (option) {
        var input = option || $('#has_placed_orders');
        if (input.length > 0 && input.val().trim() == 'yes') {
            $('.form-group.min_total_order, .form-group.last_order_from, .form-group.purchased_product, .form-group.not_purchased_product').show();
        } else
            $('.form-group.min_total_order, .form-group.last_order_from, .form-group.purchased_product, .form-group.not_purchased_product').hide();
    },
    getParameterByName: function (name, url) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    },
    saveAndSend: function (El) {
        var choice = El || $('input[id^=enabled]:checked').val(),//send_email_now
            saveAndSend = $('button.ets_ac_btn_step_save')
        ;
        if (parseInt(choice) === 0) {
            saveAndSend.html(saveAndSend.data('no-send-mail'));
        } else
            saveAndSend.html(saveAndSend.data('send-mail'));
    }
};
var ets_ab_cr = {
    groupTabs: function (el) {
        var _el = el || $('.ets_abancart_cronjob_tab_item').first();
        if (_el.length <= 0 || _el.hasClass('active'))
            return false;

        $('.ets_abancart_cronjob_tab_item.active, .form-group.ets_abancart_cronjob.active').removeClass('active');
        _el.addClass('active');
        $('.form-group.ets_abancart_cronjob[data-tab-id=' + _el.data('tab') + ']').addClass('active');
    }
};
var ets_ab_chart = {
    timeSeriesOption: function (el) {
        var _el = el || $('.ets_abancart_time_series'),
            _pt = _el.parents('.ets_abancart_chart'),
            _val = _el.hasClass('dashboard') ? _el.find('.ets_abancart_time_series_li.active').attr('data-value') : _el.val()
        ;
        if (_val !== 'time_range') {
            _pt.find('.ets_abancart_form_group').hide();
        } else {
            _pt.find('.ets_abancart_form_group').show();
        }
    },
    addLineChart: function (cavans, json) {
        return new Chart(cavans, {
            type: 'line',
            data: {
                datasets: json.datasets,
                labels: json.labels,
            },
            spanGaps: true,
            options: {
                responsive: true,
                title: {
                    text: json.title,
                    position: 'top',
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: json.labelX
                        },
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: json.labelY
                        },
                        min: json.minY,
                        max: json.maxY
                    }]
                },
                legend: {
                    fullWidth: true,
                    position: 'bottom',
                },
                layout: {
                    padding: {
                        left: 20,
                        top: 20,
                        bottom: 20
                    }
                },
                tooltips: {
                    mode: 'point',
                    intersect: true,
                }
            }
        });
    },
    updateLineChart: function (json) {
        if (!ETS_ABANCART_LINE_CHART) {
            ETS_ABANCART_LINE_CHART = ets_ab_chart.addLineChart($('#ets_abancart_chart1'), {
                title: json.title,
                labels: json.dataAxesX,
                datasets: json.datasets,
                labelX: json.axesX,
                labelY: json.axesY,
                minY: json.minY,
                maxY: json.maxY,
            });
        } else {
            ETS_ABANCART_LINE_CHART.data.labels = json.dataAxesX || [];
            ETS_ABANCART_LINE_CHART.data.datasets = json.datasets || [];
            ETS_ABANCART_LINE_CHART.options.title.text = json.title || [];
            ETS_ABANCART_LINE_CHART.options.scales.xAxes = [{
                scaleLabel: {
                    display: true,
                    labelString: json.axesX || [],
                },
            }];
            ETS_ABANCART_LINE_CHART.options.scales.yAxes = [{
                min: json.minY,
                max: json.maxY,
                ticks: {
                    beginAtZero: true,
                    callback: function (value) {
                        if (value % 1 === 0) {
                            return value;
                        }
                    }
                },
                scaleLabel: {
                    display: true,
                    labelString: json.axesY || [],
                },
            }];
            ETS_ABANCART_LINE_CHART.update();
        }
    },
    updateTopStats: function (top_stats) {
        if (top_stats && $('.ets_abancart_stats').length > 0) {
            Object.keys(top_stats).forEach(function (key) {
                $('.ets_abancart_item.' + top_stats[key].class + ' .ets_abancart_stats_price').html(top_stats[key].label.replace('[1]', '<span class="ets_abancart_label">').replace('[/1]', '</span>'));
            });
        }
    },
    updateStats: function (stats) {
        if (stats) {
            var stats_campaigns = $('.ets_abancart_stats_campaigns');
            if (stats_campaigns.length > 0) {
                stats_campaigns.html(stats);
            }
        }
    },
    chartAjax: function (btn) {
        if ($('.ets_abancart_chart').length > 0 && !btn.hasClass('active') > 0 && ETS_ABANCART_AJAX_LINK) {
            btn.addClass('active');
            if (btn.hasClass('ets_ac_ft_email'))
                btn.closest('.ets_abancart_charts_col').addClass('js-chart-loading');

            var sChart = btn.parents('.ets_abancart_chart'),
                select_time_series = sChart.find('.ets_abancart_time_series').length > 0 ? sChart.find('.ets_abancart_time_series').val() : ($('.ets_abancart_time_series_li.active').length > 0 ? $('.ets_abancart_time_series_li.active').attr('data-value') : 'this_year'),
                select_id_campaign = ets_ab_fn.campaign_filter || ets_ab_fn.getParameterByName('id_ets_abancart_campaign', window.location.href),
                reminderFilter = ets_ab_fn.reminder_filter
            ;

            $.ajax({
                url: ETS_AC_ADMIN_CONTROLLER === 'AdminEtsACDashboard' ? '' : ETS_AC_LINK_REMINDER_ADMIN,
                type: 'POST',
                dataType: 'json',
                data: 'ajax=1&action=initChart&id_campaign=' + (select_id_campaign ? select_id_campaign : '') + '&reminder_filter=' + reminderFilter + '&time_series=' + select_time_series + (select_time_series !== 'time_range' ? '' : '&from_time=' + (sChart.find('input[name=from_time]').length > 0 ? sChart.find('input[name=from_time]').val() : $('.dashboard input[name=from_time]').val()) + '&to_time=' + (sChart.find('input[name=to_time]').length > 0 ? sChart.find('input[name=to_time]').val() : $('.dashboard input[name=to_time]').val())) + '&chartType=' + (sChart.hasClass('chart1') ? 'line_chart' : 'stats'),
                success: function (json) {
                    btn.removeClass('active');
                    if (btn.hasClass('ets_ac_ft_email'))
                        btn.closest('.ets_abancart_charts_col').removeClass('js-chart-loading');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else if (sChart.hasClass('chart1')) {
                            ets_ab_chart.updateLineChart(json.line_chart);
                        } else if (sChart.hasClass('chart3')) {
                            ets_ab_chart.updateStats(json.stats);
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                    if (btn.hasClass('ets_ac_ft_email'))
                        btn.closest('.ets_abancart_charts_col').removeClass('js-chart-loading');
                }
            });
        }
    },
    statsDashboard: function (btn) {
        var dashboard = $('.ets_abancart_dashboard');
        if ($('.ets_abancart_chart').length > 0 && !dashboard.hasClass('ets-rv-stats-loading') && ETS_ABANCART_AJAX_LINK) {
            dashboard.addClass('ets-rv-stats-loading');
            var sChart = btn.parents('.ets_abancart_heading'),
                select_time_series = btn.find('.ets_abancart_time_series_li.active').attr('data-value'),
                reminderFilter = $('select[name=ets_ac_ft_all]').val()
            ;
            $('.bootstrap .alert').remove();
            $.ajax({
                url: ETS_AC_ADMIN_CONTROLLER === 'AdminEtsACDashboard' ? '' : ETS_AC_LINK_REMINDER_ADMIN,
                type: 'POST',
                dataType: 'json',
                data: 'ajax=1&action=initDashboard&reminder_filter=' + reminderFilter + '&time_series=' + select_time_series + (select_time_series !== 'time_range' ? '' : '&from_time=' + sChart.find('input[name=from_time]').val() + '&to_time=' + sChart.find('input[name=to_time]').val()),
                success: function (json) {
                    dashboard.removeClass('ets-rv-stats-loading');
                    if (json) {
                        if (json.errors)
                            showErrorMessage(json.errors);
                        else {
                            if (json.top_stats)
                                ets_ab_chart.updateTopStats(json.top_stats);
                            if (json.line_chart)
                                ets_ab_chart.updateLineChart(json.line_chart);
                            if (json.stats)
                                ets_ab_chart.updateStats(json.stats);
                        }
                    }
                },
                error: function () {
                    dashboard.removeClass('ets-rv-stats-loading');
                }
            });
        }
    }
};
var ets_ab_popup = {
    offPopupTracking: function () {
        $('.ets_abancart_overload.view_tracking.active').removeClass('view_tracking active');
    },
    offDisplayPopup: function () {
        $('.ets_abancart_overload.active').removeClass('active').parents('body').removeClass('ets_open_modal');
        $('.ets_abancart_overload.reminder_log').removeClass('reminder_log');
        $('#mColorPicker:visible, #mColorPickerBg:visible').hide();
        $('td.ets_abancart_send_date.active').removeClass('active');
        $('.ets_abancart_overload.view_tracking').removeClass('view_tracking');
    },
    offAlert: function () {
        if ($('.alert.alert-success').length > 0) {
            setTimeout(function () {
                $('.alert.alert-success').remove();
            }, 3000);
        }
    }
};
var ets_ab_file = {
    clearInputFile: function (el) {
        var _el = el || $('input[type=file]'),
            _dummy = _el.next()
        ;
        _el.val('');
        if (_dummy.hasClass('dummyfile')) {
            _dummy.find('input[type=text]').val('');
        }
    },
    readURL: function (input) {
        var images = $(input).parents('.form-group-file').eq(0),
            id = $(input).attr('name')
        ;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                if (images.find('#' + id + '-images-thumbnails').length <= 0) {
                    images.find('.form-group').before('<div class="form-group"><div class="col-lg-12" id="' + id + '-images-thumbnails"><div><img src="#" alt="" class="imgm img-thumbnail"><p><a class="btn btn-default base64encode" href="#"><i class="icon-trash"></i> ' + ETS_ABANCART_DELETE_TITLE + '</a></p></div></div></div>');
                }
                var _thumbnail = $('#' + id + '-images-thumbnails .img-thumbnail');
                _thumbnail.attr({
                    src: e.target.result,
                    alt: input.files[0].name,
                    width: '180'
                });
                ets_ab_fn.pvIconBrowser(_thumbnail);
            };
            reader.readAsDataURL(input.files[0]);
        }
    },
};
var ets_ab_actions = [
    'saveReminder',
    'saveCart',
    'submitAddets_abancart_cart',
    'saveEmail_template',
    'saveEmail',
    'savePopup',
    'saveBar',
    'saveBrowser',
    'saveCustomer',
    'submitSendTestMail',
];
var ets_ab_validates = {
    isUnsignedInt: function ($value) {
        return /^\d+$/i.test($value) && $value < 4294967296 && parseInt($value) >= 0;
    },
    isUnsignedFloat: function ($float) {
        return /^\d+(\.\d+)?$/i.test($float) && parseFloat($float) >= 0;
    },
    isCleanHtml: function ($html) {
        //ETS_AB_HTML_PURIFIER
        var $events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        $events += '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        $events += '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        $events += '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        $events += '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        $events += '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        $events += '|onselectstart|onstart|onstop';

        var regExp = new RegExp('/(' + $events + ')[\s]*=/ims');
        if (/<[\s]*script/ims.test($html) || regExp.test($html) || /.*script\:/ims.test($html)) {
            return false;
        }

        if (!ETS_AB_HTML_PURIFIER && /<[s]*(i?frame|form|input|embed|object)/ims.test($html)) {
            return false;
        }

        return true;
    },
    isPercentage: function ($value) {
        return ets_ab_validates.isUnsignedFloat($value) && parseFloat($value) >= 0 && parseFloat($value) <= 100;
    },
    isColor: function ($color) {
        return /^(#[0-9a-fA-F]{6}|[a-zA-Z0-9-]*)$/.test($color);
    },
    isMailSubject: function ($mail_subject) {
        //var pattern = ets_ab_validates.cleanNonUnicodeSupport('/^[^<>]*$/u');
        //var regExp = new RegExp(pattern);
        return /^[^<>]*$/u.test($mail_subject);
    },
    cleanNonUnicodeSupport: function ($pattern) {
        return $pattern.replace(/\\\[px]\{[a-z]{1,2}\}|(\/[a-z]*)u([a-z]*)$/i, '$1$2');
    },
    isEmail: function ($email) {

        return $email.trim() !== '' && /^[a-z\p{L}0-9!#$%&'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+(?:[.]?[_a-z\p{L}0-9-])*\.[a-z\p{L}0-9]+$/ui.test($email);
    }
};
var ets_ab_autocomplete = {
    product: function (input_search, input_hidden, result_class, list, input_connect) {
        if ($(input_search).length > 0 && $(input_connect).length > 0 && ETS_ABANCART_CAMPAIGN_URL) {
            $(input_search).autocomplete(ETS_ABANCART_CAMPAIGN_URL + '&ajax=1&action=searchProduct&time=' + new Date().getTime(), {
                resultsClass: result_class,
                appendTo: result_class,
                delay: 100,
                minChars: 1,
                autoFill: true,
                max: 20,
                matchContains: true,
                mustMatch: true,
                scroll: false,
                cacheLength: 0,
                multipleSeparator: '||',
                extraParams: {
                    excludeIds: $(input_hidden).val(),
                },
                formatItem: function (item) {
                    return '<span data-id="' + item[0] + '"><img src="' + item[3] + '" title="' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '" width="64"/>' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '</span>';
                }
            }).result(function (event, item) {
                if (item == null)
                    return false;

                // Check not or purchased:
                if ($(input_connect).val().split(',').indexOf(item[0]) !== -1) {
                    $(input_connect).val(function () {
                        return ets_ab_autocomplete.removeIds($(this).val().split(','), item[0]);
                    });
                    $('.ets_abancart_product_item[data-id=' + item[0] + ']').remove();
                }
                // Add list:
                if ($(input_hidden).val().trim() === '') {
                    $(input_hidden).val(item[0]);
                    ets_ab_autocomplete.appendList(list, item, input_hidden);
                } else {
                    if ($(input_hidden).val().split(',').indexOf(item[0]) === -1) {
                        $(input_hidden).val(function () {
                            return $(this).val() + ',' + item[0];
                        });
                        ets_ab_autocomplete.appendList(list, item, input_hidden);
                    } else
                        return false;
                }
                ets_ab_autocomplete.refresh(input_search, input_hidden, result_class, list, input_connect, true);
            });
        }
    },
    appendList: function (list, item, input_hidden) {
        if (item && $(list).length > 0) {
            $(list)
                .addClass('active')
                .append('<li class="ets_abancart_product_item" data-id="' + item[0] + '" ref="' + input_hidden + '"><a href="' + item[4] + '"><img src="' + item[3] + '" title="' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '" width="64"/>' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '</a><span class="remove_ctm"><i class="icon-trash"></i></span></li>');
        }
    },
    destroy: function (input_search, destroy) {
        var _destroy = destroy || false;
        if ($(input_search).length > 0) {
            $(input_search).val('');
            if (_destroy) {
                $(input_search).unautocomplete();
            }
        }
    },
    refresh: function (input_search, input_hidden, result_class, list, input_connect, destroy) {
        ets_ab_autocomplete.destroy(input_search, destroy);
        ets_ab_autocomplete.product(input_search, input_hidden, result_class, list, input_connect);
    },
    removeIds: function (parent, element) {
        var ax = -1;
        if ((ax = parent.indexOf(element)) !== -1)
            parent.splice(ax, 1);
        return parent;
    },
};

function setHeight() {
    var _height = $('.aband_group_header_fixed').height();
    $('.aban_menu_height').height(_height);
}

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    ets_ab_fn.reminder_filter = $('select[name=ets_ac_ft_all] option[selected="selected"]').val() || $('select[name=ets_ac_ft_all] option:first').val();
    $('select[name=ets_ac_ft_all]').val(ets_ab_fn.reminder_filter);

    ets_ab_fn.init();
    ets_ab_popup.offAlert();
    setHeight();
    $(window).load(function () {
        setHeight();
    });
    $(window).resize(function () {
        setHeight();
    });
    $('body .ets_abancart_wrapper table .filter.row_hover').find('input').attr('autocomplete', 'off');

    etsAcChangeOptionCustomerReminder($('input[name=email_timing_option]:checked').length > 0 ? $('input[name=email_timing_option]:checked') : $('input[name=email_timing_option]').first(), false);

    $(document).on('click', '.ets_ac_mail_log_form.active .panel-footer a.btn', function (e) {
        e.preventDefault();
        $('.ets_abancart_forms .ets-abancart-overload.active').removeClass('active');
    });
    $(document).on('click', '.table.ets_abancart_mail_log a.btn', function (e) {
        e.preventDefault();
        var btn = $(this);
        if (!btn.hasClass('active')) {
            btn.addClass('active');
            $.ajax({
                url: btn.attr('href'),
                data: 'ajax=1&action=renderView',
                type: 'GET',
                dataType: 'json',
                success: function (json) {
                    if (json) {
                        btn.removeClass('active');
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            if (json.html) {
                                if ($('.ets-abancart-overload.ets_ac_mail_log_form').length < 1) {
                                    $('.ets_abancart_forms').append(json.html);
                                } else {
                                    $('.ets-abancart-overload.ets_ac_mail_log_form').replaceWith(json.html);
                                }
                                $('.ets-abancart-overload.ets_ac_mail_log_form').addClass('active');
                            }
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });
    $(document).on('click', '#table-ets_abancart_email_queue a[href*=viewets_abancart_email_queue]', function (e) {
        e.preventDefault();
        var btn = $(this), wrapper = $('body');
        if (!wrapper.hasClass('loading')) {
            wrapper.addClass('loading');
            $.ajax({
                url: btn.attr('href'),
                data: 'ajax=1&action=renderView',
                type: 'GET',
                dataType: 'json',
                success: function (json) {
                    if (json) {
                        wrapper.removeClass('loading');
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            if (json.html) {
                                if ($('.ets-abancart-overload.ets_ac_mail_queue_form').length < 1) {
                                    $('.ets_abancart_forms').append(json.html);
                                } else {
                                    $('.ets-abancart-overload.ets_ac_mail_queue_form').replaceWith(json.html);
                                }
                                $('.ets-abancart-overload.ets_ac_mail_queue_form').addClass('active');
                            }
                        }
                    }
                },
                error: function () {
                    wrapper.removeClass('loading');
                }
            });
        }
    });

    $(document).on('click', '.ets_ab_btn_change_status', function (e) {
        e.preventDefault();
        var btn = $(this);
        if (!btn.hasClass('active')) {
            btn.addClass('active');
            $.ajax({
                url: btn.attr('href'),
                data: {ajax: 1},
                type: 'POST',
                dataType: 'json',
                success: function (json) {
                    if (json) {
                        if (json.errors)
                            showErrorMessage(json.errors);
                        else {
                            if (json.msg)
                                showSuccessMessage(json.msg);
                            if (json.list) {
                                $('.ets_abancart_reminder').html(json.list);
                            }
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    })

    $(document).on('click', '.ets_abancart_secure_token span.input-group-addon', function () {
        var chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz",
            random = '',
            secure_token = $('#ets_abancart_secure_token').val();
        for (var i = 1; i <= 10; ++i)
            random += chars.charAt(Math.floor(Math.random() * chars.length));

        $('#ets_abancart_secure_token').val(random);
        $('#ets_abd_cronjob_path').html($('#ets_abd_cronjob_path').html().replace('secure=' + secure_token, 'secure=' + $('#ets_abancart_secure_token').val()));
        $('#ets_abd_cronjob_link').attr('href', $('#ets_abd_cronjob_link').attr('href').replace('secure=' + secure_token, 'secure=' + $('#ets_abancart_secure_token').val()));
    });

    $(document).on('click', '.ets_abancart_enabled', function () {//send_email_now
        ets_ab_fn.saveAndSend($(this).find('input[id^=enabled]').val());//send_email_now
    });

    $(document).on('click', '.ets_abancart_product_item .remove_ctm', function () {
        var btn = $(this),
            productLi = btn.parents('li'),
            input_hidden = $(productLi.attr('ref')),
            idProduct = productLi.data('id').toString()
        ;
        if (productLi.length > 0 && input_hidden.length > 0 && idProduct !== '') {
            var ids = input_hidden.val().split(',');
            if (ids.indexOf(idProduct) !== -1) {
                input_hidden.val(ets_ab_autocomplete.removeIds(ids, idProduct));
                productLi.remove();
            }
        }
    });

    $(document).on('change', '#has_placed_orders', function () {
        ets_ab_fn.hasPlacedOrder($(this));
    });

    $(document).on('click change', 'select[id^=countries], select[id^=languages]', function () {
        ets_ab_fn.selectMultiple($(this));
    });

    $(document).on('click', 'button[name=sendTestMail]', function () {
        $('.ets_abancart_wrapper_overload:not(.active)').addClass('active');
    });
    $(document).on('click', '.sendmail_cancel', function (e) {
        $(this).parents('.ets_abancart_wrapper_overload.active').removeClass('active');
    });
    $(document).on('click', 'button[name=submitSendTestMail]', function (e) {
        e.preventDefault();
        var btn = $(this),
            form = btn.parents('form'),
            postUrl = form.attr('action')
        ;
        if (!btn.hasClass('active') && postUrl !== '' && ets_ab_fn.validateForm(form.find('.form-group'))) {
            btn.addClass('active');
            var formData = new FormData(form.get(0));
            var emailContent = $('.ets_abancart_preview').html() || '';
            if ($('.ets_abancart_preview .iframe-email-content-preview').length) {
                $('.ets_abancart_preview .iframe-email-content-preview').contents().find('.ets-ac-msg-lead-form-disabled,.ets-ac-msg-lead-form-not-found').hide();
                emailContent = $('.ets_abancart_preview .iframe-email-content-preview').contents().find('body').html();
                $('.ets_abancart_preview .iframe-email-content-preview').contents().find('.ets-ac-msg-lead-form-disabled,.ets-ac-msg-lead-form-not-found').show();
            }
            formData.append('ajax', 1);
            formData.append('action', 'sendTestMail');
            formData.append('email_content', emailContent);
            formData.append('email_subject', $('input[name^="title_"]:visible').first().val() || '');

            $.ajax({
                type: 'POST',
                url: postUrl,
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            showErrorMessage(json.errors);
                        else {
                            if (json.msg)
                                showSuccessMessage(json.msg);
                            $('.ets_abancart_wrapper_overload.active').removeClass('active');
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            })
        }
    });
    $(document).on('click', '.ets_abancart_responsive_mode a', function (e) {
        e.preventDefault();
        var mode = $(this).attr('data-respon');
        $('.ets_abancart_responsive_mode a').removeClass('active');
        $(this).addClass('active');
        $('.ets_abancart_preview_info').removeClass('desktop_mode').removeClass('tablet_mode').removeClass('mobile_mode').addClass(mode);
        $('.ets_abancart_preview_title').removeClass('desktop_mode').removeClass('tablet_mode').removeClass('mobile_mode').addClass(mode);
    });
    $(document).on('click', '.ets_abancart_cronjob_tab_item', function () {
        ets_ab_cr.groupTabs($(this));
    });
    $(document).on('click', 'input[name=template_type]', function () {
        ets_ab_fn.templateType($(this));
    });
    $(document).on('click', '.ets_abancart_time_series_li', function () {
        var ele = $(this).parents('.ets_abancart_time_series').eq(0);
        ele.find('.ets_abancart_time_series_li.active').removeClass('active');
        $(this).addClass('active');
        ets_ab_chart.timeSeriesOption(ele);
        ets_ab_chart.statsDashboard(ele);
    });
    $(document).on('change', 'select.ets_abancart_time_series', function () {
        ets_ab_chart.timeSeriesOption($(this));
        if ($(this).val() !== 'time_range') {
            ets_ab_chart.chartAjax($(this));
        }
    });
    $(document).on('change', 'input[name=ETS_ABANCART_MAIL_SERVICE]', function () {
        ets_ab_fn.mailService($(this));
    });
    $(document).on('change keyup', 'input[id^=title_]', function () {
        ets_ab_fn.prevNext();
        $('.ets_abancart_preview_title').html($(this).val());
    });
    $(document).on('click', 'input[type=checkbox].abancart_group', function () {
        ets_ab_fn.groupCheck();
    });
    $(document).on('click', '.translatable-field .dropdown-menu a', function (ev) {
        ev.preventDefault();
        var btn = $(this), id_language = id_language || default_language,
            matches = btn.attr('href').match(/^javascript:hideOtherLanguage\((\d+)\);$/);
        if (matches && matches.length && parseInt(matches[1]) > 0)
            id_language = matches[1];
        hideOtherLanguage(id_language);
        ets_ab_fn.previewLanguage();
    });
    $(document).on('click', 'button[type=submit]:not([name*=save])', function () {
        var submitFilter = $('#submitFilterButtoncart');
        if (ets_ab_actions.indexOf($(this).attr('name')) === -1) {
            $(this).addClass('active');
        }
        if (submitFilter.parents('tr.filter').length > 0) {
            var flag = 0;
            submitFilter.parents('tr.filter').find('input').each(function () {
                if ($(this).val().length > 0) {
                    flag = 1;
                }
            });
            if (flag < 1) {
                $('#submitFilterButtoncart').removeClass('active');
            } else {
                $('#submitFilterButtoncart').addClass('active');
            }
        }
    });
    $(document).on('change', 'input[name=enable_count_down_clock],input[name=ETS_ABANCART_ENABLE_COUNTDOWN_CLOCK]', function () {
        ets_ab_fn.countDownOption();
    });
    $(document).on('click', '.ets_abancart_btn_short_code', function () {
        var email_rte = $('textarea[name=email_content_' + id_language + '].rte'),
            content_rte = $('textarea[name=content_' + id_language + '].rte, textarea[name=content].rte'),
            content2_rte = $('textarea[name=ETS_ABANCART_CONTENT_' + id_language + '].rte')
        ;
        var shortCode = $(this).data('short-code');
        if (shortCode == '[countdown_clock]') {
            var date = new Date();
            date.setDate(date.getDate() + 1);
            var time = date.getFullYear() + '-' + etsAcFormatTimeNumber(date.getMonth() + 1) + '-' + etsAcFormatTimeNumber(date.getDate()) + ' ' + etsAcFormatTimeNumber(date.getHours()) + ':' + etsAcFormatTimeNumber(date.getMinutes()) + ':' + etsAcFormatTimeNumber(date.getSeconds());
            shortCode = '[countdown_clock endtime="' + time + '"]';
        }
        if (email_rte.length > 0 && typeof tinyMCE !== "undefined") {

            tinyMCE.get(email_rte.attr('id')).execCommand('mceInsertContent', false, shortCode);

        } else if (content_rte.length > 0 && typeof tinyMCE !== "undefined") {

            tinyMCE.get(content_rte.attr('id')).execCommand('mceInsertContent', false, shortCode);

        } else if ($('#content_' + id_language).length > 0) {

            ets_ab_fn.insertAtCaret('content_' + id_language, shortCode);

        } else if (content2_rte.length > 0 && typeof tinyMCE !== "undefined") {

            tinyMCE.get(content2_rte.attr('id')).execCommand('mceInsertContent', false, shortCode);

        }
        ets_ab_fn.previewLanguage();
        ets_ab_fn.prevNext();
    });
    //sua1:
    $(document).on('click', '.ets_abancart_nav_tabs .ets_abancart_tab_item', function () {
        var btnContinue = $('button.ets_ac_btn_step_continue'),
            _tab = $(this).data('tab')
        ;
        var prevTabIndex = $('.ets_abancart_nav_tabs .ets_abancart_tab_item.active').index();
        var tabIndex = $(this).index();
        var $this = $(this);
        if (prevTabIndex < tabIndex) {

            var dataStep = {validateStepForm: 1};
            for (var i = 0; i <= tabIndex; i++) {
                var dataTabItem = $('.ets_abancart_nav_tabs .ets_abancart_tab_item:nth-child(' + i + ')').attr('data-tab');
                $('.form-group.form_' + dataTabItem).find('input[type=text], input[type=hidden], select, textarea, input[type=radio]:checked, input[type=checkbox]:checked').each(function () {
                    dataStep[$(this).attr('name')] = $(this).val();
                });
            }
            dataStep['id_ets_abancart_campaign'] = $('input[name="id_ets_abancart_campaign"]').val();
            if (!btnContinue.hasClass('active')) {
                btnContinue.addClass('active');
                $.ajax({
                    url: ETS_AC_LINK_REMINDER_ADMIN,
                    type: 'POST',
                    data: dataStep,
                    dataType: 'json',
                    beforeSend: function () {
                        $('.ets_abancart_form .bootstrap')
                            .html('')
                            .removeClass('abancart_alert');
                    },
                    success: function (res) {
                        btnContinue.removeClass('active');
                        if (res) {
                            if (!res.success) {
                                $this.closest('form').before(res.message);
                                $('.ets_abancart_form .bootstrap').addClass('abancart_alert');
                                return false;
                            }
                            ets_ab_fn.groupTabs($this);
                            switch (_tab) {
                                case 'discount':
                                    ets_ab_fn.discountOpt();
                                    break;
                                case 'message':
                                    $('.form-group.abancart.form_message.preview.active:not(.hide)').addClass('hide');
                                    break;
                                case 'select_template':
                                    ets_ab_fn.displayEmailTemplate();
                                    break;
                            }

                            etsAcChangeOptionCustomerReminder($('#email_timing_option'), _tab == 'frequency' ? true : false);

                            if (_tab == 'confirm_information') {
                                $('.ets_ac_btn_step_continue').addClass('hide');
                                $('.ets_ac_btn_step_save').removeClass('hide');
                            } else {
                                $('.ets_ac_btn_step_save').addClass('hide');
                                $('.ets_ac_btn_step_continue').removeClass('hide');
                            }
                            if (_tab !== 'discount') {
                                $('.ets_ac_selected_product_group').hide();
                                $('.ets_ac_specific_product_group').hide();
                            } else {
                                $('.ets_ac_selected_product_group').show();
                                $('.ets_ac_specific_product_group').show();
                            }
                            if (_tab == 'message') {
                                etsAcSetDefaultContentPopup();
                                if ($('.ets_abancart_preview iframe').length) {
                                    etsAcResizeIframe($('.ets_abancart_preview iframe')[0]);
                                }
                            }
                        }
                    },
                    error: function () {
                        btnContinue.removeClass('active');
                    }
                });
            }

            return false;
        }

        if (_tab !== 'discount') {

            $('.ets_ac_selected_product_group').hide();
            $('.ets_ac_specific_product_group').hide();
        } else {
            $('.ets_ac_selected_product_group').show();
            $('.ets_ac_specific_product_group').show();
        }
        ets_ab_fn.groupTabs($(this));

        switch (_tab) {
            case 'discount':
                ets_ab_fn.discountOpt();
                break;
            case 'message':
                $('.form-group.abancart.form_message.preview.active:not(.hide)').addClass('hide');
                break;
            case 'select_template':
                ets_ab_fn.displayEmailTemplate();
                break;
        }

        etsAcChangeOptionCustomerReminder($('#email_timing_option'), _tab == 'frequency' ? true : false);

        if (_tab == 'confirm_information') {

            $('.ets_ac_btn_step_continue').addClass('hide');
            $('.ets_ac_btn_step_save').removeClass('hide');

            // custom:
            if (false && $('#ETS_ABANCART_LANG_DEFAULT').length > 0) {
                var form = $('#ets_abancart_cart_form'),
                    eL = $(this),
                    formFiles = form.find('input[type="file"]')
                ;
                if (form.length > 0 && !eL.hasClass('loading') && form.attr('action')) {
                    eL.addClass('loading');
                    if (typeof tinyMCE !== "undefined" && tinyMCE.editors.length > 0) {
                        tinyMCE.triggerSave();
                    }
                    var formData = new FormData(form.get(0));
                    // Safari fixed.
                    if (formFiles.length > 0) {
                        formFiles.each(function () {
                            if ($($(this).attr('id')).files.length == 0) {
                                formData.delete($(this).attr('id'));
                            }
                        });
                    }
                    formData.append('ajax', 1);
                    formData.append('preview', 1);
                    formData.append('action', 'sendMail');

                    $.ajax({
                        url: form.attr('action'),
                        type: 'post',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (json) {
                            eL.removeClass('loading');
                            if (json) {
                                if (json.errors) {
                                    form.before(json.errors);
                                    $('.ets_abancart_form .bootstrap').addClass('abancart_alert');
                                } else {
                                    if (json.preview) {
                                        $('.ets_abancart_display_confirm .content-confirm').html(json.preview);
                                    }
                                }
                            }
                        },
                        error: function () {
                            eL.removeClass('loading');
                        }
                    });
                }
            }
        } else {
            $('.ets_ac_btn_step_save').addClass('hide');
            $('.ets_ac_btn_step_continue').removeClass('hide');
        }
    });
    $(document).on('click', 'button[name*=continue], button[name=finishStepAndRun]', function () {
        if (!$(this).hasClass('finish')) {
            var currentStep = $('.ets_abancart_tab_item.active');
            if (!currentStep.is(':last')) {
                currentStep.next('li').trigger('click');
            }
            ets_ab_fn.prevNext();
        } else if ($(this).is(':enabled') && ets_ab_fn.validateForm()) {
            $(this).addClass('active');
            $('button[name*=save]').trigger('click');
        }
    });
    $(document).on('click', 'button[name*=back]', function () {
        var currentStep = $('.ets_abancart_tab_item.active');
        if (!currentStep.is(':first')) {
            currentStep.prev('li').trigger('click');
        }
        ets_ab_fn.prevNext();
    });

    $(document).on('change keyup', 'textarea[id^=content_], input[id^=content_]', function () {
        ets_ab_fn.previewLanguage();
        ets_ab_fn.prevNext();
    });

    /*--------------------AJAX---------------------*/
    $(document).on('click', '#ets_abd_cronjob_link', function (ev) {
        ev.preventDefault();
        var _self = $(this);
        if (!_self.hasClass('active')) {
            _self.addClass('active');
            $.ajax({
                type: 'post',
                //url: _url,
                data: 'ajax=1&action=cronjobExecute&secure=' + $('#ETS_ABANCART_SECURE_TOKEN').val(),
                dataType: 'json',
                success: function (json) {
                    _self.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            if (json.result)
                                showSuccessMessage(json.result);
                            if (json.log)
                                $('#ETS_ABANCART_CRONJOB_LOG').val($('#ETS_ABANCART_CRONJOB_LOG').val() + json.log + "\r\n");
                            if (json.status)
                                $('.ets_abancart_cronjobs').replaceWith(json.status);
                        }
                    }
                },
                error: function () {
                    _self.removeClass('active');
                }
            });
        }
    });
    $(document).on('click', '.ets_abancart_clear_log', function () {
        var _self = $(this);

        if (!_self.hasClass('active') && ETS_ABANCART_AJAX_LINK) {
            _self.addClass('active');
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_AJAX_LINK,
                dataType: 'json',
                data: 'ajax=1&action=clearLog',
                success: function (json) {
                    _self.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            if (json.msg)
                                showErrorMessage(json.msg);
                        } else {
                            showSuccessMessage(json.msg);
                            $('#ETS_ABANCART_CRONJOB_LOG').val('');
                        }
                    }
                },
                error: function () {
                    _self.removeClass('active');
                }
            });
        }
    });
    $(document).on('click', '.ets_abancart_campaign_type', function () {
        var btn = $(this);
        if ($('.ets_abancart_campaign_recent.' + btn.data('id')).length > 0) {
            $('.ets_abancart_campaign_recent, .ets_abancart_campaign_type').removeClass('active');
            btn.addClass('active');
            $('.ets_abancart_campaign_recent.' + btn.data('id')).addClass('active');

        } else if (ETS_ABANCART_AJAX_LINK && !btn.hasClass('ajax-loading')) {
            btn.addClass('ajax-loading');
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_AJAX_LINK,
                dataType: 'json',
                data: 'ajax=1&action=listRecent&type=' + btn.data('id'),
                success: function (json) {
                    btn.removeClass('ajax-loading');
                    $('.ets_abancart_campaign_recent, .ets_abancart_campaign_type').removeClass('active');
                    btn.addClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            $('.ets_abancart_tables').append('<div class="ets_abancart_campaign_recent ' + btn.data('id') + ' active">' + json.html + '</div>');
                        }
                    }
                },
                error: function () {
                    btn.removeClass('ajax-loading');
                }
            });
        }
    });

    // Upload files.
    $(document).on('change', 'input[type=file]', function () {
        ets_ab_file.readURL(this);
    });

    // Charts.
    $(document).on('click', 'button.ets_abancart_btn_apply', function () {
        if ($(this).parents('.ets_abancart_chart').hasClass('dashboard'))
            ets_ab_chart.statsDashboard($(this).parents('.ets_abancart_chart').find('.ets_abancart_time_series').eq(0));
        else
            ets_ab_chart.chartAjax($(this));
    });
    $(document).on('change', '.ets_ac_ft_email, .ets_ac_ft_other', function () {
        ets_ab_fn.reminder_filter = $(this).val();
        ets_ab_chart.chartAjax($(this));
    });

    // View reminder log.
    $(document).on('click', 'a.ets_abancart_reminder_log', function (ev) {
        ev.preventDefault();
        var btn = $(this),
            url_link = btn.attr('href')
        ;
        if (!btn.hasClass('active') && url_link) {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: /((?:.*)index.php\?(.*?))/.test(url_link) ? url_link : btn.parents('form').attr('action'),
                dataType: 'json',
                data: 'ajax=1&action=reminderLog',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            $('.ets_abancart_form').html(json.html);
                            $('.ets_abancart_overload:not(.active)').addClass('reminder_log active');
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });
    $(document).on('click', 'a.ets_abancart_view_tracking', function (ev) {
        ev.preventDefault();
        var btn = $(this), url_link = btn.attr('href');
        if (!btn.hasClass('active') && url_link) {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: /(^index.php\?(.*?))/.test(url_link) ? url_link : btn.parents('form').attr('action'),
                dataType: 'json',
                data: '&ajax=1&action=viewTracking',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            $('.ets_abancart_form').html(json.html);

                            $('.ets_abancart_overload:not(.active)').addClass('view_tracking active');
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', 'a.ets_abancart_sendmail:not(.disabled)', function (ev) {
        ev.preventDefault();
        var btn = $(this), url = btn.attr('href'), _wrap_form = $('.ets_abancart_form');
        if (!btn.hasClass('active') && url) {
            btn.addClass('active');
            $('td.ets_abancart_send_date.active').removeClass('active');
            $.ajax({
                type: 'post',
                url: /(^index.php\?(.*?))/.test(url) ? url : btn.parents('form').attr('action'),
                data: 'ajax=1&action=formSendMail',
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            _wrap_form.html(json.html);
                            $('.ets_abancart_overload:not(.active)').addClass('active').parents('body').addClass('ets_open_modal');
                            btn.parents('tr').find('td.ets_abancart_send_date').addClass('active');
                            ets_ab_fn.groupTabs();
                            var _newTimer = setInterval(function () {
                                if (_wrap_form.find('textarea.autoload_rte').length > 0 && _wrap_form.find('[id*=mce_]').length > 0 && typeof tinyMCE !== "undefined") {
                                    ets_ab_fn.selectTemplates();
                                    clearInterval(_newTimer);
                                }
                            }, 128);
                            $('[data-toggle="tooltip"]').tooltip();
                            etsAcInitSearchSpecificProduct();
                            etsAcInitSearchMultipleProduct();
                            etsAcInitSearchGiftProduct();
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', 'a[id*=ets_abancart_reminder], a.ets_ac_add_reminder_btn_msg, form[id$=ets_abancart_reminder] a.edit, a.ets_abancart_add_new_reminder', function (ev) {
        ev.preventDefault();
        var btn = $(this),
            url = btn.attr('href')
        ;

        if (!btn.hasClass('active') && url) {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: /(^index.php\?(.*?))/.test(url) ? url : btn.parents('form').attr('action'),
                data: 'ajax=1&action=renderForm',
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            $('.ets_abancart_form').html(json.html);
                            $('.ets_abancart_overload:not(.active)').addClass('active').parents('body').addClass('ets_open_modal');
                            $('.ets_abancart_form .mColorPickerInput').mColorPicker();
                            etsAcChangeOptionCustomerReminder($('#email_timing_option'), false);
                            ets_ab_fn.groupTabs();
                            if (!btn.hasClass('edit')) {
                                ets_ab_fn.selectTemplates();
                            }
                            $('[data-toggle="tooltip"]').tooltip();
                            etsAcInitSearchSpecificProduct();
                            etsAcInitSearchMultipleProduct();
                            etsAcInitSearchGiftProduct();

                            if ($('.ets_abancart_preview_info.popup').length) {
                                $('.ets_abancart_preview_title').css('background-color', $('.form_message input[name="header_bg"]').val());
                                $('.ets_abancart_preview').css('background-color', $('.form_message input[name="popup_body_bg"]').val());
                                $('.ets_abancart_preview_content_view').css('width', $('.form_message input[name="popup_width"]').val() + 'px');
                                $('.ets_abancart_preview_content_view').css('border-radius', $('.form_message input[name="border_radius"]').val() + 'px');
                            }
                            $('input[name="minute"]').parents('.input-group').addClass('hide');
                            $('input[name="second"]').parents('.input-group').addClass('hide');
                            $('input[name="hour"]').parents('.input-group').addClass('hide');
                            $('input[name="redisplay"]').parents('.input-group').addClass('hide');
                            $('input[name="day"]').parents('.input-group').addClass('hide');

                            $('input[name="minute"]').parents('.form-group').find('.ets-ac-show-range-time-tool').addClass('hide');
                            $('input[name="second"]').parents('.form-group').find('.ets-ac-show-range-time-tool').addClass('hide');
                            $('input[name="hour"]').parents('.form-group').find('.ets-ac-show-range-time-tool').addClass('hide');
                            $('input[name="redisplay"]').parents('.form-group').find('.ets-ac-show-range-time-tool').addClass('hide');
                            $('input[name="day"]').parents('.form-group').find('.ets-ac-show-range-time-tool').addClass('hide');

                            /*$('.ets-ac-range-input input[type="range"]').each(function () {
                                etsAcSetBubble($(this));
                            });*/
                            $('.ets-ac-range-input input.range').each(function () {
                                etsAcSetInputRange($(this));
                            });
                            $('.mColorPickerInput').change();
                            if ($('input[name="enable_count_down_clock"]').length) {
                                if ($('input[name="enable_count_down_clock"]:checked').val() == 1) {
                                    $('.ets_abancart_short_code.discount_count_down_clock').removeClass('hide');
                                } else {
                                    $('.ets_abancart_short_code.discount_count_down_clock').addClass('hide');
                                }
                            }
                            $('.ets_ac_config_popup_item').addClass('cloned').clone().appendTo($('.ets_abancart_form_group_left')).removeClass('cloned');
                            $('.ets_ac_config_popup_item.cloned').remove();
                            if ($('select[name="vertical_align"]').length) {
                                $('select[name="vertical_align"]').change();
                            }
                            if ($('#has_shopping_cart').length > 0 && parseInt($('#has_shopping_cart').val()) < 1) {
                                $.each(etsACListShoppingShortcode, function (i, el) {
                                    $('.ets_abancart_short_code.' + el).addClass('hide');
                                });
                            }
                            etsAcToggleTabContentDesign('content');
                            if (ets_ab_fn.getParameterByName('addets_abancart_reminder', url) !== null) {
                                ets_ab_fn.selectTemplates($('.ets_abancart_template_ul .ets_abancart_template_li:nth-child(1)').first());
                            }
                            if ($('#customer_email_schedule_time').length > 0) {
                                $('#customer_email_schedule_time').datetimepicker({
                                    minDate: new Date(),
                                    dateFormat: 'yy-mm-dd',
                                    amNames: ['AM', 'A'],
                                    pmNames: ['PM', 'P'],
                                    timeFormat: 'hh:mm:ss tt',
                                    formatTime: 'hh:mm:ss tt',
                                })
                            }
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', 'button[name$=BackToCampaign], button[name$=BackToCampaign]', function (ev) {
        ev.preventDefault();
        ets_ab_popup.offDisplayPopup();
    });

    $(document).on('click', '.ets_abancart_template_ul .ets_abancart_template_li', function () {
        var btn = $(this);
        if (!btn.hasClass('active')) {
            ets_ab_fn.selectTemplates($(this));
        }
    });

    if ($('.datepicker').length > 0) {
        $('.datepicker').attr('autocomplete', 'off')
    }

    $(document).on('click', 'a.action-disabled, a.action-enabled', function (ev) {
        ev.preventDefault();
        var btn = $(this), url = btn.attr('href');
        if (!btn.hasClass('active') && url && url !== '#') {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: /(^index.php\?(.*?))/.test(url) ? url : btn.parents('form').attr('action'),
                data: 'ajax=1&action=status',
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.errors);
                        } else {
                            showSuccessMessage(json.msg);
                            btn.removeClass('action-' + (json.enabled ? 'disabled' : 'enabled')).addClass('action-' + (json.enabled ? 'enabled' : 'disabled'));
                            if (json.enabled) {
                                btn.find('i.icon-check').removeClass('hidden');
                                btn.find('i.icon-remove').addClass('hidden');
                            } else {
                                btn.find('i.icon-check').addClass('hidden');
                                btn.find('i.icon-remove').removeClass('hidden');
                            }
                            if (json.status) {
                                btn.closest('tr').find('td.status').html(json.status);
                            }
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
            return false;
        }
    });

    $(document).on('click', 'form[id$=ets_abancart_reminder] a:not(.edit):not(.ets_ab_btn_change_status):not(.delete):not([id$=-new]):not(.pagination-link):not(.pagination-items-page):not(.action-disabled):not(.action-enabled):not(.ets_abancart_view_tracking):not(.ets_ab_execute_times_link)', function (ev) {
        var btn = $(this),
            url = btn.attr('href')
        ;
        if (!btn.find('i.process-icon-database').length && !btn.find('i.process-icon-terminal').length) {
            ev.preventDefault();
            if (!btn.hasClass('active') && url && url !== '#') {
                btn.addClass('active');
                $.ajax({
                    type: 'post',
                    url: /(^index.php\?(.*?))/.test(url) ? url : btn.parents('form').attr('action'),
                    data: 'ajax=1&action=renderList',
                    dataType: 'json',
                    success: function (json) {
                        btn.removeClass('active');
                        if (json) {
                            if (json.errors) {
                                showErrorMessage(json.errors);
                            } else {
                                $('.ets_abancart_reminder').html(json.html);
                            }
                        }
                    },
                    error: function () {
                        btn.removeClass('active');
                    }
                });
            }
        } else
            btn.click();
    });

    $(document).on('click', 'form[id$=ets_abancart_reminder] a.pagination-items-page, a.pagination-link', function (ev) {
        ev.preventDefault();
        if (parseInt($(this).data('page')) > 0) {
            $('form[id$=-ets_abancart_reminder] input[type=hidden][name=page]').val($(this).data('page'));
            $('#submitFilter' + $(this).data("list-id")).val($(this).data("page"));
        } else if (parseInt($(this).data('items')) > 0) {
            $('form[id$=-ets_abancart_reminder] input[type=hidden][id$=-pagination-items-page]').val($(this).data('items'));
        }
        if ($(this).hasClass('pagination-link') && !$(this).parents('li').hasClass('active') || $(this).hasClass('pagination-items-page')) {
            ets_ab_fn.formSubmit($(this).parents('form'));
        }
    });

    $(document).on('click', 'form[id$=ets_abancart_reminder] button[name^=submitReset]', function (ev) {
        ev.preventDefault();
        var btn = $(this);
        ets_ab_fn.formSubmit(btn.parents('form'), btn);
    });

    $(document).on('submit', 'form[id$=ets_abancart_reminder]', function (ev) {
        ev.preventDefault();
        var form = $(this);
        ets_ab_fn.formSubmit(form);
    });

    $(document).on('click', 'button[name*=save], #ets_abancart_cart_form_submit_btn', function (ev) {

        var _eln = $(this).attr('name')
        ;
        if (ets_ab_actions.indexOf(_eln) !== -1) {
            ev.preventDefault();
            var _self = $(this),
                _form = _self.parents('form')
            ;
            if (_form.length > 0 && !_self.hasClass('active') && _form.attr('action')) {
                var _continue = $('button[name*=continue].finish'),
                    _ff = $('.defaultForm.active input[type="file"]'),
                    _et = 'saveEmail_template'
                ;
                $('.ets_abancart_form .bootstrap.abancart_alert').remove();
                _self.addClass('active');

                if (typeof tinyMCE !== "undefined" && tinyMCE.editors.length > 0) {
                    tinyMCE.triggerSave();
                }

                var _fd = new FormData(_form.get(0));

                // Safari fixed.
                if (_ff.length > 0) {
                    _ff.each(function () {
                        if ($($(this).attr('id')).files.length == 0) {
                            _fd.delete($(this).attr('id'));
                        }
                    });
                }
                _fd.append('ajax', 1);

                // Post process action.
                if (_form.hasClass('AdminEtsACCart')) {
                    _fd.append('action', 'sendMail');
                } else {
                    _fd.append('action', 'saveData');
                }
                // End process.
                $.ajax({
                    url: _form.attr('action'),
                    type: 'post',
                    data: _fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (json) {
                        _self.removeClass('active');
                        _continue.removeClass('active');
                        if (json) {
                            if (json.errors) {
                                _form.before(json.errors);
                                $('.ets_abancart_form .bootstrap').addClass('abancart_alert');
                            } else {

                                if ($('.js-ets-ac-alert-no-reminder').length) {
                                    $('.js-ets-ac-alert-no-reminder').addClass('hide');
                                }
                                showSuccessMessage(json.msg, 3500);

                                // Reminder.
                                if (json.html)
                                    $('.ets_abancart_reminder').html(json.html);
                                else if (json.date_upd)
                                    $('td.ets_abancart_send_date.active').html(json.date_upd);
                                if (json.list) {
                                    $('.ets_abancart_forms').html(json.list);
                                }
                                if (json.nb_reminders)
                                    $('.ets_abancart_reminder_empty.active').removeClass('active');
                                ets_ab_popup.offDisplayPopup();

                                // Template Email.
                                if (_eln === _et) {
                                    _form.attr('action', json.currentIndex);
                                    $('input[id=' + json.identifier + ']').val(json.id);
                                    ets_ab_file.clearInputFile();
                                }

                            }
                        }
                    },
                    complete: function () {
                        _self.removeClass('active');
                        $('button[name="finishStepAndRun"]').removeClass('active').prop('disabled', false);
                    },
                    error: function () {
                        _continue.removeClass('active');
                        _self.removeClass('active');
                    }
                });
            }
        }
    });

    // Delete image.
    $(document).on('click', '[id$=-images-thumbnails] a.btn', function (ev) {
        ev.preventDefault();
        var _self = $(this),
            _fg = $(this).parents('.form-group').eq(0),
            _file = _fg.next().find('input[type=file]').eq(0)
        ;
        if (!_self.hasClass('active') && _self.attr('href') !== '#') {
            _self.addClass('active');
            $.ajax({
                type: 'post',
                url: _self.attr('href'),
                data: 'ajax=1&action=deleteImage',
                dataType: 'json',
                success: function (json) {
                    _self.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            if (json.msg)
                                showErrorMessage(json.msg);
                        } else {
                            showSuccessMessage(json.msg);
                            $('.ets_abancart_preview_content_view .ets_abancart_image').remove();
                        }
                    }
                },
                error: function () {
                    _self.removeClass('active');
                }
            });
        }
        if (_self.hasClass('base64encode')) {
            $('.ets_abancart_preview_content_view .ets_abancart_image').remove();
        }
        ets_ab_file.clearInputFile(_file);

        _fg.remove();
        var _img = $('.ets_abancart_preview_browser .ets_abancart_image');
        if (_img.length > 0) {
            _img.remove();
        }

        ets_ab_fn.pvIconBrowser();
    });

    $(document).on('click', 'form[id$=ets_abancart_reminder] a.delete', function (ev) {
        ev.preventDefault();
        var btn = $(this), confirm_msg = btn.data('confirm');
        if (!btn.hasClass('active') && btn.attr('href') && btn.attr('href') !== '#' && confirm(confirm_msg)) {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: btn.attr('href'),
                data: 'ajax=1&action=delete',
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(etsAcStripTags(json.errors));
                        } else {
                            showSuccessMessage(json.msg);
                            $('.ets_abancart_reminder').html(json.html);
                            if (!json.nb_reminders) {
                                $('.ets_abancart_reminder_empty:not(.active)').addClass('active');
                                $('.js-ets-ac-alert-no-reminder').removeClass('hide');
                            }
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    //end ajax.
    $(document).on('change', 'input[name=ETS_ABANCART_DISCOUNT_OPTION]', function () {
        ets_ab_fn.discountOption($(this).val());
    });

    $(document).on('change', 'input[name=ETS_ABANCART_APPLY_DISCOUNT]', function () {
        ets_ab_fn.applyDiscount($(this).val());
    });

    $(document).on('change', 'input[name=discount_option]', function () {
        ets_ab_fn.discountOpt($(this).val());

        if ($(this).val() == 'auto') {
            $('.ets_ac_discount_qty').removeClass('hide');
        } else {
            $('.ets_ac_discount_qty').addClass('hide');
        }
        ets_ab_fn.resetReduction();
    });

    $(document).on('change', 'input[name=apply_discount]', function () {
        ets_ab_fn.discountType($(this).val());
        ets_ab_fn.resetReduction();
        if ($(this).val() == 'amount') {
            $('#apply_discount_to_cheapest').closest('li').addClass('hide');
            $('#apply_discount_to_selection').closest('li').addClass('hide');
            if ($('#apply_discount_to_cheapest').is(':checked') || $('#apply_discount_to_selection').is(':checked')) {
                $('#apply_discount_to_cheapest').prop('checked', false);
                $('#apply_discount_to_selection').prop('checked', false);
                $('#apply_discount_to_order').prop('checked', true);
            }
        } else if ($(this).val() == 'percent') {
            $('#apply_discount_to_cheapest').closest('li').removeClass('hide');
            $('#apply_discount_to_selection').closest('li').removeClass('hide');
        }
        $('.ets_ac_selected_product_group').addClass('hide');
        $('.ets_ac_specific_product_group').addClass('hide');
        if ($('.ets_ac_apply_discount').hasClass('active')) {

            if ($('input[name="apply_discount_to"]:checked').val() == 'selection') {
                $('.ets_ac_selected_product_group').removeClass('hide');
            } else if ($('input[name="apply_discount_to"]:checked').val() == 'specific') {
                $('.ets_ac_specific_product_group').removeClass('hide');
            }
        }
    });

    $(document).on('change', 'input[name=ETS_ABANCART_APPLY_DISCOUNT]', function () {
        if ($(this).val() == 'amount') {
            $('#ETS_ABANCART_APPLY_DISCOUNT_TO_cheapest').closest('li').addClass('hide');
            $('#ETS_ABANCART_APPLY_DISCOUNT_TO_selection').closest('li').addClass('hide');
            if ($('#ETS_ABANCART_APPLY_DISCOUNT_TO_cheapest').is(':checked') || $('#ETS_ABANCART_APPLY_DISCOUNT_TO_selection').is(':checked')) {
                $('#ETS_ABANCART_APPLY_DISCOUNT_TO_cheapest').prop('checked', false);
                $('#ETS_ABANCART_APPLY_DISCOUNT_TO_selection').prop('checked', false);
                $('#ETS_ABANCART_APPLY_DISCOUNT_TO_order').prop('checked', true);
            }
        } else if ($(this).val() == 'percent') {
            $('#ETS_ABANCART_APPLY_DISCOUNT_TO_cheapest').closest('li').removeClass('hide');
            $('#ETS_ABANCART_APPLY_DISCOUNT_TO_selection').closest('li').removeClass('hide');
        }
        $('.ets_ac_selected_product_group').addClass('hide');
        $('.ets_ac_specific_product_group').addClass('hide');
        if ($(this).val() == 'amount' || $(this).val() == 'percent') {

            if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]:checked').val() == 'selection') {
                $('.ets_ac_selected_product_group').removeClass('hide');
            } else if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]:checked').val() == 'specific') {
                $('.ets_ac_specific_product_group').removeClass('hide');
            }
        }
    });
    if ($('input[name="ETS_ABANCART_DISCOUNT_OPTION"]').length) {
        etsAcInitSearchSpecificProduct();
        etsAcInitSearchGiftProduct();
        etsAcInitSearchMultipleProduct();
        if ($('input[name="ETS_ABANCART_DISCOUNT_OPTION"]:checked').val() == 'auto' && $('input[name="ETS_ABANCART_APPLY_DISCOUNT"]').val() == 'percent') {
            if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]').val() == 'selection') {
                $('.ets_ac_selected_product_group').removeClass('hide');
            } else if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]:checked').val() == 'specific') {
                $('.ets_ac_specific_product_group').removeClass('hide');
            }
        }

        if ($('input[name="ETS_ABANCART_SEND_A_GIFT"]:checked').val() == 1) {
            $('.ets_ac_gift_product_filter_group').removeClass('hide');
        } else {
            $('.ets_ac_gift_product_filter_group').addClass('hide');
        }
        $('.ets_ac_specific_product_group').addClass('hide');
        $('.ets_ac_selected_product_group').addClass('hide');
        if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]:checked').val() == 'specific') {
            $('.ets_ac_specific_product_group').removeClass('hide');
        } else if ($('input[name="ETS_ABANCART_APPLY_DISCOUNT_TO"]:checked').val() == 'selection') {
            $('.ets_ac_selected_product_group').removeClass('hide');
        }
    }
    $(document).on('change', 'input[name=apply_discount_to],input[name=ETS_ABANCART_APPLY_DISCOUNT_TO]', function () {
        var val = $(this).val();

        $('.ets_ac_selected_product_group').show().addClass('hide');
        $('.ets_ac_specific_product_group').show().addClass('hide');
        if (val == 'specific') {
            $('.ets_ac_specific_product_group').removeClass('hide');
        } else if (val == 'selection') {
            $('.ets_ac_selected_product_group').removeClass('hide');
        }
    });

    $(document).on('change', 'input[name="ETS_ABANCART_SEND_A_GIFT"]', function () {
        if ($(this).val() == 1) {

            $('.ets_ac_gift_product_filter_group').removeClass('hide');
        } else {
            $('.ets_ac_gift_product_filter_group').addClass('hide');
        }
    });

    //form.
    $(document).on('click', '.ets_abancart_close_form', function () {
        ets_ab_popup.offDisplayPopup();
    });
    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            //ets_ab_popup.offDisplayPopup();
            ets_ab_popup.offPopupTracking();
        }
    });

    $(document).on('click', '.ets_abancart_table_cell', function (ev) {
        ev.stopPropagation();
    });

    $(document).on('click', '.ets_abancart_overload', function (ev) {
        ets_ab_popup.offPopupTracking();
    });

    $(document).mouseup(function (e) {
        var formLoad = $(".ets_abancart_form"),
            colorPicker = $('#mColorPicker'),
            tinyMCEPanel = $('[id^=mce_].mce-window');

        if (!formLoad.is(e.target) && formLoad.has(e.target).length === 0 && (!colorPicker.length || !colorPicker.is(e.target) && colorPicker.has(e.target).length === 0 && colorPicker.css('display') == 'none') && (!tinyMCEPanel.length || !tinyMCEPanel.is(e.target) && tinyMCEPanel.has(e.target).length === 0 && tinyMCEPanel.css('display') == 'none')) {
            //ets_ab_popup.offDisplayPopup();
        }
        if (!colorPicker.length || !colorPicker.is(e.target) && colorPicker.has(e.target).length === 0) {
            colorPicker.fadeOut();
            $(".mColor, .mPastColor, #mColorPickerInput, #mColorPickerWrapper").unbind();
            $("#mColorPickerBg").hide();
        }
    });

    if ($(".datepicker").length > 0) {
        var dateFormat = 'yy-mm-dd',
            from = $('input[name=available_from], input[name=from_time], input[name=last_order_from]'),
            to = $('input[name=available_to], input[name=to_time], input[name=last_order_to]')
        ;
        from.datepicker({
            prevText: '',
            nextText: '',
            changeMonth: true,
            changeYear: true,
            dateFormat: dateFormat,
            maxDate: to.val(),
        }).on("change", function () {
            to.datepicker("option", "minDate", getDate(this));
        });
        to.datepicker({
            prevText: '',
            nextText: '',
            changeMonth: true,
            changeYear: true,
            dateFormat: dateFormat,
            minDate: from.val(),
        }).on("change", function () {
            from.datepicker("option", "maxDate", getDate(this));
        });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
    }

    //new.
    $(document).on('change', '.mColorPickerInput', function () {
        var _bx = $('#ets_abancart_reminder_form .ets_abancart_preview'),
            _p = $(this).attr('name'),
            _v = $(this).val();
        if (_bx.length > 0) {
            _bx.css(_p.replace(/\_/g, '-').replace(/text-/g, ''), _v);
        }
    });
    if ($('#content.bootstrap > .bootstrap > .alert.alert-warning').length > 0) {
        var alertwarning = $('#content.bootstrap > .bootstrap > .alert.alert-warning');
        $('.aban_menu_height').after(alertwarning);
        $('#content.bootstrap > .bootstrap > .alert.alert-warning').remove();
    }
    $(document).on('click', '.ets_abancart_re_sendmail', function (e) {
        e.preventDefault();
        var btn = $(this), postUrl = btn.attr('href');
        if (!btn.hasClass('active') && postUrl !== '#') {
            btn.addClass('active');
            $.ajax({
                type: 'POST',
                data: 'ajax=1&action=sendmail',
                url: postUrl,
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            showErrorMessage(json.errors);
                        else {
                            if (json.msg)
                                showSuccessMessage(json.msg);
                            if (json.html)
                                $('.ets_abancart_forms').html(json.html);
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_email_queue .btn-group a.btn:not(.ets_abancart_re_sendmail)', function (e) {
        e.preventDefault();
        var btn = $(this), postUrl = btn.attr('href');
        if (!btn.hasClass('active') && postUrl !== '#') {
            btn.addClass('active');
            $.ajax({
                type: 'POST',
                data: 'ajax=1&action=renderView',
                url: postUrl,
                dataType: 'json',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            showErrorMessage(json.errors);
                        if (json.html) {
                            const _viewer = $(json.html);
                            const _overload = $('.ets_abancart_forms .ets-abancart-overload');
                            if (_overload.length > 0) {
                                _overload.remove();
                            }
                            $('#form-ets_abancart_email_queue').after(json.html);
                            $('.ets_abancart_forms .ets-abancart-overload').addClass('active');
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets-abancart-close-view', function () {
        $('.ets_abancart_forms .ets-abancart-overload.active').removeClass('active');
    });

    $(document).on('click', '.js-ets-ac-export-campaign-tracking', function () {
        var $form = $(this).closest('form');
        $form.find('.form-errors').html('');
        if ($form.find('select[name="filter_time"]').val() == 'time_range') {
            var dateFrom = $form.find('input[name="time_range_from"]').val();
            var dateTo = $form.find('input[name="time_range_to"]').val();
            var error = '';
            if (!dateFrom || !dateTo) {
                error = ets_ac_trans.date_range_required;
            } else {
                var timeFrom = new Date(dateFrom);
                var timeTo = new Date(dateTo);

                if (!timeFrom || !timeTo) {
                    error = ets_ac_trans.date_range_invalid;
                } else if (timeFrom.getTime() > timeTo.getTime()) {
                    error = ets_ac_trans.date_range_from_less_than;
                }
            }

            if (error) {
                $form.find('.form-errors').html('<div class="alert alert-danger ets_ac_alert_popup">' + error + '</div>');
                return false;
            }
        }
        $form.submit();
        var $this = $(this);
        $this.prop('disabled', true);
        setTimeout(function () {
            $('#etsAcModalDownloadEmailTracking').modal('hide');
            $this.prop('disabled', false);
        }, 500);
    });

    $(document).on('click', '.ets-ac-products-list-selected .del_product_search', function (e) {
        $(this).closest('.form-group').find('.ac_input').prop('disabled', false);
        $(this).parents('.ets-ac-products-list-selected').removeClass('has_content');
        $(this).closest('.form-group').find('.ac_input').val('');
        $(this).closest('.form-group').find('input[type=hidden]').val('');
        $(this).closest('li').remove();
    });
    $(document).on('change', 'input[name="free_gift"]', function () {
        if ($(this).val() == '1') {
            $('.ets_ac_gift_product_filter_group').removeClass('hide');
        } else {
            $('.ets_ac_gift_product_filter_group').addClass('hide');
        }
    });

    $(document).on('change', 'input[name=email_timing_option]', function () {
        etsAcChangeOptionCustomerReminder($('input[name=email_timing_option]:checked'), false);
    });
    $(document).on('change', '.ets_ac_popup_filter_time', function () {

        if ($(this).val() == 'time_range') {
            $('.ets_ac_popup_time_range_box').removeClass('hide');
        } else {
            $('.ets_ac_popup_time_range_box').addClass('hide');
        }
    });
    $('#etsAcModalDownloadEmailTracking').on('hidden.bs.modal', function (e) {
        $(this).find('.form-errors').html('');
    });
    $('.ets_ac_popup_datepicker').datepicker({dateFormat: 'yy-mm-dd'});

    $(document).on('click', '.js-ets-ac-duplicate-email-temp', function () {
        if ($(this).hasClass('loading')) {
            return false;
        }
        var $this = $(this);
        $.ajax({
            url: $this.attr('data-href'),
            type: 'POST',
            dataType: 'json',
            data: {etsAcDuplicateEmailTemp: 1},
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    showSuccessMessage(res.message);
                } else {
                    showErrorMessage(res.message);
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
    });
    if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderLeave') {
        if ($('input[name="ETS_ABANCART_QUANTITY"]').val() == '') {
            $('input[name="ETS_ABANCART_QUANTITY"]').val('1')
        }
        if ($('input[name="ETS_ABANCART_QUANTITY_PER_USER"]').val() == '') {
            $('input[name="ETS_ABANCART_QUANTITY_PER_USER"]').val('1')
        }
        if ($('select[name="ETS_ABANCART_HAS_PRODUCT_IN_CART"]').val() != 1) {
            $.each(etsACListShoppingShortcode, function (i, el) {
                $('.ets_abancart_short_code.' + el).addClass('hide');
            });
        }
        var hasProductInCart = parseInt($('#ETS_ABANCART_HAS_PRODUCT_IN_CART').val());
        $(document).on('change', 'select[name="ETS_ABANCART_HAS_PRODUCT_IN_CART"]', function () {
            var productInCartChoice = parseInt($(this).val())
            if (productInCartChoice < 1) {
                $.each(etsACListShoppingShortcode, function (i, el) {
                    $('.ets_abancart_short_code.' + el).addClass('hide');
                });
            } else {
                $.each(etsACListShoppingShortcode, function (i, el) {
                    $('.ets_abancart_short_code.' + el).removeClass('hide');
                });
            }
            if ((hasProductInCart === 1 && productInCartChoice !== 1 || hasProductInCart !== 1 && productInCartChoice === 1) && confirm(ETS_ABANCART_MSG_WARNING_CONTENT)) {
                etsAcSetDefaultContentPopup();
                ets_ab_fn.discountOption();
            }
            hasProductInCart = productInCartChoice;
        });

        setTimeout(function () {
            //etsAcSetDefaultContentPopup();
            $('.ets-ac-range-input input').change();
            $('.mColorPickerInput').change();
        }, 500);

        setTimeout(function () {
            if ($('.iframe-leaving-content-preview').length) {
                etsAcResizeIframe($('.iframe-leaving-content-preview')[0]);
            }
        }, 1000);
    }

    $(document).on('click', '.js-ets-ac-btn-reset-content-popup', function () {
        var confirmMsg = $(this).attr('data-confirm');
        if (!confirmMsg || !confirm(confirmMsg)) {
            return false;
        }
        $('[id^="content_"],[id^="ETS_ABANCART_CONTENT_"]').each(function () {
            if (typeof tinyMCE !== 'undefined' && typeof tinyMCE.get($(this).attr('id')) !== 'undefined') {
                tinyMCE.get($(this).attr('id')).setContent('');
            } else {
                $(this).val('');
            }
        });
        etsAcSetDefaultContentPopup();
    });

    $(document).on('mouseenter', '.ets_abancart_lookup>i', function () {
        var el = $(this).closest('.ets_abancart_template_li');
        var rightPos = $(window).width() - (el.offset().left + el.width());
        if (rightPos < 600) {
            $(this).next('.ets_abancart_lookup_content').addClass('pos-left');
        }
    });

    $(document).on('mouseenter', '.ets_abancart_thumb', function (e) {
        var pos_top = $(this).offset().top;
        var pos_top_admin = $('.adminetsacemailtemplate ').offset().top;
        var screen_height = $(window).height();
        var item_height = $(this).find('.ets_abancart_lookup_content').height();
        var wd_scroll = $(window).scrollTop();
        if ( screen_height > item_height + 200){
            if ( pos_top + item_height - wd_scroll > screen_height) {
                var pos_change = ( pos_top + item_height ) - screen_height - wd_scroll;
                $(this).find('.ets_abancart_lookup_content').css('margin-top','-'+pos_change+'px');
                $(this).find('.ets_abancart_lookup_content .thumb_arrow').css('margin-top',pos_change+'px');
            }
        } else {
            var new_height_item = screen_height - 250;
            $(this).find('.ets_abancart_lookup_content').css('height',new_height_item+'px');
        }
    });
    $(document).on('mouseleave', '.ets_abancart_thumb', function () {
        $('.ets_abancart_lookup_content').css('margin-top','auto');
        $('.ets_abancart_lookup_content').css('height','auto');
        $(this).find('.ets_abancart_lookup_content .thumb_arrow').css('margin-top','auto');
    });


    $(window).resize(function () {
        if ($('.ets_abancart_lookup_content.pos-left').length > 0) {
            $('.ets_abancart_lookup_content').removeClass('pos-left');
        }
    });
    $(document).on('mouseleave', '.ets_abancart_lookup>i', function () {
        //$(this).next('.ets_abancart_lookup_content').removeClass('pos-left');
    });

    if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACTracking' && $('#form-ets_abancart_tracking').length) {
        $('#form-ets_abancart_tracking').find('.panel-heading').append('<a href="' + ETS_AC_LINK_CAMPAIGN_TRACKING + '&clearTracking=1" class="btn btn-default pull-right ets_ac-clear-tracking js-ets_ac-clear-tracking" title="' + ETS_AC_TRANS.clear_tracking + '"><svg width="16" height="14" viewBox="0 0 2048 1792"><path d="M960 1408l336-384h-768l-336 384h768zm1013-1077q15 34 9.5 71.5t-30.5 65.5l-896 1024q-38 44-96 44h-768q-38 0-69.5-20.5t-47.5-54.5q-15-34-9.5-71.5t30.5-65.5l896-1024q38-44 96-44h768q38 0 69.5 20.5t47.5 54.5z"/></svg></a>');
    }

    $(document).on('click', '.js-ets_ac-clear-tracking', function () {
        if (!confirm(ETS_AC_TRANS.confirm_clear_tracking)) {
            return false;
        }
        if ($(this).hasClass('loading')) {
            return false;
        }
        $(this).addClass('loading');
        return true;
    });

    $(document).on('click', '.js-ets-ac-pause-reminder', function () {
        var $this = $(this);
        if ($this.hasClass('loading')) {
            return false;
        }
        $.ajax({
            url: ETS_AC_LINK_REMINDER_ADMIN,
            type: 'POST',
            data: {
                etsAcPauseReminder: 1,
                id_reminder: $this.attr('data-reminder')
            },
            dataType: 'json',
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    showSuccessMessage(res.message);
                    $this.closest('tr').find('.list-action-enable').addClass('action-disabled').removeClass('action-enabled');
                    $this.closest('tr').find('.list-action-enable .icon-check').addClass('hidden');
                    $this.closest('tr').find('.list-action-enable .icon-remove').removeClass('hidden');
                    $this.closest('td').html(res.status);
                    $this.closest('ets_ab_reminder_status_running').removeClass('hidden').next('.ets_ab_reminder_status_stopped').addClass('hidden');
                } else {
                    showErrorMessage(res.message);
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
    });
    $(document).on('click', '.js-ets-ac-continue-reminder', function () {
        var $this = $(this);
        if ($this.hasClass('loading')) {
            return false;
        }
        $.ajax({
            url: ETS_AC_LINK_REMINDER_ADMIN,
            type: 'POST',
            data: {
                etsAcContinueReminder: 1,
                id_reminder: $this.attr('data-reminder')
            },
            dataType: 'json',
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    showSuccessMessage(res.message);
                    $this.closest('tr').find('.list-action-enable').removeClass('action-disabled').addClass('action-enabled');
                    $this.closest('tr').find('.list-action-enable .icon-check').removeClass('hidden');
                    $this.closest('tr').find('.list-action-enable .icon-remove').addClass('hidden');
                    $this.closest('td').html(res.status);
                    $this.closest('ets_ab_reminder_status_stopped').removeClass('hidden').next('.ets_ab_reminder_status_running').addClass('hidden');
                } else {
                    showErrorMessage(res.message);
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
    });

    $('#page-header-desc-ets_abancart_email_template-import_email_template, #desc-ets_abancart_email_template-import').click(function () {
        $('#etsAcModalImportEmailTemplate').modal('show');
        $('#etsAcModalImportEmailTemplate').find('.form-errors').html('');
    });
    $('.js-ets-ac-import-email-temp').click(function () {
        if ($(this).hasClass('loading')) {
            return false;
        }
        var $this = $(this);
        $this.closest('form').find('.form-errors').html('');
        var formData = new FormData();
        formData.append('email_template', $this.closest('form').find('input[name="email_template"]')[0].files[0]);
        formData.append('name', $this.closest('form').find('input[name="name"]').val());
        formData.append('etsAcImportEmailTemplate', 1);
        $.ajax({
            url: '',
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
                $this.closest('form').find('.form-errors').html('');
            },
            success: function (res) {
                if (res.success) {
                    showSuccessMessage(res.message);
                    $('#etsAcModalImportEmailTemplate').modal('hide');
                    window.location.reload();
                } else {
                    var error = '<div class="alert alert-danger alert-relative"><ul>';
                    if (typeof res.message === 'string') {
                        error += '<li>' + res.message + '</li>';
                    } else {
                        $.each(res.message, function (i, el) {
                            error += '<li>' + el + '</li>';
                        });
                    }
                    error += '</ul></div>';

                    $this.closest('form').find('.form-errors').html(error);
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
    });

    if ($('input[name=enable_captcha]:checked').val() == '1') {
        $('.ets_ac_lead_captcha_item').removeClass('hide');
        $('.ets_ac_lead_captcha_item_type').addClass('hide');
        $('.ets_ac_lead_captcha_item_type_' + $('select[name="captcha_type"]').val()).removeClass('hide');
    } else {
        $('.ets_ac_lead_captcha_item').addClass('hide');
    }

    $('input[name=enable_captcha]').change(function () {
        if ($(this).val() == '1') {
            $('.ets_ac_lead_captcha_item').removeClass('hide');
            $('.ets_ac_lead_captcha_item_type').addClass('hide');
            $('.ets_ac_lead_captcha_item_type_' + $('select[name="captcha_type"]').val()).removeClass('hide');
        } else {
            $('.ets_ac_lead_captcha_item').addClass('hide');
        }
    });

    $('select[name="captcha_type"]').change(function () {
        $('.ets_ac_lead_captcha_item_type').addClass('hide');
        $('.ets_ac_lead_captcha_item_type_' + $(this).val()).removeClass('hide');
    });

    if ($('.ets_abancart_preview.view_email_template').length) {
        etsAcSetIframeviewEmailTemp();
    }

    //Toggle tab lead
    if ($('.js-ets-ac-lead-tab-item').length) {
        $('.ets_ac_tab_lead_content_item').hide();
        $('.ets_ac_tab_lead_content_item_info').show();
    }
    $('.js-ets-ac-lead-tab-item').click(function () {
        $('.js-ets-ac-lead-tab-item').parent('li').removeClass('active');
        $(this).parent('li').addClass('active');
        var dataTab = $(this).attr('data-tab');
        $('.ets_ac_tab_lead_content_item').hide();
        if (dataTab == 'info') {
            $('.ets_ac_tab_lead_content_item_info').show();
        } else if (dataTab == 'fields') {
            $('.ets_ac_tab_lead_item_field_list').show();
        } else if (dataTab == 'thankyoupage') {
            $('.ets_ac_tab_lead_item_thankyou_page').show();
        }
    });
    //End toggle tab list
    $('.js-ets-ac-btn-add-field').click(function () {
        var field = $('.ets_ac_new_fields').html();
        field = field.replace(/\(new_id_field\)/g, etsAcGetRandInt(11111, 999999));
        $('.ets-ac-lead-list-fields').append(field);
        return false;
    });

    $(document).on('change', '.js-ets-ac-field-type-input', function () {
        var type = $(this).find('option:selected').attr('data-type');
        $('.ets_ac_is_contact_name').addClass('hide');
        $('.ets_ac_is_contact_email').addClass('hide');
        if (type == 'radio' || type == 'checkbox' || type == 'select') {
            $(this).closest('.lead-field-item').find('.options').removeClass('hide');
        } else if (type == 'text') {
            $('.ets_ac_is_contact_name').removeClass('hide');
        } else if (type == 'email') {
            $('.ets_ac_is_contact_email').removeClass('hide');
        } else {
            $(this).closest('.lead-field-item').find('.options').addClass('hide');
        }
        if (type == 'radio' || type == 'checkbox' || type == 'select' || type == 'file') {
            $(this).closest('.lead-field-item').find('.placeholder').addClass('hide');
        } else {
            $(this).closest('.lead-field-item').find('.placeholder').removeClass('hide');
        }
    });

    $(document).on('click', '.js-ets-ac-btn-delete-lead-field', function () {
        if (!confirm(ETS_AC_TRANS.confirm_delete_lead_field)) {
            return false;
        }
        var $this = $(this);
        if ($this.hasClass('loading')) {
            return false;
        }
        $.ajax({
            url: '',
            type: 'POST',
            dataType: 'json',
            data: {
                etsAcDeleteLeadField: 1,
                id_field: $this.attr('data-id'),
            },
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    showSuccessMessage(res.message);
                    $this.closest('.lead-field-item').remove();
                } else {
                    showErrorMessage(res.message);
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
        return false;
    });


    if ($('.form_lead_form input[name="lead_form"]').length) {
        $(document).on('change', '.form_lead_form input[name="lead_form"]', function () {
            ets_ab_fn.previewLanguage();
        });
    } else if ($('input[name="ETS_ABANCART_LEAD_FORM"]').length) {
        $(document).on('change', 'input[name="ETS_ABANCART_LEAD_FORM"]', function () {
            ets_ab_fn.previewLanguage();
        });
    }

    if ($('.ets-ac-lead-list-fields').length) {
        $(".ets-ac-lead-list-fields").sortable({
            items: '.lead-field-item:not(.group-fields)',
            update: function () {
                var sortData = [];
                $('.ets-ac-lead-list-fields .lead-field-item').each(function () {
                    sortData.push($(this).attr('data-id'));
                });
                $.ajax({
                    url: '',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        etsAcSortFormField: 1,
                        idForm: $('.ets-ac-lead-list-fields').attr('data-form-id'),
                        sortData: sortData
                    },
                    success: function (res) {
                        if (res.success) {
                            showSuccessMessage(res.message);
                        } else {
                            showErrorMessage(res.message);
                        }
                    }
                })
            }
        });
        $(".ets-ac-lead-list-fields").disableSelection();
    }

    //Range bubble input
    $(document).on('input change', '.ets-ac-range-input .range', function () {
        etsAcSetInputRange($(this));
    });

    $(document).on('change', '.mColorPickerInput', function () {
        var selectorChange = $(this).attr('data-selector-change');
        var attrChange = $(this).attr('data-attr-change');
        if (selectorChange && attrChange) {
            if ($(this).attr('name') == 'close_btn_color' || $(this).attr('name') == 'ETS_ABANCART_CLOSE_BTN_COLOR') {
                $(selectorChange).parent().find('style').remove();
                $(selectorChange).parent().append('<style>' + selectorChange + ':before,' + selectorChange + ':after{' + attrChange + ': ' + $(this).val() + ' !important;}</style>');
            } else {
                if ($('.ets_abancart_preview iframe').length && $('.ets_abancart_preview iframe').contents().find(selectorChange).length) {
                    $('.ets_abancart_preview iframe').contents().find(selectorChange).css(attrChange, $(this).val());
                } else {
                    if ($(this).attr('name') == 'ETS_ABANCART_OVERLAY_BG') {
                        var rgba = etsAcHexToRgb($(this).val());
                        if (rgba) {
                            var opacity = $('input[name="ETS_ABANCART_OVERLAY_BG_OPACITY"]').val();
                            var color = 'rgba(' + rgba.r + ',' + rgba.g + ',' + rgba.b + ',' + opacity + ')';
                            $(selectorChange).css(attrChange, color);
                        } else {
                            $(selectorChange).css(attrChange, $(this).val());
                        }
                    } else
                        $(selectorChange).css(attrChange, $(this).val());
                }
            }
        }

    });

    $(document).on('change', 'select[name="vertical_align"],select[name="ETS_ABANCART_VERTICLE_ALIGN"]', function () {
        var value = $(this).val();
        if ($('.ets_abancart_preview iframe').length) {
            $('.ets_abancart_preview iframe').contents().find('body').css('text-align', value);
            $('.ets_abancart_preview iframe').contents().find('a,p,div:not(.ets_abancart_product_list_table)').css('text-align', 'inherit');
        } else {
            $('.ets_abancart_preview').css('text-align', value);
            $('.ets_abancart_preview p,.ets_abancart_preview a, .ets_abancart_preview div:not(.ets_abancart_product_list_table )').css('text-align', 'inherit');
        }
    });

    $(document).on('click', '.ets-ac-hide-range-time-tool', function () {
        $(this).closest('.ets-ac-range-time-tool').addClass('hide');
        $(this).closest('.form-group').find('.ets-ac-show-range-time-tool').removeClass('hide');
        $(this).closest('.ets-ac-range-time-tool').prev('.input-group').removeClass('hide');
    });
    $(document).on('click', '.ets-ac-show-range-time-tool', function () {
        $(this).closest('.form-group').find('.input-group').addClass('hide');
        $(this).closest('.form-group').find('.ets-ac-range-time-tool').removeClass('hide');
        $(this).addClass('hide');
        return false;
    });
    $(document).on('change', '.form-group.abancart input[name="day"],.form-group.abancart input[name="minute"],.form-group.abancart input[name="second"],.form-group.abancart input[name="hour"],.form-group.abancart input[name="redisplay"]', function () {
        var value = $(this).val();
        if (value) {
            value = parseFloat(value);
        }
        var rangeItem = $(this).closest('.form-group').find('.ets-ac-range-time-tool input[type=range]');
        var rangeTitle = $(this).closest('.form-group').find('.max-number');
        var max = rangeItem.attr('max');
        if (max && typeof max !== 'undefined') {
            max = parseFloat(max);
        } else {
            max = 0;
        }
        if (max < value) {
            max = value * 2;
            rangeItem.attr('max', max);
            rangeTitle.html(max);
        }
        rangeItem.val(value);
        etsAcSetBubble(rangeItem);
    });

    if ($('.ets-ac-form-alias').length) {
        var etsAcFormHasAlias = false;
        if ($('.ets-ac-form-alias').first().val()) {
            etsAcFormHasAlias = true;
        }
        var etsAcTimeout11 = null;
        $(document).on('keyup', '.ets-ac-form-title', function () {
            clearTimeout(etsAcTimeout11);
            var $this = $(this);
            etsAcTimeout11 = setTimeout(function () {
                var splitId = $this.attr('id').split('_');
                var id_lang = splitId[splitId.length - 1];
                if (!etsAcFormHasAlias) {
                    $('.ets-ac-form-alias[name=alias_' + id_lang + ']').val(str2url($this.val()));
                    $('.ets-ac-form-alias[name=alias_' + id_lang + ']').change();
                }
            }, 200);
        });
    }
    if ($('input[name^="thankyou_page_title"]').length) {
        var etsAcFormTpHasAlias = false;
        if ($('input[name^="thankyou_page_title"]').first().val()) {
            etsAcFormTpHasAlias = true;
        }
        var etsAcTimeout22 = null;
        $(document).on('keyup', 'input[name^="thankyou_page_title"]', function () {
            clearTimeout(etsAcTimeout22);
            var $this = $(this);
            etsAcTimeout22 = setTimeout(function () {
                var splitId = $this.attr('id').split('_');
                var id_lang = splitId[splitId.length - 1];
                if (!etsAcFormTpHasAlias) {
                    $('input[name="thankyou_page_alias_' + id_lang + '"]').val(str2url($this.val()));
                    $('input[name="thankyou_page_alias_' + id_lang + '"]').change();
                }
            }, 200);
        });
    }

    $(document).on('click', '.translatable-field .dropdown-menu a', function () {
        var id_lang = $(this).attr('href').replace('javascript:hideOtherLanguage(', '').replace(');', '');
        if (id_lang && id_lang.match(/[0-9]+/)) {
            if ($('.ets-ac-form-alias').length) {
                $('.ets-ac-desc-link-lead').addClass('hide');
                $('.ets-ac-desc-link-tp').addClass('hide');
                if ($('.ets-ac-form-alias[name="alias_' + id_lang + '"]').val())
                    $('.ets-ac-desc-link-lead[data-lang="' + id_lang + '"]').removeClass('hide');
                if ($('input[name="thankyou_page_alias_' + id_lang + '"]').val())
                    $('.ets-ac-desc-link-tp[data-lang="' + id_lang + '"]').removeClass('hide');
            }
        }
    });

    $(document).on('change keyup', '.ets-ac-form-alias', function () {
        var splitId = $(this).attr('id').split('_');
        var id_lang = splitId[splitId.length - 1];
        if ($(this).val()) {
            $('.ets-ac-desc-link-lead[data-lang="' + id_lang + '"]').removeClass('hide').find('.alias-link').html($(this).val());
        } else {
            $('.ets-ac-desc-link-lead[data-lang="' + id_lang + '"]').addClass('hide').find('.alias-link').html('');
        }
    });
    $(document).on('change keyup', 'input[name^="thankyou_page_alias"]', function () {
        var splitId = $(this).attr('id').split('_');
        var id_lang = splitId[splitId.length - 1];
        if ($(this).val()) {
            $('.ets-ac-desc-link-tp[data-lang="' + id_lang + '"]').removeClass('hide').find('.alias-link').html($(this).val());
        } else {
            $('.ets-ac-desc-link-tp[data-lang="' + id_lang + '"]').addClass('hide').find('.alias-link').html('');
        }
    });
    $(document).on('change', 'input[name="enable_count_down_clock"]', function () {
        if ($(this).val() == 1) {
            $('.ets_abancart_short_code.discount_count_down_clock').removeClass('hide');
        } else {
            $('.ets_abancart_short_code.discount_count_down_clock').addClass('hide');
        }
    });
    if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACReminderLeave') {
        if ($('input[name="ETS_ABANCART_ENABLE_COUNTDOWN_CLOCK"]:checked').val() == 1) {
            $('.ets_abancart_short_code.discount_count_down_clock').removeClass('hide');
        } else {
            $('.ets_abancart_short_code.discount_count_down_clock').addClass('hide');
        }
        $('.ets-ac-range-input input[type="range"]').each(function () {
            etsAcSetBubble($(this));
        });
        $('.ets_abancart_short_code.countdown_clock').show();
        if ($('select[name="ETS_ABANCART_VERTICLE_ALIGN"]').length) {
            $('select[name="ETS_ABANCART_VERTICLE_ALIGN"]').change();
        }
        etsAcToggleTabContentDesign('content');
    }

    if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACEmailTemplate') {
        etsAcToggleTabContentDesign('content');
    }
    if (ETS_AC_ADMIN_CONTROLLER == 'AdminEtsACMailQueue' && $('.ets_ac_mail_queue_form').length) {
        var iframeElQueue = $('<iframe class="iframe-view-email-queue" onload="etsAcResizeIframe(this)"></iframe>');
        var contentEmailQueue = $('.ets_ac_mail_queue_form .ets-abancart-content .form-wrapper textarea').val();
        contentEmailQueue = contentEmailQueue.replace(/\{shop_logo\}/g, ETS_AC_LOGO_LINK);
        $('.ets_ac_mail_queue_form .ets-abancart-content .form-wrapper').html(iframeElQueue);
        var iFrameEmailQueue = iframeElQueue[0].contentDocument || iframeElQueue[0].contentWindow.document;
        iFrameEmailQueue.write(contentEmailQueue);
        iFrameEmailQueue.close();
    }

    $(document).on('click', '.ets-ac-content-design-tab .tab-menu-item', function () {
        etsAcToggleTabContentDesign($(this).attr('data-tab'));
    });

    $(document).on('click', '#table-ets_abancart_campaign a.delete', function () {
        var confirmMsg = $(this).attr('data-confirm');
        if (confirmMsg && !confirm(confirmMsg.replace(/\\n/g, ' '))) {
            return false;
        }
        return true;
    });
    $(document).on('click', '.ets-rv-clean-log', function (e) {
        if (!confirm(ETS_ABANCART_CLEAN_LOG_CONFIRM))
            e.preventDefault();
    })
});

function etsAcHideOtherLanguage(id_lang) {
    $('.trans_field').addClass('hidden');
    $('.trans_field_' + id_lang).removeClass('hidden');
}

function etsAcGetRandInt(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

function etsAcSetIframeviewEmailTemp() {
    var iframeEl = $('<iframe class="iframe-view-email-template" onload="etsAcResizeIframe(this)"></iframe>');
    var contentEmail = $('.ets_abancart_preview.view_email_template').html();
    $('.ets_abancart_preview.view_email_template').html(iframeEl);
    var iFrameEmail = iframeEl[0].contentDocument || iframeEl[0].contentWindow.document;
    iFrameEmail.write(ets_ab_fn.doShortCode(contentEmail, $('.ets_abancart_preview.view_email_template').attr('data-type')));
    iFrameEmail.close();
}

function etsAcInitSearchSpecificProduct() {
    $('.ets_ac_specific_product_filter').autocomplete(ETS_ABANCART_CAMPAIGN_URL + '&ajax=1&action=searchProduct&time=' + new Date().getTime(), {
        resultsClass: 'ac_results ets_ac_result_autocomplete_on_popup',
        delay: 100,
        minChars: 1,
        autoFill: true,
        max: 20,
        matchContains: true,
        mustMatch: true,
        scroll: false,
        cacheLength: 0,
        multipleSeparator: '||',
        formatItem: function (item) {
            return '<span data-id="' + item[0] + '"><img src="' + item[3] + '" title="' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '" width="32"/>' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '</span>';
        }
    }).result(function (event, item) {
        if (item == null)
            return false;

        if ($('input[name="reduction_product"]').length)
            $('input[name="reduction_product"]').val(item[0]);
        else {
            $('input[name="ETS_ABANCART_REDUCTION_PRODUCT"]').val(item[0]);
        }
        $('.ets_ac_specific_product_filter').prop('disabled', true);
        //$('input[name="reduction_product"]').prev().val(item[1]);
        //$('input[name="reduction_product"]').prev().val('');

        //New code
        var productId = item[0];
        var productName = item[1] + (item[2] ? ' (' + item[2] + ')' : '');
        var productImage = item[3];
        var productLink = item[4];
        if ($('input[name="reduction_product"]').length)
            $('#ets-ac-products-list-reduction_product').html('<li class="product" data-id="' + productId + '"><input type="hidden" name="selected_specific_product[]" value="' + productId + '"/> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a></li>').addClass('has_content');
        else
            $('#ets-ac-products-list-ETS_ABANCART_REDUCTION_PRODUCT').html('<li class="product" data-id="' + productId + '"><input type="hidden" name="" value="' + productId + '"/> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a></li>').addClass('has_content');
    });
}

function etsAcInitSearchGiftProduct() {
    $('.ets_ac_gift_product_filter').autocomplete(ETS_ABANCART_CAMPAIGN_URL + '&ajax=1&action=searchProduct&getAttribute=1&time=' + new Date().getTime(), {
        resultsClass: 'ac_results ets_ac_result_autocomplete_on_popup',
        delay: 100,
        minChars: 1,
        autoFill: true,
        max: 20,
        matchContains: true,
        mustMatch: true,
        scroll: false,
        cacheLength: 0,
        multipleSeparator: '||',
        formatItem: function (item) {
            return '<span data-id="' + item[0] + '"><img src="' + item[3] + '" title="' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '" width="32"/>' + item[1] + ' ' + item[6] + ' ' + (item[2] ? ' (' + item[2] + ')' : '') + '</span>';
        }
    }).result(function (event, item) {
        if (item == null || $('input[name="ETS_ABANCART_GIFT_PRODUCT"]').val() || $('input[name="gift_product"]').val()) {
            return false;
        }
        if ($('input[name="gift_product"]').length) {
            $('input[name="gift_product"]').val(item[0]);
            $('input[name="gift_product_attribute"]').val(item[5]);
        } else {
            $('input[name="ETS_ABANCART_GIFT_PRODUCT"]').val(item[0]);
            $('input[name="ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE"]').val(item[5]);
        }
        $('.ets_ac_gift_product_filter').prop('disabled', true);
        //$('input[name="gift_product"]').prev().val(item[1]+' '+item[6]);
        //$('input[name="gift_product"]').prev().val('');

        var productId = item[0];
        var productName = item[1] + ' ' + item[6];
        var productImage = item[3];
        var productLink = item[4];
        if ($('#ets-ac-products-list-product_gift').length)
            $('#ets-ac-products-list-product_gift').html('<li class="product" data-id="' + productId + '"><input type="hidden" name="gift_selected_product[]" value="' + productId + '"/> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a></li>');
        else
            $('#ets-ac-products-list-ETS_ABANCART_PRODUCT_GIFT').html('<li class="product" data-id="' + productId + '"><input type="hidden" name="" value="' + productId + '"/> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a></li>');
    });
}

function etsAcInitSearchMultipleProduct() {

    $('.ets_ac_selected_product_filter').autocomplete(ETS_ABANCART_CAMPAIGN_URL + '&ajax=1&action=searchProduct&time=' + new Date().getTime(), {
        resultsClass: 'ac_results ets_ac_result_autocomplete_on_popup',
        delay: 100,
        minChars: 1,
        autoFill: true,
        max: 20,
        matchContains: true,
        mustMatch: true,
        scroll: false,
        cacheLength: 0,
        multipleSeparator: '||',
        formatItem: function (item) {
            return '<span data-id="' + item[0] + '"><img src="' + item[3] + '" title="' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '" width="64"/>' + item[1] + (item[2] ? ' (' + item[2] + ')' : '') + '</span>';
        }
    }).result(function (event, item) {
        if (item == null)
            return false;

        var productId = item[0];
        var productName = item[1] + (item[2] ? ' (ref:' + item[2] + ')' : '');
        var productImage = item[3];
        var productLink = item[4];

        if (!$('#ets-ac-products-list-selected_product .product[data-id="' + productId + '"]').length) {
            if ($('#ets-ac-products-list-selected_product').length)
                $('#ets-ac-products-list-selected_product').append('<li class="product" data-id="' + productId + '"><input type="hidden" name="selected_product[]" value="' + productId + '"/> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button></li>');
            else
                $('#ets-ac-products-list-ETS_ABANCART_SELECTED_PRODUCT').append('<li class="product" data-id="' + productId + '"><input type="hidden" name="ETS_ABANCART_SELECTED_PRODUCT[]" value="' + productId + '"/> <img src="' + productImage + '" style="width:32px;"/> <a href="' + productLink + '" target="_blank">' + productName + '</a> <button class="btn btn-default del_product_search" type="button"><i class="icon-remove text-danger"></i></button></li>');
        }

    });
}

function etsAcInitHideTimingOptions() {
    $('.label_email_timing_option').addClass('hide');
    $('.ets_ac_send_repeat_email').addClass('hide');
    $('.ets_ac_customer_email_schedule_time').addClass('hide');
    $('.ets_ac_customer_email_register_order').addClass('hide');
}

function etsAcChangeOptionCustomerReminder(timing_option, displayFrequency) {
    if (displayFrequency)
        $('.abancart.form_frequency').removeClass('hide');
    var shortCodeCustomer = ['registration_date', 'last_order_id', 'last_order_reference', 'last_order_total', 'order_total', 'last_time_login_date'];
    $.each(shortCodeCustomer, function (i, el) {
        $('.ets_abancart_short_code.' + el).addClass('hide');
    });
    $('.ets_ac_step_hidden .ets_abancart_tab_item').addClass('ets_abancart_tab_item_copy').removeClass('ets_abancart_tab_item');
    if (timing_option.length < 1) {
        return false;
    }
    etsAcInitHideTimingOptions();
    var customerMailOptionValue = parseInt(timing_option.val());
    if (customerMailOptionValue === 1) {
        $('.label_email_timing_option.register').removeClass('hide');
        $('.ets_abancart_short_code.registration_date').removeClass('hide');
        $('.ets_ac_customer_email_register_order').removeClass('hide');
        $('.form-group.customer_group').addClass('hide');
        $('.form-group.countries').addClass('hide');
        $('.form-group.has_placed_orders').addClass('hide');
    } else if (customerMailOptionValue === 5) {
        $('.ets_ac_customer_email_register_order').removeClass('hide');
        $('.label_email_timing_option.register_newsletter').removeClass('hide');
        $('.ets_abancart_short_code.registration_date').removeClass('hide');
    } else if (customerMailOptionValue === 2) {
        $('.ets_ac_customer_email_register_order').removeClass('hide');
        $('.ets_ac_send_repeat_email').removeClass('hide');
        $('.label_email_timing_option.order').removeClass('hide');
        $('.ets_abancart_short_code.last_order_id').removeClass('hide');
        $('.ets_abancart_short_code.last_order_reference').removeClass('hide');
        $('.ets_abancart_short_code.last_order_total').removeClass('hide');
        $('.ets_abancart_short_code.order_total').removeClass('hide');
    } else if (customerMailOptionValue === 3) {
        $('.ets_ac_customer_email_schedule_time').removeClass('hide');
        $('.ets_ac_customer_email_register_order').addClass('hide');
    } else if (customerMailOptionValue === 6) {
        $('.ets_ac_send_repeat_email').removeClass('hide');
        $('.label_email_timing_option.last_login').removeClass('hide');
        $('.ets_ac_customer_email_register_order').removeClass('hide');
        $('.ets_abancart_short_code.last_time_login_date').removeClass('hide');
    }
    if (customerMailOptionValue !== 1) {
        $('.form-group.customer_group').removeClass('hide');
        $('.form-group.countries').removeClass('hide');
        $('.form-group.has_placed_orders').removeClass('hide');
    }
}

function etsAcSetDefaultContentPopup() {
    var pagesAllows = ['AdminEtsACReminderPopup', 'AdminEtsACReminderBar', 'AdminEtsACReminderBrowser', 'AdminEtsACReminderLeave', 'AdminEtsACCampaign'];
    if (pagesAllows.indexOf(ETS_AC_ADMIN_CONTROLLER) === -1) {
        return false;
    }
    var discountOption = 'discount_option', hasShoppingCart = parseInt($('#has_shopping_cart').val()), reset = false;
    if (ETS_AC_ADMIN_CONTROLLER === 'AdminEtsACReminderLeave') {
        discountOption = 'ETS_ABANCART_DISCOUNT_OPTION';
        hasShoppingCart = parseInt($('#ETS_ABANCART_HAS_PRODUCT_IN_CART').val());
        reset = true;
    }
    if (hasShoppingCart !== 1) {
        etsAcSetTitle('.ets_ac_default_title_no_product_in_cart', reset);
        etsAcSetContent('.ets_ac_default_content_no_product_in_cart', reset);
    } else {
        if ($('input[name="' + discountOption + '"]:checked').val() === 'no') {
            etsAcSetTitle('.ets_ac_default_title_no_discount', reset);
            etsAcSetContent('.ets_ac_default_content_no_discount', reset);
        } else {
            etsAcSetTitle('.ets_ac_default_title_has_discount', reset);
            etsAcSetContent('.ets_ac_default_content_has_discount', reset);
        }
    }
    ets_ab_fn.previewLanguage();
}

function etsAcSetTitle(title, reset) {
    var eleTitle = $('input[id^="title_"]');
    if (eleTitle.length > 0 && $(title).length > 0) {
        eleTitle.each(function () {
            if (!$(this).val().trim() || reset) {
                $(this).val($(title).val());
            }
        });
    }
}

function etsAcSetContent(content, reset) {
    var eleContent = $('[id^="content_"], textarea[id^="ETS_ABANCART_CONTENT_"]');
    if (eleContent.length > 0 && $(content).length > 0) {
        eleContent.each(function () {
            if (typeof tinyMCE !== 'undefined' && typeof tinyMCE.get($(this).attr('id')) !== 'undefined' && tinyMCE.get($(this).attr('id')) !== null) {
                var currentContent = tinyMCE.get($(this).attr('id')).getContent();
                if (!currentContent.trim() || reset) {
                    tinyMCE.get($(this).attr('id')).setContent($(content).val());
                    tinyMCE.triggerSave();
                }
            } else if (!$(this).val().trim() || reset) {
                $(this).val($(content).val());
            }
        });
    }
}

function etsAcSetInputRange(inputRange) {
    if (inputRange.length < 1)
        return;

    etsAcSetBubble(inputRange);

    var value = inputRange.val();
    var nameInput = inputRange.attr('name');

    if (nameInput == 'overlay_bg' || nameInput == 'overlay_bg_opacity' || nameInput == 'ETS_ABANCART_OVERLAY_BG' || nameInput == 'ETS_ABANCART_OVERLAY_BG_OPACITY') {
        var color = $('input[name="overlay_bg"]').val();
        var opacity = $('input[name="overlay_bg_opacity"]').val();
        if ($('input[name="ETS_ABANCART_OVERLAY_BG"]').length) {
            opacity = $('input[name="ETS_ABANCART_OVERLAY_BG_OPACITY"]').val();
            color = $('input[name="ETS_ABANCART_OVERLAY_BG"]').val();
        }
        var rgba = etsAcHexToRgb(color);
        if (rgba) {
            value = 'rgba(' + rgba.r + ',' + rgba.g + ',' + rgba.b + ',' + opacity + ')';
        }
    }

    if (inputRange.hasClass('for-target-name')) {
        var nameTarget = inputRange.attr('data-name-target');
        if (nameTarget) {
            $('input[name="' + nameTarget + '"]').val(value);
        }
    }

    var selectorChange = inputRange.attr('data-selector-change');
    var attrChange = inputRange.attr('data-attr-change');

    if (selectorChange && attrChange) {
        var attrHasPx = ['with', 'height', 'border-radius', 'border-width', 'font-size', 'padding', 'margin'];
        var unit = '';
        if (attrHasPx.indexOf(attrChange) !== -1) {
            unit = 'px';
        }
        if (attrChange == 'border-width') {
            $(selectorChange).css('border-style', 'solid');
        }
        if ($('.ets_abancart_preview iframe').length > 0 && $('.ets_abancart_preview iframe').contents().find(selectorChange).length) {
            $('.ets_abancart_preview iframe').contents().find(selectorChange).css(attrChange, value + unit);
        } else {
            $(selectorChange).css(attrChange, value + unit);
            if (attrChange == 'height')
                $(selectorChange).css('min-height', value + unit);
        }

        if ($(selectorChange).find('iframe').length && (attrChange == 'width' || attrChange == 'height')) {
            etsAcResizeIframe($(selectorChange).find('iframe')[0]);
        }
    }
}

function etsAcSetBubble(range) {
    //var bubble = range.prev('.range-bubble');
    //var bubble_bar = range.parent('.ets-ac-range-input').find('.range-bubble-bar');
    var targetName = range.attr('data-name-target');
    var val = $('#' + targetName).val();
    var min = 0;
    var unit = '';
    if (range.attr('data-unit')) {
        unit = range.attr('data-unit');
    }
    if (range.attr('min'))
        min = range.attr('min');
    var max = 0;
    if (range.attr('max'))
        max = range.attr('max');
    if (!val || val == '0') {
        val = 0;
    }
    if (parseFloat(max) < parseFloat(val)) {
        max = parseFloat(val) * 2;
        $(range).attr('max', max);
        $(range).closest('.ets_range_input').find('.max-number').html(max);
    }
    const newVal = Number(((val - min) * 100) / (max - min));
    $(range).prev('.range-bubble')
        .css('left', 'calc(' + newVal + '% + (' + (10 - newVal * 0.27) + 'px))')
        .html('<span>' + val + unit + '</span>')
    ;
    $(range).parent('.ets-ac-range-input')
        .find('.range-bubble-bar')
        .css('width', 'calc(' + newVal + '% + (' + (10 - newVal * 0.2) + 'px))');
}

function etsAcStripTags(htmlText) {
    htmlText = htmlText.replace(/&times;/g, '').replace(/×/g, '');
    var div = document.createElement("div");
    div.innerHTML = htmlText;
    return (div.textContent || div.innerText || "").replace(/&times;/g, '');
}

function etsAcResizeIframe(obj) {
    var pHeight = $(obj).parent().height();
    if (obj.contentWindow.document.documentElement.scrollHeight > pHeight) {
        obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
    } else {
        obj.style.height = ($(obj).parent().height() ?? 500) + 'px';
    }
}

function etsAcFormatTimeNumber(number) {
    if (parseInt(number) < 10) {
        return '0' + number;
    }
    return number;
}

function etsAcHexToRgb(hex) {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, function (m, r, g, b) {
        return r + r + g + g + b + b;
    });

    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function etsAcToggleTabContentDesign(tab) {
    if (!$('#ets_abancart_reminder_form .ets_ac_config_popup_item').length && !$('.adminetsacreminderleave .ets_ac_config_popup_item').length && !$('.adminetsacemailtemplate .ets_ac_config_popup_item').length) {
        return false;
    }

    var prefixSelector = '#ets_abancart_reminder_form';
    if ($('.adminetsacreminderleave').length) {
        prefixSelector = '.adminetsacreminderleave';
    } else if ($('.adminetsacemailtemplate').length) {
        prefixSelector = '.adminetsacemailtemplate';
    }
    $('.ets-ac-content-design-tab .tab-menu-item').removeClass('active');
    $('.ets-ac-content-design-tab .tab-menu-item[data-tab="' + tab + '"]').addClass('active');
    $(prefixSelector + ' .ets_ac_config_popup_content').removeClass('tabShow').addClass('tabHide');
    $(prefixSelector + ' .ets_ac_config_popup_item').removeClass('tabShow').addClass('tabHide');
    if (tab == 'content') {
        $(prefixSelector + ' .ets_ac_config_popup_content').addClass('tabShow');
        $(prefixSelector + ' .ets_ac_config_popup_content').removeClass('tabHide');
    } else if (tab == 'design') {
        $(prefixSelector + ' .ets_ac_config_popup_item').addClass('tabShow');
        $(prefixSelector + ' .ets_ac_config_popup_item').removeClass('tabHide');
    }
}