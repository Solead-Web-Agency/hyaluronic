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

class Carrier30 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://soa-gw.canadapost.ca/vis/track/pin/'.$this->tracking_number.'/summary',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic MzQ2ZmRkOGYwZjkzYjhiODoyMzI4ODViODZlYWI4MGJmNDFjZWNk",
            "Cookie: OWSPRD002TRACK-SERVICE=track-service_02803_s002ptom001"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
        $xml = simplexml_load_string($response);
        $events = array();
        if (isset($xml->message)) {
            $rs = $xml->message;
            $date = date('Y-m-d H:i:s');
            $status = (string)$rs->description;
            $result_status = '';
            $success = false;
        } else {
            $rs = $xml->{'pin-summary'};
            $date = (string)$rs->{'event-date-time'};
            $status = (string)$rs->{'event-description'};
            $result_status = '';
            $date = DateTime::createFromFormat('Ymd:His', $date);
            $date = $date->format('Y-m-d H:i:s');
            $success = true;
            $events = array(array(
                'event_code' => $status,
                'event_description' => $status,
                'event_date' => $date,
                'id_status' => TrackingModel::searchIdStatus(30, $status)
            ));
        }
        $id_status = TrackingModel::searchIdStatus(30, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => $success,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
