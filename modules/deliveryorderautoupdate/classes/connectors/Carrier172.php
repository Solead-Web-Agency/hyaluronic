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

class Carrier172 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'api-gw.dhlparcel.nl/track-trace?key='.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Cookie: __cfruid=0185369450cbe5fb08dd157c737c760005fb18c5-1614325308'
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

        $id_status = '100';
        $success = false;
        $status = 'E206';
        $date = date("Y-m-d H:i:s");
        $result_status = '';
        $events = array();
        if ($response) {
            if (count($response)) {
                $response = reset($response);
                if (isset($response['events']) && count($response['events'])) {
                    $events = array_map(function ($e) {
                        return array(
                            'event_code' => $e['status'],
                            'event_description' => $e['status'],
                            'event_date' => $e['timestamp'],
                            'id_status' => TrackingModel::searchIdStatus(172, $e['status'])
                        );
                    }, $response['events']);
                    $event = end($response['events']);
                    $success = true;
                    $status = (string)$event['status'];
                    $date = $event['timestamp'];
                    $result_status = (string)$event['status'];
                }
            }
        } else {
            $result_status = $rs;
        }
        $id_status = TrackingModel::searchIdStatus(172, $status);
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
