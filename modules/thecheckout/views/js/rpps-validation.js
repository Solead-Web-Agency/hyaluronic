/**
 * RPPS Validation for TheCheckout Module - Version corrigée
 */

(function() {
    'use strict';
    
    // Configuration
    const config = {
        proProductIds: [1, 472, 8, 9, 11, 13, 14, 20, 21, 22, 23, 33, 34, 37, 38, 47, 48, 49, 52, 53, 55, 62, 64, 65, 101, 103, 104, 111, 113, 115, 116, 119, 120, 121, 122, 123, 124, 125, 126, 127, 132, 144, 145, 146, 147, 148, 149, 150, 168, 170, 171, 172, 173, 174, 179, 195, 197, 202, 203, 302, 306, 304, 688, 689, 310, 354, 358, 359, 365, 367, 368, 369, 370, 371, 433, 447, 452, 453, 454, 463, 464, 465, 466, 467, 468, 470, 471, 473, 474, 487, 515, 518, 519, 522, 524, 525, 526, 527, 528, 533, 552, 592, 593, 594, 595, 596, 597, 660, 661, 662, 663, 667, 668, 686, 696, 699, 701, 706, 707, 708, 709, 710, 712, 713, 714, 715, 716, 717, 718, 719, 724, 745, 746, 747, 752, 753, 754, 755, 756, 757, 758, 759, 760, 762, 763, 764, 765, 766, 767, 768, 770, 771, 772, 773, 774, 775, 776, 798, 799, 800, 801, 802, 803, 804, 810, 811, 814, 815, 822, 823, 825, 826, 6281, 6282, 6283, 6286, 6287, 6288, 6289, 6291, 6348, 6292, 6293, 6294, 6316, 6325, 6326, 6327, 6328, 6329, 6330, 6331, 6332, 6335, 6336, 6337, 6344, 6343, 573, 574, 325, 499, 475, 287, 781, 6345, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 384, 385, 386, 387, 435, 436, 481, 498, 625, 721, 722, 723, 725, 726, 727, 728, 730, 731, 732, 733, 734, 735, 736, 737, 738, 739, 740, 741, 742, 743, 744, 784, 785, 819, 2534, 6312, 6313, 6314, 6320, 6321, 6364, 6365],
        apiKey: "fbbc02f2-881e-4821-be23-906b3677cb01",
        frenchCountryId: "8"
    };
    
    let lastValidatedRpps = null;
    let rppsIsValid = false;
    
    // Utilitaires
    function cartContainsProProduct() {
        if (window.prestashop && window.prestashop.cart && window.prestashop.cart.products) {
            return window.prestashop.cart.products.some(function(product) {
                return config.proProductIds.includes(parseInt(product.id));
            });
        }
        return false;
    }
    
    // Vérifier si on est en France
    function isCountryFrance() {
        // Méthode 1 : Vérifier le select du pays EN PREMIER (plus fiable)
        const countrySelect = document.querySelector('select[name="id_country"]');
        if (countrySelect) {
            const selectedValue = countrySelect.value;
            console.log('RPPS: Pays sélectionné =', selectedValue);
            return selectedValue === config.frenchCountryId || selectedValue === '8';
        }
        
        // Méthode 2 : Vérifier la classe du body
        if (document.body.classList.contains('country-fr')) {
            return true;
        }
        
        // Méthode 3 : Vérifier via l'objet PrestaShop
        if (window.prestashop && window.prestashop.customer && window.prestashop.customer.addresses) {
            const addresses = window.prestashop.customer.addresses;
            for (const addr of Object.values(addresses)) {
                if (addr.id_country === config.frenchCountryId) {
                    return true;
                }
            }
        }
        
        // Méthode 4 : Vérifier le texte affiché du pays
        const countryDisplay = document.querySelector('.address-country');
        if (countryDisplay && (countryDisplay.textContent.includes('France') || countryDisplay.textContent.includes('Frankreich'))) {
            return true;
        }
        
        return false;
    }
    
    // Validation RPPS via API
    function validateRpps(rpps) {
        if (rpps === lastValidatedRpps) {
            return Promise.resolve(rppsIsValid);
        }
        
        return fetch(`https://gateway.api.esante.gouv.fr/fhir/v1/Practitioner?_pretty=true&_format=json&_revinclude=PractitionerRole:practitioner&active=true&identifier=${rpps}`, {
            headers: {
                "ESANTE-API-KEY": config.apiKey
            }
        })
        .then(response => response.json())
        .then(data => {
            let practitioner = data.entry && data.entry[0] ? data.entry[0].resource : null;
            if (practitioner) {
                let active = practitioner.active;
                let codes = practitioner.identifier.map(id => id.value);
                let isValid = codes.includes(rpps) && active;
                if (isValid) {
                    lastValidatedRpps = rpps;
                    rppsIsValid = true;
                }
                return isValid;
            }
            rppsIsValid = false;
            return false;
        })
        .catch(error => {
            console.error('Erreur validation RPPS:', error);
            rppsIsValid = false;
            return false;
        });
    }
    
    // Fonction principale pour gérer le champ RPPS
    function setupRppsField() {
        // Ne rien faire si pas en France ou pas de produits pro
        if (!isCountryFrance() || !cartContainsProProduct()) {
            hideRppsField(); // Masquer si pas en France
            return;
        }
        
        console.log('RPPS Setup: Produits pro détectés, configuration en cours...');
        
        // Chercher TOUS les labels qui contiennent "Autre"
        let rppsFormGroup = null;
        const allLabels = document.querySelectorAll('label');
        
        for (const label of allLabels) {
            if (label.textContent.trim() === 'Autre' || 
                label.textContent.trim() === 'Other' ||
                label.innerHTML.includes('Autre')) {
                rppsFormGroup = label.closest('.form-group');
                if (rppsFormGroup) {
                    console.log('RPPS: Form-group trouvé via le label', label);
                    break;
                }
            }
        }
        
        // Si pas trouvé par label, chercher par nom de champ
        if (!rppsFormGroup) {
            const otherInput = document.querySelector('input[name="other"]');
            if (otherInput) {
                rppsFormGroup = otherInput.closest('.form-group');
                console.log('RPPS: Form-group trouvé via input[name="other"]');
            }
        }
        
        // Si toujours pas trouvé, chercher par classe
        if (!rppsFormGroup) {
            const possibleGroups = document.querySelectorAll('.form-group.other, .other, .form-group');
            for (const group of possibleGroups) {
                const label = group.querySelector('label');
                if (label && (label.textContent.includes('Autre') || label.textContent.includes('Other'))) {
                    rppsFormGroup = group;
                    console.log('RPPS: Form-group trouvé via classe');
                    break;
                }
            }
        }
        
        if (!rppsFormGroup) {
            console.error('RPPS: Impossible de trouver le form-group du champ Autre');
            return;
        }
        
        // Forcer l'affichage du form-group
        rppsFormGroup.classList.remove('hidden');
        rppsFormGroup.style.display = '';
        rppsFormGroup.style.visibility = 'visible';
        rppsFormGroup.style.opacity = '1';
        
        // NOUVELLE APPROCHE : Créer un container clair pour le champ et le message
        let rppsContainer = rppsFormGroup.querySelector('.rpps-field-container');
        if (!rppsContainer) {
            rppsContainer = document.createElement('div');
            rppsContainer.className = 'rpps-field-container';
            rppsContainer.style.position = 'relative';
            rppsContainer.style.width = '100%';
            rppsContainer.style.marginTop = '5px';
            
            // Insérer le container après le label
            const label = rppsFormGroup.querySelector('label');
            if (label) {
                // Modifier le label
                label.innerHTML = 'RPPS <sup class="required" style="color: red;">*</sup>';
                label.style.fontWeight = 'bold';
                label.style.color = '#dc3545';
                
                if (label.nextSibling) {
                    label.parentNode.insertBefore(rppsContainer, label.nextSibling);
                } else {
                    label.parentNode.appendChild(rppsContainer);
                }
            } else {
                rppsFormGroup.appendChild(rppsContainer);
            }
        }
        
        // Récupérer ou créer le champ input DANS le container
        let rppsField = rppsContainer.querySelector('input[name="other"]');
        if (!rppsField) {
            // Chercher si le champ existe ailleurs et le déplacer
            rppsField = rppsFormGroup.querySelector('input[name="other"]');
            if (!rppsField) {
                rppsField = rppsFormGroup.querySelector('input[type="text"], input');
            }
            
            if (rppsField) {
                // Déplacer le champ existant dans le container
                rppsContainer.appendChild(rppsField);
            } else {
                // Créer un nouveau champ
                rppsField = document.createElement('input');
                rppsField.type = 'text';
                rppsField.name = 'other';
                rppsField.className = 'form-control';
                rppsField.id = 'rpps-field-' + Date.now();
                rppsContainer.appendChild(rppsField);
            }
        }
        
        // S'assurer que le champ a le bon nom
        rppsField.name = 'other';
        
        // Configurer le champ
        rppsField.placeholder = 'Saisissez votre numéro RPPS (11 chiffres)';
        rppsField.required = true;
        rppsField.pattern = "^\\d{11}$";
        rppsField.maxLength = 11;
        rppsField.setAttribute('data-rpps-required', 'true');
        rppsField.setAttribute('data-validate', 'isRpps');
        rppsField.setAttribute('autocomplete', 'off');
        rppsField.disabled = false;
        rppsField.readOnly = false;
        
        // Styles pour garantir la visibilité
        rppsField.style.display = 'block';
        rppsField.style.width = '100%';
        rppsField.style.height = '38px';
        rppsField.style.padding = '6px 12px';
        rppsField.style.fontSize = '14px';
        rppsField.style.marginBottom = '10px';
        rppsField.style.position = 'relative';
        rppsField.style.zIndex = '10';
        
        // Ajouter le message d'info DANS le container, APRÈS le champ
        let rppsInfo = rppsContainer.querySelector('.rpps-info');
        if (!rppsInfo) {
            rppsInfo = document.createElement('div');
            rppsInfo.className = 'rpps-info alert alert-danger';
            rppsInfo.style.marginTop = '0';
            rppsInfo.style.marginBottom = '10px';
            rppsInfo.style.position = 'relative';
            rppsInfo.style.zIndex = '1';
            rppsInfo.innerHTML = '<strong>⚠️ OBLIGATOIRE :</strong> Saisissez votre numéro RPPS valide (11 chiffres) pour continuer votre commande.';
            rppsContainer.appendChild(rppsInfo);
        }
        
        // Nettoyer : supprimer tout ancien message en dehors du container
        const oldMessages = rppsFormGroup.querySelectorAll('.rpps-info');
        oldMessages.forEach(msg => {
            if (msg !== rppsInfo) {
                msg.remove();
            }
        });
        
        // Gestion de la saisie
        const existingListeners = rppsField.getAttribute('data-listeners-added');
        if (!existingListeners) {
            rppsField.setAttribute('data-listeners-added', 'true');
            
            rppsField.addEventListener('input', function(e) {
                // Autoriser seulement les chiffres
                this.value = this.value.replace(/[^\d]/g, '').slice(0, 11);
                
                // Réinitialiser la validation
                rppsIsValid = false;
                lastValidatedRpps = null;
                
                // Supprimer les anciens messages de validation
                const oldMsg = rppsContainer.querySelectorAll('.rpps-validation-msg');
                oldMsg.forEach(m => m.remove());
                
                // Si 11 chiffres, valider automatiquement
                if (this.value.length === 11) {
                    validateAndShowResult(this);
                }
            });
            
            // Validation au blur aussi
            rppsField.addEventListener('blur', function() {
                if (this.value.length === 11) {
                    validateAndShowResult(this);
                }
            });
        }
        
        function validateAndShowResult(field) {
            let msg = rppsContainer.querySelector('.rpps-validation-msg');
            if (!msg) {
                msg = document.createElement('div');
                msg.className = 'rpps-validation-msg';
                msg.style.marginTop = '5px';
                rppsContainer.appendChild(msg);
            }
            msg.innerHTML = '<span class="text-info">⏳ Vérification en cours...</span>';
            
            validateRpps(field.value).then(isValid => {
                if (isValid) {
                    msg.innerHTML = '<span class="text-success">✅ RPPS valide</span>';
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                } else {
                    msg.innerHTML = '<span class="text-danger">❌ RPPS invalide ou inactif</span>';
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                }
            });
        }
        
        console.log('RPPS Setup: Configuration terminée', rppsField);
    }
    
    // Fonction pour masquer le champ RPPS et restaurer le champ "Autre"
    function hideRppsField() {
        const rppsContainer = document.querySelector('.rpps-field-container');
        const rppsFormGroup = rppsContainer ? rppsContainer.closest('.form-group') : null;
        
        if (rppsFormGroup) {
            // Restaurer le label original
            const label = rppsFormGroup.querySelector('label');
            if (label && label.innerHTML.includes('RPPS')) {
                label.innerHTML = 'Autre';
                label.style.fontWeight = '';
                label.style.color = '';
            }
            
            // Retirer les attributs RPPS du champ
            const input = rppsFormGroup.querySelector('input[name="other"]');
            if (input) {
                input.removeAttribute('data-rpps-required');
                input.removeAttribute('data-validate');
                input.removeAttribute('pattern');
                input.removeAttribute('maxLength');
                input.required = false;
                input.placeholder = '';
                input.classList.remove('is-valid', 'is-invalid');
                input.style = ''; // Réinitialiser les styles
                
                // Si le champ est dans le container, le sortir
                if (rppsContainer && input.parentNode === rppsContainer) {
                    rppsFormGroup.insertBefore(input, rppsContainer);
                }
            }
            
            // Supprimer le container RPPS
            if (rppsContainer) {
                rppsContainer.remove();
            }
            
            // Réinitialiser les états
            rppsIsValid = false;
            lastValidatedRpps = null;
            
            console.log('RPPS: Champ masqué car pas en France');
        }
    }
    
    // Bloquer TOUS les boutons de progression
    function blockAllProgress(e) {
        // Ne bloquer que si on a des produits pro et qu'on est en France
        if (!cartContainsProProduct() || !isCountryFrance()) {
            return;
        }
        
        // Liste exhaustive des sélecteurs de boutons à bloquer
        const blockedSelectors = [
            'button[type="submit"]',
            '.btn-primary',
            '[name="processCarrier"]',
            '[name="confirmDeliveryOption"]',
            '#payment-confirmation button',
            '.payment-option',
            '[data-action="confirm-order"]',
            '.confirm-order',
            'button:contains("Procéder")',
            'button:contains("Payer")',
            'button:contains("Commander")',
            'button:contains("Confirmer")',
            'button:contains("Continuer")',
            'a:contains("Procéder")',
            'a.btn'
        ];
        
        const target = e.target;
        let isBlockedButton = false;
        
        // Vérifier si c'est un bouton à bloquer
        for (const selector of blockedSelectors) {
            if (target.matches && target.matches(selector)) {
                isBlockedButton = true;
                break;
            }
            if (target.closest && target.closest(selector)) {
                isBlockedButton = true;
                break;
            }
        }
        
        // Vérifier aussi par le texte
        const text = target.textContent || '';
        if (text.match(/procéder|payer|commander|confirmer|continuer|suivant/i)) {
            isBlockedButton = true;
        }
        
        if (!isBlockedButton) return;
        
        // Chercher le champ RPPS
        const rppsField = document.querySelector('input[data-rpps-required="true"]');
        if (!rppsField) {
            // Si pas de champ, le créer
            setupRppsField();
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            alert('⚠️ ERREUR\n\nLe champ RPPS est requis mais n\'apparaît pas.\nVeuillez rafraîchir la page.');
            return false;
        }
        
        // Vérifier si RPPS est valide
        if (!rppsIsValid || rppsField.value.length !== 11) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Message d'erreur
            alert('⛔ STOP !\n\nVous DEVEZ saisir un numéro RPPS valide (11 chiffres) pour continuer.\n\nCe champ est OBLIGATOIRE pour les produits réservés aux professionnels de santé.');
            
            // Focus et animation
            rppsField.focus();
            rppsField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            rppsField.classList.add('is-invalid');
            rppsField.style.animation = 'shake 0.5s';
            setTimeout(() => {
                rppsField.style.animation = '';
            }, 500);
            
            // Mettre le champ en rouge vif
            rppsField.style.borderColor = '#dc3545';
            rppsField.style.borderWidth = '3px';
            
            return false;
        }
    }
    
    // CSS amélioré
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        input[data-rpps-required="true"] {
            border: 2px solid #007bff !important;
            min-height: 38px !important;
            padding: 6px 12px !important;
            background-color: #ffffff !important;
        }
        input[data-rpps-required="true"]:focus {
            border-color: #0056b3 !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
        }
        input[data-rpps-required="true"].is-valid {
            border-color: #28a745 !important;
            background-color: #f8fff9 !important;
        }
        input[data-rpps-required="true"].is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }
        .rpps-validation-msg {
            margin-top: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .rpps-info {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        /* Forcer l'affichage du label RPPS */
        label:has(+ input[data-rpps-required="true"]),
        label:has(~ input[data-rpps-required="true"]) {
            font-weight: bold !important;
        }
        /* S'assurer que le message ne cache pas le champ */
        .rpps-info {
            clear: both;
            position: relative !important;
            z-index: 1;
        }
        /* S'assurer que le champ input est visible */
        input[data-rpps-required="true"] {
            position: relative !important;
            z-index: 2;
            margin-bottom: 10px !important;
        }
    `;
    document.head.appendChild(style);
    
    // Forcer la mise à jour continue des labels
    function forceUpdateLabels() {
        if (!cartContainsProProduct() || !isCountryFrance()) {
            return;
        }
        
        const labels = document.querySelectorAll('label');
        labels.forEach(label => {
            if (label.textContent.trim() === 'Autre' || label.textContent.trim() === 'Other') {
                label.innerHTML = 'RPPS <sup class="required" style="color: red;">*</sup>';
                label.style.fontWeight = 'bold';
                label.style.color = '#dc3545';
            }
        });
    }
    
    // Initialisation avec multiple tentatives
    function init() {
        console.log('RPPS Init: Démarrage...');
        
        // Setup initial
        setupRppsField();
        forceUpdateLabels();
        
        // Intercepter TOUS les clics au plus haut niveau
        document.addEventListener('click', blockAllProgress, true);
        document.addEventListener('submit', blockAllProgress, true);
        
        // NOUVEAU : Écouter les changements de pays
        setupCountryChangeListener();
        
        // Réessayer plusieurs fois pour être sûr
        setTimeout(() => { setupRppsField(); forceUpdateLabels(); }, 500);
        setTimeout(() => { setupRppsField(); forceUpdateLabels(); }, 1000);
        setTimeout(() => { setupRppsField(); forceUpdateLabels(); }, 2000);
        
        // Mise à jour continue des labels
        setInterval(forceUpdateLabels, 1000);
        
        // Observer les changements
        const observer = new MutationObserver(() => {
            const rppsField = document.querySelector('input[data-rpps-required="true"]');
            if (!rppsField) {
                setupRppsField();
            }
            forceUpdateLabels();
            
            // Réattacher l'écouteur si nécessaire
            setupCountryChangeListener();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true
        });
    }
    
    // Fonction pour écouter les changements de pays
    function setupCountryChangeListener() {
        const countrySelect = document.querySelector('select[name="id_country"]');
        
        if (countrySelect && !countrySelect.hasAttribute('data-rpps-listener')) {
            countrySelect.setAttribute('data-rpps-listener', 'true');
            
            countrySelect.addEventListener('change', function() {
                console.log('RPPS: Changement de pays détecté');
                const newCountry = this.value;
                console.log('RPPS: Nouveau pays sélectionné =', newCountry);
                
                // Mise à jour immédiate sans attendre
                if (newCountry === '8' || newCountry === config.frenchCountryId) {
                    console.log('RPPS: Pays = France, affichage du champ RPPS');
                    if (cartContainsProProduct()) {
                        setupRppsField();
                    }
                } else {
                    console.log('RPPS: Pays ≠ France, masquage immédiat du champ RPPS');
                    hideRppsField();
                }
                
                // Nouvelle vérification après un délai pour s'assurer
                setTimeout(() => {
                    if (isCountryFrance() && cartContainsProProduct()) {
                        setupRppsField();
                    } else if (!isCountryFrance()) {
                        hideRppsField();
                    }
                }, 300);
            });
        }
    }
    
    // Démarrer immédiatement et au DOMContentLoaded
    init();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    }
    
    // Réinitialiser sur les événements PrestaShop
    if (typeof $ !== 'undefined') {
        $(document).on('updatedAddressForm updatedDeliveryForm changedCheckoutStep', init);
    }
    
})(); 