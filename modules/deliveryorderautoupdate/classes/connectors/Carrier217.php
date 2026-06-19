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

class Carrier217 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $CodiceCliente = Configuration::get('HL_CARRIER217_id1');
        $Controllo = $this->order_reference.'FERCAM'.$CodiceCliente ;
        $Controllo = md5($Controllo);
        $Controllo = Tools::substr($Controllo, 0, 10);
        //echo $Controllo ; exit ;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tracktrace.fercamapps.com/DirektRef/'.$this->order_reference.'/'.$CodiceCliente.'/'.$Controllo.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept-Language: it',
            'Cookie: ARRAffinity=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ARRAffinitySameSite=ca3c1e581f05eb74edd90e6b97659d62bcf6e282c3dfc021b1ca35fd9bac1dd5; ASP.NET_SessionId=ocvti3htfecc23no2yj2uj0p'
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
        $events = array();
        $li = $finder->query("//ul[contains(@class, 'timeline')]/li");
        if ($li->length) {
            $success = true;
            $timeLabelNode = $finder->query('span[@class="bg-orange"]', $li->item(0));
            if ($timeLabelNode->length) {
                $timeLabel = trim((string)$timeLabelNode->item(0)->nodeValue);
            } else {
                $timeLabel = '';
            }
            for ($i=0; $i < $li->length; $i++) {
                $timelineNode = $finder->query('div[@class="timeline-item"]/div[@class="timeline-body"]', $li->item($i));
                $locationNode = $finder->query('div[@class="timeline-item"]/h3[@class="timeline-header"]/a', $li->item($i));
                $statusNode = $finder->query('div[@class="movement"]/span[@class="descMov"]', $timelineNode->item(0));
                if (!$statusNode->length) {
                    continue;
                }
                $dateNode = $finder->query('div[@class="timeline-item"]/span[@class="time"]', $li->item($i));
                $status = (string)$statusNode->item(0)->nodeValue;
                $des = $status;
                if (!$status) {
                    $status = $timeLabel;
                }
                if ($locationNode->length) {
                    $location = (string)$locationNode->item(0)->nodeValue;
                    if ($location) {
                        $des .= ' - '.$location;
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
                $dateStr = trim((string)$dateNode->item(0)->nodeValue);
                $date = str_replace('/', '-', $dateStr);
                if ($date === false) {
                    $date = str_replace('/', '-', $dateStr);
                }
                $date = date('Y-m-d H:i:s', strtotime($date));
                $events[] = array(
                    'event_code' => $status,
                    'event_description' => $des,
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(217, $status),
                );
            }
            $events = array_reverse($events);
            $event = end($events);
            $status = $event['event_code'];
            $date = $event['event_date'];
            $result_status = $event['event_description'];
        } else {
            $success = false;
            $status = 'Tracking was not found';
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }
        $id_status = TrackingModel::searchIdStatus(217, $status);
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
