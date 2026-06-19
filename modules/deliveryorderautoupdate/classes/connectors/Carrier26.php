<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

class Carrier26 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tracking.dpd.de/rest/plc/en_NL/'.$this->tracking_number.'',
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
        $response = json_Decode($response, true);
        $id_status = 0;
        $result_carrier = array();
        $events = array();
        if ($response &&
            isset($response['parcellifecycleResponse']['parcelLifeCycleData']['statusInfo'])
        ) {
            $rs = $response['parcellifecycleResponse']['parcelLifeCycleData']['statusInfo'];
            $events = array_map(function ($e) {
                if (isset($e['date'])) {
                    $datenode = (string)$e['date'];
                    $date = DateTime::createFromFormat('d.m.Y, H:i', $datenode);
                    if ($date != false) {
                        $date = $date->format('Y-m-d H:i:s');
                    } else {
                        $date = date("Y-m-d H:i:s");
                    }
                } else {
                    $date = date("Y-m-d H:i:s");
                }
                return array(
                    'event_code' => (string)$e['status'],
                    'event_description' => count($e['description']['content'])?$e['description']['content'][0]:$e['label'],
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(26, (string)$e['status'])
                );
            }, $rs);
            $rs = array_filter($rs, function ($i) {
                return $i['statusHasBeenReached'];
            });
            $rs = $rs[count($rs)-1];
            $success = true;
            if (isset($rs['date'])) {
                $datenode = (string)$rs['date'];
                $date = DateTime::createFromFormat('d.m.Y, H:i', $datenode);
                $date = $date->format('Y-m-d H:i:s');
            } else {
                $date = date("Y-m-d H:i:s");
            }
            $status = (string)$rs['status'];
            $result_status = count($rs['description']['content'])?$rs['description']['content'][0]:$rs['label'];
        } else {
            $success = false;
            $status = '404';
            $date = date("Y-m-d H:i:s");
            $result_status = 'Not found';
        }
        $id_status = TrackingModel::searchIdStatus(26, $status);
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
