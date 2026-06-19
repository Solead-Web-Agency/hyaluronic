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

class Carrier1 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $account = Configuration::get('HL_CARRIER1_id1');
        $passwords = Configuration::get('HL_CARRIER1_id2');
        $tracking_number = $this->tracking_number;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.coliposte.fr/tracking-chargeur-cxf/TrackingServiceWS/track?accountNumber='.
            $account.'&password='.$passwords.'&skybillNumber='.$tracking_number.'',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $response = str_ireplace(['soapenv:', 'ns1:', 'soap:', 'xsi:'], '', $response);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $id_status = 0;
        $rs = $xml->Body->trackResponse->return;
        if ((string)$rs->errorCode !== '0') {
            $success = false;
            $status = (string)$rs->errorCode;
            $date = date("Y-m-d H:i:s");
            $result_status = (string)$rs->errorMessage;
        } else {
            $success = true;
            $status = (string)$rs->eventCode;
            $date = (string)$rs->eventDate;
            $date = date('Y-m-d H:i:s', strtotime($date));
            $result_status = (string)$rs->eventLibelle;
        }

        $id_status = TrackingModel::searchIdStatus(1, $status);
        $events = array(
            array(
                'event_code' => $status,
                'event_description' => $result_status,
                'event_date' => $date,
                'id_status' => $id_status,
            )
        );
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
