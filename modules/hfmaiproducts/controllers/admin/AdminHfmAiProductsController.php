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
        $out .= $this->formGroup(
            $this->l('URL de la page source (optionnel)'),
            '<input type="url" name="source_url" class="form-control" placeholder="https://…" value="'
            . htmlspecialchars((string) Tools::getValue('source_url'), ENT_QUOTES, 'UTF-8') . '"/>'
            . '<p class="help-block">' . $this->l('Sans URL, l\'IA cherche elle-même le produit sur le web, croise 2-3 pages fournisseurs et en extrait référence, EAN13, prix HT, descriptions et images.') . '</p>'
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
            'source_url' => trim((string) Tools::getValue('source_url')),
        ];

        if ($input['name'] === '') {
            return $this->displayWarning($this->l('Le nom est obligatoire.')) . $this->renderInputForm();
        }
        // Validation légère du texte libre.
        if ($input['notes'] !== '' && !Validate::isCleanHtml($input['notes'])) {
            return $this->displayWarning($this->l('Les notes contiennent du contenu non autorisé.')) . $this->renderInputForm();
        }
        if ($input['source_url'] !== '' && !Validate::isAbsoluteUrl($input['source_url'])) {
            return $this->displayWarning($this->l('L\'URL de la page source est invalide.')) . $this->renderInputForm();
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
        // Diagnostic de lecture de la page source : l'admin voit ce qui a réellement
        // été extrait (et comprend un brouillon maigre au lieu de le subir).
        $stats = $g($draft, '_source_stats', null);
        if (is_array($stats)) {
            $out .= '<br/><span class="text-muted" style="font-size:12px;">'
                . sprintf(
                    $this->l('Page source lue : %d caractères de texte · %d bloc(s) de données structurées · %d image(s) candidate(s).'),
                    (int) $stats['text'],
                    (int) $stats['jsonld'],
                    (int) $stats['images']
                )
                . '</span>';
            if ((int) $stats['text'] < 500 && (int) $stats['jsonld'] === 0) {
                $out .= '<div class="alert alert-warning" style="margin:10px 0 0;">'
                    . $this->l('Cette page n\'a livré presque aucun contenu exploitable (site probablement rendu en JavaScript). Le brouillon sera pauvre : complétez les notes fournisseur ou essayez une autre URL.')
                    . '</div>';
            }
        }
        // Sources web consultées par l'IA (recherche automatique).
        $sources = is_array($g($draft, 'sources', [])) ? $draft['sources'] : [];
        if (!empty($sources)) {
            $links = [];
            foreach ($sources as $u) {
                if (!is_string($u) || !Validate::isAbsoluteUrl($u)) {
                    continue;
                }
                $safe = htmlspecialchars($u, ENT_QUOTES, 'UTF-8');
                $links[] = '<a href="' . $safe . '" target="_blank" rel="noopener noreferrer">' . $safe . '</a>';
            }
            if (!empty($links)) {
                $out .= '<br/><span class="text-muted" style="font-size:12px;">'
                    . $this->l('Sources consultées') . ' : ' . implode(' · ', $links) . '</span>';
            }
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
        // Par défaut, le groupe le plus utilisé par les produits actifs du catalogue
        // (en pratique la TVA standard de la boutique), et non le 1er de la liste.
        $defaultTax = (int) Db::getInstance()->getValue(
            'SELECT id_tax_rules_group FROM ' . _DB_PREFIX_ . 'product
             WHERE active = 1 AND id_tax_rules_group > 0
             GROUP BY id_tax_rules_group ORDER BY COUNT(*) DESC'
        );
        $taxSelect = '<select name="d_tax_rules_group" class="form-control">';
        foreach (TaxRulesGroup::getTaxRulesGroups(true) as $trg) {
            $idTrg = (int) $trg['id_tax_rules_group'];
            $sel = ($idTrg === $defaultTax) ? ' selected' : '';
            $taxSelect .= '<option value="' . $idTrg . '"' . $sel . '>'
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

        // Images : celles retenues par l'IA (cochées) puis les autres candidates de la
        // page (décochées) — l'admin garde la main même si le modèle a été trop prudent.
        // La 1re image cochée à la soumission deviendra la couverture du produit.
        $selected = is_array($g($draft, 'images', [])) ? $draft['images'] : [];
        $candidates = is_array($g($draft, '_source_images', [])) ? $draft['_source_images'] : [];
        $all = array_values(array_unique(array_merge($selected, $candidates)));
        $imgCells = '';
        foreach ($all as $i => $u) {
            if (!is_string($u) || !Validate::isAbsoluteUrl($u)) {
                continue;
            }
            $isSelected = in_array($u, $selected, true);
            $safeUrl = htmlspecialchars($u, ENT_QUOTES, 'UTF-8');
            $imgCells .= '<label style="display:inline-block;margin:0 14px 14px 0;text-align:center;vertical-align:top;cursor:pointer;max-width:160px;">'
                . '<img src="' . $safeUrl . '" alt="" loading="lazy" style="height:90px;max-width:160px;object-fit:contain;display:block;border:2px solid ' . ($isSelected ? '#72c279' : '#ddd') . ';border-radius:4px;background:#fff;margin-bottom:5px;"/>'
                . '<input type="checkbox" name="d_images[]" value="' . $safeUrl . '"' . ($isSelected ? ' checked' : '') . '/> '
                . '<span style="font-size:11px;">' . ($isSelected ? $this->l('retenue par l\'IA') : $this->l('candidate')) . '</span>'
                . '</label>';
        }
        if ($imgCells !== '') {
            $out .= $this->formGroup(
                $this->l('Images'),
                $imgCells . '<p class="help-block">' . $this->l('Images de la page source : cochez celles à importer (la première cochée devient la couverture). Les images « candidates » viennent de la page mais n\'ont pas été retenues par l\'IA — souvent des produits associés, vérifiez avant de cocher.') . '</p>'
            );
        }

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

        // Contenus éditoriaux générés (lecture seule, transmis via draft_json).
        $points = is_array($g($draft, 'points_cles', [])) ? $draft['points_cles'] : [];
        if (!empty($points)) {
            $lis = '';
            foreach ($points as $p) {
                $lis .= '<li>' . htmlspecialchars((string) $p, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $out .= $this->formGroup($this->l('Points clés'), '<ul style="margin:6px 0;">' . $lis . '</ul>');
        }
        $compo = is_array($g($draft, 'composition', [])) ? $draft['composition'] : [];
        if (!empty($compo)) {
            $rows = '';
            foreach ($compo as $c) {
                $rows .= '<tr><td>' . htmlspecialchars((string) (isset($c['label']) ? $c['label'] : ''), ENT_QUOTES, 'UTF-8') . '</td><td>'
                    . htmlspecialchars((string) (isset($c['valeur']) ? $c['valeur'] : ''), ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            $out .= $this->formGroup($this->l('Composition'), '<table class="table"><tbody>' . $rows . '</tbody></table>');
        }
        // Cross-selling proposé par l'IA (produits existants du catalogue, cochables).
        $crossSell = is_array($g($draft, '_cross_sell', [])) ? $draft['_cross_sell'] : [];
        if (!empty($crossSell)) {
            $boxes = '';
            foreach ($crossSell as $cs) {
                $cid = (int) (isset($cs['id']) ? $cs['id'] : 0);
                $cname = htmlspecialchars((string) (isset($cs['name']) ? $cs['name'] : ''), ENT_QUOTES, 'UTF-8');
                if ($cid) {
                    $boxes .= '<label style="display:block;margin-bottom:6px;cursor:pointer;">'
                        . '<input type="checkbox" name="d_cross[]" value="' . $cid . '" checked/> '
                        . $cname . ' <span class="text-muted">#' . $cid . '</span></label>';
                }
            }
            $out .= $this->formGroup($this->l('Souvent achetés ensemble'), $boxes
                . '<p class="help-block">' . $this->l('Produits complémentaires du catalogue choisis par l\'IA — décochez ceux à ne pas lier.') . '</p>');
        }

        $faq = is_array($g($draft, 'faq', [])) ? $draft['faq'] : [];
        if (!empty($faq)) {
            $blocks = '';
            foreach ($faq as $f) {
                $blocks .= '<div style="margin-bottom:10px;"><strong>'
                    . htmlspecialchars((string) (isset($f['question']) ? $f['question'] : ''), ENT_QUOTES, 'UTF-8') . '</strong><br/>'
                    . htmlspecialchars((string) (isset($f['reponse']) ? $f['reponse'] : ''), ENT_QUOTES, 'UTF-8') . '</div>';
            }
            $out .= $this->formGroup($this->l('FAQ produit'), $blocks
                . '<p class="help-block">' . $this->l('Points clés, composition et FAQ seront affichés sur la fiche produit du site.') . '</p>');
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

        // Les caractéristiques deviennent de VRAIES caractéristiques PrestaShop
        // (créées après le Product::add(), voir plus bas) — plus d'ajout à la description.
        $caracs = (isset($draft['caracteristiques']) && is_array($draft['caracteristiques'])) ? $draft['caracteristiques'] : [];

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

        // Caractéristiques réelles (ps_feature) : celles du brouillon + « Lidocaïne ».
        $this->attachFeatures($product, $caracs, $avecLidocaine);

        // Contenus éditoriaux IA (points clés, FAQ, composition) pour le front headless.
        $this->saveExtras((int) $product->id, $draft);

        // Cross-selling : liaison accessoires PS avec les produits cochés (revalidés en base).
        $crossLinked = $this->attachCrossSell($product, Tools::getValue('d_cross'));

        // Import des images cochées (téléchargées depuis la page source).
        list($importedImages, $failedImages) = $this->importImages($product, Tools::getValue('d_images'));

        // Lien vers la fiche produit standard du BO pour finaliser (publication, etc.).
        $productToken = Tools::getAdminTokenLite('AdminProducts');
        $productLink = 'index.php?controller=AdminProducts&id_product=' . (int) $product->id
            . '&updateproduct&token=' . $productToken;

        $msg = sprintf(
            $this->l('Produit #%d créé (inactif). '),
            (int) $product->id
        );
        if ($importedImages > 0) {
            $msg .= sprintf($this->l('%d image(s) importée(s). '), $importedImages);
        }
        if ($crossLinked > 0) {
            $msg .= sprintf($this->l('%d produit(s) lié(s) en « souvent achetés ensemble ». '), $crossLinked);
        }
        if ($failedImages > 0) {
            $msg .= sprintf($this->l('%d image(s) en échec (à ajouter manuellement). '), $failedImages);
        }
        $msg .= '<a href="' . htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8') . '" class="btn btn-default btn-xs">'
            . '<i class="icon-pencil"></i> ' . $this->l('Finaliser la fiche (images, publication…)') . '</a>';

        // PS9 : displayConfirmation() n'existe que sur Module, pas sur les contrôleurs
        // (contrairement à displayWarning) — on rend l'alerte succès nous-mêmes.
        return '<div class="alert alert-success">' . $msg . '</div>';
    }

    /**
     * Lie les produits « souvent achetés ensemble » (accessoires PrestaShop).
     * Ids revalidés : entiers, existants et actifs en base, différents du produit, 6 max.
     *
     * @param Product $product
     * @param mixed $ids valeur brute de d_cross[]
     *
     * @return int nombre de produits liés
     */
    protected function attachCrossSell(Product $product, $ids)
    {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $clean = [];
        foreach (array_slice($ids, 0, 6) as $id) {
            $id = (int) $id;
            if ($id > 0 && $id !== (int) $product->id && !in_array($id, $clean, true)
                && (int) Db::getInstance()->getValue('SELECT id_product FROM `' . _DB_PREFIX_ . 'product` WHERE id_product = ' . $id . ' AND active = 1')) {
                $clean[] = $id;
            }
        }
        if (empty($clean)) {
            return 0;
        }
        try {
            $product->changeAccessories($clean);
        } catch (Exception $e) {
            return 0;
        }

        return count($clean);
    }

    /**
     * Crée/rattache de vraies caractéristiques PrestaShop à partir du brouillon
     * (helpers d'import natifs : la caractéristique et sa valeur sont créées si
     * besoin, réutilisées sinon) + la caractéristique métier « Lidocaïne ».
     * Valeurs en mode custom : propres à ce produit, pas de pollution des listes.
     *
     * @param Product $product
     * @param array $caracs [['label'=>, 'valeur'=>], …]
     * @param bool $avecLidocaine
     */
    protected function attachFeatures(Product $product, array $caracs, $avecLidocaine)
    {
        $idLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $rows = [];
        foreach ($caracs as $c) {
            $label = isset($c['label']) ? trim((string) $c['label']) : '';
            $valeur = isset($c['valeur']) ? trim((string) $c['valeur']) : '';
            if ($label !== '' && $valeur !== '') {
                $rows[] = [$label, $valeur, true];
            }
        }
        // Critère métier filtrable : valeur partagée (non custom).
        $rows[] = [$this->l('Lidocaïne'), $avecLidocaine ? $this->l('Présente') : $this->l('Absente'), false];

        foreach ($rows as $row) {
            list($label, $valeur, $custom) = $row;
            try {
                $idFeature = (int) Feature::addFeatureImport(Tools::substr($label, 0, 128));
                if (!$idFeature) {
                    continue;
                }
                $idValue = (int) FeatureValue::addFeatureValueImport($idFeature, Tools::substr($valeur, 0, 255), (int) $product->id, $idLang, $custom);
                if ($idValue) {
                    Product::addFeatureProductImport((int) $product->id, $idFeature, $idValue);
                }
            } catch (Exception $e) {
                // Une caractéristique en échec ne remet pas en cause la création.
            }
        }
        Feature::cleanPositions();
    }

    /**
     * Persiste les contenus éditoriaux générés (points clés, FAQ, composition)
     * dans hfm_product_extra pour toutes les langues actives (contenu de la
     * langue de base recopié, comme les descriptions). Le bridge headless les
     * expose ensuite sur la fiche produit.
     *
     * @param int $idProduct
     * @param array $draft
     */
    protected function saveExtras($idProduct, array $draft)
    {
        $keyPoints = [];
        foreach ((isset($draft['points_cles']) && is_array($draft['points_cles'])) ? $draft['points_cles'] : [] as $p) {
            if (is_string($p) && trim($p) !== '') {
                $keyPoints[] = trim($p);
            }
        }
        $faq = [];
        foreach ((isset($draft['faq']) && is_array($draft['faq'])) ? $draft['faq'] : [] as $f) {
            $q = isset($f['question']) ? trim((string) $f['question']) : '';
            $a = isset($f['reponse']) ? trim((string) $f['reponse']) : '';
            if ($q !== '' && $a !== '') {
                $faq[] = ['q' => $q, 'a' => $a];
            }
        }
        $composition = [];
        foreach ((isset($draft['composition']) && is_array($draft['composition'])) ? $draft['composition'] : [] as $c) {
            $k = isset($c['label']) ? trim((string) $c['label']) : '';
            $v = isset($c['valeur']) ? trim((string) $c['valeur']) : '';
            if ($k !== '' && $v !== '') {
                $composition[] = ['k' => $k, 'v' => $v];
            }
        }

        if (empty($keyPoints) && empty($faq) && empty($composition)) {
            return;
        }

        $db = Db::getInstance();
        foreach (Language::getLanguages(true) as $lang) {
            $db->execute(
                'REPLACE INTO `' . _DB_PREFIX_ . 'hfm_product_extra` (id_product, id_lang, key_points, faq, composition) VALUES ('
                . (int) $idProduct . ', ' . (int) $lang['id_lang'] . ', '
                . '\'' . pSQL(json_encode($keyPoints, JSON_UNESCAPED_UNICODE), true) . '\', '
                . '\'' . pSQL(json_encode($faq, JSON_UNESCAPED_UNICODE), true) . '\', '
                . '\'' . pSQL(json_encode($composition, JSON_UNESCAPED_UNICODE), true) . '\')'
            );
        }
    }

    /**
     * Télécharge et rattache au produit les images cochées dans le brouillon.
     * Chaque URL est revalidée (absolue, hôte public) avant téléchargement par
     * ImageManager::copyImg() (génère toutes les déclinaisons). La première image
     * réussie devient la couverture. Aucun échec d'image ne remet en cause le produit.
     *
     * @param Product $product produit fraîchement créé
     * @param mixed $urls valeur brute de d_images[]
     *
     * @return array{0:int,1:int} [importées, en échec]
     */
    protected function importImages(Product $product, $urls)
    {
        if (!is_array($urls) || empty($urls)) {
            return [0, 0];
        }

        require_once dirname(__FILE__) . '/../../lib/HfmAiSourceScraper.php';
        $scraper = new HfmAiSourceScraper();

        $langs = Language::getLanguages(true);
        $legend = [];
        foreach ($langs as $lang) {
            $legend[(int) $lang['id_lang']] = Tools::substr((string) $product->name[(int) $lang['id_lang']], 0, 128);
        }

        $imported = 0;
        $failed = 0;
        $needCover = true;

        foreach (array_slice($urls, 0, 10) as $url) {
            $url = trim((string) $url);
            if ($url === '' || !Validate::isAbsoluteUrl($url)) {
                continue;
            }
            try {
                // Téléchargement par nos soins (user-agent navigateur + contrôle que
                // c'est une vraie image) : copyImg télécharge en client « robot » et
                // renvoie succès même quand le site a servi une page anti-bot.
                $tmp = tempnam(_PS_TMP_IMG_DIR_, 'hfmai');
                if (!$scraper->downloadImage($url, $tmp)) {
                    @unlink($tmp);
                    ++$failed;
                    continue;
                }

                $image = new Image();
                $image->id_product = (int) $product->id;
                $image->position = Image::getHighestPosition((int) $product->id) + 1;
                $image->cover = $needCover;
                $image->legend = $legend;
                if (!$image->add()) {
                    @unlink($tmp);
                    ++$failed;
                    continue;
                }
                $done = ImageManager::copyImg((int) $product->id, (int) $image->id, $tmp, 'products', true);
                @unlink($tmp);
                // copyImg ne vérifie pas ses redimensionnements : on contrôle le fichier final.
                if ($done && file_exists($image->getPathForCreation() . '.jpg')) {
                    ++$imported;
                    $needCover = false;
                } else {
                    $image->delete();
                    ++$failed;
                }
            } catch (Exception $e) {
                ++$failed;
            }
        }

        return [$imported, $failed];
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
