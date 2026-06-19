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

class Carrier181 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://status.dpd.lv/external/tracking?lang=en&pknr='.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $response = json_Decode($response);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $trace = end($response);

        if ($trace->error) {
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'status' => 'E206',
            ));
        } else {
            $details = $trace->details;
            $detail = end($details);
            $status = $detail->status;

            $id_status = TrackingModel::searchIdStatus(179, $status);
            $date = $detail->dateTime;
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'success' => true,
                'status' => $status,
                'desc' => $status,
                'date' => $date,
                'id_status' => $id_status,
            ));
        }
        return $status;
    }
}
