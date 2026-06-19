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

class SdevAtosjsModuleFrontController extends ModuleFrontController
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
     * Get JavaScript translations.
     *
     * @return array
     */
    public function ajaxProcessGetJsDefL()
    {
        SdevTools::sendAjaxResult(array(
            'is_js_translations_loaded' => true,
            'js_translations' => (array)$this->module->getJsDefL()
        ));
    }
}
