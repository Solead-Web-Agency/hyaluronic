<?php
/**
 * Couche de cache backend du bridge headless HFM.
 *
 * Objectif : mettre en cache les LECTURES LOURDES et publiques (taxonomy / products /
 * content) via le cache natif PrestaShop (Cache::getInstance(), backend CacheFs par
 * défaut sur mutualisé), afin de soulager la base à chaque requête du front Next.js.
 *
 * Stratégie de purge par « tag » — VERSIONNAGE DE CLÉ (la plus simple et la plus fiable
 * avec CacheFs, qui ne gère pas les tags) :
 *   - un compteur de version par tag est stocké en Configuration (HFM_CACHE_VER_<tag>) ;
 *   - la version est intégrée dans la clé de cache ;
 *   - purger un tag = incrémenter son compteur -> toutes les anciennes clés deviennent
 *     inatteignables (et seront évincées naturellement par le backend / le TTL).
 * Aucun index de clés à maintenir, aucune énumération, robuste en concurrence.
 *
 * Dégradation gracieuse OBLIGATOIRE : si le cache est indisponible (get/set échoue,
 * exception, backend absent), on retombe TOUJOURS sur la requête directe. Le cache ne
 * doit JAMAIS casser une réponse d'API.
 *
 * IMPORTANT : n'est utilisé QUE par les endpoints publics en lecture (taxonomy,
 * products, content). JAMAIS pour cart/checkout/customer/orders/wishlist (données par
 * utilisateur / mutations).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HfmCache
{
    /** Vocabulaire de TAGS fermé, partagé avec le front. */
    const TAG_TAXONOMY = 'taxonomy';
    const TAG_PRODUCTS = 'products';
    const TAG_CONTENT = 'content';

    /** TTL par défaut (secondes), alignés sur le contrat partagé. */
    const TTL_TAXONOMY = 3600;
    const TTL_PRODUCTS = 300;
    const TTL_CONTENT = 3600;

    /** Préfixe de clé Configuration pour les compteurs de version par tag. */
    const VER_PREFIX = 'HFM_CACHE_VER_';

    /** Préfixe des clés de cache (namespace du module). */
    const KEY_PREFIX = 'hfm_';

    /**
     * Version courante d'un tag (compteur monotone stocké en Configuration).
     * Démarre à 1. En cas d'indisponibilité de Configuration, renvoie 1 (le cache
     * reste cohérent, il ne « voit » simplement pas les purges antérieures).
     */
    public static function version($tag)
    {
        $v = (int) Configuration::getGlobalValue(self::VER_PREFIX . $tag);
        return $v > 0 ? $v : 1;
    }

    /**
     * Purge d'un ou plusieurs tags : incrémente le(s) compteur(s) de version.
     * Les clés cachées sous l'ancienne version deviennent inatteignables.
     */
    public static function flushTags(array $tags)
    {
        foreach (array_unique($tags) as $tag) {
            $tag = (string) $tag;
            if ($tag === '') {
                continue;
            }
            try {
                $next = self::version($tag) + 1;
                Configuration::updateGlobalValue(self::VER_PREFIX . $tag, (string) $next);
            } catch (\Throwable $e) {
                // Silencieux : une purge ratée ne doit jamais casser l'appelant.
            }
        }
    }

    /**
     * Construit une clé de cache stable, versionnée par tag.
     *
     * @param string $tag   Tag de cache (taxonomy|products|content).
     * @param string $scope Sous-espace logique (ex. 'menu', 'single', 'list').
     * @param array  $parts Composantes discriminantes (id_lang, id_shop, params...).
     */
    public static function key($tag, $scope, array $parts)
    {
        $ver = self::version($tag);
        // Normalise les composantes en chaîne déterministe.
        $flat = [];
        foreach ($parts as $k => $v) {
            $flat[] = $k . '=' . (is_scalar($v) ? (string) $v : json_encode($v));
        }
        sort($flat);
        $raw = $tag . '|v' . $ver . '|' . $scope . '|' . implode('&', $flat);
        // md5 pour une longueur bornée (compatible noms de fichiers CacheFs).
        return self::KEY_PREFIX . $tag . '_' . md5($raw);
    }

    /**
     * Lecture cachée générique avec repli.
     *
     * Récupère la valeur en cache si présente, sinon exécute $producer, met le
     * résultat en cache (TTL), et le renvoie. Toute défaillance du cache est
     * absorbée : on renvoie systématiquement le résultat de $producer.
     *
     * On sérialise en JSON pour rester compatible avec tous les backends de cache
     * (CacheFs, Memcached...) sans dépendre de la sérialisation d'objets.
     *
     * @param string   $key      Clé de cache (via self::key()).
     * @param int      $ttl      Durée de vie en secondes.
     * @param callable $producer Fonction sans argument qui produit la donnée fraîche.
     * @return mixed
     */
    public static function remember($key, $ttl, callable $producer)
    {
        $cache = self::backend();

        // Tentative de lecture.
        if ($cache !== null) {
            try {
                if ($cache->exists($key)) {
                    $cached = $cache->get($key);
                    if (is_string($cached) && $cached !== '') {
                        $decoded = json_decode($cached, true);
                        if (is_array($decoded) && array_key_exists('d', $decoded)) {
                            return $decoded['d'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignoré : on produira la donnée fraîche.
            }
        }

        // Production de la donnée fraîche (toujours exécutée en cas de miss/erreur).
        $data = $producer();

        // On ne met JAMAIS en cache une réponse d'erreur (ex. ['error' => 'product_not_found']
        // ou un producteur qui renvoie null) : sinon une panne transitoire ou un id invalide
        // serait figé pour tout le TTL et servi à tous les visiteurs.
        if ($data === null || (is_array($data) && isset($data['error']))) {
            return $data;
        }

        // Tentative d'écriture (best effort).
        if ($cache !== null) {
            try {
                $payload = json_encode(['d' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($payload !== false) {
                    $cache->set($key, $payload, (int) $ttl);
                }
            } catch (\Throwable $e) {
                // Ignoré : l'écriture en cache est optionnelle.
            }
        }

        return $data;
    }

    /**
     * Instance du backend de cache PrestaShop, ou null si indisponible.
     * On isole l'appel pour ne jamais laisser remonter d'exception.
     */
    protected static function backend()
    {
        try {
            return Cache::getInstance();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
