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

class Carrier200 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER200_id1');
        $password = Configuration::get('HL_CARRIER200_id2');
        $result_carrier = array();

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://express.tnt.com/expressconnect/track.do?xml_in=%3C?xml%20version=%221.0%22%20encoding='.
          '%22UTF-8%22%20standalone=%22no%22?%3E%0A%3CTrackRequest%3E%0A%3CSearchCriteria%3E%0A%3CConsignmentNumber%3E'.
          $this->tracking_number.'%3C/ConsignmentNumber%3E%0A%3C/SearchCriteria%3E%0A%3CLevelOfDetail%3E%0A%3CComplete%20originAddre'.
          'ss=%22true%22%20destinationAddress=%22true%22%20package=%22true%22%20shipment=%22true%22/%3E%0A%3C/LevelOfDetail%3E'.
          '%0A%3C/TrackRequest%3E',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: text/xml",
            "Authorization: Basic ". base64_encode($username.':'.$password),
            "Cookie: BIGipServerexpress.tnt.com_pool_7=2676609546.20992.0000"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        libxml_use_internal_errors(true);
        $response = str_ireplace(['ns1:', 'access="full"'], '', $response);
        $xml = simplexml_load_string($response);
        $id_status = 100;
        if ($xml === false) {
            $success = false;
            $status = 0;
            $desc = '';
            $date = Date('Y-m-d H:i:s');
        } elseif (isset($xml->HEAD)) {
            $success = false;
            $status = (string)$xml->HEAD->TITLE;
            $desc = $status;
            $date = Date('Y-m-d H:i:s');
        } elseif (isset($xml->Error)) {
            $success = false;
            $status = (string)$xml->Error->Code;
            $desc = (string)$xml->Error->Message;
            $date = Date('Y-m-d H:i:s');
        } elseif (!isset($xml->Consignment->StatusData)) {
            $success = false;
            $status = (string)$xml->Consignment->SummaryCode;
            $desc = 'Invalid number';
            $date = Date('Y-m-d H:i:s');
        } else {
            $success = true;
            $traceResult = $xml->TrackResponse;
            $trace = $traceResult->xpath('//StatusData');
            $trace = current($trace);
            $status = trim((string)$trace->StatusCode);
            $date = $trace->LocalEventDate.' '.$trace->LocalEventTime;
            $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
            $desc = (string)$trace->StatusDescription;
        }

        $id_status = TrackingModel::searchIdStatus(200, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
        ));
        return $status;
    }
}
