<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';

class AdminCedWishColorMappingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cedwish_color_mapping';
        $this->identifier = 'id_cedwish_color_mapping';
        $this->_orderBy = 'id_cedwish_color_mapping';
        $this->_orderWay = 'DESC';
        $this->_select .= 'wish_option_id as temp_wish_option_id';
        $this->list_no_link = true;
        $this->addRowAction('edit');
        $this->addRowAction('remove');
        parent::__construct();
        $this->fields_list = array(
            'id_cedwish_color_mapping' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
            ),
            'wish_option_id' => array(
                'title' => $this->l('Wish Attribute Code'),
                'type' => 'text',
            ),
            'store_option_id' => array(
                'title' => $this->l('Store Attribute Id'),
                'type' => 'text',
                'callback' => 'storeColorName'
            ),
        );
        if (Tools::getIsset('removewish_option_mapping')
            && Tools::getValue('removewish_option_mapping')
            && Tools::getValue('id')
        ) {
            $status = $this->deleteAttributeMap(Tools::getValue('id'));
            if ($status) {
                $this->confirmations[] = 'Attribute Deleted Successfully.';
            } else {
                $this->errors[] = 'Failed To Delete Attribute(s).';
            }
        }
        if (Tools::getIsset('mapped') && Tools::getValue('mapped')) {
            $this->confirmations[] = 'Mapping Saved Successfully.';
        }
    }

    public function deleteAttributeMap($id)
    {
        $db = Db::getInstance();
        $result = $db->delete(
            'cedwish_color_mapping',
            'id=' . (int)$id
        );
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function storeColorName($attribute_id)
    {
        if ($attribute_id) {
            $db = Db::getInstance();
            $default_lang = Configuration::get('PS_LANG_DEFAULT');
            $sql = "SELECT `name` FROM `" . _DB_PREFIX_ . "attribute_group_lang` 
            where `id_lang`='" . (int)$default_lang . "' AND `id_attribute_group`='" . (int)$attribute_id . "'";
            return $db->getValue($sql);
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_mapping'] = array(
                'href' => self::$currentIndex . '&addcedwish_color_mapping&token=' . $this->token,
                'desc' => 'Add Mapping',
                'icon' => 'process-icon-new'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function displayRemoveLink($token = null, $id = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        if (!array_key_exists('Remove', self::$cache_lang)) {
            self::$cache_lang['Remove'] = 'Remove';
        }

        $tpl->assign(
            array(
                'href' => self::$currentIndex . '&' . $this->identifier . '=' .
                    $id . '&removewish_option_mapping=' .
                    $id . '&token=' . ($token != null ? $token : $this->token),
                'action' => self::$cache_lang['Remove'],
                'id' => $id
            )
        );

        return $tpl->fetch();
    }

    public function renderForm()
    {
        $wish_attributes = array(
            'color'
        );
        $wish_attributes_values = array();
        $this->context->smarty->assign(array('attributes' => array()));
        $wish_attributes_values['color'] = $this->getWishOptionValues();

        $rowCount = 0;
        $already_mapped_attributes = array();
        if (Tools::getIsset('id_cedwish_color_mapping') && Tools::getValue('id_cedwish_color_mapping')) {
            $already_mapped_attributes = $this->getAttributeMappings(Tools::getValue('id_cedwish_color_mapping'));
            if (count($already_mapped_attributes)) {
                $rowCount = count($already_mapped_attributes);
            }
        }

        $features = $this->getStoreAttributes();
        $option_values = $this->getStoreAttributeValues();
        $controllerUrl = $this->context->link->getAdminLink('AdminCedWishColorMapping');
        $this->context->smarty->assign(
            array(
                'token' => $this->token,
                'controllerUrl' => $controllerUrl,
                'option_values' => $option_values,
                'features' => $features,
                'attribute_row' => $rowCount,
                'already_mapped_attributes' => $already_mapped_attributes,
                'wish_attributes' => $wish_attributes,
                'wish_attributes_values' => $wish_attributes_values,
            )
        );

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cedwish/views/templates/admin/option/option_mapping_list.tpl'
        );
    }

    public function getWIshOptionValues()
    {
        $wishHelper = new CedWishHelper();
        return $wishHelper->getAcceptedColors();
    }

    public function getAttributeMappings($id)
    {
        $db = Db::getInstance();
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_color_mapping` 
        where `id_cedwish_color_mapping`='" . (int)$id . "'";
        $result = $db->getRow($sql);
        if (!empty($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public function getStoreAttributes()
    {
        $db = Db::getInstance();
        $default_lang = Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT `id_attribute_group` as `id_attribute`,`name` 
        FROM `" . _DB_PREFIX_ . "attribute_group_lang` where `id_lang`='" . (int)$default_lang . "'";
        $result = $db->ExecuteS($sql);
        if (is_array($result) && count($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public function getStoreAttributeValues()
    {
        $db = Db::getInstance();
        $default_lang = Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "attribute` a 
        LEFT join `" . _DB_PREFIX_ . "attribute_lang` al ON (a.id_attribute = al.id_attribute) 
        where al.id_lang='" . (int)$default_lang . "'";
        $result = $db->ExecuteS($sql);
        if (is_array($result) && count($result)) {
            $option_values = array();
            foreach ($result as $value) {
                $option_values[$value['id_attribute_group']][$value['id_attribute']] = $value['name'];
            }
            return $option_values;
        } else {
            return array();
        }
    }

    public function postProcess()
    {
        try {
            if (Tools::getIsset('savemapping') && Tools::getValue('savemapping')) {
                if (version_compare(_PS_VERSION_, '1.6.1', '>=') == true) {
                    $values = Tools::getAllValues();
                } else {
                    $values = $_POST;
                }
                $status = $this->saveOptionMapping($values);
                if ($status) {
                    $link = new LinkCore();
                    $controller_link = $link->getAdminLink('AdminCedWishColorMapping') . '&mapped=1';
                    Tools::redirectAdmin($controller_link);
                    $this->confirmations[] = 'Color(s) Mapped Successfully.';
                } else {
                    $this->errors[] = 'Failed To Map Color(s).';
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        parent::postProcess();
    }

    public function saveOptionMapping($data)
    {
        if (isset($data['cedwish_color_mapping'])
            && count($data['cedwish_color_mapping'])
            && isset($data['cedwish_option_id'])
            && isset($data['store_option_id'])
        ) {
            $db = Db::getInstance();
            $sql = "SELECT `id_cedwish_color_mapping` FROM `" . _DB_PREFIX_ . "cedwish_color_mapping` 
            where `wish_option_id` LIKE '" . pSQL($data['cedwish_option_id']) . "' 
            AND `store_option_id` = '" . (int)$data['store_option_id'] . "'";
            $result = $db->getRow($sql);
            if (isset($result['id_cedwish_color_mapping']) && $result['id_cedwish_color_mapping']) {
                $res = $db->update(
                    'cedwish_color_mapping',
                    array(
                        'store_option_id' => (int)$data['store_option_id'],
                        'mapped_options' => pSQL(json_encode($data['cedwish_color_mapping']))
                    ),
                    'id_cedwish_color_mapping=' . (int)$result['id_cedwish_color_mapping']
                );
            } else {
                $res = $db->insert(
                    'cedwish_color_mapping',
                    array(
                        'wish_option_id' => pSQL($data['cedwish_option_id']),
                        'store_option_id' => (int)$data['store_option_id'],
                        'mapped_options' => pSQL(json_encode($data['cedwish_color_mapping'])),
                    )
                );
            }
            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }
}
