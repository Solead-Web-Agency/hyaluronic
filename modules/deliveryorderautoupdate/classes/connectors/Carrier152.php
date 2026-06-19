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

class Carrier152 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $user = Configuration::get('HL_CARRIER152_id1');
        $password = Configuration::get('HL_CARRIER152_id2');
        $client_id = Configuration::get('HL_CARRIER152_id3');
        $client_secret = Configuration::get('HL_CARRIER152_id4');
        $id_number = Configuration::get('HL_CARRIER152_id5');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://servicios.api.seur.io/pic_token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'grant_type=password&client_id='.$client_id.'&client_secret='.$client_secret.'&username='.$user.'&password='.$password.'',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: 8a001cb98b95ca7ca8dee5d504821ceb=35fca9e8f1d1a6c92f38aa806e485ac2; JSESSIONID=1D8AB59B84A2D6656674F135257CEC61'
          ),
        ));

        $response1 = curl_exec($curl);
        $response1 = json_Decode($response1, true);
        $token = (string)$response1['access_token'];
        curl_close($curl);

        // Now that we got token, let's call tracking API
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://servicios.api.seur.io/pic/v1/tracking-services/extended?ref='.$this->tracking_number.'&refType=REFERENCE&idNumber='.$id_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token.'',
            'Cookie: incap_ses_392_2392811=Pjh0X2mBfz0DOdbwqKpwBbqsB2MAAAAAK1YLsW7r2jMETUGDoymXAQ==; visid_incap_2392811=AH59W9lYS+2oXLs5JLVyEmPYA2MAAAAAQUIPAAAAAADzdbqU5Pp0x8VkCpSdJp/W; 8a001cb98b95ca7ca8dee5d504821ceb=0c7f77be3821caceda43b526ef6fe8c6; JSESSIONID=05DF61EEDF96CEA1B4CAA3B5AD7A70C0'
          ),
        ));

        $response2 = curl_exec($curl);
        $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $response2;
    }
    public function track()
    {
        $response = $this->getResponse();
        $rs = json_Decode($response, true);

        $id_status = 100;
        $events = array();
        if (!$rs) {
            $status_error = true;
            $date = Date('Y-m-d H:i:s');
            $status = $this->httpcode;
            $result_status = '';
        } elseif (isset($rs['errors'])) {
            $status_error = true;
            $date = Date('Y-m-d H:i:s');
            $status = $rs['errors'][0]['status'];
            $result_status = $rs['errors'][0]['detail'];
        } else {
            $status_error = false;
            $SITUACION = $rs['data'][0]['situations'];
            $events = array_map(function ($e) {
                return array(
                    'event_code' => trim($e['eventCode']),
                    'event_description' => $e['description'],
                    'event_date' => date("y-m-d H:i:s", strtotime($e['situationDate'])),
                    'id_status' => TrackingModel::searchIdStatus(152, trim($e['eventCode']))
                );
            }, $SITUACION);
            $trace = end($SITUACION);
            $status = $trace['eventCode'];
            $date = $trace['situationDate'];
            $date = date("y-m-d H:i:s", strtotime($date));
            $result_status = $trace['description'];
        }
        $id_status = TrackingModel::searchIdStatus(152, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $status_error,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
