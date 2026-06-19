/**
 * 2007-2020 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

(function ($) {
	'use strict';
	// Apply Google autocomplete to billing address

	if (opggApilanguage !='')	{
		var lng =  '&language=' + opggApilanguage ;
	}else {
		var lng =  '' ;
	}
	$.getScript(
		'https://maps.googleapis.com/maps/api/js?key=' + opggApiKey + '&libraries=places' + lng + '&callback=initAutocomplete&ver=1.0.0'
	);

	$('[name ="address1"]').attr('id', 'address1');
	var  autocomplete;

	
	var componentPS = {
		address2: 'short_name',
		postcode: 'long_name',
		city: 'long_name',
		id_country: 'long_name',
		id_state: 'long_name',
	};
	for (var component in componentPS) {
		$('[name ="' + component + '"]').attr('id', component);
	}

	window.initAutocomplete = function () {
		// Create the autocomplete object, restricting the search predictions to
		// geographical location types.
		autocomplete = new google.maps.places.Autocomplete(
			document.getElementById('address1'),
			{
				types: ['geocode'],
				
			}
		);
		if (opggApirestricttopays !='')	
		{	autocomplete.setComponentRestrictions({
				country: opggApirestricttopays.split(","),
	  });
	}
	if (opggApirestricttopostcode !='')	
		{	autocomplete.setComponentRestrictions({
				postalCode: opggApirestricttopostcode.split(",")
	  });
	}
		// Avoid paying for data that you don't need by restricting the set of
		// place fields that are returned to just the address components.
		autocomplete.setFields(['address_component']);

		// When the user selects an address from the drop-down, populate the
		// address fields in the form.
		autocomplete.addListener('place_changed', fillInAddress);
    };
   
	function fillInAddress() {
		// Get the place details from the autocomplete object.
		var place = autocomplete.getPlace();

		for (var component in componentPS) {
			if (component != 'id_country' && component != 'id_state') {
				document.getElementById(component).value = '';
				document.getElementById(component).disabled = false;
			}
		}

		// Get each component of the address from the place details,
		// and then fill-in the corresponding field on the form.
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];

			
			if (addressType == 'street_number' || addressType == "town_square") {
				var stree = place.address_components[i]['long_name'];
				jQuery('#address1').val(place.address_components[i]['long_name']);
			}
			if (addressType == 'sublocality_level_1') {
				var sublocality_level_1 = place.address_components[i]['long_name'];
			}
			if (addressType == 'locality') {
				var locality = place.address_components[i]['long_name'];
			}
			if (addressType == 'administrative_area_level_1') {
				var administrative_area_level_1 = place.address_components[i]['long_name'];
			}
			if (addressType == 'administrative_area_level_2') {
				var administrative_area_level_2 = place.address_components[i]['long_name'];
			}
			if (addressType == 'postal_code') {
				var postal_code = place.address_components[i]['long_name'];
			}


			if (addressType == 'route') {
				var $street = jQuery('#address1').val();
				var route = place.address_components[i]['long_name'];
				jQuery('#address1').val($street + ' ' + place.address_components[i]['long_name']);
			}

		


			if (addressType == 'postal_town' || addressType == 'locality') {
				jQuery('#city').val(place.address_components[i]['long_name']);
			}

			if (addressType == 'postal_code') {
				jQuery('#postcode').val(place.address_components[i]['long_name']);
			}

			if (addressType == 'country') {
				var countr = $('#id_country option')
					.filter(function () {
						return this.text == place.address_components[i]['long_name'];
					})
                    .val();
                    
                if(typeof(countr)=='undefined') alert(opcheckcountry.replace("{country}", place.address_components[i]['long_name']));
				$('#id_country').val(countr).change();
			}

			if (addressType == 'administrative_area_level_2') {
                window.localStorage.setItem(
					'states',
					place.address_components[i]['long_name']
                );
                
                $( document ).ajaxComplete(function() {
                    var countr = $('#id_state option')
                    .filter(function () {

						var stat = window.localStorage.getItem('states'); 
						stat =  stat.replace("Provincia di ", ''); 
						stat =  stat.replace("Città Metropolitana di ", ''); 
                        return this.text == stat ;
                    })
                    .val();
                  
                $('#id_state').val(countr).change();
                  });
	


			}
		}
		var format_address = '';

		if(oppgg_format['opgg_format_county_'+iso_gg] != "" ){
			format_address = oppgg_format['opgg_format_county_'+iso_gg];
			format_address = format_address.replace("{street}", stree);
			format_address = format_address.replace("{route}", route);
			format_address = format_address.replace("{sublocality_level_1}", sublocality_level_1);
			format_address = format_address.replace("{locality}", locality);
			format_address = format_address.replace("{administrative_area_level_1}", administrative_area_level_1);
			format_address = format_address.replace("{administrative_area_level_2}", administrative_area_level_2);
			format_address = format_address.replace("{postal_code}", postal_code);
			format_address = format_address.replaceAll("undefined", '');

			if(format_address != ""){
				jQuery('#address1').val(format_address);
			}

		}
	}

	$('body').on('Focus', '[name ="address1"]', function () {
		geolocate();
	});

	// Bias the autocomplete object to the user's geographical location,
	// as supplied by the browser's 'navigator.geolocation' object.
	function geolocate() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				var geolocation = {
					lat: position.coords.latitude,
					lng: position.coords.longitude,
				};
				var circle = new google.maps.Circle({
					center: geolocation,
					radius: position.coords.accuracy,
				});
				autocomplete.setBounds(circle.getBounds());
			});
		}
	}
})(jQuery);
