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

class Carrier72 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://tracking.bring.com/api/tracking.xml?q='.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Cookie: BIGipServerpool_posten_konsernportal_sporing_prod_8010=2259513995.18975.0000"
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = json_decode(json_encode(simplexml_load_string($response)), true);
        $id_status = '100';

        if (isset($response['Consignment'])) {
            $events = $response['Consignment']['PackageSet']['Package']['EventSet']['Event'];
            $event = $events[0];
            $server = 1;
            $label_status = $event['Description'];
            $status = $event['Status'];
            $date = $event['OccuredAtIsoDateTime'];
            $events = array_map(function ($e) {
                return array(
                    'event_code' => $e["Status"],
                    'event_description' => $e["Description"],
                    'event_date' => $e["OccuredAtIsoDateTime"],
                    'id_status' => TrackingModel::searchIdStatus(72, $e["Status"])
                );
            }, array_reverse($events));
        } else {
            $event = $response['Status'];
            $server = 0;
            $label_status = $event['Error'];
            $status = $event['Code'];
            $date = date('Y-m-d H:i:s');
        }

        $id_status = TrackingModel::searchIdStatus(72, $status);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => !($server == 0),
            'status' => $status,
            'desc' => $label_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
