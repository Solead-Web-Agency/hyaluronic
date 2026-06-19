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
namespace deliveryorderautoupdate;
class Carrier
{
    public function __construct($order)
    {
        $tracking_number = trim($order['tracking_number']);
        $tracking_number = explode(',', $tracking_number);
        $tracking_number = end($tracking_number);
        $this->tracking_number = $tracking_number;
        $this->id_order = $order['id_order_carrier'];
        $this->id_order_carrier = $order['id_order_carrier'];
        $this->pretty_reponse = 'asdlfasdf';
        $this->order_reference = $order['reference'];
    }
    public function getResponse()
    {
        return 'no response';
    }
    public function track()
    {
        return new \Status();
    }
    public function print()
    {
        libxml_use_internal_errors(true);
        $response = $this->getResponse();
        $doc = simplexml_load_string($response);
        if ($doc) {
            header('Content-type: text/xml');
        } else {
            header('Content-type: text/plain');
        }
        print_r($response);
    }
}