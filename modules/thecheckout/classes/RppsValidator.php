<?php
/**
 * RPPS Validator for TheCheckout Module
 * Validation côté serveur des numéros RPPS pour les produits réservés aux professionnels
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RppsValidator
{
    // IDs des produits réservés aux professionnels
    private static $proProductIds = [1, 472, 8, 9, 11, 13, 14, 20, 21, 22, 23, 33, 34, 37, 38, 47, 48, 49, 52, 53, 55, 62, 64, 65, 101, 103, 104, 111, 113, 115, 116, 119, 120, 121, 122, 123, 124, 125, 126, 127, 132, 144, 145, 146, 147, 148, 149, 150, 168, 170, 171, 172, 173, 174, 179, 195, 197, 202, 203, 302, 306, 304, 688, 689, 310, 354, 358, 359, 365, 367, 368, 369, 370, 371, 433, 447, 452, 453, 454, 463, 464, 465, 466, 467, 468, 470, 471, 473, 474, 487, 515, 518, 519, 522, 524, 525, 526, 527, 528, 533, 552, 592, 593, 594, 595, 596, 597, 660, 661, 662, 663, 667, 668, 686, 696, 699, 701, 706, 707, 708, 709, 710, 712, 713, 714, 715, 716, 717, 718, 719, 724, 745, 746, 800, 801, 802, 803, 804, 810, 811, 814, 815, 822, 823, 825, 826, 6281, 6282, 6283, 6286, 6287, 6288, 6289, 6291, 6348, 6292, 6293, 6294, 6316, 6325, 6326, 6327, 6328, 6329, 6330, 6331, 6332, 6335, 6336, 6337, 6344, 6343, 573, 574, 325, 499, 475, 287, 781, 6345, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 384, 385, 386, 387, 435, 436, 481, 498, 625, 721, 722, 723, 725, 726, 727, 728, 730, 731, 732, 733, 734, 735, 736, 737, 738, 739, 740, 741, 742, 743, 744, 784, 785, 819, 2534, 6312, 6313, 6314, 6320, 6321, 6364, 6365];
    
    private static $apiKey = "fbbc02f2-881e-4821-be23-906b3677cb01";
    private static $apiUrl = "https://gateway.api.esante.gouv.fr/fhir/v1/Practitioner";
    private static $frenchCountryId = 8;
    
    /**
     * Vérifie si le panier contient des produits réservés aux professionnels
     */
    public static function cartContainsProProduct($cart = null)
    {
        if (!$cart) {
            $context = Context::getContext();
            $cart = $context->cart;
        }
        
        if (!Validate::isLoadedObject($cart)) {
            return false;
        }
        
        $products = $cart->getProducts();
        foreach ($products as $product) {
            if (in_array((int)$product['id_product'], self::$proProductIds)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si une adresse est en France
     */
    public static function isAddressFrance($address)
    {
        if (!Validate::isLoadedObject($address)) {
            return false;
        }
        
        return (int)$address->id_country === self::$frenchCountryId;
    }
    
    /**
     * Valide un numéro RPPS via l'API eSanté
     */
    public static function validateRpps($rpps)
    {
        // Vérifier le format (11 chiffres)
        if (!preg_match('/^\d{11}$/', $rpps)) {
            return false;
        }
        
        // Appel API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$apiUrl . "?_pretty=true&_format=json&_revinclude=PractitionerRole:practitioner&active=true&identifier=" . $rpps);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'ESANTE-API-KEY: ' . self::$apiKey,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            // En cas d'erreur API, on peut logger et retourner false ou true selon la politique
            PrestaShopLogger::addLog('RPPS API Error: HTTP ' . $httpCode, 3, null, 'RppsValidator');
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['entry']) || !is_array($data['entry']) || empty($data['entry'])) {
            return false;
        }
        
        $practitioner = $data['entry'][0]['resource'] ?? null;
        
        if (!$practitioner) {
            return false;
        }
        
        // Vérifier que le praticien est actif
        if (!isset($practitioner['active']) || !$practitioner['active']) {
            return false;
        }
        
        // Vérifier que le RPPS correspond
        if (isset($practitioner['identifier']) && is_array($practitioner['identifier'])) {
            foreach ($practitioner['identifier'] as $identifier) {
                if (isset($identifier['value']) && $identifier['value'] === $rpps) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si une adresse nécessite un RPPS valide
     */
    public static function addressNeedsRpps($address, $cart = null)
    {
        if (!self::isAddressFrance($address)) {
            return false;
        }
        
        return self::cartContainsProProduct($cart);
    }
    
    /**
     * Extrait le RPPS du champ "other" d'une adresse
     */
    public static function extractRppsFromAddress($address)
    {
        if (!Validate::isLoadedObject($address)) {
            return null;
        }
        
        $other = trim($address->other);
        
        // Vérifier si c'est un RPPS (11 chiffres)
        if (preg_match('/^\d{11}$/', $other)) {
            return $other;
        }
        
        return null;
    }
    
    /**
     * Valide une adresse pour la commande
     */
    public static function validateAddressForOrder($address, $cart = null)
    {
        if (!self::addressNeedsRpps($address, $cart)) {
            return array('valid' => true);
        }
        
        $rpps = self::extractRppsFromAddress($address);
        
        if (!$rpps) {
            return array(
                'valid' => false,
                'error' => 'Un numéro RPPS valide est requis pour commander ces produits réservés aux professionnels de santé.'
            );
        }
        
        if (!self::validateRpps($rpps)) {
            return array(
                'valid' => false,
                'error' => 'Le numéro RPPS fourni n\'est pas valide ou n\'est pas actif.'
            );
        }
        
        return array('valid' => true);
    }
    
    /**
     * Vérifie si un mode de paiement est autorisé
     */
    public static function isPaymentMethodAllowed($moduleName, $cart = null)
    {
        // Bloquer Amazon Pay pour les produits pro
        $blockedPaymentMethods = ['amazonpay', 'amzpayments'];
        
        if (in_array(strtolower($moduleName), $blockedPaymentMethods)) {
            if (self::cartContainsProProduct($cart)) {
                return false;
            }
        }
        
        return true;
    }
} 