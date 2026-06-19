<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class CartReminderProductInfo
{
    /**
     * Allow to check if $linkRewrite is an array or not and only return a valid value
     *
     * @param array|string $linkRewrite
     *
     * @return string
     */
    public function checkLinkRewrite($linkRewrite)
    {
        if (is_array($linkRewrite)) {
            $aFilteredArray = array_filter($linkRewrite);
            $linkRewrite = current($aFilteredArray);
        }

        if (is_array($linkRewrite)) {
            throw(new InvalidArgumentException('Array to string conversion'));
        }

        return (string) $linkRewrite;
    }

    /**
     * Return random products Ids
     *
     * @param  int $iLimit
     *
     * @return array
     */
    public function getRandomProducts($iLimit)
    {
        return Db::getInstance()->executeS('SELECT id_product, 1 as quantity FROM `'._DB_PREFIX_.'product` WHERE active = 1 ORDER BY RAND() LIMIT '.(int)$iLimit);
    }

    /**
     * Return Products IDs from a cart_id
     *
     * @param  int $iLimit
     *
     * @return array
     */
    public function getProductsFromCartId($iCartId)
    {
        return Db::getInstance()->executeS(
            'SELECT cp.id_product, 
                    cp.quantity 
            FROM `'._DB_PREFIX_.'cart_product` cp
            INNER JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
            WHERE 
                p.active = 1
                AND cp.id_cart = '.(int) $iCartId
        );
    }

    /**
     * Prepare smarty variable products
     *
     * @param  array $aRandomProductIds
     *
     * @return array $aProductList
     */
    public function prepareProductListForTemplate($aRandomProductIds, $iShopId = null, $iLangId = null)
    {
        $oProductInfo = new CartReminderProductInfo();
        $context = Context::getContext();
        $aProductList = array();

        foreach($aRandomProductIds as $aProductId) {
            $iProductId = (int)$aProductId['id_product'];
            $iQuantity = (int)$aProductId['quantity'];
            $oProduct = new Product($iProductId, null, $iLangId, $iShopId);
            // get Product price with taxes
            $fProductPrice = $iQuantity * $oProduct->getPriceStatic($iProductId);

            $aGetCoverImage = Image::getCover($iProductId);
            $aProductToPush = array(
                'id_product' => $iProductId,
                'name' => $oProduct->name,
                'description' => $oProduct->description_short,
                'price' => Tools::displayPrice($fProductPrice),
                'amount' => $iQuantity,
                'link' => $context->link->getProductLink($iProductId, null, null, null, $iLangId, $iShopId),
                'image' => $context->link->getImageLink($oProductInfo->checkLinkRewrite($oProduct->link_rewrite), $aGetCoverImage['id_image'])
            );
            array_push($aProductList, $aProductToPush);
            unset($oProduct);
        }

        return $aProductList;
    }
}