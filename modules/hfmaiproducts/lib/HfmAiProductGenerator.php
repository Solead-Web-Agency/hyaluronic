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

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;

class HfmAiProductGenerator
{
    /** id_lang FR par défaut (contexte lu en FR pour le référentiel). */
    const REFERENCE_LANG = 1;

    /**
     * @param array $input ['name'=>, 'reference'=>, 'id_lang'=>, 'notes'=>]
     *
     * @return array Brouillon décodé (clés du schéma json_schema)
     *
     * @throws Exception message lisible (clé absente, erreur API, réponse vide)
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

        // Référentiel (catégories éditoriales + marques) lu en base.
        $categories = $this->fetchEditorialCategories();
        $brands = $this->fetchBrands();

        // Ids réels des catégories éditoriales -> enum du schéma.
        $categoryIds = array_map(static function ($c) {
            return (int) $c['id_category'];
        }, $categories);
        if (empty($categoryIds)) {
            // Garde-fou : un enum vide serait rejeté par l'API. On tolère 0 (= à classer).
            $categoryIds = [0];
        }

        $schema = $this->buildSchema($categoryIds);
        $systemBlocks = $this->buildSystemBlocks($categories, $brands);
        $userText = $this->buildUserMessage($input);

        try {
            $client = new Client(apiKey: $apiKey);

            $message = $client->messages->create(
                model: $model,
                maxTokens: 16000,
                system: $systemBlocks,
                messages: [
                    ['role' => 'user', 'content' => $userText],
                ],
                outputConfig: [
                    'format' => [
                        'type' => 'json_schema',
                        'schema' => $schema,
                    ],
                ],
            );
        } catch (APIStatusException $e) {
            // On expose un message lisible sans jamais divulguer la clé.
            // La propriété ->status (code HTTP) est présente sur l'exception ; on
            // reste défensif au cas où une future version exposerait ->type.
            $status = isset($e->status) ? (int) $e->status : 0;
            $detail = $status ? ('HTTP ' . $status) : 'erreur API';
            throw new Exception('Appel à Claude en échec (' . $detail . '). Vérifiez la clé API et réessayez.');
        } catch (Throwable $e) {
            // Réseau, autoload, etc. : message générique côté admin.
            throw new Exception('Impossible de contacter Claude : ' . $e->getMessage());
        }

        // On récupère le premier bloc texte (contient le JSON structuré).
        $json = null;
        foreach ($message->content as $block) {
            if (isset($block->type) && $block->type === 'text') {
                $json = $block->text;
                break;
            }
        }

        if ($json === null) {
            throw new Exception('Réponse de Claude vide ou inattendue.');
        }

        $draft = json_decode($json, true);
        if (!is_array($draft)) {
            throw new Exception('Réponse de Claude illisible (JSON invalide).');
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
    protected function buildSystemBlocks(array $categories, array $brands)
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
            . "avec_lidocaine : true seulement si le nom/réf/notes l'indiquent explicitement.";

        return [
            [
                'type' => 'text',
                'text' => $context,
                'cacheControl' => ['type' => 'ephemeral'],
            ],
        ];
    }

    /**
     * Message utilisateur = données saisies par l'admin.
     *
     * @param array $input
     *
     * @return string
     */
    protected function buildUserMessage(array $input)
    {
        $name = isset($input['name']) ? (string) $input['name'] : '';
        $reference = isset($input['reference']) ? (string) $input['reference'] : '';
        $notes = isset($input['notes']) ? (string) $input['notes'] : '';
        $idLang = isset($input['id_lang']) ? (int) $input['id_lang'] : 1;

        return "Génère le brouillon de fiche produit à partir de ces informations "
            . "(langue de base id_lang = " . $idLang . ", rédige les textes en français) :\n\n"
            . "Nom : " . $name . "\n"
            . "Référence : " . $reference . "\n"
            . "Notes fournisseur : " . ($notes !== '' ? $notes : '(aucune)');
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
                'confiance',
                'champs_incertains',
            ],
            'additionalProperties' => false,
        ];
    }
}
