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

class Carrier179 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tracking.dpd.ro/?shipmentNumber='.$this->tracking_number.'',
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
        $id_status = '100';
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML($response);
        $finder = new DomXPath($doc);
        $table = $finder->query("//table[@class='standard-table']/tr");
        $events = array();
        if ($table->length > 1) {
            for ($i=1; $i < $table->length; $i++) {
                $tr = $finder->query('td', $table->item($i));
                $status = $tr->item(2)->C14N();
                $status = str_replace('<td>', '', $status);
                $status = str_replace('</td>', '', $status);
                $j = strpos($status, '<br>');
                if ($j !== false) {
                    $status = Tools::substr($status, 0, $j);
                }
                $datenode = (string)$tr->item(0)->nodeValue;
                $timenode = (string)$tr->item(1)->nodeValue;
                $date = $datenode.' '.$timenode;
                $date = DateTime::createFromFormat('d.m.Y H:i:s', $date);
                $date = $date->format('Y-m-d H:i:s');
                $result_status = (string)$tr->item(2)->nodeValue;
                $events[] = array(
                    'event_code' => $status,
                    'event_description' => $result_status,
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(179, $status)
                );
            }
            $tr = $finder->query('td', $table->item($table->length-1));
            $success = true;
            $status = $tr->item(2)->C14N();
            $status = str_replace('<td>', '', $status);
            $status = str_replace('</td>', '', $status);
            $i = strpos($status, '<br>');
            if ($i !== false) {
                $status = Tools::substr($status, 0, $i);
            }
            $datenode = (string)$tr->item(0)->nodeValue;
            $timenode = (string)$tr->item(1)->nodeValue;
            $date = $datenode.' '.$timenode;
            $date = DateTime::createFromFormat('d.m.Y H:i:s', $date);
            $date = $date->format('Y-m-d H:i:s');
            $result_status = (string)$tr->item(2)->nodeValue;
        } else {
            $success = false;
            $status = '404';
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }

        $id_status = TrackingModel::searchIdStatus(179, $status);
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
