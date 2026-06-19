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

class Carrier7 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $key = Configuration::get('HL_CARRIER7_id1');
        $result = 'https://api.laposte.fr/suivi/v2/idships/'.$this->tracking_number;

        $ch = curl_init($result);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER  => array('X-Okapi-Key: '.$key.'',
                'Accept: application/json'),
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 1
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $url = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);

        if (isset($url['shipment']) && isset($url['shipment']['event'])) {
            $events = $url['shipment']['event'];
            if (is_array($events) && count($events)) {
                $event = $events[0];
                $events = array_map(function ($e) {
                    if (trim($e['code']) == '') {
                        $id_status = 1;
                    } else {
                        $id_status = TrackingModel::searchIdStatus(7, trim($e['code']));
                    }
                    return array(
                        'event_code' => trim($e['code']),
                        'event_description' => $e['label'],
                        'event_date' => date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $e['date']))),
                        'id_status' => $id_status
                    );
                }, array_reverse($events));
            } else {
                $event = false;
            }
        } else {
            $event = false;
        }
        $date = Date('Y-m-d H:i:s');
        if ($event === false) {
            $result = 0;
            if (isset($url['returnCode'])) {
                $error_code = $url['returnCode'];
                $msg = $url['returnMessage'];
            } else {
                $error_code = $url['code'];
                $msg = $url['message'];
            }
            $result = TrackingModel::searchIdStatus(7, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $msg,
                'date' => $date,
                'id_status' => $result
            ));
            return $status;
        }


        $carrier_xml = array();
        $result_ = null;
        $date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $event['date'])));
        $carrier_shipping_status_text = $event['label'];
        if ($event['code'] == '') {
            $result = 1;
        } else {
            $result = TrackingModel::searchIdStatus(7, $event['code']);
        }

        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => true,
            'status' => $event['code'],
            'desc' => $carrier_shipping_status_text,
            'date' => $date,
            'id_status' => $result,
            'events' => $events
        ));
        return $status;
    }
}

