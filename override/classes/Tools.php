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

    /** Supprimée en PS9 — équivalent à getValue (valeur de requête) */
    public static function getValueRaw($key, $default_value = false)
    {
        return self::getValue($key, $default_value);
    }
}
