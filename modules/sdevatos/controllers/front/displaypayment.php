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

use ScaleDEV\SdevAtos\SdevTools;
use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevConfiguration;

class SdevAtosDisplayPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->module = Module::getInstanceByName(SdevModule::LNAME);
        parent::initContent();
        if (Tools::getValue('ajax') === '1' && Tools::getValue('action')
            && method_exists($this, 'ajaxProcess'.Tools::ucfirst(Tools::getValue('action')))
            && Tools::getValue('token') == SdevConfiguration::get('TOKEN')
        ) {
            $this->{'ajaxProcess'.Tools::ucfirst(Tools::getValue('action'))}();
        }
    }

    /**
     * Action payment.
     */
    public function ajaxProcessActionPayment()
    {
        $Ws = new SdevAtosWs();
        $form = (array)$Ws->getForm((int)SdevTools::decodeRequest()['id_payment_method']);
        if (array_key_exists('is_success', $form) && (bool)$form['is_success']) {
            if ($form['sips_version'] != '1.0' && $form['sips_version'] != '2.0') {
                SdevTools::sendAjaxResult(array(
                    'is_success' => false,
                    'has_error' => true,
                    'error' => 'SipsVersion'
                ));
            }

            if ($form['sips_version'] == '2.0') {
                if (Tools::version_compare(_PS_VERSION_, '1.7')) {
                    $redirect = true;
                    $data_exploded = explode('&', $form['data']);

                    SdevAtosModule::smartyAssign(array(
                        'url' => (bool)$form['is_test_mode'] ? SdevAtosWs::URL_TEST : SdevAtosWs::URL_PROD,
                        'data_exploded' => (array)$data_exploded
                    ));

                    $template = $this->module->display(dirname(__FILE__).'../../../'.$this->module->name.'.php', SdevAtosModule::HOOK_PATH.'front/template_sips2.tpl');

                    unset($data_exploded);
                } else {
                    $redirect = false;
                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLINFO_HEADER_OUT => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $form['data'],
                        CURLOPT_URL => (bool)$form['is_test_mode'] ? SdevAtosWs::URL_TEST : SdevAtosWs::URL_PROD
                    ));
                    $template = curl_exec($ch);
                    curl_close($ch);
                }
            }

            SdevTools::sendAjaxResult(array(
                'redirect' => isset($redirect) && (bool)$redirect ? true : false,
                'is_success' => true,
                'has_error' => false,
                'template' => isset($template) ? $template : $form['template']
            ));
        } elseif (array_key_exists('has_error', $form) && (bool)$form['has_error']) {
            SdevTools::sendAjaxResult((array)$form);
        } else {
            SdevTools::sendAjaxResult(array(
                'is_success' => false,
                'has_error' => true,
                'error' => 'UnknownError'
            ));
        }
    }
}
