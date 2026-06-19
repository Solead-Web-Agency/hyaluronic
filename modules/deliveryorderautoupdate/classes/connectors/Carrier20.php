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

class Carrier20 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER20_id1');
        $password = Configuration::get('HL_CARRIER20_id2');
        $AccessLicenseNumber = Configuration::get('HL_CARRIER20_id3');

        $data= '<?xml version="1.0"?>
        <AccessRequest xml:lang="en-US">
             <AccessLicenseNumber>'.$AccessLicenseNumber.'</AccessLicenseNumber>
             <UserId>'.$username.'</UserId>
             <Password>'.$password.'</Password>
             </AccessRequest>
             <?xml version="1.0"?>
         <TrackRequest xml:lang="en-US">
                 <Request>
                     <TransactionReference>
                             <CustomerContext>Your Test Case Summary Description</CustomerContext>
                         </TransactionReference>
                         <RequestAction>Track</RequestAction>
                         <RequestOption>activity</RequestOption>
                 </Request>
            <TrackingNumber>'.$this->tracking_number.'</TrackingNumber>
        </TrackRequest>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://onlinetools.ups.com/ups.app/xml/Track",
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
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);

        $status_error = false;
        if (!isset($xml->Shipment)) {
            $result = 100;
            $error_code = (string)$xml->Response->Error->ErrorCode;
            $error_desc = (string)$xml->Response->Error->ErrorDescription;
            $result = TrackingModel::searchIdStatus(9, $error_code);
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $result
            ));
            return $status;
        }
        $traceResult = $xml->Shipment->Package;
        $trace = $traceResult->xpath('//Activity');
        $events = array_map(function ($e) {
            return array(
                'event_code' => (string)$e->Status->StatusCode->Code,
                'event_description' => (string)$e->Status->StatusType->Description,
                'event_date' => date("y-m-d H:i:s", strtotime(str_replace("/", "-", $e->Date.' '.$e->Time))),
                'id_status' => TrackingModel::searchIdStatus(9, (string)$e->Status->StatusCode->Code)
            );
        }, array_reverse($trace));
        $trace = current($trace);
        $status = (string)$trace->Status->StatusCode->Code;
        $id_status = TrackingModel::searchIdStatus(9, $status);
        $date = $trace->Date.' '.$trace->Time;
        $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
        $result_status = (string)$trace->Status->StatusType->Description;
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
