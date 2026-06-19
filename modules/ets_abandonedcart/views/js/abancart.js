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
var ETS_ABANCART_HAS_BROWSER = parseInt(ETS_ABANCART_HAS_BROWSER) || 0;
if (ETS_ABANCART_HAS_BROWSER) {
    document.addEventListener('DOMContentLoaded', function () {
        if (!("Notification" in window)) {
            //alert("This browser does not support desktop notification 1");
        } else if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    });
}
var ETS_ABANCART_CAMPAIGNS = ETS_ABANCART_CAMPAIGNS || [],
    ETS_ABANCART_COOKIE_CAMPAIGNS = ETS_ABANCART_COOKIE_CAMPAIGNS || [],
    ETS_ABANCART_LINK_AJAX = ETS_ABANCART_LINK_AJAX || '',
    ETS_ABANCART_LINK_SHOPPING_CART = ETS_ABANCART_LINK_SHOPPING_CART || '',
    ETS_ABANCART_TEXT_COLOR = ETS_ABANCART_TEXT_COLOR || '#ffffff',
    ETS_ABANCART_BACKGROUND_COLOR = ETS_ABANCART_BACKGROUND_COLOR || '#ff0000',
    ets_abancart_timeout = false,
    ets_abancart_delay = 0,
    ets_abancart_disable_keydown = false,
    ETS_ABANCART_COPIED_MESSAGE = ETS_ABANCART_COPIED_MESSAGE || 'Copied',
    ETS_ABANCART_CLOSE_TITLE = ETS_ABANCART_CLOSE_TITLE || 'Close',
    ETS_ABANCART_QUEUE = {},
    ETS_ABANCART_LEAVE_DISPLAY = 1,
    ETS_ABANCART_REQUEST = {}
;

if (typeof ETS_ABANCART_LIFE_TIME == "undefined") ETS_ABANCART_LIFE_TIME = -1;

/*----------LEAVE WEBSITE----------*/

//document.documentElement.addEventListener('mouseleave', ets_abancart_mouseleave);
document.documentElement.addEventListener('mouseenter', ets_abancart_mouseenter);
document.documentElement.addEventListener('keydown', ets_abancart_keydown);

function ets_abancart_leavewebsite() {
    var _overload = $('.ets_abancart_leave_website_overload:not(.disabled)');
    if (ETS_ABANCART_LEAVE_DISPLAY && ETS_ABANCART_LINK_AJAX && _overload.length > 0 && _overload.find('.ets_abancart_wrapper').length && !_overload.hasClass('active') && !_overload.hasClass('loading')) {

        _overload.addClass('loading');
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: ETS_ABANCART_LINK_AJAX,
            data: 'leave&ajax=1',
            success: function (json) {
                _overload.removeClass('loading');
                if (json) {
                    if (typeof json.redisplay !== "undefined" && parseInt(json.redisplay) < 0) {
                        ETS_ABANCART_LEAVE_DISPLAY = 0
                        return;
                    }
                    if (json.errors) {
                        _overload
                            .removeClass('active')
                            .addClass('disabled');
                        ets_abancart_mouseenter();
                    } else {
                        var _wrapper = _overload.find('.ets_abancart_wrapper').clone(true),
                            _html = _wrapper.length ? _wrapper.html() : '';
                        $.each(json, function (p, item) {
                            var pattern = p.replace(/([\[\]])/g, '\\$1'),
                                regExp = new RegExp(pattern, 'g');
                            _html = _html.replace(regExp, item);
                        });
                        _overload.find('.ets_abancart_wrapper').addClass('form-original').after(
                            _wrapper
                                .html(_html)
                                .addClass('active')
                        );
                        _overload.addClass('active');
                        _overload.find('.ets_abancart_wrapper.form-original').remove();
                        ets_ab_fn.countdown();
                        ets_ab_fn.countdown2();
                        if ($('.ets_ac_datepicker').length) {
                            $('.ets_ac_datepicker').removeClass('hasDatepicker');
                            $('.ets_ac_datepicker').datepicker({dateFormat: 'yy-mm-dd'});
                        }
                        if ($('.ets_ac_datetimepicker').length) {
                            $('.ets_ac_datetimepicker').removeClass('hasDatepicker');
                            $('.ets_ac_datetimepicker').datetimepicker({
                                prevText: '',
                                nextText: '',
                                dateFormat: 'yy-mm-dd',
                                currentText: 'Now',
                                closeText: 'Done',
                                ampm: false,
                                amNames: ['AM', 'A'],
                                pmNames: ['PM', 'P'],
                                timeFormat: 'hh:mm:ss tt',
                                formatTime: 'hh:mm:ss tt',
                                timeSuffix: '',
                                timeOnlyTitle: 'Choose Time',
                                timeText: 'Time',
                                hourText: 'Hour',
                                minuteText: 'Minute',
                            });
                        }

                        if (json.background_color) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css('background-color', json.background_color);
                        }
                        if (json.popup_width) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css('width', json.popup_width + 'px');
                        }
                        if (json.popup_height) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css('height', json.popup_height + 'px');
                        }
                        if (json.border_radius) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css({
                                'border-radius': json.border_radius + 'px',
                                'overflow': 'hidden'
                            });
                        }
                        if (json.border_width) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css({
                                'border-width': json.border_width + 'px',
                                'border-style': 'solid'
                            });
                        }
                        if (json.border_color) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css('border-color', json.border_color);
                        }
                        if (json.padding) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container').css('border-color', json.padding + 'px');
                        }
                        if (json.close_btn_color) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container .ets_abancart_close').find('style').remove();
                            $('.ets_abancart_leave_website_overload  .ets_abancart_container .ets_abancart_close').append('<style rel="stylesheet">.ets_abancart_leave_website_overload  .ets_abancart_container .ets_abancart_close:after,.ets_abancart_leave_website_overload  .ets_abancart_container .ets_abancart_close:before{background-color: ' + json.close_btn_color + ';}</style>');
                        }
                        if (json.font_size) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_wrapper div,.ets_abancart_leave_website_overload  .ets_abancart_wrapper p,.ets_abancart_leave_website_overload  .ets_abancart_wrapper a').css('font-size', json.font_size + 'px');
                        }
                        if (json.vertical_align) {
                            $('.ets_abancart_leave_website_overload  .ets_abancart_wrapper p,.ets_abancart_leave_website_overload  .ets_abancart_wrapper a,.ets_abancart_leave_website_overload  .ets_abancart_wrapper div:not(.ets_abancart_product_list_table)').css('text-align', 'inherit');
                            $('.ets_abancart_leave_website_overload  .ets_abancart_wrapper').css('text-align', json.vertical_align);
                        }
                        if (json.overlay_bg) {
                            var color = json.overlay_bg;
                            if (json.overlay_bg_opacity) {
                                var colorRgb = etsAcHexToRgb(json.overlay_bg);
                                if (colorRgb) {
                                    color = 'rgba(' + colorRgb.r + ',' + colorRgb.g + ',' + colorRgb.b + ',' + json.overlay_bg_opacity + ')';
                                }
                            }
                            $('.ets_abancart_leave_website_overload').css('background-color', color);
                        }
                        etsAcCheckHasCaptcha(_overload.find('.ets_abancart_wrapper'));
                        etsAcOnLoadRecaptcha();

                    }
                }
            },
            error: function () {
                _overload.removeClass('loading');
            }
        });
    }
}

function etsAcCheckHasCaptcha(el) {
    if (el.find('input[name="captcha_type"]').length) {
        if (el.find('input[name="captcha_type"]').first().val() == 'v2') {
            el.prepend('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');
        } else if (el.find('input[name="captcha_type"]').first().val() == 'v3') {
            var captchaKey = el.find('input[name="captcha_site_key"]').first().val();
            el.prepend('<script src="https://www.google.com/recaptcha/api.js?render=' + captchaKey + '" async defer></script>');
        }
    }
}

function isIE() {
    ua = navigator.userAgent;
    /* MSIE used to detect old browsers and Trident used to newer ones*/
    var is_ie = ua.indexOf("MSIE ") > -1 || ua.indexOf("Trident/") > -1;

    return is_ie;
}

function ets_abancart_mouseleave(event) {
    var y, _ie = isIE();
    if (_ie) {
        y = event.clientY || event.screenY || event.pageY;
    } else {
        y = event.clientY;
    }
    if ((y > -5 && !_ie) || (_ie && y > 5)) {
        return;
    }
    ets_abancart_timeout = setTimeout(ets_abancart_leavewebsite, 0);
}

function ets_abancart_mouseenter() {
    if (ets_abancart_timeout) {
        clearTimeout(ets_abancart_timeout);
        ets_abancart_timeout = null;
    }
}

function ets_abancart_keydown(e) {
    if (ets_abancart_disable_keydown || !e.metaKey || e.keyCode !== 76) {
        return;
    }
    ets_abancart_disable_keydown = true;
    ets_abancart_timeout = setTimeout(ets_abancart_leavewebsite, ets_abancart_delay);
}

//display a success/error/notice message
function showSuccessMessage(msg) {
    $.growl.notice({title: "", message: msg});
}

function showErrorMessage(msg) {
    $.growl.error({title: "", message: msg});
}

/*----------END LEAVE WEBSITE----------*/

var ets_ab_fn = {
    init: function () {
        if (typeof ETSFavico !== "undefined") {
            window.favicon = new ETSFavico({
                animation: 'popFade',
                bgColor: ETS_ABANCART_BACKGROUND_COLOR,
                textColor: ETS_ABANCART_TEXT_COLOR,
            });
            ets_ab_fn.loadAjax(true);
        }
        ets_ab_fn.initCampaign();
        ets_ab_fn.saveCart();
    },
    initCampaign: function () {
        if (ETS_ABANCART_CAMPAIGNS) {
            ETS_ABANCART_CAMPAIGNS.forEach(function (item) {
                ets_ab_fn.setCampaign(item);
            });
        }
        if (ETS_ABANCART_COOKIE_CAMPAIGNS) {
            ETS_ABANCART_COOKIE_CAMPAIGNS.forEach(function (item) {
                ets_ab_fn.setCampaignCookie(item);
            });
        }
    },
    clearTimeout: function (id, isRemove) {
        if (typeof ETS_ABANCART_QUEUE[id] !== "undefined") {
            clearTimeout(ETS_ABANCART_QUEUE[id]);
            if (isRemove)
                delete ETS_ABANCART_QUEUE[id];
        }
    },
    setCampaign: function (item) {
        ets_ab_fn.clearTimeout(item.id_ets_abancart_reminder);
        ETS_ABANCART_QUEUE[item.id_ets_abancart_reminder] = setTimeout(
            function () {
                ets_ab_fn.request(parseInt(item.id_ets_abancart_reminder), item.campaign_type);
            }
            , parseInt((parseFloat(item.lifetime) > 0 ? parseFloat(item.lifetime) * 1000 : 0))
        );
    },
    setCampaignCookie: function (item) {
        ets_ab_fn.clearTimeout(item.id_ets_abancart_reminder);
        var timeOut = 0;
        if (typeof item.lifetime !== "undefined" || parseFloat(item.redisplay) >= 0) {
            if (typeof item.lifetime !== "undefined") {
                timeOut = item.lifetime * 1000;
            } else {
                timeOut = parseFloat(item.redisplay) > 0 ? parseFloat(item.redisplay) * 1000 : 0;
            }
            ETS_ABANCART_QUEUE[item.id_ets_abancart_reminder] = setTimeout(
                function () {
                    ets_ab_fn.request(parseInt(item.id_ets_abancart_reminder), item.type);
                }
                , timeOut
            );
        }
    },
    mergeCampaign: function (reminder, campaigns, action, isCookie) {
        var flag = 0;
        if (campaigns.length > 0) {
            campaigns.forEach(function (item) {
                if (isCookie) {
                    if (item.length > 0) {
                        item.forEach(function (rem) {
                            if (parseInt(rem.id_ets_abancart_reminder) === parseInt(reminder.id_ets_abancart_reminder)) {
                                flag = 1;
                                return true;
                            }
                        });
                    }
                } else {
                    if (parseInt(item.id_ets_abancart_reminder) === parseInt(reminder.id_ets_abancart_reminder)) {
                        flag = 1;
                    }
                }
                if (flag > 0)
                    return true;
            });
        }
        if (flag < 1) {
            switch (action) {
                case 'add':
                    if (isCookie)
                        ets_ab_fn.setCampaignCookie(reminder);
                    else
                        ets_ab_fn.setCampaign(reminder);
                    break;
                case 'delete':
                    ets_ab_fn.removeCampaign(reminder);
                    break;
            }
        }
    },
    restCampaigns: function (campaigns) {
        if (ETS_ABANCART_CAMPAIGNS.length > 0) {
            ETS_ABANCART_CAMPAIGNS.forEach(function (item) {
                ets_ab_fn.mergeCampaign(item, campaigns, 'delete');
            })
        }
        if (campaigns.length > 0) {
            campaigns.forEach(function (item) {
                ets_ab_fn.mergeCampaign(item, ETS_ABANCART_CAMPAIGNS, 'add');
            });
        }
    },
    restCookieCampaigns: function (campaigns) {
        if (ETS_ABANCART_COOKIE_CAMPAIGNS.length > 0) {
            ETS_ABANCART_COOKIE_CAMPAIGNS.forEach(function (item) {
                ets_ab_fn.mergeCampaign(item, campaigns, 'delete', true);
            });
        }
        if (campaigns.length > 0) {
            campaigns.forEach(function (item) {
                if (item.length > 0) {
                    item.forEach(function (rem) {
                        ets_ab_fn.mergeCampaign(rem, ETS_ABANCART_COOKIE_CAMPAIGNS, 'add');
                    });
                }
            });
        }
    },
    removeCampaign: function (id) {
        ets_ab_fn.clearTimeout(id, true);
        delete ETS_ABANCART_REQUEST[id];
    },
    ajaxState: function () {
        var flag = 0,
            first = 0,
            requestQueue = Object.keys(ETS_ABANCART_REQUEST);
        if (requestQueue.length > 0) {
            requestQueue.forEach(function (key) {
                if (parseInt(first) <= 0)
                    first = ETS_ABANCART_REQUEST[key].id;
                if (ETS_ABANCART_REQUEST[key].state > 0) {
                    flag = 1;
                    return true;
                }
            });
        }
        return flag <= 0 ? first : 0;
    },
    request: function (id, campaign_type) {
        if (ETS_ABANCART_LINK_AJAX && parseInt(id) > 0) {
            ETS_ABANCART_REQUEST[id] = {
                type: 'post',
                url: ETS_ABANCART_LINK_AJAX,
                dataType: 'json',
                data: 'renderDisplay&id_ets_abancart_reminder=' + id + '&campaign_type=' + campaign_type,
                state: 0,
                id: id
            };
            var nextId = ets_ab_fn.ajaxState();
            if (parseInt(nextId) > 0)
                ets_ab_fn.doRequestAjax(id);
        }
    },
    doRequestAjax: function (id) {
        var request = ETS_ABANCART_REQUEST[id];
        request.state = 1;
        request.success = function (json) {
            delete ETS_ABANCART_REQUEST[id];
            if (json) {
                if (json.campaigns)
                    ets_ab_fn.restCampaigns(json.campaigns);
                if (json.cookies)
                    ets_ab_fn.restCookieCampaigns(json.cookies);
                if (json.redisplay < 0 && json.id_ets_abancart_reminder > 0) {
                    ets_ab_fn.removeCampaign(json.id_ets_abancart_reminder);
                } else {
                    switch (json.type) {
                        case 'popup':
                            ets_ab_fn.popup(json, id);
                            break;
                        case 'bar':
                            ets_ab_fn.bar(json, id);
                            break;
                        case 'browser':
                            ets_ab_fn.browser(json, id);
                            break;
                    }
                }
            }
        }
        $.ajax(request);
    },
    views: function (id, json, group_class) {
        if (id && json) {
            // FIRST:
            var overloadEl = '.ets_abancart_' + json.type + '_overload';
            if ($('.ets_abancart_' + json.type + '_overload').length <= 0) {
                $('body').prepend('<div class="ets_abancart_' + json.type + '_overload ' + group_class + ' ets_abancart_overload" data-id="' + id + '" data-type="' + json.type + '" ' + (json.type !== 'popup' ? 'style="background-color: ' + json.background_color + '; color: ' + json.text_color + '"' : '') + '><div class="ets_abancart_width"><div class="ets_table"><div class="ets_tablecell"><div class="ets_abancart_container"><div class="ets_abancart_close" title="' + ETS_ABANCART_CLOSE_TITLE + '"></div><div class="ets_abancart_wrapper"></div></div></div></div></div></div>');
            }
            // NEXT:
            var _container = $('body .ets_abancart_' + json.type + '_overload');
            _container
                .attr({'data-id': id, 'data-type': json.type})
                .addClass('active')
                .find('.ets_abancart_wrapper')
                .html('<div class="ets-ac-popup-body" style="' + (json.type === 'popup' && json.popup_body_bg ? 'background-color: ' + json.popup_body_bg + ';' : '') + '">' + json.html + '</div>')
                .prepend((json.type === 'popup' ? '<h4 class="ets_abancart_title" style="' + (json.header_bg ? 'background-color: ' + json.header_bg + ';' : '') + (json.header_text_color ? 'color: ' + json.header_text_color + ';' : '') + (json.header_height ? 'height: ' + json.header_height + 'px;' : '') + (json.header_font_size ? 'font-size: ' + json.header_font_size + 'px;' : '') + '">' + json.title + '</h4>' : ''))
            ;
            /*---HIGHLIGHT BAR---*/
            if (json.type !== 'popup') {
                _container.attr('style', 'background-color: ' + json.background_color + '; color: ' + json.text_color);
            }
            var selectorContainer = _container.find('.ets_abancart_container');
            if (json.type === 'bar') {
                selectorContainer = _container.find('.ets_abancart_width');
            }
            selectorContainer.css('margin', '0 auto');
            if (json.popup_width)
                selectorContainer.css('width', json.popup_width + 'px');
            if (json.popup_height) {
                selectorContainer.css('height', json.popup_height + 'px');
                selectorContainer.css('min-height', json.popup_height + 'px');
            }
            if (json.border_radius)
                selectorContainer.css('border-radius', json.border_radius + 'px');
            if (json.border_width) {
                selectorContainer.css('border-width', json.border_width + 'px');
                selectorContainer.css('border-style', 'solid');
            }
            if (json.border_color)
                selectorContainer.css('border-color', json.border_color);
            if (json.close_btn_color) {
                _container.find('.ets_abancart_close').find('style').remove();
                _container.find('.ets_abancart_close').append('<style>' + overloadEl + ' .ets_abancart_close:before,' + overloadEl + ' .ets_abancart_close:after{background-color: ' + json.close_btn_color + ';}</style>');
            }
            if (json.vertical_align) {
                $(overloadEl + ' .ets-ac-popup-body p, ' + overloadEl + ' .ets-ac-popup-body a,' + overloadEl + ' .ets-ac-popup-body div:not(.ets_abancart_product_list_table)').css('text-align', 'inherit');
                $(overloadEl + ' .ets-ac-popup-body').css('text-align', json.vertical_align);
            }
            if (json.font_size) {
                $('' + overloadEl + ' .ets-ac-popup-body,' + overloadEl + ' .ets-ac-popup-body p, ' + overloadEl + ' .ets-ac-popup-body a,' + overloadEl + ' .ets-ac-popup-body div').css('font-size', json.font_size + 'px');
            }
            if (json.padding) {
                if (json.popup_width) {
                    if (json.type === 'bar') {
                        selectorContainer.css('padding', json.padding + 'px');
                    } else
                        $('' + overloadEl + ' .ets-ac-popup-body').css('padding', json.padding + 'px');
                }

            }
            if (json.overlay_bg) {
                var color = json.overlay_bg;
                if (json.overlay_bg_opacity) {
                    var rgbColor = etsAcHexToRgb(json.overlay_bg);
                    color = 'rgba(' + rgbColor.r + ',' + rgbColor.g + ',' + rgbColor.b + ',' + json.overlay_bg_opacity + ')';
                }
                $('.ets_abancart_popup_overload').css('background-color', color);
            }

            ets_ab_fn.countdown();
            ets_ab_fn.countdown2();
            if ($('.ets_ac_datepicker').length) {
                $('.ets_ac_datepicker').removeClass('hasDatepicker');
                $('.ets_ac_datepicker').datepicker({dateFormat: 'yy-mm-dd'});
            }
            if ($('.ets_ac_datetimepicker').length) {
                $('.ets_ac_datetimepicker').removeClass('hasDatepicker');
                $('.ets_ac_datetimepicker').datetimepicker({
                    prevText: '',
                    nextText: '',
                    dateFormat: 'yy-mm-dd',
                    currentText: 'Now',
                    closeText: 'Done',
                    ampm: false,
                    amNames: ['AM', 'A'],
                    pmNames: ['PM', 'P'],
                    timeFormat: 'hh:mm:ss tt',
                    formatTime: 'hh:mm:ss tt',
                    timeSuffix: '',
                    timeOnlyTitle: 'Choose Time',
                    timeText: 'Time',
                    hourText: 'Hour',
                    minuteText: 'Minute',
                });
            }
            etsAcOnLoadRecaptcha();
        }

    },
    popup: function (json, id) {
        ets_ab_fn.views(id, json, 'ets_abancart_popup');
    },
    bar: function (json, id) {
        ets_ab_fn.views(id, json, '');
    },
    browser: function (json, id) {

        if (json && id) {
            var notification;
            if (!("Notification" in window)) {
                //alert("This browser does not support desktop notification 1");
            } else if (Notification.permission === "granted") {
                ets_ab_fn.setNotification(notification, json, id);
            } else if (Notification.permission !== "denied" && ETS_ABANCART_HAS_BROWSER) {
                Notification.requestPermission().then(function (permission) {
                    if (permission === "granted") {
                        ets_ab_fn.setNotification(notification, json, id);
                    }
                });
            }
        }
    },
    setNotification: function (notification, json, id) {

        // New notification.
        notification = new Notification(json.title, {icon: json.icon, body: json.html});

        // Event click.
        notification.onclick = function () {
            if (typeof json.code !== "undefined" && json.code) {
                $.ajax({
                    type: 'post',
                    url: ETS_ABANCART_LINK_AJAX,
                    dataType: 'json',
                    data: 'add_cart_rule&discount_code=' + json.code,
                    success: function (json) {
                        if (json) {
                            if (json.errors) {
                                showErrorMessage(json.errors)
                            } else {
                                window.location.href = json.link_checkout;
                            }
                        }
                    },
                });
            }
        };

        // Event close.
        notification.onclose = function () {
            if (id) {
                $.ajax({
                    type: 'post',
                    url: ETS_ABANCART_LINK_AJAX,
                    dataType: 'json',
                    data: 'type=browser&redisplay=1&id=' + id,
                });
            }
        };
    },
    close: function (type, json) {
        $('body .ets_abancart_' + type + '_overload.active').remove();
        if (parseFloat(json.redisplay) > 0) {
            ETS_ABANCART_QUEUE[json.id_ets_abancart_reminder] = setTimeout(function () {
                ets_ab_fn.request(json.id_ets_abancart_reminder, type);
            }, parseFloat(json.redisplay) * 1000);
        }
    },
    countdown: function () {
        var clock = $('.ets_abancart_count_down_clock');
        var style = clock.attr('data-style') || '';
        if (clock.length > 0) {
            clock.countdown(parseInt(clock.data('date')) * 1000).on('update.countdown', function (event) {
                $(this).html(event.strftime(''
                    + (event.offset.weeks > 0 ? '<span class="ets_abancart_countdown weeks" style="' + style + '"><span>%-w</span> week%!w </span>' : '')
                    + (event.offset.days > 0 ? '<span class="ets_abancart_countdown days" style="' + style + '"><span>%-d</span> day%!d </span>' : '')
                    + '<span class="ets_abancart_countdown hours" style="' + style + '"><span>%H</span> hr </span>'
                    + '<span class="ets_abancart_countdown minutes" style="' + style + '"><span>%M</span> min </span>'
                    + '<span class="ets_abancart_countdown seconds" style="' + style + '"><span>%S</span> sec </span>'));
            });
        }
    },
    countdown2: function () {
        var clock = $('.ets_ac_evt_countdown2');
        var style = clock.attr('data-style') || '';
        if (clock.length > 0) {
            clock.countdown(parseInt(clock.data('date')) * 1000).on('update.countdown', function (event) {
                $(this).html(event.strftime(''
                    + (event.offset.weeks > 0 ? '<span class="ets_ac_countdown2 weeks" style="' + style + '"><span>%-w</span> week%!w </span>' : '')
                    + (event.offset.days > 0 ? '<span class="ets_ac_countdown2 days" style="' + style + '"><span>%-d</span> day%!d </span>' : '')
                    + '<span class="ets_ac_countdown2 hours" style="' + style + '"><span>%H</span> hr </span>'
                    + '<span class="ets_ac_countdown2 minutes" style="' + style + '"><span>%M</span> min </span>'
                    + '<span class="ets_ac_countdown2 seconds" style="' + style + '"><span>%S</span> sec </span>'));
            });
        }
    },
    saveCart: function () {
        if ((ETS_ABANCART_LIFE_TIME >= 0 || $('#ets_abancart_cart_save.active').length > 0) && ETS_ABANCART_LINK_SHOPPING_CART) {
            setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: ETS_ABANCART_LINK_SHOPPING_CART,
                    data: 'init',
                    success: function (json) {
                        $('#ets_abancart_cart_save.active').removeClass('active');
                        if (json) {
                            if ($('body .ets_abancart_shopping_cart_overload').length <= 0) {
                                $('body').prepend('<div class="ets_abancart_shopping_cart_overload ets_abancart_overload"><div class="ets_abancart_wrapper"></div></div>');
                            }
                            if (json.html)
                                $('body .ets_abancart_shopping_cart_overload').addClass('active').find('.ets_abancart_wrapper').html(json.html);
                        }
                    },
                    error: function () {
                        $('#ets_abancart_cart_save.active').removeClass('active');
                    }
                });
            }, $('#ets_abancart_cart_save.active').length > 0 ? 0 : ETS_ABANCART_LIFE_TIME * 1000);
        }
    },
    exitPopupSaveCart: function (notReDisplay) {

        var notReDisplay = notReDisplay || true;

        $('.ets_abancart_shopping_cart_overload.active').removeClass('active');
        if (notReDisplay && ETS_ABANCART_LINK_SHOPPING_CART) {
            $('#save_cart_form .bootstrap').remove();
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_LINK_SHOPPING_CART,
                dataType: 'json',
                data: 'ajax=1&offCart',
                success: function () {

                },
                error: function () {

                }
            });
        }
    },
    exitPopupCart: function () {
        $('.ets_abancart_display_shopping_cart_overload.active').removeClass('active');
    },
    loadAjax: function (initialized) {
        if (typeof ETS_ABANCART_BROWSER_TAB_ENABLED === "undefined" || !ETS_ABANCART_BROWSER_TAB_ENABLED)
            return;
        if (initialized) {
            favicon.badge(parseInt(ETS_ABANCART_PRODUCT_TOTAL));
        } else if (ETS_ABANCART_LINK_AJAX && typeof favicon !== "undefined") {
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: ETS_ABANCART_LINK_AJAX,
                data: 'favicon&ajax=1',
                success: function (json) {
                    if (json) {
                        favicon.badge(parseInt(json.product_total));
                    }
                }
            });
        }
    },
    exitPopupLeave: function () {
        $('.ets_abancart_leave_website_overload.active').removeClass('active');
        $('.ets_abancart_leave_website_overload .ets_abancart_wrapper.active').remove();
    },
    copyToClipboard: function (el) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(el.text().trim()).select();
        document.execCommand("copy");
        $temp.remove();
        showSuccessMessage(ETS_ABANCART_COPIED_MESSAGE);
        setTimeout(function () {
            el.removeClass('copy');
        }, 300);
    },
};

$(document).ready(function () {

    if ($('.ets_ac_datepicker').length) {
        $('.ets_ac_datepicker').datepicker({dateFormat: 'yy-mm-dd'});
    }
    if ($('.ets_ac_datetimepicker').length) {
        $('.ets_ac_datetimepicker').datetimepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd',
            currentText: 'Now',
            closeText: 'Done',
            ampm: false,
            amNames: ['AM', 'A'],
            pmNames: ['PM', 'P'],
            timeFormat: 'hh:mm:ss tt',
            formatTime: 'hh:mm:ss tt',
            timeSuffix: '',
            timeOnlyTitle: 'Choose Time',
            timeText: 'Time',
            hourText: 'Hour',
            minuteText: 'Minute',
        });
    }

    if ($('.ets_speed_dynamic_hook').length < 1) {
        ets_ab_fn.init();
    }
    $(document).on("hooksLoaded", function () {
        Object.keys(ETS_ABANCART_QUEUE).forEach(function (i) {
            clearTimeout(ETS_ABANCART_QUEUE[i]);
            delete ETS_ABANCART_QUEUE[i];
        });
        ets_ab_fn.init();
    });

    /*---favicon---*/
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (typeof settings.data !== "undefined" && (settings.data.toString().match(/(qty=\d+)/i) && settings.data.toString().match(/(add=\d+)/i) || settings.url.match(/(id_product=\d+)/i) && settings.url.match(/(update=\d+)/i) || settings.url.match(/(id_product=\d+)/i) && settings.url.match(/(delete=\d+)/i))) {
            ets_ab_fn.loadAjax(false);
        }
        var nextId = ets_ab_fn.ajaxState();
        if (parseInt(nextId) > 0)
            ets_ab_fn.doRequestAjax(nextId);
    });
    /*---end favicon---*/

    $(document).on('click', '.ets_abancart_box .ets_abancart_box_discount', function (ev) {
        ev.preventDefault();
        ets_ab_fn.copyToClipboard($(this));
    });

    $(document).on('click', '.ets_abancart_leave_website_overload .ets_abancart_close', function (ev) {
        ev.preventDefault();
        ets_ab_fn.exitPopupLeave();
        var btn = $(this);
        if (!btn.hasClass('active') && ETS_ABANCART_LINK_AJAX) {
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_LINK_AJAX,
                dataType: 'json',
                data: 'leave_closed',
                success: function (json) {
                    btn.removeClass('active');
                },
            });
        }
    });


    $(document).on('click', '.ets_abancart_shopping_cart_overload .ets_abancart_create_account', function (ev) {
        ev.preventDefault();
        if ($('#id_customer').length > 0 && parseInt($('#id_customer').val()) <= 0) {
            $('.ets_abancart_form_login').fadeOut();
            $('.ets_abancart_form_create').fadeIn();
        }
    });

    $(document).on('click', '.ets_abancart_view_shopping_cart', function (ev) {
        ev.preventDefault();
        var btn = $(this);
        if (!btn.hasClass('active') && btn.attr('href') != '') {
            btn.addClass('active');
            $.ajax({
                type: 'POST',
                url: btn.attr('href'),
                dataType: 'json',
                data: 'ajax=1',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if ($('body .ets_abancart_display_shopping_cart_overload').length <= 0) {
                            $('body').prepend('<div class="ets_abancart_display_shopping_cart_overload ets_abancart_popup ets_abancart_overload"><div class="ets_table"><div class="ets_tablecell"><div class="ets_abancart_container"><div class="ets_abancart_close" title="' + ETS_ABANCART_CLOSE_TITLE + '"></div><div class="ets_abancart_wrapper"></div></div></div></div></div>');
                        }
                        $('body .ets_abancart_display_shopping_cart_overload').addClass('active').find('.ets_abancart_wrapper').html(json.html);
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_display_shopping_cart_overload .ets_abancart_close, .ets_abancart_display_shopping_cart_overload .ets_abancart_cancel', function (ev) {
        ev.preventDefault();
        ets_ab_fn.exitPopupCart();
    });

    $(document).on('click', '.ets_abancart_load_this_cart', function (ev) {
        ev.preventDefault();
        var btn = $(this);
        if (!btn.hasClass('active') && btn.attr('href') != '') {
            btn.addClass('active');
            $.ajax({
                type: 'POST',
                url: btn.attr('href'),
                dataType: 'json',
                data: 'ajax=1',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            $('body .ets_abancart_display_shopping_cart_overload').prepend(json.errors);
                        else
                            window.location.href = json.link_checkout;
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_shopping_cart_overload .ets_abancart_close', function (ev) {
        ev.preventDefault();
        ets_ab_fn.exitPopupSaveCart();
    });

    $(document).on('click', '.ets_abancart_shopping_cart_overload button[id=submit_cart]', function (ev) {
        ev.preventDefault();

        var btn = $(this), form = $('#save_cart_form');
        btn.parents('form#save_cart_form').find('input.cart_name').removeClass('error');
        if (!btn.hasClass('active') && form.attr('action')) {
            btn.addClass('active');
            var formData = new FormData(form.get(0));
            formData.append('ajax', 1);
            $('#save_cart_form .bootstrap').remove();
            $.ajax({
                type: 'post',
                url: form.attr('action'),
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.not_logged && parseInt($('#id_customer').val()) <= 0) {
                            $('.ets_abancart_form_login').fadeIn();
                            $('.ets_abancart_form_save_cart').fadeOut();
                        } else if (json.errors) {
                            form.prepend(json.errors);
                            btn.parents('form#save_cart_form').find('input#cart_name').addClass('error').focus();
                        } else {
                            if (json.msg)
                                showSuccessMessage(json.msg);
                            $('#ets_abancart_cart_save').remove();
                            ets_ab_fn.exitPopupSaveCart(false);
                        }
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });
    $(document).on('change', 'input#cart_name', function (e) {
        if ($(this).val() != '') {
            $(this).removeClass('error');
        } else {
            $(this).addClass('error');
        }
    });
    $(document).on('click', '.ets_abancart_shopping_cart_overload button[name=submitLogin]', function (ev) {
        ev.preventDefault();
        var btn = $(this), form = $('#login_form');
        if (!btn.hasClass('active') && form.attr('action')) {
            btn.addClass('active');
            var formData = new FormData(form.get(0));
            formData.append('cart_name', $('#cart_name').val());
            formData.append('ajax', 1);
            $('#login_form .bootstrap').remove();
            $.ajax({
                type: 'post',
                url: form.attr('action'),
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            form.prepend(json.errors);
                        else
                            window.location.reload();
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_shopping_cart_overload button[name=submitCreate]', function (ev) {
        ev.preventDefault();
        var btn = $(this), form = $('#create_form');
        if (!btn.hasClass('active') && form.attr('action')) {
            btn.addClass('active');
            var formData = new FormData(form.get(0));
            formData.append('cart_name', $('#cart_name').val());
            formData.append('ajax', 1);
            $('#login_form .bootstrap').remove();
            $.ajax({
                type: 'post',
                url: form.attr('action'),
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                        if (json.errors)
                            form.prepend(json.errors);
                        else
                            window.location.reload();
                    }
                },
                error: function () {
                    btn.removeClass('active');
                    window.location.reload();
                }
            });
        }
    });

    $(document).on('click', '#ets_abancart_cart_save', function (ev) {
        ev.preventDefault();
        if (!$(this).hasClass('active')) {
            $(this).addClass('active');
            ets_ab_fn.saveCart();
        }

    });

    $(document).on('click', '.ets_abancart_close:not(.leave), .ets_abancart_no_thanks', function (ev) {
        ev.preventDefault();
        var btn = $(this),
            overload = btn.parents('.ets_abancart_overload'),
            id = overload.attr('data-id'),
            type = overload.attr('data-type');
        $('body .ets_abancart_' + type + '_overload.active').remove();
        if (!btn.hasClass('active') && ETS_ABANCART_LINK_AJAX && id) {
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_LINK_AJAX,
                dataType: 'json',
                data: 'type=' + type + '&redisplay=1&id=' + id + (btn.hasClass('ets_abancart_no_thanks') ? '&closed=1' : ''),
                success: function (json) {
                    if (json) {
                        ets_ab_fn.close(type, json);
                    }
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_leave_website_overload .ets_abancart_no_thanks', function (ev) {
        ev.preventDefault();
        var btn = $(this),
            overload = btn.parents('.ets_abancart_overload');
        overload.remove();
        if (!btn.hasClass('active') && ETS_ABANCART_LINK_AJAX) {
            btn.addClass('active');
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_LINK_AJAX,
                dataType: 'json',
                data: 'offLeave',
                success: function (json) {
                    btn.removeClass('active');
                    if (json) {
                    }
                },
                error: function () {
                    btn.removeClass('active');
                }
            });
        }
    });

    $(document).on('click', '.ets_abancart_overload .ets_abancart_add_discount', function (ev) {
        ev.preventDefault();
        var btn = $(this),
            overload = btn.parents('.ets_abancart_overload'),
            discount_code = btn.data('code');
        if (!btn.hasClass('active') && ETS_ABANCART_LINK_AJAX) {
            $.ajax({
                type: 'post',
                url: ETS_ABANCART_LINK_AJAX,
                dataType: 'json',
                data: 'add_cart_rule&discount_code=' + discount_code,
                success: function (json) {
                    if (json) {
                        if (json.errors) {
                            //overload.prepend(json.errors);
                            showErrorMessage(json.errors);
                        } else
                            window.location.href = json.link_checkout;
                    }
                }
            });
        }
    });

    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            ets_ab_fn.exitPopupCart();
            ets_ab_fn.exitPopupSaveCart();
        }
    });

    $(document).mouseup(function (e) {

        var displayShoppingCart = $('.ets_abancart_display_shopping_cart_overload.active .ets_abancart_container'),
            displayCartSave = $('.ets_abancart_shopping_cart_overload.active .ets_abancart_shopping_cart');

        if (displayShoppingCart.length > 0 && !displayShoppingCart.is(e.target) && displayShoppingCart.has(e.target).length === 0) {
            ets_ab_fn.exitPopupCart();
        }
        if (displayCartSave.length > 0 && !displayCartSave.is(e.target) && displayCartSave.has(e.target).length === 0) {
            ets_ab_fn.exitPopupSaveCart();
        }
    });

    $(document).on('click', '.ets_abancart_delete_cart, .ets_abancart_delete', function (ev) {
        var btn = $(this);
        if (!confirm(btn.data('confirm'))) {
            ev.preventDefault();
        }
    });

    $(document).on('click', '.js-ets-ac-btn-submit-lead-form', function (e) {
        var $this = $(this);
        if ($this.hasClass('loading')) {
            return false;
        }
        if ($this.closest('form').find('.ets_ac_captchav2').length && typeof grecaptcha !== 'undefined') {
            if (!grecaptcha.getResponse()) {
                $this.closest('.ets-ac-lead-form-field-shortcode').find('.form-errors').html('<div class="alert alert-danger"><ul>' + ETS_AC_TRANS.captchv2_invalid + '</ul></div>');
                return false;
            }
        }
        var formData = new FormData();
        var inputDatas = $this.closest('form').serializeArray();

        $.each(inputDatas, function (i, el) {
            if ($this.closest('form').find('[name="' + el.name + '"]').attr('type') == 'file') {
                var fileItem = $this.closest('form').find('[name=' + el.name + ']')[0].files;
                if (fileItem.length) {
                    formData.append(el.name, fileItem[0]);
                }
            } else {
                formData.append(el.name, el.value);
            }
        });
        $this.closest('form').find('input[type=file]').each(function () {
            var fileItem = $(this)[0].files;
            if (fileItem.length) {
                formData.append($(this).attr('name'), fileItem[0]);
            }
        });

        formData.append('submitEtsAcLeadForm', 1);
        $.ajax({
            url: ETS_AC_LINK_SUBMIT_LEAD_FORM + (ETS_AC_LINK_SUBMIT_LEAD_FORM.indexOf('?') !== -1 ? '&ajax=1' : '?ajax=1'),
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $this.addClass('loading');
                $this.prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    if (res.display_thankyou_page) {
                        $this.closest('.ets_abancart_wrapper').html(res.thankyou);
                        if ($('.ets_abancart_popup_overload .ets_abancart_close').length) {
                            $('.ets_abancart_popup_overload .ets_abancart_close').addClass('thankyou-page');
                        }
                    } else {
                        $this.closest('.ets-ac-lead-form-field-shortcode').html('<div class="alert alert-success">' + res.message + '</div>');
                    }
                } else {
                    var msg = '';
                    $.each(res.message, function (i, el) {
                        msg += '<li>' + el + '</li>';
                    });
                    $this.closest('.ets-ac-lead-form-field-shortcode').find('.form-errors').html('<div class="alert alert-danger"><ul>' + msg + '</ul></div>');
                }
            },
            complete: function () {
                $this.removeClass('loading');
                $this.prop('disabled', false);
            }
        });
        return false;
    });

    $(document).on('click', '.ets-ac-btn-submit-lead-form ', function () {
        var $this = $(this);
        if ($this.closest('form').find('.ets_ac_captchav2').length && typeof grecaptcha !== 'undefined') {
            if (!grecaptcha.getResponse()) {
                $this.closest('form').find('.ets_ac_captchav2').parent().find('.form-error-item').remove();
                $this.closest('form').find('.ets_ac_captchav2').after('<p class="form-error-item">' + ETS_AC_TRANS.captchv2_invalid + '</p>');
                return false;
            }
        }
    });
    $(document).mouseleave(function () {
        setTimeout(ets_abancart_leavewebsite, 0)
    });

});

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