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

class Carrier128 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $Franquicia = Configuration::get('HL_CARRIER128_id1');
        $Cliente = Configuration::get('HL_CARRIER128_id2');
        $Password = Configuration::get('HL_CARRIER128_id3');

        $data='<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
         xmlns:seg="http://www.mrw.es/webservices/seguimiento">
           <soap:Header/>
           <soap:Body>
              <seg:SeguimientoNumeroEnvioMRWNacional>
                 <!--Optional:-->
                 <seg:Franquicia>'.$Franquicia.'</seg:Franquicia>
                 <!--Optional:-->
                 <seg:Cliente>'.$Cliente.'</seg:Cliente>
                 <!--Optional:-->
                 <seg:Password>'.$Password.'</seg:Password>
                 <!--Optional:-->
                 <seg:NumeroMRW>'.$this->tracking_number.'</seg:NumeroMRW>
                 <!--Optional:-->
                 <seg:Referencia>PEDIDO-'.$this->id_order_carrier.'</seg:Referencia>
                 <seg:Agrupado>0</seg:Agrupado>
              </seg:SeguimientoNumeroEnvioMRWNacional>
           </soap:Body>
        </soap:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://seguimiento.mrw.es/swc/wssgmntnvs.asmx?op=SeguimientoNumeroEnvioMRWNacional",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-type: application/soap+xml; charset=utf-8",
            "Accept: text/xml",
            "Host: seguimiento.mrw.es"
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['soap:', 'xmlns:'], '', $response);
        $response = json_decode(json_encode(simplexml_load_string($response)), true);
        $id_status = '0';
        $response = $response['Body']['SeguimientoNumeroEnvioMRWNacionalResponse']['SeguimientoNumeroEnvioMRWNacionalResult'];
        $events = array();
        if ($response['Estado'] != 'true') {
            $server = 0;
            $label_status = $response['Mensaje'];
            if (strpos($label_status, 'no existe información disponible para el número indicado') !== false) {
                $status = '105';
            } elseif (strpos($label_status, 'Error de acceso al seguimiento de envíos') !== false) {
                $status = '102';
            } else {
                $status = '100';
            }
            $date = date('Y-m-d H:i:s');
            $id_status = TrackingModel::searchIdStatus(128, $status);
        } else {
            $server = 1;
            $track = $response['Envio'];
            $label_status = $track['EstadoDescripcion'];
            $status = $track['Estado'];
            $myDateTime = DateTime::createFromFormat(
                'dmY Hi',
                $track['FechaEntrega'].' '.$track['HoraEntrega']
            );
            $date = $myDateTime->format('Y-m-d H:i:s');
            $id_status = TrackingModel::searchIdStatus(128, $status);
            $events = array(array(
                'event_code' => $status,
                'event_description' => $label_status,
                'event_date' => $date,
                'id_status' => $id_status
            ));
        }

        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
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
