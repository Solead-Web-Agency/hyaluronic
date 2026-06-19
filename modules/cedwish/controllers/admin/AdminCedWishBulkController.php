<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   cedwish
 */

class AdminCedWishBulkController extends ModuleAdminController
{
    public $productHelper;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $link = new LinkCore();
            $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => $link->getAdminLink('AdminCedWishProduct'),
                'desc' => $this->l('Back To Product List', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function initContent()
    {
        parent::initContent();
        try {
            $content = null;
            $link = new LinkCore();
            $controllerUrl = $link->getAdminLink('AdminFacebookBulkUpload');
            $token = $this->token;
            $this->context->smarty->assign(array('controllerUrl' => $controllerUrl));
            $this->context->smarty->assign(array('token' => $token));
            if (Tools::getIsset('cron_bulk_upload') && Tools::getValue('cron_bulk_upload')) {
                $this->context->smarty->assign(
                    array(
                        'chuck_limit' => Configuration::get('CED_WISH_CRON_CHUNK_SIZE')
                    )
                );
                $content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/form/bulk_upload_cron.tpl'
                );
                $query = new DbQuery();
                $query->select("cpp.id_product");
                $query->from("cedwish_profile_product", "cpp");
                $query->leftJoin(
                    "cedwish_product",
                    "cp",
                    "(cpp.id_product = cp.id_product) "
                );
                $query->where(" (cp.id_product_attribute =0) AND (cp.enabled =1) 
                    AND (cp.marketplace_id IS NULL)");
                $query->orderBy(
                    " cpp.id_product ASC"
                );

                $products = Db::getInstance()->executeS($query);

                if (!empty($products)) {
                    $products = array_column($products, 'id_product');
                    $products = array_chunk($products, 499);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'upload',
                            $product
                        );
                    }
                }
            }
            if (Tools::getIsset('cron_bulk_update') && Tools::getValue('cron_bulk_update')) {
                $this->context->smarty->assign(
                    array(
                        'chuck_limit' => Configuration::get('CED_WISH_CRON_CHUNK_SIZE')
                    )
                );
                $query = new DbQuery();
                $query->select("cpp.id_product");
                $query->from("cedwish_profile_product", "cpp");
                $query->leftJoin(
                    "cedwish_product",
                    "cp",
                    "(cpp.id_product = cp.id_product) "
                );
                $query->where(" (cp.id_product_attribute =0) AND (cp.enabled =1) 
                    AND (cp.marketplace_id IS NOT NULL)");
                $query->orderBy(
                    " cpp.id_product ASC"
                );
                $products = Db::getInstance()->executeS($query);

                if (!empty($products)) {
                    $products = array_column($products, 'id_product');
                    $products = array_chunk($products, 499);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'update',
                            $product
                        );
                    }
                }
                $content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/form/bulk_upload_cron.tpl'
                );
            }

            if (Tools::getIsset('cron_bulk_sync_inventory') && Tools::getValue('cron_bulk_sync_inventory')) {
                $this->context->smarty->assign(
                    array(
                        'chuck_limit' => Configuration::get('CED_WISH_CRON_CHUNK_SIZE')
                    )
                );
                $query = new DbQuery();
                $query->select("cpp.id_product");
                $query->from("cedwish_profile_product", "cpp");
                $query->leftJoin(
                    "cedwish_product",
                    "cp",
                    "(cpp.id_product = cp.id_product) "
                );
                $query->where(" (cp.id_product_attribute =0) AND (cp.enabled =1) 
                    AND (cp.marketplace_id IS NOT NULL)");
                $query->orderBy(
                    " cpp.id_product ASC"
                );
                $products = Db::getInstance()->executeS($query);
                if (!empty($products)) {
                    $products = array_column($products, 'id_product');
                    $products = array_chunk($products, 499);
                    foreach ($products as $product) {
                        CedWishQueue::addQueue(
                            'stock',
                            $product
                        );
                    }
                }
                $content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/form/bulk_upload_cron.tpl'
                );
            }

            if (Tools::getIsset('brand_tagging') && Tools::getValue('brand_tagging')) {
                $this->addJqueryPlugin('autocomplete');
                if (Tools::getValue('brand_tagging') == 'redirected') {
                    $productQueueIds = CedWishQueue::getQueueByType('brand_tagging');
                    $productIds = array();
                    if (!empty($productQueueIds)) {
                        $productQueueIds = array_column($productQueueIds, 'queued_items');
                        if (!empty($productQueueIds)) {
                            foreach ($productQueueIds as $Ids) {
                                $Ids = json_decode(Tools::getDescriptionClean($Ids), true);
                                $productIds = array_merge($productIds, $Ids);
                            }
                        }
                        CedWishQueue::deleteQueueByType('brand_tagging');
                    }
                } else {
                    $productIds = array();
                }
                $brand_count = Db::getInstance()->getValue(
                    "SELECT COUNT(id_cedwish_brand) FROM `" . _DB_PREFIX_ . "cedwish_brand`"
                );
                $this->context->smarty->assign(
                    array(
                        'brand_count' => $brand_count,
                        'brand_tagging' => addslashes(json_encode($productIds)),
                        'controllerUrl' => $this->context->link->getAdminLink('AdminCedWishBulk'),
                    )
                );
                $content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/product/form/brand_tagging.tpl'
                );
            }

            $this->context->smarty->assign(array(
                'content' => $this->content . $content
            ));
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function ajaxProcessBulkBrandTagging()
    {
        $params = Tools::getAllValues();
        $response = array('success' => false, 'message' => 'Failed to assign Brands.');
        if (isset($params['selected']) && !empty($params['selected']) && isset($params['wish_brand_id'])
            && $params['wish_brand_id'] && isset($params['brand_name']) && $params['brand_name']) {
            $wish_brand_id = $params['wish_brand_id'];
            $product_ids = $params['selected'];
            $product_brands = array();
            foreach ($product_ids as $product_id) {
                $product_brands[] = array(
                  'id_product' =>   (int)$product_id,
                  'id_wish_brand' =>   pSQL($wish_brand_id),
                );
            }
            try {
                Db::getInstance()->insert(
                    'cedwish_product_brand',
                    $product_brands,
                    false,
                    true,
                    Db::REPLACE,
                    true
                );
                $response = array('success' => true, 'message' => 'Brand assigned successfully.');
            } catch (PrestaShopDatabaseException $e) {
                $response = array('success' => true, 'message' => $e->getMessage());
                die(json_encode($response));
            }
        }
        die(json_encode($response));
    }

    public function ajaxProcessBrandTagging()
    {
        $q = Tools::getValue('q');
        $data = Db::getInstance()->executeS(
            "SELECT id, name FROM `" . _DB_PREFIX_ . "cedwish_brand` WHERE name LIKE '%" . pSQL($q) . "%' LIMIT 20"
        );
        die(json_encode($data));
    }

    public function ajaxProcessGetBrand()
    {
        $api = new CedWishApi();
        $id_min = Tools::getValue('id_min', '');
        $params = array(
            'id_min' => $id_min,
            'limit' => 500
        );
        $response = $api->getBrand($params);
        try {
            if (!empty($response) && isset($response['code']) && ($response['code']==0)) {
                if (count($response['data'])==500) {
                    $response = $response['data'];
                    foreach ($response as $key => $resp) {
                        $response[$key]['name'] = pSQL(addslashes($resp['name']));
                        unset($response[$key]['website']);
                    }

                    Db::getInstance()->insert(
                        'cedwish_brand',
                        $response
                    );
                    $last = end($response);
                    die(json_encode($last));
                } else {
                    die(json_encode(array('success' => true, 'message' =>  'All brands fetched')));
                }
            } elseif (isset($response['message'])) {
                die(json_encode(array('success' => false, 'message' =>  $response['message'])));
            } else {
                die(json_encode(array('success' => false, 'message' =>  'Failed to get Brands')));
            }
        } catch (PrestaShopDatabaseException $e) {
            die(json_encode(array('success' => false, 'message' =>  $e->getMessage())));
        }
    }
}
