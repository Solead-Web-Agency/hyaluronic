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
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

/**
 * Debug a variable.
 *
 * @param mixed $var - Variable to debug.
 * @param bool $die - Die after debug.
 * @throws Exception
 */
function sdevdebug($var, $die = true)
{
    try {
        if (is_bool($die)) {
            $function = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? 'dump' : 'ppp';
            $function($var);
            if ((bool)$die) {
                die();
            }
        } else {
            throw new Exception('The parameter $die must be a boolean, '.gettype($die).' given !');
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

/**
 * Debug a variable then die.
 *
 * @param mixed $var - Variable to debug.
 * @throws Exception
 */
function sdevddd($var)
{
    sdevdebug($var);
}

/**
 * Debug a variable.
 *
 * @param mixed $var - Variable to debug.
 * @throws Exception
 */
function sdevppp($var)
{
    sdevdebug($var, false);
}
