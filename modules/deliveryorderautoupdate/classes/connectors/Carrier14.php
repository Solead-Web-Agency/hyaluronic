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

class Carrier14 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $postcode = Db::getInstance()->getValue(
            'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
            LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
            LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            WHERE oc.id_order_carrier = '.$this->id_order
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis={$this->tracking_number}{$postcode}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
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
        $id_status = '100';
        $events = array();
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML($response);
        $finder = new DomXPath($doc);
        $nodes = $finder->query("//table[contains(@class, 'tableHistoriqueColis')]");
        if ($nodes->length) {
            $row = $finder->query("tr[contains(@class, 'bandeauText')]", $nodes->item(0));
            if ($row->length) {
                $success = true;
                for ($i=0; $i < $row->length; $i++) {
                    $dataNode = $finder->query("td[contains(@class, 'tdText')]", $row->item($i));
                    $status = (string)$dataNode->item(1)->nodeValue;
                    $date = (string)$dataNode->item(0)->nodeValue;
                    $date = DateTime::createFromFormat('d/m/Y H:i:s', $date.' 00:00:00');
                    $date = $date->format('Y-m-d H:i:s');
                    $events[] = array(
                        'event_code' => $status,
                        'event_description' => $status,
                        'event_date' => $date,
                        'id_status' => TrackingModel::searchIdStatus(14, $status),
                    );
                }
                $events = array_reverse($events);
                $event = end($events);
                $status = $event['event_code'];
                $date = $event['event_date'];
                $result_status = $event['event_description'];
            }
        } else {
            $success = false;
            $status = '105';
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }

        $id_status = TrackingModel::searchIdStatus(14, $status);
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
