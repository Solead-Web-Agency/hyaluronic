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

class Carrier210 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $result = 'https://www.tnt.it/tracking/getXMLTrack?WT=1&ConsigNos='.$this->tracking_number;
        $url_page = simplexml_load_string(
            Tools::file_get_contents($result, true),
            'SimpleXMLElement',
            LIBXML_NOCDATA | LIBXML_NOBLANKS
        );

        $url = Tools::stripslashes($url_page);
        return $url;
    }
    public function track()
    {
        $url = $this->getResponse();
        $events = array();
        if ($url->RuntimeError) {
            $status = (string)$url->RuntimeError->Code;
            $desc = (string)$url->RuntimeError->Message;
            $date = Date('Y-m-d H:i:s');
            $success = false;
        } else {
            $success = true;
            $trace = $url->Consignment->StatusDetails;
            foreach ($trace as $key => $value) {
                $date = str_replace(
                    '/',
                    '-',
                    (string)$value->StatusDate
                );
                $date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $date)));
                $events[] = array(
                    'event_code' => (string)$value->StatusCode,
                    'event_description' => (string)$value->StatusDescription,
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(210, (string)$value->StatusCode, true)
                );
            }
            $status = (string)$url->Consignment->StatusDetails->StatusCode;
            $date = str_replace(
                '/',
                '-',
                (string)$url->Consignment->StatusDetails->StatusDate
            );
            $date = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $date)));
            $desc = (string)$url->Consignment->StatusDetails->StatusDescription;
        }

        $id_status = TrackingModel::searchIdStatus(210, $status, true);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
            'events' => array_reverse($events)
        ));
        return $status;
    }
}
