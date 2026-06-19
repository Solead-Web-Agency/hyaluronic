/**
 * This is main js file. Don't edit the file if you want to update module in future.
 * 
 * @author    Globo Software Solution JSC <contact@globosoftware.net>
 * @copyright 2020 Globo JSC
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

function loadOwlSlider() {
    if ($('.owl_slider').length > 0) {
        max_item = 4;
        if ($('body#cart').length > 0) max_item = 3;
        $('.owl_slider').each(function() {
            if (!$(this).hasClass('owl-loaded'))
                $(this).owlCarousel({
                    loop: true,
                    margin: 10,
                    responsiveClass: true,
                    navText: [($('#prevtitle').length > 0 ? $('#prevtitle').html() : 'Prev'), ($('#nexttitle').length > 0 ? $('#nexttitle').html() : 'Next')],
                    responsive: {
                        0: {
                            items: 1,
                            nav: true
                        },
                        320: {
                            items: 1,
                            nav: true
                        },
                        480: {
                            items: 2,
                            nav: true,
                            loop: false
                        },
                        768: {
                            items: 2,
                            nav: true,
                            loop: false
                        },
                        992: {
                            items: 3,
                            nav: true,
                            loop: false
                        },
                        1200: {
                            items: max_item,
                            nav: true,
                            loop: false
                        }
                    }
                });
        });
    }
}

function loadFancybox() {
    if ($('.gupsell-popup').length > 0) {
        $('.gupsell-popup').each(function() {
            $(this).fancybox({
                'closeBtn': false,
                'padding': 0,
                'wrapCSS': 'fancybox_gupsellpro_style',
                'hideOnOverlayClick': false,
                'closeEffect': 'elastic',
                'hideOnContentClick': false,
                'beforeLoad': function() {
                    $('.gpopup-conten').addClass('pendding bootstrap');
                },
                helpers: {
                    overlay: { closeClick: false, locked: false }
                }
            });
        });
    }
}

$(document).ready(function() {
    Gupsellpro.Checkproductalls('');
    loadOwlSlider();
    loadFancybox();
    if ($('.upsell-jsoninput').length > 0) {
        if ($('.upsell-jsoninput .upsellfields-popup').val() != '') {
            var upsellfieldpopups = JSON.parse($('.upsell-jsoninput .upsellfields-popup').val());
            $.each(upsellfieldpopups, function(i, item) {
                if ($('.popup-product-box').length > 0 && $('.popup-product-box #gupsellpropopup-' + item).length > 0) {
                    setTimeout(function() {
                        $('.popup-product-box').find('a[href="#gupsellpropopup-' + item + '"]').trigger('click');
                        var previous_number = parseInt(i) - 1;
                        var next_number = parseInt(i) + 1;
                        if ((typeof(upsellfieldpopups[previous_number]) != "undefined" && upsellfieldpopups[previous_number] !== null) || (typeof(upsellfieldpopups[next_number]) != "undefined" && upsellfieldpopups[next_number] !== null)) {

                            if (typeof(upsellfieldpopups[previous_number]) != "undefined" && upsellfieldpopups[previous_number] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-back" data-id="' + item + '">' + $('.upsellfields-popup-titleback').val() + '</div>');
                                }
                            } else if (typeof(upsellfieldpopups[next_number]) != "undefined" && upsellfieldpopups[next_number] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-next" data-id="' + item + '">' + $('.upsellfields-popup-titlenext').val() + '</div>');
                                }
                            }
                        }
                    }, parseInt($('.upsellfields-time-show').val()) * 1000);
                    return false;
                }
            });
        }
        if ($('.upsell-jsoninput .upsellfields-floating').val() != '') {
            var upsellfieldfloatings = JSON.parse($('.upsell-jsoninput .upsellfields-floating').val());
            $.each(upsellfieldfloatings, function(i, item) {
                if ($('.floating-product-box').length > 0 && $('#gupsellprofloating-' + item).length > 0) {
                    $('#gupsellprofloating-' + item).addClass('active');
                    setTimeout(function() {
                        if ($('#gupsellprofloating-' + item).find('.floating-button-text1.active').length > 0) {
                            $('#gupsellprofloating-' + item).find('.floating-button-text1.active').trigger('click');
                        }
                        var previous_number = parseInt(i) - 1;
                        var next_number = parseInt(i) + 1;
                        if ((typeof(upsellfieldfloatings[previous_number]) != "undefined" && upsellfieldfloatings[previous_number] !== null) || (typeof(upsellfieldfloatings[next_number]) != "undefined" && upsellfieldfloatings[next_number] !== null)) {
                            $('#gupsellprofloating-' + item).find('.upsellfloating-prev-action').addClass('active');
                            if (typeof(upsellfieldfloatings[previous_number]) != "undefined" && upsellfieldfloatings[previous_number] !== null) {
                                $('#gupsellprofloating-' + item).find('.upsellfloating-prev-back').addClass('active');
                            }
                            if (typeof(upsellfieldfloatings[next_number]) != "undefined" && upsellfieldfloatings[next_number] !== null) {
                                $('#gupsellprofloating-' + item).find('.upsellfloating-prev-next').addClass('active');
                            }
                        }
                    }, parseInt($('.upsellfields-time-show').val()) * 1000);
                    return false;
                }
            });
        }
    }
    $(document).on('click', '.gupsellpropopup-prev-back', function(e) {
        e.preventDefault();
        var id_old = $(this).data('id');
        Gupsellpro.popupload(id_old, 'back');
    });
    $(document).on('click', '.gupsellpropopup-prev-next', function(e) {
        e.preventDefault();
        var id_old = $(this).data('id');
        Gupsellpro.popupload(id_old, 'next');
    });

    $(document).on('click', '.upsellfloating-prev-next', function(e) {
        e.preventDefault();
        var id_old = $(this).closest('.upsellfloating-prev-action').data('id');
        Gupsellpro.floatingload(id_old, 'next');

    });
    $(document).on('click', '.upsellfloating-prev-back', function(e) {
        e.preventDefault();
        var id_old = $(this).closest('.upsellfloating-prev-action').data('id');
        Gupsellpro.floatingload(id_old, 'back');

    });
    $(document).on('click', '.gupsell-qty .product_quantity_up', function(e) {
        e.preventDefault();
        Gupsellpro.upQTY($(this), 'up');
    });
    $(document).on('click', '.gupsell-qty .product_quantity_down', function(e) {
        e.preventDefault();
        Gupsellpro.upQTY($(this), 'down');
    });

    $(document).on('change', '.gupsell-qty input.gupsell_quantity_wanted', function(e) {
        Gupsellpro.upQTY($(this), '');
    });

    $(document).on('click', '.gupsell-qty-bundle .product_quantity_up', function(e) {
        e.preventDefault();
        var parent_box = $(this).parents('.gupsellpro-box');
        var number = parseInt($(this).closest('.extra-touchspin').find('.gupsell_quantity_wanted').val());
        if ($(this).attr('data-field-17') == '17') {
            number = parseInt(number) + 1;
        }
        parent_box.find('.gupsell_quantity_wanted').each(function() {
            $(this).val(number);
        });
        Gupsellpro.Checkproductalls('');
    });
    $(document).on('click', '.gupsell-qty-bundle .product_quantity_down', function(e) {
        e.preventDefault();
        var parent_box = $(this).parents('.gupsellpro-box');
        var number = parseInt($(this).closest('.extra-touchspin').find('.gupsell_quantity_wanted').val());
        if ($(this).attr('data-field-17') == '17') {
            number = parseInt(number) - 1;
        }
        if (number < 1) {
            number = 1;
        }
        parent_box.find('.gupsell_quantity_wanted').each(function() {
            $(this).val(number);
        });
        Gupsellpro.Checkproductalls('');
    });

    $(document).on('change', '.gupsell-qty-bundle input.gupsell_quantity_wanted', function(e) {
        var number = parseInt($(this).val());
        var parent_box = $(this).parents('.gupsellpro-box');
        if (parseInt(number) < 1) {
            number = 1;
        }
        parent_box.find('.gupsell_quantity_wanted').each(function() {
            $(this).val(number);
        });
        Gupsellpro.Checkproductalls('');
    });
    $(document).on('change', '.upsellcheckbox-sample-overlay', function() {
        var urlajax = currentIndex + "&token=" + token;
        var gupsell_idupsell_rule = $(this).closest('.gupsellbox-checkproducts').find('.gupsell-idupsell-rule').val();
        if ($(this).closest('.gupsellbox-checkproducts').find('.upsellcheckbox-sample-overlay:checked').length > 0) {
            var sampleproducts = [];
            $(this).closest('.gupsellbox-checkproducts').find('.upsellcheckbox-sample-overlay:checked').each(function() {
                var gupsell_id_product = $(this).data('idproduct');
                var gupsell_id_product_attribute = $(this).data('idattribute');
                var gupsell_qty = $(this).closest('.item').find('.gupsell_quantity_wanted').val();
                sampleproducts.push(gupsell_id_product + '|' + gupsell_id_product_attribute + '|' + gupsell_qty);
            });

            var gupsell_idproextra = parseInt($(this).closest('.item').data('numberkey'));
            var gupsell_showinpage = $(this).closest('.item').data('showinpage');
            var gupsell_type = $(this).closest('.item').data('type');
            volumes = $("#" + gupsell_showinpage + '_addition').val();
            var form = $('.AdminGupselloffers');
            var serializedForm = form.serialize();
            if (typeof sampleproducts !== 'undefined' && sampleproducts.length > 0)
                $.ajax({
                    type: 'POST',
                    url: urlajax + '&CheckproductsaddCart=1',
                    async: true,
                    cache: false,
                    dataType: "json",
                    data: serializedForm+'&gupsell_idupsell_rule=' + gupsell_idupsell_rule + '&sampleproducts=' + sampleproducts + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
                    beforeSend: function() {
                        $(this).addClass('disabled');
                    },
                    success: function(jsonData) {
                        if (!jsonData.error) {
                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gshow');
                            $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gshow');
                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .old-price.product-price').text(jsonData.totalprice);
                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .price-product-price').text(jsonData.totalprice_dc);
                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                            if (gupsell_type == 'volume')
                                if (jsonData.text_discount != '') {
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').show();
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').text(jsonData.text_discount);
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                                } else {
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').hide();
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').text(jsonData.text_discount);
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                                }
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    },
                    complete: function() { $(this).removeClass('disabled'); }
                });
            else {
                $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gnone');
                $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').removeClass('gshow');
                $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gnone');
                $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').removeClass('gshow');
            }
        } else {
            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gnone');
            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').removeClass('gshow');
            $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gnone');
            $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').removeClass('gshow');
        }

    });
    $(document).on('click', '.gupsell-close-fancybox-btn, .upsellpro-overlay-close', function() {
        $.fancybox.close();
    });
    $(document).on('click', '.floating-button-text1, .floating-button-text2', function() {
        var id_floating_box = $(this).attr('href');
        $(id_floating_box).find('.floating-button-text1').toggleClass('active');
        $(id_floating_box).find('.floating-button-text2').toggleClass('active');
        $(id_floating_box).find('.floating-product-box-show-product').toggleClass('active');
        if ($(this).hasClass('floating-button-text2')) {
            Gupsellpro.floatingload($(id_floating_box).find('.gupsellpro-box').data('gupsell-id'), 'none');
        } else {
            Gupsellpro.floatingload($(id_floating_box).find('.gupsellpro-box').data('gupsell-id'), 'check');
        }
    });
    $(document).on('change', '.gupsellpro-variants .form-control-select', function() {
        var id_upsellpro = $(this).closest('.gupsellpro-box').data('gupsell-id');
        var urlajax = currentIndex + "&token=" + token;
        var qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
        var id_product = $(this).closest('.item').data('product-id');
        var type_template = $(this).closest('.item').data('type');
        var itemhtml = $(this).closest('.item');
        var id_attribute = $(this).val();
        var form = $('.AdminGupselloffers');
        var serializedForm = form.serialize();

        gupsell_idproextra = parseInt($(this).closest('.item').data('numberkey'));
        gupsell_showinpage = $(this).closest('.item').data('showinpage');
        gupsell_type = $(this).closest('.item').data('type');
        volumes = $("#" + gupsell_showinpage + '_addition').val();
        $.ajax({
            type: 'POST',
            url: urlajax + '&Resetproduct=1',
            async: true,
            cache: false,
            dataType: "json",
            data: serializedForm + '&id_attribute=' + id_attribute + '&id_upsellpro=' + id_upsellpro + '&qty=' + qty + '&id_product=' + id_product + '&type_template=' + type_template + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
            beforeSend: function() {},
            success: function(jsonData) {
                if (!jsonData.error) {
                    itemhtml.html(jsonData.html);
                }
                Gupsellpro.Checkproductalls('');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
            }
        });
    });
    $(document).on('click', '.gupsell_ajax_delete_cart_button', function(e) {
        e.preventDefault();
        if (!$(this).hasClass('disabled')) {
            var a_button_this = $(this);
            var urlajax = currentIndex + "&token=" + token;
            var gupsell_idupsell_rule = $(this).closest('.gupsellpro-box').find('.gupsell-idupsell-rule').val();
            idCombination = $(this).attr('data-ipa');
            idProduct = $(this).attr('data-id-product');
            qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
            gupsellprodc = $(this).attr('data-gupsellprodc');
            remove_productin_cart = $(this).closest('.gupsellpro-box').find('.gupsell-remove-cart-product').val();
            var parent_box = $(this).parents('.gupsellpro-box');
            $.ajax({
                type: 'POST',
                url: urlajax + '&RemoveproductincartProduct=1',
                async: true,
                cache: false,
                dataType: "json",
                data: '&idProduct=' + idProduct + '&idCombination=' + idCombination + '&gupsell_idupsell_rule=' + gupsell_idupsell_rule,
                beforeSend: function() {
                    a_button_this.addClass('disabled');
                    a_button_this.find('span').hide();
                    a_button_this.append('<div class="gupselllds-ring"><div></div><div></div><div></div><div></div></div>');
                },
                success: function(jsonData) {},
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.responseText);
                },
                complete: function() {
                    a_button_this.removeClass('disabled');
                    a_button_this.addClass('gnone');
                    a_button_this.removeClass('gshow');
                    a_button_this.find('.gupselllds-ring').remove();
                    a_button_this.closest('.item').find('.gupsellpro-variants').removeClass('gnone_change');
                    a_button_this.closest('.item').find('.content_qty').removeClass('gnone_change');
                    a_button_this.find('span').show();
                    a_button_this.closest('.item').find('.gupsell_ajax_add_to_cart_button').addClass('gshow');
                    Gupsellpro.checkcartbuttonadd(parent_box);
                }
            });
        }
    });
    $(document).on('click', '.gupsell_ajax_add_to_cart_button', function(e) {
        e.preventDefault();
        if (!$(this).hasClass('disabled')) {
            var a_button_this = $(this);
            var urlajax = currentIndex + "&token=" + token;
            var gupsell_idupsell_rule = $(this).closest('.gupsellpro-box').find('.gupsell-idupsell-rule').val();
            idCombination = $(this).attr('data-ipa');
            idProduct = $(this).attr('data-id-product');
            qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
            gupsellprodc = $(this).attr('data-gupsellprodc');
            remove_productin_cart = $(this).closest('.gupsellpro-box').find('.gupsell-remove-cart-product').val();
            var parent_box = $(this).parents('.gupsellpro-box');
            if (parent_box.hasClass('gps17')) {
                urlajax_cart = parent_box.find('.gupsell_action_link').val();
                static_token = parent_box.find('.gupsell_cart_token').val();
                if (remove_productin_cart > 0) {
                    $.ajax({
                        type: 'POST',
                        url: urlajax + '?Removeproductincart=1',
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule,
                        success: function(jsonData) {
                            if (!jsonData.error) {
                                $.ajax({
                                    type: 'POST',
                                    headers: { "cache-control": "no-cache" },
                                    url: urlajax_cart,
                                    async: true,
                                    cache: false,
                                    dataType: "json",
                                    data: 'action=update&update=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule + '&remove_productin_cart=' + remove_productin_cart,
                                    beforeSend: function() {
                                        a_button_this.addClass('disabled');
                                        a_button_this.find('span').hide();
                                        a_button_this.append('<div class="gupselllds-ring"><div></div><div></div><div></div><div></div></div>');
                                    },
                                    success: function(jsonData1) {
                                        if ($('.upsell-jsoninput .upsellfields-page_name').val() == 'cart') {
                                            location.reload();
                                        }
                                        /* else {
                                        Gupsellpro.refreshcart(urlajax_cart);
                                        }*/
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                                        alert(XMLHttpRequest.responseText);
                                    },
                                    complete: function() {
                                        a_button_this.removeClass('disabled');
                                        a_button_this.addClass('gnone');
                                        a_button_this.removeClass('gshow');
                                        a_button_this.find('span').show();
                                        a_button_this.find('.gupselllds-ring').remove();
                                        a_button_this.closest('.item').find('.gupsellpro-variants').addClass('gnone_change');
                                        a_button_this.closest('.item').find('.content_qty').addClass('gnone_change');
                                        a_button_this.closest('.item').find('.gupsell_ajax_delete_cart_button').addClass('gshow');
                                        Gupsellpro.checkcartbuttonadd(parent_box);
                                    }
                                });
                            } else {
                                alert(jsonData.warrning);
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                    });
                } else {
                    $.ajax({
                        type: 'POST',
                        headers: { "cache-control": "no-cache" },
                        url: urlajax_cart,
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: 'action=update&update=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule,
                        beforeSend: function() {
                            a_button_this.addClass('disabled');
                            a_button_this.find('span').hide();
                            a_button_this.append('<div class="gupselllds-ring"><div></div><div></div><div></div><div></div></div>');
                        },
                        success: function(jsonData) {
                            a_button_this.removeClass('disabled');
                            if ($('.upsell-jsoninput .upsellfields-page_name').val() == 'cart') {
                                location.reload();
                            }
                            /* else {
                            Gupsellpro.refreshcart(urlajax_cart);
                            }*/
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() {
                            a_button_this.removeClass('disabled');
                            a_button_this.addClass('gnone');
                            a_button_this.removeClass('gshow');
                            a_button_this.find('span').show();
                            a_button_this.find('.gupselllds-ring').remove();
                            a_button_this.closest('.item').find('.gupsellpro-variants').addClass('gnone_change');
                            a_button_this.closest('.item').find('.content_qty').addClass('gnone_change');
                            a_button_this.closest('.item').find('.gupsell_ajax_delete_cart_button').addClass('gshow');
                            Gupsellpro.checkcartbuttonadd(parent_box);
                        }
                    });
                }
            } else {
                urlajax_cart = baseUri + '?rand=' + new Date().getTime();
                if (remove_productin_cart > 0) {
                    $.ajax({
                        type: 'POST',
                        url: urlajax + '?Removeproductincart=1',
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule,
                        beforeSend: function() {
                            $(this).addClass('disabled');
                        },
                        success: function(jsonData) {
                            if (!jsonData.error) {
                                $.ajax({
                                    type: 'POST',
                                    headers: { "cache-control": "no-cache" },
                                    url: urlajax_cart,
                                    async: true,
                                    cache: false,
                                    dataType: "json",
                                    data: 'controller=cart&add=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule + '&remove_productin_cart=' + remove_productin_cart,
                                    beforeSend: function() {
                                        a_button_this.addClass('disabled');
                                        a_button_this.find('span').hide();
                                        a_button_this.append('<div class="gupselllds-ring"><div></div><div></div><div></div><div></div></div>');
                                    },
                                    success: function(jsonData1) {
                                        if (page_name != undefined && page_name == 'order') {
                                            location.reload();
                                        } else {
                                            Gupsellpro.refreshcart(urlajax_cart);
                                            ajaxCart.updateCartInformation(jsonData1, 0);
                                            if (jsonData1.crossSelling)
                                                $('.crossseling').html(jsonData1.crossSelling);
                                            if (idCombination)
                                                $(jsonData1.products).each(function() {
                                                    if (this.id != undefined && this.id == parseInt(idProduct) && this.idCombination == parseInt(idCombination))
                                                        ajaxCart.updateLayer(this);
                                                });
                                            else
                                                $(jsonData1.products).each(function() {
                                                    if (this.id != undefined && this.id == parseInt(idProduct))
                                                        ajaxCart.updateLayer(this);
                                                });
                                        }
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                                        alert(XMLHttpRequest.responseText);
                                    },
                                    complete: function() {
                                        a_button_this.removeClass('disabled');
                                        a_button_this.addClass('gnone');
                                        a_button_this.removeClass('gshow');
                                        a_button_this.find('span').show();
                                        a_button_this.find('.gupselllds-ring').remove();
                                        a_button_this.closest('.item').find('.gupsellpro-variants').addClass('gnone_change');
                                        a_button_this.closest('.item').find('.content_qty').addClass('gnone_change');
                                        a_button_this.closest('.item').find('.gupsell_ajax_delete_cart_button').addClass('gshow');
                                        Gupsellpro.checkcartbuttonadd(parent_box);
                                    }
                                });
                            } else {
                                alert(jsonData.warrning);
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() { $(this).removeClass('disabled'); }
                    });
                } else {
                    $.ajax({
                        type: 'POST',
                        headers: { "cache-control": "no-cache" },
                        url: urlajax_cart,
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: 'controller=cart&add=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule,
                        beforeSend: function() {
                            a_button_this.addClass('disabled');
                            a_button_this.find('span').hide();
                            a_button_this.append('<div class="gupselllds-ring"><div></div><div></div><div></div><div></div></div>');
                        },
                        success: function(jsonData) {
                            if (typeof(page_name) != "undefined" && page_name !== null && page_name == 'order') {
                                location.reload();
                            } else {
                                ajaxCart.updateCartInformation(jsonData, 0);
                                if (jsonData.crossSelling)
                                    $('.crossseling').html(jsonData.crossSelling);
                                if (idCombination)
                                    $(jsonData.products).each(function() {
                                        if (this.id != undefined && this.id == parseInt(idProduct) && this.idCombination == parseInt(idCombination))
                                            ajaxCart.updateLayer(this);
                                    });
                                else
                                    $(jsonData.products).each(function() {
                                        if (this.id != undefined && this.id == parseInt(idProduct))
                                            ajaxCart.updateLayer(this);
                                    });
                            }

                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() {
                            a_button_this.removeClass('disabled');
                            a_button_this.addClass('gnone');
                            a_button_this.removeClass('gshow');
                            a_button_this.find('span').show();
                            a_button_this.find('.gupselllds-ring').remove();
                            a_button_this.closest('.item').find('.gupsellpro-variants').addClass('gnone_change');
                            a_button_this.closest('.item').find('.content_qty').addClass('gnone_change');
                            a_button_this.closest('.item').find('.gupsell_ajax_delete_cart_button').addClass('gshow');
                            Gupsellpro.checkcartbuttonadd(parent_box);
                        }
                    });
                }
            }
        }
        return false;
    });
    $(document).on('click', '.gupsellpro_volume .item', function(e) {
        if (!$(this).hasClass('active')) {
            $(this).closest('.gupsellpro_volume').find('.warp-upsellcheckbox-cart input.upsellcheckbox-sample-overlay').prop('checked', false);
            $(this).closest('.gupsellpro_volume').find('.item').removeClass('active');
            $(this).find('.warp-upsellcheckbox-cart input.upsellcheckbox-sample-overlay').prop('checked', true);
            $(this).addClass('active');
            Gupsellpro.Checkproductalls($(this).closest('.gupsellbox-checkproducts'));
        }
    });
    /*multi upcart*/
    $(document).on('click', '.gupsell-updatecart-checkout,.gupsell-addcart-checkout', function(e) {
        e.preventDefault();
        var parent_box = $(this).parents('.gupsellpro-box');
        var urlajax = currentIndex + "&token=" + token;
        var gupsell_idupsell_rule = $(this).closest('.gupsellpro-box').find('.gupsell-idupsell-rule').val();
        remove_productin_cart = $(this).closest('.gupsellpro-box').find('.gupsell-remove-cart-product').val();
        parent_box.find('.alert').slideUp(500);
        if (parent_box.find('.upsellcheckbox-sample-overlay').length > 0) {
            nbr_checked = parent_box.find('.upsellcheckbox-sample-overlay:checked').length;
            if (nbr_checked > 0) {
                if (parent_box.hasClass('gps17')) {
                    if (remove_productin_cart > 0) {
                        $.ajax({
                            type: 'POST',
                            url: urlajax + '?Removeproductincart=1',
                            async: true,
                            cache: false,
                            dataType: "json",
                            data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule,
                            beforeSend: function() {
                                $(this).addClass('disabled');
                            },
                            success: function(jsonData) {
                                if (!jsonData.error) {
                                    parent_box.find('.upsellcheckbox-sample-overlay:checked').each(function() {
                                        idCombination = $(this).attr('data-idattribute');
                                        idProduct = $(this).attr('data-idproduct');
                                        gupsellprodc = $(this).attr('data-gupsellprodc');
                                        if ($(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle').length > 0) {
                                            qty = $(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle .gupsell_quantity_wanted input[name="gupsellproqty"]').val();
                                        } else {
                                            qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
                                        }
                                        $.ajax({
                                            type: 'POST',
                                            headers: { "cache-control": "no-cache" },
                                            url: parent_box.find('.gupsell_action_link').val(),
                                            async: true,
                                            cache: false,
                                            dataType: "json",
                                            data: 'update=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + parent_box.find('.gupsell_cart_token').val() + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&remove_productin_cart=' + remove_productin_cart,
                                            beforeSend: function() {},
                                            success: function(jsonData) {},
                                            complete: function() {
                                                nbr_checked = nbr_checked - 1;
                                                if (nbr_checked <= 0) {
                                                    window.location.href = parent_box.find('.gupsell-link-show').val();
                                                }
                                            }
                                        });
                                    });
                                } else {
                                    alert(jsonData.warrning);
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                alert(XMLHttpRequest.responseText);
                            },
                        });
                    } else
                        parent_box.find('.upsellcheckbox-sample-overlay:checked').each(function() {
                            idCombination = $(this).attr('data-idattribute');
                            idProduct = $(this).attr('data-idproduct');
                            gupsellprodc = $(this).attr('data-gupsellprodc');
                            if ($(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle').length > 0) {
                                qty = $(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle .gupsell_quantity_wanted input[name="gupsellproqty"]').val();
                            } else {
                                qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
                            }
                            $.ajax({
                                type: 'POST',
                                headers: { "cache-control": "no-cache" },
                                url: parent_box.find('.gupsell_action_link').val(),
                                async: true,
                                cache: false,
                                dataType: "json",
                                data: 'update=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + parent_box.find('.gupsell_cart_token').val() + (gupsellprodc == 1 ? '&gupsellprodc=1' : ''),
                                beforeSend: function() {},
                                success: function(jsonData) {},
                                complete: function() {
                                    nbr_checked = nbr_checked - 1;
                                    if (nbr_checked <= 0) {
                                        window.location.href = parent_box.find('.gupsell-link-show').val();
                                    }
                                }
                            });

                        });
                } else {
                    if (remove_productin_cart > 0) {
                        $.ajax({
                            type: 'POST',
                            url: urlajax + '?Removeproductincart=1',
                            async: true,
                            cache: false,
                            dataType: "json",
                            data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule,
                            beforeSend: function() {
                                $(this).addClass('disabled');
                            },
                            success: function(jsonData) {
                                if (!jsonData.error) {
                                    parent_box.find('.upsellcheckbox-sample-overlay:checked').each(function() {
                                        idCombination = $(this).attr('data-idattribute');
                                        idProduct = $(this).attr('data-idproduct');
                                        gupsellprodc = $(this).attr('data-gupsellprodc');
                                        if ($(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle').length > 0) {
                                            qty = $(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle .gupsell_quantity_wanted input[name="gupsellproqty"]').val();
                                        } else {
                                            qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
                                        }
                                        $.ajax({
                                            type: 'POST',
                                            headers: { "cache-control": "no-cache" },
                                            url: baseUri + '?rand=' + new Date().getTime(),
                                            async: true,
                                            cache: false,
                                            dataType: "json",
                                            data: 'controller=cart&add=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule + '&remove_productin_cart=' + remove_productin_cart,
                                            beforeSend: function() {},
                                            success: function(jsonData) {},
                                            error: function(XMLHttpRequest, textStatus, errorThrown) {},
                                            complete: function() {
                                                nbr_checked = nbr_checked - 1;
                                                if (nbr_checked <= 0) {
                                                    window.location.href = parent_box.find('.gupsell-link-show').val();
                                                }
                                            }
                                        });
                                    });
                                } else {
                                    alert(jsonData.warrning);
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                alert(XMLHttpRequest.responseText);
                            },
                        });
                    } else
                        parent_box.find('.upsellcheckbox-sample-overlay:checked').each(function() {
                            idCombination = $(this).attr('data-idattribute');
                            idProduct = $(this).attr('data-idproduct');
                            gupsellprodc = $(this).attr('data-gupsellprodc');
                            if ($(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle').length > 0) {
                                qty = $(this).closest('.gupsellpro-box').find('.gupsell-qty-bundle input[name="gupsellproqty"]').val();
                            } else {
                                qty = $(this).closest('.item').find('input[name="gupsellproqty"]').val();
                            }
                            $.ajax({
                                type: 'POST',
                                headers: { "cache-control": "no-cache" },
                                url: baseUri + '?rand=' + new Date().getTime(),
                                async: true,
                                cache: false,
                                dataType: "json",
                                data: 'controller=cart&add=1&ajax=true&qty=' + qty + '&id_product=' + idProduct + '&ipa=' + idCombination + '&token=' + static_token + (gupsellprodc == 1 ? '&gupsellprodc=1' : '') + '&id_upsellpro=' + gupsell_idupsell_rule,
                                beforeSend: function() {},
                                success: function(jsonData) {},
                                error: function(XMLHttpRequest, textStatus, errorThrown) {},
                                complete: function() {
                                    nbr_checked = nbr_checked - 1;
                                    if (nbr_checked <= 0) {
                                        window.location.href = parent_box.find('.gupsell-link-show').val();
                                    }
                                }
                            });
                        });
                }
            } else {
                parent_box.find('.alert-danger').slideDown(500);
                $('html,body').animate({
                    scrollTop: parent_box.find('.alert-danger').offset().top - 20
                }, 'slow');
            }
        } else {
            parent_box.find('.alert-danger').slideDown(500);
            $('html,body').animate({
                scrollTop: parent_box.find('.alert-danger').offset().top - 20
            }, 'slow');
        }
        return false;
    });
});
var Gupsellpro = {
    Checkproductalls: function(restart) {
        if (restart != '') {
            var eachclass = restart;
        } else {
            var eachclass = $('.gupsellbox-checkproducts');
        }
        if (eachclass.length > 0) {
            eachclass.each(function() {
                var urlajax = currentIndex + "&token=" + token;
                var gupsell_idupsell_rule = $(this).closest('.gupsellbox-checkproducts').find('.gupsell-idupsell-rule').val();

                var form = $('.AdminGupselloffers');
                var serializedForm = form.serialize();
                if ($(this).find('.upsellcheckbox-sample-overlay:checked').length > 0) {
                    var sampleproducts = [];
                    $(this).closest('.gupsellbox-checkproducts').find('.upsellcheckbox-sample-overlay:checked').each(function() {
                        var gupsell_id_product = $(this).data('idproduct');
                        var gupsell_id_product_attribute = $(this).data('idattribute');
                        if ($('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsellpro_bundle').length > 0) {
                            var gupsell_qty = 0;
                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsellpro_bundle').find('.gupsell_quantity_wanted').each(function() {
                                gupsell_qty = parseInt($(this).val());
                                return false;
                            });
                        } else {
                            var gupsell_qty = $(this).closest('.item').find('.gupsell_quantity_wanted').val();
                        }
                        sampleproducts.push(gupsell_id_product + '|' + gupsell_id_product_attribute + '|' + gupsell_qty);
                    });
                    var gupsell_idproextra = parseInt($(this).find('.item.active').data('numberkey'));
                    var gupsell_showinpage = $(this).find('.item').data('showinpage');
                    var gupsell_type = $(this).find('.item').data('type');
                    volumes = $("#" + gupsell_showinpage + '_addition').val();
                    
                    if (typeof sampleproducts !== 'undefined' && sampleproducts.length > 0)
                        $.ajax({
                            type: 'POST',
                            url: urlajax + '&CheckproductsaddCart=1',
                            async: true,
                            cache: false,
                            dataType: "json",
                            data: serializedForm + '&gupsell_idupsell_rule=' + gupsell_idupsell_rule + '&sampleproducts=' + sampleproducts + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
                            beforeSend: function() {
                                $(this).addClass('disabled');
                            },
                            success: function(jsonData) {
                                if (!jsonData.error) {
                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gshow');
                                    $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gshow');

                                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .price-product-price').text(jsonData.totalprice_dc);
                                    if ($('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .old-price.product-price').length > 0) {
                                        $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .old-price.product-price').text(jsonData.totalprice);
                                        $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                                    }
                                    if (gupsell_type == 'volume')
                                        if (jsonData.text_discount != '') {
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').show();
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text  .gupselldiscount .gupselldiscount-amount-action-label').text(jsonData.text_discount);
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                                        } else {
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text .gupselldiscount .gupselldiscount-amount-action-label').hide();
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell-Price-text .gupselldiscount .gupselldiscount-amount-action-label').text(jsonData.text_discount);
                                            $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts .gupsell-Price-text .gupselldiscount-amount-action-label').text(jsonData.text_discountprice);
                                        }
                                }
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                alert(XMLHttpRequest.responseText);
                            },
                            complete: function() { $(this).removeClass('disabled'); }
                        });
                    else {
                        $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gnone');
                        $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').removeClass('gshow');
                        $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gnone');
                        $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').removeClass('gshow');
                    }
                } else {
                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').addClass('gnone');
                    $('#gupsellpro-' + gupsell_idupsell_rule).find('.gupsell_products_action_allproducts').removeClass('gshow');
                    $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').addClass('gnone');
                    $('#gupsellpro-' + gupsell_idupsell_rule).closest('.gupsellpro-box').find('.gupsell-button-addtocart').removeClass('gshow');
                }
            });
        }
    },
    checkcartbuttonadd: function(parent_box) {
        var id_upsellpro = parent_box.data('gupsell-id');
        if (parent_box.find('.gupsell_ajax_delete_cart_button.gnone.gshow').length > 0) {
            if (parent_box.find('.upsel-footer-floating-title-checkout').length > 0) {
                parent_box.find('.upsel-footer-floating-title-checkout').addClass('gshow');
            } else if ($('#gupsellpropopup-' + id_upsellpro).length > 0) {
                $('#gupsellpropopup-' + id_upsellpro).find('.button-footer-popup a.gupsellbtn-default').addClass('gshow');
                Gupsellpro.refreshcartPopup($('#gupsellpropopup-' + id_upsellpro), id_upsellpro);
            }
        } else {
            if (parent_box.find('.upsel-footer-floating-title-checkout').length > 0) {
                parent_box.find('.upsel-footer-floating-title-checkout').removeClass('gshow');
            } else if ($('#gupsellpropopup-' + id_upsellpro).length > 0) {
                $('#gupsellpropopup-' + id_upsellpro).find('.button-footer-popup a.gupsellbtn-default').removeClass('gshow');
                Gupsellpro.refreshcartPopup($('#gupsellpropopup-' + id_upsellpro), id_upsellpro);
            }
        }
    },
    refreshcartPopup: function(parent_box, id_upsellpro) {
        $.ajax({
            type: 'POST',
            url: currentIndex + "&token=" + token,
            async: true,
            cache: false,
            data: '&refreshCartpopup=1&upsellpro=' + id_upsellpro,
            dataType: "html",
            success: function(html) {
                parent_box.find('.gupsellcart-footer-popup').html(html);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
            },
        });
    },
    refreshcart: function(url) {
        $.ajax({
            type: 'POST',
            url: url + '?ajax=1&action=refresh',
            async: true,
            cache: false,
            dataType: "json",
            data: '&upsellpro=1',
            beforeSend: function() {
                $(this).addClass('disabled');
            },
            success: function(jsonData) {},
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
            },
        });
    },
    popupload: function(id_old, next) {
        $.fancybox.close();
        var id_key = 0;
        if ($('.upsell-jsoninput .upsellfields-popup').val() != '') {
            var upsellfieldpopups = JSON.parse($('.upsell-jsoninput .upsellfields-popup').val());
            $.each(upsellfieldpopups, function(i, item) {
                if (item == id_old) {
                    id_key = i;
                    return false;
                }
            });
            if (next == 'next') {
                id_key_new = parseInt(id_key) + 1;
                id_key_new_check = parseInt(id_key_new) + 1;
                if (typeof upsellfieldpopups[id_key_new] !== 'undefined' && upsellfieldpopups[id_key_new] != '') {
                    if ($('.popup-product-box').find('a[href="#gupsellpropopup-' + upsellfieldpopups[id_key_new] + '"]').length > 0) {
                        $('.popup-product-box').find('a[href="#gupsellpropopup-' + upsellfieldpopups[id_key_new] + '"]').trigger('click');
                        if ((typeof(upsellfieldpopups[id_key]) != "undefined" && upsellfieldpopups[id_key] !== null) || (typeof(upsellfieldpopups[id_key_new_check]) != "undefined" && upsellfieldpopups[id_key_new_check] !== null)) {
                            if (typeof(upsellfieldpopups[id_key]) != "undefined" && upsellfieldpopups[id_key] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-back" data-id="' + upsellfieldpopups[id_key_new] + '">' + $('.upsellfields-popup-titleback').val() + '</div>');
                                }
                            }
                            if (typeof(upsellfieldpopups[id_key_new_check]) != "undefined" && upsellfieldpopups[id_key_new_check] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-next" data-id="' + upsellfieldpopups[id_key_new] + '">' + $('.upsellfields-popup-titlenext').val() + '</div>');
                                }
                            }
                        }
                    }
                }
            } else if (next == 'back') {
                id_key_new = parseInt(id_key) - 1;
                id_key_new_check = parseInt(id_key_new) - 1;
                if (typeof upsellfieldpopups[id_key_new] !== 'undefined' && upsellfieldpopups[id_key_new] != '') {
                    if ($('.popup-product-box').find('a[href="#gupsellpropopup-' + upsellfieldpopups[id_key_new] + '"]').length > 0) {
                        $('.popup-product-box').find('a[href="#gupsellpropopup-' + upsellfieldpopups[id_key_new] + '"]').trigger('click');
                        if ((typeof(upsellfieldpopups[id_key]) != "undefined" && upsellfieldpopups[id_key] !== null) || (typeof(upsellfieldpopups[id_key_new]) != "undefined" && upsellfieldpopups[id_key_new] !== null)) {
                            if (typeof(upsellfieldpopups[id_key_new_check]) != "undefined" && upsellfieldpopups[id_key_new_check] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-back" data-id="' + upsellfieldpopups[id_key_new] + '">' + $('.upsellfields-popup-titleback').val() + '</div>');
                                }
                            }
                            if (typeof(upsellfieldpopups[id_key]) != "undefined" && upsellfieldpopups[id_key] !== null) {
                                if ($('.fancybox-overlay').length > 0) {
                                    $('.fancybox-overlay').append('<div class="gupsellpropopup-prev-next" data-id="' + upsellfieldpopups[id_key_new] + '">' + $('.upsellfields-popup-titlenext').val() + '</div>');
                                }
                            }
                        }
                    }
                }

            }
        }
    },
    floatingload: function(id_old, next) {
        var id_key = 0;
        if ($('.upsell-jsoninput .upsellfields-floating').val() != '') {
            var upsellfieldpopups = JSON.parse($('.upsell-jsoninput .upsellfields-floating').val());
            $.each(upsellfieldpopups, function(i, item) {
                if (item == id_old) {
                    id_key = i;
                    return false;
                }
            });
            if (next == 'next') {
                id_key_new = parseInt(id_key) + 1;
                if (typeof upsellfieldpopups[id_key] !== 'undefined' && upsellfieldpopups[id_key] != '') {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-action').addClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-back').addClass('active');
                }
                if (typeof upsellfieldpopups[id_key_new] !== 'undefined' && upsellfieldpopups[id_key_new] != '') {
                    $('#gupsellprofloating-' + id_old).removeClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).addClass('active');
                    if ($('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.floating-button-text1.gnone.active').length > 0) {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.floating-button-text1.gnone.active').trigger('click');
                    }
                    id_key_new_check = parseInt(id_key_new) + 1;
                    if (typeof upsellfieldpopups[id_key_new_check] !== 'undefined' && upsellfieldpopups[id_key_new_check] != '') {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-action').addClass('active');
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-next').addClass('active');
                    } else {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-next').removeClass('active');
                    }
                }
            } else if (next == 'back') {
                id_key_old = parseInt(id_key) - 1;
                id_key_new_check = parseInt(id_key_old) - 1;
                if (typeof upsellfieldpopups[id_key_new_check] !== 'undefined' && upsellfieldpopups[id_key_new_check] != '') {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-action').addClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-back').addClass('active');
                } else {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-back').removeClass('active');
                }
                if (typeof upsellfieldpopups[id_key_old] !== 'undefined' && upsellfieldpopups[id_key_old] != '') {
                    $('#gupsellprofloating-' + id_old).removeClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key_old]).addClass('active');
                    if ($('#gupsellprofloating-' + upsellfieldpopups[id_key_old]).find('.floating-button-text1.gnone.active').length > 0) {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_old]).find('.floating-button-text1.gnone.active').trigger('click');
                    }
                    if (typeof upsellfieldpopups[id_key] !== 'undefined' && upsellfieldpopups[id_key] != '') {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-action').addClass('active');
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-next').addClass('active');
                    } else {
                        $('#gupsellprofloating-' + upsellfieldpopups[id_key_new]).find('.upsellfloating-prev-next').removeClass('active');
                    }
                }
            } else if (next == 'check') {
                id_key_new = parseInt(id_key) + 1;
                id_key_old = parseInt(id_key) - 1;
                if (typeof upsellfieldpopups[id_key_new] !== 'undefined' && upsellfieldpopups[id_key_new] != '') {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-action').addClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-next').addClass('active');
                } else {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-next').removeClass('active');
                }
                if (typeof upsellfieldpopups[id_key_old] !== 'undefined' && upsellfieldpopups[id_key_old] != '') {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-action').addClass('active');
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-back').addClass('active');
                } else {
                    $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-back').removeClass('active');
                }
            } else if (next == 'none') {
                console.log(id_old);
                $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-action').removeClass('active');
                $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-back').removeClass('active');
                $('#gupsellprofloating-' + upsellfieldpopups[id_key]).find('.upsellfloating-prev-next').removeClass('active');
            }
        }
    },
    upQTY: function(el, mathqty) {
        urlajax = currentIndex + "&token=" + token;
        switch (mathqty) {
            case 'up':
                fieldName = el.data('field-qty');
                id_product = parseInt(el.data('field-idproduct'));
                id_product_attribute = parseInt(el.data('field-idattribute'));
                gupsell_idupsell_rule = parseInt(el.data('gupsell-id'));
                minqty = parseInt(el.data('field-minqty'));
                gupsell_idproextra = parseInt(el.data('proextraid'));
                gupsell_showinpage = el.data('showinpage');
                gupsell_type = el.data('type');
                volumes = $("#" + gupsell_showinpage + '_addition').val();
                var currentVal = parseInt(el.closest('.gupsell-qty').find('input[name=' + fieldName + ']').val());
                if (!isNaN(currentVal) && currentVal < 100000000) {
                    $.ajax({
                        type: 'POST',
                        url: urlajax + '&MathQty=1',
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule + '&qtyOld=' + currentVal + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&mathqty=' + mathqty + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
                        beforeSend: function() {
                            $(this).addClass('disabled');
                        },
                        success: function(jsonData) {
                            if (!jsonData.error) {
                                el.closest('.gupsell-qty').find('input[name=' + fieldName + ']').val(parseInt(currentVal + 1)).trigger('keyup');
                                if (gupsell_type == 'volume') {
                                    el.closest('.item').find('.gupsellpro_des>.product-name>a>span').text(parseInt(currentVal + 1));
                                }
                                if (el.closest('.item').find('.content_price').length > 0) {
                                    el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                    if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                        el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                }
                            } else {
                                el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                    el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                alert(jsonData.warrning);
                            }
                            if (minqty > currentVal) {
                                el.val(minqty);
                            }
                            Gupsellpro.Checkproductalls('');
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() { $(this).removeClass('disabled'); }
                    });
                } else {
                    el.closest('.gupsell-qty').find('input[name=' + fieldName + ']').val(100000000);
                }
                break;
            case 'down':
                fieldName = el.data('field-qty');
                id_product = parseInt(el.data('field-idproduct'));
                id_product_attribute = parseInt(el.data('field-idattribute'));
                gupsell_idupsell_rule = parseInt(el.data('gupsell-id'));
                minqty = parseInt(el.data('field-minqty'));
                gupsell_idproextra = parseInt(el.data('proextraid'));
                gupsell_showinpage = el.data('showinpage');
                gupsell_type = el.data('type');
                volumes = $("#" + gupsell_showinpage + '_addition').val();
                var currentVal = parseInt(el.closest('.gupsell-qty').find('input[name=' + fieldName + ']').val());
                if (!isNaN(currentVal))
                    $.ajax({
                        type: 'POST',
                        url: urlajax + '&MathQty=1',
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule + '&qtyOld=' + currentVal + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&mathqty=' + mathqty + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
                        beforeSend: function() {
                            $(this).addClass('disabled');
                        },
                        success: function(jsonData) {
                            if (!jsonData.error) {
                                el.closest('.gupsell-qty').find('input[name=' + fieldName + ']').val(currentVal - 1).trigger('keydown');
                                if (el.closest('.item').find('.content_price').length > 0) {
                                    el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                    if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                        el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                    if (gupsell_type == 'volume') {
                                        el.closest('.item').find('.gupsellpro_des>.product-name>a>span').text(parseInt(currentVal - 1));
                                    }
                                }
                            } else {
                                el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                    el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                alert(jsonData.warrning);
                            }
                            if (minqty > currentVal) {
                                el.val(minqty);
                            }
                            Gupsellpro.Checkproductalls('');
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() { $(this).removeClass('disabled'); }
                    });
                break;
            default:
                fieldName = el.attr('name');
                id_product = parseInt(el.data('field-idproduct'));
                id_product_attribute = parseInt(el.data('field-idattribute'));
                gupsell_idupsell_rule = parseInt(el.data('gupsell-id'));
                minqty = parseInt(el.data('field-minqty'));
                gupsell_idproextra = parseInt(el.data('proextraid'));
                gupsell_showinpage = el.data('showinpage');
                gupsell_type = el.data('type');
                volumes = $("#" + gupsell_showinpage + '_addition').val();
                var currentVal = parseInt(el.val());
                if (!isNaN(currentVal))
                    $.ajax({
                        type: 'POST',
                        url: urlajax + '&MathQty=1',
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: '&gupsell_idupsell_rule=' + gupsell_idupsell_rule + '&qtyOld=' + currentVal + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&mathqty=' + mathqty + '&gupsell_idproextra=' + gupsell_idproextra + '&gupsell_showinpage=' + gupsell_showinpage + '&gupsell_type=' + gupsell_type + '&volumes=' + volumes,
                        beforeSend: function() {
                            $(this).addClass('disabled');
                        },
                        success: function(jsonData) {
                            if (!jsonData.error) {
                                if (el.closest('.item').find('.content_price').length > 0) {
                                    el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                    if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                        el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                    if (gupsell_type == 'volume') {
                                        el.closest('.item').find('.gupsellpro_des>.product-name>a>span').text(parseInt(currentVal));
                                    }
                                }
                            } else {
                                el.closest('.item').find('.content_price .content_price_label').text(jsonData.totalprice_dc);
                                if (el.closest('.item').find('.content_price .content_price_labelold').length > 0)
                                    el.closest('.item').find('.content_price .content_price_labelold').text(jsonData.totalpriceold);
                                alert(jsonData.warrning);
                            }
                            if (minqty > currentVal) {
                                el.val(minqty);
                            }
                            Gupsellpro.Checkproductalls('');
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(XMLHttpRequest.responseText);
                        },
                        complete: function() { $(this).removeClass('disabled'); }
                    });
        }
    },
}