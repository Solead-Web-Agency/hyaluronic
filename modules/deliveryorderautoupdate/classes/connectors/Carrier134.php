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

class Carrier134 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $client_secret = Configuration::get('HL_CARRIER134_id1');
        $client_id = Configuration::get('HL_CARRIER134_id2');
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.ctt.pt/cttorg/clients/tracktracers/api/v2/events/search',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "objects": [
                {"objectNumber": "'.$this->tracking_number.'"}
            ]
        }',
          CURLOPT_HTTPHEADER => array(
            'x-ibm-Client-Secret: '.$client_secret.'',
            'x-ibm-Client-ID: '.$client_id.'',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $id_status = 100;
        $events = array();
        if (!$response) {
            $success = false;
            $status = 106;
            $desc = '';
            $date = Date('Y-m-d H:i:s');
            $id_status = 106;
        } else {
            $response = json_Decode($response, true);
            $success = true;
            $events = array_map(function ($e) {
                return array(
                    'event_code' => $e['eventTypeCode'],
                    'event_description' => $e['eventDesig'],
                    'event_date' => date('Y-m-d H:i:s', strtotime($e['eventDate'].' '.$e['eventHour'])),
                    'id_status' => TrackingModel::searchIdStatus(133, $e['eventTypeCode']),
                );
            }, $response);
            $event = end($events);
            $status = $event['event_code'];
            $desc = $event['event_description'];
            $date = $event['event_date'];
            $id_status = $event['id_status'];
        }
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
