{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

{literal}
<script async
        src="https://maps.googleapis.com/maps/api/js?key={/literal}{$z_tc_config->google_maps_api_key|escape:'javascript':'UTF-8'}{literal}&libraries=places&loading=async&callback=googlePlacesScriptLoadCallbackOnce">
</script>
    <script>

        function googlePlacesScriptLoadCallbackOnce() {
            if (typeof googlePlacesScriptLoadCallbackOnce.called === 'undefined') {
                googlePlacesScriptLoadCallbackOnce.called = true;
                googlePlacesScriptLoadCallback();
            }
        }

        function tc_reInitGooglePlaces() {
            googlePlacesScriptLoadCallback();
        }
        function isAddressController() {
            return document.querySelector('body#address') !== null;
        }
        var tc_autocomplete = {}
        var debug_google_places = 0;
        var isoCodes = [];

        try {
            if (typeof tc_countriesIsoCodes !== 'undefined' && tc_countriesIsoCodes.length) {
                isoCodes = JSON.parse(tc_countriesIsoCodes);
            }
        } catch (e) {
            console.warn('Countries ISO codes parsing failed!');
        }

        function googlePlacesScriptLoadCallback() {
            if (debug_google_places == 1) {
                console.log('googlePlacesScriptLoadCallback');
            }
            // addEventListener('DOMContentLoaded', (event) => {
            /* global google */
            if (google) {
                if (isAddressController()) {
                    // When included for Prestashop's address edit controller
                    tc_autocomplete['address'] = new google.maps.places.Autocomplete(document.querySelector(`[name=address1]`), {
                        fields: ['address_components'],
                        strictBounds: false,
                        types: ['address'],
                        // TODO: component restriction based on selected country
                        // TODO: what about US states?
                        // componentRestrictions: { country: ['sk'] },
                    });
                    tc_autocomplete['address'].addListener('place_changed', () => googlePlaceChanged('address', tc_autocomplete['address'].getPlace()));

                    // jQuery version
                    // const idCountry = $(`[name=id_country]`).val();

                    // Vanilla JS version
                    const idCountry = document.querySelector(`[name=id_country]`).value;

                    const matchingCountry = isoCodes.find(x => x.id_country == idCountry);
                    if (typeof matchingCountry !== undefined && matchingCountry !== null) {
                        const initialIso = matchingCountry?.iso_code;
                        tc_autocomplete['address'].setComponentRestrictions({'country': initialIso});
                    }

                    if (typeof prestashop?.on === 'function') {
                        prestashop.on('updatedAddressForm', function () {
                            tc_reInitGooglePlaces();
                        })
                    }
                } else {
                    // When included for TheCheckout module
                    for (const tc_addr_type of ['invoice', 'delivery']) {
                        // console.log('Attempt to bind ' + tc_addr_type, document.querySelector(`[data-address-type=${tc_addr_type}] [name=address1]`))
                        tc_autocomplete[tc_addr_type] = new google.maps.places.Autocomplete(document.querySelector(`[data-address-type=${tc_addr_type}] [name=address1]`), {
                            fields: ['address_components'],
                            strictBounds: false,
                            types: ['address'],
                            // TODO: component restriction based on selected country
                            // TODO: what about US states?
                            // componentRestrictions: { country: ['sk'] },
                        });
                        tc_autocomplete[tc_addr_type].addListener('place_changed', () => googlePlaceChanged(tc_addr_type, tc_autocomplete[tc_addr_type].getPlace()));

                        // jQuery version
                        // const initialIso = $(`[data-address-type=${tc_addr_type}] [name=id_country]`).children('option').filter(':selected').attr('data-iso-code');

                        // Vanilla JS version
                        const initialIso = document.querySelector(`[data-address-type=${tc_addr_type}] [name=id_country] option:checked`)?.dataset?.isoCode

                        if (initialIso !== undefined && initialIso !== null) {
                            tc_autocomplete[tc_addr_type].setComponentRestrictions({'country': initialIso});
                        }

                        // jQuery version
                        // $('body').off('googlePlacesScriptLoadCallback').on('change.componentRestrictions', `[data-address-type=${tc_addr_type}] [name=id_country]`, function() {
                        //     // console.log('Google places, change.componentRestrictions change listener called');
                        //     if (tc_autocomplete && tc_autocomplete[tc_addr_type]) {
                        //         const iso = $(this).children('option').filter(':selected').attr('data-iso-code');
                        //         if (debug_google_places == 1) {
                        //             console.log(`setting '${iso}' as component/country restriction`)
                        //         }
                        //         tc_autocomplete[tc_addr_type].setComponentRestrictions({'country': iso});
                        //     }
                        // });

                        // Vanilla JS version
                        document.body.addEventListener('change', function(event) {
                            if (event.target.matches(`[data-address-type=${tc_addr_type}] [name=id_country]`)) {
                                if (tc_autocomplete && tc_autocomplete[tc_addr_type]) {
                                    const iso = event.target.querySelector('option:checked')?.dataset?.isoCode;
                                    if (debug_google_places == 1) {
                                        console.log(`setting '${iso}' as component/country restriction (vanilla JS)`)
                                    }
                                    tc_autocomplete[tc_addr_type].setComponentRestrictions({'country': iso});
                                }
                            }
                        });

                    }
                }
            }
            // });
        }

        function googlePlaceChanged(addressType, place) {
            if (place.address_components) {
                if (debug_google_places == 1) {
                    console.log(addressType, place);
                }
                const placeDetails = place.address_components.reduce((acc, x) => ({...acc, [x.types[0]]: x.long_name}), {});
                if (debug_google_places == 1) {
                    console.log('Place details: ', placeDetails);
                }

                const streetNumberFirst = ['US', 'GB', 'AU'].includes(tc_autocomplete[addressType]?.componentRestrictions?.country);
                const stateFromAdministrativeArea2 = ['IT'].includes(tc_autocomplete[addressType]?.componentRestrictions?.country);

                const tc_place_address = {};
                // DISABLED: Automatic street number reordering
                // Keep the address as entered by Google Places without reordering
                tc_place_address.street = place.name || `${placeDetails?.street_number || ''} ${placeDetails?.route || ''}`.trim();
                
                // Original code commented out:
                // if (streetNumberFirst) {
                //     tc_place_address.street = `${placeDetails?.subpremise ? placeDetails.subpremise + '/' : ''}${placeDetails?.street_number || placeDetails?.premise || ''} ${placeDetails?.route || ''}`;
                // } else {
                //     tc_place_address.street = `${placeDetails?.route || placeDetails?.locality} ${placeDetails?.street_number || placeDetails?.premise || ''}`;
                // }
                //
                // if (streetNumberFirst) {
                //     tc_place_address.street = `${placeDetails?.street_number || ''} ${placeDetails?.route || ''}`;
                // } else {
                //     tc_place_address.street = `${placeDetails?.route || ''} ${placeDetails?.street_number || ''}`;
                // }
                tc_place_address.city = placeDetails?.locality || placeDetails?.sublocality_level_1 || placeDetails?.postal_town || '';
                tc_place_address.postcode = placeDetails?.postal_code || '';

                if (stateFromAdministrativeArea2) {
                    var provincia = placeDetails?.administrative_area_level_2 || placeDetails?.administrative_area_level_1 || '';
                    tc_place_address.state = provincia.replace(/^.*? di /, '');
                } else {
                    tc_place_address.state = placeDetails?.administrative_area_level_1 || '';
                    if (['RO'].includes(tc_autocomplete[addressType]?.componentRestrictions?.country)) {
                        tc_place_address.state = tc_place_address.state.replace(/^Județul\s?/, '');
                    }
                }

                if (debug_google_places == 1) {
                    console.log(tc_place_address);
                }

                var mapPlacePropsToFields = {
                    address1: 'street',
                    city: 'city',
                    postcode: 'postcode',
                    id_state: 'state'
                }

                var el;
                var stateEl;
                for (const [fieldName, propName] of Object.entries(mapPlacePropsToFields)) {
                    // jQuery version
                    // if (isAddressController()) {
                    //     el = $(`[name=${fieldName}]`);
                    // } else {
                    //     el = $(`[data-address-type=${addressType}] [name=${fieldName}]`);
                    // }
                    // if (tc_place_address && tc_place_address[propName]) {
                    //     if (propName === 'state') {
                    //         stateEl = el.find('option').filter(function() {
                    //             return $.trim($(this).text()).toLowerCase().replace(/[^a-z]/g,'') === (tc_place_address[propName] || '').toLowerCase().replace(/[^a-z]/g,'');
                    //         });
                    //         if (stateEl && stateEl.length) {
                    //             stateEl.attr('selected', true).trigger('change');
                    //         }
                    //     } else {
                    //         el.val(tc_place_address[propName] || '');
                    //         setTimeout(function (thisEl) {
                    //             // console.log('el.val', thisEl.val())
                    //             thisEl.change();
                    //         }, 100, el);
                    //     }
                    // }

                    // Vanilla JS version
                    if (isAddressController()) {
                        el = document.querySelector(`[name=${fieldName}]`);
                    } else {
                        el = document.querySelector(`[data-address-type=${addressType}] [name=${fieldName}]`);
                    }
                    if (tc_place_address && tc_place_address[propName] && el) {
                        if (propName === 'state') {
                            stateEl = el.querySelector('option:checked');
                            stateEl = Array.from(el.querySelectorAll('option')).filter(x =>
                                    x.innerText.trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z]/g, '') === (tc_place_address[propName] || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z]/g, ''));
                            if (stateEl && stateEl.length) {
                                stateEl[0].selected = true;
                                stateEl[0].dispatchEvent(new Event('change'));
                            }
                        } else {
                            el.value = tc_place_address[propName] || '';
                            setTimeout(function (thisEl) {
                                // console.log(`change() ${thisEl.name}: ${thisEl.value}`)
                                // Prefer jquery call so that attached event handlers are properly executed
                                if (typeof $ === 'function' && typeof $(thisEl) !== 'undefined' && typeof $(thisEl).change === 'function') {
                                    const event = new Event('change', { bubbles: true });
                                    thisEl.dispatchEvent(event); // change() wasn't detected by review block (monitorAddressChanges), this helped
                                    $(thisEl).change();
                                } else {
                                    thisEl.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            }, 100, el);
                        }
                    }
                }
            }
        }
    </script>
{/literal}