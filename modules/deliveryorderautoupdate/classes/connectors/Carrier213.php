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

class Carrier213 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $contract = Configuration::get('HL_CARRIER213_id1');
        $depotsender = Tools::substr($this->tracking_number, 0, 2);
        $tracking_number = Tools::substr($this->tracking_number, 2, 9);
        $url = 'https://infoweb.gls-italy.com/XML/get_xml_track.php?locpartenza='.$depotsender.'&NumSped='.$tracking_number.'&CodCli='.$contract.'';
        //print_r($url);exit;
        $result_carrier = array();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => ''.$url.'',
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
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA );
        $rs = json_Decode(json_Encode($xml), true);
        $events = array();
        if (isset($rs['TESTOERRORE'])) {
            $success = true;
            $status = trim($rs['TESTOERRORE']);
            $id_status = TrackingModel::searchIdStatus(213, $status);
            $date = date('Y-m-d H:i:s');
            $result_status = trim($rs['TESTOERRORE']);
        } else {
            $tracking = $rs['SPEDIZIONE']['TRACKING'];
            $success = true;
            foreach ($tracking['Data'] as $key => $date) {
                $time = $tracking['Ora'][$key]?(string)$tracking['Ora'][$key]:"00:00";
                $datetime = DateTime::createFromFormat('d/m/y H:i', $date.' '.$time);
                $events[] = array(
                    'event_code' => $tracking['Codice'][$key],
                    'event_description' => $tracking['Stato'][$key],
                    'event_date' => $datetime->format('Y-m-d H:i:s'),
                    'id_status' => TrackingModel::searchIdStatus(213, $tracking['Codice'][$key])
                );
            }
            $event = $events[0];
            $status = $event['event_code'];
            $id_status = $event['id_status'];
            $date = $event['event_date'];
            $result_status = $event['event_description'];
        }

        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => array_reverse($events)
        ));
        return $status;
    }
}
