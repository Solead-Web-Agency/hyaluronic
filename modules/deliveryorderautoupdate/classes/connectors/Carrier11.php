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

class Carrier11 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $uid = Configuration::get('HL_CARRIER11_id1');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://wsclientes.asmred.com/b2b.asmx?op=GetExpCli',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'<?xml version="1.0" encoding="utf-8"?>
                <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
                  <soap12:Body>
                    <GetExpCli xmlns="http://www.asmred.com/">
                      <codigo>'.$this->tracking_number.'</codigo>
                      <uid>'.$uid.'</uid>
                    </GetExpCli>
                  </soap12:Body>
                </soap12:Envelope>',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: text/xml; charset=UTF-8'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['soap:'], '', $response);
        $xml = json_Decode(json_Encode(simplexml_load_string($response)), true);
        $rs = $xml['Body']['GetExpCliResponse']['GetExpCliResult']['expediciones']['exp']['tracking_list']['tracking'];
        $id_status = 0;
        if (false) {
            $success = false;
            $status = (string)$rs->errorCode;
            $date = date("Y-m-d H:i:s");
            $result_status = (string)$rs->errorMessage;
        } else {
            $success = true;
            $rs = array_filter($rs, function($track) {
              return $track['tipo'] == 'ESTADO';
            });
            $events = array_map(function($track) {
              $date = DateTime::createFromFormat('d/m/Y H:i:s', $track['fecha']);
              return array(
                'event_code' => $track['codigo'],
                'event_description' => $track['evento'],
                'event_date' => $date->format('Y-m-d H:i:s'),
                'id_status' => TrackingModel::searchIdStatus(11, $track['codigo']),
              );
            }, $rs);
            $event = end($events);
            $status = $event['event_code'];
            $result_status = $event['event_description'];
            $date = $event['event_date'];
            $id_status = $event['id_status'];
        }

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
