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

if (!defined('_PS_VERSION_'))
{
    exit;
}

class EtsSeoUpdating
{
    public function updateDuplicateProduct()
    {
        $products = Db::getInstance()->executeS(
            "SELECT link_rewrite,count(id_product) as count_id,min(id_product) as minid,id_shop,id_lang
                        FROM `" . _DB_PREFIX_ . "product_lang`
                        GROUP BY id_shop,id_lang,link_rewrite
                        HAVING count_id>1");
        if ($products && !empty($products)) {
            foreach ($products as $product) {
                if ($product['link_rewrite']) {
                    try {
                        Db::getInstance()->execute(
                            "UPDATE `" . _DB_PREFIX_ . "product_lang` 
                                        SET link_rewrite=CONCAT(link_rewrite, '-', id_product) 
                                        WHERE `link_rewrite`='" . (string)$product['link_rewrite'] . "' AND id_product != " . (int)$product['minid'] . " AND `id_shop`=" . (int)$product['id_shop'] . " AND `id_lang`=" . (int)$product['id_lang']);
                    } catch (Exception $ex) {
                        //
                    }

                }
            }
        }
    }

    public function updateDuplicateCategory()
    {
        $results = Db::getInstance()->executeS(
            "SELECT link_rewrite,count(id_category) as count_id,min(id_category) as minid,id_shop,id_lang
                        FROM `" . _DB_PREFIX_ . "category_lang`
                        GROUP BY id_shop,id_lang,link_rewrite
                        HAVING count_id>1");
        if ($results && !empty($results)) {
            foreach ($results as $item) {
                if ($item['link_rewrite']) {
                    try {
                        Db::getInstance()->execute(
                            "UPDATE `" . _DB_PREFIX_ . "category_lang` 
                                        SET link_rewrite=CONCAT(link_rewrite, '-', id_category) 
                                        WHERE `link_rewrite`='" . (string)$item['link_rewrite'] . "' AND id_category != " . (int)$item['minid'] . " AND `id_shop`=" . (int)$item['id_shop'] . " AND `id_lang`=" . (int)$item['id_lang']);
                    } catch (Exception $ex) {
                        //
                    }

                }
            }
        }
    }

    public function updateDuplicateCMS()
    {
        $results = Db::getInstance()->executeS(
            "SELECT link_rewrite,count(id_cms) as count_id,min(id_cms) as minid,id_shop,id_lang
                        FROM `" . _DB_PREFIX_ . "cms_lang`
                        GROUP BY id_shop,id_lang,link_rewrite
                        HAVING count_id>1");
        if ($results && !empty($results)) {
            foreach ($results as $item) {
                if ($item['link_rewrite']) {
                    try {
                        Db::getInstance()->execute(
                            "UPDATE `" . _DB_PREFIX_ . "cms_lang` 
                                        SET link_rewrite=CONCAT(link_rewrite, '-', id_cms) 
                                        WHERE `link_rewrite`='" . (string)$item['link_rewrite'] . "' AND id_cms != " . (int)$item['minid'] . " AND `id_shop`=" . (int)$item['id_shop'] . " AND `id_lang`=" . (int)$item['id_lang']);
                    } catch (Exception $ex) {
                        //
                    }

                }
            }
        }
    }

    public function updateDuplicateCMSCategory()
    {
        $results = Db::getInstance()->executeS(
            "SELECT link_rewrite,count(id_cms_category) as count_id,min(id_cms_category) as minid,id_shop,id_lang
                        FROM `" . _DB_PREFIX_ . "cms_category_lang`
                        GROUP BY id_shop,id_lang,link_rewrite
                        HAVING count_id>1");
        if ($results && !empty($results)) {
            foreach ($results as $item) {
                if ($item['link_rewrite']) {
                    try {
                        Db::getInstance()->execute(
                            "UPDATE `" . _DB_PREFIX_ . "cms_category_lang` 
                                        SET link_rewrite=CONCAT(link_rewrite, '-', id_cms_category) 
                                        WHERE `link_rewrite`='" . (string)$item['link_rewrite'] . "' AND id_cms_category != " . (int)$item['minid'] . " AND `id_shop`=" . (int)$item['id_shop'] . " AND `id_lang`=" . (int)$item['id_lang']);
                    } catch (Exception $ex) {
                        //
                    }

                }
            }
        }
    }

    public function updateDuplicateMeta()
    {
        $results = Db::getInstance()->executeS(
            "SELECT url_rewrite,count(id_meta) as count_id,min(id_meta) as minid,id_shop,id_lang
                        FROM `" . _DB_PREFIX_ . "meta_lang`
                        GROUP BY id_shop,id_lang,url_rewrite
                        HAVING count_id>1");
        if ($results && !empty($results)) {
            foreach ($results as $item) {
                if ($item['url_rewrite']) {
                    try {
                        Db::getInstance()->execute(
                            "UPDATE `" . _DB_PREFIX_ . "meta_lang` 
                                        SET url_rewrite=CONCAT(url_rewrite, '-', id_meta) 
                                        WHERE `url_rewrite`='" . (string)$item['url_rewrite'] . "' AND id_meta != " . (int)$item['minid'] . " AND `id_shop`=" . (int)$item['id_shop'] . " AND `id_lang`=" . (int)$item['id_lang']);
                    } catch (Exception $ex) {
                        //
                    }

                }
            }
        }
    }

    public static function updateBaseUrlTheme($dir=null)
    {
        if(!$dir){
            $dir = _PS_THEME_DIR_;
        }
        $files = glob(rtrim($dir, '/').'/*');
        foreach ($files as $file){
            if(is_dir($file)){
                self::updateBaseUrlTheme($file);
            }
            elseif(is_file($file) && file_exists($file)){
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if($ext == 'tpl'){
                    $content = Tools::file_get_contents($file);
                    if(preg_match('/<a(.*)\s+href="\{(\$urls\.base_url)\}"([^>]*)>/', $content)){
                        file_put_contents(dirname($file).'/'.basename($file,'.tpl').'.ets_seo_bak.tpl', $content);
                        $replacement = "{if isset(\$ets_seo_base_url) && \$ets_seo_base_url}{\$ets_seo_base_url}{else}{\$urls.base_url}{/if}";
                        $content = preg_replace('/(<a(.*)\s+href=")(\{\$urls\.base_url\})("([^>]*)>)/', '$1'.$replacement.'$4', $content);
                        file_put_contents($file, $content);
                    }
                }
            }
        }
    }
}