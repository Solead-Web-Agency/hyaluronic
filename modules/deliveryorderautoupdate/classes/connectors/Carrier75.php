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

class Carrier75 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER75_id1');
        $password = Configuration::get('HL_CARRIER75_id2');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.dsv.com/my/tracking/v1/shipments/tmsId/'.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "DSV-Service-Auth: Basic YXBpLmRlbW9AZGVtby5kc3YuY29tOkRlbW8xMjM0NQ==",
            "DSV-Subscription-Key: cf86bfb0a069464aa65d89416da3a0f3"
          ),
        ));
        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $id_status = '100';
        if (isset($response['events']) && count($response['events'])) {
            $event = $response['events'][count($response['events'])-1];
            $success = true;
            $status = $event['code'];
            $date = $event['date'];
            $result_status = '';
        } else {
            $success = false;
            $status = '105';
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }


        $id_status = TrackingModel::searchIdStatus(75, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status
        ));
        return $status;
    }
}
