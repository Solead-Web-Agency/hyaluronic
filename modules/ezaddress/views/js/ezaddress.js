/**
* Copyright EZMods
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 website only.
* If you want to use this file on more websites ( or projects ), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.
*
* @author EZ Mods
* @copyright  EZ Mods
* @license    Valid for 1 website ( or project ) for each purchase of license
*/
if (!document.body.classList.contains('country-fr')) {
var selCountryId,selStateId,googleReady,autocomplete,langParam="",inadsAPI=(1==ezaddress_autolang&&("geoapify"==ezaddress_api?langParam="&lang="+ezaddress_lang:"google"==ezaddress_api&&(langParam="&language="+ezaddress_lang)),"https://inaadress.maaamet.ee/inaadress/gazetteer"),geoapifyAPI="https://api.geoapify.com/v1/geocode/autocomplete?apiKey="+ezaddress_api_key+langParam,googleAPI="https://maps.googleapis.com/maps/api/js?key="+ezaddress_api_key+"&callback=googleAddressAutocomplete&libraries=places&v=weekly"+langParam,companyInput="#field-company",vatInput="#field-vat_number",address1Input="#field-address1",address2Input="#field-address2",cityInput="#field-city",stateInput="#field-id_state",postcodeInput="#field-postcode",countrySelect="#field-id_country",alertContainer=".ez-autocomplete__alert",selectDropItem=".ez-autocomplete .dropdown .dropdown-menu .dropdown-item",selectDrop=".ez-autocomplete .dropdown .dropdown-menu",icon=".ez-autocomplete__input__icon",iconLoadingClass="ez-autocomplete__input__icon--loading",countriesData=JSON.parse(ezaddress_countries),enabledCountries=[],debounceDelay=300,minChar=3;function hideExtraFields(){$(companyInput).val()||$(vatInput).val()||$(vatInput).closest(".form-group").hide(),$(companyInput).on("input",function(e){0<this.value.length||0<$(vatInput).val().length?$(vatInput).closest(".form-group").slideDown(200):$(vatInput).closest(".form-group").slideUp(200)}),$(postcodeInput).val()||$(cityInput).val()||$(address2Input+","+cityInput+","+stateInput+","+postcodeInput+","+countrySelect).closest(".form-group").hide(),$(address1Input).on("input",function(e){var a=this.value;a&&a.replace(/\s/g,"").length>=minChar&&$(address2Input+","+cityInput+","+stateInput+","+postcodeInput+","+countrySelect).closest(".form-group").slideDown(200)})}function fixLabelClick(){$("body").on("click",".firstname .form-control-label, .lastname .form-control-label",function(){$("#checkout-login-form").hasClass("active")&&($("#checkout-login-form").removeClass("active"),$("#checkout-guest-form").addClass("active"))})}function ezAddressAutocomplete(){var t,o={abort:function(){}};function s(e){var a,l=0;"geoapify"==ezaddress_api?a="features":"inads"==ezaddress_api&&(a="addresses"),$.each(e[a],function(e,a){var t,o,s,n,r,d,i="";"geoapify"==ezaddress_api?(t=a.properties.address_line1,o=a.properties.formatted,s=a.properties.city,n=a.properties.postcode,r=a.properties.state_code,d=a.properties.country_code,a.properties.category&&(i=a.properties.category)):"inads"==ezaddress_api&&(t=a.aadresstekst,o=a.ipikkaadress,n=a.sihtnumber,d="ee",s=!a.asustusyksus||0<=a.asustusyksus.indexOf("linnaosa")?a.omavalitsus:a.asustusyksus),("inads"==ezaddress_api||"geoapify"==ezaddress_api&&"building"==a.properties.result_type&&-1!==$.inArray(d,enabledCountries)||"geoapify"==ezaddress_api&&i.includes("building")&&-1!==jQuery.inArray(d,enabledCountries))&&(l=1,$(selectDrop).append('<li><a href="#" class="dropdown-item" data-address="'+t+'" data-postcode="'+n+'" data-city="'+s+'" data-state="'+r+'" data-country="'+d+'">'+o+"</a></li>"))}),l&&$(selectDrop).closest(".dropdown").addClass("open"),$(icon).removeClass(iconLoadingClass),$(icon).find("i").text("search")}$(address1Input).on("input",function(e){clearTimeout(t);var a=this.value;$(selectDrop).closest(".dropdown").removeClass("open"),$(selectDrop).find("li").remove(),o.abort(),a&&a.replace(/\s/g,"").length>=minChar?t=setTimeout(function(){var e;e=a,$(icon).find("i").text("autorenew"),$(icon).addClass(iconLoadingClass),"geoapify"==ezaddress_api?o=$.ajax({url:geoapifyAPI,data:{text:e},global:!1}).done(function(e){s(e)}).fail(function(e,a,t){"abort"!=t&&($(icon).removeClass(iconLoadingClass),$(icon).find("i").text("error_outline"))}):"inads"==ezaddress_api&&(o=$.ajax({url:inadsAPI,data:{address:e,appartment:2,ihist:0,unik:1,results:10,features:"EHITISHOONE",tech:0,ky:1},global:!1}).done(function(e){s(e)}).fail(function(e,a,t){"abort"!=t&&($(icon).removeClass(iconLoadingClass),$(icon).find("i").text("error_outline"))}))},debounceDelay):($(icon).removeClass(iconLoadingClass),$(icon).find("i").text("search"))})}function googleAddressAutocomplete(){(autocomplete=new google.maps.places.Autocomplete($(address1Input)[0],{fields:["address_components","name"],types:["address"]})).addListener("place_changed",setGoogleAddress)}function setGoogleAddress(){var t,o,s,n,r,e=autocomplete.getPlace(),d="";$.each(e.address_components,function(e,a){switch(a.types[0]){case"postal_code":d=a.long_name;break;case"locality":t=a.long_name;break;case"postal_town":n=a.long_name;break;case"sublocality_level_1":r=a.long_name;break;case"administrative_area_level_1":o=a.short_name;break;case"country":s=a.short_name}}),e=e.name,(t=t||n)||(t=r),-1!==$.inArray(s.toLowerCase(),enabledCountries)?fillAddressFields(e,d,t,o,s):displayAlert(ezaddress_google_error,"danger")}function fillAddressFields(e,a,t,o,s){$.each(countriesData,function(e,a){if(a.iso_code.toLowerCase()==s.toLowerCase())return selCountryId=a.id_country,$.each(a.states,function(e,a){if(a.iso_code.toLowerCase()==o.toLowerCase())return selStateId=a.id_state,!1}),!1}),$(address1Input).val(e),$(postcodeInput).val(a),$(cityInput).val(t),$(countrySelect).val(selCountryId).trigger("change"),$(selectDrop).closest(".dropdown").removeClass("open")}function initEzAddress(){$(".ez-autocomplete").length<=0&&($(address1Input).attr("autocomplete","off").closest(".form-group").addClass("ez-autocomplete"),$(address1Input).wrap('<div class="ez-autocomplete__input"></div>').before('<div class="ez-autocomplete__input__icon"><i class="material-icons" aria-hidden="true">search</i></div>'),$(".ez-autocomplete__input").after('<div class="ez-autocomplete__alert"></div>')),fixLabelClick(),1==ezaddress_autohide&&hideExtraFields(),"google"==ezaddress_api?googleReady&&googleAddressAutocomplete():($(selectDrop).length<=0&&$(address1Input).after('<div class="dropdown"><ul class="dropdown-menu"></ul></div>'),ezAddressAutocomplete())}function clearAlerts(){$(alertContainer).html("")}function displayAlert(e,a){a='<div class="alert alert-'+a+' mt-1 mb-0" role="alert">'+e+"</div>";clearAlerts(),$(a).appendTo(alertContainer)}$.each(countriesData,function(e,a){enabledCountries.push(a.iso_code.toLowerCase())}),$("body").on("click",selectDropItem,function(e){e.preventDefault(),fillAddressFields($(this).data("address"),$(this).data("postcode"),$(this).data("city"),$(this).data("state"),$(this).data("country"))}),$(document).on("click",function(e){0!==$(selectDrop).has(e.target).length||$(address1Input).is(e.target)||$(selectDrop).closest(".dropdown").removeClass("open")}),$(document).ready(function(){var e,a;$('form[action*="address"]')&&(initEzAddress(),"google"==ezaddress_api)&&(e=document.createElement("script"),a=document.getElementsByTagName("script")[0],e.defer=!0,e.src=googleAPI,a.parentNode.insertBefore(e,a),googleReady=!0)}),prestashop.on("updatedAddressForm",function(e){$(stateInput).val(selStateId),initEzAddress()});}

if (document.body.classList.contains('country-fr')) {
document.addEventListener('DOMContentLoaded', function() {
   var proProductIds = [1, 8, 9, 11, 13, 14, 20, 21, 22, 23, 33, 34, 37, 38, 47, 48, 49, 52, 53, 55, 62, 64, 65, 101, 103, 104, 111, 113, 115, 116, 119, 120, 121, 122, 123, 124, 125, 126, 127, 132, 147, 148, 149, 150, 168, 170, 171, 172, 173, 174, 179, 195, 197, 202, 203, 354, 358, 359, 365, 367, 368, 369, 370, 371, 433, 447, 452, 453, 454, 463, 464, 465, 466, 467, 468, 470, 471, 473, 474, 487, 515, 518, 519, 522, 524, 525, 526, 527, 528, 533, 552, 592, 593, 594, 595, 596, 597, 660, 661, 662, 663, 667, 668, 686, 696, 699, 701, 706, 707, 708, 709, 710, 712, 714, 715, 716, 717, 718, 719, 724, 745, 746, 747, 752, 753, 754, 755, 756, 757, 758, 759, 760, 762, 763, 764, 765, 766, 767, 768, 770, 771, 772, 773, 774, 775, 776, 798, 799, 800, 801, 802, 803, 804, 810, 811, 814, 815, 822, 823, 825, 826, 6281, 6282, 6283, 6286, 6287, 6288, 6289, 6291, 6292, 6293, 6294, 6316, 6325, 6326, 6327, 6328, 6329, 6330, 6331, 6332, 6335, 6336, 6337];
    var Zf = ["10", "40"];
    let lastValidatedRpps = null;
    function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
    function updateContinueButton(isValid) {
        var continueButton = document.querySelector('button[name="confirm-addresses"]');
        if (continueButton) {
            continueButton.disabled = !isValid;
            continueButton.style.opacity = isValid ? '' : '0.5';
            continueButton.style.cursor = isValid ? '' : 'not-allowed';
            continueButton.title = isValid ? "" : "Veuillez ajouter un numéro RPPS valide pour continuer.";
            if (!isValid) {
                continueButton.addEventListener('click', preventContinue);
            } else {
                continueButton.removeEventListener('click', preventContinue);
            }
        }
    }

    function preventContinue(event) {
        event.preventDefault();
        event.stopPropagation();
        alert("Veuillez ajouter un numéro RPPS valide pour continuer.");
    }
function updateAddressContinueButton(isValid) {
    var continueButton = document.querySelector('button[name="confirm-addresses"]');
    if (continueButton) {
        continueButton.disabled = !isValid;
        continueButton.style.opacity = isValid ? '' : '0.5';
        continueButton.style.cursor = isValid ? '' : 'not-allowed';
        continueButton.title = isValid ? "" : "Veuillez remplir correctement tous les champs pour continuer.";
        if (!isValid) {
            continueButton.addEventListener('click', preventContinue);
        } else {
            continueButton.removeEventListener('click', preventContinue);
        }
    }
}
    function cartContainsProProduct() {
        if (window.prestashop && window.prestashop.cart && window.prestashop.cart.products) {
            return window.prestashop.cart.products.some(function(product) {
                return proProductIds.includes(parseInt(product.id));
            });
        }
        return false;
    }

    function isCurrentPageProProduct() {
        const productIdMatch = document.body.className.match(/product-id-(\d+)/);
        if (productIdMatch) {
            const productId = parseInt(productIdMatch[1]);
            return proProductIds.includes(productId);
        }
        return false;
    }

    function extractRppsFromAddress(addressElement) {
        if (!addressElement) return null;
        const lines = addressElement.innerHTML.split('');
        for (let line of lines) {
            const potentialRpps = line.trim();
            if (/^\d{11}$/.test(potentialRpps)) {
                return potentialRpps;
            }
        }
        return null;
    }

    function validateRpps(rpps) {
        if (rpps === lastValidatedRpps) {
            return Promise.resolve(true);
        }

        return fetch(`https://gateway.api.esante.gouv.fr/fhir/v1/Practitioner?_pretty=true&_format=json&_revinclude=PractitionerRole:practitioner&active=true&identifier=${rpps}`, {
            headers: {
                "ESANTE-API-KEY": "fbbc02f2-881e-4821-be23-906b3677cb01"
            }
        }).then(response => response.json())
            .then(data => {
                let practitioner = data.entry && data.entry[0] ? data.entry[0].resource : null;
                if (practitioner) {
                    let active = practitioner.active;
                    let codes = practitioner.identifier.map(id => id.value);
                    let isValid = codes.includes(rpps) && active;
                    if (isValid) {
                        lastValidatedRpps = rpps;
                    }
                    return isValid;
                }
                return false;
            });
    }

    function updateRppsField() {
        var rppsField = document.getElementById('field-other');
        var countrySelect = document.getElementById('field-id_country');

        if (!rppsField || !countrySelect) return;

        var rppsLabel = rppsField.closest('.form-group').querySelector('label');
        var commentElement = rppsField.closest('.form-group').querySelector('.form-control-comment');
        var isFrance = countrySelect.value === '8';
        var needsRpps = isFrance && (cartContainsProProduct() || isCurrentPageProProduct());

        if (needsRpps) {
            rppsField.required = true;
            rppsLabel.textContent = 'RPPS';
            rppsLabel.classList.add('required');
            rppsField.style.borderColor = 'red';
            rppsField.pattern = "^\\d{11}$";
            rppsField.title = "Le numéro RPPS est invalide";
            rppsField.maxLength = 11;
            rppsField.setAttribute('onkeydown', "if(event.key === 'Enter'){return false;}");
            rppsField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^\d]/g, '').slice(0, 11);
            });
            if (commentElement) {
                commentElement.textContent = '';
            }

            var warningMessage = document.getElementById('rpps-warning-message');
            if (!warningMessage) {
                warningMessage = document.createElement('div');
                warningMessage.id = 'rpps-warning-message';
                warningMessage.style.color = 'red';
                warningMessage.style.fontWeight = 'bold';
                warningMessage.style.marginTop = '10px';
                warningMessage.style.padding = '10px';
                warningMessage.style.border = '1px solid red';
                warningMessage.style.backgroundColor = '#FFF0F0';
                rppsField.parentNode.appendChild(warningMessage);
            }
            warningMessage.innerHTML = "!";

            rppsField.addEventListener('blur', function() {
                var rpps = rppsField.value;
                if (rpps.length === 11) {
                    validateRpps(rpps).then(isValid => {
                        updateRppsFieldValidation(isValid);
                    });
                }
            });

        } else {
            rppsField.required = false;
            rppsLabel.textContent = 'Autre';
            rppsLabel.classList.remove('required');
            rppsField.style.borderColor = '';
            rppsField.removeAttribute('pattern');
            rppsField.removeAttribute('title');
            rppsField.removeAttribute('maxLength');
            rppsField.removeEventListener('input', function() {});
            if (commentElement) {
                commentElement.textContent = 'Optionnel';
            }

            var warningMessage = document.getElementById('rpps-warning-message');
            if (warningMessage) {
                warningMessage.remove();
            }
        }
    }

    function updateRppsFieldValidation(isValid) {
        var rppsField = document.getElementById('field-other');
        var warningMessage = document.getElementById('rpps-warning-message');
        var continueButton = document.querySelector('button[name="confirm-addresses"]');

        if (isValid) {
            rppsField.style.borderColor = 'green';
            warningMessage.textContent = "Le numéro RPPS est valide.";
            warningMessage.style.color = 'green';
            warningMessage.style.borderColor = 'green';
            warningMessage.style.backgroundColor = '#F0FFF0';
        } else {
            rppsField.style.borderColor = 'red';
            warningMessage.textContent = "Le numéro RPPS est invalide.";
            warningMessage.style.color = 'red';
            warningMessage.style.borderColor = 'red';
            warningMessage.style.backgroundColor = '#FFF0F0';
        }
    updateContinueButton(isValid);
    }

    function isDeliveryAddressFrance() {
        var selectedAddress = document.querySelector('#delivery-addresses .address-item.selected .address');
        return selectedAddress && selectedAddress.textContent.includes('France');
    }


    function addRppsWarning() {
        var addressesSection = document.getElementById('delivery-addresses');
        if (!addressesSection) return;
        var warningElement = document.createElement('div');
        warningElement.id = 'rpps-warning';
        warningElement.style.color = 'red';
        warningElement.style.marginTop = '10px';
        warningElement.style.fontWeight = 'bold';
        warningElement.style.padding = '10px';
        warningElement.style.border = '2px solid red';
        warningElement.style.backgroundColor = '#FFEEEE';
        warningElement.innerHTML = `
            Attention : Produits réservés aux professionnels de santé
            Votre panier contient des produits réservés à la vente aux professionnels de santé. Pour finaliser votre commande, veuillez suivre ces étapes :
            
                Cliquez sur "Modifier" à côté de votre adresse de livraison.
                Ajoutez votre numéro RPPS dans le champ correspondant.
                Sauvegardez vos modifications.
            
            Important : Un numéro RPPS valide est requis pour l'expédition de ces produits. En cas d'absence ou d'invalidité du numéro RPPS, votre commande sera automatiquement annulée et des frais de traitement pourront vous être facturés.
        `;
        addressesSection.parentNode.insertBefore(warningElement, addressesSection.nextSibling);
    }

    function updateRppsWarning() {
        var warningElement = document.getElementById('rpps-warning');
        var isFrance = isDeliveryAddressFrance();
        var needsRpps = isFrance && (cartContainsProProduct() || isCurrentPageProProduct());

        if (needsRpps) {
            if (!warningElement) {
                addRppsWarning();
                warningElement = document.getElementById('rpps-warning');
            }
            var selectedAddress = document.querySelector('#delivery-addresses .address-item.selected .address');
            var rppsFromAddress = extractRppsFromAddress(selectedAddress);
            if (rppsFromAddress) {
                validateRpps(rppsFromAddress).then(isValid => {
                    updateRppsWarningValidation(warningElement, isValid);
                    updateContinueButton(isValid);
                });
            } else {
                updateRppsWarningValidation(warningElement, false);
                updateContinueButton(false);
            }
        } else if (warningElement) {
            warningElement.remove();
            updateContinueButton(true);
        }
    }

    function updateRppsWarningValidation(warningElement, isValid) {
        if (isValid) {
            warningElement.style.color = 'green';
            warningElement.style.borderColor = 'green';
            warningElement.style.backgroundColor = '#F0FFF0';
            warningElement.innerHTML = "RPPS valide. Vous pouvez continuer votre commande.";
        } else {
            warningElement.style.color = 'red';
            warningElement.style.borderColor = 'red';
            warningElement.style.backgroundColor = '#FFEEEE';
            warningElement.innerHTML = `
                Attention : Produits réservés aux professionnels de santé
                Votre panier contient des produits réservés à la vente aux professionnels de santé. Pour finaliser votre commande, veuillez suivre ces étapes :
                
                    Cliquez sur "Modifier" à côté de votre adresse de livraison.
                    Ajoutez votre numéro RPPS dans le champ correspondant.
                    Sauvegardez vos modifications.
                
                Important : Un numéro RPPS valide est requis pour l'expédition de ces produits. En cas d'absence ou d'invalidité du numéro RPPS, votre commande sera automatiquement annulée et des frais de traitement pourront vous être facturés.
            `;
        }
    }

function blockAmazonButtons() {
    console.log("not fr");

    if (document.body.classList.contains('country-fr')) {
    console.log("cest fr");
        var amazonButtons = document.querySelectorAll('[class*="amazon"], [id*="amazon"]');
        amazonButtons.forEach(function(button) {
            if (!button.classList.contains('blocked')) {
                button.classList.add('blocked');
                button.style.pointerEvents = 'none';
                button.style.opacity = '0.5';
                var warningMessage = document.createElement('div');
                warningMessage.textContent = "Ce mode de paiement n'est pas disponible pour les produits réservés aux professionnels de santé.";
                warningMessage.style.color = 'red';
                warningMessage.style.marginTop = '5px';
                button.parentNode.insertBefore(warningMessage, button.nextSibling);
            }
        });
    }
}
    
    function checkAndBlockAmazonButtons() {
      if (document.body.classList.contains('country-fr') &&(cartContainsProProduct() || isCurrentPageProProduct())) {
        if (document.body.classList.contains('country-fr')){
        blockAmazonButtons()}
    }
}

    async function handleCountryChange() {
    await sleep(3000); // Pause de 3 secondes pour permettre le rechargement du formulaire
    updateRppsField();
    checkAndBlockAmazonButtons();
    updateRppsWarning();
}


    // Script pour page produit
    if (document.body.classList.contains('country-fr')) {
        const productIdMatch = document.body.className.match(/product-id-(\d+)/);
        if (productIdMatch) {
            const productId = parseInt(productIdMatch[1]);
            if (proProductIds.includes(productId)) {
                const shortDescription = document.querySelector('.rte-content.product-description');
                if (shortDescription) {
                    const warningMessage = document.createElement('p');
                    warningMessage.style.color = 'red';
                    warningMessage.style.fontWeight = 'bold';
                    warningMessage.innerHTML = "!";
                    shortDescription.insertBefore(warningMessage, shortDescription.firstChild);
                }
            }
        }
    }

    // Vérification périodique du changement de pays
    let previousValue = document.getElementById('field-id_country') ? document.getElementById('field-id_country').value : null;

    setInterval(function() {
        let countrySelect = document.getElementById('field-id_country');
        if (countrySelect) {
            let currentValue = countrySelect.value;
            if (currentValue !== previousValue) {
                previousValue = currentValue;
                let event = new Event('change');
                countrySelect.dispatchEvent(event);
                handleCountryChange();
            }

            // Revalider le RPPS si le champ est visible et actif
            var rppsField = document.getElementById('field-other');
            if (rppsField && rppsField.required && rppsField.value.length === 11) {
                validateRpps(rppsField.value);
            }
        }
    }, 1000);

    // Initialisation et écouteurs d'événements
    document.addEventListener('DOMContentLoaded', function() {
        updateRppsField();
        if (document.body.classList.contains('country-fr')) {
        checkAndBlockAmazonButtons();
        }
        updateRppsWarning();
           var inputFields = document.querySelectorAll('input, textarea, select');
    inputFields.forEach(function(field) {
       if (!field.classList.contains('form-search-control')){
        field.setAttribute('onkeydown', "if(event.key === 'Enter'){return false;}");
        }
    });
    // Désactiver le bouton "Continuer" par défaut
    // Initialiser l'état du bouton "Continuer"
    var continueButton = document.querySelector('button[name="confirm-addresses"]');
    if (continueButton && document.body.classList.contains('country-fr')) {
        // Vérifiez si le panier contient des produits réservés
        var needsRpps = cartContainsProProduct() || isCurrentPageProProduct();
        continueButton.disabled = needsRpps;
        continueButton.style.opacity = needsRpps ? '0.5' : '';
        continueButton.style.cursor = needsRpps ? 'not-allowed' : '';

        // Empêcher le clic sur le bouton désactivé
        continueButton.addEventListener('click', function(event) {
            if (continueButton.disabled) {
                event.preventDefault();
                event.stopPropagation();
                alert("Veuillez ajouter un numéro RPPS valide pour continuer.");
            }
        });
    }
        var countrySelect = document.getElementById('field-id_country');
        if (countrySelect) {
            countrySelect.addEventListener('change', handleCountryChange);
        }

        document.addEventListener('change', function(event) {
            if (event.target.name === 'id_address_delivery') {
                updateRppsWarning();
            }
        });

        var observer = new MutationObserver(function(mutations) {
            checkAndBlockAmazonButtons();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    console.log("Script RPPS et blocage Amazon chargé et exécuté");
});}