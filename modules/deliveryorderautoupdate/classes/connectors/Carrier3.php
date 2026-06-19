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

class Carrier3 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order.''
        );
        $url = 'https://gls-group.eu/app/service/open/rest/'.$lang.'/'.$lang.'/rstt001?match='.$this->tracking_number;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
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
        if (isset($response['exceptionText'])) {
            $error_code = (string)$response['exceptionText'];
            $error_desc = (string)$response['exceptionText'];
            $id_status = TrackingModel::searchIdStatus(3, $error_code, true);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $id_status,
            ));
            return $status;
        }
        $carrier_xml = array();
        $evtNos = $response['tuStatus'][0]['progressBar']['evtNos'];
        $history = $response['tuStatus'][0]['history'];
        $events = array_map(function ($e) {
            return array(
                'event_code' => (string)$e,
                'event_description' => '',
                'event_date' => '',
                'id_status' => TrackingModel::searchIdStatus(3, (string)$e, true)
            );
        }, $evtNos);
        foreach ($history as $key => $h) {
            if (isset($events[$key])) {
                $events[$key]['event_date'] = $h['date'].' '.$h['time'];
                $events[$key]['event_description'] = $h['evtDscr'];
            }
        }
        $events = array_reverse($events);
        if (count($evtNos)) {
            $statusInfo = (string)$evtNos[0];
        } else {
            $statusInfo = "E206";
        }
        $id_status = TrackingModel::searchIdStatus(3, $statusInfo, true);

        $description = $response['tuStatus'][0]['history'][0]['evtDscr'];
        $date = $response['tuStatus'][0]['history'][0]['date']
        .' '.$response['tuStatus'][0]['history'][0]['time'];
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => true,
            'status' => $statusInfo,
            'desc' => $description,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
