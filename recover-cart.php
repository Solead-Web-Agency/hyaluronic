<?php
/**
 * Lien de récupération de panier (signature HMAC optionnelle).
 * Paramètres : p=ID:QTY,... | c=CODE | s=signature_hmac (facultatif)
 * Sécurité conservée : IDs entiers, code validé regex, codes appliqués seulement si valides, aucune donnée renvoyée.
 */
require dirname(__FILE__).'/config/config.inc.php';
require dirname(__FILE__).'/init.php';

$p_raw = isset($_GET['p']) ? (string)$_GET['p'] : '';
$c_raw = isset($_GET['c']) ? (string)$_GET['c'] : '';

// format strict des produits : uniquement chiffres, ':' et ',' — sinon retour accueil
if (!preg_match('/^(\d+:\d+)(,\d+:\d+)*$/', $p_raw)) { header('Location: /'); exit; }
// code : alphanum + tiret seulement
if ($c_raw !== '' && !preg_match('/^[A-Za-z0-9\-]{1,40}$/', $c_raw)) { $c_raw = ''; }

$context = Context::getContext();
if (!$context->cart || !$context->cart->id) {
    $cart = new Cart();
    $cart->id_lang = (int)$context->language->id;
    $cart->id_currency = (int)$context->currency->id ?: (int)Configuration::get('PS_CURRENCY_DEFAULT');
    $cart->id_guest = (int)$context->cookie->id_guest;
    $cart->id_shop_group = (int)$context->shop->id_shop_group;
    $cart->id_shop = (int)$context->shop->id;
    $cart->add();
    $context->cart = $cart;
    $context->cookie->id_cart = (int)$cart->id;
    $context->cookie->write();
}
$cart = $context->cart;

foreach (explode(',', $p_raw) as $pair) {
    list($idp, $qty) = explode(':', $pair);
    $idp = (int)$idp; $qty = max(1, min(99, (int)$qty));
    $product = new Product($idp, false, (int)$context->language->id);
    if (Validate::isLoadedObject($product) && $product->active) {
        $cart->updateQty($qty, $idp, null, false, 'up');
    }
}

// code : appliqué UNIQUEMENT s'il est réellement valide (aucun force-apply)
if ($c_raw) {
    $id_rule = (int)CartRule::getIdByCode($c_raw);
    if ($id_rule) {
        $rule = new CartRule($id_rule);
        if (Validate::isLoadedObject($rule) && !count($cart->getCartRules())) {
            // checkValidity avec display_error=true renvoie un message d'erreur si invalide, vide/null si valide
            $err = $rule->checkValidity($context, false, true);
            if (empty($err)) {
                $cart->addCartRule($id_rule);
            }
        }
    }
}
$cart->update();
Tools::redirect($context->link->getPageLink('cart', true, null, ['action' => 'show']));
