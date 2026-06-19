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

class Carrier221 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $client_id = Configuration::get('HL_CARRIER221_id1');

        $curl = curl_init();
        $data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
         xmlns:get="http://getidspedizionebyidcollo.wsbeans.iseries/">
           <soapenv:Header/>
           <soapenv:Body>
              <get:getidspedizionebyidcollo>
                 <arg0>
                    <CLIENTE_ID>'.$client_id.'</CLIENTE_ID>
                    <COLLO_ID>'.$this->tracking_number.'</COLLO_ID>
                 </arg0>
              </get:getidspedizionebyidcollo>
           </soapenv:Body>
        </soapenv:Envelope>';

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo",
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
        $response1 = curl_exec($curl);
        curl_close($curl);

        $response1 = str_ireplace(['soap:', 'ns2:'], '', $response1);
        $response1 = simplexml_load_string($response1);
        $array = json_decode(json_encode($response1), true);
        $id_spedizione = $array['Body']['getidspedizionebyidcolloResponse']['return']['SPEDIZIONE_ID'] ;
        //print_r($array);exit;
        $curl = curl_init();
        $data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
         xmlns:brt="http://brt_trackingbybrtshipmentid.wsbeans.iseries/">
           <soapenv:Header/>
           <soapenv:Body>
              <brt:brt_trackingbybrtshipmentid>
                 <arg0>
                    <LINGUA_ISO639_ALPHA2>EN</LINGUA_ISO639_ALPHA2>
                    <SPEDIZIONE_ANNO>0</SPEDIZIONE_ANNO>
                    <SPEDIZIONE_BRT_ID>'.$id_spedizione.'</SPEDIZIONE_BRT_ID>
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
            $events = array_map(function ($e) {
                $date = DateTime::createFromFormat('d.m.Y H.i', ($e['EVENTO']["DATA"]?$e['EVENTO']["DATA"]:'').' '.($e['EVENTO']['ORA']?$e['EVENTO']['ORA']:''));
                return array(
                    'event_code' => $e["EVENTO"]["ID"]?(string)$e["EVENTO"]["ID"]:'',
                    'event_description' => $e["EVENTO"]["DESCRIZIONE"]?(string)$e["EVENTO"]["DESCRIZIONE"]:'',
                    'event_date' => $date?$date->format('Y-m-d H:i:s'):date('Y-m-d H:i:s'),
                    'id_status' => TrackingModel::searchIdStatus(220, $e["EVENTO"]["ID"]?(string)$e["EVENTO"]["ID"]:'')
                );
            }, array_reverse($array["return"] ["LISTA_EVENTI"]));
            $array1 = $array["return"]["LISTA_EVENTI"][0]["EVENTO"];
            $date = DateTime::createFromFormat('d.m.Y H.i', ($array1["DATA"]?$array1["DATA"]:'').' '. ($array1['ORA']?$array1['ORA']:''));
            $date = $date?$date->format('Y-m-d H:i:s'):date('Y-m-d H:i:s');
            $status_event = $array1["DESCRIZIONE"]?$array1["DESCRIZIONE"]:'';
            $status = $array1["ID"]?$array1["ID"]:'';
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
