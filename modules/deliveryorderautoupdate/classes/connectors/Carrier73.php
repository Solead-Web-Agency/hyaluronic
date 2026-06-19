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

class Carrier73 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $apikey = Configuration::get('HL_CARRIER73_id1');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api2.postnord.com/rest/shipment/v5/trackandtrace/findByIdentifier.xml?apikey='.$apikey.'&id='.$this->tracking_number.'&locale=en',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $events = array();
        if (isset($response['shipments']['Shipment'])) {
            $events = $response['shipments']['Shipment']['items']['Item']['events']['TrackingEvent'];
            $events = array_map(function ($e) {
                return array(
                    'event_code' => $e["eventCode"],
                    'event_description' => $e["eventDescription"],
                    'event_date' => date('Y-m-d H:i:s', strtotime($e["eventTime"])),
                    'id_status' => TrackingModel::searchIdStatus(73, $e["eventCode"])
                );
            }, $events);
            $event = $events[count($events)-1];
            $server = 1;
            $label_status = $event['event_description'];
            $status = $event['event_code'];
            $date = $event['event_date'];
        } else {
            $server = 0;
            $label_status = 'Not found';
            $status = 404;
            $date = date('Y-m-d H:i:s');
        }

        $id_status = TrackingModel::searchIdStatus(73, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => !($server == 0),
            'status' => $status,
            'desc' => $label_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
