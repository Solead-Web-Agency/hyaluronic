<?php
/**
 * HfmAiProductGenerator — appelle Claude (SDK Anthropic PHP officiel) pour produire
 * un BROUILLON de fiche produit à partir d'un nom / référence / notes fournisseur.
 *
 * La clé API vient TOUJOURS de Configuration::get('HFMAIPRODUCTS_API_KEY') : jamais en
 * dur, jamais loggée, jamais renvoyée au navigateur. Le contexte volumineux (catégories
 * éditoriales, marques, garde-fous médicaux) est mis en cache (prompt caching) pour
 * réduire le coût des appels répétés.
 *
 * Sortie structurée (json_schema) : Claude retourne directement un objet JSON conforme
 * au schéma ci-dessous (tous champs requis, additionalProperties:false).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/HfmAiSourceScraper.php';

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;

class HfmAiProductGenerator
{
    /** id_lang FR par défaut (contexte lu en FR pour le référentiel). */
    const REFERENCE_LANG = 1;

    /**
     * @param array $input ['name'=>, 'reference'=>, 'id_lang'=>, 'notes'=>, 'source_url'=>]
     *
     * @return array Brouillon décodé (clés du schéma json_schema)
     *
     * @throws Exception message lisible (clé absente, URL inaccessible, erreur API, réponse vide)
     */
    public function generate(array $input)
    {
        $apiKey = (string) Configuration::get('HFMAIPRODUCTS_API_KEY');
        if ($apiKey === '') {
            throw new Exception('Clé API Anthropic non configurée (module > Configurer).');
        }

        $model = (string) Configuration::get('HFMAIPRODUCTS_MODEL');
        if ($model === '') {
            $model = 'claude-opus-4-8';
        }

        // Page source fournisseur (optionnelle) : texte + JSON-LD + images candidates.
        // Une URL fournie mais inaccessible est une erreur franche (plutôt qu'un brouillon
        // silencieusement appauvri, comme si la page avait été lue).
        $source = null;
        $sourceUrl = isset($input['source_url']) ? trim((string) $input['source_url']) : '';
        if ($sourceUrl !== '') {
            $scraper = new HfmAiSourceScraper();
            $source = $scraper->scrape($sourceUrl);
        }

        // Référentiel (catégories éditoriales + marques) lu en base.
        $categories = $this->fetchEditorialCategories();
        $brands = $this->fetchBrands();
        $catalogue = $this->fetchCatalogue();

        // Ids réels des catégories éditoriales -> enum du schéma.
        $categoryIds = array_map(static function ($c) {
            return (int) $c['id_category'];
        }, $categories);
        if (empty($categoryIds)) {
            // Garde-fou : un enum vide serait rejeté par l'API. On tolère 0 (= à classer).
            $categoryIds = [0];
        }

        $schema = $this->buildSchema($categoryIds);
        $systemBlocks = $this->buildSystemBlocks($categories, $brands, $catalogue);
        $userText = $this->buildUserMessage($input, $source);

        // Outils serveur Anthropic : le modèle CHERCHE lui-même 2-3 pages
        // fournisseurs/concurrents (web_search) et les lit (web_fetch) — exécutés
        // côté Anthropic, aucun aller-retour à gérer. Variantes de base volontairement :
        // les variantes _20260209 (filtrage dynamique via code execution) donnent des
        // résultats dégradés combinées à la sortie structurée json_schema (testé).
        $tools = [
            ['type' => 'web_search_20250305', 'name' => 'web_search', 'max_uses' => 6],
            ['type' => 'web_fetch_20250910', 'name' => 'web_fetch', 'max_uses' => 6],
        ];

        // Recherches + lectures de pages : l'appel peut durer plusieurs minutes.
        // On impose le transporteur Guzzle (timeout global long, pas de coupure
        // d'inactivité) : le client PSR-18 découvert par défaut est celui de
        // Symfony/PrestaShop, dont l'idle timeout de 60 s tue les appels longs.
        @set_time_limit(300);
        $client = new Client(
            apiKey: $apiKey,
            requestOptions: [
                'transporter' => new \GuzzleHttp\Client(['timeout' => 570, 'connect_timeout' => 15]),
            ],
        );

        // Boucle qualité : le modèle « oublie » parfois de chercher (brouillon sans
        // aucune source alors qu'on est en mode recherche) — on relance une fois.
        $draft = null;
        for ($quality = 1; $quality <= 2; $quality++) {

        // Surcharges (429/529), erreurs 5xx et coupures réseau sont transitoires :
        // on retente jusqu'à 3 fois avec un délai croissant avant d'abandonner.
        $message = null;
        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $messages = [['role' => 'user', 'content' => $userText]];
                // La boucle d'outils serveur peut se suspendre (stop_reason pause_turn) :
                // on renvoie l'échange tel quel et le serveur reprend où il en était.
                for ($turn = 0; $turn < 4; $turn++) {
                    $message = $client->beta->messages->create(
                        model: $model,
                        maxTokens: 16000,
                        system: $systemBlocks,
                        messages: $messages,
                        tools: $tools,
                        outputConfig: [
                            'format' => [
                                'type' => 'json_schema',
                                'schema' => $schema,
                            ],
                        ],
                        betas: ['web-fetch-2025-09-10'],
                    );
                    if ($message->stopReason !== 'pause_turn') {
                        break;
                    }
                    $messages[] = ['role' => 'assistant', 'content' => $message->content];
                }
                break; // Succès.
            } catch (APIStatusException $e) {
                $status = isset($e->status) ? (int) $e->status : 0;
                $retryable = ($status === 429 || $status === 529 || $status >= 500);
                if ($retryable && $attempt < $maxAttempts) {
                    sleep(4 * $attempt);
                    continue;
                }
                // On expose un message lisible sans jamais divulguer la clé.
                if ($status === 429 || $status === 529) {
                    throw new Exception('Claude est momentanément surchargé (HTTP ' . $status . '). Réessayez dans quelques minutes.');
                }
                $detail = $status ? ('HTTP ' . $status) : 'erreur API';
                throw new Exception('Appel à Claude en échec (' . $detail . '). Vérifiez la clé API et réessayez.');
            } catch (Throwable $e) {
                // Réseau, autoload, etc. : on retente aussi (coupures transitoires).
                if ($attempt < $maxAttempts) {
                    sleep(4 * $attempt);
                    continue;
                }
                throw new Exception('Impossible de contacter Claude : ' . $e->getMessage());
            }
        }

        // Le JSON structuré est dans le DERNIER bloc texte (les recherches/lectures
        // produisent des blocs d'outils, parfois entrecoupés de textes intermédiaires).
        $draft = null;
        $blocks = ($message !== null && is_array($message->content)) ? $message->content : [];
        foreach (array_reverse($blocks) as $block) {
            if (isset($block->type) && $block->type === 'text') {
                $decoded = json_decode($block->text, true);
                if (is_array($decoded)) {
                    $draft = $decoded;
                    break;
                }
            }
        }

        if ($draft === null) {
            throw new Exception('Réponse de Claude vide ou illisible.');
        }

        // Brouillon exploitable (sources trouvées, ou page fournie) : on sort de la
        // boucle qualité ; sinon on relance une génération.
        $hasSources = isset($draft['sources']) && is_array($draft['sources']) && !empty($draft['sources']);
        if ($sourceUrl !== '' || $hasSources || $quality >= 2) {
            break;
        }
        } // fin boucle qualité

        // Sources consultées par le modèle : URLs validées, 3 max.
        $sources = [];
        if (isset($draft['sources']) && is_array($draft['sources'])) {
            foreach ($draft['sources'] as $u) {
                if (is_string($u) && Validate::isAbsoluteUrl($u) && count($sources) < 3) {
                    $sources[] = $u;
                }
            }
        }
        $draft['sources'] = $sources;

        // Images candidates : extraites par NOTRE parseur depuis la page fournie
        // et les pages sources trouvées par la recherche (web_fetch ne restitue pas
        // toujours les balises <img>, et on ne fait pas confiance aux URLs inventées).
        $scraper = isset($scraper) ? $scraper : new HfmAiSourceScraper();
        $candidates = ($source !== null) ? $source['images'] : [];
        foreach ($sources as $u) {
            try {
                $extra = $scraper->scrape($u);
                $candidates = array_merge($candidates, $extra['images']);
            } catch (Throwable $e) {
                // Source annexe illisible : on continue avec ce qu'on a.
            }
        }
        $candidates = array_values(array_unique($candidates));

        // Images retenues : celles du modèle, à condition de ressembler à une vraie
        // image (extension) ou de figurer parmi les candidates extraites — écarte les
        // ancres de page (#primaryimage) et autres URLs plausibles-mais-fausses.
        $selected = [];
        if (isset($draft['images']) && is_array($draft['images'])) {
            foreach ($draft['images'] as $u) {
                if (!is_string($u) || !Validate::isAbsoluteUrl($u) || in_array($u, $selected, true)) {
                    continue;
                }
                $looksImage = (bool) preg_match('#\.(jpe?g|png|webp|gif|avif)([?\#]|$)#i', $u);
                if ($looksImage || in_array($u, $candidates, true)) {
                    $selected[] = $u;
                }
            }
        }
        // … à défaut, la meilleure candidate (og:image / JSON-LD de la 1re source).
        if (empty($selected) && !empty($candidates)) {
            $selected = [$candidates[0]];
        }
        $draft['images'] = array_slice($selected, 0, 10);

        // Cross-selling : uniquement des ids qui existent VRAIMENT dans le catalogue
        // envoyé au modèle (aucun id inventé possible), avec libellés pour le formulaire.
        $byId = [];
        foreach ($catalogue as $row) {
            $byId[(int) $row['id_product']] = trim((string) $row['name']);
        }
        $crossSell = [];
        foreach ((isset($draft['cross_sell_ids']) && is_array($draft['cross_sell_ids'])) ? $draft['cross_sell_ids'] : [] as $cid) {
            $cid = (int) $cid;
            if (isset($byId[$cid]) && !isset($crossSell[$cid]) && count($crossSell) < 4) {
                $crossSell[$cid] = $byId[$cid];
            }
        }
        $draft['cross_sell_ids'] = array_keys($crossSell);
        $draft['_cross_sell'] = [];
        foreach ($crossSell as $cid => $cname) {
            $draft['_cross_sell'][] = ['id' => $cid, 'name' => $cname];
        }

        // Transparence pour l'écran d'édition : toutes les candidates (l'admin peut
        // récupérer une image écartée par le modèle) + stats de lecture de la page.
        $draft['_source_images'] = array_slice($candidates, 0, HfmAiSourceScraper::MAX_IMAGES);
        if ($source !== null) {
            $draft['_source_stats'] = [
                'text' => Tools::strlen($source['text']),
                'jsonld' => count($source['jsonld']),
                'images' => count($source['images']),
            ];
        }

        return $draft;
    }

    /**
     * Catégories éditoriales actives (id 301-359), libellé FR.
     *
     * @return array<int, array{id_category:int, name:string}>
     */
    protected function fetchEditorialCategories()
    {
        $db = Db::getInstance();
        $lang = (int) self::REFERENCE_LANG;
        $sql = 'SELECT c.id_category, cl.name
                FROM `' . _DB_PREFIX_ . 'category` c
                JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON cl.id_category = c.id_category
                    AND cl.id_lang = ' . $lang . '
                    AND cl.id_shop = 1
                WHERE c.active = 1
                    AND c.id_category BETWEEN 301 AND 359
                ORDER BY c.id_category';

        $rows = $db->executeS($sql);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Catalogue interne actif (id, nom, marque) : sert au modèle pour choisir les
     * produits de cross-selling parmi ce qui EXISTE réellement en boutique.
     *
     * @return array<int, array{id_product:int, name:string, brand:string|null}>
     */
    protected function fetchCatalogue()
    {
        $db = Db::getInstance();
        $lang = (int) self::REFERENCE_LANG;
        $sql = 'SELECT p.id_product, pl.name, m.name AS brand
                FROM `' . _DB_PREFIX_ . 'product` p
                JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON pl.id_product = p.id_product AND pl.id_lang = ' . $lang . ' AND pl.id_shop = 1
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.id_manufacturer = p.id_manufacturer
                WHERE p.active = 1
                ORDER BY p.id_product';

        $rows = $db->executeS($sql);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Marques éditoriales = catégories filles de la catégorie 30, libellé FR.
     *
     * @return array<int, array{id_category:int, name:string}>
     */
    protected function fetchBrands()
    {
        $db = Db::getInstance();
        $lang = (int) self::REFERENCE_LANG;
        $sql = 'SELECT c.id_category, cl.name
                FROM `' . _DB_PREFIX_ . 'category` c
                JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON cl.id_category = c.id_category
                    AND cl.id_lang = ' . $lang . '
                    AND cl.id_shop = 1
                WHERE c.active = 1
                    AND c.id_parent = 30
                ORDER BY cl.name';

        $rows = $db->executeS($sql);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Blocs system : garde-fous + référentiel (mis en cache via cacheControl).
     *
     * @param array $categories
     * @param array $brands
     *
     * @return array
     */
    protected function buildSystemBlocks(array $categories, array $brands, array $catalogue = [])
    {
        $catList = [];
        foreach ($categories as $c) {
            $catList[] = (int) $c['id_category'] . ': ' . (string) $c['name'];
        }
        $brandList = [];
        foreach ($brands as $b) {
            $brandList[] = (int) $b['id_category'] . ': ' . (string) $b['name'];
        }

        $context = "Tu es un assistant catalogue pour une boutique B2B d'esthétique médicale "
            . "(injectables, dispositifs, consommables). Tu prépares un BROUILLON de fiche produit "
            . "que l'administrateur relira et validera avant publication.\n\n"
            . "GARDE-FOUS MÉDICAUX (impératifs) :\n"
            . "- N'INVENTE JAMAIS d'indication médicale, d'allégation thérapeutique, de dosage, "
            . "de mode d'administration ou de bénéfice clinique qui ne découle pas explicitement "
            . "du nom, de la référence ou des notes fournisseur.\n"
            . "- En cas d'incertitude sur un champ, laisse-le VIDE (chaîne vide, 0, ou false selon "
            . "le type) et ajoute son nom dans champs_incertains.\n"
            . "- Ne remplis que ce que tu peux déduire raisonnablement des informations fournies.\n"
            . "- Ton professionnel, factuel, orienté praticien (B2B), sans marketing exagéré.\n\n"
            . "CATÉGORIES ÉDITORIALES DISPONIBLES (choisis categorie_id parmi ces id uniquement ; "
            . "si aucune ne convient, mets 0 et signale-le) :\n"
            . (empty($catList) ? "(aucune)" : implode("\n", $catList)) . "\n\n"
            . "MARQUES CONNUES (utilise le libellé exact si le produit correspond à l'une d'elles ; "
            . "sinon laisse marque vide) :\n"
            . (empty($brandList) ? "(aucune)" : implode("\n", $brandList)) . "\n\n"
            . "confiance : nombre entre 0 et 1 estimant ta confiance globale dans le brouillon.\n"
            . "prix_ht_indicatif : prix HORS TAXE indicatif en euros (0 si inconnu).\n"
            . "avec_lidocaine : true seulement si le nom/réf/notes/page source l'indiquent explicitement.\n\n"
            . "RECHERCHE WEB OBLIGATOIRE : les informations produit ne sont PAS dans le message — "
            . "tu DOIS appeler web_search AVANT toute rédaction, ne réponds jamais de mémoire. "
            . "Trouve les pages de 2 à 3 fournisseurs ou boutiques décrivant EXACTEMENT ce produit "
            . "(même marque, même variante, même conditionnement), lis les plus fiables avec "
            . "web_fetch, et CROISE leurs informations. En cas de contradiction entre sources, "
            . "prends la valeur majoritaire et ajoute le champ dans champs_incertains. "
            . "Seule exception : si une PAGE SOURCE complète est déjà fournie dans le message, "
            . "elle est prioritaire et la recherche sert à compléter et vérifier.\n"
            . "Extrais des sources : la référence, l'ean13 (13 chiffres — souvent le champ sku/gtin13 "
            . "du JSON-LD), le prix_ht_indicatif (montant HORS taxe ; si les pages n'affichent que du "
            . "TTC, convertis seulement si le taux de TVA est explicite, sinon 0 + champs_incertains), "
            . "la marque, les caractéristiques. REFORMULE les descriptions avec tes mots : ne copie "
            . "jamais les textes des pages mot à mot (ce sont des sites concurrents).\n"
            . "Sois EXHAUSTIF sur le contenu produit : description_longue structurée en SECTIONS "
            . "titrées <h2> (Présentation, Composition & rhéologie, Indications, Posologie / "
            . "Protocole, Conservation, Sécurité et certifications…), chaque section contenant "
            . "1 à 3 <p> ou une <ul>. N'écris JAMAIS de paragraphes « <strong>Label :</strong> "
            . "texte » en guise de titres — utilise de vrais <h2>. Couvre tout ce que les sources "
            . "apportent de factuel — composition/concentration, indications et zones, protocole "
            . "d'injection, conditionnement, durée des effets, conservation, certification CE/MDR… "
            . "Reprends dans caracteristiques TOUTES les lignes des tableaux de spécifications "
            . "(label + valeur).\n\n"
            . "sources : les URLs des pages réellement consultées et utilisées (2-3 max, la plus "
            . "fiable en premier). Tableau vide si tu n'as rien trouvé de fiable.\n"
            . "points_cles : 4 à 6 points forts FACTUELS de ce produit précis (bénéfice, techno, "
            . "tenue, confort…), une phrase courte chacun, tirés des sources.\n"
            . "faq : 4 à 6 questions/réponses qu'un PRATICIEN se pose sur CE produit précis "
            . "(tenue, zones, technique, dilution, conservation, différences avec la gamme…). "
            . "Réponses factuelles de 2-3 phrases, fondées sur les sources et la notice ; ton "
            . "prudent, sans allégation inventée — renvoie à la notice fabricant en cas de doute.\n"
            . "composition : les composants du produit (label + valeur, ex. « Acide hyaluronique » "
            . "-> « 20 mg/ml réticulé »), d'après les sources. Tableau vide si inconnu.\n"
            . "cross_sell_ids : choisis dans le CATALOGUE INTERNE (second bloc système) 3 à 4 "
            . "produits COMPLÉMENTAIRES qu'un praticien achèterait avec ce produit — aiguilles/"
            . "canules compatibles, post-care, hyaluronidase, produit complémentaire de la même "
            . "gamme… PAS des substituts quasi identiques. Renvoie leurs id exacts ; [] si rien "
            . "de pertinent.\n"
            . "images : URLs d'images montrant LE produit décrit, vues sur les pages consultées "
            . "(og:image, galerie produit — jamais les produits associés ni l'habillage du site). "
            . "Image principale en premier. Tableau vide si aucune URL sûre : le module extraira "
            . "alors lui-même les images des pages listées dans sources.";

        $blocks = [
            [
                'type' => 'text',
                'text' => $context,
            ],
        ];

        // Catalogue interne (second bloc, le plus gros) : le breakpoint de cache est
        // posé sur le DERNIER bloc système -> tout le système est mis en cache.
        if (!empty($catalogue)) {
            $list = [];
            foreach ($catalogue as $row) {
                $brand = isset($row['brand']) && $row['brand'] ? ' (' . trim((string) $row['brand']) . ')' : '';
                $list[] = (int) $row['id_product'] . ': ' . trim((string) $row['name']) . $brand;
            }
            $blocks[] = [
                'type' => 'text',
                'text' => "CATALOGUE INTERNE (id: nom (marque)) — réservoir pour cross_sell_ids :\n"
                    . implode("\n", $list),
            ];
        }

        $blocks[count($blocks) - 1]['cacheControl'] = ['type' => 'ephemeral'];

        return $blocks;
    }

    /**
     * Message utilisateur = données saisies par l'admin + contenu de la page source.
     *
     * @param array $input
     * @param array|null $source retour de HfmAiSourceScraper::scrape() (ou null)
     *
     * @return string
     */
    protected function buildUserMessage(array $input, $source = null)
    {
        $name = isset($input['name']) ? (string) $input['name'] : '';
        $reference = isset($input['reference']) ? (string) $input['reference'] : '';
        $notes = isset($input['notes']) ? (string) $input['notes'] : '';
        $idLang = isset($input['id_lang']) ? (int) $input['id_lang'] : 1;

        $msg = "Génère le brouillon de fiche produit à partir de ces informations "
            . "(langue de base id_lang = " . $idLang . ", rédige les textes en français) :\n\n"
            . "Nom : " . $name . "\n"
            . "Référence : " . $reference . "\n"
            . "Notes fournisseur : " . ($notes !== '' ? $notes : '(aucune)');

        if ($source === null) {
            return $msg;
        }

        $msg .= "\n\n=== PAGE SOURCE : " . $source['url'] . " ===\n";
        if (!empty($source['jsonld'])) {
            $msg .= "\n--- Données structurées (JSON-LD) ---\n" . implode("\n", $source['jsonld']) . "\n";
        }
        if ($source['text'] !== '') {
            $msg .= "\n--- Texte de la page ---\n" . $source['text'] . "\n";
        }
        if (!empty($source['images'])) {
            $msg .= "\n--- IMAGES CANDIDATES ---\n";
            foreach ($source['images'] as $i => $u) {
                $msg .= ($i + 1) . '. ' . $u . "\n";
            }
        }

        return $msg;
    }

    /**
     * Schéma json_schema (structured output). Tous les champs requis,
     * additionalProperties:false. L'enum de categorie_id = les id réels 301-359.
     *
     * @param array<int> $categoryIds
     *
     * @return array
     */
    protected function buildSchema(array $categoryIds)
    {
        return [
            'type' => 'object',
            'properties' => [
                'nom' => ['type' => 'string'],
                'reference' => ['type' => 'string'],
                'ean13' => ['type' => 'string'],
                'marque' => ['type' => 'string'],
                'categorie_id' => [
                    'type' => 'integer',
                    'enum' => array_values(array_unique($categoryIds)),
                ],
                'description_courte' => ['type' => 'string'],
                'description_longue' => ['type' => 'string'],
                'caracteristiques' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'valeur' => ['type' => 'string'],
                        ],
                        'required' => ['label', 'valeur'],
                        'additionalProperties' => false,
                    ],
                ],
                'avec_lidocaine' => ['type' => 'boolean'],
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'prix_ht_indicatif' => ['type' => 'number'],
                'images' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'sources' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'points_cles' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'faq' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'reponse' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'reponse'],
                        'additionalProperties' => false,
                    ],
                ],
                'composition' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'valeur' => ['type' => 'string'],
                        ],
                        'required' => ['label', 'valeur'],
                        'additionalProperties' => false,
                    ],
                ],
                'cross_sell_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                ],
                'confiance' => ['type' => 'number'],
                'champs_incertains' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => [
                'nom',
                'reference',
                'ean13',
                'marque',
                'categorie_id',
                'description_courte',
                'description_longue',
                'caracteristiques',
                'avec_lidocaine',
                'meta_title',
                'meta_description',
                'prix_ht_indicatif',
                'images',
                'sources',
                'points_cles',
                'faq',
                'composition',
                'cross_sell_ids',
                'confiance',
                'champs_incertains',
            ],
            'additionalProperties' => false,
        ];
    }
}
