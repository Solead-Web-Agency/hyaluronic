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

require_once _PS_MODULE_DIR_ . 'cedwish/classes/api.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/helper.php';
require_once _PS_MODULE_DIR_ . 'cedwish/classes/warehouse.php';

class AdminCedWishWarehouseController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'cedwish_warehouse';
        $this->className = 'CedWishWarehouse';
        $this->bootstrap = true;
        $this->identifier = 'id_cedwish_warehouse';
        $this->_orderBy = 'id_cedwish_warehouse';
        $this->_orderWay = 'DESC';
        $this->lang = false;
        $this->list_no_link = true;
        $this->addRowAction('delete');

        parent::__construct();
        $this->fields_list = array(
            'id' => array(
                'title' =>$this->l('ID'),
                'type' => 'text',
            ),
            'name' => array(
                'title' =>$this->l('Warehouse Name'),
                'type' => 'text',
            ),
            'address' => array(
                'title' =>$this->l('Region'),
                'type' => 'text',
            )
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' =>$this->l('Delete'),
                'confirm' =>$this->l(
                    'Delete Selected Warehouse(s) ?'
                ),
                'icon' => 'icon-trash'
            )
        );
        if (Tools::getIsset('get_warehouse') && Tools::getValue('get_warehouse')) {
            $this->fetchWarehouse();
        }
    }

    public function fetchWarehouse()
    {
        try {
            $apiHelper = new CedWishApi();
            $response = $apiHelper->getMerchantWarehouses();
            if (!isset($response['message'])) {
                if (!empty($response)) {
                    Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "cedwish_warehouse` ");
                    $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cedwish_warehouse` (
                    `id_cedwish_warehouse`,
                    `id`,
                    `shipping_type`,
                    `name`,
                    `address`,
                    `destination_countries`,
                    `ship_to_name`,
                    `city`,
                    `state`,
                    `country_code`,
                    `zipcode`,
                    `street_address1`,
                    `street_address2`
                    ) VALUES ';
                    foreach ($response as $warehouse) {
                        if (!isset($warehouse['destination_countries'])) {
                            $warehouse['destination_countries'] = array();
                        }
                        $sql .= "(
                        NULL,
                        '" . pSQL($warehouse['id']) . "',
                        '" . pSQL($warehouse['shipping_type']) . "',
                        '" . pSQL($warehouse['name']) . "',
                        '" . pSQL(json_encode($warehouse['address'])) . "',
                        '" . pSQL(json_encode($warehouse['destination_countries'])) . "',
                        '" . pSQL($warehouse['address']['ship_to_name']) . "',
                        '" . pSQL($warehouse['address']['city']) . "',
                        '" . pSQL($warehouse['address']['state']) . "',
                        '" . pSQL($warehouse['address']['country_code']) . "',
                        '" . pSQL($warehouse['address']['zipcode']) . "',
                        '" . pSQL($warehouse['address']['street_address1']) . "',
                        '" . pSQL($warehouse['address']['street_address2']) . "'
                        ), ";
                    }
                    $sql = rtrim($sql, ", ");
                    Db::getInstance()->execute($sql);
                }
                $response = array(
                    'success' => true,
                    'message' => 'Warehouses Fetched successfully.'
                );
            } elseif (isset($response['code']) && $response['code'] != 0) {
                $response = array(
                    'success' => false,
                    'message' => $response['message']
                );
            }
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }

        if (isset($response['success']) && $response['success']) {
            $this->confirmations[] = 'Warehouse Fetched Successfully.';
        } elseif (isset($response['message'])) {
            $this->errors[] = $response['message'];
        } else {
            $this->errors[] = 'Some Error While getting warehouse.';
        }
    }

    public function processDelete()
    {
        $id_return_logistics = Tools::getValue('id_warehouse');
        if ($id_return_logistics) {
            Db::getInstance()->execute(
                "DELETE FROM `" . _DB_PREFIX_ . "cedwish_warehouse` 
                WHERE id_warehouse ='" . (int)$id_return_logistics . "'"
            );
            $this->confirmations[] = 'Deleted Successfully.';
        } else {
            $this->errors[] = 'Failed to delete.';
        }
    }

    public function processBulkDelete()
    {
        if (!empty($this->boxes)) {
            Db::getInstance()->execute(
                "DELETE FROM `" . _DB_PREFIX_ . "cedwish_warehouse` 
                WHERE id_warehouse IN ('" . implode("', '", array_map('intval', $this->boxes)) . "')"
            );
            $this->confirmations[] = 'Deleted Successfully.';
        } else {
            $this->errors[] = 'Failed to delete.';
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['fetch_return_warehouse'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token . '&get_warehouse=1',
                'desc' => 'Fetch Warehouse',
                'icon' => 'process-icon-download'
            );
            $this->page_header_toolbar_btn['add_warehouse'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token . '&addcedwish_warehouse=1',
                'desc' => 'Add Warehouse',
                'icon' => 'process-icon-plus'
            );
        } elseif ($this->display == 'view' || $this->display == 'add') {
            $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Back To List', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        $legend_title =$this->l('Create New Warehouse');
        $this->fields_form = array(
            'legend' => array(
                'title' => $legend_title,
                'icon' => 'icon-group'
            ),
            'input' => array(

                array(
                    'type' => 'text',
                    'label' =>$this->l('destination_countries'),
                    'name' => "destination_countries",
                    'required' => false,
                    'desc' =>$this->l(
                        'The region (such as "EU", "MX", or "US") of the warehouse.'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Warehouse Name'),
                    'name' => "name",
                    'required' => false,
                    'desc' =>$this->l(
                        'The name of the warehouse.'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Ship To Name'),
                    'name' => "address[ship_to_name]",
                    'required' => false,
                    'desc' =>$this->l(
                        ' The entity that return parcels should be addressed to. This should be a name that helps 
                        the carrier successfully identify the warehouse and deliver return parcels. .  '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('City'),
                    'name' => "address[city]",
                    'required' => false,
                    'desc' =>$this->l(
                        'The city in which of the warehouse is located. '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('State'),
                    'name' => "address[state]",
                    'required' => false,
                    'desc' =>$this->l(
                        'The state in which of the warehouse is located. '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Country'),
                    'name' => "address[country_code]",
                    'required' => false,
                    'desc' =>$this->l(
                        'The country in which of the warehouse is located. Country code should follow 
                        ISO 3166 Alpha-2 code. Example: CN, US. '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Zipcode'),
                    'name' => "address[zipcode]",
                    'required' => false,
                    'desc' =>$this->l(
                        'The zip code/postal code in which the warehouse is located. '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Street Address 1'),
                    'name' => "address[street_address1]",
                    'required' => false,
                    'desc' =>$this->l(
                        'The primary street address of the warehouse. '
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->l('Street Address 2'),
                    'name' => "address[street_address2]",
                    'required' => false,
                    'desc' =>$this->l(
                        'Optional The secondary street address of the warehouse.'
                    ),
                ),
            ),
            'submit' => array(
                'class' => 'btn btn-default pull-right',
                'name' => 'addcedwish_batch',
                'title' =>$this->l('Save'),
            )
        );
        return parent::renderForm();
    }

    public function processAdd()
    {
        $post_data = Tools::getAllValues();
        $apiHelper = new CedWishApi();
        $params = array(
            'address' => $post_data['address'],
            'destination_countries' => explode(",", $post_data['destination_countries']),
            'name' => $post_data['name']
        );

        $response = $apiHelper->createWarehouse($params);
        $updated = false;
        if (isset($response['success']) && $response['success']) {
            $this->confirmations[] = 'Batch Requested Successfully.';
            $warehouse = $response['message'];
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cedwish_warehouse` (
            `id_warehouse`,
            `id`,
            `shipping_type`,
            `name`,
            `address`,
            `destination_countries`,
            `ship_to_name`,
            `city`,
            `state`,
            `country_code`,
            `zipcode`,
            `street_address1`,
            `street_address2`
            ) VALUES ';
            $sql .= "(
            (SELECT `id_warehouse` FROM `" . _DB_PREFIX_ . "cedwish_warehouse` pscp 
            WHERE pscp.id LIKE '" . pSQL($warehouse['id']) . "' LIMIT 1),
            '" . pSQL($warehouse['id']) . "',
            '" . pSQL($warehouse['shipping_type']) . "',
            '" . pSQL($warehouse['name']) . "',
            '" . pSQL(json_encode($warehouse['address'])) . "',
            '" . pSQL(json_encode($warehouse['destination_countries'])) . "',
            '" . pSQL($warehouse['address']['ship_to_name']) . "',
            '" . pSQL($warehouse['address']['city']) . "',
            '" . pSQL($warehouse['address']['state']) . "',
            '" . pSQL($warehouse['address']['country_code']) . "',
            '" . pSQL($warehouse['address']['zipcode']) . "',
            '" . pSQL($warehouse['address']['street_address1']) . "',
            '" . pSQL($warehouse['address']['street_address2']) . "'
            ) ";
            $sql .= " ON DUPLICATE KEY UPDATE 
            id=values(id),
            ship_to_name=values(ship_to_name),
            name=values(name),
            destination_countries=values(destination_countries),
            country_code=values(country_code),
            address=values(address),
            city=values(city),
            state=values(state),
            zipcode=values(zipcode),
            street_address1=values(street_address1),
            street_address2=values(street_address2) ";
            $updated = Db::getInstance()->execute($sql);
        } elseif (isset($response['message'])) {
            $this->errors[] = $response['message'];
        } else {
            $this->errors[] = 'Some Error While Creating Warehouse.';
        }
        if (!$updated) {
            return parent::processAdd();
        }
    }
}
