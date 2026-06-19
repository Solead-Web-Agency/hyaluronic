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

class Carrier127 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER127_id1');
        $password = Configuration::get('HL_CARRIER127_id2');

        $data= "<SeguimientoEnviosRequest
        xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
        xsi:noNamespaceSchemaLocation='SeguimientoEnviosRequest.xsd'>
        <Solicitante>xx</Solicitante> <Dato>".$this->tracking_number."</Dato> </SeguimientoEnviosRequest>";
        $uri = 'https://www.correosexpress.com/wpsc/apiRestSeguimientoEnvios/rest/seguimientoEnvios';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "authorization: Basic ".base64_encode($username.':'.$password),// YWdvcmExOnFseFpB",
            "cache-control: no-cache",
            "content-type: application/xml"
          ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


        $response = curl_exec($ch);
        return $response;
    }
    public function track()
    {
        $result = $this->getResponse();
        $aa = strpos($result, "close")+9;
        $string = Tools::substr($result, $aa);
        libxml_use_internal_errors(true);
        $aa = mb_convert_encoding($string, 'utf-8', 'ISO-8859-15');
        $url_page = simplexml_load_string($aa, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $id_status = 0;
        $events = array();
        if (!$url_page) {
            $server = 0;
            preg_match('/Error ([0-9]*)/', $aa, $content);
            $status = isset($content[1])?$content[1]:100;
            preg_match('/<h2>(.*)<\/h2>/', $aa, $content);
            $label_status = isset($content[1])?$content[1]:'';
            $date = Date('Y-m-d H:i:s');
        } elseif ((string)$url_page->Error) {
            $server = 0;
            $status = (string)$url_page->Error;
            $label_status = (string)$url_page->MensajeError;
            $date = Date('Y-m-d H:i:s');
        } else {
            $server = 1;
            $trace = $url_page->xpath('//EstadoEnvios');
            $events = array_map(function ($e) {
                $myDateTime = DateTime::createFromFormat(
                    'dmY His',
                    (string)$e->FechaEstado.' '.(string)$e->HoraEstado
                );
                $date = $myDateTime->format('Y-m-d H:i:s');
                $id_status = TrackingModel::searchIdStatus(127, (string)$e->CodEstado, true);
                return array(
                    'event_code' => (string)$e->CodEstado,
                    'event_description' => (string)$e->DescEstado,
                    'event_date' => $date,
                    'id_status' => $id_status
                );
            }, $trace);
            $label_status = (string)$url_page->DescEstado;
            $status = (string)$url_page->CodEstado;
            $myDateTime = DateTime::createFromFormat(
                'dmY His',
                (string)$url_page->FechaEstado.' '.(string)$url_page->HoraEstado
            );
            $date = $myDateTime->format('Y-m-d H:i:s');
        }

        $id_status = TrackingModel::searchIdStatus(127, $status, true);
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
