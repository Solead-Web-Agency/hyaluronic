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

class Carrier74 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER74_id1');
        $password = Configuration::get('HL_CARRIER74_id2');

        //1st step : Request access token
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://oauth2.posti.com/oauth/token?grant_type=client_credentials',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            "Authorization: Basic ". base64_encode($username.':'.$password),
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $access_token = json_Decode($response, true);
        $access_token = $access_token['access_token'];
        //echo $access_token;exit;

        //2nd step : Request tracking status
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.posti.fi/tracking/7/shipments/trackingnumber/'.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer' .$access_token.''
          ),
        ));

        $response2 = curl_exec($curl);
        curl_close($curl);
        return $response2;
    }
    public function track()
    {
        $response = $this->getResponse();
        $array = json_Decode($response, true);
        $result_ = 100;
        if (!isset($array['parcelShipments']) || count($array['parcelShipments'])) {
            $success = false;
            $status = $array['Fault']['faultcode']??0;
            $status_event = $array['Fault']['faultstring']??'';
            $date = date('Y-m-d H:i:s');
        } else {
            $success = true;
            $shipment = end($array['parcelShipments']);
            $date = $shipment['modifiedAt'];
            $status_event = $shipment['status']['description']['en'];
            $status = $shipment['status']['code'];
        }
        $result_ = TrackingModel::searchIdStatus(74, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $status_event,
            'date' => $date,
            'id_status' => $result_
        ));
        return $status;
    }
}
