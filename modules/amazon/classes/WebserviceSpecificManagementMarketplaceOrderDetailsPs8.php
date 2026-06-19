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
class WebserviceSpecificManagementMarketplaceOrderDetails implements WebserviceSpecificManagementInterface
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
        $orderItemId = !empty($this->wsObject->urlSegment[1]) ? $this->wsObject->urlSegment[1] : null;
        if ($orderItemId) {
            if (!Validate::isUnsignedId($orderItemId)) {
                throw new WebserviceException('The order item id is invalid. Please set a valid id of the marketplace order', array(60, 400));
            }
            $mpOrderItem = Db::getInstance()->getRow(
                'SELECT `id_order_detail`
                FROM `' . _DB_PREFIX_ . AmazonConstant::TABLE_MKP_ORDER_DETAILS . '`
                WHERE `id_order_detail` = ' . (int)$orderItemId
            );
            $objects = array(
                'empty' => new WSAmazonOrderDetail(),
            );
            $objects[] = new WSAmazonOrderDetail($mpOrderItem['id_order_detail']);
            $this->output .= $this->objOutput->getContent($objects, $this->wsObject->schemaToDisplay, 'full', $this->wsObject->depth, WebserviceOutputBuilder::VIEW_DETAILS, false);
        } else {
            $mpOrderDetails = Db::getInstance()->executeS(
                'SELECT `id_order_detail`
                FROM `' . _DB_PREFIX_ . AmazonConstant::TABLE_MKP_ORDER_DETAILS . '`
                ORDER BY `id_order_detail`'
            );

            $objects = array(
                'empty' => new WSAmazonOrderDetail(),
            );
            foreach ($mpOrderDetails as $mpOrderItem) {
                $objects[] = new WSAmazonOrderDetail($mpOrderItem['id_order_detail']);
            }

            $this->output .= $this->objOutput->getContent($objects, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
        }
    }

    public function getContent()
    {
        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }
}

class WSAmazonOrderDetail extends ObjectModel
{
    public static $definition = array(
        'table' => AmazonConstant::TABLE_MKP_ORDER_DETAILS,
        'primary' => 'id_order_detail',
        'fields' => array(
            'id_order_detail' => array('type' => self::TYPE_INT),
            'order_item_id' => array('type' => self::TYPE_STRING),
            'id_order' => array('type' => self::TYPE_INT),
            'mp_order_id' => array('type' => self::TYPE_STRING),
            'id_product' => array('type' => self::TYPE_INT),
            'id_product_attribute' => array('type' => self::TYPE_INT),
            'quantity' => array('type' => self::TYPE_INT),
            'sku' => array('type' => self::TYPE_STRING),
            'asin' => array('type' => self::TYPE_STRING),
            'customization' => array('type' => self::TYPE_STRING),
        ),
    );

    protected $webserviceParameters = array(
        'objectsNodeName' => 'marketplace_order_details',
        'objectNodeName' => 'marketplace_order_detail',
        'fields' => array(
            'id_order_detail' => array('xlink_resource' => 'order_details'),
            'order_item_id' => array(),
            'id_order' => array('xlink_resource' => 'orders'),
            'mp_order_id' => array(),
            'id_product' => array('xlink_resource' => 'products'),
            'id_product_attribute' => array('xlink_resource' => 'combinations'),
            'quantity' => array(),
            'sku' => array(),
            'asin' => array(),
            'customization' => array(),
        ),
    );
}
