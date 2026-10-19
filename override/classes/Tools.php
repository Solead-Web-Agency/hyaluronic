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

    /** Supprimée en PS9 — équivalent à getValue (valeur de requête) */
    public static function getValueRaw($key, $default_value = false)
    {
        return self::getValue($key, $default_value);
    }
}
