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

class Carrier77 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("fr","es","nl","pt","pl","de","it") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order_carrier.''
        );
        $username = Configuration::get('HL_CARRIER77_id1');
        $password = Configuration::get('HL_CARRIER77_id2');
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://extservices.matkahuolto.fi/mpaketti/public/tracking?ids='.$this->tracking_number,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '. base64_encode($username.':'.$password),
              'Accept-Language: '.$lang
          ),
        ));

        $response = curl_exec($curl);
        $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $events = array();
        $id_status = 0;
        if ($this->httpcode !== 200) {
            $status_error = false;
            $status = $this->httpcode;
            $date = date('Y-m-d H:i:s');
            $result_status = '';
        } else {
            $response = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
            $xml = simplexml_load_string($response);
            $status_error = false;
            $labels = json_decode(Tools::file_get_contents(dirname(__FILE__).'/labelcode77.txt'), true);
            if (!isset($xml->Event)) {
                $status_error = false;
                $status = '';
                $date = date('Y-m-d H:i:s');
                $result_status = '';
            } else {
                $traces = $xml->xpath('*');
                $status_error = true;
                if (count($traces)) {
                    $events = array_map(function ($e) use ($labels) {
                        $date = $e->EventTime;
                        $date = date("Y-m-d H:i:s", strtotime($date));
                        $code = (string)$e->EventCode;
                        return array(
                            'event_code' => $code,
                            'event_description' => isset($labels[$code])?$labels[$code]:0,
                            'event_date' => $date,
                            'id_status' => TrackingModel::searchIdStatus(77, $code)
                        );
                    }, $traces);
                    $event = $events[count($events)-1];
                    $status = $event['event_code'];
                    $result_status = $event['event_description'];
                    $date = $event['event_date'];
                } else {
                    $status = '';
                    $date = date('Y-m-d H:i:s');
                    $result_status = '';
                }
            // }
            }
        }

        $id_status = TrackingModel::searchIdStatus(77, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $status_error,
            'status' => $status,
            'desc' => $result_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
