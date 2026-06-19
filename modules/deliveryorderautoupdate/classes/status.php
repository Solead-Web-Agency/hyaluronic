<?php
/**
* 2007-2018 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

class Status
{
    public function __construct($arr = array())
    {
        $this->shipment_ref = $arr['id_order'] ?? 0;
        $this->carrier_server_success = $arr['success']??false;
        $this->carrier_server_status_code = $arr['status']??'';
        $this->carrier_server_status_text = $arr['desc']??'';
        $this->carrier_shipping_status_code = $arr['status']??'';
        $this->carrier_shipping_status_text = $arr['desc']??'';
        $this->carrier_shipping_status_date = $arr['date']??Date('Y-m-d H:i:s');
        $this->module_shipping_status_code = $arr['id_status']??100;
        $this->all_events = $arr['events']??array();
    }
}