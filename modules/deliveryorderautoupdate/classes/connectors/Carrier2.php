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

class Carrier2 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("fr","es","pl","de","it") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order.''
        );
        $status_error = null;
        $status = null;
        $result_carrier = array();

        $data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:cxf="http://cxf.tracking.soap.chronopost.fr/">
        <soapenv:Header/>
        <soapenv:Body>
        <cxf:trackSkybill>
        <!--Optional:-->
        <language>'.$lang.'</language>
        <!--Optional:-->
        <skybillNumber>'.$this->tracking_number.'</skybillNumber>
        </cxf:trackSkybill>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.chronopost.fr/tracking-cxf/TrackingServiceWS",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/xml"
        ),
      ));

        $response = curl_exec($curl);
        $response = str_ireplace(['ns1:', 'ns2:', 'SOAP:'], '', $response);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $traceResult = $xml->Body->trackSkybillResponse->return->listEvents;

        if (!isset($traceResult->events)) {
            $error_code = (string)$xml->Body->trackSkybillResponse->return->errorCode;
            $result = TrackingModel::searchIdStatus(2, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'id_status' => $result,
            ));
            return $status;
        }
        $trace = $traceResult->xpath('//events');
        $events = array_map(function ($e) {
            if (trim($e->code) == "SM") return null;
            return array(
                'event_code' => trim($e->code),
                'event_description' => (string)$e->eventLabel,
                'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e->eventDate))),
                'id_status' => TrackingModel::searchIdStatus(2, trim($e->code), true)
            );
        }, $trace);
        $events = array_filter($events, function($e) {
            return $e !== null;
        });
        $event = end($events);
        $status = $event['event_code'];
        $id_status = $event['id_status'];
        if ($id_status < 0) {
            $id_status = 0;
        }
        $date = $event['event_date'];
        $result_status = $event['event_description'];
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => true,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
