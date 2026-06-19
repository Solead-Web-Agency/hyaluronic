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

class Carrier4 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $postcode = Db::getInstance()->getValue(
            'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
            LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
            LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            WHERE oc.id_order_carrier = '.$this->id_order
        );
        $postcode = str_replace(' ', '', $postcode);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.gls-info.nl/Tracking?parcelNo='.$this->tracking_number.'&zipcode='.$postcode.'&lang=EN',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
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
        $table = $finder->query("//table[@id='lastscan']/tr/td");
        $trace = $finder->query("//table[@id='scandata_table']/tr");
        $events = array();
        if ($table->length) {
            $success = true;
            for ($i=1; $i < $trace->length; $i++) {
                $td = $finder->query("td", $trace->item($i));
                $datenode = (string)$td->item(0)->nodeValue;
                $date = str_replace('<br />', '', $datenode);
                $date = DateTime::createFromFormat('d-m-Y H:i', $date);
                $date = $date->format('Y-m-d H:i:s');
                $events[] = array(
                    'event_code' => (string)$td->item(6)->nodeValue,
                    'event_description' => trim((string)$td->item(7)->nodeValue),
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(3, (string)$td->item(6)->nodeValue, true)
                );
            }
            $status = (string)$table->item(6)->nodeValue;
            $datenode = (string)$table->item(0)->nodeValue;
            $date = str_replace('<br />', '', $datenode);
            $date = DateTime::createFromFormat('d-m-Y H:i', $date);
            $date = $date->format('Y-m-d H:i:s');
            $result_status = trim((string)$table->item(7)->nodeValue);
        } else {
            $success = false;
            $status = 'E206';
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }
        $id_status = TrackingModel::searchIdStatus(3, $status, true);
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

