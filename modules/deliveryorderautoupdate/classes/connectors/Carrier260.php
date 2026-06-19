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

class Carrier260 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $data= '{ "deliveryOrderNo":"'.$this->tracking_number.'" }';
        $appkey = 'e6428e3a-6563-469f-ae28-0db5c0059e05';
        $appsecret = 'b9c11814-ee1c-4279-bd1b-b58f6a9c87e9';
        $date= date("Y-d-j H:i:s");
        $dateurl= urlencode($date);
        $sign_row= 'app_key'.$appkey.'formatjsonmethodtr.order.tracking.gettimestamp'.$date.'v1.0'.$data.$appsecret;
        $sign= Tools::strtoupper(md5($sign_row));
        $uri = 'http://open.4px.com/router/api/service?method=tr.order.tracking.get&v=1.0&app_key='
        .$appkey.'&timestamp='.$dateurl.'&format=json&access_token=&sign='.$sign;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Accpet: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Content-Type: application/json'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $id_status = 0;
        $result = json_decode($result, true);
        return $result;
    }
    public function track()
    {
        $result = $this->getResponse();
        if ($result['result']) {
            $events = array_map(function ($e) {
                return array(
                    'event_code' => (string)$e['businessLinkCode'],
                    'event_description' => (string)$e['trackingContent'],
                    'event_date' => $e['occurDatetime'],
                    'id_status' => TrackingModel::searchIdStatus(260, (string)$e['businessLinkCode'])
                );
            }, array_reverse($result['data']['trackingList']));
            $id_status = TrackingModel::searchIdStatus(
                260,
                $result['data']['trackingList'][0]['businessLinkCode']
            );
        } else {
            /*
            $result = 0;
            $eventDate = null;
            // do nothing
            $result_status = 'no found case';
            $document = Tools::file_get_contents(dirname(__FILE__)."/labelcode_260.txt");
            $lines = explode("\n", $document);
            foreach ($lines as $newline) {
                if (strpos($newline, $result['data']['trackingList'][0]['businessLinkCode']) !== false) {
                    $label = explode("_", $newline);
                    $label_status = $label[1];
                    $status_text = $label[2];
                }
            }
            $status_date = Date('Y-m-d H:i:s');
            */
        }
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $result['result'] == 1,
            'status' => $result['result'] == 1 ?
            null : $result['data']['trackingList'][0]['businessLinkCode'],
            'desc' => $result['result'] == 1 ?
        null : $result['data']['trackingList'][0]['trackingContent'],
            'date' => $result['data']['trackingList'][0]['occurDatetime'],
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
