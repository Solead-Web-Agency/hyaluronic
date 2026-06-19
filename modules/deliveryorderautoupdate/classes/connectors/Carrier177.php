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

class Carrier177 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER177_id1');
        $password = Configuration::get('HL_CARRIER177_id2');

        $uri = 'https://api.bpost.be/services/trackedmail/item/'.$this->tracking_number.'/trackingInfo';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '. base64_encode($username.':'.$password)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return $result;
    }
    public function track()
    {
        $result_ent = $this->getResponse();
        $id_status = 100;
        if ($result_ent === false) {
            $status_error = 'Numéro incorrect';
            $date = Date('Y-m-d H:i:s');
            $status = 100;
            $desc = '';
        } if (strpos($result_ent, 'Error report')) {
            $content = array();
            $status_error = 'Numéro incorrect';
            preg_match('/HTTP Status ([0-9]*)/', $result_ent, $content);
            $status = isset($content[1])?$content[1]:100;
            $date = Date('Y-m-d H:i:s');
            preg_match('/<u>(JBWEB.*)<\/u>/', $result_ent, $content);
            $desc = isset($content[1])?$content[1]:'';
        } else {
            $xml = new SimpleXMLElement($result_ent);
            $status = (string)$xml->stateInfo[count($xml->stateInfo)-1]->stateCode;
            $date = (string)$xml->stateInfo[count($xml->stateInfo)-1]->time;
            $desc = $status;
        }
        $id_status = TrackingModel::searchIdStatus(177, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => true,
            'status' => $status,
            'desc' => $desc,
            'date' => date(
                "Y-m-d H:i:s",
                strtotime(str_replace("/", "-", $date))
            ),
            'id_status' => $id_status,
        ));
        return $status;
    }
}
