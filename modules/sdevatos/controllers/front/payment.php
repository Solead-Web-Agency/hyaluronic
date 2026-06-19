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

use ScaleDEV\SdevAtos\SdevConfiguration;
use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevTools;

class SdevAtosPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (Tools::getValue('token') != SdevConfiguration::get('TOKEN')) {
            return;
        }

        parent::initContent();

        /*
         * @since 1.1.0
         * Defines whether to display the debug mode according to the current IP address.
         */
        $isDebugMode = (bool)SdevConfiguration::get('DEBUG_MODE');
        if (!SdevConfiguration::get('IP_FILTERING')) {
            $isDebugMode = false;
        } else {
            $ipAddressesList = explode(',', SdevConfiguration::get('IP_FILTERING'));
            if (!$ipAddressesList || !is_array($ipAddressesList) || !in_array(SdevTools::getIpAddress(), $ipAddressesList)) {
                $isDebugMode = false;
            }
        }

        $form = (new SdevAtosWs())->getForm((int)Tools::getValue($this->module->name.'-payment-method-id'));
        $this->context->smarty->assign(array(
            'sdevatosTemplate' => isset($form['template']) ? $form['template'] : false,
            /*
             * @since 1.1.0
             * Sends these vars to display the debug mode and/or the error.
             */
            'sdevatosIsDebugMode' => (bool)$isDebugMode,
            'sdevatosError' => isset($form['error']) ? $form['error'] : false,
            'sdevatosErrorMessage' => isset($form['errorMessage']) ? $form['errorMessage'] : false,
        ));
        $this->setTemplate('module:'.SdevModule::LNAME.'/views/templates/front/payment.tpl');
    }
}
