<?php
/**
 * Override de compatibilité PS9 : réintroduit des méthodes Tools supprimées en PrestaShop 9
 * mais encore utilisées par de nombreux modules 1.7 (évite les "undefined method").
 * Correctif global — ne pas supprimer sans avoir porté les modules concernés.
 */
class Tools extends ToolsCore
{
    /** Supprimée en PS9 — encodage JSON */
    public static function jsonEncode($data, $encode_options = 0, $depth = 512)
    {
        return json_encode($data, (int) $encode_options, (int) $depth);
    }

    /** Supprimée en PS9 — décodage JSON */
    public static function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode((string) $json, (bool) $assoc, (int) $depth, (int) $options);
    }

    /** Supprimée en PS9 — était un hash salé à sens unique (clés/jetons internes) */
    public static function encrypt($data)
    {
        return self::hash((string) $data);
    }

    /**
     * Supprimée en PS9 — déchiffrement legacy. Best-effort via PhpEncryption si possible,
     * sinon renvoie la valeur telle quelle (ne casse pas le chargement).
     */
    public static function decrypt($data)
    {
        try {
            if (class_exists('PhpEncryption') && defined('_NEW_COOKIE_KEY_')) {
                $enc = new PhpEncryption(_NEW_COOKIE_KEY_);
                $res = $enc->decrypt((string) $data);
                if ($res !== false && $res !== null) {
                    return $res;
                }
            }
        } catch (\Throwable $e) {
            // ignore : on retombe sur le best-effort ci-dessous
        }

        return $data;
    }

    /**
     * Supprimée en PS9 — formatage d'un prix avec sa devise. Encore appelée par une dizaine de
     * modules (statscompta, relances panier abandonné, ps_emailalerts, vivawallet…).
     * $currency : objet Currency, id, tableau (id_currency) ou null (devise du contexte).
     */
    public static function displayPrice($price, $currency = null, $no_utf8 = false, ?Context $context = null)
    {
        if (!is_numeric($price)) {
            return $price;
        }
        $context = $context ?: Context::getContext();
        if ($currency === null) {
            $currency = $context->currency;
        } elseif (is_array($currency)) {
            $currency = Currency::getCurrencyInstance((int) ($currency['id_currency'] ?? ($currency['id'] ?? 0)));
        } elseif (!is_object($currency)) {
            $currency = Currency::getCurrencyInstance((int) $currency);
        }
        $iso = (is_object($currency) && !empty($currency->iso_code)) ? $currency->iso_code : 'EUR';
        try {
            $locale = $context->getCurrentLocale();
            if ($locale) {
                return $locale->formatPrice((float) $price, $iso);
            }
        } catch (\Throwable $e) {
            // pas de locale (CLI, contexte partiel) : repli ci-dessous
        }

        return number_format((float) $price, 2, ',', "\u{202F}") . "\u{00A0}" . $iso;
    }

    // ------------------------------------------------------------------------------------------
    // Autres méthodes supprimées en PS9, encore appelées par des modules installés (recensement
    // du 2026-10-19 : stripslashes ×19 modules dont advancedvatmanager, dgridproducts,
    // gmerchantcenter ; link_rewrite, getBrightness, redirectLink, addJS/addCSS…).
    // Comportement repris de PrestaShop 1.7.
    // ------------------------------------------------------------------------------------------

    /** PS 1.7 : ne retirait les antislashs que si magic_quotes_gpc (inexistant en PHP 8) -> identité. */
    public static function stripslashes($string)
    {
        return $string;
    }

    /** PS 1.7 : alias de str2url(). */
    public static function link_rewrite($str)
    {
        return self::str2url($str);
    }

    /** PS 1.7 : description sans balises HTML. */
    public static function getDescriptionClean($description)
    {
        return strip_tags(stripslashes((string) $description));
    }

    /** PS 1.7 : luminosité perçue d'une couleur hexadécimale (0-255). */
    public static function getBrightness($hex)
    {
        if (self::strtolower((string) $hex) == 'transparent') {
            return '129';
        }
        $hex = str_replace('#', '', (string) $hex);
        if (self::strlen($hex) == 3) {
            $hex .= $hex;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    }

    /** PS 1.7 : redirection vers une URL ou une page du front (« controller?params »). */
    public static function redirectLink($url)
    {
        if (!preg_match('@^https?://@i', (string) $url)) {
            if (strpos($url, __PS_BASE_URI__) === 0) {
                $url = substr($url, strlen(__PS_BASE_URI__));
            }
            if (strpos($url, 'index.php?controller=') === 0) {
                $url = substr($url, strlen('index.php?controller='));
            }
            $explode = explode('?', $url);
            $url = Context::getContext()->link->getPageLink($explode[0]);
            if (isset($explode[1])) {
                $url .= '?' . $explode[1];
            }
        }
        header('Location: ' . $url);
        exit;
    }

    /** PS 1.7 (déprécié) : délègue au contrôleur courant. */
    public static function addJS($js_uri)
    {
        $controller = Context::getContext()->controller;
        if ($controller && method_exists($controller, 'addJS')) {
            $controller->addJS($js_uri);
        }
    }

    /** PS 1.7 (déprécié) : délègue au contrôleur courant. */
    public static function addCSS($css_uri, $css_media_type = 'all')
    {
        $controller = Context::getContext()->controller;
        if ($controller && method_exists($controller, 'addCSS')) {
            $controller->addCSS($css_uri, $css_media_type);
        }
    }

    /** PS 1.7 : compatibilité PCRE ancienne -> identité en PHP 8. */
    public static function cleanNonUnicodeSupport($pattern)
    {
        return $pattern;
    }

    /** PS 1.7 : régénère l'index des classes (overrides). */
    public static function generateIndex()
    {
        PrestaShopAutoload::getInstance()->generateIndex();
    }

    /** PS 1.7 : nombre formaté selon la langue courante. */
    public static function displayNumber($number, $currency = null)
    {
        try {
            $locale = Context::getContext()->getCurrentLocale();
            if ($locale) {
                return $locale->formatNumber($number);
            }
        } catch (\Throwable $e) {
            // repli ci-dessous
        }

        return number_format((float) $number, 2, ',', "\u{202F}");
    }

    /** PS 1.7 (débogage) : affiche une variable. */
    public static function p($object)
    {
        echo '<pre>' . htmlspecialchars(print_r($object, true)) . '</pre>';

        return $object;
    }

    /** PS 1.7 (débogage) : affiche une variable puis s'arrête. */
    public static function d($object)
    {
        self::p($object);
        exit;
    }

    /** PS 1.7 : minification CSS -> renvoyée telle quelle (non critique). */
    public static function minifyCSS($css)
    {
        return $css;
    }

    /** PS 1.7 : alias de array_replace_recursive(). */
    public static function arrayReplaceRecursive($base, $replacements)
    {
        return array_replace_recursive((array) $base, (array) $replacements);
    }

    /** Supprimée en PS9 — équivalent à getValue (valeur de requête) */
    public static function getValueRaw($key, $default_value = false)
    {
        return self::getValue($key, $default_value);
    }
}
