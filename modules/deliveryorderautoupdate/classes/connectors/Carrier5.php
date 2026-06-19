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

class Carrier5 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de","it") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order.''
        );
        $username = Configuration::get('HL_CARRIER5_id1');
        $password = Configuration::get('HL_CARRIER5_id2');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://gls-group.eu/public/v1/tracking/references/'.$this->tracking_number,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic '. base64_encode($username.':'.$password),
                'Accept-Language: '.$lang
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
        $id_status = 0;
        if (isset($response['lastError'])) {
            $error_code = (string)$response['lastError'];
            $error_desc = (string)$response['exceptionText'];
            $id_status = TrackingModel::searchIdStatus(5, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $id_status
            ));
            return $status;
        }
        if (!isset($response['parcels'][0])) {
            $error_code = 'empty';
            $error_desc = 'empty';
            $id_status = TrackingModel::searchIdStatus(5, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $id_status
            ));
            return $status;
        }
        $carrier_xml = array();
        $evtNos = $response['parcels'][0]['events'][0]['code'];
        $history = $response['parcels'][0]['events'];
        $events = array_map(function ($e) {
            return array(
                'event_code' => (string)$e['code'],
                'event_description' => '',
                'event_date' => '',
                'id_status' => TrackingModel::searchIdStatus(3, (string)$e['code'])
            );
        }, $history);
        foreach ($history as $key => $h) {
            if (isset($events[$key])) {
                $events[$key]['event_date'] = $h['timestamp'];
                $events[$key]['event_description'] = $h['description'];
            }
        }
        $events = array_reverse($events);
        if (count($history)) {
            $statusInfo = (string)$history[0]['code'];
        } else {
            $statusInfo = "E206";
        }
        $id_status = TrackingModel::searchIdStatus(3, $statusInfo);

        $description = $response['parcels'][0]['events'][0]['description'];
        $date = $response['parcels'][0]['events'][0]['timestamp'];
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => $status_error,
            'status' => $statusInfo,
            'desc' => $description,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}

