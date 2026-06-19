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

class Carrier25 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $credentials = Db::getInstance()->getValue(
            'SELECT REPLACE(c2.url,"@","'.$this->tracking_number.'")
            FROM '._DB_PREFIX_.'order_carrier oc
          LEFT JOIN '._DB_PREFIX_.'carrier c ON oc.id_carrier=c.id_carrier
        LEFT JOIN '._DB_PREFIX_.'carrier c2 ON c.id_reference=c2.id_reference AND c2.deleted=0
          WHERE oc.id_order_carrier = '.$this->id_order.''
        );

        $credentials = explode('_', $credentials);
        $credentials = end($credentials);
        $shippingcustomercenter = substr($credentials,0,3);
        $shippingcustomer = substr($credentials,3,8);
        $tracking_number = explode('_', $this->tracking_number);
        $tracking_number = current($tracking_number);
        $data = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:car="http://www.cargonet.software/">
           <soap:Header/>
           <soap:Body>
              <car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
                 <car:customer_center>3</car:customer_center>
                 <car:customer>1064</car:customer>
                 <!--Optional:-->
                 <car:password>Pr2%5sHg</car:password>
                 <!--Optional:-->
                 <car:reference>'.$tracking_number.'</car:reference>
                 <!--Optional:-->
                 <car:shipping_date></car:shipping_date>
                 <car:shipping_customer_center>'.$shippingcustomercenter.'</car:shipping_customer_center>
                 <car:shipping_customer>'.$shippingcustomer.'</car:shipping_customer>
              </car:getShipmentTraceByReferenceGlobalWithCenterAsArray>
           </soap:Body>
        </soap:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            'Host: webtrace.dpd.fr',
            'Content-Type: application/soap+xml',
            'Accept-Encoding: gzip,deflate',
            'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
            'Cookie: __cfduid=def5d54a460a440781c23b59b07fda7371607457320; pers=!Q0wB/bV2PZfYll6rEm4m3rxJRTAJ'.
            'BMh81p3nQaI6ifVr84HKfk8L6MM7UTYoQSatsP//lq0OyJRaWUUAGr38ecGSvl2Dg2LX0c0TmjI='
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
        $xml = simplexml_load_string($response);

        $status_error = false;
        $events = array();
        $id_status = 0;
        if (isset($xml->Body->Fault)) {
            $status_error = false;
            $traceResult = $xml->Body->Fault;
            $status = (string)$traceResult->Code->Value;
            $result_status = (string)$traceResult->Reason->Text;
            $date = date('Y-m-d H:i:s');
        } else {
            $traceResult = $xml->Body->getShipmentTraceByReferenceGlobalWithCenterAsArrayResponse;
            $traceResult = $traceResult->getShipmentTraceByReferenceGlobalWithCenterAsArrayResult;
            $Traces = $traceResult->clsShipmentTrace->Traces;
            $traces = $Traces->xpath('*');
            $traceResult = $traceResult->clsShipmentTrace;
            $error = (array)$traceResult->LastError;
            if (count($error)) {
                $status_error = false;
                $status = (string)$traceResult->LastError;
                $date = date('Y-m-d H:i:s');
                $result_status = '';
            } else {
                $trace = isset($traceResult->Traces->clsTrace)?$traceResult->Traces->clsTrace:false;
                $status_error = true;
                if ($trace) {
                    $events = array_map(function ($e) {
                        $date = $e->ScanDate.' '.$e->ScanTime;
                        $date = date("Y-m-d H:i:s", strtotime($date));
                        return array(
                            'event_code' => (string)$e->StatusNumber,
                            'event_description' => (string)$e->StatusDescription,
                            'event_date' => $date,
                            'id_status' => TrackingModel::searchIdStatus(8, (string)$e->StatusNumber)
                        );
                    }, array_reverse($traces));
                    $status = (string)$trace->StatusNumber;
                    $date = $trace->ScanDate.' '.$trace->ScanTime;
                    $date = date("Y-m-d H:i:s", strtotime($date));
                    $result_status = (string)$trace->StatusDescription;
                } else {
                    $status = '';
                    $date = date('Y-m-d H:i:s');
                    $result_status = '';
                }
            }
        }

        $id_status = TrackingModel::searchIdStatus(8, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => $status_error,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
