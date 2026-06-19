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

class Carrier187 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER187_id1');
        $password = Configuration::get('HL_CARRIER187_id2');
        $data = '<soapenv:Envelope
        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:even="http://events.dpdinfoservices.dpd.com.pl/">
           <soapenv:Header/>
           <soapenv:Body>
              <even:getEventsForWaybillV1>
                 <!--Optional:-->
                 <waybill>'.$this->tracking_number.'</waybill>
                 <!--Optional:-->
                 <eventsSelectType>ALL</eventsSelectType>
                 <!--Optional:-->
                 <language>EN</language>
                 <!--Optional:-->
                 <authDataV1>
                    <!--Optional:-->
                    <channel>?</channel>
                    <!--Optional:-->
                    <login>'.$username.'</login>
                    <!--Optional:-->
                    <password>'.$password.'</password>
                 </authDataV1>
              </even:getEventsForWaybillV1>
           </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://dpdinfoservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml;charset=UTF-8'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['s:', 'ns2:'], '', $response);
        $response = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$response), true);
        $array = $array['Body']['getEventsForWaybillV1Response']['return'];

        $events = array();
        if (!isset($array['eventsList'])) {
            $success = false;
            $status = $array['confirmId'];
            $desc = '';
            $date = date('Y-m-d H:i:s');
            $id_status = TrackingModel::searchIdStatus(187, $status);
        } else {
            $events = $array['eventsList'];
            $success = true;
            $events = array_map(function ($e) use ($webxml_crr) {
                return array(
                    'event_code' => $e["businessCode"],
                    'event_description' => $e["description"],
                    'event_date' => date('Y-m-d H:i:s', strtotime($e['eventTime'])),
                    'id_status' => TrackingModel::searchIdStatus(187, $e["businessCode"])
                );
            }, array_reverse($events));
            $event = $events[count($events)-1];
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
