<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    SeoSA    <885588@bk.ru>
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__).'/classes/tools/config.php');

class MassEditProduct extends Module
{
    public $module_container;

    public function __construct()
    {
        $this->name = 'masseditproduct';
        $this->tab = 'front_office_features';
        $this->version = '2.0.15';
        $this->author = 'SeoSa';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->module_container = ModuleContainerMEP::getInstance($this)->setTabs(
            array(
                array(
                    'tab' => 'AdminMassEditProduct',
                    'parent' => 'AdminCatalog',
                    'name' => array(
                        'en' => 'Mass edit product',
                        'ru' => 'Массовое редактирование товаров',
                        'es' => 'Producto de edición de masas',
                        'fr' => 'En masse produits d édition de',
                    ),
                ),
                array(
                    'tab' => 'AdminSeoSaExtendedFeatures',
                    'parent' => 'AdminCatalog',
                    'name' => array(
                        'en' => 'Extended features',
                    ),
                    'visible' => false,
                )
            )
        )->setClasses(array(
            'TemplateProductsMEP',
            'TemplateProductsProductMEP'
        ))->setDocumentation(
            true
        );

        $this->tabs = $this->module_container->getTabs();

        parent::__construct();
        $this->displayName = $this->l('Mass edit product');
        $this->description = $this->l('Mass edit product');
        $this->module_key = '6f052f2d8d49a03ec1d864d012e19ad7';
    }

    public function install()
    {
        return parent::install() && $this->module_container->install() && $this->module_container->addColumn();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->module_container->uninstall();
    }

    public function getContent()
    {
        return $this->module_container->getDocumentation();
    }
}
