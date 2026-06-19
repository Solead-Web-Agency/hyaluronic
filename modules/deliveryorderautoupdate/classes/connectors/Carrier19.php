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

class Carrier19 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER19_id1');
        $password = Configuration::get('HL_CARRIER19_id2');
        $AccessLicenseNumber = Configuration::get('HL_CARRIER19_id3');

        $data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:v1="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0"
        xmlns:v3="http://www.ups.com/XMLSchema/XOLTWS/Track/v2.0"
        xmlns:v11="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0">
        <soapenv:Header>
        <v1:UPSSecurity>
        <v1:UsernameToken>
        <v1:Username>'.$username.'</v1:Username>
        <v1:Password>'.$password.'</v1:Password>
        </v1:UsernameToken>
        <v1:ServiceAccessToken>
        <v1:AccessLicenseNumber>'.$AccessLicenseNumber.'</v1:AccessLicenseNumber>
        </v1:ServiceAccessToken>
        </v1:UPSSecurity>
        </soapenv:Header>
        <soapenv:Body>
        <v3:TrackRequest>
        <v11:Request>
        <v11:RequestOption>1</v11:RequestOption>
        <v11:TransactionReference>
        <v11:CustomerContext>Your Test Case Summary Description</v11:CustomerContext>
        </v11:TransactionReference>
        </v11:Request>
        <v3:InquiryNumber>'.$this->tracking_number.'</v3:InquiryNumber>
        </v3:TrackRequest>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://onlinetools.ups.com/webservices/Track",
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

        curl_close($curl);
        $response = str_ireplace(['soapenv:', 'trk:', 'common:', 'err:'], '', $response);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $body = $xml->Body;

        $id_status = 0;
        if (isset($body->Fault)) {
            $error_code = (string)$body->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Code;
            $error_desc = (string)$body->Fault->detail->Errors->ErrorDetail->PrimaryErrorCode->Description;
            $result = TrackingModel::searchIdStatus(9, $error_code, true);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $result
            ));
            return $status;
        }
        $traceResult = $xml->Body->TrackResponse->Shipment->Package;
        $trace = $traceResult->xpath('//Activity');
        $events = array_map(function ($e) {
            return array(
                'event_code' => (string)$e->Status->Code,
                'event_description' => (string)$e->Status->Description,
                'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e->Date.' '.$e->Time))),
                'id_status' => TrackingModel::searchIdStatus(9, (string)$e->Status->Code, true)
            );
        }, array_reverse($trace));
        $trace = current($trace);
        $status = (string)$trace->Status->Code;
        $id_status = TrackingModel::searchIdStatus(9, $status);
        $date = $trace->Date.' '.$trace->Time;
        $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
        $result_status = (string)$trace->Status->Description;
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
