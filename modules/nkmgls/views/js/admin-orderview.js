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
var glsmap,infowindow,glsBounds,markers,glsRelayLoaded;

$(document).ready(function() {

    var glsRelaysAddressData = $('#gls-relay-address-data');

    if (glsRelaysAddressData.length === 1) {
        gls_relay_search({
            address1: glsRelaysAddressData.data('addressAddress1'),
            postcode: glsRelaysAddressData.data('addressPostcode'),
            city: glsRelaysAddressData.data('addressCity')
        });
    }

    if ($('#glsChangeRelay').length > 0) {
        $('#glsChangeRelay').on('click', function(e) {
            e.preventDefault();
            $('#modalChangeRelay').modal({keyboard: false, backdrop: 'static'});
            $('#modalChangeRelay').modal('show');
        });
    }

    $('#glsChangeRelayModal').on('shown.bs.modal', function() {
        setTimeout(function() {
            initGlsMap();
        }, 50);
    });

    /**
     * Recherche de nouveaux points relais
     */
    $('#glsChangeRelayModal').on('click', '.gls-search-relay', function(e) {

        e.preventDefault();
        $('#gls-error-modal').hide();

        if($('#gls-search-postcode').val()) {
            gls_relay_search({
                postcode: $('#gls-search-postcode').val(),
                city: $('#gls-search-city').val(),
            });
        } else {
            gls_display_error(gls_js_search_error);
        }
        return false;

    });

    $('#glsChangeRelayModal').on('keypress', '.gls-search-input', function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('.gls-search-relay').trigger('click');
        }
    });

    $('#glsChangeRelayModal').on('search', '.gls-search-input', function(e) {
        e.preventDefault();
        $('.gls-search-relay').trigger('click');
    });

    $('#saveGlsChangeRelay').on('click', function(e) {

        e.preventDefault();

        $('.modal-footer .btn').hide();
        $('.GlsChangeRelayModalLoader').show();
        $('#gls-error-modal').hide();

        $.ajax({
            type: 'POST',
            url: gls_ajax_change_relay_point_url,
            cache: false,
            dataType: 'json',
            context: this,
            data: {
                glsrelayid: $("input[name='gls_relay']:checked").val(),
                glsIdOrder: $('#modal_gls_id_order').val(),
                glsCustomerMobile: $('#modal_gls_customer_mobile').val(),
            }
        }).done(function(data) {
            if(!data.result) {
                gls_display_error(data.message);
                $('.modal-footer .btn').show();
                $('.GlsChangeRelayModalLoader').hide();
            } else {
                document.location.reload();
            }
        }).fail(function() {
            gls_display_error(gls_js_general_error);
            $('.modal-footer .btn').show();
            $('.GlsChangeRelayModalLoader').hide();
            return false;
        });
    });

    $('#updateTrackingState').on('click', function(e) {

        e.preventDefault();

        //TODO spinner sur le bouton ou le disable ?

        $.ajax({
            type: 'POST',
            url: $('#gls-admin-order-check-tracking-state').attr('action'),
            cache: false,
            dataType: 'json',
            context: this,
            data: $('#gls-admin-order-check-tracking-state').serialize()
        }).done(function(data) {
            $('.gls-tracking-information div.alert').remove();
            if(!data.result) {
                $('.gls-tracking-information').prepend('<div class="alert alert-danger" role="alert"><p class="alert-text">'+data.message+'</p></div>');
            } else {
                if (data.current_state != '' && data.current_state_date != '') {
                    $('.gls-tracking-current-state').html(data.current_state);
                    $('.gls-tracking-current-state-date').html(data.current_state_date);
                    if(typeof $('.gls-tracking-information-empty-template').html() !== 'undefined' && !$('.gls-tracking-information-empty-template').is(':visible')) {
                        $('.gls-tracking-information-empty-template').show();
                        $('.gls-tracking-information-unavailable').remove();
                    }
                }
                if (data.message != '') {
                    $('.gls-tracking-information').prepend('<div class="alert alert-info" role="alert"><p class="alert-text">'+data.message+'</p></div>')
                } else {
                    $('.gls-tracking-information').prepend('<div class="alert alert-success" role="alert"><p class="alert-text">'+gls_js_update_success+'</p></div>')
                }
            }
        }).fail(function() {
            $('.gls-tracking-information div.alert').remove();
            $('.gls-tracking-information').prepend('<div class="alert alert-danger" role="alert"><p class="alert-text">'+gls_js_general_error+'</p></div>')
            return false;
        });

    });

});


function gls_relay_search(searchData) {
    $('#gls-relay-container').show();
    $('#gls-relay-loader').show();
    $('#gls-relay-error').hide();

    searchData.country_code = $('input[name="gls_search_country"]').val();

    return $.ajax({
        type: 'POST',
        url: gls_ajax_search_relay_url,
        cache: false,
        dataType: 'json',
        context: this,
        data: searchData
    }).done(function(data) {
        glsRelayLoaded = true;
        $('.gls-relay-list').html(data.point_relay_tpl).show();
        $('.gls-relay-map').show();
        initGlsMap();
    }).fail(function(data) {
        if (!glsRelayLoaded) {
            $('#gls-relay-container').hide();
        }

        var errorText = gls_get_error_message(data);

        gls_display_error(errorText);
    }).always(function () {
        $('#gls-relay-loader').hide();
        $('.gls-container > .alert').hide();
    });
}

/**
 * Initialisation de la carte
 */
function initGlsMap() {
    if (!glsRelayLoaded) {
        return;
    }

    markers = [];

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

            $('body').on('change', '.gls-select-relay', function(event) {
                var index = $(this).closest('.gls-relay-infos').closest('.row').index();
                google.maps.event.trigger(markers[index], 'click');
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

            $('body').off('change').on('change', '.gls-select-relay', function(event) {
                var index = $(this).closest('.gls-relay-infos').closest('.row').index();
                markers[index].fire('click');
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
                infowindow.setContent('<div id="leaflet-content" style="padding-right:17px;">'+
                    '<div id="firstHeading" class="h4">' + name + '</div>'+
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
        marker.bindPopup('<div id="leaflet-content" style="padding-right:17px;">'+
            '<div id="firstHeading" class="h4">' + name + '</div>'+
            '<div id="bodyContent" style="width:180px">' + infos + '</div>'+
        '</div>');

        glsBounds.push(lat_lng);

    }
    markers.push(marker);
}

function gls_display_error(error) {
    $('#gls-error-modal').text(error);
    $('#gls-error-modal').show();
}
