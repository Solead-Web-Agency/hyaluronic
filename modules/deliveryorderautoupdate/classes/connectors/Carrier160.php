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

class Carrier160 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $key = Configuration::get('HL_CARRIER160_id1');

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api-eu.dhl.com/track/shipments?trackingNumber='.$this->tracking_number.
          '&requesterCountryCode=DE&originCountryCode=DE&language=en',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            'DHL-API-Key: '.$key.''
          ),
        ));

        $response = curl_exec($curl);
        $response = json_Decode($response, true);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $id_status = 100;
        if (isset($response['status']) || !count($response['shipments'])) {
            $success = false;
            $status =  $response['status'];
            $desc = $response['detail'];
            $date = Date('Y-m-d H:i:s');
            $events = array();
        } else {
            $success = true;
            $shipment = reset($response['shipments']);
            $events = array_map(function ($e) {
                return array(
                    'event_code' => $e['description']??0,
                    'event_description' => $e['description'],
                    'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e['timestamp']))),
                    'id_status' => TrackingModel::searchIdStatus(160, $e['description']??0)
                );
            }, array_reverse($shipment['events']));
            $status = $shipment['status']['statusCode'];
            $desc = isset($shipment['status']['description'])?$shipment['status']['description']:$shipment['status']['status'];
            $date = $shipment['status']['timestamp'];
            $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
        }
        $id_status = TrackingModel::searchIdStatus(160, $status);
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
