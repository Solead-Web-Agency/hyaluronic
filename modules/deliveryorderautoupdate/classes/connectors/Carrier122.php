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

class Carrier122 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $subscrid = Configuration::get('HL_CARRIER122_id1');
        $username = Configuration::get('HL_CARRIER122_id2');
        $password = Configuration::get('HL_CARRIER122_id3');

        $data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:est="http://www.estafeta.com/">
        <soapenv:Header/>
        <soapenv:Body>
        <est:ExecuteQuery>
        <!--Optional:-->
        <est:suscriberId>'.$subscrid.'</est:suscriberId>
        <!--Optional:-->
        <est:login>'.$username.'</est:login>
        <!--Optional:-->
        <est:password>'.$password.'</est:password>
        <!--Optional:-->
        <est:searchType>
        <!--Optional:-->
        <est:waybillList>
        <!--Optional:-->
        <est:waybillType>G</est:waybillType>
        <!--Optional:-->
        <est:waybills>
        <!--Zero or more repetitions:-->
        <est:string>'.$this->tracking_number.'</est:string>
        </est:waybills>
        </est:waybillList>
        <!--Optional:-->
        <est:type>L</est:type>
        </est:searchType>
        <!--Optional:-->
        <est:searchConfiguration>
        <est:historyConfiguration>
        <est:includeHistory>1</est:includeHistory>
        <!--Optional:-->
        <est:historyType>LAST_EVENT</est:historyType>
        </est:historyConfiguration>
        <!--Optional:-->
        <est:filterType>
        <est:filterInformation>0</est:filterInformation>
        <!--Optional:-->
        <est:filterType></est:filterType>
        </est:filterType>
        </est:searchConfiguration>
        </est:ExecuteQuery>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://tracking.estafeta.com/Service.asmx",
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
                "Content-Type: text/xml"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
        $xml = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$xml), true);
        $error = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCode'];
        $events = array();
        if (isset($error) && $error) {
            $status = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCode'];
            $date = Date('Y-m-d H:i:s');
            $desc = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']['errorCodeDescriptionENG'];
            if (is_array($desc)) {
                $desc = implode(';', $desc);
            } else {
                $desc = (string)$desc;
            }
            $server = 0;
            $id_status = TrackingModel::searchIdStatus(122, $status);
        } else {
            $server = 1;
         /*   $status = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult']
            ['trackingData']['TrackingData']['statusENG'];*/
            $queryresult = $array['soapBody']['ExecuteQueryResponse']['ExecuteQueryResult'];
            $history = $queryresult['trackingData']['TrackingData']['history'];
            if (isset($history['History'])) {
                $trace = array_values($history);
                $events = array_map(function ($e) {
                    $id_status = TrackingModel::searchIdStatus(122, $e['eventId']);
                    $event = array(
                        'event_code' => $e['eventId'],
                        'event_description' => $e['eventDescriptionENG'],
                        'event_date' => date(
                            "Y-m-d H:i:s",
                            strtotime(str_replace("/", "-", $e['eventDateTime']))
                        ),
                        'id_status' => $id_status
                    );
                    return $event;
                }, array_reverse($trace));
                if (count($events)) {
                    $event = $events[count($events)-1];
                    $date = $event['event_date'];
                    $desc = $event['event_description'];
                    $status = $event['event_code'];
                    $id_status = $event['id_status'];
                }
            } else {
                $date = $queryresult['trackingData']['TrackingData']['deliveryData']['deliveryDateTime'];
                $date = date(
                    "Y-m-d H:i:s",
                    strtotime(str_replace("/", "-", $date))
                );
                $desc = $queryresult['trackingData']['TrackingData']['statusENG'];
                $status = $queryresult['trackingData']['TrackingData']['statusENG'];
                $id_status = TrackingModel::searchIdStatus(122, $status);
                $events[] = array(
                    'event_code' => $status,
                    'event_description' => $desc,
                    'event_date' => $date,
                    'id_status' => $id_status
                );
            }
        }
        $date = date(
            "Y-m-d H:i:s",
            strtotime(str_replace("/", "-", $date))
        );
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
