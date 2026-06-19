<?php
/**
 * Project : everpsminimumorder
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'everpsminimumorder/classes/EverMinimumOrderClass.php';

class AdminEverMinimumOrderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->display = 'Ever Minimum Order';
        $this->meta_title = $this->l('Ever Minimum Order');
        $this->table = 'everpsminimumorder';
        $this->className = 'EverMinimumOrderClass';
        $this->context = Context::getContext();
        $this->identifier = 'id_everpsminimumorder';
        $this->_select = 'l.name AS groupname, cl.name AS countryname, s.name AS shopname';
        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'group_lang` l
                ON (
                    l.`id_group` = a.`id_group`
                    AND l.id_lang = '.(int)$this->context->language->id.'
                )
            LEFT JOIN `'._DB_PREFIX_.'country_lang` cl
                ON (
                    cl.id_country = a.id_country
                    AND cl.id_lang = '.(int)$this->context->language->id.'
                )
            LEFT JOIN `'._DB_PREFIX_.'shop` s
                ON (
                    s.id_shop = a.id_shop
                )';

        $this->_where = 'AND a.id_shop = '.(int)$this->context->shop->id;
        $this->_group = 'GROUP BY a.id_everpsminimumorder';
        $this->_orderBy = 'id_everpsminimumorder';
        $this->_orderWay = 'ASC';

        $this->fields_list = array(
            'id_everpsminimumorder' => array(
                'title' => $this->l('Rule ID'),
                'align' => 'left',
                'width' => 25
            ),
            'shopname' => array(
                'title' => $this->l('Shop'),
                'align' => 'left',
                'width' => 25,
                'havingFilter' => true,
                'filter_key' => 's!name'
            ),
            'countryname' => array(
                'title' => $this->l('Country'),
                'align' => 'left',
                'width' => 25,
                'havingFilter' => true,
                'filter_key' => 'cl!name'
            ),
            'groupname' => array(
                'title' => $this->l('Group'),
                'align' => 'left',
                'width' => 25,
                'havingFilter' => true,
                'filter_key' => 'l!name'
            ),
            'amount' => array(
                'title' => $this->l('Minimum amount'),
                'align' => 'left',
                'width' => 25
            ),
            'use_tax' => array(
                'title' => $this->l('With taxes'),
                'type' => 'bool',
                'active' => 'use_tax',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'active',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
        );

        $this->colorOnBackground = true;

        $this->context->smarty->assign(array(
            'minorderimg_dir' => _MODULE_DIR_.'everpsminimumorder/views/img/',
        ));

        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsminimumorder.Admineverminimumordercontroller'
            );
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    /**
     * Gestion de la toolbar
     */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new'
        );
 
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->html = '';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete all selected'),
                'confirm' => $this->l('Delete all those selected items ?')
            ),
            'taxes' => array(
                'text' => $this->l('Switch with/without taxes'),
                'confirm' => $this->l('Switch with/without taxes on selected items ?')
            ),
        );

        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
        }

        if (Tools::isSubmit('submitBulktaxes'.$this->table)) {
            $this->processBulkTaxes();
        }

        if (Tools::isSubmit('submitBulkdisableSelectioneverpsminimumorder')) {
            $this->processBulkDisable();
        }

        if (Tools::isSubmit('submitBulkdisableSelectioneverpsminimumorder')) {
            $this->processBulkEnable();
        }

        $this->toolbar_title = $this->l('Minimal order rules');

        $lists = parent::renderList();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.
            '/everpsminimumorder/views/templates/admin/header.tpl'
        );
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.
            '/everpsminimumorder/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function renderForm()
    {
        $country_options = EverMinimumOrderClass::getAvailableCountries();
        $group_options = EverMinimumOrderClass::getAllGroups(
            (int)Context::getContext()->language->id
        );
        // $group_options = Group::getGroups(
        //     (int)Context::getContext()->language->id
        // );
        $shop_options = Shop::getShops();

        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('All fields are required'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'button pull-right btn btn-default'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Shop'),
                    'name' => 'id_shop',
                    'required' => true,
                    'options' => array(
                    'query' => $shop_options,
                    'id' => 'id_shop',
                    'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Country'),
                    'name' => 'id_country',
                    'required' => true,
                    'options' => array(
                    'query' => $country_options,
                    'id' => 'id_country',
                    'name' => 'iso_code'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Apply on all countries ?'),
                    'desc' => $this->l('Will auto-add this rule for each enabled country'),
                    'name' => 'apply_all_countries',
                    'lang' => false,
                    'size' => 2,
                    'values' => array(
                        array(
                          'id' => 'active_on',
                          'value' => 1,
                          'label' => $this->l('Enable')
                        ),
                        array(
                          'id' => 'active_off',
                          'value' => 0,
                          'label' => $this->l('Disable')
                        ),
                     ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer group'),
                    'name' => 'id_group',
                    'required' => true,
                    'options' => array(
                    'query' => $group_options,
                    'id' => 'id_group',
                    'name' => 'name'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Minimal amount'),
                    'desc' => $this->l('Minimal amount to have in cart'),
                    'required' => true,
                    'name' => 'amount',
                    'lang' => false,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Minimal amount is with taxes ?'),
                    'desc' => $this->l('Set yes for taxes included'),
                    'name' => 'use_tax',
                    'lang' => false,
                    'size' => 2,
                    'values' => array(
                        array(
                          'id' => 'active_on',
                          'value' => 1,
                          'label' => $this->l('Enable')
                        ),
                        array(
                          'id' => 'active_off',
                          'value' => 0,
                          'label' => $this->l('Disable')
                        ),
                     ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate rule ?'),
                    'name' => 'active',
                    'lang' => false,
                    'size' => 2,
                    'values' => array(
                        array(
                          'id' => 'active_on',
                          'value' => 1,
                          'label' => $this->l('Enable')
                        ),
                        array(
                          'id' => 'active_off',
                          'value' => 0,
                          'label' => $this->l('Disable')
                        ),
                     ),
                ),
            )
        );
        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('use_taxeverpsminimumorder')) {
            $everObj = new EverMinimumOrderClass(
                (int)Tools::getValue('id_everpsminimumorder')
            );
            $everObj->use_tax = !$everObj->use_tax;
            if (!$everObj->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
        }

        if (Tools::isSubmit('activeeverpsminimumorder')) {
            $everObj = new EverMinimumOrderClass(
                (int)Tools::getValue('id_everpsminimumorder')
            );
            $everObj->active = !$everObj->active;
            if (!$everObj->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
        }

        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_shop')
                || !Validate::isInt(Tools::getValue('id_shop'))
            ) {
                $this->errors[] = $this->l('error : [id shop] is not valid');
            }
            if (!Tools::getValue('id_country')
                || !Validate::isFloat(Tools::getValue('id_country'))
            ) {
                $this->errors[] = $this->l('error : [id country] is not valid');
            }
            if (!Validate::isBool(Tools::getValue('apply_all_countries'))) {
                $this->errors[] = $this->l('error : [apply all countries] is not valid');
            }
            if (!Tools::getValue('id_group')
                || !Validate::isInt(Tools::getValue('id_group'))
            ) {
                $this->errors[] = $this->l('error : [id group] is not valid');
            }
            if (!Tools::getValue('amount')
                || !Validate::isFloat(Tools::getValue('amount'))
            ) {
                $this->errors[] = $this->l('error : [amount] is not valid');
            }
            if (!Validate::isBool(Tools::getValue('use_tax'))) {
                $this->errors[] = $this->l('error : [use_tax] is not valid');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('error : [active] is not valid');
            }
            if (!count($this->errors)) {
                // Need to loop on each country if apply_all_countries is true
                if ((bool)Tools::getValue('apply_all_countries')) {
                    return $this->applyOnAllCountries();
                }
            }
        }
        return parent::postProcess();
    }

    private function applyOnAllCountries()
    {
        $saved = true;
        foreach (EverMinimumOrderClass::getAvailableCountries() as $country) {
            $rule = new EverMinimumOrderClass();
            $rule->id_shop = (int)$this->context->shop->id;
            $rule->id_country = (int)$country['id_country'];
            $rule->id_group = (int)Tools::getValue('id_group');
            $rule->amount = (float)Tools::getValue('amount');
            $rule->use_tax = (bool)Tools::getValue('use_tax');
            $rule->active = (bool)Tools::getValue('active');
            if (!$rule->save()) {
                $saved = false;
            }
        }
        return $saved;
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEver) {
            $everObj = new EverMinimumOrderClass((int)$idEver);

            if (!$everObj->delete()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkTaxes()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEver) {
            $everObj = new EverMinimumOrderClass((int)$idEver);
            $everObj->use_tax = !$everObj->use_tax;
            if (!$everObj->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
        }
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEver) {
            $everObj = new EverMinimumOrderClass((int)$idEver);
            $everObj->active = false;
            if (!$everObj->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
        }
    }

    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEver) {
            $everObj = new EverMinimumOrderClass((int)$idEver);
            $everObj->active = 1;
            if (!$everObj->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
        }
    }
}
