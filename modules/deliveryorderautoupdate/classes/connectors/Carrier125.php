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

class Carrier125 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $lang = Db::getInstance()->getValue(
            'SELECT CASE WHEN l.iso_code IN("es") THEN "1" ELSE "2" END
            FROM '._DB_PREFIX_.'order_carrier oc
            LEFT JOIN '._DB_PREFIX_.'orders o ON oc.id_order=o.id_order
            LEFT JOIN '._DB_PREFIX_.'lang l ON o.id_lang=l.id_lang
            WHERE oc.id_order_carrier='.(int)$this->id_order_carrier.''
        );

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xmlns:xsd="http://www.w3.org/2001/XMLSchema"
         xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
         <soap:Body>
         <ConsultaLocalizacionEnviosFases xmlns="ServiciosWebLocalizacionMI/">
         <XMLin><![CDATA[<?xml version="1.0" encoding="utf-8" ?>
                    <ConsultaXMLin Idioma="'.$lang.'"
                    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <Consulta><Codigo>'.$this->tracking_number.'</Codigo></Consulta></ConsultaXMLin>]]></XMLin>
         </ConsultaLocalizacionEnviosFases>
         </soap:Body>
        </soap:Envelope>
        ';
        $wsdl_url = 'https://online.correos.es/servicioswebLocalizacionMI/localizacionMI.asmx?wsdl';
        $action_url = 'https://online.correos.es/servicioswebLocalizacionMI/localizacionMI.asmx';

        $client = new SoapClient(null, array(
            'location' => $wsdl_url,
            'uri'      => '',
            'trace'    => 1,
        ));
        $response = $client->__doRequest($xml, $wsdl_url, $action_url, 1);
        return $response;
    }
    public function track()
    {
        try {
            $response = $this->getResponse();
            $content = array();
            $order_return = str_ireplace(['soap:', 'xmlns:'], '', $response);
            $xml = @simplexml_load_string($order_return);
            if (isset($xml->Body)) {
                $content = $xml->Body->ConsultaLocalizacionEnviosFasesResponse->ConsultaLocalizacionEnviosFasesResult;
            } else {
                throw new Exception("Error Processing Request");
            }
            if (Tools::getIsset('devmode') && Tools::getValue('devmode')) {
                header("Content-Type: text/xml");
                die($content);
            }
            $xml = simplexml_load_string($content);
            $arr = json_decode(json_encode($xml), true);
            $events = array();
            if (!is_array($arr)) {
                $success = false;
                $status = 0;
                $status_event = '';
                $date = date('Y-m-d H:i:s');
            } else {
                $success = true;
                $array = $arr['Respuestas']['DatosIdiomas']['DatosEnvios']['Datos'];
                $events = array_map(function ($e) {
                    $date = DateTime::createFromFormat('d/m/Y H:i', $e['Fecha'].' 00:00');
                    return array(
                        'event_code' => (string)$e["Estado"],
                        'event_description' => (string)$e["Estado"],
                        'event_date' => $date->format('Y-m-d H:i:s'),
                        'id_status' => TrackingModel::searchIdStatus(125, (string)$e["Estado"])
                    );
                }, $array);
                if (count($events)) {
                    $event = $events[count($events)-1];
                    $date = $event['event_date'];
                    $status_event = $event['event_description'];
                    $status = $event['event_code'];
                } else {
                    $status = 0;
                    $status_event = '';
                    $date = date('Y-m-d H:i:s');
                }
            }
        } catch (SoapFault $exception) {
            $success = false;
            $status = 0;
            $status_event = '';
            $date = date('Y-m-d H:i:s');
        }
        $result_ = TrackingModel::searchIdStatus(125, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $status_event,
            'date' => $date,
            'id_status' => $result_,
            'events' => $events
        ));
        return $status;
    }
}
