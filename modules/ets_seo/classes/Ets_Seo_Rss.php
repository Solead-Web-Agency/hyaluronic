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

 class Ets_Seo_Rss
 {
    protected $rss_content_before;
    protected $rss_content_after;

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

    public function feed()
    {

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/"
                xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:atom="http://www.w3.org/2005/Atom"
                xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
                xmlns:slash="http://purl.org/rss/1.0/modules/slash/" 
                version="2.0">';
        $pageType = $this->getPageType();
        if($pageType)
        {
            $id_lang = $this->context->language->id;
            $idPage  = $this->getIdPage();
            if($idPage)
            {
                switch($pageType)
                {
                    case 'product':
                        $xml .= $this->feedProduct($idPage, $id_lang);
                        break;
                    case 'cms':
                        $xml .= $this->feedCms($idPage, $id_lang);
                        break;
                    case 'cms_category':
                        $xml .= $this->feedCmsCategory($idPage, $id_lang);
                        break;
                    case 'category':
                        $xml .= $this->feedCategory($idPage, $id_lang);
                        break;
                    case 'manufacturer':
                        $xml .= $this->feedManufacturer($idPage, $id_lang);
                        break;
                    case 'supplier':
                        $xml .= $this->feedSupplier($idPage, $id_lang);
                        break;
                }
            }
            else{
                $xml .= $this->feedMeta($pageType, $id_lang);
            }
            
        }
        
        $xml .= '</rss>';

        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header("Content-Type: application/xml; charset=ISO-8859-1");
        mb_internal_encoding('UTF-8');
        die(utf8_encode($xml));
    }
     protected function feedProduct($id_product, $id_lang)
    {
        $product = new Product($id_product,null, $id_lang);

        $xml = '<channel>';
            $xml .= '<title><![CDATA['.$this->cleanUTF8($product->name).']]></title>';
            $xml .= '<link>'.$product->getLink($this->context).'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$product->description_short.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
            if($image = Product::getCover($id_product))
            {
                $image_url = $this->context->link->getImageLink($product->link_rewrite, $image['id_image'], ImageType::getFormatedName('home'));
                $xml .= '<image>';
                    $xml .= '<title>'.$this->cleanUTF8($product->name).'</title>';
                    $xml .= '<link>'.$product->getLink($this->context).'</link>';
                    $xml .= '<url>'.$image_url.'</url>';
                $xml .= '</image>';
            }
        $xml .= '</channel>';
        
        
        return $xml;
    }

    protected function feedCms($id_cms, $id_lang)
    {
        $cms = new CMS($id_cms, $id_lang);
        $link = $cms->getLinks($id_lang, array($id_cms)) ? $cms->getLinks($id_lang, array($id_cms))[0]['link'] : '';
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($cms->meta_title).'</title>';
            $xml .= '<link>'.$link.'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$cms->meta_description.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }
    protected function feedCmsCategory($id_cms_category, $id_lang)
    {
        $cms = new CMSCategory($id_cms_category, $id_lang);
        $link = $cms->getLink($this->context->link);
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($cms->meta_title).'</title>';
            $xml .= '<link>'.$link.'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$cms->meta_description.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }

    protected function feedManufacturer($id_manufacturer, $id_lang)
    {
        $manuf = new Manufacturer($id_manufacturer, $id_lang);
        $link = $this->context->link->getManufacturerLink($manuf, null, $id_lang);
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($manuf->name).'</title>';
            $xml .= '<link>'.$link.'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$manuf->description.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }

    protected function feedSupplier($id_supplier, $id_lang)
    {
        $supplier = new Manufacturer($id_supplier, $id_lang);
        $link = $this->context->link->getSupplierLink($supplier, null, $id_lang);
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($supplier->name).'</title>';
            $xml .= '<link>'.$link.'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$supplier->description.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }

    protected function feedMeta($page_name, $id_lang)
    {
        $meta = Meta::getMetaByPage($page_name, $id_lang);

        $link = new Link();
        $link_meta = $link->getPageLink($page_name, null, $id_lang);
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($meta['title']).'</title>';
            $xml .= '<link>'.$link_meta.'</link>';
            $xml .= '<description>'.strip_tags($this->cleanUTF8($this->rss_content_before.$meta['description'].$this->rss_content_after)).'</description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
        $xml .= '</channel>';
        return $xml;
    }

    protected function feedCategory($id_category, $id_lang)
    {
        $category = new Category($id_category, $id_lang);
        
        $xml = '<channel>';
            $xml .= '<title>'.$this->cleanUTF8($category->name).'</title>';
            $xml .= '<link>'.$category->getLink(new Link(), $id_lang).'</link>';
            $xml .= '<description><![CDATA['.strip_tags($this->cleanUTF8($this->rss_content_before.$category->description.$this->rss_content_after)).']]></description>';
            $xml .= '<generator>'.$this->context->shop->domain.'</generator>';
 
            if((int)Configuration::get('ETS_SEO_RSS_DISPLAY_ITEM'))
            {
                $per_page = (int)Configuration::get('ETS_SEO_RSS_NUM_ITEM');
                $products = $category->getProducts($id_lang, 1, $per_page);

                if($products)
                {
                    foreach($products as $product)
                    {
                        $product = (object)$product;
                        $xml .= '<item>';
                            $xml .= '<title>'.$this->cleanUTF8($product->name).'</title>';
                            $xml .='<description><![CDATA[';
                            if($image = Product::getCover($product->id_product))
                            {
                                $image_url = $this->context->link->getImageLink($product->link_rewrite, $image['id_image'], ImageType::getFormatedName('home'));
                                $xml.='<a href="'.$product->link.'"><img width=130 height=100 src="'.$image_url.'" ></a></br>';
                            }
                            $xml .= strip_tags($this->cleanUTF8($product->description_short)); 
                            $xml.=']]></description>';
                            $xml .='<link>'.$product->link.'</link>';
                        $xml .='<guid>'.$product->link.'</guid>';
                        $xml .='<slash:comments>0</slash:comments>';
                        $xml .= '</item>';
                    }
                }
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
            if(preg_match("/rss\/".$type."(\/(\d+)|)\.xml/", $this->request_uri))
            {
                $page_type = $type;
            }
        }
        return $page_type;
        
    }

    public function getIdPage()
    {
        if(preg_match("/rss\/\w+\/\d+\.xml$/", $this->request_uri))
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