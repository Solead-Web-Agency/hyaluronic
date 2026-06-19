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

class Carrier124 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER124_id1');
        $password = Configuration::get('HL_CARRIER124_id2');

        $data = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
        xmlns:req=\"http://www.post.ch/npp/trackandtracews/v02/shipmentssearch/req\">\r\n
        <soapenv:Header/>\r\n   <soapenv:Body>\r\n
        <req:ShipmentsSearch>\r\n
        <language>en</language>\r\n         <ShipmentNumbers>\r\n
        <ShipmentNumber>".$this->tracking_number."</ShipmentNumber>\r\n         </ShipmentNumbers>\r\n
        <Identity>?</Identity>\r\n         <!--Optional:-->\r\n
        <Version>2.5</Version>\r\n      </req:ShipmentsSearch>\r\n
        </soapenv:Body>\r\n</soapenv:Envelope>";
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://webservices.post.ch:443/IN_MYPBxTT/services/TrackAndTraceDFUv25.ws",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Accept-Encoding: gzip,deflate",
            "Content-Type: text/xml",
        //    "Content-Length: 541",
            "Host: webservices.post.ch:443",
            "Connection: Keep-Alive",
            "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
            "Authorization: Basic ". base64_encode($username.':'.$password),
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$xml), true);
        $events  = array();
        if (isset($array['soapBody']['ns9ShipmentsSearchDFURes'])) {
            $events = $array['soapBody']['ns9ShipmentsSearchDFURes']['Envelope'];
            if (isset($events[1])) {
                $events = end($events);
            }
            $trace = $events['Data']['Provider']['Sending']['Item']['Event'];
            $events = array_map(function ($e) {
                $date = isset($e['Timestamp'])?$e['Timestamp']:Date('Y-m-d H:i:s');
                $date = date(
                    "Y-m-d H:i:s",
                    strtotime(str_replace("/", "-", $date))
                );
                $status = isset($e['EventNumber'])?$e['EventNumber']:0;
                return array(
                    'event_code' => $status,
                    'event_description' => isset($e['Description'])?$e['Description']:'',
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(124, $status, true)
                );
            }, $trace);
            $event = array_pop($trace);
            $status = isset($event['EventNumber'])?$event['EventNumber']:0;
            $date = isset($event['Timestamp'])?$event['Timestamp']:Date('Y-m-d H:i:s');
            $desc = isset($event['Description'])?$event['Description']:'';
            $server = 1;
        } else {
            $server = 0;
            $date = Date('Y-m-d H:i:s');
            if (isset($array['soapBody']['soapFault'])) {
                $status = $array['soapBody']['soapFault']['detail']['ns16TrackAndTraceLog']['Entry']['Code'];
                $desc = $array['soapBody']['soapFault']['detail']['ns16TrackAndTraceLog']['Entry']['Desc'];
            } else {
                $status = $array['envBody']['envFault']['faultstring'];
                $desc = '';
            }
        }
        $date = date(
            "Y-m-d H:i:s",
            strtotime(str_replace("/", "-", $date))
        );
        $id_status = TrackingModel::searchIdStatus(124, $status, true);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => !($server == 0),
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
