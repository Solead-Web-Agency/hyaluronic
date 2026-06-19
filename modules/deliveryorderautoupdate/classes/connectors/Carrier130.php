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

class Carrier130 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $key = Configuration::get('HL_CARRIER130_id1');
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.viadirectanet.pt/WebApiV2/api/ServiceVD/?Key='.$key.'&CodSRV='.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = json_Decode($response, true);

        $id_status = '0';
        if (is_array($response)) {
            $success = true;
            $status = $response['Cod'];
            $datenode = $response['DataEstado'];
            $date = DateTime::createFromFormat('d-m-Y', $datenode);
            $date = $date->format('Y-m-d');
            $result_status = $response['Descricao'];
        } else {
            $success = false;
            $status = $response;
            $date = date("Y-m-d H:i:s");
            $result_status = '';
        }
        $id_status = TrackingModel::searchIdStatus(130, $status);
        $events = array(
            array(
                'event_code' => $status,
                'event_description' => $result_status,
                'event_date' => $date,
                'id_status' => $id_status
            )
        );
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
