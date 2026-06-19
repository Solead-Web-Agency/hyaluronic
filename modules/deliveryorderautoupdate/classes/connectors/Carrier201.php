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

class Carrier201 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $data = '<soapenv:Envelope
        xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:cxf="http://cxf.ws.app.tnt.fr/">
           <soapenv:Header/>
           <soapenv:Body>
              <cxf:trackingByConsignment>
                 <!--Optional:-->
                 <parcelNumber>'.$this->tracking_number.'</parcelNumber>
              </cxf:trackingByConsignment>
           </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://www.tnt.fr/service/tracking",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/xml"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
        $response = utf8_decode($response);
        $xml = simplexml_load_string($response);
        $Body = $xml->Body;
        if (isset($Body->Fault)) {
            $error_code = trim((string)$Body->Fault->faultstring);
            $result = TrackingModel::searchIdStatus(201, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'status' => $error_code,
                'desc' => $result_status,
                'date' => $date,
                'id_status' => $result,
            ));
            return $status;
        }
        $traceResult = $Body->trackingByConsignmentResponse->Parcel;
        $trace = $traceResult->events;
        //$trace = end($trace);
        $status = $traceResult->statusCode;
        $id_status = TrackingModel::searchIdStatus(201, $status);
        $date = $trace->requestDate;
        $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
        $result_status = (string)$traceResult->shortStatus;
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => true,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
        ));
        return $status;
    }
}
