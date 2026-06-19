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

class Carrier220 extends deliveryorderautoupdate\Carrier
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

        $curl = curl_init();
        $data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
         xmlns:brt="http://brt_trackingbybrtshipmentid.wsbeans.iseries/">
           <soapenv:Header/>
           <soapenv:Body>
              <brt:brt_trackingbybrtshipmentid>
                 <arg0>
                    <LINGUA_ISO639_ALPHA2>'.$lang.'</LINGUA_ISO639_ALPHA2>
                    <SPEDIZIONE_ANNO>0</SPEDIZIONE_ANNO>
                    <SPEDIZIONE_BRT_ID>'.$this->tracking_number.'</SPEDIZIONE_BRT_ID>
                 </arg0>
              </brt:brt_trackingbybrtshipmentid>
           </soapenv:Body>
        </soapenv:Envelope>';

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: text/xml;charset=UTF-8"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['ns1:', 'SOAP:', 'ns2:'], '', $response);
        $response = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$response), true);
        $array = $array['Body'];
        $result_ = 0;
        $events = array();
        if (isset($array['Fault'])) {
            $success = false;
            $status = $array['Fault']['faultcode'];
            $status_event = $array['Fault']['faultstring'];
            $date = date('Y-m-d H:i:s');
        } else {
            $success = true;
            $array = $array['brt_trackingbybrtshipmentidResponse'];
            foreach (array_reverse($array["return"]["LISTA_EVENTI"]) as $key => $e) {
                if (!$e['EVENTO']["DATA"]) {
                    continue;
                }
                $date = DateTime::createFromFormat('d.m.Y H.i', (string)$e['EVENTO']["DATA"].' '.(string)$e['EVENTO']['ORA']);
                $events[] = array(
                    'event_code' => (string)$e["EVENTO"]["ID"],
                    'event_description' => (string)$e["EVENTO"]["DESCRIZIONE"],
                    'event_date' => $date->format('Y-m-d H:i:s'),
                    'id_status' => TrackingModel::searchIdStatus(220, (string)$e["EVENTO"]["ID"])
                );
            }
            $array1 = $array["return"]["LISTA_EVENTI"][0]["EVENTO"];
            if (!empty($array1["DATA"]) && !empty($array1['ORA'])) {
                $date = DateTime::createFromFormat('d.m.Y H.i', $array1["DATA"].' '.$array1['ORA']);
            } else {
                $date = new DateTime();
            }
            $date = $date->format('Y-m-d H:i:s');
            $status_event = !empty($array1["DESCRIZIONE"])?$array1["DESCRIZIONE"]:'';
            $status = !empty($array1["ID"])?$array1["ID"]:'';
        }
        $result_ = TrackingModel::searchIdStatus(220, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $status_event,
            'date' => $date,
            'id_status' => $result_,
            'events' => $events
        ));
        return $status;
    }
}
