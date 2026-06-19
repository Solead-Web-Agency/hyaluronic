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

require_once(dirname(__FILE__).'../../../autoload.php');

class AdminInfosdevatosController extends ModuleAdminController
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
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();
    }

    /**
     * Display a template.
     */
    public function ajaxProcessDisplay()
    {
        $has_overrides = false;
        $overrides = array();
        $classes_overrided = $this->module->getClassesFromDir('override/classes/', defined('_PS_HOST_MODE_'));
        if (isset($classes_overrided['Cart'])) {
            unset($classes_overrided['Cart']);
        }
        $controllers_overrided = $this->module->getClassesFromDir('override/controllers/', defined('_PS_HOST_MODE'));

        if ($this->module->hasOverrides()
            || $classes_overrided
            || $controllers_overrided
        ) {
            $has_overrides = true;
            $overrides = array(
                'classes' => $classes_overrided,
                'controllers' => $controllers_overrided
            );
        }

        SdevModule::smartyAssign(array(
            'has_overrides' => (bool)$has_overrides,
            'overrides' => (array)$overrides,
            'doc_list' => (array)SdevModule::getDocList()
        ));

        die($this->module->display(SdevModule::DIR, 'views/templates/admin/info.tpl'));
    }
}
