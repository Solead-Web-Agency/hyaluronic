<?php
/**
 * HFM AI Products — pré-remplissage IA de fiches produit (back-office)
 *
 * Depuis un formulaire simple (Nom + Référence + notes fournisseur + langue de base),
 * ce module appelle Claude (SDK Anthropic officiel) pour générer un BROUILLON de fiche
 * produit (descriptions, méta, caractéristiques, catégorie éditoriale, marque, prix
 * indicatif HT…). L'admin relit/édite le brouillon avant la création réelle dans le
 * catalogue : rien n'est publié automatiquement (produit créé inactif).
 *
 * Garde-fous métier (esthétique médicale B2B) : le prompt système interdit d'inventer
 * des indications ou allégations thérapeutiques ; en cas d'incertitude, le champ est
 * laissé vide et signalé. Cohérent avec le style de hfmstorefront.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HfmAiProducts extends Module
{
    public function __construct()
    {
        $this->name = 'hfmaiproducts';
        $this->tab = 'administration';
        $this->version = '1.4.0';
        $this->author = 'HFM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Produits IA');
        $this->description = $this->l('Pré-remplit une fiche produit via Claude à partir du nom, de la référence et de notes fournisseur ; l\'admin valide avant création.');
        $this->confirmUninstall = $this->l('Supprimer le module Produits IA ? La clé API et l\'onglet BO seront retirés.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Configuration par défaut : modèle Claude + clé API (vide au départ).
        Configuration::updateValue('HFMAIPRODUCTS_MODEL', 'claude-opus-4-8');
        if (Configuration::get('HFMAIPRODUCTS_API_KEY') === false) {
            Configuration::updateValue('HFMAIPRODUCTS_API_KEY', '');
        }

        return $this->installTab() && self::installExtraTable();
    }

    /**
     * Table des contenus éditoriaux générés par l'IA (points clés, FAQ, composition),
     * lue par le bridge headless (hfmstorefront) pour la fiche produit.
     */
    public static function installExtraTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hfm_product_extra` (
                `id_product` INT UNSIGNED NOT NULL,
                `id_lang` INT UNSIGNED NOT NULL,
                `key_points` TEXT,
                `faq` TEXT,
                `composition` TEXT,
                PRIMARY KEY (`id_product`, `id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    public function uninstall()
    {
        $this->uninstallTab();
        Configuration::deleteByName('HFMAIPRODUCTS_MODEL');
        Configuration::deleteByName('HFMAIPRODUCTS_API_KEY');

        return parent::uninstall();
    }

    /**
     * Installe l'onglet BO (contrôleur AdminHfmAiProducts) sous « Catalogue ».
     */
    protected function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminHfmAiProducts';
        $tab->module = $this->name;
        // id_parent = 0 -> entrée de PREMIER NIVEAU dans le menu BO (section principale
        // directement cliquable, au même niveau que « Vendre » / « Personnaliser »).
        $tab->id_parent = 0;
        $tab->active = 1;
        $tab->icon = 'smart_toy';

        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'Produits IA';
        }

        return $tab->add();
    }

    /**
     * Supprime l'onglet BO du module.
     */
    protected function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminHfmAiProducts');
        if ($idTab) {
            $tab = new Tab($idTab);

            return $tab->delete();
        }

        return true;
    }

    /**
     * Écran de configuration du module (HelperForm) :
     *  - clé API Anthropic (champ mot de passe, jamais réaffichée en clair),
     *  - modèle Claude (qualité vs éco).
     */
    public function getContent()
    {
        $out = '';

        if (Tools::isSubmit('submitHfmAiProductsConfig')) {
            // On ne réécrit la clé que si l'admin en a saisi une nouvelle
            // (le champ mot de passe est vidé à l'affichage pour ne pas exposer la clé).
            $newKey = (string) Tools::getValue('HFMAIPRODUCTS_API_KEY');
            if ($newKey !== '') {
                Configuration::updateValue('HFMAIPRODUCTS_API_KEY', trim($newKey));
            }

            $model = (string) Tools::getValue('HFMAIPRODUCTS_MODEL');
            $allowedModels = ['claude-opus-4-8', 'claude-haiku-4-5'];
            if (!in_array($model, $allowedModels, true)) {
                $model = 'claude-opus-4-8';
            }
            Configuration::updateValue('HFMAIPRODUCTS_MODEL', $model);

            $out .= $this->displayConfirmation($this->l('Paramètres enregistrés.'));
        }

        return $out . $this->renderConfigForm();
    }

    /**
     * Construit le formulaire de configuration via HelperForm.
     */
    protected function renderConfigForm()
    {
        $keySet = (string) Configuration::get('HFMAIPRODUCTS_API_KEY') !== '';

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration IA'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'password',
                        'label' => $this->l('Clé API Anthropic'),
                        'name' => 'HFMAIPRODUCTS_API_KEY',
                        'desc' => $keySet
                            ? $this->l('Une clé est déjà enregistrée. Laissez vide pour la conserver, ou saisissez-en une nouvelle pour la remplacer.')
                            : $this->l('Renseignez votre clé API Anthropic (sk-ant-...). Elle n\'est jamais affichée en clair.'),
                        'autocomplete' => 'off',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Modèle Claude'),
                        'name' => 'HFMAIPRODUCTS_MODEL',
                        'options' => [
                            'query' => [
                                ['id' => 'claude-opus-4-8', 'name' => $this->l('Opus 4.8 (qualité)')],
                                ['id' => 'claude-haiku-4-5', 'name' => $this->l('Haiku 4.5 (économique)')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Enregistrer'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHfmAiProductsConfig';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) $this->context->language->id;

        // Le champ mot de passe reste toujours vide : on n'expose jamais la clé.
        $helper->fields_value = [
            'HFMAIPRODUCTS_API_KEY' => '',
            'HFMAIPRODUCTS_MODEL' => (string) Configuration::get('HFMAIPRODUCTS_MODEL') ?: 'claude-opus-4-8',
        ];

        return $helper->generateForm([$fieldsForm]);
    }
}
