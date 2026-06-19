<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

require_once dirname(__FILE__) . '/amazon.order_info.class.php';
require_once dirname(__FILE__) . '/AmazonOrderMkpShippingServices.php';

class AmazonOrderMkpInfo extends AmazonOrderInfo
{
    /** @var AmazonOrderMkpShippingServices */
    public $shipping_services;

    public function __construct($id = null)
    {
        parent::__construct($id);
        if ($this->id_order) {
            $this->getMkpInfo();
        }
    }

    /**
     * Prime
     * @return AmazonOrderMkpShippingServices|null
     */
    public function getShippingServices()
    {
        $idOrder = $this->id_order && $this->is_prime ? $this->id_order : null;
        $this->shipping_services = new AmazonOrderMkpShippingServices($idOrder);

        return $this->shipping_services;
    }

    protected function getMkpInfo()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_ORDERS . '` WHERE `id_order` = ' . (int)$this->id_order;
        $row = Db::getInstance()->getRow($sql);
        if ($row) {
            foreach ($row as $field => $value) {
                // todo: 'merchant_fulfillment' use the name 'shipping_services', fix it
                if (property_exists($this, $field)) {
                    if ($field != 'shipping_services') {
                        $this->{$field} = $value;
                    } else {
                        $this->shipping_method = $value;
                    }
                }
            }
        }
    }
}
