<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevMonetico
 * Support by mail : contact@scaledev.fr
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use ScaleDEV\SdevAtos\SdevDbTools;

function addMultiShops()
{
    $is_success = true;

    if (!(bool)SdevAtosPaymentMethod::installSQL()) {
        $is_success = false;
    }

    $shops = Shop::getShops(false, null, true);
    $methods = SdevAtosPaymentMethod::read();

    foreach (array_keys($methods) as $method_id) {
        $method = new SdevAtosPaymentMethod((int)$method_id);
        $method->addShops($shops);
    }

    return $is_success;
}

function upgrade_module_1_2_0($object)
{
    return SdevDbTools::removeColumn('sdevatos_contract', 'id_shop_group')
        && SdevDbTools::removeColumn('sdevatos_contract', 'id_shop')
        && SdevDbTools::removeColumn('sdevatos_payment_method', 'id_shop_group')
        && SdevDbTools::removeColumn('sdevatos_payment_method', 'id_shop')
        && addMultiShops();
}
