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

class Carrier215 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $data = '{"tipoRichiedente":"WEB",
                    "codiceSpedizione":"'.$this->tracking_number.'",
                    "periodoRicerca":1
                }';
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.poste.it/online/dovequando/DQ-REST/ricercasemplice",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
          ),
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
        $events = array();
        if (!isset($response['listaMovimenti']) || !count($response['listaMovimenti'])) {
            $success = false;
            if (isset($response['esitoRicerca'])) {
                $status =  $response['esitoRicerca'];
                $desc = $status =  $response['stato'];
            } else {
                $status =  $response['tipo'];
                $desc = $response['descrizione'];
            }
            $date = Date('Y-m-d H:i:s');
        } else {
            $success = true;
            $events = array_map(function ($e) {
                $date = $e['dataOra']/1000;
                $date = date("Y-m-d H:i:s", $date);
                return array(
                    'event_code' => $e['statoLavorazione'],
                    'event_description' => $e['luogo'],
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(215, trim($e['statoLavorazione'])),
                );
            }, $response['listaMovimenti']);
            $shipment = $response['listaMovimenti'][count($response['listaMovimenti'])-1];
            $status = $shipment['statoLavorazione'];
            $desc = $shipment['luogo'];
            $date = $shipment['dataOra']/1000;
            $date = date("Y-m-d H:i:s", $date);
        }
        $id_status = TrackingModel::searchIdStatus(215, $status);
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
