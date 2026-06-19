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

class Carrier35 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $data = '%3CTrackRequest%20USERID=%22758HELLO7881%22%3E%0A%3CTrackID%20ID=%22'.$this->tracking_number.
        '%22%3E%3C/TrackID%3E%3C/TrackRequest%3E';
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://production.shippingapis.com/ShippingAPI.dll?API=TrackV2&XML='.$data.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['ns1:', 'SOAP:'], '', $response);
        $xml = simplexml_load_string($response);

        $rs = $xml->TrackInfo;
        $TrackSummary = (string)$rs->TrackSummary;
        $id_status = 100;
        $trace = (array)$rs->TrackDetail;
        $events = array_map(function ($e) {
            $match = array();
            $datetime = array();
            if (preg_match('/, (\w* \d{1,2}, \d{4}),/', $e, $match)) {
                $datetime[] = $match[1];
            } else {
                $datetime[] = Date('Y-m-d');
            }
            if (preg_match('/, (\d{1,2}:\d{1,2} (am|pm)),/', $e, $match)) {
                $datetime[] = $match[1];
            } else {
                $datetime[] = Date('H:i:s');
            }
            $event = explode(',', $e);
            $status = $event[0];
            $date = implode(' ', $datetime);
            return array(
                'event_code' => (string)$status,
                'event_description' => (string)$status,
                'event_date' => date('Y-d-d H:i:s', strtotime($date)),
                'id_status' => TrackingModel::searchIdStatus(35, (string)$status)
            );
        }, array_reverse($trace));
        $match = array();
        $datetime = array();
        if (preg_match('/on (\w* \d{1,2}, \d{4})/', $TrackSummary, $match)) {
            $datetime[] = $match[1];
        } else {
            $datetime[] = Date('Y-m-d');
        }
        if (preg_match('/at (\d{1,2}:\d{1,2} (am|pm))/', $TrackSummary, $match)) {
            $datetime[] = $match[1];
        } else {
            $datetime[] = Date('H:i:s');
        }
        $date = implode(' ', $datetime);
        $date = date('Y-d-d H:i:s', strtotime($date));
        $status = $TrackSummary;
        if (!isset($rs->TrackDetail)) {
            $success = false;
            $result_status = '';
        } else {
            $success = true;
            $detail = (array)$rs->TrackDetail;
            $result_status = count($detail)?$detail[0]:'';
        }
        $id_status = TrackingModel::searchIdStatus(35, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
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
