<?php
/**
 * The file is controller. Do not modify the file if you want to upgrade the module in future
 * 
 * @author    Globo Jsc <contact@globosoftware.net>
 * @copyright 2020 Globo., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

class AdminGloboupsellController extends ModuleAdminController
{
    public function __construct()
    {
        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminGmktDashboard'));
        exit();
    }
}
