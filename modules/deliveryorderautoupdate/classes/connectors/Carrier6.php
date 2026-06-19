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

class Carrier6 extends deliveryorderautoupdate\Carrier
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
            CURLOPT_ENCODING => "UTF-8",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/xml; charset: UTF-8"
            ),
        ));

        $response = curl_exec($curl);
        $response = utf8_decode($response);
        $response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $Body = $xml->Body;
        if (isset($Body->Fault) || empty($Body->trackingByConsignmentResponse->Parcel)) {
            $status = new Status(array(
                'id_order' => $this->id_order,
            ));
            return $status;
        }
        $status_error = false;
        $traceResult = $Body->trackingByConsignmentResponse->Parcel;
        $trace = $traceResult->events;
        $status = (string)$traceResult->statusCode;
        $id_status = 0;
        $id_status = TrackingModel::searchIdStatus(6, $status);
        $date = $trace->requestDate;
        $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
        $result_status = (string)$traceResult->longStatus;
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => true,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => array(
                array(
                    'event_code' => $status,
                    'event_description' => $result_status,
                    'event_date' => $date,
                    'id_status' => $id_status
                )
            )
        ));
        return $status;
    }
}
