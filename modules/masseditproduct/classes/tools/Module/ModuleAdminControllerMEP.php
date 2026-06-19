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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2012-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class ModuleAdminControllerMEP extends ModuleAdminController
{
    /**
        protected $position_identifier = 'id_object';
    */

    /**
     * @var bool
     */
    public $redirect_to_controller = false;
    /**
     * @var int
     */
    public $thumbnail_list_size = 50;

    public function __construct()
    {
        if ($this->redirect_to_controller) {
            $this->context = Context::getContext();
            $this->table = 'configuration';
            $this->identifier = 'id_configuration';
            $this->className = 'Configuration';
            $this->lang = false;
            $this->bootstrap = true;
            $this->display = 'list';
        }
        /**
            $this->list_id = 'table';
            $this->_defaultOrderBy = 'position';
            $this->_defaultOrderWay = 'ASC';
        */
        parent::__construct();

        if ($this->redirect_to_controller) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink($this->redirect_to_controller, true)
            );
        }
        SmartyMEP::registerSmartyFunctions();
        ToolsModuleMEP::globalAssignVar();
        ToolsModuleMEP::convertJSONRequestToPost();
    }

    public function assignModuleTabAdminLink()
    {
        $this->context->smarty->assign(
            'link_to_documentation',
            HelperModuleMEP::getModuleTabAdminLink()
        );
    }

    public function setMedia()
    {
        parent::setMedia();
        if (property_exists($this, 'position_identifier')) {
            $this->context->controller->addJqueryUI('ui.sortable');
        }
    }

    public function renderList()
    {
        if ($this->module->documentation) {
            $this->assignModuleTabAdminLink();
            return ToolsModuleMEP::fetchTemplate(
                'admin/documentation_row.tpl'
            ).parent::renderList();
        } else {
            return parent::renderList();
        }
    }

    public function renderView()
    {
        if ($this->module->documentation) {
            $this->assignModuleTabAdminLink();
            return ToolsModuleMEP::fetchTemplate(
                'admin/documentation_row.tpl'
            ).parent::renderView();
        } else {
            return parent::renderView();
        }
    }

    public function renderForm()
    {
        if ($this->module->documentation) {
            $this->assignModuleTabAdminLink();
            return ToolsModuleMEP::fetchTemplate(
                'admin/documentation_row.tpl'
            ).parent::renderForm();
        } else {
            return parent::renderForm();
        }
    }

    public function initAngular()
    {
        ToolsModuleMEP::autoloadCSS($this->module->getPathUri().'views/css/autoload/');
        $this->context->controller->addJS(array(
            $this->module->getPathUri().'views/js/lib/angular/vendor/jquery.fileStyle.js',
            $this->module->getPathUri().'views/js/lib/angular/vendor/jquery.binarytransport.js',
            $this->module->getPathUri().'views/js/lib/angular/vendor/jquery.binarytransport.js',
            $this->module->getPathUri().'views/js/lib/angular/vendor/angular.js'
        ));

        AngularAppMEP::getInstance(
            $this->module->getPathUri().'views/js/lib/angular/vendor/packages/lazy-load/'
        )->autoloadApp();
        AngularAppMEP::getInstance(
            $this->module->getPathUri().'views/js/lib/angular/'
        )->autoloadApp();
    }

    public $return = array();
    public function ajaxProcessApi()
    {
        ErrorHandlerMEP::setErrorHandler();
        HelperModuleMEP::createAjaxApiCall($this);
    }

    protected function assignAngularFiles()
    {
        $angular_templates_folder = $this->module->getLocalPath().'views/templates/admin/angular-templates';
        $angular_templates = ToolsModuleMEP::globRecursive($angular_templates_folder.'/**.tpl');

        foreach ($angular_templates as &$path) {
            $path = str_replace($angular_templates_folder.'/', '', $path);
        }
        unset($path);

        $this->context->smarty->assign('angular_templates', $angular_templates);
        $this->context->smarty->assign(
            'path_angular',
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/'
        );
    }

    public function processSave()
    {
        /**
         * @var ObjectModelMEP $object
         */
        $object = parent::processSave();
        $object_name = $this->className;
        $property = 'has_image';
        if (Validate::isLoadedObject($object)
            && property_exists($object_name, $property) && $object_name::$$property) {
            $property = 'lang_image';
            if (property_exists($object_name, $property) && $object_name::$$property) {
                foreach (ToolsModuleMEP::getLanguages(false) as $l) {
                    $image = Tools::fileAttachment('image_'.$l['id_lang']);
                    if ($image['tmp_name'] && ToolsModuleMEP::checkImage($image['tmp_name'])) {
                        $object->uploadImage($image['tmp_name'], $l['id_lang']);
                    }
                }
            } else {
                $image = Tools::fileAttachment('image');
                if ($image['tmp_name'] && ToolsModuleMEP::checkImage($image['tmp_name'])) {
                    $object->uploadImage($image['tmp_name']);
                }
            }
        }

        return $object;
    }

    public function getFieldsValue($obj)
    {
        $fields_value = parent::getFieldsValue($obj);
        $object_name = $this->className;
        $property = 'has_image';
        if (property_exists($object_name, $property) && $object_name::$$property) {
            $fields_value['image'] = $obj->getImage();
        }
        return $fields_value;
    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        if (array_key_exists('image', $this->fields_list)) {
            $object_name = $this->className;
            $property = 'lang_image';
            $id_lang = null;
            if (property_exists($object_name, $property) && $object_name::$$property) {
                $id_lang = $this->context->language->id;
            }

            foreach ($this->_list as &$row) {
                $row['image'] = forward_static_call(
                    array($this->className, 'getObjectThumbnail'),
                    $row[$this->identifier],
                    $this->thumbnail_list_size,
                    $id_lang
                );
            }
        }
    }

    public function ajaxProcessDeleteImage()
    {
        $id_lang = Tools::getValue('id_lang', null);
        /**
         * @var ObjectModelMEP $object
         */
        $object = $this->loadObject(true);
        $object->deleteImg($id_lang);
        die(Tools::jsonEncode(array(
            'hasError' => false
        )));
    }

    public function ajaxProcessUpdatePositions()
    {
        if ($this->tabAccess['edit'] === '1') {
            $way = (int)Tools::getValue('way');
            $id_object = (int)Tools::getValue('id');
            $positions = Tools::getValue($this->table);

            $new_positions = array();
            foreach ($positions as $v) {
                if (!empty($v)) {
                    $new_positions[] = $v;
                }
            }

            foreach ($new_positions as $position => $value) {
                $pos = explode('_', $value);

                if (isset($pos[2]) && (int)$pos[2] === $id_object) {
                    $object_name = $this->className;
                    if ($object = new $object_name((int)$pos[2])) {
                        if (isset($position) && $object->updatePosition($way, $position, $id_object)) {
                            echo 'ok position '.(int)$position.' for item '.(int)$pos[1].'\r\n';
                        } else {
                            echo '{"hasError" : true, "errors" : "Can not update item '
                                .(int)$id_object.' to position '.(int)$position.' "}';
                        }
                    } else {
                        echo '{"hasError" : true, "errors" : "This item ('.(int)$id_object.') can t be loaded"}';
                    }
                    break;
                }
            }
        }
        die();
    }
}
