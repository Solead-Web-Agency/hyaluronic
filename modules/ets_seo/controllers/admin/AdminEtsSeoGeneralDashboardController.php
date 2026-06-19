<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsSeoGeneralDashboardController extends ModuleAdminController
{
    public $pageTypes;
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $this->fields_options = array(
            'dashboard' => array(
                'title' => $this->l('Dashboard'),
                'info' => $this->l('Coming soon...'),
                'fields' => array(),
                'icon' => '',
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }

        $this->pageTypes = array(
            'product' => $this->l('Product pages'),
            'category' => $this->l('Product category pages'),
            'cms' => $this->l('CMS pages'),
            'cms_category' => $this->l('CMS category pages'),
            'manufacturer' => $this->l('Brand pages'),
            'supplier' => $this->l('Supplier pages'),
            'meta' => $this->l('Other pages'),
        );
    }

    public function renderOptions()
    {
        $totalIndexFollow = $this->getTotalIndexFollow();
        $totalMetaIndex = $this->getTotalMetaIndex();
        $chart_index = array();
        if((int)$totalIndexFollow['index'] || (int)$totalIndexFollow['noindex'])
        {
            $chart_index = array(
                array(
                    'label' => $this->l('Index'),
                    'value' => $totalIndexFollow['index'],
                ),
                array(
                    'label' => $this->l('No index'),
                    'value' => $totalIndexFollow['noindex'],
                )
            );
        }

        $chart_follow = array();
        if((int)$totalIndexFollow['follow'] || (int)$totalIndexFollow['nofollow']) {
            $chart_follow = array(
                array(
                    'label' => $this->l('Follow'),
                    'value' => $totalIndexFollow['follow'],
                ),
                array(
                    'label' => $this->l('No follow'),
                    'value' => $totalIndexFollow['nofollow'],
                )
            );
        }
        $seo_score = array(
            'bad' => $totalIndexFollow['seo_score_bad'],
            'na' => $totalIndexFollow['seo_score_na'],
            'good' => $totalIndexFollow['seo_score_good'],
            'noanalysis' => $totalMetaIndex['noanalysis'],
        );
        $readability_score = array(
            'bad' => $totalIndexFollow['readability_score_bad'],
            'na' => $totalIndexFollow['readability_score_na'],
            'good' => $totalIndexFollow['readability_score_good'],
        );
        $meta_data = array(
            array(
                'label' => $this->l('Completed'),
                'value' => $totalMetaIndex['hasmeta'],
            ),
            array(
                'label' => $this->l('Not completed'),
                'value' => $totalMetaIndex['nometa'],
            )
        );

        $page_analysis = $this->pageAnalysis();

        foreach ($page_analysis as $pk=> &$item)
        {
            foreach ($item as &$page)
            {
                foreach ($this->pageTypes as $key => $type)
                {
                    $page['values'][] = array(
                        'label' => $type,
                        'type' => $key,
                        'value' => $page['type'] !== 'noanalysis' ? $totalIndexFollow['pages'][$key][$pk][$page['type']] : $totalMetaIndex['pages'][$key][$pk]['noanalysis']
                    );
                }
            }
        }
        $this->context->smarty->assign(
            array(
                'ets_seo_link_dashboard_js' => __PS_BASE_URI__.'modules/'.$this->module->name.'/views/js/dashboard.js',
                'ets_seo_data_dashboard' => array(
                    'chart_index' => $chart_index,
                    'chart_follow' => $chart_follow,
                    'total_index' =>  $totalIndexFollow['index'],
                    'total_noindex' =>  $totalIndexFollow['noindex'],
                    'total_follow' =>  $totalIndexFollow['follow'],
                    'total_nofollow' =>  $totalIndexFollow['nofollow'],
                    'seo_score' => $seo_score,
                    'readability_score' => $readability_score,
                    'meta_data' => $meta_data,
                    'chart_page_analytics' => $page_analysis
                ),
                'ets_seo_checklist' => $this->getSeoChecklist(),
                'multi_lang_enable' => Language::isMultiLanguageActivated(),
                'txt_multilang' => $this->l('These numbers of web page have been multiplied with the number of available languages due to your multi-language mode.'),
            )
        );
        return $this->module->renderDashboardView();
    }

    public static function updateKeyphrase()
    {
        $products = Db::getInstance()->executeS("
                SELECT pl.id_product, pl.id_lang,pl.id_shop, pl.name FROM `"._DB_PREFIX_."product_lang` pl
                LEFT JOIN `"._DB_PREFIX_."ets_seo_product` sp ON sp.id_product=pl.id_product AND sp.id_lang=pl.id_lang AND sp.id_shop=pl.id_shop
                LEFT JOIN `"._DB_PREFIX_."product` p ON p.id_product=pl.id_product
                WHERE p.active=1 AND (sp.key_phrase IS NULL OR sp.key_phrase = '')");
        foreach ($products as $p){
            if(!Db::getInstance()->getValue("SELECT id_product FROM `"._DB_PREFIX_."ets_seo_product` WHERE id_product = ".(int)$p['id_product']." AND id_lang=".(int)$p['id_lang']." AND id_shop=".(int)$p['id_shop'])){
                Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."ets_seo_product`(id_product, id_lang,id_shop, key_phrase,allow_search,allow_flw_link) VALUES(".(int)$p['id_product'].",".(int)$p['id_lang'].",".(int)$p['id_shop'].",'".pSQL($p['name'])."',1,1)");
            }
            else{
                Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."ets_seo_product` SET key_phrase='".pSQL($p['name'])."' WHERE id_product=".(int)$p['id_product']." AND id_lang=".(int)$p['id_lang']);
            }
        }
    }


    protected function getTotalMetaIndex()
    {
        $sql = array();
        $sql['product'] = "SELECT 
                        SUM(IF(b.id_product IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END ) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."product_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_product` b ON (a.id_product = b.id_product AND b.id_lang=a.id_lang)";
        $sql['category'] = "SELECT 
                        SUM(IF(b.id_category IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."category_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_category` b ON (a.id_category = b.id_category AND b.id_lang=a.id_lang)";
        $sql['cms'] = "SELECT 
                        SUM(IF(b.id_cms IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."cms_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_cms` b ON (a.id_cms = b.id_cms AND b.id_lang=a.id_lang)";
        $sql['cms_category'] = "SELECT 
                        SUM(IF(b.id_cms_category IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."cms_category_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_cms_category` b ON (a.id_cms_category = b.id_cms_category AND b.id_lang=a.id_lang)";
        $sql['meta'] = "SELECT 
                        SUM(IF(b.id_meta IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.title IS NULL OR a.title = '' OR a.description IS NULL OR a.description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."meta_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_meta` b ON (a.id_meta = b.id_meta AND b.id_lang=a.id_lang)";
        $sql['manufacturer'] = "SELECT 
                        SUM(IF(b.id_manufacturer IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."manufacturer_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_manufacturer` b ON (a.id_manufacturer = b.id_manufacturer AND b.id_lang=a.id_lang)";
        $sql['supplier'] = "SELECT 
                        SUM(IF(b.id_supplier IS NULL, 1, 0)) as noanalysis,
                        SUM(CASE WHEN a.meta_title IS NULL OR a.meta_title = '' OR a.meta_description IS NULL OR a.meta_description = '' THEN 1 ELSE 0 END) as nometa,
                        COUNT(*) as total
                        FROM `"._DB_PREFIX_."supplier_lang` a
                        LEFT JOIN `"._DB_PREFIX_."ets_seo_supplier` b ON (a.id_supplier = b.id_supplier AND b.id_lang=a.id_lang)";

        $result = array(
            'noanalysis' => 0,
            'noanalysis_pages' => array(),
            'nometa' => 0,
            'hasmeta' => 0,
            'pages' => array()
        );
        foreach($sql as $k=>$s) {
            $data = Db::getInstance()->getRow($s);
            $result['noanalysis'] += (int)$data['noanalysis'];
            $result['nometa'] += (int)$data['nometa'];
            $result['hasmeta'] += ((int)$data['total'] - (int)$data['nometa']);
            $result['pages'][$k] = array(
                'readability_score' => array(
                    'noanalysis' => (int)$data['noanalysis']
                ),
                'seo_score' => array(
                    'noanalysis' => (int)$data['noanalysis']
                )
            );
        }

        return $result;

    }

    protected function getTotalIndexFollow()
    {
        $sqlOverAllSeoScore = " (b.`seo_score` / (".ETS_TOTAL_SEO_RULE_SCORE." * 9) * 10)";
        $sqlOverAllReadabilityScore = " (b.`readability_score` / (".ETS_TOTAL_READABILITY_RULE_SCORE." * 9) * 10)";
        $languages = Language::getLanguages(true);
        $firstLangId = isset($languages[0]['id_lang']) ? $languages[0]['id_lang'] : Configuration::get('PS_LANG_DEFAULT');
        $sql = array();
        $sql['product'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0 THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."product` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_product` b ON a.id_product = b.id_product AND b.id_lang=".(int)$firstLangId;
        $sql['category'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL  THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."category` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_category` b ON a.id_category = b.id_category AND b.id_lang=".(int)$firstLangId;
        $sql['cms'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."cms` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_cms` b ON a.id_cms = b.id_cms AND b.id_lang=".(int)$firstLangId;
        $sql['cms_category'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."cms_category` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_cms_category` b ON a.id_cms_category = b.id_cms_category AND b.id_lang=".(int)$firstLangId;
        $sql['meta'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_META_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_META_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_META_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."meta` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_meta` b ON a.id_meta = b.id_meta AND b.id_lang=".(int)$firstLangId;
        $sql['manufacturer'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."manufacturer` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_manufacturer` b ON a.id_manufacturer = b.id_manufacturer AND b.id_lang=".(int)$firstLangId;
        $sql['supplier'] = "SELECT 
                    SUM(CASE WHEN b.allow_search = 1 OR (b.allow_search = 2 AND 1 = ".(int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT').") OR (b.allow_search IS NULL AND 1 = ".(int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as hasindex, 
                    SUM(CASE WHEN b.allow_search = 0 OR (b.allow_search = 2 AND 0 = ".(int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT').") THEN 1 ELSE 0 END) as noindex, 
                    SUM(CASE WHEN b.allow_flw_link = 1 OR b.allow_flw_link IS NULL THEN 1 ELSE 0 END) as follow, 
                    SUM(CASE WHEN b.allow_flw_link = 0  THEN 1 ELSE 0 END) as nofollow,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." <= 4 THEN 1 END) as seo_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 4 AND ".(string)$sqlOverAllSeoScore." <= 7 THEN 1 END) as seo_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllSeoScore." > 7 THEN 1 END) as seo_score_good,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." <= 4 THEN 1 END) as readability_score_bad,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 4 AND ".(string)$sqlOverAllReadabilityScore." <= 7 THEN 1 END) as readability_score_na,
                    SUM(CASE WHEN ".(string)$sqlOverAllReadabilityScore." > 7 THEN 1 END) as readability_score_good
                    FROM `"._DB_PREFIX_."supplier` a
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_supplier` b ON a.id_supplier = b.id_supplier AND b.id_lang=".(int)$firstLangId;


        $result = array(
            'index' => 0,
            'noindex' => 0,
            'follow' => 0,
            'nofollow' => 0,
            'seo_score_bad' => 0,
            'seo_score_na' => 0,
            'seo_score_good' => 0,
            'readability_score_bad' => 0,
            'readability_score_na' => 0,
            'readability_score_good' => 0,
            'pages' => array(),
        );
        foreach ($sql as $k=> $s)
        {
            $item = Db::getInstance()->getRow($s);
            $result['index'] += (int)$item['hasindex'];
            $result['noindex'] += (int)$item['noindex'];
            $result['follow'] += (int)$item['follow'];
            $result['nofollow'] += (int)$item['nofollow'];
            $result['seo_score_bad'] += (int)$item['seo_score_bad'];
            $result['seo_score_na'] += (int)$item['seo_score_na'];
            $result['seo_score_good'] += (int)$item['seo_score_good'];
            $result['readability_score_bad'] += (int)$item['readability_score_bad'];
            $result['readability_score_na'] += (int)$item['readability_score_na'];
            $result['readability_score_good'] += (int)$item['readability_score_good'];
            $result['pages'][$k] = array(
                'readability_score' => array(
                    'bad' => (int)$item['readability_score_bad'],
                    'na' => (int)$item['readability_score_na'],
                    'good' => (int)$item['readability_score_good'],
                ),
                'seo_score' => array(
                    'bad' => (int)$item['seo_score_bad'],
                    'na' => (int)$item['seo_score_na'],
                    'good' => (int)$item['seo_score_good'],
                ),
            );
        }

        return $result;
    }

    protected function pageAnalysis()
    {
        return array(
            'readability_score' => array(
                array(
                    'key' => $this->l('Excellent'),
                    'color' => '#32C020',
                    'type' => 'good',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('Acceptable'),
                    'color' => '#FF8E37',
                    'type' => 'na',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('Not good'),
                    'color' => '#FF5D5E',
                    'type' => 'bad',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('No analysis'),
                    'color' => '#6C868E',
                    'type' => 'noanalysis',
                    'values'=> array()
                ),
            ),
            'seo_score' => array(
                array(
                    'key' => $this->l('Excellent'),
                    'color' => '#32C020',
                    'type' => 'good',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('Acceptable'),
                    'color' => '#FF8E37',
                    'type' => 'na',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('Not good'),
                    'color' => '#FF5D5E',
                    'type' => 'bad',
                    'values'=> array()
                ),
                array(
                    'key' => $this->l('No analysis'),
                    'color' => '#6C868E',
                    'type' => 'noanalysis',
                    'values'=> array()
                ),
            ),
        );
    }

    protected function getSeoChecklist()
    {
        $webMasterToolsStatus = Configuration::get('ETS_SEO_BAIDU_VERIFY_CODE')
            || Configuration::get('ETS_SEO_BING_VERIFY_CODE')
            || Configuration::get('ETS_SEO_GOOGLE_VERIFY_CODE')
            || Configuration::get('ETS_SEO_YANDEX_VERIFY_CODE')
            || (int)Configuration::get('ETS_SEO_VERIFIED_BY_USING_OTHER_METHODS');

        $robotFileGenerated = file_exists(_PS_ROOT_DIR_.'/robots.txt') || file_exists(_PS_ROOT_DIR_.'/_robots.txt');
        return array(
            array(
                'title'=> $this->l('Enable friendly URL'),
                'status' => (int)Configuration::get('PS_REWRITING_SETTINGS') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminMeta', true)
            ),
            array(
                'title'=> $this->l('Remove ID (numbers) in URL'),
                'status' => (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminMeta', true)
            ),
            array(
                'title'=> $this->l('Enable old link to new link redirects'),
                'status' => (int)Configuration::get('ETS_SEO_ENABLE_REDRECT_NOTFOUND') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminMeta', true),
                'hide' => (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL') ? 0 : 1,
            ),
            array(
                'title'=> $this->l('Enable SSL (HTTPS) on all pages'),
                'status' => (int)Configuration::get('PS_SSL_ENABLED') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminPreferences', true)
            ),
            array(
                'title'=> $this->l('Enable RSS'),
                'status' => (int)Configuration::get('ETS_SEO_RSS_ENABLE') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceRSS', true)
            ),
            array(
                'title'=> $this->l('Enable sitemap'),
                'status' => (int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceSitemap', true)
            ),
            array(
                'title'=> $this->l('Verify site ownership on webmaster tools'),
                'status' => $webMasterToolsStatus,
                'link' => $this->context->link->getAdminLink('AdminEtsSeoSearchAppearanceGeneral', true)
            ),
            array(
                'title'=> $this->l('robots.txt file created'),
                'status' => $robotFileGenerated,
                'link' => $this->context->link->getAdminLink('AdminEtsSeoFileEditor', true)
            ),
            array(
                'title'=> $this->l('Debug mode is off'),
                'status' => _PS_MODE_DEV_ ? 0 : 1,
                'link' => $this->context->link->getAdminLink('AdminPerformance', true),
            ),
            array(
                'title'=> $this->l('Maintenance mode is off '),
                'status' => (int)Configuration::get('PS_SHOP_ENABLE') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminMaintenance', true),
            ),
            array(
                'title'=> $this->l('Cache is enabled'),
                'status' => (int)Configuration::get('PS_SMARTY_CACHE') ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminPerformance', true),
            ),
            array(
                'title'=> $this->l('Template compilation is set to "Never"'),
                'status' => (int)Configuration::get('PS_SMARTY_FORCE_COMPILE') == 0 ? 1 : 0,
                'link' => $this->context->link->getAdminLink('AdminPerformance', true),
            ),
            array(
                'title'=> $this->l('Enable Super Speed - The best speed optimization module'),
                'status' => Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed') ? 1 : 0,
                'is_module' => true,
                'is_installed' => Module::isInstalled('ets_superspeed') ? true : false,
                'link' => !Module::isInstalled('ets_superspeed') ? 'https://addons.prestashop.com/en/website-performance/44977-super-speed-incredibly-fast-gtmetrix-optimization.html' : (!Module::isEnabled('ets_superspeed') ? $this->context->link->getAdminLink('AdminModulesManage') : $this->context->link->getAdminLink('AdminModules').'&configure=ets_superspeed&module_name=ets_superspeed'),
            ),
            array(
                'title'=> $this->l('Enable BLOG - The best blog module for Prestashop'),
                'status' => Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog') ? 1 : 0,
                'is_module' => true,
                'is_installed' => Module::isInstalled('ybc_blog') ? true : false,
                'link' => !Module::isInstalled('ybc_blog') ? 'https://addons.prestashop.com/en/blog-forum-new/25908-blog.html' : (!Module::isEnabled('ybc_blog') ? $this->context->link->getAdminLink('AdminModulesManage') : $this->context->link->getAdminLink('AdminModules').'&configure=ybc_blog&module_name=ybc_blog'),
            ),
        );
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::isSubmit('etsSeoGetAnalysisModal')){
            $totalMetaIndex = $this->getTotalMetaIndex();
            $page_analysis = $this->pageAnalysis();
            $dataNoAnalysis = array();
            foreach ($page_analysis as $pk=> &$item)
            {
                foreach ($item as &$page)
                {
                    foreach ($this->pageTypes as $key => $type)
                    {
                        if($type){
                            //
                        }
                        if($page['type'] !== 'noanalysis'){
                            $dataNoAnalysis[$key] = $totalMetaIndex['pages'][$key][$pk]['noanalysis'];
                        }
                    }
                }
            }
            if(isset($item)) unset($item);
            if(isset($page)) unset($page);
            $totalPages = 0;
            foreach ($dataNoAnalysis as $value){
                $totalPages += (int)$value;
            }

            $this->context->smarty->assign(array(
                'dataNoAnalysis' => $dataNoAnalysis,
                'listPages' => $this->pageTypes,
                'totalPage' => (int)$totalPages,
            ));
            die(Tools::jsonEncode(array(
                'success' => true,
                'modal_html' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'ets_seo/views/templates/admin/modal_select_analysis.tpl')
            )));
        }

        if((int)Tools::isSubmit('etsSeoAnalysisPages')){
            $dataPages = Tools::getValue('dataPages');
            if(!$dataPages || !is_array($dataPages)){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'stop' => 1,
                    'message' => $this->l('Analysis successfully'),
                )));
            }
            $data = EtsSeoAnalysis::getInstance()->analysisPages($dataPages);
            die(Tools::jsonEncode(array(
                'success' => true,
                'data' => $data,
                'stop' => 0
            )));
        }

        if((int)Tools::isSubmit('etsSeoSaveDataAnalysis')){
            $scoreData = Tools::getValue('scoreData');
            if(!$scoreData || !isset($scoreData['page_type']) || !isset($scoreData['score'])){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'message' => $this->l('Analysis successfully'),
                    'stop' => 1,
                )));
            }
            EtsSeoAnalysis::getInstance()->updateDataAnalysis($scoreData['page_type'], $scoreData['score']);
            die(Tools::jsonEncode(array(
                'success' => true,
                'message' => $this->l('Success'),
                'pages' =>  isset($scoreData['pages']) ? $scoreData['pages'] : array(),
                'stop'=> isset($scoreData['stop']) && (int)$scoreData['stop'] ? 1 : 0
            )));
        }
    }
}