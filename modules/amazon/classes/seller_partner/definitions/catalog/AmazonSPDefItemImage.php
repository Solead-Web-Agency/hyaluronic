<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
class AmazonSPDefItemImage extends AmazonSPDefObject
{
    const MAIN = 'MAIN';
    const ALT_1 = 'PT01';
    const ALT_2 = 'PT02';
    const ALT_3 = 'PT03';
    const ALT_4 = 'PT04';
    const ALT_5 = 'PT05';
    const ALT_6 = 'PT06';
    const ALT_7 = 'PT07';
    const ALT_8 = 'PT08';
    const SWCH = 'SWCH';
    
    // All are required
    public $variant;    // MAIN, PT01, PT02, PT03, PT04, PT05, PT06, PT07, PT08, SWCH
    public $link;       // string
    public $height;     // integer
    public $width;      // integer
}
