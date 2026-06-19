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
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class Ets_Seo_Sitemap
{
    public $context;
    public $request_uri;

    public function __construct($request_uri, $context = null)
    {
        $this->request_uri = $request_uri;
        if(!$context)
        {
            $this->context = Context::getContext();
        }
    }


    public function sitemap()
    {
        $xml = '';
        if((int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP'))
        {
            $isoCode = $this->getLangFromUrl(true);
            $page_type = $this->getPageType();
            if($isoCode && Language::isMultiLanguageActivated())
            {
                $xml = $this->sitemapPage($isoCode, $page_type);
            }
            else
            {
                $languages = Language::getLanguages(true);
                if(count($languages) > 1 && Language::isMultiLanguageActivated())
                {
                    $xml = $this->sitemapLang($languages);
                }
                else{
                    $xml = $this->sitemapPage($this->context->language->iso_code, $page_type);
                }
            }
        }


        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header("Content-Type: application/xml; charset=UTF-8");
        mb_internal_encoding('UTF-8');
        die($xml);
    }
    /**
     * getLinkSitemap
     *
     * @param  string $name
     * @param  int $id
     * @param  int $page
     *
     * @return string
     */
    protected function getLinkSitemap($params = array())
    {
        $linkObj = Context::getContext()->link;
        $link = $linkObj->getBaseLink();
        if(isset($params['lang']) && $params['lang'])
        {
            $link .= $params['lang'].'/';
        }
        $link.= 'sitemap';
        if(isset($params['page_type']) && $params['page_type'])
        {
            if(isset($params['page']) && (int)$params['page'])
            {
                return $link.'/'.$params['page_type'].'/'.(int)$params['page'].'.xml';
            }
            return $link.'/'.$params['page_type'].'.xml';
        }
        return trim($link.'.xml');

    }


    /**
     * sitemapLang
     *
     * @param  array $languages
     *
     * @return string
     */
    protected function sitemapLang($languages)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($languages as $lang)
        {
            $id_lang = $lang['id_lang'];
            $isoCode = $lang['iso_code'];
            $listItemsDispayedSitemap = explode(',', (string)Configuration::get('ETS_SEO_SITEMAP_OPTION'));
            $xml .= $this->getSitemapProduct($id_lang);

            if($this->getCategories($id_lang, true) && in_array('category', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'category')) . ']]></loc>';
                $xml .= '</sitemap>';
            }
            if(CMS::getCMSPages($id_lang, null, true, $this->context->shop->id) && in_array('cms', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'cms')) . ']]></loc>';
                $xml .= '</sitemap>';
            }
            if(CMSCategory::getSimpleCategories($id_lang) && in_array('cms_category', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'cms_category')) . ']]></loc>';
                $xml .= '</sitemap>';
            }
            if(Meta::getMetasByIdLang($id_lang) && in_array('meta', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'meta')) . ']]></loc>';
                $xml .= '</sitemap>';
            }
            if(Manufacturer::getManufacturers(false, $id_lang, true) && in_array('manufacturer', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'manufacturer')) . ']]></loc>';
                $xml .= '</sitemap>';
            }
            if(Supplier::getSuppliers(false, $id_lang, true) && in_array('supplier', $listItemsDispayedSitemap)) {
                $xml .= '<sitemap>';
                $xml .= '<loc><![CDATA[' . $this->getLinkSitemap(array('lang' => $isoCode, 'page_type' => 'supplier')) . ']]></loc>';
                $xml .= '</sitemap>';
            }

        }
        $xml .= '</sitemapindex>';
        return $xml;
    }

    /**
     * sitemapPage
     *
     * @param  int $id_lang
     * @param  string $page_type
     *
     * @return void
     */
    protected function sitemapPage( $isoCode, $page_type = null)
    {
        $listItemsDispayedSitemap = explode(',', (string)Configuration::get('ETS_SEO_SITEMAP_OPTION'));
        $id_lang = Language::getIdByIso($isoCode);
        if($page_type)
        {
            switch ($page_type) {
                case 'category':
                    return $this->getSitemapCategory($id_lang);
                case 'cms':
                    return $this->getSitemapCms($id_lang);
                case 'meta':
                    return $this->getSitemapMeta($id_lang);
                case 'product':
                    return $this->getSitemapProduct($id_lang);
                case 'cms_category':
                    return $this->getSitemapCmsCategory($id_lang);
                case 'manufacturer':
                    return $this->getSitemapManufacturer($id_lang);
                case 'supplier':
                    return $this->getSitemapSupplier($id_lang);
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        //Category
        $categories = $this->getCategories($id_lang, true);
        if(in_array('category', $listItemsDispayedSitemap) && $categories && count($categories))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'category')).']]></loc>';
            $xml .= '</sitemap>';
        }


        //CMS
        $cmsPages  = CMS::getCMSPages($id_lang, null, true, $this->context->shop->id);
        if(in_array('cms', $listItemsDispayedSitemap) && $cmsPages && count($cmsPages))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'cms')).']]></loc>';
            $xml .= '</sitemap>';
        }

        //CMS category
        $cmsCatePages  = CMSCategory::getSimpleCategories($id_lang);
        if(in_array('cms_category', $listItemsDispayedSitemap) && $cmsCatePages && count($cmsCatePages))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'cms_category')).']]></loc>';
            $xml .= '</sitemap>';
        }

        //Meta
        $metas  = Meta::getMetasByIdLang($id_lang);
        if(in_array('meta', $listItemsDispayedSitemap) && $metas && count($metas))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'meta')).']]></loc>';
            $xml .= '</sitemap>';
        }

        //Product
        if(in_array('product', $listItemsDispayedSitemap))
        {
            $xml .= $this->getSitemapProduct($id_lang);
        }

        //manufacturer
        $manufs = Manufacturer::getManufacturers(false, $id_lang, true);
        if(in_array('manufacturer', $listItemsDispayedSitemap) && $manufs && count($manufs))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'manufacturer')).']]></loc>';
            $xml .= '</sitemap>';
        }

        $suppliers = Supplier::getSuppliers(false, $id_lang, true);
        //supplier
        if(in_array('supplier', $listItemsDispayedSitemap) && $suppliers && count($suppliers))
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$this->getLinkSitemap(array('lang'=>$isoCode, 'page_type'=>'supplier')).']]></loc>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    protected function getCategories($id_lang,$nb=false)
    {
        $sql = "
                SELECT ".($nb ? "COUNT(*)" : "c.`id_category`")." FROM `"._DB_PREFIX_."category` c 
                JOIN `"._DB_PREFIX_."category_lang` cl ON c.id_category=cl.id_category AND cl.id_lang=".(int)$id_lang." AND cl.id_shop=".(int)$this->context->shop->id."
                LEFT JOIN `"._DB_PREFIX_."ets_seo_category` ec ON c.id_category=ec.id_category
                WHERE c.is_root_category=0 AND c.id_parent > 0 AND c.active=1 AND (ec.id_category IS NULL OR ((ec.allow_search=1 OR (ec.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT')."=1)) AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id.")) 
                ".($nb ? "" : " GROUP BY c.`id_category`");
        return $nb ? Db::getInstance()->getValue($sql) : Db::getInstance()->executeS($sql);

    }

    protected function getSitemapCategory($id_lang)
    {
        //$categories = $this->getCategories();
        $categories = $this->getCategories($id_lang);
        $params = array();
        $link = Context::getContext()->link;
        foreach ($categories as $item) {
            $cate = new Category($item['id_category'], $id_lang);
            $dataItem = array();
            $dataItem['link'] = $this->addParamsToUrl($cate->getLink( Context::getContext()->link, $id_lang));
            $dataItem['image'] = array(
                'link' => $link->getCatImageLink($cate->link_rewrite, $cate->id/*, 'category_default'*/),
            );

            $params[] = $dataItem;

        }

        return $this->renderXmlLinks($params, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CATEGORY'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_CATEGORY'),
        ));

    }

    protected function getSitemapMeta($id_lang)
    {
        //$metas  = Meta::getMetasByIdLang($id_lang);
        $metas = Db::getInstance()->executeS("
                SELECT c.`id_meta`, c.page, cl.* FROM `"._DB_PREFIX_."meta` c 
                JOIN `"._DB_PREFIX_."meta_lang` cl ON c.id_meta=cl.id_meta AND cl.id_lang=".(int)$id_lang." AND cl.id_shop=".(int)$this->context->shop->id."
                LEFT JOIN `"._DB_PREFIX_."ets_seo_meta` ec ON c.id_meta=ec.id_meta
                WHERE ec.id_meta IS NULL OR (ec.allow_search=1 AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id.") 
                GROUP BY c.`id_meta`");

        $params = array();
        foreach ($metas as $meta) {
            if(!$meta['url_rewrite'] && $meta['page'] !== 'index'){
                continue;
            }

            $dataItem = array();
            $link = Context::getContext()->link;
            $dataItem['link'] = $link->getPageLink($meta['page'], null, $id_lang);
            $params[] = $dataItem;
        }

        return $this->renderXmlLinks($params, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_META'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_META'),
        ));
    }

    protected function getSitemapCms($id_lang)
    {
        //$cmsPages  = CMS::getCMSPages($id_lang, null, true, $this->context->shop->id);
        $cmsPages = Db::getInstance()->executeS("
                SELECT c.`id_cms` FROM `"._DB_PREFIX_."cms` c 
                JOIN `"._DB_PREFIX_."cms_lang` cl ON c.id_cms=cl.id_cms AND cl.id_lang=".(int)$id_lang." AND cl.id_shop=".(int)$this->context->shop->id."
                LEFT JOIN `"._DB_PREFIX_."ets_seo_cms` ec ON c.id_cms=ec.id_cms
                WHERE c.active=1 AND (ec.id_cms IS NULL OR ((ec.allow_search=1 OR (ec.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT')."=1)) AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id.")) 
                GROUP BY c.`id_cms`");
        $links = array();
        foreach ($cmsPages as $cms)
        {
            $dataItem = array();
            $obj = new CMS($cms['id_cms'], $id_lang);
            $link = Context::getContext()->link;
            $dataItem['link'] = $link->getCMSLink($obj, null, null, $id_lang);
            $links[] = $dataItem;
        }

        return $this->renderXmlLinks($links, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CMS'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_CMS'),
        ));
    }

    protected function getSitemapCmsCategory($id_lang)
    {
        //$cmsPages  = CMSCategory::getSimpleCategories($id_lang);
        $cmsPages = Db::getInstance()->executeS("
                SELECT c.`id_cms_category` FROM `"._DB_PREFIX_."cms_category` c 
                JOIN `"._DB_PREFIX_."cms_category_lang` cl ON c.id_cms_category=cl.id_cms_category AND cl.id_lang=".(int)$id_lang." AND cl.id_shop=".(int)$this->context->shop->id."
                LEFT JOIN `"._DB_PREFIX_."ets_seo_cms_category` ec ON c.id_cms_category=ec.id_cms_category
                WHERE c.active=1 AND (ec.id_cms_category IS NULL OR ((ec.allow_search=1 OR (ec.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT')."=1)) AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id.")) 
                GROUP BY c.`id_cms_category`");
        $links = array();
        foreach ($cmsPages as $cms)
        {
            $dataItem = array();
            $link = Context::getContext()->link;
            $obj = new CMSCategory($cms['id_cms_category'], $id_lang);
            $dataItem['link'] = $link->getCMSCategoryLink($obj, null, $id_lang);
            $links[] = $dataItem;
        }

        return $this->renderXmlLinks($links, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_CMS_CATEGORY'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_CMS_CATEGORY'),
        ));
    }

    protected function getSitemapManufacturer($id_lang)
    {
        //$manufs = Manufacturer::getManufacturers(false, $id_lang, true);
        $manufs = Db::getInstance()->executeS("
                SELECT c.`id_manufacturer` FROM `"._DB_PREFIX_."manufacturer` c 
                LEFT JOIN `"._DB_PREFIX_."manufacturer_shop` ms ON c.id_manufacturer=ms.id_manufacturer AND ms.id_shop=".(int)$this->context->shop->id." 
                LEFT JOIN `"._DB_PREFIX_."ets_seo_manufacturer` ec ON c.id_manufacturer=ec.id_manufacturer
                WHERE c.active=1 AND (ec.id_manufacturer IS NULL OR ((ec.allow_search=1 OR (ec.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT')."=1)) AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id.")) 
                GROUP BY c.`id_manufacturer`");
        $links = array();
        foreach ($manufs as $manuf)
        {
            $dataItem = array();
            $link = Context::getContext()->link;
            $obj = new Manufacturer($manuf['id_manufacturer'], $id_lang);
            $dataItem['link'] = $link->getManufacturerLink($obj, null, $id_lang);
            if(file_exists(_PS_ROOT_DIR_.'/img/m/'.$obj->id.'.jpg'))
            {
                $dataItem['image'] = array(
                    'link' => $this->context->shop->getBaseURL(true, true).'img/m/'.$obj->id.'.jpg',
                );
            }
            $links[] = $dataItem;
        }

        return $this->renderXmlLinks($links, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_MANUFACTURER'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_MANUFACTURER'),
        ));
    }

    protected function getSitemapSupplier($id_lang)
    {
        //$suppliers = Supplier::getSuppliers(false, $id_lang, true);
        $suppliers = Db::getInstance()->executeS("
                SELECT c.`id_supplier` FROM `"._DB_PREFIX_."supplier` c 
                LEFT JOIN `"._DB_PREFIX_."supplier_shop` ms ON c.id_supplier=ms.id_supplier AND ms.id_shop=".(int)$this->context->shop->id." 
                LEFT JOIN `"._DB_PREFIX_."ets_seo_supplier` ec ON c.id_supplier=ec.id_supplier
                WHERE c.active=1 AND (ec.id_supplier IS NULL OR ((ec.allow_search=1 OR (ec.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT')."=1)) AND ec.id_lang=".(int)$id_lang." AND ec.id_shop=".(int)$this->context->shop->id."))
                GROUP BY c.id_supplier");
        $links = array();
        foreach ($suppliers as $supplier)
        {
            $dataItem = array();
            $link = Context::getContext()->link;
            $obj = new Supplier($supplier['id_supplier'], $id_lang);
            $dataItem['link'] = $link->getSupplierLink($obj, null, $id_lang);

            if(file_exists(_PS_ROOT_DIR_.'/img/s/'.$obj->id.'.jpg'))
            {
                $dataItem['image'] = array(
                    'link' => $this->context->shop->getBaseURL(true, true).'img/s/'.$obj->id.'.jpg',
                );
            }
            $links[] = $dataItem;
        }
        return $this->renderXmlLinks($links, array(
            'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_SUPPLIER'),
            'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_SUPPLIER'),
        ));
    }

    protected function getSitemapProduct($id_lang)
    {
        $per_page = 9999999;
        if($limit_prod = (int) Configuration::get('ETS_SEO_PROD_SITEMAP_LIMIT'))
        {
            $per_page = $limit_prod;
        }
        $page = $this->getProductPage();
        if($page)
        {
            $page = (int)$page;
            $start = ($page - 1) * $per_page;
            $products = Db::getInstance()->executeS("
                    SELECT ep.*, p.id_product as id_product FROM `"._DB_PREFIX_."product` p 
                    LEFT JOIN `"._DB_PREFIX_."product_shop` ps ON p.id_product=ps.id_product AND ps.id_shop=".(int)$this->context->shop->id." 
                    LEFT JOIN `"._DB_PREFIX_."ets_seo_product` ep ON p.id_product=ep.id_product AND ep.id_shop=".(int)$this->context->shop->id." 
                    WHERE ps.`visibility` IN ('both', 'catalog') AND ps.`active`=1 AND (ep.id_product IS NULL OR ((ep.allow_search=1 OR (ep.allow_search=2 AND ".(int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT')."=1)) AND ep.id_lang=".(int)$id_lang." AND ep.id_shop=".(int)$this->context->shop->id_shop_group."))
                    GROUP BY p.id_product
                    ORDER BY p.id_product DESC
                    LIMIT ".(int)$start.",".(int)$per_page);
            //$products = Product::getProducts($id_lang, $start, $per_page, 'id_product', 'DESC');
            $links = array();
            foreach ($products as $item)
            {

                $dataItem = array();
                $link = Context::getContext()->link;
                $product = new Product((int)$item['id_product'], false,$id_lang);

                $dataItem['link'] = $product->getLink($this->context);

                $images = Product::getCover($product->id);
                if($images && isset($images['id_image']) && (int)($images['id_image']))
                {
                    $caption = null;
                    $img = new Image($images['id_image'], $id_lang);
                    if($img && isset($img->legend))
                    {
                        $caption = $img->legend;
                    }
                    $dataItem['image'] = array(
                        'link'=> $link->getImageLink($product->link_rewrite, (isset($images['id_image']) ? $images['id_image'] : ''),  ImageType::getFormattedName('home')),
                        'caption' => $caption,
                        'title' => $product->name,
                    );
                }

                $links[] = $dataItem;
            }

            return $this->renderXmlLinks($links, array(
                'priority'=> Configuration::get('ETS_SEO_SITEMAP_PRIORITY_PRODUCT'),
                'changefreq'=> Configuration::get('ETS_SEO_SITEMAP_FREQ_PRODUCT'),
            ));
        }

        $ets_seo = Module::getInstanceByName('ets_seo');
        $total_product = $ets_seo->getTotalProduct(true, $id_lang);
        $isoCode = Language::getIsoById($id_lang);
        $total_page = ceil($total_product / $per_page);
        if($total_page)
        {
            for ($p=1; $p <= $total_page ; $p++)
            {
                $paramPage = array('lang' => $isoCode, 'page_type' => 'product', 'page' =>$p);
                $links[] = $this->getLinkSitemap($paramPage);
            }
            return $this->renderXmlPages($links);
        }
    }

    /**
     * renderXmlPage
     *
     * @param  array $links
     * @param  array $params
     *
     * @return void
     */
    protected function renderXmlLinks($links, $params = array())
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        if($links)
        {
            foreach ($links as $link) {
                $xml .= '<url>';
                $xml .= '<loc><![CDATA['.$link['link'].']]></loc>';
                $xml .= '<changefreq>'.(isset($params['changefreq']) && $params['changefreq'] ? $params['changefreq'] : 'weekly').'</changefreq>';
                $xml .= '<priority>'.(isset($params['priority']) && $params['priority'] !== '' ? number_format($params['priority'], 1, '.', '') : 1.0 ).'</priority>';
                if(isset($link['image']) && !empty($link['image']))
                {
                    $xml .= '<image:image>';
                    $xml .= '<image:loc><![CDATA['.$link['image']['link'].']]></image:loc>';
                    if(isset($link['image']['caption']) && $link['image']['caption'] && strip_tags($link['image']['caption']))
                        $xml .= '<image:caption><![CDATA['.strip_tags($link['image']['caption']).']]></image:caption>';
                    if(isset($link['image']['title']) && $link['image']['title'] && strip_tags($link['image']['title']))
                        $xml .= '<image:title><![CDATA['.strip_tags($link['image']['title']).']]></image:title>';
                    $xml .= '</image:image>';
                }
                $xml .= '</url>';
            }
        }
        $xml .= '</urlset>';
        return $xml;

    }

    protected function renderXmlPages($pages)
    {
        $xml = '';
        foreach ($pages as $page)
        {
            $xml .= '<sitemap>';
            $xml .= '<loc><![CDATA['.$page.']]></loc>';
            //$xml .= '<lastmod>2004-10-01T18:23:17+00:00</lastmod>';
            $xml .= '</sitemap>';
        }
        return $xml;
    }

    /**
     * addParamsToUrl
     *
     * @param  string $link
     * @param  array $params
     *
     * @return string
     */
    protected function addParamsToUrl($link, $params = array())
    {
        $count = 0;
        foreach($params as $k=>$p)
        {
            if($count == 0 && Tools::strpos($link, '?') !== false)
            {
                $link = $link .'?'.$k.'='.$p;
            }
            else{
                $link = $link .'&'.$k.'='.$p;
            }
        }

        return $link;

    }

    public function getLangFromUrl($getIsoCode = false)
    {
        // Get request uri (HTTP_X_REWRITE_URL is used by IIS)
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        $requestUri = rawurldecode($requestUri);

        if (isset(Context::getContext()->shop) && is_object(Context::getContext()->shop)) {
            $requestUri = preg_replace(
                '#^'.preg_quote(Context::getContext()->shop->getBaseURI(), '#').'#i',
                '/',
                $requestUri
            );
        }

        // If there are several languages, get language from uri
        if (Language::isMultiLanguageActivated()) {
            if (preg_match('#^/([a-z]{2})(?:/.*)?$#', $requestUri, $m)) {
                $isoCode = $m[1];
                if($isoCode)
                {
                    $id_lang = Language::getIdByIso($isoCode);
                    if($id_lang)
                    {
                        if($getIsoCode)
                        {
                            return $isoCode;
                        }
                        return (int)$id_lang;
                    }
                    return false;
                }
            }
        }
        return 0;
    }

    public function getPageType()
    {
        $types = array(
            'product',
            'category',
            'cms',
            'cms_category',
            'meta',
            'manufacturer',
            'supplier'
        );
        $page_type = null;
        foreach ($types as $type) {
            if(preg_match("/sitemap\/".$type."(\/(\d+)|)\.xml/", $this->request_uri))
            {
                $page_type = $type;
            }
        }
        return $page_type;

    }

    public function getProductPage()
    {
        if(preg_match("/sitemap\/product\/\d+\.xml$/", $this->request_uri))
        {
            $uri = $this->request_uri;
            if(preg_match('/\?/', $this->request_uri))
            {
                $uri = explode('?', $this->request_uri)[0];
            }
            $uri = explode('/', $uri);
            $uri =end($uri);
            return  (int)str_replace('.xml', '', $uri);

        }
        return false;
    }
}
