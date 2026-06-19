/*
 *    Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
var glsmap, infowindow, glsBounds;
var glsRelayLoaded = false;

$(document).ready(function() {

    /**
     * Bug Fix 1.7.1.0
     * http://forge.prestashop.com/browse/BOOM-2843
     */
    $('.carrier-extra-content > .gls-container').each(function() {
        $(this).parent().toggleClass('carrier-extra-content carrier-extra-content-gls');
    });

    var carrier_extra_content = get_carrier_extra_content();
    var original_checked_relay = $('input.gls-select-relay:checked');
    var glsRelaysAddressData = $('#gls-relay-address-data');

    if (glsRelaysAddressData.length === 1) {
        gls_relay_search({
            address1: glsRelaysAddressData.data('addressAddress1'),
            postcode: glsRelaysAddressData.data('addressPostcode'),
            city: glsRelaysAddressData.data('addressCity')
        }).fail(function(data) {
            var errorText = gls_get_error_message(data);

            $('#gls-relay-error')
                .show()
                .find('.alert-text')
                .html(errorText)
            ;

            return false;
        });
    }

    $('.delivery-options').on('change', 'input[name^="delivery_option"]', function() {
        carrier_extra_content = get_carrier_extra_content();
        if(typeof carrier_extra_content !== 'undefined') {
            $(carrier_extra_content).find('.gls-customer-mobile').trigger('keyup');
            $(carrier_extra_content).find('input[name^="gls_relay"]:checked').removeProp('checked').prop('checked', true).change();
        }
        //__ Fix wanted behavior leads to unwanted consequences in 1.7.5 (remove on 1.7.6): autoreload checkout if payment already displayed
        if(!check_gls_option() && $('.js-cart-payment-step-refresh').length > 0) {
            $('.js-cart-payment-step-refresh').removeClass('js-cart-payment-step-refresh').addClass('js-cart-payment-step-refresh-blocked');
        }
    });

    $('.delivery-options').on('keyup', '.gls-customer-mobile', function() {

        if(gls_validate_mobile()) {

            var checked_carrier = parseInt($('input[name^="delivery_option"]:checked').val());

            $.ajax({
                type: 'POST',
                url: gls_ajax_save_phone_mobile_url,
                cache: false,
                dataType: 'json',
                context: this,
                data: {
                    id_carrier: checked_carrier,
                    gls_customer_mobile: $('#gls-customer-mobile-'+checked_carrier).val(),
                    is_relay: (checked_carrier == glsrelais_carrier_id) ? 1 : 0
                }
            }).done(function(data) {
                if(!data.result) {
                    $('#gls-error-modal').find('.alert-content').text(data.message);
                }
            }).fail(function() {
                $('#gls-error-modal').find('.alert-content').text(gls_js_general_error);
            });
        }
    });

    $('.gls-container').off('change', 'input.gls-select-relay').on('change', 'input.gls-select-relay', function(e) {

        e.preventDefault();
        //__ TODO: gestion d'erreur
        checked_carrier = parseInt($('input[name^="delivery_option"]:checked').val());

        $.ajax({
            type: 'POST',
            url: gls_ajax_select_relay_point_url,
            cache: false,
            dataType: 'json',
            context: this,
            data: {
                glsrelayid: $(this).attr('data-glsrelayid'),
                gls_customer_mobile: $('#gls-customer-mobile-' + checked_carrier).val()
            }
        }).done(function(data) {
            if(!data.result) {
                $(this).prop('checked', false);
                if($(original_checked_relay).length > 0) {
                    $(original_checked_relay).prop('checked', true);
                }
                gls_display_error(data.message);
            } else {
                original_checked_relay = $('input.gls-select-relay:checked');
            }
        }).fail(function() {
            $(this).prop('checked', false);
            if($(original_checked_relay).length > 0) {
                $(original_checked_relay).prop('checked', true);
            }
            gls_display_error(gls_js_general_error);
            return false;
        });
        return false;

    });

    /**
     * Recherche de nouveaux points relais
     */
     $('.gls-container').off('click', '.gls-search-relay').on('click', '.gls-search-relay', function(e) {
        e.preventDefault();

        if($('#gls-search-postcode').val()) {
            gls_relay_search({
                postcode: $('#gls-search-postcode').val(),
                city: $('#gls-search-city').val()
            }).fail(function(data) {
                var errorText = gls_get_error_message(data);

                gls_display_error(errorText);

                return false;
            });
        } else {
            gls_display_error(gls_js_search_error);
        }

        return false;
    });

    $('.gls-container').off('keypress', '.gls-search-input').on('keypress', '.gls-search-input', function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('.gls-search-relay').trigger('click');
        }
    });

    $('.gls-container').off('search', '.gls-search-input').on('search', '.gls-search-input', function(e) {
        e.preventDefault();
        $('.gls-search-relay').trigger('click');
    });

    /**
     * Check avant étape confirmation et paiement
     */
     $(document).on('click', 'button[name=confirmDeliveryOption]', function(e) {
        if(!check_gls_option()) {
            $('#gls-error-modal').modal('show');
            e.stopPropagation();
            return false;
        }
    });

    prestashop.on('updatedDeliveryForm', function(params) {

        /*
        if (typeof params.deliveryOption === 'undefined' || 0 === params.deliveryOption.length) {

            if(typeof params.dataForm !== 'undefined' && params.dataForm.length > 0) {
                var regex = new RegExp(/^delivery_option\[/);
                $(params.dataForm).each(function(index,value){
                    if(regex.test(value['name'])) {
                        params.deliveryOption = $('input[name="' + value['name'] + '"][value="' + value['value'] + '"]').closest('.delivery-option');
                        return true;
                    }
                });
            }

            if(typeof params.deliveryOption === 'undefined' || 0 === params.deliveryOption.length) {
                return;
            }
        }
        */

        if(typeof params.deliveryOption === 'undefined' || 0 === params.deliveryOption.length) {
            return;
        }

        $('.gls-container').each(function() {
            var gls_element = $(this);

            if ($(this).closest('.carrier-extra-content-gls').prev('.delivery-option').length > 0) {
                var delivery_option = $(this).closest('.carrier-extra-content-gls').prev('.delivery-option');
                var gls_element = $(this).closest('.carrier-extra-content-gls');
            } else if ($(this).closest('.delivery-option').length > 0) {
                var delivery_option = $(this).closest('.delivery-option');
                var gls_element = $(this).closest('.carrier-extra-content-gls');
            } else if ($(this).prev('.delivery-option').length > 0) {
                var delivery_option = $(this).prev('.delivery-option');
            } else if ($(this).closest('.carrier-extra-content').prev('.delivery-option').length > 0) {
                var delivery_option = $(this).closest('.carrier-extra-content').prev('.delivery-option');
                var gls_element = $(this).closest('.carrier-extra-content');
            }

            if (typeof delivery_option !== 'undefined' && $(delivery_option).is(params.deliveryOption)) {
                gls_element.stop(true, true).slideDown(function() {
                    if(typeof(glsmap) != 'undefined') {
                        if(google_maps_enable) {
                            google.maps.event.trigger(glsmap, 'resize');
                            glsmap.fitBounds(glsBounds);
                        } else {
                            setTimeout(function() {
                                glsmap.invalidateSize();
                                glsmap.fitBounds(new L.LatLngBounds(glsBounds));
                            }, 500);
                        }
                    }
                });
            } else {
                gls_element.stop(true, true).hide();
            }

        });

    });

    if(typeof carrier_extra_content !== 'undefined') {
        $(carrier_extra_content).find('.gls-customer-mobile').trigger('keyup');
        $(carrier_extra_content).find('input[name^="gls_relay"]:checked').removeProp('checked').prop('checked', true).change();
    }

    if (google_maps_enable && typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initGlsMap);
    } else {
        initGlsMap();
    }

    if($('.js-current-step').attr('id') == 'checkout-payment-step') {

        $('#checkout-delivery-step').on('click', function() {
            initGlsMap();
        });

        if(typeof carrier_extra_content !== 'undefined' && $(carrier_extra_content).find('.gls-customer-mobile').val() == '') {
            $('#checkout-delivery-step').trigger('click');
            $('#checkout-payment-step').off('click');
        }

    }

});

function gls_get_error_message(data) {
    var errorText = gls_js_general_error;

    if (
        data.status === 400 &&
        data.responseJSON &&
        data.responseJSON.message
    ) {
        errorText = data.responseJSON.message;
    }

    return errorText;
}

function gls_relay_search(searchData) {
    $('#gls-relay-container').show();
    $('#gls-relay-loader').show();
    $('#gls-relay-error').hide();

    return $.ajax({
        type: 'POST',
        url: gls_ajax_search_relay_url,
        cache: false,
        dataType: 'json',
        context: this,
        data: searchData
    }).then(function(data) {
        glsRelayLoaded = true;
        $('.gls-relay-list').html(data.point_relay_tpl);
        $('.gls-relay-list, .gls-relay-map').show();
        initGlsMap();
    }).fail(function () {
        if (!glsRelayLoaded) {
            $('#gls-relay-container').hide();
        }
    }).always(function () {
        $('#gls-relay-loader').hide();
        $('.gls-container > .alert').hide();
    });
}

function get_carrier_extra_content() {

    extra_content = '';
    if($('input[name^="delivery_option"]:checked').closest('.delivery-option').next('.carrier-extra-content-gls').length > 0) {
        var extra_content = $('input[name^="delivery_option"]:checked').closest('.delivery-option').next('.carrier-extra-content-gls');
    } else if($('input[name^="delivery_option"]:checked').closest('.delivery-option').find('.carrier-extra-content-gls').length > 0) {
        var extra_content = $('input[name^="delivery_option"]:checked').closest('.delivery-option').find('.carrier-extra-content-gls');
    } else if($('input[name^="delivery_option"]:checked').closest('.delivery-option').next('.carrier-extra-content').find('.gls-container:first').length > 0) {
        var extra_content = $('input[name^="delivery_option"]:checked').closest('.delivery-option').next('.carrier-extra-content');
    }
    return extra_content;

}

function check_gls_option() {

    var checked_carrier = parseInt($('input[name^="delivery_option"]:checked').val());

    var gls_container = $('.gls-container[data-carrier="' + checked_carrier + '"]');

    if (gls_container.length === 0) {
        return true;
    }

    var no_error = true;

    check_mobile = false;
    if(checked_carrier == glsrelais_carrier_id || checked_carrier == gls13h_carrier_id || checked_carrier == glschezvousplus_carrier_id) {
        check_mobile = true;
    }

    if(check_mobile) {
        no_error &= gls_validate_mobile();
    }

    if(checked_carrier == glsrelais_carrier_id) {

        $carrier_selected = false;
        $('.gls-select-relay').each(function() {
            if($(this).is(':checked')) {
                $carrier_selected = true;
            }
        });
        if(!$carrier_selected) {
            $('#gls-error-modal').find('.alert-content').text(gls_js_relay_error);
            if($('.js-current-step').attr('id') == 'checkout-payment-step') {
                $('#checkout-delivery-step').trigger('click');
                $('#checkout-payment-step').off('click');
            }
        }
        no_error &= $carrier_selected;

    }

    return no_error;
}

function gls_validate_mobile() {

    var checked_carrier = parseInt($('input[name^="delivery_option"]:checked').val());

    if($('#gls-customer-mobile-'+checked_carrier).val() && gls_mobile_number_checker($('#gls-customer-mobile-'+checked_carrier).val())) {

        $('#gls-customer-mobile-'+checked_carrier).removeClass('form-control-danger');
        $('#gls-customer-mobile-'+checked_carrier).closest('div.form-group').removeClass('has-danger');
        $('#gls-customer-mobile-'+checked_carrier).addClass('form-control-success');
        $('#gls-customer-mobile-'+checked_carrier).closest('div.form-group').addClass('has-success');
        return true;

    } else {
        $('#gls-scustomer-mobile-'+checked_carrier).removeClass('form-control-success');
        $('#gls-customer-mobile-'+checked_carrier).closest('div.form-group').removeClass('has-success');
        $('#gls-customer-mobile-'+checked_carrier).addClass('form-control-danger');
        $('#gls-customer-mobile-'+checked_carrier).closest('div.form-group').addClass('has-danger');

        $('#gls-error-modal').find('.alert-content').text(gls_js_mobile_error);
        return false;
    }

}

/**
 * Validation du numéro de mobile du destinataire
 */
function gls_mobile_number_checker(phone_mobile) {

    var regex = new RegExp(/^(\+[\d]|0)([\d _.-]{6,})$/);

    if(typeof prestashop.cart["id_address_delivery"]  !== 'undefined' && typeof prestashop.customer.addresses[prestashop.cart["id_address_delivery"]].country_iso !== 'undefined') {
        if(prestashop.customer.addresses[prestashop.cart["id_address_delivery"]].country_iso === 'FR' ||
            prestashop.customer.addresses[prestashop.cart["id_address_delivery"]].country_iso === 'COS') {
            regex = new RegExp(/^(\+33|0)([\d _.-]{9,})$/);
        }
    }

    var numbers = phone_mobile.substr(-8);
    var pattern = ['00000000','11111111','22222222','33333333','44444444','55555555','66666666','77777777','88888888','99999999','12345678','23456789','98765432'];

    if (regex.test(phone_mobile) && jQuery.inArray(numbers, pattern) == -1) {
        // GSM OK : fonction suivante
        return true;
    } else {
        return true;
    }

}

/**
 * Initialisation de la carte
 */
function initGlsMap() {
    if (!glsRelayLoaded) {
        return;
    }

    if(document.getElementById('gls-map')) {

        if(google_maps_enable && typeof google !== 'undefined') {

            glsmap = new google.maps.Map(document.getElementById('gls-map'), {
                zoom: 12,
                maxZoom: 18,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                fullscreenControl: false
            });

            infowindow = new google.maps.InfoWindow({ });

            glsBounds = new google.maps.LatLngBounds();

            //__ Si des marqueurs google maps existent, on les ajoute sur la carte
            if(typeof glsGmapsMarkers !== 'undefined') {
                if(glsGmapsMarkers.length > 0) {
                    glsGmapsMarkers.forEach(function(item, index) {
                        addGlsMapMarker(new google.maps.LatLng(item['lat'], item['lng']), item['name'], item['id'], item['infos']);
                    });
                }
            }

            glsmap.fitBounds(glsBounds);

            $('body').on('click', function(event) {
                $('.js-current-step').off('click');
            });

        } else {

            if(typeof glsmap !== 'undefined') {
                glsmap.remove();
            }
            glsmap = L.map('gls-map', {
                zoom: 12,
                scrollWheelZoom: false
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(glsmap);

            glsBounds = [];

            //__ Si des marqueurs existent, on les ajoute sur la carte
            if(typeof glsGmapsMarkers !== 'undefined') {
                if(glsGmapsMarkers.length > 0) {
                    glsGmapsMarkers.forEach(function(item, index) {
                        addGlsMapMarker([item['lat'], item['lng']], item['name'], item['id'], item['infos']);
                    });
                }
            }

            setTimeout(function() {
                glsmap.invalidateSize();
                glsmap.fitBounds(new L.LatLngBounds(glsBounds));
            }, 500);

            $('body').on('click', function(event) {
                $('.js-current-step').off('click');
            });

        }

    }

}

/**
 * Ajout des points relais sur la carte
 */
function addGlsMapMarker(lat_lng, name, id, infos) {

    if (google_maps_enable) {

        var marker = new google.maps.Marker({
            position: lat_lng,
            label: id,
            map: glsmap
        });

        glsBounds.extend(marker.position);

        google.maps.event.addListener(marker, 'click', (function (marker, name, infos) {

            return function () {
                infowindow.setContent('<div id="content" style="padding-right:17px;">'+
                    '<div id="firstHeading" class="h6">' + name + '</div>'+
                    '<div id="bodyContent" style="width:180px">' + infos + '</div>'+
                '</div>');
                infowindow.open(glsmap, marker);
            }

        })(marker, name, infos));

    } else {

        var marker = L.marker(lat_lng, {title: id, alt: name, icon: L.divIcon({
            className: 'nkmgls-osmap-marker-icon',
            html: '<div class="gls-marker-wrapper" data-marker-id="' + id + '"><img class="gls-marker-img" src="' + gls_marker_path + 'gls-marker.png" alt="' + id + '"></div>',
            iconSize:[54,40],
            iconAnchor:[27,40],
            popupAnchor:[0,-36]
        })}).addTo(glsmap);
        marker.bindPopup('<div id="content" style="padding-right:17px;">'+
            '<div id="firstHeading" class="h6">' + name + '</div>'+
            '<div id="bodyContent" style="width:180px">' + infos + '</div>'+
        '</div>');

        glsBounds.push(lat_lng);

    }

}

function gls_display_error(error) {
    $('#gls-error-modal').find('.alert-content').text(error);
    $('#gls-error-modal').modal('show');
}
