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

class Carrier132 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $user = Configuration::get('HL_CARRIER132_id1');
        $password = Configuration::get('HL_CARRIER132_id2');
        $pass = Tools::strtoupper(md5($password));
        $agency = Tools::substr($this->tracking_number, 0, 4);
        $tracking_number = str_replace('/', '', $this->tracking_number);
        $tracking_number = Tools::substr($tracking_number, 4, 8);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://pda.nacex.com/nacex_ws/ws?method=getInfoEnvio&data=tipo=E%7Cdel='
          .$agency.'%7Cnum='.$tracking_number.'&user='.$user.'&pass='.$pass.'',
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
        $response = explode('|', $response);
        if (isset(Tools::getValue('devmode')) && Tools::getValue('devmode')) {
            die($response);
        }

        $id_status = '0';
        $events = array();
        if ($response[0] != 'ERROR') {
            $patt = '/^\d{1,2}\/\d{1,2}\/\d{1,4}\s\d{1,2}:\d{1,2}:\d{1,2}\~\d*~\w*~/';
            $trace = array_filter($response, function ($r) use ($patt) {
                return preg_match($patt, $r);
            });
            $events = array_map(function ($e) {
                $rs = explode('~', $e);
                $datenode = $rs[0];
                $date = DateTime::createFromFormat('d/m/Y H:i:s', $datenode);
                $date = $date->format('Y-m-d H:i:s');
                $id_status = TrackingModel::searchIdStatus(132, $rs[1]);
                return array(
                    'event_code' => $rs[1],
                    'event_description' => $rs[2],
                    'event_date' => $date,
                    'id_status' => $id_status,
                );
            }, array_values($trace));
            $rs = $response[count($response)-1];
            $rs = explode('~', $rs);
            $success = true;
            $datenode = $rs[0];
            $status = $rs[1];
            $date = DateTime::createFromFormat('d/m/Y H:i:s', $datenode);
            $date = $date->format('Y-m-d H:i:s');
            $result_status = utf8_encode($rs[2]);
        } else {
            $success = false;
            $status = $response[2];
            $date = date("Y-m-d H:i:s");
            $result_status = utf8_encode($response[1]);
        }

        $id_status = TrackingModel::searchIdStatus(132, $status);
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
