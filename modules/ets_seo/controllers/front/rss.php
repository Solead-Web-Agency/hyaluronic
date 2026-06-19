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
 * needs please, contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class Ets_seoRssModuleFrontController extends ModuleFrontController
{
    protected $rss_content_before;
    protected $rss_content_after;
    protected $limit_data;
    protected  $special_rss;

    public function __construct()
    {
        parent::__construct();
        $this->setConfigContent();
        $this->limit_data = 9999999;
        if((int)Configuration::get('ETS_SEO_RSS_POST_LIMIT') || Configuration::get('ETS_SEO_RSS_POST_LIMIT') === '0')
        {
            $this->limit_data = (int)Configuration::get('ETS_SEO_RSS_POST_LIMIT');
        }
        $this->special_rss = array('all-products', 'new-products', 'special-products', 'popular-products');
    }

    public function initContent()
    {
        parent::initContent();
        $rssOptions = explode(',', (string)Configuration::get('ETS_SEO_RSS_OPTION'));
        $idPage = $this->getIdPage();
        $id_lang = $this->context->language->id;
        if(!$idPage && $idPage !== false)
        {
            $categories = in_array('product_category', $rssOptions) ? EtsSeoCategory::getCategoriesWithoutRoot() : '';
            $cms = in_array('cms_category', $rssOptions) ? CMS::getCMSPages($id_lang, null, true, $this->context->shop->id) : '';
            $featuredProductList = array(
                'all_products' => array(
                    'link' => $this->context->shop->getBaseURL(true, true).'rss/all-products',
                    'name' => $this->module->l('All products', 'rss'),
                ),
                'new_products' => array(
                    'link' => $this->context->shop->getBaseURL(true, true).'rss/new-products',
                    'name' => $this->module->l('New products', 'rss'),
                ),
                'special_products' => array(
                    'link' => $this->context->shop->getBaseURL(true, true).'rss/special-products',
                    'name' => $this->module->l('Special products', 'rss'),
                ),
                'popular_products' => array(
                    'link' => $this->context->shop->getBaseURL(true, true).'rss/popular-products',
                    'name' => $this->module->l('Popular products', 'rss'),
                ),
            );
            $featuredProductOptions = array();
            foreach ($featuredProductList as $k=>$item)
            {
                if(in_array($k, $rssOptions))
                {
                    $featuredProductOptions[$k] = $item;
                }
            }

            $this->context->smarty->assign(
                array(
                    'ets_seo_categories'=> $categories,
                    'ets_seo_cms'=> $cms,
                    'featured_product_options' => $featuredProductOptions,
                    'ets_seo_rss_enable' => (int)Configuration::get('ETS_SEO_RSS_ENABLE'),
                    'ets_seo_base_url'=> $this->context->link->getBaseLink()
                )
            );
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/rss.tpl');
        }
        elseif($idPage)
        {
            $pageType = (int)$idPage ? $this->getIdPage(true) : (in_array( $idPage, $this->special_rss) ? $idPage : false);
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/"
                    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                    xmlns:dc="http://purl.org/dc/elements/1.1/"
                    xmlns:atom="http://www.w3.org/2005/Atom"
                    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
                    xmlns:slash="http://purl.org/rss/1.0/modules/slash/" 
                    version="2.0">';
            if($pageType && (int)Configuration::get('ETS_SEO_RSS_ENABLE'))
            {
                switch ($pageType)
                {
                    case 'category':
                        $xml .= $this->feedProduct($idPage, $id_lang);
                        break;
                    case 'page':
                        $xml .= $this->feedProduct($idPage, $id_lang);
                        break;
                    case 'all-products':
                        $xml .= $this->feedAllProducts($id_lang);
                        break;
                    case 'new-products':
                        $xml .= $this->feedNewProducts($id_lang);
                        break;
                    case 'special-products':
                        $xml .= $this->feedSpecialProducts($id_lang);
                        break;
                    case 'popular-products':
                        $xml .= $this->feedPopularProducts($id_lang);
                        break;
                }

            }
            $xml .= '</rss>';

            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            header("Content-Type: application/xml; charset=UTF-8");
            mb_internal_encoding('UTF-8');
            die($xml);
        }
        else{
            Tools::redirect(404);
        }

    }

    protected function feedProduct($id_category, $id_lang)
    {
        $category = new Category($id_category, $id_lang);
        $products = $category->getProducts($id_lang, 1, $this->limit_data, 'date_upd', 'DESC');
        $params = array(
            'title' => $category->name,
            'link' => $category->getLink($this->context->link, $id_lang),
            'description' => $category->description
        );
        return $this->generateXmlProduct($products, $id_lang, $params);
    }

    protected function feedCms($id_cms, $id_lang)
    {
        $cms = new CMS($id_cms, $id_lang);
        $link_cms = $this->context->link->getCMSLink($cms, null, null, $id_lang);
        $xml = '<channel>';
        $xml .= '<title>'.htmlspecialchars($this->cleanUTF8($cms->meta_title)).'</title>';
        $xml .= '<link>'.$link_cms.'</link>';
        $xml .= '<description>'.strip_tags($this->cleanUTF8($this->rss_content_before.($cms->meta_description ? $cms->meta_description : $cms->content).$this->rss_content_after)).'</description>';
        $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }

    protected function feedAllProducts($id_lang)
    {
        $products = Product::getProducts($id_lang, 0, $this->limit_data, 'id_product', 'DESC');
        $params = array(
            'title' => $this->l('All products', 'rss'),
            'link' => $this->context->link->getPageLink('index'),
            'description' => $this->l('All products', 'rss')
        );

        return $this->generateXmlProduct($products, $id_lang, $params);
    }

    protected function feedNewProducts($id_lang)
    {
        $params = array(
            'title' => $this->l('New products', 'rss'),
            'link' => '',
            'description' => ''
        );
        $meta = Meta::getMetaByPage('new-products', $id_lang);

        if($meta && isset($meta['id_meta']))
        {
            $params['title'] = $meta['title'];
            $params['link'] = $this->context->link->getPageLink($meta['page'], null, $id_lang);
            $params['description'] =  $meta['description'];
        }

        $products = Product::getNewProducts($id_lang, 1, $this->limit_data, false, 'id_product', 'DESC');
        return $this->generateXmlProduct($products, $id_lang, $params);
    }

    protected function feedSpecialProducts($id_lang)
    {
        $params = array(
            'title' => $this->l('New products', 'rss'),
            'link' => '',
            'description' => ''
        );
        $meta = Meta::getMetaByPage('	prices-drop', $id_lang);

        if($meta && isset($meta['id_meta']))
        {
            $params['title'] = $meta['title'];
            $params['link'] = $this->context->link->getPageLink($meta['page'], null, $id_lang);
            $params['description'] =  $meta['description'];
        }
        $products = Product::getPricesDrop($id_lang, 1, $this->limit_data, false, 'id_product', 'DESC');
        return $this->generateXmlProduct($products, $id_lang, $params);
    }
    protected function feedPopularProducts($id_lang)
    {
        $params = array(
            'title' => $this->l('Popular products', 'rss'),
            'link' => '',
            'description' => ''
        );
        if((int) Configuration::get('HOME_FEATURED_CAT'))
        {
            $category = new Category((int) Configuration::get('HOME_FEATURED_CAT'), $id_lang);
            $params['description'] = $category->description ? $category->description : $this->l('Popular products', 'rss');
            $params['link'] = $category->getLink($this->context->link, $id_lang);
        }

        $products = $this->getPopularProducts($this->limit_data);
        return $this->generateXmlProduct($products, $id_lang, $params);
    }

    protected function generateXmlProduct($products, $id_lang, $params = array())
    {
        if(!$products)
        {
            $products = array();
        }
        $xml = '<channel>';
        if($params)
        {
            $xml .= '<title>'.htmlspecialchars($this->cleanUTF8($params['title'])).'</title>';
            $xml .= '<link>'.$params['link'].'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$params['description'].$this->rss_content_after)).']]></description>';

        }
        $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        foreach ($products as $item)
        {
            $xml .= '<item>';

            $product = new Product($item['id_product'],null, $id_lang);

            $xml .= '<title>'.htmlspecialchars($this->cleanUTF8($product->name)).'</title>';
            $xml .= '<description><![CDATA[';
            if(($image = Product::getCover($product->id)) && isset($image['id_image'])){
                $xml .= '<a href="'.$product->getLink($this->context).'"  rel="self"><img width="130" height="100" src="'.$this->context->link->getImageLink($product->link_rewrite[$id_lang], $image['id_image'], ImageType::getFormatedName('home')).'" ></a>';
            }
            $xml .= '<br/>'.strip_tags($this->cleanUTF8($this->rss_content_before.$product->description_short.$this->rss_content_after));
            $xml .= ']]></description>';
            $xml .='<pubDate>'.date('r', strtotime($product->date_add)).'</pubDate>'."\n";
            $xml .='<link>'.$product->getLink($this->context).'</link>'."\n";;
            $xml .='<guid>'.$product->getLink($this->context).'</guid>'."\n";;
            $xml .= '</item>';
        }
        $xml .= '</channel>';

        return $xml;
    }

    public function cleanUTF8($some_string)
    {
        $some_string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','?', $some_string );
        $some_string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $some_string );
        return $some_string;
    }

    protected function setConfigContent()
    {
        $rss_content_before = Configuration::get('ETS_SEO_RSS_CONTENT_BEFORE', $this->context->language->id);
        $rss_content_after = Configuration::get('ETS_SEO_RSS_CONTENT_AFTER', $this->context->language->id);
        $this->rss_content_before = $rss_content_before ? $rss_content_before.' <br/>' : '';
        $this->rss_content_after = $rss_content_after ? '<br/> '.$rss_content_after : '';

    }

    public function getIdPage($getType = false)
    {
        $request_uri = Tools::substr($_SERVER['REQUEST_URI'], Tools::strlen(__PS_BASE_URI__));
        if(preg_match("/rss\/[^\/]+(\/\d+|)\.xml$/", $request_uri))
        {
            $uri = $request_uri;
            if(preg_match('/\?/', $request_uri))
            {
                $uri = explode('?', $request_uri)[0];
            }
            $uri = explode('/', $uri);
            if($getType)
                return $uri[1];
            $uri =end($uri);
            $id = str_replace('.xml', '', $uri);
            if((int)$id || in_array($id, $this->special_rss))
            {
                return $id;
            }
            return false;

        }
        elseif(preg_match("/rss$/", $request_uri))
        {
            return null;
        }
        return false;
    }

    protected function getPopularProducts($limit = 12)
    {
        $category = new Category((int) Configuration::get('HOME_FEATURED_CAT'));

        $searchProvider = new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            $category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();

        $nProducts = $limit;

        $query
            ->setResultsPerPage($nProducts)
            ->setPage(1)
        ;

        if (Configuration::get('HOME_FEATURED_RANDOMIZE')) {
            $query->setSortOrder(SortOrder::random());
        } else {
            $query->setSortOrder(new SortOrder('product', 'position', 'asc'));
        }

        $result = $searchProvider->runQuery(
            $context,
            $query
        );

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        foreach ($result->getProducts() as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }
        $results = array();
        foreach ($products_for_template as $item){
            $results[] = array('id_product' => $item->getId());
        }
        return $results;
    }


}