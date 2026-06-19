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

class Carrier56 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $access_key = Configuration::get('HL_CARRIER56_id1');

        $data= '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
        <env:Header/>
        <env:Body>
        <ns3:getPublicServiceShipmentDetails
        xmlns:ns2="http://www.schenker.com/SGI/v4_0"
        xmlns:ns3="http://www.schenker.com/CustomerServices/eBusiness/ShipmentService/v2">
        <AccessKey>'.$access_key.'</AccessKey>
        <in>
        <ns2:ApplicationArea>
        <ns2:requestId>NGES-TRACKING-144255386</ns2:requestId>
        <ns2:CreationDateTime>2020-04-20T11:42:33.867+01:00</ns2:CreationDateTime>
        </ns2:ApplicationArea>
        <ns2:referenceType>ff</ns2:referenceType>
        <ns2:referenceNumber>'.$this->tracking_number.'</ns2:referenceNumber>
        </in>
        </ns3:getPublicServiceShipmentDetails>
        </env:Body>
        </env:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://eschenker.dbschenker.com/webservice/trackingWebServiceV2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/xml",
                "Cookie: INGRESSCOOKIE=1587483340.164.1737.82550"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['ns3:', 'ns2:', 'SOAP:'], '', $response);
        $xml = simplexml_load_string($response);
        $id_status = 100;
        $tracking_number = $this->tracking_number;
        if (isset($xml->Body->Fault)) {
            $status_error = true;
            $status = (string)$xml->Body->Fault->faultstring;
            $status = str_replace($tracking_number, '', $status);
            $date = Date('Y-m-d H:i:s');
            $result_status = (string)$xml->Body->Fault->faultstring;
        } elseif (!isset($xml->Body->getPublicShipmentDetailsResponse->out->Shipment)) {
            $status_error = true;
            $status = (string)$xml->Body->getPublicShipmentDetailsResponse->out->RequestID;
            $date = Date('Y-m-d H:i:s');
            $result_status = (string)$xml->Body->getPublicShipmentDetailsResponse->out->RequestID;
        } else {
            $status_error = false;
            $traceResult = $xml->Body->getPublicShipmentDetailsResponse->out->Shipment->ShipmentBasicInfo;
            $trace = $traceResult->xpath('//StatusEvent');
            $trace = end($trace);
            $status = $trace->Status;
            $date = $trace->Date.' '.$trace->Time;
            $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
            $result_status = (string)$trace->StatusDescription;
        }
        $id_status = TrackingModel::searchIdStatus(56, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => !$status_error,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
        ));
        return $status;
    }
}
