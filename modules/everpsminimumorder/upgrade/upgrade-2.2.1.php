<?php
/**
 * Project : everpsminimumorder
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_1()
{
    return Configuration::updateValue(
        'EVERMINIMUM_CURRENCY',
        (int)Configuration::get('PS_CURRENCY_DEFAULT')
    );
}
