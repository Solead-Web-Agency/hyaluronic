<?php
/**
 * AdminHfmAiProductsController — écran BO du module Produits IA.
 *
 * Flux en 2 étapes :
 *   1) Formulaire de saisie (Nom / Référence / langue de base / notes) -> bouton « Générer ».
 *      À la soumission, on appelle HfmAiProductGenerator->generate() (Claude) et on
 *      réaffiche un formulaire d'ÉDITION pré-rempli avec TOUS les champs du brouillon,
 *      un badge de confiance et la liste des champs incertains.
 *   2) Le formulaire d'édition (bouton « Créer le produit ») construit un Product PS,
 *      multilingue, inactif (brouillon non publié), et renvoie un lien vers la fiche
 *      standard du BO pour finaliser (images, etc.).
 *
 * Le brouillon transite entre les deux étapes via un champ caché JSON.
 * Sécurité : toutes les sorties sont échappées ; entrées cast/validées ; token géré par PS.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminHfmAiProductsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * Rendu principal : on pilote nous-mêmes l'affichage (pas de liste standard).
     */
    public function renderView()
    {
        return $this->renderScreen();
    }

    /**
     * Le contrôleur n'a pas de liste : on renvoie l'écran custom en initContent.
     */
    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign('content', $this->renderScreen());
    }

    /**
     * Dispatch des actions et construction du HTML de l'écran.
     */
    protected function renderScreen()
    {
        $html = '';

        if (Tools::isSubmit('submitCreate')) {
            $html .= $this->processCreate();
            // Après création, on repropose un formulaire de saisie vierge.
            return $html . $this->renderInputForm();
        }

        if (Tools::isSubmit('submitGenerate')) {
            return $html . $this->processGenerate();
        }

        return $this->renderInputForm();
    }

    // ------------------------------------------------------------------
    // Étape 1 : formulaire de saisie
    // ------------------------------------------------------------------

    protected function renderInputForm()
    {
        $token = $this->getCurrentToken();
        $action = self::$currentIndex . '&token=' . $token;

        $langs = Language::getLanguages(true);
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $out = '<div class="panel">';
        $out .= '<div class="panel-heading"><i class="icon-magic"></i> ' . $this->l('Générer une fiche produit par IA') . '</div>';
        $out .= '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" class="form-horizontal">';

        $out .= $this->formGroup(
            $this->l('Nom'),
            '<input type="text" name="name" class="form-control" required value="' . htmlspecialchars((string) Tools::getValue('name'), ENT_QUOTES, 'UTF-8') . '"/>'
        );
        $out .= $this->formGroup(
            $this->l('Référence'),
            '<input type="text" name="reference" class="form-control" value="' . htmlspecialchars((string) Tools::getValue('reference'), ENT_QUOTES, 'UTF-8') . '"/>'
        );

        // Langue de base.
        $langSelect = '<select name="id_lang" class="form-control">';
        foreach ($langs as $lang) {
            $idLang = (int) $lang['id_lang'];
            $selected = ($idLang === $defaultLang) ? ' selected' : '';
            $langSelect .= '<option value="' . $idLang . '"' . $selected . '>'
                . htmlspecialchars((string) $lang['name'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $langSelect .= '</select>';
        $out .= $this->formGroup($this->l('Langue de base'), $langSelect);

        $out .= $this->formGroup(
            $this->l('Notes fournisseur'),
            '<textarea name="notes" rows="5" class="form-control" placeholder="' . htmlspecialchars($this->l('Infos fournisseur, optionnel'), ENT_QUOTES, 'UTF-8') . '"></textarea>'
        );

        $out .= '<div class="panel-footer">';
        $out .= '<button type="submit" name="submitGenerate" class="btn btn-primary pull-right">'
            . '<i class="process-icon-cogs"></i> ' . $this->l('Générer') . '</button>';
        $out .= '</div>';
        $out .= '</form></div>';

        return $out;
    }

    // ------------------------------------------------------------------
    // Étape 1bis : génération IA -> formulaire d'édition
    // ------------------------------------------------------------------

    protected function processGenerate()
    {
        $input = [
            'name' => trim((string) Tools::getValue('name')),
            'reference' => trim((string) Tools::getValue('reference')),
            'id_lang' => (int) Tools::getValue('id_lang'),
            'notes' => (string) Tools::getValue('notes'),
        ];

        if ($input['name'] === '') {
            return $this->displayWarning($this->l('Le nom est obligatoire.')) . $this->renderInputForm();
        }
        // Validation légère du texte libre.
        if ($input['notes'] !== '' && !Validate::isCleanHtml($input['notes'])) {
            return $this->displayWarning($this->l('Les notes contiennent du contenu non autorisé.')) . $this->renderInputForm();
        }

        require_once dirname(__FILE__) . '/../../lib/HfmAiProductGenerator.php';

        try {
            $generator = new HfmAiProductGenerator();
            $draft = $generator->generate($input);
        } catch (Exception $e) {
            // Erreur claire, on ne plante pas : on repropose la saisie.
            return $this->displayWarning(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'))
                . $this->renderInputForm();
        }

        // On mémorise la langue de base choisie dans le brouillon pour l'étape de création.
        $draft['_id_lang'] = $input['id_lang'];

        return $this->renderEditForm($draft);
    }

    /**
     * Formulaire d'ÉDITION pré-rempli (tous les champs éditables) + bouton « Créer ».
     *
     * @param array $draft brouillon (clés du schéma + _id_lang)
     */
    protected function renderEditForm(array $draft)
    {
        $token = $this->getCurrentToken();
        $action = self::$currentIndex . '&token=' . $token;

        // Valeurs sûres.
        $g = static function ($arr, $key, $default = '') {
            return isset($arr[$key]) ? $arr[$key] : $default;
        };

        $confiance = (float) $g($draft, 'confiance', 0);
        $confiancePct = (int) round(max(0, min(1, $confiance)) * 100);
        $uncertain = is_array($g($draft, 'champs_incertains', [])) ? $draft['champs_incertains'] : [];

        $out = '<div class="panel">';
        $out .= '<div class="panel-heading"><i class="icon-edit"></i> ' . $this->l('Brouillon généré — à valider') . '</div>';

        // Badge de confiance.
        $badgeClass = $confiancePct >= 70 ? 'success' : ($confiancePct >= 40 ? 'warning' : 'danger');
        $out .= '<div style="padding:12px 18px;">';
        $out .= '<span class="badge badge-' . $badgeClass . '" style="font-size:13px;">'
            . $this->l('Confiance') . ' : ' . $confiancePct . '%</span>';
        if (!empty($uncertain)) {
            $safeList = array_map(static function ($f) {
                return htmlspecialchars((string) $f, ENT_QUOTES, 'UTF-8');
            }, $uncertain);
            $out .= ' <span class="text-muted">' . $this->l('Champs incertains') . ' : '
                . implode(', ', $safeList) . '</span>';
        }
        $out .= '</div>';

        $out .= '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" class="form-horizontal">';

        // Brouillon complet transmis via champ caché JSON (source de vérité pour
        // les champs non ré-édités : caractéristiques, catégorie, marque, langue…).
        $out .= '<input type="hidden" name="draft_json" value="'
            . htmlspecialchars(json_encode($draft), ENT_QUOTES, 'UTF-8') . '"/>';

        $out .= $this->formGroup($this->l('Nom'),
            '<input type="text" name="d_nom" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'nom'), ENT_QUOTES, 'UTF-8') . '"/>');
        $out .= $this->formGroup($this->l('Référence'),
            '<input type="text" name="d_reference" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'reference'), ENT_QUOTES, 'UTF-8') . '"/>');
        $out .= $this->formGroup($this->l('EAN13'),
            '<input type="text" name="d_ean13" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'ean13'), ENT_QUOTES, 'UTF-8') . '"/>');
        $out .= $this->formGroup($this->l('Marque'),
            '<input type="text" name="d_marque" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'marque'), ENT_QUOTES, 'UTF-8') . '"/>');
        // Catégorie éditoriale : select restreint aux catégories 301-359 (revalidé à la création).
        $catSelected = (int) $g($draft, 'categorie_id', 0);
        $catSelect = '<select name="d_categorie_id" class="form-control">';
        $catSelect .= '<option value="0">' . $this->l('— choisir —') . '</option>';
        foreach ($this->getEditorialCategories() as $idCat => $catName) {
            $sel = ($idCat === $catSelected) ? ' selected' : '';
            $catSelect .= '<option value="' . (int) $idCat . '"' . $sel . '>'
                . htmlspecialchars($catName . ' (#' . $idCat . ')', ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $catSelect .= '</select>';
        $out .= $this->formGroup($this->l('Catégorie éditoriale'), $catSelect);

        $out .= $this->formGroup($this->l('Prix HT indicatif (€)'),
            '<input type="text" name="d_prix_ht" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'prix_ht_indicatif', 0), ENT_QUOTES, 'UTF-8') . '"/>');

        // Groupe de taxe (TVA) : indispensable pour que le prix HT soit correctement taxé.
        // Par défaut le 1er groupe actif de la boutique (l'admin peut choisir « Aucune taxe »).
        $taxSelect = '<select name="d_tax_rules_group" class="form-control">';
        $firstTax = true;
        foreach (TaxRulesGroup::getTaxRulesGroups(true) as $trg) {
            $sel = $firstTax ? ' selected' : '';
            $firstTax = false;
            $taxSelect .= '<option value="' . (int) $trg['id_tax_rules_group'] . '"' . $sel . '>'
                . htmlspecialchars((string) $trg['name'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $taxSelect .= '<option value="0">' . $this->l('Aucune taxe') . '</option>';
        $taxSelect .= '</select>';
        $out .= $this->formGroup($this->l('Groupe de taxe (TVA)'), $taxSelect);

        $lidoChecked = ((bool) $g($draft, 'avec_lidocaine', false)) ? ' checked' : '';
        $out .= $this->formGroup($this->l('Avec lidocaïne'),
            '<input type="checkbox" name="d_avec_lidocaine" value="1"' . $lidoChecked . '/>');

        $out .= $this->formGroup($this->l('Description courte'),
            '<textarea name="d_description_courte" rows="3" class="form-control">' . htmlspecialchars((string) $g($draft, 'description_courte'), ENT_QUOTES, 'UTF-8') . '</textarea>');
        $out .= $this->formGroup($this->l('Description longue'),
            '<textarea name="d_description_longue" rows="8" class="form-control">' . htmlspecialchars((string) $g($draft, 'description_longue'), ENT_QUOTES, 'UTF-8') . '</textarea>');
        $out .= $this->formGroup($this->l('Meta title'),
            '<input type="text" name="d_meta_title" class="form-control" value="' . htmlspecialchars((string) $g($draft, 'meta_title'), ENT_QUOTES, 'UTF-8') . '"/>');
        $out .= $this->formGroup($this->l('Meta description'),
            '<textarea name="d_meta_description" rows="2" class="form-control">' . htmlspecialchars((string) $g($draft, 'meta_description'), ENT_QUOTES, 'UTF-8') . '</textarea>');

        // Caractéristiques : lecture seule (transmises via draft_json).
        $caracs = is_array($g($draft, 'caracteristiques', [])) ? $draft['caracteristiques'] : [];
        if (!empty($caracs)) {
            $rows = '';
            foreach ($caracs as $c) {
                $label = htmlspecialchars((string) (isset($c['label']) ? $c['label'] : ''), ENT_QUOTES, 'UTF-8');
                $valeur = htmlspecialchars((string) (isset($c['valeur']) ? $c['valeur'] : ''), ENT_QUOTES, 'UTF-8');
                $rows .= '<tr><td>' . $label . '</td><td>' . $valeur . '</td></tr>';
            }
            $out .= '<div class="form-group"><label class="control-label col-lg-3">' . $this->l('Caractéristiques') . '</label>';
            $out .= '<div class="col-lg-9"><table class="table"><thead><tr><th>' . $this->l('Label') . '</th><th>' . $this->l('Valeur') . '</th></tr></thead><tbody>'
                . $rows . '</tbody></table>'
                . '<p class="help-block">' . $this->l('Ces caractéristiques seront ajoutées à la description du produit.') . '</p></div></div>';
        }

        $out .= '<div class="panel-footer">';
        $out .= '<button type="submit" name="submitCreate" class="btn btn-primary pull-right">'
            . '<i class="process-icon-new"></i> ' . $this->l('Créer le produit') . '</button>';
        $out .= '</div>';
        $out .= '</form></div>';

        return $out;
    }

    // ------------------------------------------------------------------
    // Étape 2 : création réelle du produit
    // ------------------------------------------------------------------

    protected function processCreate()
    {
        // Le brouillon (champs non ré-édités) vient du JSON caché ; les champs éditables
        // priment via les champs d_*.
        $draft = json_decode((string) Tools::getValue('draft_json'), true);
        if (!is_array($draft)) {
            $draft = [];
        }

        $idBaseLang = (int) (isset($draft['_id_lang']) ? $draft['_id_lang'] : Configuration::get('PS_LANG_DEFAULT'));
        if ($idBaseLang <= 0) {
            $idBaseLang = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        // Champs édités par l'admin (priment).
        $name = trim((string) Tools::getValue('d_nom'));
        $reference = trim((string) Tools::getValue('d_reference'));
        $ean13 = trim((string) Tools::getValue('d_ean13'));
        $marque = trim((string) Tools::getValue('d_marque'));
        $categorieId = (int) Tools::getValue('d_categorie_id');
        $prixHt = (float) str_replace(',', '.', (string) Tools::getValue('d_prix_ht'));
        $avecLidocaine = (bool) Tools::getValue('d_avec_lidocaine');
        $descCourte = (string) Tools::getValue('d_description_courte');
        $descLongue = (string) Tools::getValue('d_description_longue');
        $metaTitle = (string) Tools::getValue('d_meta_title');
        $metaDesc = (string) Tools::getValue('d_meta_description');
        $taxRulesGroup = (int) Tools::getValue('d_tax_rules_group');

        // Validation alignée sur les VRAIS validateurs des champs Product de PrestaShop
        // (sinon Product::add() lève une PrestaShopException fatale, gérée aussi par le try/catch plus bas).
        if ($name === '' || !Validate::isCatalogName($name)) {
            return $this->displayWarning($this->l('Le nom est manquant ou contient des caractères interdits (< > { }).'));
        }
        // Descriptions : HTML riche autorisé (isCleanHtml, comme le modèle Product).
        foreach ([$descCourte, $descLongue] as $rich) {
            if ($rich !== '' && !Validate::isCleanHtml($rich)) {
                return $this->displayWarning($this->l('Une description contient du contenu non autorisé.'));
            }
        }
        // Meta : validateur strict isGenericName (interdit < > { }).
        foreach ([$metaTitle, $metaDesc] as $meta) {
            if ($meta !== '' && !Validate::isGenericName($meta)) {
                return $this->displayWarning($this->l('Un champ meta contient des caractères interdits (< > { }).'));
            }
        }

        // Persiste les caractéristiques du brouillon + l'info « avec lidocaïne » (critère métier)
        // en les ajoutant à la description longue (le modèle Product accepte du HTML propre).
        $extra = '';
        $caracs = (isset($draft['caracteristiques']) && is_array($draft['caracteristiques'])) ? $draft['caracteristiques'] : [];
        $rowsHtml = '';
        foreach ($caracs as $c) {
            $label = isset($c['label']) ? trim((string) $c['label']) : '';
            $valeur = isset($c['valeur']) ? trim((string) $c['valeur']) : '';
            if ($label !== '' || $valeur !== '') {
                $rowsHtml .= '<li><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong> : '
                    . htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8') . '</li>';
            }
        }
        if ($rowsHtml !== '') {
            $extra .= '<h3>' . $this->l('Caractéristiques') . '</h3><ul>' . $rowsHtml . '</ul>';
        }
        $extra .= '<p><strong>' . $this->l('Avec lidocaïne') . '</strong> : '
            . ($avecLidocaine ? $this->l('Oui') : $this->l('Non')) . '</p>';
        $descLongue = trim($descLongue . $extra);

        // Multilingue : on remplit la langue de base ET on recopie sur toutes les langues actives.
        $langs = Language::getLanguages(true);

        $nameML = [];
        $descCourteML = [];
        $descLongueML = [];
        $metaTitleML = [];
        $metaDescML = [];
        $linkRewriteML = [];

        // Valeur de base (langue choisie).
        $safeName = Tools::substr($name, 0, 128);
        foreach ($langs as $lang) {
            $idLang = (int) $lang['id_lang'];
            $nameML[$idLang] = $safeName;
            $descCourteML[$idLang] = $descCourte;
            $descLongueML[$idLang] = $descLongue;
            $metaTitleML[$idLang] = Tools::substr($metaTitle, 0, 255);
            $metaDescML[$idLang] = Tools::substr($metaDesc, 0, 512);
            $linkRewriteML[$idLang] = Tools::str2url($safeName) ?: 'produit-ia-' . time();
        }

        $product = new Product();
        $product->name = $nameML;
        $product->description = $descLongueML;
        $product->description_short = $descCourteML;
        $product->meta_title = $metaTitleML;
        $product->meta_description = $metaDescML;
        $product->link_rewrite = $linkRewriteML;

        $product->reference = Tools::substr($reference, 0, 64);
        if ($ean13 !== '' && Validate::isEan13($ean13)) {
            $product->ean13 = $ean13;
        }

        $product->price = $prixHt > 0 ? $prixHt : 0; // prix HORS taxe
        $product->id_tax_rules_group = $taxRulesGroup > 0 ? $taxRulesGroup : 0; // groupe de TVA choisi

        // Fabricant : on cherche par nom, on ne crée jamais.
        if ($marque !== '') {
            $idManufacturer = (int) Manufacturer::getIdByName($marque);
            if ($idManufacturer) {
                $product->id_manufacturer = $idManufacturer;
            }
        }

        // Catégorie éditoriale : doit appartenir à l'ensemble éditorial réel (301-359).
        $editorial = $this->getEditorialCategories();
        if ($categorieId <= 0 || !isset($editorial[$categorieId])) {
            return $this->displayWarning($this->l('Choisissez une catégorie éditoriale valide (Catalogue / Par zone / Par effet).'));
        }
        $idCategory = $categorieId;
        $product->id_category_default = $idCategory;

        $product->active = 0; // brouillon : non publié
        $product->state = 1;

        // La validation stricte des champs multilingues de Product peut lever une
        // PrestaShopException (die=true) : on l'attrape pour renvoyer une erreur propre.
        try {
            if (!$product->add()) {
                return $this->displayWarning($this->l('Échec de la création du produit dans le catalogue.'));
            }
            // Rattachement aux catégories : catégorie éditoriale + Accueil (2).
            $categories = array_values(array_unique(array_filter([$idCategory, 2])));
            if (!empty($categories)) {
                $product->addToCategories($categories);
            }
        } catch (PrestaShopException $e) {
            return $this->displayWarning(
                $this->l('Création refusée par PrestaShop : ') . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
            );
        }

        // Lien vers la fiche produit standard du BO pour finaliser (images, etc.).
        $productToken = Tools::getAdminTokenLite('AdminProducts');
        $productLink = 'index.php?controller=AdminProducts&id_product=' . (int) $product->id
            . '&updateproduct&token=' . $productToken;

        $msg = sprintf(
            $this->l('Produit #%d créé (inactif). '),
            (int) $product->id
        );
        $msg .= '<a href="' . htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8') . '" class="btn btn-default btn-xs">'
            . '<i class="icon-pencil"></i> ' . $this->l('Finaliser la fiche (images, publication…)') . '</a>';

        return $this->displayConfirmation($msg);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Catégories éditoriales actives (301-359) : [id_category => nom] dans la langue par défaut.
     * Sert au <select> du formulaire d'édition ET à la revalidation à la création.
     */
    protected function getEditorialCategories()
    {
        static $cats = null;
        if ($cats !== null) {
            return $cats;
        }
        $cats = [];
        $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $idShop = (int) $this->context->shop->id;
        $sql = 'SELECT c.id_category, cl.name
                FROM ' . _DB_PREFIX_ . 'category c
                INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                    ON (cl.id_category = c.id_category AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . $idShop . ')
                WHERE c.active = 1 AND c.id_category BETWEEN 301 AND 359
                ORDER BY c.id_category';
        foreach ((array) Db::getInstance()->executeS($sql) as $r) {
            $cats[(int) $r['id_category']] = $r['name'];
        }
        return $cats;
    }

    /**
     * Un groupe de formulaire Bootstrap PS (label + champ).
     */
    protected function formGroup($label, $field)
    {
        return '<div class="form-group">'
            . '<label class="control-label col-lg-3">' . htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') . '</label>'
            . '<div class="col-lg-9">' . $field . '</div>'
            . '</div>';
    }

    /**
     * Token courant du contrôleur (géré par PS).
     */
    protected function getCurrentToken()
    {
        return $this->token ?: Tools::getAdminTokenLite('AdminHfmAiProducts');
    }
}
