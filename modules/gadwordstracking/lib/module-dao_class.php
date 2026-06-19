<?php
/**
 * module-dao_class.php file defines method of management of DATA ACCESS OBJECT
 */

class BT_GactModuleDao
{
    /**
     * returns all the product attribute ids
     *
     * @param int  $iProductId   the id of the product
     * @param bool $bDefaultOnly default one
     * @return array product attribute id list
     */
    public static function getProductAttributesIds(
        $iProductId,
        $bDefaultOnly = false
    ) {
        $sQuery = 'SELECT pa.id_product_attribute as id, pa.default_on as default_on'
          . ' FROM `' . _DB_PREFIX_ . 'product_attribute` pa'
          . ' WHERE pa.id_product = ' . (int)$iProductId;

        if ($bDefaultOnly) {
            $sQuery .= ' AND pa.default_on = 1';
        }

        return Db::getInstance()->executeS($sQuery);
    }

    /**
     * set order to our table
     *
     * @param int    $iCartId  the id of the cart
     * @param bool   $bSent    the send status
     * @param int    $iOrderId the id order => could be set to 0 when we register the id cart during the last checkout step
     * @param bool   $bConfirmation
     * @param string $sType
     * @return bool
     */
    public static function addOrder(
        $iCartId,
        $bSent,
        $iOrderId,
        $bConfirmation = 0,
        $sType = 'confirmation'
    ) {
        $sQuery = 'INSERT INTO ' . _DB_PREFIX_
          . Tools::strtolower(_GACT_MODULE_NAME)
          . '_orders  (`cart_id`, `type`, `is_sent`, `order_id`, `confirmation`) VALUES ('
          . (int)$iCartId . ', "' . pSQL($sType) . '", ' . (int)$bSent
          . ', ' . (int)$iOrderId . ', ' . (int)$bConfirmation . ')';

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * update is sent value on our table
     *
     * @param int    $iOrderId      : the order id
     * @param bool   $bSent         : status of the order
     * @param bool   $bConfirmation the id order
     * @return bool
     */
    public static function updateOrder(
        $iOrderId,
        $bSent = null,
        $bConfirmation = null
    ) {
        $result = false;

        // update the order ID or the sent status
        if ($bSent !== null
            || $bConfirmation !== null
        ) {
            $sQuery = 'UPDATE ' . _DB_PREFIX_ . Tools::strtolower(_GACT_MODULE_NAME) . '_orders SET ';

            // update the sent
            if ($bSent !== null) {
                $sQuery .= '`is_sent` = ' . (int)$bSent . ', ';
            }
            // update the sent
            if ($bConfirmation !== null) {
                $sQuery .= '`confirmation` = ' . (int)$bConfirmation . ', ';
            }

            if (substr($sQuery, -2, 2) == ', ') {
                $sQuery = substr($sQuery, 0, strlen($sQuery) - 2);
            }

            $sQuery .= '  WHERE `order_id` = ' . $iOrderId;

            $result = Db::getInstance()->Execute($sQuery);
        }

        return $result;
    }


    /**
     * if the order is already in our table
     *
     * @param int $iOrderId id_order
     * @return bool
     */
    public static function checkOrder(
        $iOrderId,
        $bIsSent = false
    ) {
        $sQuery = 'SELECT order_id, is_sent as sent FROM ' . _DB_PREFIX_ . Tools::strtolower(_GACT_MODULE_NAME) . '_orders WHERE `order_id` = ' . (int)$iOrderId;

        $aData = Db::getInstance()->ExecuteS($sQuery);

        if (!empty($aData[0])) {
            if (!empty($bIsSent)) {
                $bReturn = $aData[0]['sent'] == 0 ? false : true;
            } else {
                $bReturn = true;
            }
        } else {
            $bReturn = false;
        }

        return $bReturn;
    }
}
