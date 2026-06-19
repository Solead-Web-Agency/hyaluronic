 if (document.body.classList.contains('country-fr')) {
(function() {
    var proProductIds = [1, 472, 8, 9, 11, 13, 14, 20, 21, 22, 23, 33, 34, 37, 38, 47, 48, 49, 52, 53, 55, 62, 64, 65, 101, 103, 104, 111, 113, 115, 116, 119, 120, 121, 122, 123, 124, 125, 126, 127, 132, 144, 145, 146, 147, 148, 149, 150, 168, 170, 171, 172, 173, 174, 179, 195, 197, 202, 203, 302, 306, 304, 688, 689, 310, 354, 358, 359, 365, 367, 368, 369, 370, 371, 433, 447, 452, 453, 454, 463, 464, 465, 466, 467, 468, 470, 471, 473, 474, 487, 515, 518, 519, 522, 524, 525, 526, 527, 528, 533, 552, 592, 593, 594, 595, 596, 597, 660, 661, 662, 663, 667, 668, 686, 696, 699, 701, 706, 707, 708, 709, 710, 712, 713, 714, 715, 716, 717, 718, 719, 724, 745, 746, 747, 752, 753, 754, 755, 756, 757, 758, 759, 760, 762, 763, 764, 765, 766, 767, 768, 770, 771, 772, 773, 774, 775, 776, 798, 799, 800, 801, 802, 803, 804, 810, 811, 814, 815, 822, 823, 825, 826, 6281, 6282, 6283, 6286, 6287, 6288, 6289, 6291,6391, 6348, 6391, 6346, 6292, 6293, 6294, 6316, 6325, 6326, 6327, 6328, 6329, 6330, 6331, 6332, 6335, 6336, 6337, 6344, 6343,573,574,325,499,475,287,781,6345,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,435,436,481,498,625,721,722,723,725,726,727,728,730,731,732,733,734,735,736,737,738,739,740,741,742,743,744,784,785,819,2534,6312,6313,6314,6320,6321];
    var Zf = ["10", "40"];
    let lastValidatedRpps = null;
    let currentAddressRppsValid = false; // Nouvelle variable pour suivre l'état de l'adresse actuelle
    
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
            const potentialRpps = line.replace(/]*>/g, '').trim();
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

    function isDeliveryAddressFrance() {
        var selectedAddress = document.querySelector('#delivery-addresses .address-item.selected .address');
        return selectedAddress && selectedAddress.textContent.includes('France');
    }

    // Fonction principale pour vérifier l'état actuel de l'adresse
    function checkCurrentAddressRpps() {
        var selectedAddress = document.querySelector('#delivery-addresses .address-item.selected .address');
        var isFrance = isDeliveryAddressFrance();
        var needsRpps = isFrance && (cartContainsProProduct() || isCurrentPageProProduct());
        
        if (!needsRpps) {
            currentAddressRppsValid = true;
            updateContinueButton(true);
            return Promise.resolve(true);
        }
        
        var rppsFromAddress = extractRppsFromAddress(selectedAddress);
        
        if (rppsFromAddress) {
            return validateRpps(rppsFromAddress).then(isValid => {
                currentAddressRppsValid = isValid;
                updateContinueButton(isValid);
                updateRppsWarningValidation(document.getElementById('rpps-warning'), isValid);
                return isValid;
            });
        } else {
            currentAddressRppsValid = false;
            updateContinueButton(false);
            updateRppsWarningValidation(document.getElementById('rpps-warning'), false);
            return Promise.resolve(false);
        }
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
            warningMessage.innerHTML = "ATTENTION : Ce produit est réservé aux médecins et chirurgiens. Un numéro RPPS valide est requis pour finaliser votre commande. En cas de RPPS invalide ou manquant, votre commande sera automatiquement annulée. Des frais de traitement de 5 euros seront imputés pour toute annulation due à un RPPS invalide.";
            
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
            // Vérifier l'adresse actuelle au lieu de bloquer immédiatement
            checkCurrentAddressRpps();
        } else if (warningElement) {
            warningElement.remove();
            updateContinueButton(true);
        }
    }
    
    function updateRppsWarningValidation(warningElement, isValid) {
        if (!warningElement) return;
        
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
        if (document.body.classList.contains('country-fr')) {
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
        if (document.body.classList.contains('country-fr') && (cartContainsProProduct() || isCurrentPageProProduct())) {
            blockAmazonButtons();
        }
    }

    async function handleCountryChange() {
        await sleep(3000);
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
                    warningMessage.innerHTML = "ATTENTION : Ce produit est réservé aux médecins et chirurgiens!UN RPPS VALIDE VOUS SERA DEMANDÉ POUR POURSUIVRE VOTRE COMMANDE.                                                 Par ailleurs, chaque RPPS et chaque adresse de livraison sont vérifiés manuellement.                                                               EN CAS DE FAUSSE INFORMATION, VOTRE COMMANDE VOUS SERA REMBOURSÉE, IMPUTÉE DES FRAIS DE TRAITEMENT LIÉS À CELLE-CI.";
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

        // MODIFICATION IMPORTANTE : Ne plus bloquer par défaut, vérifier d'abord l'adresse existante
        var continueButton = document.querySelector('button[name="confirm-addresses"]');
        if (continueButton && document.body.classList.contains('country-fr')) {
            var needsRpps = cartContainsProProduct() || isCurrentPageProProduct();
            if (needsRpps) {
                // Vérifier d'abord si l'adresse actuelle a déjà un RPPS valide
                checkCurrentAddressRpps().then(isValid => {
                    if (!isValid) {
                        // Seulement bloquer si aucun RPPS valide n'est trouvé
                        continueButton.addEventListener('click', function(event) {
                            if (continueButton.disabled) {
                                event.preventDefault();
                                event.stopPropagation();
                                alert("Veuillez ajouter un numéro RPPS valide pour continuer.");
                            }
                        });
                    }
                });
            }
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
})();
}