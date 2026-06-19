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
class WebserviceSpecificManagementMarketplaceOrders implements WebserviceSpecificManagementInterface
{
    /** @var WebserviceOutputBuilder */
    protected $objOutput;
    protected $output;
    protected $urlSegment;

    /** @var WebserviceRequest */
    protected $wsObject;

    public function setObjectOutput(WebserviceOutputBuilder $obj)
    {
        $this->objOutput = $obj;

        return $this;
    }

    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    public function setWsObject(WebserviceRequest $obj)
    {
        $this->wsObject = $obj;

        return $this;
    }

    public function getWsObject()
    {
        return $this->wsObject;
    }

    public function setUrlSegment($segments)
    {
        $this->urlSegment = $segments;

        return $this;
    }

    public function getUrlSegment()
    {
        return $this->urlSegment;
    }

    public function manage()
    {
        $orderId = !empty($this->wsObject->urlSegment[1]) ? $this->wsObject->urlSegment[1] : null;
        if ($orderId) {
            if (!Validate::isUnsignedId($orderId)) {
                throw new WebserviceException('The order id is invalid. Please set a valid id of the marketplace order', array(60, 400));
            }
            $mpOrder = Db::getInstance()->getRow(
                'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . AmazonConstant::TABLE_MKP_ORDERS . '`
                WHERE `id_order` = ' . (int)$orderId
            );
            $objects = array(
                'empty' => new WSAmazonOrder(),
            );
            $objects[] = new WSAmazonOrder($mpOrder['id_order']);
            $this->output .= $this->objOutput->getContent($objects, $this->wsObject->schemaToDisplay, 'full', $this->wsObject->depth, WebserviceOutputBuilder::VIEW_DETAILS, false);
        } else {
            $mpOrders = Db::getInstance()->executeS(
                'SELECT `id_order`
                FROM `' . _DB_PREFIX_ . AmazonConstant::TABLE_MKP_ORDERS . '`
                ORDER BY `id_order`'
            );

            $objects = array(
                'empty' => new WSAmazonOrder(),
            );
            foreach ($mpOrders as $mpOrder) {
                $objects[] = new WSAmazonOrder($mpOrder['id_order']);
            }

            $this->output .= $this->objOutput->getContent($objects, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
        }
    }

    public function getContent()
    {
        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }
}

class WSAmazonOrder extends ObjectModel
{
    public static $definition = array(
        'table' => AmazonConstant::TABLE_MKP_ORDERS,
        'primary' => 'id_order',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT),
            'mp_order_id' => array('type' => self::TYPE_STRING),
            'marketplace_id' => array('type' => self::TYPE_STRING),
            'sales_channel' => array('type' => self::TYPE_STRING),
            'channel' => array('type' => self::TYPE_STRING),
            'buyer_name' => array('type' => self::TYPE_STRING),
            'earliest_ship_date' => array('type' => self::TYPE_DATE),
            'latest_ship_date' => array('type' => self::TYPE_DATE),
            'earliest_delivery_date' => array('type' => self::TYPE_DATE),
            'latest_delivery_date' => array('type' => self::TYPE_DATE),
            'fulfillment_center_id' => array('type' => self::TYPE_STRING),
        ),
    );

    protected $webserviceParameters = array(
        'objectsNodeName' => 'marketplace_orders',
        'objectNodeName' => 'marketplace_order',
        'fields' => array(
            'id_order' => array('xlink_resource' => 'orders'),
            'mp_order_id' => array(),
            'marketplace_id' => array(),
            'sales_channel' => array(),
            'channel' => array(),
            'buyer_name' => array(),
            'earliest_ship_date' => array(),
            'latest_ship_date' => array(),
            'earliest_delivery_date' => array(),
            'latest_delivery_date' => array(),
            'fulfillment_center_id' => array(),
        ),
    );
}
