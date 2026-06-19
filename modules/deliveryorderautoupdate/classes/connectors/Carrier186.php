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

class Carrier186 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://dpd.pt/track-and-trace?reference='.$this->tracking_number,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Cookie: SERVERID=sf1'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML($response);
        $finder = new DomXPath($doc);
        $table = $finder->query("//table[@class='table']/tbody/tr");
        $events = array();
        if ($table->length) {
            for ($i=0; $i < $table->length; $i++) {
                $td = $finder->query('td', $table->item($i));
                $status = (string)$td->item(0)->nodeValue;
                $datenode = (string)$td->item(1)->nodeValue;
                $date = DateTime::createFromFormat('Y/m/d H:i', $datenode);
                $date = $date->format('Y-m-d H:i:s');
                $result_status = (string)$td->item(2)->nodeValue;
                $events[] = array(
                    'event_code' => $status,
                    'event_description' => $result_status,
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(186, $status)
                );
            }
            $success = true;
            $event = $events[0];
            $status = $event['event_code'];
            $date = $event['event_date'];
            $result_status = $event['event_description'];
        } else {
            $success = false;
            $status = 'PARCEL NOT FOUND';
            $date = date("Y-m-d H:i:s");
            // $err = $finder->query("//h2[contains(@class, 'title')]");
            $result_status = '';
        }

        $id_status = TrackingModel::searchIdStatus(186, $status);
        $events = array_reverse($events);
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
