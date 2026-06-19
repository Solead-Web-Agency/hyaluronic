/**
 * NOTICE OF LICENSE.
 *
 * @file Get Google Maps Place API and select your path 
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2016 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 */

var placeSearch, autocomplete, autocompleteListener;
window.autogoogleplace = [];

function initialize_autogoogleplace() {
    if (document.getElementById(window.autogoogleplace.id_address_input)) {

        if (typeof google == "undefined") {
            return false;
        }

        resetGoogleAutocomplete();


        try {

            var restriction = { types: ['geocode'] };

            autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById(window.autogoogleplace.id_address_input)),
                restriction
            );

            // Country filter changed by Google on their API 
            // Cannot handle more than 5 countries, which is impossible to handle on our script to be user friendly
            // Disabling until further notice (april 2020)
            if (typeof autogoogleplace_excluded_countries !== 'undefined' && autogoogleplace_excluded_countries.length > 0) {

                autocomplete.setComponentRestrictions({
                    country: autogoogleplace_excluded_countries
                });

            }

        } catch (error) {

            // handle error
            console.log(error.message);

            if (typeof window.autogoogleplace_admin_logged !== 'undefined' && window.autogoogleplace_admin_logged == 1) {
                $('#' + window.autogoogleplace.id_address_input).append('<p><b>(Admin visible only) : ' + error.message + '</b></p>');
            }
        }

        // Future v2
        // geoPlace(window.autogoogleplace.id_address_input);

        autocompleteListener = google.maps.event.addListener(autocomplete, 'place_changed', function() {

            var inputAutocomplete = document.getElementById(window.autogoogleplace.id_address_input);

            // binding and resetting values
            if ($('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="city"]').length > 0) {
                window.autogoogleplace.city_input = $('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="city"]').eq(0).attr('id');
                document.getElementById(window.autogoogleplace.city_input).value = "";
            }

            if ($('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="address2"]').length > 0) {
                window.autogoogleplace.address2_input = $('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="address2"]').eq(0).attr('id');
                document.getElementById(window.autogoogleplace.address2_input).value = "";
            }

            if ($('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="postcode"]').length > 0) {
                window.autogoogleplace.postcode_input = $('#' + window.autogoogleplace.id_address_input).closest('form').find('input[type="text"][id*="postcode"]').eq(0).attr('id');
                document.getElementById(window.autogoogleplace.postcode_input).value = "";
            }

            if ($('#' + window.autogoogleplace.id_address_input).closest('form').find('select[id*="country"]').length > 0) {
                window.autogoogleplace.country_input = $('#' + window.autogoogleplace.id_address_input).closest('form').find('select[id*="country"]').eq(0).attr('id');
            }

            var returnFill = fillAddressesFields();
            google.maps.event.clearListeners(inputAutocomplete, "focus");
            google.maps.event.clearListeners(inputAutocomplete, "blur");

            return loadAddressHelper();

        });

    }
}

function resetGoogleAutocomplete() {
    if (autocomplete !== undefined) {
        google.maps.event.removeListener(autocompleteListener);
        google.maps.event.clearInstanceListeners(autocomplete);
        $(".pac-container").remove();
    }
}

// Backward compatibility
if (typeof fillInAddress == 'undefined') {
    function fillInAddress() {
        fillAddressesFields();
    }
}

function fillAddressesFields() {

    var place = autocomplete.getPlace();
    var street = "";
    var city = false;
    var state_1 = false;
    var state_2 = false;
    var state_3 = false;
    var temp_array = [];
    var routes = [];
    var country_iso = "";

    var addresses_forms = [
        'short_name',
        'long_name'
    ];

    var only_state_1 = [];
    var only_state_2 = ["ES"];
    var only_state_3 = [];
    var number_after_street_countries = ["DE"];
    var number_after_street = false;
    var street_number = "";

    if (typeof place !== 'undefined' && typeof place.address_components !== 'undefined') {

        for (var i = 0; i < place.address_components.length; i++) {

            var addressType = place.address_components[i].types[0];

            for (var y = 0; y < addresses_forms.length; y++) {

                var form = addresses_forms[y];

                if (typeof place.address_components[i][form] == 'undefined') {
                    continue;
                }

                var val = place.address_components[i][form];

                if (typeof val != "undefined" && val) {

                    if (addressType == "premise") {
                        temp_array.push(val + "/");
                    }

                    if (addressType == "route") {
                        routes.push(val);
                    }

                    if (addressType == "street_number") {
                        temp_array.push(val + " ");
                        street_number = val;
                    }

                    if (addressType == "locality") {
                        city = val;
                    }

                    if (typeof window.autogoogleplace.postcode_input !== 'undefined' && addressType == "postal_code") {
                        document.getElementById(window.autogoogleplace.postcode_input).value = val;
                        $('#' + window.autogoogleplace.postcode_input).trigger('keypress').trigger('blur').trigger('change');
                    }

                    if (typeof window.autogoogleplace.country_input !== 'undefined' && addressType == "country") {

                        if (number_after_street_countries.indexOf(val) > -1) {
                            number_after_street = true;
                        }

                        if (typeof window.autogoogleplace_countries[val] !== 'undefined' && $("#" + window.autogoogleplace.country_input + ' option[value=' + window.autogoogleplace_countries[val] + ']').length > 0) {

                            // Checking DB related Id's
                            if (window.autogoogleplace_countries[val] != $("#" + window.autogoogleplace.country_input + ' option:selected').attr('value')) {
                                $("#" + window.autogoogleplace.country_input + ' option[value=' + window.autogoogleplace_countries[val] + ']').prop('selected', true).click().change();
                            }

                            country_iso = val;

                        } else {

                            // Fallback
                            var old_val = place.address_components[i]['long_name'];
                            var selected_country_option = $("#" + window.autogoogleplace.country_input + ' option[text=' + old_val + ']').length > 0 ? $("#" + window.autogoogleplace.country_input + ' option[text=' + old_val + ']') : $("#" + window.autogoogleplace.country_input + ' option:contains(' + old_val + ')').first();
                            selected_country_option.prop('selected', true).click().change();
                        }

                    }

                    // First level research
                    if (addressType == "administrative_area_level_1") {
                        state_1 = val;
                    }

                    if (addressType == "administrative_area_level_2") {
                        state_2 = val;
                    }

                    if (addressType == "administrative_area_level_3") {
                        state_3 = val;
                    }

                    // For second area, not used in most country but can't harm to indicate it
                    if (typeof autogoogleplace_active_address2 !== 'undefined' && parseInt(autogoogleplace_active_address2) == 1) {

                        if (typeof window.autogoogleplace.address2_input !== 'undefined' && addressType == "administrative_area_level_2") {
                            document.getElementById(window.autogoogleplace.address2_input).value = val;
                            $(window.autogoogleplace.address2_input).trigger('keyup').trigger('keypress').trigger('blur').trigger('change');
                        }

                    }

                }

            }

        }

        // Filtering duplicates, IE9+ compat
        temp_array = temp_array.filter(function(x, i, a) {
            return a.indexOf(x) == i;
        });

        if (!number_after_street) {
            street = (temp_array.join('') + ' ').replace('  ', ' ');
        }

        if (autogoogleplace_long_address == 0) {
            street += routes.reduce(
                function(a, b) {
                    return a.length < b.length ? a : b;
                }
            );
        } else {
            street += routes.reduce(
                function(a, b) {
                    return a.length > b.length ? a : b;
                }
            );
        }

        if (number_after_street) {
            street += ' ' + street_number.toString();
        }

    }

    // Better version, not always available, so we kept fallback
    if (typeof place.name !== 'undefined' && autogoogleplace_long_address == 0) {

        street = place.name;

    }

    if (typeof place.adr_address !== 'undefined' && autogoogleplace_long_address == 0) {

        // It's HTML
        var html_place = $('<div />').html(place.adr_address);
        var res_street = html_place.find('.street-address');
        var res_city = html_place.find('.locality');

        if (res_street.length >= 1 && res_street.text().length > 1 && street.length < 3) {
            street = res_street.text();
        }

        if (res_city.length >= 1 && res_city.text().length > 1) {
            city = res_city.text();
        }

    }

    if (city && window.autogoogleplace.city_input) {
        document.getElementById(window.autogoogleplace.city_input).value = city;
        $('#' + window.autogoogleplace.city_input).trigger('keyup').trigger('keypress').trigger('blur').trigger('change');
    }

    if (street.length > 1 && window.autogoogleplace.id_address_input) {
        document.getElementById(window.autogoogleplace.id_address_input).value = street;
        $('#' + window.autogoogleplace.id_address_input + ',#' + window.autogoogleplace.city_input + ',#' + window.autogoogleplace.postcode_input).addClass('input-success');
    }

    if (state_1 && country_iso && only_state_1.includes(country_iso)) {

        setStateInput(state_1);

    } else if (state_2 && country_iso && only_state_2.includes(country_iso)) {

        setStateInput(state_2);

    } else if (state_3 && country_iso && only_state_3.includes(country_iso)) {

        setStateInput(state_3);

    } else {

        if (state_1) {
            setStateInput(state_1);
        }

        if (state_2) {
            setStateInput(state_2);
        }

        if (state_3) {
            setStateInput(state_3);
        }

    }

    // Bugfix uniform.js
    if (typeof $.uniform == 'object') {
        $.uniform.update();
    }


}

function setStateInput(state) {

    if (state.length == 0) {
        return;
    }

    $state_input = $('#' + window.autogoogleplace.id_address_input).closest('form').find('select[id*="state"]');

    if ($state_input.length) {

        $selected_option = $state_input.find("option:contains('" + state.replace('-', ' ') + "'), option:contains('" + state + "'), option[value='" + state + "']");

        if ($selected_option.length) {

            // Prestashop core updates the field every time you select a new country
            // which leads to update this field and create a loop
            // Dirty trick, but working as expected
            for (var i = 0; i < 100; i++) {

                var time = i * 100 + 1000;

                setTimeout(function() {

                    if ($selected_option.attr('selected') != 'selected') {

                        $selected_option
                            .prop('selected', true)
                            .attr('selected', true)
                            .click()
                            .change();

                    }

                }, time);

            }

        } else {

            if (typeof window.autogoogleplace_states[state] !== 'undefined' && $("#" + window.autogoogleplace.state_input + ' option[value=' + window.autogoogleplace_states[state] + ']').length > 0) {

                if ($("#" + window.autogoogleplace.state_input + ' option:selected').attr('value') != window.autogoogleplace_states[state]) {

                    // Checking DB related Id's
                    // console.log('DB state', window.autogoogleplace_states[state], state);
                    $("#" + window.autogoogleplace.state_input + ' option[value=' + window.autogoogleplace_states[state] + ']').prop('selected', true).attr('selected', true).click().change();

                }

            }

        }

    }

}

/*
Future version v3, activate at your own risk
function geoPlace(window.autogoogleplace.id_address_input) {

    if(window.autogoogleplace.id_address_input && typeof google.maps !== 'undefined' && typeof google.maps.Geocoder !== "undefined") {

        var geocoder = new google.maps.Geocoder;

        if (typeof navigator.geolocation !== "undefined") {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
            geocoder.geocode({'location': pos}, function(results, status) {
              if (status === 'OK') {
                if (typeof results[0] !== 'undefined' && results[0].formatted_address) {
                    document.getElementById(window.autogoogleplace.id_address_input).value = results[0].formatted_address;
                }
              }
            });
            }, function() {
                console.log('unable to detect location');
            });
        }
        
    }

}
*/

function loadAddressHelper(parent_selector = '') {

    if ($(parent_selector + 'input#address1').length > 0) {
        window.autogoogleplace.id_address_input = 'address1';

    } else if ($(parent_selector + 'input[name*="address1"]').length > 0) {

        // 1.7 classic theme ?
        var inputs_to_check = ['address1', 'address2', 'city', 'postcode']
        var selects_to_check = ['country', 'state'];

        for (var i = inputs_to_check.length - 1; i >= 0; i--) {

            // Unbinding previous entries
            $('#autogoogleplace-' + inputs_to_check[i]).removeAttr('id', '');

            if ($(parent_selector + 'input[name*="' + inputs_to_check[i] + '"]').length > 0 && typeof $(parent_selector + 'input[name*="' + inputs_to_check[i] + '"]').attr('id') == 'undefined') {
                $(parent_selector + 'input[name*="' + inputs_to_check[i] + '"]').attr('id', 'autogoogleplace-' + inputs_to_check[i]);
                // console.log(parent_selector, inputs_to_check[i], 'autogoogleplace-'+inputs_to_check[i], $(parent_selector + 'input[name*="'+inputs_to_check[i]+'"]'));
            }
        }

        for (var i = selects_to_check.length - 1; i >= 0; i--) {

            // Unbinding previous entries
            $('#autogoogleplace-' + selects_to_check[i]).removeAttr('id', '');

            if ($(parent_selector + 'select[name*="' + selects_to_check[i] + '"]').length > 0 && typeof $(parent_selector + 'select[name*="' + selects_to_check[i] + '"]').attr('id') == 'undefined') {
                $(parent_selector + 'select[name*="' + selects_to_check[i] + '"]').attr('id', 'autogoogleplace-' + selects_to_check[i]);
            }
        }

        window.autogoogleplace.id_address_input = $(parent_selector + 'input[name*="address1"]').attr('id');

    } else if ($(parent_selector + 'input#address').length > 0) {
        window.autogoogleplace.id_address_input = 'address';
    } else if ($(parent_selector + '#street').length > 0) {
        window.autogoogleplace.id_address_input = 'street';
    } else if ($(parent_selector + '#road').length > 0) {
        window.autogoogleplace.id_address_input = 'road';
    } else if ($(parent_selector + 'input[type="text"][id*="address"]').length > 0) {
        window.autogoogleplace.id_address_input = $(parent_selector + 'input[type="text"][id*="address"]').eq(0).attr('id');
    } else if ($(parent_selector + 'textarea[id*="address"]').length > 0) {
        window.autogoogleplace.id_address_input = $(parent_selector + 'textarea[id*="address"]').eq(0).attr('id');
    }

    if (typeof window.autogoogleplace.id_address_input != 'undefined') {

        initialize_autogoogleplace();

        if (typeof window.autogoogleplace_disable_browser_autocomplete != "undefined" && parseInt(window.autogoogleplace_disable_browser_autocomplete) == 1) {
            $('#' + window.autogoogleplace.id_address_input).prop('autocomplete', 'chrome-off');
        }

        var gmap_input_selected = document.getElementById(window.autogoogleplace.id_address_input);
        google.maps.event.addDomListener(gmap_input_selected, 'keydown', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });
        // Prevent some misunderstanding
        $(window.autogoogleplace.id_address_input).attr('autocomplete', 'off');
    }
}

function loadAutogoogleWrapper() {

    // Backoffice debug handler
    if ($('#gmaps-api-results').length) {

        function URLify(string) {

            var urls = string.match(/(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)/g);

            if (urls) {
                urls.forEach(function(url) {
                    string = string.replace(url, '<a target="_blank" href="' + url + '">' + url + "</a>");
                });
            }

            return string.replace("(", "<br/>(");

        }

        function handleMapsMessages(message) {

            $errors = $('#gmaps-api-results').html();

            if (!$('#gmaps-api-errors').text().includes(message)) {

                $('#gmaps-api-results').html($errors + '<div class="alert alert-warning">' + URLify(message) + "</div>");

                $('#gmap-faq:hidden').show(0);
                $('#gmaps-api-loading').hide(0);

            }

        }

        var originalConsoleWarn = console.warn;

        console.warn = function(message) {
            if (message.includes('Maps')) {
                handleMapsMessages(message);
            } else {
                originalConsoleWarn(message);
            }
        };

        var originalConsoleError = console.error;

        console.error = function(message) {
            if (message.includes('Maps')) {
                handleMapsMessages(message);
            } else {
                originalConsoleError(message);
            }
        };

        var errors = !checkGooglePlaceCompatibility();

        if (!errors && $('#gmap-test-input').length) {

            try {

                autocomplete = new google.maps.places.Autocomplete(
                    (document.getElementById('gmap-test-input')), {}
                );

            } catch (error) {

                // handle error
                console.log(error.message);
                errors = true;

            }

            setTimeout(function() {
                $('#gmap-test-input').val('Paris').keypress().focus();
            }, 3000, $);

        }

        if (errors) {

            $('#gmaps-api-generic-error').show(0);
            $('#gmaps-api-loading').hide(0);

        } else {

            setTimeout(function() {

                if ($('#gmaps-api-results .alert-warning:visible').length == 0) {
                    $('#gmaps-api-generic-success').show(0);
                    $('#gmaps-api-loading').hide(0);
                }

            }, 7500, $);

        }

    }

    if (checkGooglePlaceCompatibility() === true) {

        // CSS Fix with Prestashop 1.7 stretching some places
        if ($('body#checkout .container').css('min-height') == '100%') {
            $('body#checkout .container').css({ minHeight: 'auto' });
        }

        loadAddressHelper();

        // Hack ajax loading in authentication page for certain configuration 
        // with 5 step checkout and address when ajax on authentication
        $(document)
            .on('click', '#tc-container .second-address', function() {

                resetGoogleAutocomplete();

                setTimeout(function() {
                    loadAddressHelper('#thecheckout-address-delivery ');
                }, 500);

            })
            .on('click keypress', '#SubmitCreate', function() {

                resetGoogleAutocomplete();

                setTimeout(function() {
                    loadAddressHelper();
                }, 3500);

            })
            .on('change', 'select[name="id_country"]', function() {

                // Bugfix after some late JS loading in prestashop 1.7
                setTimeout(function() {
                    loadAddressHelper();
                }, 2500);

            });

    }

}

function checkGooglePlaceCompatibility() {
    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined' && typeof google.maps.places.Autocomplete !== 'undefined') {
        return true;
    } else {
        return false;
    }
}

(function(app) {

    app.loadAutoPlace = function() {
        loadAutogoogleWrapper();
    };

    app.loadGoogleMapsScript = function() {

        if (typeof mapsapikey !== 'undefined') {

            if (checkGooglePlaceCompatibility() === false) {

                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&' +
                    'callback=app.loadAutoPlace' +
                    '&key=' + mapsapikey;
                document.body.appendChild(script);

            } else {
                loadAutogoogleWrapper();
            }

        } else {
            alert('Please define your Google Maps API Key in Autogooglesuggest module backoffice.');
        }

    };

}(window.app = window.app || {}));

window.onload = app.loadGoogleMapsScript;

setTimeout(function() {
    // Hack againts late loader
    if (typeof loadAutogoogleWrapper !== 'undefined') {
        window.app.loadGoogleMapsScript();
    }
}, 750);