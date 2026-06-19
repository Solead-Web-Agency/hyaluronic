# Validation RPPS pour TheCheckout

## Description

Cette intégration ajoute une validation obligatoire du numéro RPPS (Répertoire Partagé des Professionnels de Santé) pour certains produits réservés aux professionnels de santé en France.

## Fonctionnalités

### 1. Validation côté client (JavaScript)
- Vérifie automatiquement si le panier contient des produits réservés aux professionnels
- Transforme le champ "Autre" en champ "RPPS" obligatoire pour les adresses en France
- Valide le format du numéro RPPS (11 chiffres)
- Valide en temps réel via l'API eSanté
- Bloque la progression de la commande si le RPPS n'est pas valide
- Affiche des messages d'avertissement clairs

### 2. Validation côté serveur (PHP)
- Double validation pour plus de sécurité
- Vérifie le RPPS via l'API eSanté
- Empêche la validation de l'adresse si le RPPS est invalide
- Filtre les méthodes de paiement (bloque Amazon Pay)

### 3. Blocage des méthodes de paiement
- Amazon Pay est automatiquement bloqué pour les produits professionnels
- Message explicatif affiché

### 4. Avertissements sur les pages produit
- Message d'avertissement sur les fiches produits concernées
- Information claire sur la nécessité d'un RPPS valide

## Configuration

### Liste des produits concernés

Les IDs des produits réservés aux professionnels sont définis dans :
- JavaScript : `thecheckout/views/js/rpps-validation.js` (ligne 10)
- PHP : `thecheckout/classes/RppsValidator.php` (ligne 14)

Pour modifier la liste, mettez à jour les deux fichiers.

### Clé API eSanté

La clé API est définie dans :
- JavaScript : `thecheckout/views/js/rpps-validation.js` (ligne 11)
- PHP : `thecheckout/classes/RppsValidator.php` (ligne 16)

## Installation

1. Les fichiers suivants ont été ajoutés/modifiés :
   - `/views/js/rpps-validation.js` - Validation côté client
   - `/classes/RppsValidator.php` - Validation côté serveur
   - `/views/css/rpps-validation.css` - Styles CSS
   - `/thecheckout.php` - Hooks et intégration

2. Vider le cache PrestaShop après installation

3. Réinstaller le module si les hooks ne sont pas enregistrés :
   ```
   - Désinstaller le module TheCheckout
   - Réinstaller le module TheCheckout
   ```

## Test

Pour tester la fonctionnalité :

1. Ajouter un produit de la liste au panier
2. Aller au checkout
3. Sélectionner la France comme pays
4. Le champ "Autre" devient "RPPS" et est obligatoire
5. Tester avec un RPPS invalide : affichage d'erreur
6. Tester avec un RPPS valide : validation OK

### Numéros RPPS de test
- Format : 11 chiffres
- Exemple valide : À obtenir auprès de l'API eSanté

## Maintenance

- Surveiller les logs pour les erreurs API (PrestaShopLogger)
- Mettre à jour la clé API si nécessaire
- Ajouter/retirer des IDs de produits selon les besoins

## Support

En cas de problème :
1. Vérifier les logs PrestaShop
2. Vérifier la console JavaScript du navigateur
3. S'assurer que l'API eSanté est accessible 