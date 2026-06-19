<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

require_once(dirname(__FILE__) . '/common.conf.php');

/* defines modules support product id */
define('_GMCP_SUPPORT_ID', '20908');

/* defines activate the BT support if false we use the ADDONS support url */
define('_GMCP_SUPPORT_BT', false);

/* defines activate the BT support if false we use the ADDONS support url */
define('_GMCP_SUPPORT_URL', 'https://addons.prestashop.com/');

/* defines admin library path */
define('_GMCP_PATH_LIB_ADMIN', _GMCP_PATH_LIB . 'admin/');

/* defines shopping action library path */
define('_GMCP_PATH_LIB_GSA', _GMCP_PATH_LIB . 'shopping-action/');

/* defines xml library path */
define('_GMCP_PATH_LIB_XML', _GMCP_PATH_LIB . 'xml/');

/* defines xml label path */
define('_GMCP_PATH_LIB_LABEL', _GMCP_PATH_LIB . 'labels/');

/* defines xml reviews path */
define('_GMCP_PATH_LIB_REVIEWS', _GMCP_PATH_LIB . 'reviews/');

/* defines xml library path */
define('_GMCP_PATH_LIB_EXCLUSION', _GMCP_PATH_LIB . 'exclusion/');

/* defines admin tpl path */
define('_GMCP_TPL_ADMIN_PATH', 'admin/');

/* defines header tpl */
define('_GMCP_TPL_HEADER', 'header.tpl');

/* defines top bar */
define('_GMCP_TPL_TOP', 'top.tpl');

/* defines top bar */
define('_GMCP_TPL_STEP_POPUP', 'step-popup.tpl');

/* defines welcome list settings tpl */
define('_GMCP_TPL_WELCOME', 'welcome-include.tpl');

/* defines template shopping action settings tpl */
define('_GMCP_TPL_GSA', 'shopping-action.tpl');

/* defines template shopping action onboarding tpl */
define('_GMCP_TPL_ONBOARDING', 'onboarding.tpl');

/* defines body tpl */
define('_GMCP_TPL_BODY', 'body.tpl');

/* defines template prerequisites settings tpl */
define('_GMCP_TPL_PREREQUISITES', 'prerequisites.tpl');

/* defines basics settings tpl */
define('_GMCP_TPL_BASICS', 'basics.tpl');

/* defines feed settings tpl */
define('_GMCP_TPL_FEED_SETTINGS', 'feed-settings.tpl');

/* defines google settings tpl */
define('_GMCP_TPL_GOOGLE_SETTINGS', 'google-settings.tpl');

/* defines google settings tpl */
define('_GMCP_TPL_ADVANCED_SETTINGS', 'advanced-settings.tpl');

/* defines iventory tpl */
define('_GMCP_TPL_INVENTORY_FEED', 'local-inventory-settings.tpl');

/* defines google category list tpl */
define('_GMCP_TPL_GOOGLE_CATEGORY_LIST', 'google-category-list.tpl');

/* defines google category popup tpl */
define('_GMCP_TPL_GOOGLE_CATEGORY_POPUP', 'google-category-popup.tpl');

/* defines google category update tpl */
define('_GMCP_TPL_GOOGLE_CATEGORY_UPD', 'google-category-update.tpl');

/* defines google custom label tpl */
define('_GMCP_TPL_GOOGLE_CUSTOM_LABEL', 'google-custom-label.tpl');

/* defines google custom label update tpl */
define('_GMCP_TPL_GOOGLE_CUSTOM_LABEL_UPD', 'google-custom-label-update.tpl');

/* defines google custom label tpl */
define('_GMCP_TPL_GOOGLE_CUSTOM_LABEL_PRODUCTS', 'google-custom-label-products.tpl');

/* defines feed list settings tpl */
define('_GMCP_TPL_FEED_LIST', 'feed-list.tpl');

/* defines feed list for lia settings tpl */
define('_GMCP_TPL_FEED_LIA_LIST', 'feed-lia-list.tpl');

/* defines reporting settings tpl */
define('_GMCP_TPL_REPORTING', 'reporting-settings.tpl');

/* defines feed generate action tpl */
define('_GMCP_TPL_FEED_GENERATE', 'feed-generate.tpl');

/* defines feed search product tpl */
define('_GMCP_TPL_PROD_SEARCH', 'product-search.tpl');

/* defines feed generate output tpl */
define('_GMCP_TPL_FEED_GENERATE_OUTPUT', 'feed-generate-output.tpl');

/* defines advanced tag category settings tpl */
define('_GMCP_TPL_ADVANCED_TAG_CATEGORY', 'advanced-tag-category.tpl');

/* defines advanced tag update tpl */
define('_GMCP_TPL_ADVANCED_TAG_UPD', 'advanced-tag-update.tpl');

/* defines reporting fancybox tpl */
define('_GMCP_TPL_REPORTING_BOX', 'reporting-box.tpl');

/* defines update sql file */
define('_GMCP_ADVANCED_1700_SQL_FILE', 'update-1700.sql');

/* defines update sql file */
define('_GMCP_ADVANCED_1712_SQL_FILE', 'update-1712.sql');

/* defines update sql file */
define('_GMCP_UPDATE_SQL_FILE', 'update.sql');

/* defines advanced tag update tpl */
define('_GMCP_TPL_GOOGLE_EXECLUSION_RULES', 'exclusion-rules.tpl');

/* defines exclusion-values tpl */
define('_GMCP_TPL_EXCLUSION_VALUES', 'exclusion-values.tpl');

/* defines rules summaray update tpl */
define('_GMCP_TPL_RULES_SUMMARY', 'rules-summary.tpl');

/* defines excluded-products tpl */
define('_GMCP_TPL_EXCLUDED_PRODUCTS', 'excluded-products.tpl');

/* defines advanced tag update tpl */
define('_GMCP_TPL_ATTRIBUTES_VALUES', 'attributes-values.tpl');

/* defines advanced tag update tpl */
define('_GMCP_TPL_EXCLUSION_RULES_UPD', 'confirm-exclusion-rules.tpl');

/** define the update sql for shop id with feature by cat */
define('_GMCP_FEATURE_SHOP_SQL_FILE', 'update-feature-shop.sql');

/** define the update sql for custom label dynamic tag */
define('_GMCP_CL_DYN_CAT_SHOP_SQL_FILE', 'update-cl-dynamic_tag.sql');

/** define the update sql for custom label dynamic tag */
define('_GMCP_DISCOUNT_ASSOC_CHANNEL', 'update-discount_assoc_channel.sql');

/* defines constant for external BT FAQ URL */
define('_GMCP_BT_FAQ_MAIN_URL', 'http://faq.businesstech.fr/');

/* defines constant for external Google taxonomy URL */
define('_GMCP_GOOGLE_TAXONOMY_URL', 'http://www.google.com/basepages/producttype/');

/* defines loader gif name */
define('_GMCP_LOADER_GIF', 'loader.gif');
define('_GMCP_LOADER_GIF_BIG', 'loader-bg.gif');

/* defines the reporting Directory */
define('_GMCP_REPORTING_DIR', _PS_MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/reporting/');

/* defines variable for sql update */
$GLOBALS['GMCP_SQL_UPDATE'] = array(
    'table' => array(
        '1700' => _GMCP_ADVANCED_1700_SQL_FILE,
        '1712' => _GMCP_ADVANCED_1712_SQL_FILE,
    ),
    'field' => array(
        'id_shop' => array('table' => 'features_by_cat', 'file' => _GMCP_FEATURE_SHOP_SQL_FILE),
        'id_shop' => array('table' => 'tags_dynamic_categories', 'file' => _GMCP_CL_DYN_CAT_SHOP_SQL_FILE),
        'channel' => array('table' => 'discount_association', 'file' => _GMCP_DISCOUNT_ASSOC_CHANNEL),
    )
);

/* defines variable for setting all request params : use for ajax request in to admin context */
$GLOBALS['GMCP_REQUEST_PARAMS'] = array(
    'basic' => array('action' => 'update', 'type' => 'basic'),
    'feed' => array('action' => 'update', 'type' => 'feed'),
    'gsa' => array('action' => 'update', 'type' => 'gsa'),
    'gsaOauth' => array('action' => 'update', 'type' => 'gsaOauth'),
    'feedDisplay' => array('action' => 'display', 'type' => 'feed'),
    'google' => array('action' => 'update', 'type' => 'google'),
    'feedList' => array('action' => 'display', 'type' => 'feedList'),
    'feedListUpdate' => array('action' => 'update', 'type' => 'feedList'),
    'reporting' => array('action' => 'update', 'type' => 'reporting'),
    'reportingBox' => array('action' => 'display', 'type' => 'reportingBox'),
    'tag' => array('action' => 'display', 'type' => 'tag'),
    'tagUpdate' => array('action' => 'update', 'type' => 'tag'),
    'googleCat' => array('action' => 'display', 'type' => 'googleCategories'),
    'googleCatUpdate' => array('action' => 'update', 'type' => 'googleCategoriesMatching'),
    'googleCatSync' => array('action' => 'update', 'type' => 'googleCategoriesSync'),
    'custom' => array('action' => 'display', 'type' => 'customLabel'),
    'customUpdate' => array('action' => 'update', 'type' => 'label'),
    'customDelete' => array('action' => 'delete', 'type' => 'label'),
    'customActivate' => array('action' => 'update', 'type' => 'labelState'),
    'autocomplete' => array('action' => 'display', 'type' => 'autocomplete'),
    'dataFeed' => array('action' => 'update', 'type' => 'xml'),
    'advancedfeed' => array('action' => 'update', 'type' => 'advancedfeed'),
    'discount' => array('action' => 'update', 'type' => 'discount'),
    'position' => array('action' => 'update', 'type' => 'position'),
    'checkDate' => array('action' => 'update', 'type' => 'customLabelDate'),
    'customProduct' => array('action' => 'display', 'type' => 'customLabelProduct'),
    'searchProduct' => array('action' => 'display', 'type' => 'searchProduct'),
    'exclusionRule' => array('action' => 'display', 'type' => 'exclusionRule'),
    'exclusionRuleDelete' => array('action' => 'delete', 'type' => 'exclusionRule'),
    'rulesSummary' => array('action' => 'display', 'type' => 'rulesSummary'),
    'rulesList' => array('action' => 'update', 'type' => 'rulesList'),
    'exclusionRuleForm' => array('action' => 'update', 'type' => 'exclusionRule'),
    'excludeValue' => array('action' => 'display', 'type' => 'excludeValue'),
    'rulesActivate' => array('action' => 'update', 'type' => 'rulesActivate'),
    'exclusionRuleProducts' => array('action' => 'display', 'type' => 'exclusionRuleProducts'),
    'stepPopup' => array('action' => 'display', 'type' => 'stepPopup'),
    'stepPopupUpd' => array('action' => 'update', 'type' => 'stepPopup'),
    'shopLink' => array('action' => 'update', 'type' => 'shopLink'),
    'local_inventory' => array('action' => 'update', 'type' => 'inventory'),
);

/* defines variable for available list of tags to use */
$GLOBALS['GMCP_TAG_LIST'] = array(
    'material',
    'pattern',
    'agegroup',
    'gender',
    'adult',
    'sizeType',
    'sizeSystem',
    'energy',
    'energy_min',
    'energy_max',
    'shipping_label',
    'unit_pricing_measure',
    'base_unit_pricing_measure',
    'excluded_destination',
    'excluded_country'
);

/* defines variable for available list of label to use */
$GLOBALS['GMCP_LABEL_LIST'] = array(
    'cats' => 'category',
    'brands' => 'brand',
    'suppliers' => 'supplier',
    'dynamic_best_sale' => 'dynamic_best_sale',
    'dynamic_features' => 'dynamic_features',
    'dynamic_new_product' => 'dynamic_new_product',
    'price_range' => 'price_range',
);

/* defines variable for available list of label to use */
$GLOBALS['GMCP_PARAM_FOR_XML'] = array(
    'iShopId',
    'sFilename',
    'iLangId',
    'sLangIso',
    'sCountryIso',
    'sCurrencyIso',
    'iFloor',
    'iStep',
    'iTotal',
    'iProcess',
    'sFeedType',
    'bExcludedProduct'
);

/* defines variable for available discount type */
$GLOBALS['GMCP_DATA_FEED_TYPE'] = array('product', 'discount', 'reviews');

/* defines variable for available custom_label type */
$GLOBALS['GMCP_CUSTOM_LABEL_TYPE'] = array(
    'en' => array(
        'custom_label' => 'Basic',
        'dynamic_categorie' => 'Categories (Dynamic mode)',
        'dynamic_features_list' => 'Features (Dynamic mode)',
        'dynamic_new_product' => 'New product (Dynamic mode)',
        'dynamic_best_sale' => 'Best sales (Dynamic mode)',
        'dynamic_price_range' => 'Price range (Dynamic mode)',
        'dynamic_last_order' => 'Last product ordered (Dynamic mode)',
        'dynamic_promotion' => 'Product promotion (Dynamic mode)'
    ),
    'fr' => array(
        'custom_label' => 'Basique',
        'dynamic_categorie' => 'Catégories (mode dynamique)',
        'dynamic_features_list' => 'Caractéristiques (mode dynamique)',
        'dynamic_new_product' => 'Nouveaux produits (mode dynamique)',
        'dynamic_best_sale' => 'Meilleures ventes (mode dynamique)',
        'dynamic_price_range' => 'Tranche de prix (mode dynamique)',
        'dynamic_last_order' => 'Derniers produits commandés (mode dynamique)',
        'dynamic_promotion' => 'Produits en promotion (mode dynamique)'
    ),
    'it' => array(
        'custom_label' => 'Di base',
        'dynamic_categorie' => 'Categorie (dinamica di modo)',
        'dynamic_features_list' => 'Caratteristiche (modalità dinamica)',
        'dynamic_new_product' => 'Nuovo prodotto (modalità dinamica)',
        'dynamic_best_sale' => 'Le migliori vendite (modalità dinamica)',
        'dynamic_price_range' => 'Fascia di prezzo (modalità dinamica)',
        'dynamic_last_order' => 'Ultimo prodotto ordinato (modalità dinamica)',
        'dynamic_promotion' => 'Promozione del prodotto (modalità dinamica)'
    ),
    'es' => array(
        'custom_label' => 'Básica',
        'dynamic_categorie' => 'Categorías (modo dinámico)',
        'dynamic_features_list' => 'Atributos (modo dinámico)',
        'dynamic_new_product' => 'Nuevo producto (modo dinámico)',
        'dynamic_best_sale' => 'Las mejores ventas (modo dinámico)',
        'dynamic_price_range' => 'Rango de precios (modo dinámico)',
        'dynamic_last_order' => 'Último producto pedido (modo dinámico)',
        'dynamic_promotion' => 'La promoción del producto (modo dinámico)',
    ),
);

/* defines variable to define default message for custom label with no product translations */
$GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'] = array(
    'en' => 'There is no product for this custom label configuration. We invite you to edit it the custom label from the list by closing the windows',
    'fr' => 'Il n y\'a pas de produits associés à la configuration du custom label. Vous pouvez l\'éditer depuis la liste en fermant cette fenêtre',
    'it' => 'Non esiste alcun prodotto per questa configurazione di etichetta personalizzata. Ti invitiamo a modificare l\'etichetta personalizzata dall\'elenco chiudendo le finestre',
    'es' => 'No hay ningún producto para esta configuración de etiqueta personalizada. Le invitamos a editar la etiqueta personalizada de la lista cerrando las ventanas',
);

/* defines variable for available unit type in best sale */
$GLOBALS['GMCP_CUSTOM_LABEL_BEST_TYPE'] = array(
    'en' => array('unit' => 'Unit', 'Items sold' => 'Revenue generated'),
    'fr' => array('unit' => 'Entités vendues', 'price' => 'Chiffre d\'affaire généré'),
    'it' => array('unit' => 'Articoli venduti', 'price' => 'Ricavi generati'),
    'es' => array('unit' => 'Cosas vendidas', 'price' => 'Ingresos generados'),
);

/* defines variable for available unit type in best sale */
$GLOBALS['GMCP_CUSTOM_LABEL_BEST_PERIOD_TYPE'] = array(
    'period' => 'Period',
    'days' => 'For X lasts days'
);

/* defines variable for filter for CL products */
$GLOBALS['GMCP_CUSTOM_LABEL_PRODUCT_FILTER'] = array(
    'category' => array(
        'sFieldSelect' => 'id_category',
        'sPopulateTable' => 'gmcp_tags_cats',
        'bUsePsTable' => 1,
        'bUseCategory' => 1,
        'sPsTable' => 'category_product',
        'sPsTableWhere' => 'id_category',
    ),
    'brand' => array(
        'sFieldSelect' => 'id_brand',
        'sPopulateTable' => 'gmcp_tags_brands',
        'bUsePsTable' => 1,
        'sPsTable' => 'product',
        'sPsTableWhere' => 'id_manufacturer',
    ),
    'product' => array(
        'sFieldSelect' => 'id_product',
        'sPopulateTable' => 'gmcp_tags_products',
        'bUsePsTable' => 0,
        'sPsTable' => '',
        'sPsTableWhere' => '',
    ),
    'dyn_cat' => array(
        'sFieldSelect' => 'id_category',
        'sPopulateTable' => 'gmcp_tags_dynamic_categories',
        'bUsePsTable' => 1,
        'sPsTable' => 'category_product',
        'sPsTableWhere' => 'id_category',
    ),
    'dyn_feature' => array(
        'sFieldSelect' => 'id_feature',
        'sPopulateTable' => 'gmcp_tags_dynamic_features',
        'bUsePsTable' => 1,
        'sPsTable' => 'feature_product',
        'sPsTableWhere' => 'id_feature',
    ),
    'dyn_new_product' => array(
        'sFieldSelect' => 'id_product',
        'sPopulateTable' => 'gmcp_tags_dynamic_new_product',
        'bUsePsTable' => 0,
        'sPsTable' => '',
        'sPsTableWhere' => '',
    ),
    'dyn_best_dale' => array(
        'sFieldSelect' => 'id_product',
        'sPopulateTable' => 'gmcp_tags_dynamic_best_sale',
        'bUsePsTable' => 0,
        'sPsTable' => '',
        'sPsTableWhere' => '',
    ),
    'dyn_price_range' => array(
        'sFieldSelect' => 'id_product',
        'sPopulateTable' => 'gmcp_tags_price_range',
        'bUsePsTable' => 0,
        'sPsTable' => '',
        'sPsTableWhere' => '',
    ),
);

// make the matching from the GMC module to GMCP module
$GLOBALS['GMCP_IMPORT_TABLE_GMC'] = array(
    'brands' => array(
        'newTable' => 'gmcp_brands',
        'oldTable' => 'gmc_brands',
    ),
    'categories' => array(
        'newTable' => 'gmcp_categories',
        'oldTable' => 'gmc_categories',
    ),
    'features' => array(
        'newTable' => 'gmcp_features_by_cat',
        'oldTable' => 'gmc_features_by_cat',
    ),
    'taxonomy' => array(
        'newTable' => 'gmcp_taxonomy',
        'oldTable' => 'gmc_taxonomy',
    ),
    'taxonomy_cat' => array(
        'newTable' => 'gmcp_taxonomy_categories',
        'oldTable' => 'gmc_taxonomy_categories',
    ),
);
/* defines variable for available list of label to use for type of exlusion*/
$GLOBALS['GMCP_RULES_LABEL_TYPE'] = array(
    'supplier' => array(
        'en' => 'Based on manufacturer',
        'es' => 'Based on manufacturer',
        'it' => 'Based on manufacturer',
        'fr' => 'Basé sur des fournisseurs'
    ),
    'word' => array(
        'en' => 'Based on words',
        'es' => 'Based on words',
        'it' => 'Based on words',
        'fr' => 'Basé sur des mots'
    ),
    'feature' => array(
        'en' => 'Based on feature',
        'es' => 'Based on feature',
        'it' => 'Based on feature',
        'fr' => 'Basé sur des caractéristiques'
    ),
    'attribute' => array(
        'en' => 'Based on attributes',
        'es' => 'Based on attributes',
        'it' => 'Based on attributes',
        'fr' => 'Basé sur des attributs'
    ),
    'specificProduct' => array(
        'en' => 'Specific product',
        'es' => 'Produits Specifiques',
        'it' => 'BSpecific product',
        'fr' => 'Specific product'
    ),


);

/* defines variable for available list of label to use */
$GLOBALS['GMCP_RULES_WORD_TYPE'] = array(
    'title' => array(
        'en' => 'Product title',
        'es' => 'Product title',
        'it' => 'Product title',
        'fr' => 'Nom du produit'
    ),
    'description' => array(
        'en' => 'Description',
        'es' => 'Description',
        'it' => 'Description',
        'fr' => 'Description'
    ),
    'both' => array(
        'en' => 'Product name + description',
        'es' => 'Product name + description',
        'it' => 'Product name + description',
        'fr' => 'Nom du produit + description'
    ),
);

/* defines variable to set request parameters */
$GLOBALS['GMCP_EXCLUSION_TYPE'] = array(
    'supplier' => array(
        'en' => 'suppliers',
        'fr' => 'des fournisseurs',
        'es' => 'proveedores',
        'it' => 'fornitori'
    ),
    'word' => array(
        'en' => 'a word or a sequence of words',
        'fr' => 'un mot ou une suite de mots',
        'es' => 'una palabra o una secuencia de palabras',
        'it' => 'una parola o una sequenza di parole'
    ),
    'feature' => array(
        'en' => 'a feature',
        'fr' => 'une caractéristique',
        'es' => 'una característica',
        'it' => 'una caratteristica'
    ),
    'attribute' => array(
        'en' => 'an attribute',
        'fr' => 'un attribut',
        'es' => 'un atributo',
        'it' => 'un attributo'
    ),
    'specificProduct' => array(
        'en' => 'a specific product or combination',
        'fr' => 'un produit ou une déclinaison spécifique',
        'es' => 'un producto o una combinación específico/a',
        'it' => 'un prodotto o una combinazione specifico/a'
    ),
);

/* defines variable to set request parameters */
$GLOBALS['GMCP_EXCLUSION_TYPE_WORD'] = array(
    'title' => array(
        'en' => 'Product title',
        'fr' => 'Titre du produit',
        'es' => 'Product title',
        'it' => 'Product title'
    ),
    'description' => array(
        'en' => 'Product description',
        'fr' => 'Description du produit',
        'es' => 'Product description',
        'it' => 'Product description'
    ),
    'both' => array(
        'en' => 'Title and description',
        'fr' => 'Titre et description',
        'es' => 'Title and description',
        'it' => 'Title and description'
    ),
);
/* defines variable to get the real value for excluded destination tag */
$GLOBALS['GMCP_EXCLUDED_DEST_VALUE'] = array(
    'shopping' => 'Shopping Ads',
    'actions' => 'Buy on Google listings',
    'display' => 'Display Ads',
    'local' => 'Local inventory ads',
    'free-listing' => 'Free listings',
    'free-local-listing' => 'Free local listings',
);

/* defines lang we have to remove on offer id for GSA */
$GLOBALS['GMCP_LANG_TO_REMOVED_OFFERID'] = array(
    'FR', 'EN', 'US', 'GB', 'DE', 'IT', 'NL', 'ES', 'MX', 'ZA', 'CA', 'JA',
    'BR', 'CR', 'RU', 'SV', 'DA', 'NO', 'PL', 'TR', 'MS', 'PT', 'AR', 'ID',
    'HE', 'VN', 'UK', 'SV', 'TH', 'KO', 'FI', 'HU', 'AG', 'UR', 'VE', 'SK', 'RO', 'EI', 'LT'
);

/* defines GSA carrier available data */
$GLOBALS['GMCP_GSA_CARRIERS_DATA'] = array(
    'ups', 'usps', 'fedex', 'dhl', 'ontrac', 'dhl express', 'deliv', 'dynamex', 'lasership', 'mpx', 'uds', 'efw',
    'jd logistics', 'yunexpress', 'china post', 'china ems', 'singapore post', 'pos malaysia', 'postnl', 'ptt', 'eub', 'chukou1', 'la poste', 'colissimo',
    'chronopost', 'gls', 'dpd', 'bpost', 'colis prive', 'boxtal', 'geodis', 'tnt', 'db schenker'
);

/* defines promotion feed channel */
$GLOBALS['GMCP_DISCOUNT_CHANNEL'] = array(
    'Shopping ads',
    'Buy on Google listings',
    'Free listings',
);

/* defines google_funded_promotion_eligibility values */
$GLOBALS['GMCP_FUNDED_PROMO'] = array('none','all');
