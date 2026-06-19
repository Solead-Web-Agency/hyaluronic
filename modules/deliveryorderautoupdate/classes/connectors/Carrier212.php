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

class Carrier212 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tracking.nexive.it/?b='.$this->tracking_number.'&lang=en',
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
        if (!$response) {
            return new Status();
        }
        d($response);
        $month_en = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $month_it = array('gen','feb','mar','apr','mag','giu','lug','ago','set','ott','nov','dic');
        $id_status = 100;
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML($response);
        $finder = new DomXPath($doc);
        $nodes = $finder->query("//div[contains(@class, 'mainState') and contains(@class, 'active')]");
        if ($nodes->length) {
            $stateDetailNote = $finder->query("div[contains(@class, 'stateDetail')]", $nodes->item(0));
            $dataNode = $finder->query("div", $stateDetailNote->item(0));
            if ($dataNode->length) {
                $success = true;
                $status = (string)$dataNode->item(0)->nodeValue;
                $date = (string)$dataNode->item(4)->nodeValue;
                $date = str_replace($month_it, $month_en, $date);
                $date = date_create_from_format('j M Y, H:i', $date);
                $date = $date->format('Y-m-d H:i:s');
                $desc = $finder->query("div[contains(@class, 'nx_text')]", $dataNode->item(6));
                $result_status = (string)$desc->item(0)->nodeValue;
            }
        } else {
            $nodes = $finder->query("//div[@class='tile']");
            $block = $nodes->item(0);
            $statusNode = $finder->query("span[contains(@class, 'nx_title')]", $block);
            $descNode = $finder->query("div[contains(@class, 'tile-body')]", $block);
            $descNode = $finder->query("div[contains(@class, 'tile-row')]", $descNode->item(0));
            $descNode = $finder->query("div[contains(@class, 'nx_text')]", $descNode->item(0));
            $success = false;
            $status = (string)$statusNode->item(0)->nodeValue;
            $date = date("Y-m-d H:i:s");
            $result_status = (string)$descNode->item(0)->nodeValue;
        }

        $id_status = TrackingModel::searchIdStatus(212, $status);
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
