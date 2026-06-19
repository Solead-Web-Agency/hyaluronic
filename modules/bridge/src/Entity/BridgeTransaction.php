<?php
/**
 * Copyright Bridge
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@202-ecommerce.com so we can send you a copy immediately.
 *
 * @author    202 ecommerce <tech@202-ecommerce.com>
 * @copyright Bridge
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace BridgeAddon\Entity;

use ObjectModel;

class BridgeTransaction extends ObjectModel
{
    /** @var int */
    public $id_bridge_transaction;

    /** @var string */
    public $id_transaction;

    /** @var int */
    public $id_cart;

    /** @var string */
    public $status;

    /** @var int */
    public $id_order;

    /** @var int */
    public $id_bank;

    /** @var int */
    public $url;

    /** @var string Date */
    public $date_add;

    /** @var string Date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'bridge_transactions',
        'multilang' => false,
        'primary' => 'id_bridge_transaction',
        'fields' => [
            'id_transaction' => [
                'type' => self::TYPE_STRING,
                'required' => false,
            ],
            'id_cart' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'status' => [
                'type' => self::TYPE_STRING,
                'required' => false,
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'id_bank' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'url' => [
                'type' => self::TYPE_STRING,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false,
            ],
        ],
    ];
}
