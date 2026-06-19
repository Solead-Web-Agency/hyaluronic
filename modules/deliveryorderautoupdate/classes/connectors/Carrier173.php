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

class Carrier173 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $tracking_number = str_replace("(J)","",$this->tracking_number);
        $username = Configuration::get('HL_CARRIER173_id1');
        $password = Configuration::get('HL_CARRIER173_id2');

        //1st step : Request access token
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://external.dhl.es/cimapi/api/v1/customer/authenticate',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
        "Username": "'.$username.'",
        "Password": "'.$password.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response1 = curl_exec($curl);

        curl_close($curl);

        // $access_token = json_Decode($response, true);
        $access_token = $response1;
        //$access_token = isset($access_token['access_token'])?$access_token['access_token']:'';
        //echo $access_token;exit;

        //2nd step : Request tracking status
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://external.dhl.es/cimapi/api/v1/customer/track?id='.$tracking_number.'&idioma=en',
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

        $response2 = curl_exec($curl);
        $response2 = json_Decode($response2, true);
        return $response2;
    }
    public function track()
    {
        $response = $this->getResponse();
        $id_status = '0';
        $success = false;
        $status = '404';
        $date = date("Y-m-d H:i:s");
        $result_status = '';
        if (count($response)) {
            $event = end($response);
            $success = true;
            $status = (string)$event['Code'];
            $date = $event['DateTime'];
            $result_status = (string)$event['Status'];
        }
        $id_status = TrackingModel::searchIdStatus(173, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
        ));
        return $status;
    }
}
