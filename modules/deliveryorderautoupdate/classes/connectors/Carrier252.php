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

class Carrier252 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $apikey = Configuration::get('HL_CARRIER252_id1');
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("fr","es","pl","de","it") THEN l.iso_code ELSE "en" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order.''
        );
        $result_carrier = array();
        $id_shop = Tools::getValue('id_shop');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'api.taroff.ir/order/getstate',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"token":"'.$apikey.'","orderbarcode": "'.$this->tracking_number.'"}',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $json = _PS_MODULE_DIR_.'deliveryorderautoupdate/classes/connectors/carrier252_label_codes.json';
        $labels = json_decode(Tools::file_get_contents($json), true);
        $id_status = 100;
        $events = array();
        if (!$response) {
            $success = false;
            $status = 106;
            $desc = '';
            $date = Date('Y-m-d H:i:s');
            $id_status = 106;
        } else {
            $response = json_Decode($response, true);
            $success = true;
            $status = $response['stateid'];
            $label = false;
            foreach ($labels as $l) {
                if ($l['status'] == $status) {
                    $label = $l;
                    break;
                }
            }
            if ($label) {
                $desc = $label['label'];
                $id_status = TrackingModel::searchIdStatus(252, $status);
            } else {
                $desc = '';
            }
            $date = Date('Y-m-d H:i:s');
            $events = array();
            $events[] = array(
                'event_code' => $status,
                'event_description' => $desc,
                'event_date' => $date,
                'id_status' => $id_status,
            );
        }
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
