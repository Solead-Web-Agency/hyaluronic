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

class Carrier148 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $client_id = Configuration::get('HL_CARRIER148_id1');
        $client_secret = Configuration::get('HL_CARRIER148_id2');
        $username = Configuration::get('HL_CARRIER148_id3');
        $password = Configuration::get('HL_CARRIER148_id4');

        //1st step : Request access token
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.ancorasemargens.pt/oauth/token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
        "grant_type" : "password",
        "client_id" : "'.$client_id.'",
        "client_secret" : "'.$client_secret.'",
        "username" : "'.$username.'",
        "password" : "'.$password.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $access_token = json_Decode($response, true);
        $access_token = isset($access_token['access_token'])?$access_token['access_token']:'';
        //echo $access_token;exit;

        //2nd step : Request tracking status
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'api.ancorasemargens.pt/v1/shipments/'.$this->tracking_number,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' .$access_token.''
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = json_Decode($response, true);
        $id_status = '0';
        if (isset($response['status'])) {
            $success = true;
            $status = (string)$response['status']['id'];
            $result_status = (string)$response['status']['name'];
            $date = date('Y-m-d H:i:s');
        } else {
            $success = false;
            $status = (string)$response['error'];
            $result_status = (string)$response['message'];
            $date = date("Y-m-d H:i:s");
        }

        $id_status = TrackingModel::searchIdStatus(148, $status);
        $events = array(
            array(
                'event_code' => $status,
                'event_description' => $result_status,
                'event_date' => $date,
                'id_status' => $id_status
            )
        );
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
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
