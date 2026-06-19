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

class Carrier126 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("es","pt") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order_carrier.''
        );
        $postcode = Db::getInstance()->getValue(
            'SELECT a.postcode FROM '._DB_PREFIX_.'orders o
            LEFT JOIN '._DB_PREFIX_.'address a ON o.id_address_delivery=a.id_address
            LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order=oc.id_order
            WHERE oc.id_order_carrier = '.$this->id_order_carrier
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://m.gls-spain.es/e/'.$this->tracking_number.'/'.$postcode.'/'.$lang.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Cookie: culture=en; ASP.NET_SessionId=xkbxx3jpcm5b3chdb2e4am3e"
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
        $table = $finder->query("//div[@id='div-info-envio']");
        $events = array();
        if ($table->length) {
            $statusnode = $finder->query("//span[@id='estado']", $table->item(0));
            $datenode = $finder->query("//table[contains(@class, 'table-striped')]/tr/td[contains(@class, 'col-sm-2')]");
            $eventsnode = $finder->query("//table[contains(@class, 'table-striped')]/tr");
            if ($statusnode->length && $eventsnode->length) {
                $success = true;
                $match = array();
                for ($i=0; $i < $eventsnode->length; $i++) {
                    $eventnode = $finder->query("td", $eventsnode->item($i));
                    $date = (string)$eventnode->item(0)->nodeValue;
                    $desc = (string)$eventnode->item(1)->nodeValue;
                    if (preg_match('/(\d{1,2}\/\d{1,2}) (\d{1,2}:\d{1,2})/', $date, $match)) {
                        $date = DateTime::createFromFormat('d/m H:i', "{$match[1]} {$match[2]}");
                        $date = $date->format('Y-m-d H:i:s');
                        $result_status = '';
                    } else {
                        $date = date();
                    }
                    $events[] = array(
                        'event_code' => $desc,
                        'event_description' => $desc,
                        'event_date' => $date,
                        'id_status' => TrackingModel::searchIdStatus(126, trim($desc))
                    );
                }
                $event = end($events);
                $status = $event['event_code'];
                $result_status = $event['event_description'];
                $date = $event['event_date'];
            }
        } else {
            $success = false;
            $status = '105';
            $date = date("Y-m-d H:i:s");
            $err = $finder->query("//h2[contains(@class, 'title')]");
            $result_status = $err->length?$err->item(0)->nodeValue:'';
        }

        $id_status = TrackingModel::searchIdStatus(126, $status);

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
