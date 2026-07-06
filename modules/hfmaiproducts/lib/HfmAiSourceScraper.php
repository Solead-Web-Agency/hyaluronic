<?php
/**
 * HfmAiSourceScraper — récupère une page produit fournisseur (URL saisie dans le BO)
 * et en extrait ce qui nourrit la génération IA : texte lisible, blocs JSON-LD bruts
 * et URLs d'images candidates (JSON-LD, og:image, balises <img>).
 *
 * Sécurité : URLs http/https uniquement, hôte résolu vers des IP publiques (anti-SSRF),
 * taille de réponse plafonnée. Sert aussi à valider les URLs d'images avant leur
 * téléchargement par ImageManager::copyImg() à la création du produit.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HfmAiSourceScraper
{
    /** Taille max du HTML téléchargé (3 Mo). */
    const MAX_HTML_BYTES = 3145728;

    /** Texte de page transmis au modèle (caractères). */
    const MAX_TEXT_CHARS = 35000;

    /** Taille max d'un bloc JSON-LD transmis (caractères). */
    const MAX_JSONLD_CHARS = 12000;

    /** Nombre max d'images candidates proposées au modèle. */
    const MAX_IMAGES = 20;

    /**
     * Télécharge et analyse la page source.
     *
     * @param string $url
     *
     * @return array{url:string, text:string, jsonld:array<string>, images:array<string>}
     *
     * @throws Exception message lisible (URL invalide, page inaccessible…)
     */
    public function scrape($url)
    {
        $this->assertSafeUrl($url);
        $html = $this->fetchHtml($url);

        $jsonldBlocks = $this->extractJsonLdBlocks($html);

        return [
            'url' => $url,
            'text' => $this->extractText($html),
            'jsonld' => $jsonldBlocks,
            'images' => $this->extractImages($html, $jsonldBlocks, $url),
        ];
    }

    /**
     * Refuse tout ce qui n'est pas une URL http(s) publique (anti-SSRF).
     *
     * @param string $url
     *
     * @throws Exception
     */
    public function assertSafeUrl($url)
    {
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])
            || empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new Exception('URL source invalide (http/https attendu).');
        }

        $host = $parts['host'];
        // Hôte IP littéral ou résolution DNS : toutes les IP doivent être publiques.
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (empty($ips)) {
            throw new Exception('Hôte de l\'URL source introuvable (' . $host . ').');
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new Exception('URL source refusée (adresse non publique).');
            }
        }
    }

    /**
     * GET de la page avec limites strictes.
     *
     * @param string $url
     *
     * @return string HTML
     *
     * @throws Exception
     */
    protected function fetchHtml($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; HFM-BO/1.0)',
            CURLOPT_ENCODING => '',
        ]);
        // Coupe le transfert au-delà du plafond (les CDN annoncent rarement Content-Length).
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, static function ($res, $dlTotal, $dlNow) {
            return ($dlNow > self::MAX_HTML_BYTES) ? 1 : 0;
        });

        $html = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!is_string($html) || $html === '') {
            throw new Exception('Page source inaccessible' . ($err ? ' (' . $err . ')' : '') . '.');
        }
        if ($status >= 400) {
            throw new Exception('Page source en erreur (HTTP ' . $status . ').');
        }

        return $html;
    }

    /**
     * Télécharge une image produit vers un fichier local (user-agent navigateur :
     * certains sites bloquent les clients « robots ») et vérifie que le contenu
     * est bien une image exploitable.
     *
     * @param string $url
     * @param string $dest chemin du fichier de destination
     *
     * @return bool
     */
    public function downloadImage($url, $dest)
    {
        try {
            $this->assertSafeUrl($url);
        } catch (Exception $e) {
            return false;
        }

        $fp = @fopen($dest, 'wb');
        if (!$fp) {
            return false;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
            CURLOPT_HTTPHEADER => ['Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8'],
        ]);
        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($fp);

        // Le fichier doit être une image décodable (pas une page HTML anti-bot).
        return $ok && $status < 400 && filesize($dest) > 0 && @getimagesize($dest) !== false;
    }

    /**
     * Blocs <script type="application/ld+json"> bruts (tronqués).
     *
     * @param string $html
     *
     * @return array<string>
     */
    protected function extractJsonLdBlocks($html)
    {
        $blocks = [];
        if (preg_match_all('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#si', $html, $m)) {
            foreach ($m[1] as $raw) {
                $raw = trim($raw);
                if ($raw === '' || json_decode($raw) === null) {
                    continue;
                }
                $blocks[] = Tools::substr($raw, 0, self::MAX_JSONLD_CHARS);
            }
        }

        return $blocks;
    }

    /**
     * Texte lisible de la page (scripts/styles retirés, entités décodées).
     *
     * @param string $html
     *
     * @return string
     */
    protected function extractText($html)
    {
        $clean = preg_replace('#<(script|style|noscript|svg|template)[^>]*>.*?</\1>#si', ' ', $html);
        $clean = preg_replace('#<(br|/p|/div|/li|/tr|/h[1-6])[^>]*>#i', "\n", (string) $clean);
        $clean = strip_tags((string) $clean);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Compacte les blancs sans perdre les sauts de ligne (structure utile au modèle).
        $clean = preg_replace('#[ \t]+#', ' ', (string) $clean);
        $clean = preg_replace('#\n\s*\n+#', "\n", (string) $clean);

        return Tools::substr(trim((string) $clean), 0, self::MAX_TEXT_CHARS);
    }

    /**
     * URLs d'images candidates, par ordre de fiabilité :
     * 1) champs "image" des JSON-LD (généralement les photos du produit lui-même),
     * 2) balises og:image,
     * 3) <img src/data-src> du document (filtrées : logos, icônes, drapeaux… exclus).
     *
     * @param string $html
     * @param array<string> $jsonldBlocks
     * @param string $baseUrl
     *
     * @return array<string>
     */
    protected function extractImages($html, array $jsonldBlocks, $baseUrl)
    {
        $candidates = [];

        foreach ($jsonldBlocks as $raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->collectJsonLdImages($decoded, $candidates);
            }
        }

        if (preg_match_all('#<meta[^>]+(?:property|name)=["\']og:image(?::url)?["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
            foreach ($m[1] as $u) {
                $candidates[] = $u;
            }
        }
        if (preg_match_all('#<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']og:image(?::url)?["\']#i', $html, $m)) {
            foreach ($m[1] as $u) {
                $candidates[] = $u;
            }
        }

        if (preg_match_all('#<img[^>]+(?:src|data-src)=["\']([^"\']+)["\']#i', $html, $m)) {
            foreach ($m[1] as $u) {
                $candidates[] = $u;
            }
        }

        $out = [];
        foreach ($candidates as $u) {
            $u = $this->normalizeImageUrl(html_entity_decode((string) $u, ENT_QUOTES, 'UTF-8'), $baseUrl);
            if ($u === '' || isset($out[$u])) {
                continue;
            }
            // Écarte l'habillage évident (logos, pictos, drapeaux, moyens de paiement…).
            if (preg_match('#logo|icon|sprite|favicon|flag|payment|avatar|placeholder#i', $u)) {
                continue;
            }
            $out[$u] = true;
            if (count($out) >= self::MAX_IMAGES) {
                break;
            }
        }

        return array_keys($out);
    }

    /**
     * Collecte récursive des valeurs "image" d'une structure JSON-LD.
     *
     * @param array $node
     * @param array $candidates alimenté par référence d'appel en appel
     */
    protected function collectJsonLdImages(array $node, array &$candidates)
    {
        foreach ($node as $key => $value) {
            if ($key === 'image' || $key === 'contentUrl') {
                foreach ((array) $value as $v) {
                    if (is_string($v)) {
                        $candidates[] = $v;
                    } elseif (is_array($v) && isset($v['url']) && is_string($v['url'])) {
                        $candidates[] = $v['url'];
                    }
                }
            } elseif (is_array($value)) {
                $this->collectJsonLdImages($value, $candidates);
            }
        }
    }

    /**
     * Absolutise l'URL, déballe les proxys d'images Next.js (/_next/image?url=…)
     * et ne garde que le http(s) plausiblement image.
     *
     * @param string $url
     * @param string $baseUrl
     *
     * @return string '' si à écarter
     */
    protected function normalizeImageUrl($url, $baseUrl)
    {
        $url = trim($url);
        if ($url === '' || strpos($url, 'data:') === 0) {
            return '';
        }

        // Proxy Next.js : la vraie image est dans le paramètre ?url=.
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query && strpos($url, '_next/image') !== false) {
            parse_str($query, $qs);
            if (!empty($qs['url']) && is_string($qs['url'])) {
                $url = $qs['url'];
            }
        }

        // Relatif -> absolu sur l'origine de la page.
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (strpos($url, 'http') !== 0) {
            $base = parse_url($baseUrl);
            if (empty($base['scheme']) || empty($base['host'])) {
                return '';
            }
            $url = $base['scheme'] . '://' . $base['host'] . '/' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])
            || empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return '';
        }

        return $url;
    }
}
