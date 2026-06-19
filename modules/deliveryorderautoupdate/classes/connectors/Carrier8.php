<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

class Carrier8 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $tracking_number = $this->tracking_number;
        if (Tools::substr($tracking_number, 0, 3) != "250") {
            $tracking_number = '250' . $tracking_number;
        }
        $data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
           <soap:Header/>
           <soap:Body>
              <car:getShipmentTrace>
                 <car:customer_center>3</car:customer_center>
                 <car:customer>1064</car:customer>
                 <!--Optional:-->
                 <car:password>Pr2%5sHg</car:password>
                 <!--Optional:-->
                 <car:shipmentnumber>'.$tracking_number.'</car:shipmentnumber>
              </car:getShipmentTrace>
           </soap:Body>
        </soap:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/soap+xml",
                "Cookie: pers=rd3o00000000000000000000ffff0a65143bo80"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $traceResult = $xml->Body->getShipmentTraceResponse->getShipmentTraceResult;
        $status_error = false;
        $error = (array)$traceResult->LastError;
        $id_status = 100;
        if (count($error)) {
            $status_error = true;
            $result = 100;
            $error_code = (string)$traceResult->LastError;
            $result = TrackingModel::searchIdStatus(8, $error_code, true);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'id_status' => $result
            ));
        } else {
            $traces = (array)$traceResult->Traces;
            $traces = $traces['clsTrace'];
            $events = array_map(function ($e) {
                return array(
                    'event_code' => (string)$e->StatusNumber,
                    'event_description' => (string)$e->StatusDescription,
                    'event_date' => date("Y-m-d H:i:s", strtotime($e->ScanDate.' '.$e->ScanTime)),
                    'id_status' => TrackingModel::searchIdStatus(8, (string)$e->StatusNumber, true)
                );
            }, array_reverse($traces));
            $events = array_filter($events, function($e) {
                return array_search($e['event_code'], array('460', '450', '461', '24')) === false;
            });
            $event = end($events);
            $status = $event['event_code'];
            $id_status = $event['id_status'];
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
        }
        return $status;
    }
}
