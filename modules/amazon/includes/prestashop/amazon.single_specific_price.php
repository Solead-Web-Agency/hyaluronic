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
class AmazonSingleSpecificPrice
{
    public $id_product;
    public $id_shop;
    public $id_currency;
    public $id_country;
    public $id_group;
    public $id_product_attribute;
    public $price;  // -1 means fixed price is not set
    public $from_quantity;
    public $reduction;  // discount value
    public $reduction_type;
    public $from;
    public $to;

    public function __construct($data)
    {
        foreach (get_object_vars($this) as $prop => $value) {
            if (isset($data[$prop])) {
                $this->$prop = $data[$prop];
            }
        }
    }

    public function getQuantity()
    {
        return $this->from_quantity;
    }

    public function getReduction()
    {
        if ($this->reduction_type == 'amount') {
            return sprintf('%.02f', $this->reduction);
        } elseif ($this->reduction_type == 'percentage') {
            return (int)($this->reduction * 100);
        }

        return $this->reduction;
    }

    public function haveFixedPrice()
    {
        return $this->price > 0;
    }

    public function priceAfterRule($priceBefore)
    {
        if ($this->reduction_type == 'amount') {
            return $priceBefore - $this->reduction;
        } elseif ($this->reduction_type == 'percentage') {
            return $priceBefore - $this->reduction * $priceBefore;
        }

        return $priceBefore;
    }
}