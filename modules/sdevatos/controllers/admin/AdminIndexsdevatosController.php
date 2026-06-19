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

use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevTools;

require_once(dirname(__FILE__).'../../../autoload.php');

class AdminIndexsdevatosController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        parent::__construct();
    }

    /**
     * Render list.
     */
    public function renderList()
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7')) {
            $current_url = (getenv('HTTPS') || getenv('HTTP_X_FORWARDED_PROTO') == 'https' ? 'https://' : 'http://').getenv('HTTP_HOST').getenv('REQUEST_URI');
            $good_url = SdevTools::getShopDomain(true, true).basename(_PS_ADMIN_DIR_).'/'.$this->context->link->getAdminLink(Tools::getValue('controller'));
            if ($current_url != $good_url) {
                Tools::redirect($good_url);
                exit;
            }
        }

        SdevModule::smartyAssign(array(
            'links' => array('index' => $this->context->link->getAdminLink('AdminIndex'.$this->module->name))
        ));

        return $this->module->display(_PS_MODULE_DIR_.$this->module->name, 'views/templates/admin/base.tpl');
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
            'js_translations' => (array)$this->module->getJsDefL(),
            'ps15' => Tools::version_compare(_PS_VERSION_, '1.6')
        ));
    }

    /**
     * Get tabs.
     *
     * @return array
     */
    public function ajaxProcessGetTabs()
    {
        $select = 'SELECT *
            FROM `'._DB_PREFIX_.'tab` t
                LEFT JOIN `'._DB_PREFIX_.'tab_lang` tl
                    ON (t.id_tab = tl.id_tab)
            WHERE t.module = \''.$this->module->name.'\' AND tl.id_lang = '.SdevTools::getIdLang()
            . ' ORDER BY t.id_tab ASC';
        $response = Db::getInstance()->executeS($select);

        $tabs = array();
        foreach ($response as $line) {
            if ($this->id != $line['id_tab']) {
                $tabs[$line['class_name']] = Profile::getProfileAccess($this->context->employee->id_profile, $line['id_tab']);
                $tabs[$line['class_name']]['name'] = $line['name'];
                $tabs[$line['class_name']]['link'] = $this->context->link->getAdminLink($line['class_name']);
            }
        }

        SdevTools::sendAjaxResult(array(
            'tabs' => (array)$tabs
        ));
    }
}
