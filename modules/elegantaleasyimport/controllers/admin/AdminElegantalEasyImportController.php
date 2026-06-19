<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is controller for admin Menu
 */
class AdminElegantalEasyImportController extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();

        Tools::redirectAdmin($this->module->getAdminUrl());
    }
}
