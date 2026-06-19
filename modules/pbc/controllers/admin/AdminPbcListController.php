<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2021 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

require_once _PS_MODULE_DIR_ . 'pbc/pbc.php';

class AdminPbcListController extends ModuleAdminController
{
    protected $position_identifier = 'id_pbc';

    public function __construct()
    {
        $this->table = 'pbc';
        $this->className = 'pricebc';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        parent::__construct();
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );
        $this->bootstrap = true;
        $this->_orderBy = 'id_pbc';

        $this->fields_list = array(
            'id_pbc' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'orderby' => true,
                'width' => 20
            ),
            'id_country' => array(
                'title' => $this->l('Country'),
                'width' => 'auto',
                'orderby' => true,
                'callback' => 'getCountryName',
            ),
            'wtd' => array(
                'title' => $this->l('Impact type'),
                'width' => 'auto',
                'orderby' => true,
                'callback' => 'getImpactType',
            ),
            'value' => array(
                'title' => $this->l('Impact value'),
                'width' => 'auto',
                'orderby' => true,
                'callback' => 'getImpactValue',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'width' => 20,
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
            ),
        );
    }

    public function getCountryName($group, $row)
    {
        $country = new Country((int)$row['id_country'], $this->context->language->id);
        return $country->name;
    }

    public function getImpactType($group, $row) {
        if ($row['wtd'] == 1){
            return $this->l('Increase price').' (%)';
        } elseif ($row['wtd'] == 2){
            return $this->l('Decrease price').' (%)';
        } elseif ($row['wtd'] == 3){
            return $this->l('Increase price').' ('.$this->context->currency->iso_code.')';
        } elseif ($row['wtd'] == 4){
            return $this->l('Decrease price').' ('.$this->context->currency->iso_code.')';
        }
    }

    public function getImpactValue($group, $row)
    {
        if (in_array($row['wtd'], array(1,2))) {
            return number_format($row['value'], 2, '.', '') . '%';
        } elseif(in_array($row['wtd'], array(3,4))) {
            return number_format($row['value'], 2, '.', '') . ' ' .$this->context->currency->iso_code;
        }
    }

    public function init()
    {
        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive())
        {
            //$this->_where = 'AND b.id_shop=' . Context::getContext()->shop->id;
        }
        parent::init();
        if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_) == false) {
            $this->context->controller->errors[] = $this->l('Module to identify customer country uses geolocation.') . ' ' . $this->l('In order to use Geolocation, please download') . ' ' . '<a href="https://mypresta.eu/prestashop-17/geolite2-city-geolocation-download.html">' . $this->l('this file') . '</a> ' . $this->l('and extract it (using Winrar or Gzip) into the /app/Resources/geoip/ directory.');
        }
    }
    public function renderForm()
    {
        if (!$this->loadObject(true))
        {
            return;
        }
        $cover = false;
        $obj = $this->loadObject(true);
        if (isset($obj->id))
        {
            $this->display = 'edit';
        } else
        {
            $this->display = 'add';
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Impact price by country'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Country'),
                    'name' => 'id_country',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Impact Type'),
                    'name' => 'wtd',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array('id'=>1, 'name'=>$this->l('Increase price by %')),
                            array('id'=>2, 'name'=>$this->l('Decrease price by %')),
                            array('id'=>3, 'name'=>$this->l('Increase price by amount')),
                            array('id'=>4, 'name'=>$this->l('Decrease price by amount')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Impact Value'),
                    'name' => 'value',
                    'required' => true,
                    'prefix' => '%',
                    'lang' => false,
                    'desc' => $this->l('Separate decimal values with dot (not comma)') . '<span class="exchangeRatesInfo">'.'. '.$this->l('Module will automatically calculate impact value to other currencies based on currency exchange rates in shop').'</span>' . $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'pbc/views/script.tpl')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active:'),
                    'name' => 'active',
                    'required' => true,
                    'lang' => false,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('On')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Off')
                        )
                    ),
                ),

            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        return parent::renderForm();
    }
}