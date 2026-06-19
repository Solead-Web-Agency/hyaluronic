<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

class Carrier16 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $result = 'http://www.dhl.co.uk/shipmentTracking?AWB='.$this->tracking_number;
        $url_page = strip_tags(Tools::file_get_contents($result, true));
        return $url_page;
    }
    public function track()
    {
        $url_page = $this->getResponse();
        $url = Tools::stripslashes($url_page);
        $url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $url), true);
        if (Tools::strlen($url_page) == 0) {
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => 'E206',
                'desc' => 'Numéro incorrect',
            ));
            return $status;
        }

        $carrier_xml = array();

        $date_ = $url['results'][0]['checkpoints'][0]['date'];
        $stt_return = $url['results'][0]['delivery']['code'];
        $result = TrackingModel::searchIdStatus(16, $stt_return);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => true,
            'status' => $stt_return,
            'desc' => $stt_return,
            'date' => date("Y-m-d", strtotime($date_)),
            'id_status' => $id_status,
        ));
        return $status;
    }
}
