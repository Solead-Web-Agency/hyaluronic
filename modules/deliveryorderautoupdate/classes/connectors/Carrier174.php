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

class Carrier174 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $credentials_embed =_PS_MODULE_DIR_.'deliveryorderautoupdate/webservices/174/credentials174.xml';
        $webxml_crr = json_decode(
            json_encode(@simplexml_load_file($credentials_embed, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        $values = array();
        foreach ($webxml_crr->credential as $cre) {
            $values[] = Configuration::get($cre->credname);
        }
        $appname = isset($values[0])?$values[0]:'';
        $password = isset($values[1])?$values[1]:'';
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
        <data appname="'.$appname.'" language-code="en" password="'.$password.
        '" piece-code="'.$this->tracking_number.'" request="d-get-piece"/>';
        $query = http_build_query(array(
          'xml' => $xml,
        ));
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://cig.dhl.de/services/sandbox/rest/sendungsverfolgung?{$query}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ZGFoaGFvdWk6YzklXmgqQzY="
          ),
        ));
        $response = curl_exec($curl);
        return $response;
    }
    // public function track()
    // {
    //     $response = $this->getResponse();
    //     $result = new SimpleXMLElement($response);
    //     $attributes = $result->data->attributes();
    //     $result_carrier = array();

    //     if ($result->attributes()->{'code'} != 0) {
    //         $success = false;
    //         $status = (string)$result->attributes()->{'code'};
    //         $status_event = (string)$result->attributes()->{'error'};
    //         $date = date('Y-m-d H:i:s');
    //     } else {
    //         $success = true;
    //         $date = (string)$attributes->{'status-timestamp'};
    //         $status_event = (string)$attributes->{'short-status'};
    //         $status = (string)$attributes->{'ice'}.'_'.$attributes->{'standard-event-code'};
    //     }
    //     $result_ = TrackingModel::searchIdStatus(174, $status);
    //     $status = new Status(array(
    //         'id_order' => $this->id_order_carrier,
    //         'success' => $success,
    //         'status' => $status,
    //         'desc' => $status_event,
    //         'date' => $date,
    //         'id_status' => $result_,
    //         'events' => $events
    //     ));
    //     return $status;
    // }
}
