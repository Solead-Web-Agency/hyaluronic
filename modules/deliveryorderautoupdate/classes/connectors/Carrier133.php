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

class Carrier133 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $userid = Configuration::get('HL_CARRIER133_id1');
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://cttexpressows.ctt.pt/CTTEWSPoolHTTPS/EventosWS.svc',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'<soapenv:Envelope
          xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"
          xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
           <soapenv:Header/>
           <soapenv:Body>
              <tem:GetEventosObjectos_V3>
                 <!--Optional:-->
                 <tem:ID>'.$userid.'</tem:ID>
                 <!--Optional:-->
                 <tem:NObjectos>
                    <!--Zero or more repetitions:-->
                    <arr:string>'.$this->tracking_number.'</arr:string>
                 </tem:NObjectos>
              </tem:GetEventosObjectos_V3>
           </soapenv:Body>
        </soapenv:Envelope>',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml;charset=UTF-8',
            'SOAPAction: "http://tempuri.org/IEventosWS/GetEventosObjectos_V3"'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['a:', 's:', 'i:'], '', $response);
        $response = json_decode(json_encode(simplexml_load_string($response)), true);
        $id_status = '0';
        $result_carrier = array();
        $events = array();
        if (isset($response['Body']['Fault'])) {
            $response = $response['Body']['Fault'];
            $server = 0;
            $label_status = $response['faultstring'];
            $status = $response['faultcode'];
            $date = date('Y-m-d H:i:s');
        } elseif (empty($response['Body']['GetEventosObjectos_V3Response']['GetEventosObjectos_V3Result']['_Objectos'])) {
            $server = 0;
            $label_status = '';
            $status = 5611;
            $date = date('Y-m-d H:i:s');
        } else {
            $response = $response['Body']['GetEventosObjectos_V3Response']['GetEventosObjectos_V3Result']['_Objectos'];
            $response = $response['DadosObjectos_V3BE']['_Eventos']['DadosEventos_V3BE'];
            $events = array_map(function ($e) {
                $label_status = empty($e['_DescricaoEvento'])?'':$e['_DescricaoEvento'];
                $status = $e['_CodigoEvento'];
                $myDateTime = DateTime::createFromFormat(
                    'd-m-Y H:i:s',
                    $e['_DataEvento']
                );
                $date = $myDateTime->format('Y-m-d H:i:s');
                return array(
                    'event_code' => $status,
                    'event_description' => $label_status,
                    'event_date' => $date,
                    'id_status' => TrackingModel::searchIdStatus(133, $status)
                );
            }, $response);
            $track = $response[count($response)-1];
            $server = 1;
            $label_status = $track['_DescricaoEvento'];
            $status = $track['_CodigoEvento'];
            $myDateTime = DateTime::createFromFormat(
                'd-m-Y H:i:s',
                $track['_DataEvento']
            );
            $date = $myDateTime->format('Y-m-d H:i:s');
        }
        $id_status = TrackingModel::searchIdStatus(133, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => ($server == 0 ? 'false' : 'true'),
            'status' => $status,
            'desc' => $label_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
