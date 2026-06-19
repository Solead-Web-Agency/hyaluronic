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

class Carrier214 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER214_id1');
        $password = Configuration::get('HL_CARRIER214_id2');
        $result_carrier = array();

        //1st step : Request access token
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://apid.gp.posteitaliane.it/dev/kindergarden/user/sessions',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"clientId": "'.$username.'",
         "secretId": "'.$password.'"}',
          CURLOPT_HTTPHEADER => array(
            'POSTE_clientID: '.$username.'',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $access_token = json_Decode($response, true);
        if (isset($access_token['error'])) {
            return $access_token;
        } else {
            $access_token = $access_token['access_token'];
            //2nd step : Request tracking status
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://apid.gp.posteitaliane.it/dev/kindergarden/postalandlogistics/parcel/tracking',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
             "arg0": {
             "shipmentsData": [
             {
             "waybillNumber": "'.$this->tracking_number.'",
            "lastTracingState": "N"
             }
             ],
             "statusDescription": "E",
            "customerType": "DQ"
             }
            } ',
              CURLOPT_HTTPHEADER => array(
                'POSTE_clientID: '.$username.'',
                'Content-Type: application/json',
               'Authorization: Bearer ' .$access_token

              ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $array = json_Decode($response, true);
            return $array;
        }
    }
    public function track()
    {
        $response = $this->getResponse();
        if (isset($response['error'])) {
            $success = false;
            $status = $response['error'];
            $status_event = $response['error_description'];
            $date = date('Y-m-d H:i:s');
        } else {
            $array = $response;
            $result_ = 0;
            $rs = $array['return']['messages'][0]['messages'];
            if (isset($rs[0]) && count($rs[0])) {
                $success = false;
                $status = $rs[0]['code'];
                $status_event = $rs[0]['message'];
                $date = date('Y-m-d H:i:s');
            } else {
                $success = true;
                $shipment = end($array['parcelShipments']);
                $date = $shipment['modifiedAt'];
                $status_event = $shipment['status']['description']['en'];
                $status = $shipment['status']['code'];
            }
        }
        $result_ = TrackingModel::searchIdStatus(214, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $status_event,
            'date' => $date,
            'id_status' => $result_,
        ));
        return $status;
    }
}
