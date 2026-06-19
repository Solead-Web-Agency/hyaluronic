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

class Carrier10 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $tracking_number = Tools::substr($this->tracking_number, -8, 8);
        $enseigne = Configuration::get('HL_CARRIER10_id1');
        $privatekey = Configuration::get('HL_CARRIER10_id2');
        $security = $enseigne.$tracking_number.'FR'.$privatekey;
        $securitymd5 = Tools::strtoupper(md5($security));

        $data= '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
        xmlns:web="http://www.mondialrelay.fr/webservice/">
        <soap:Header/>
        <soap:Body>
        <web:WSI2_TracingColisDetaille>
        <!--Optional:-->
        <web:Enseigne>'.$enseigne.'</web:Enseigne>
        <!--Optional:-->
        <web:Expedition>'.$tracking_number.'</web:Expedition>
        <!--Optional:-->
        <web:Langue>FR</web:Langue>
        <!--Optional:-->
        <web:Security>'.$securitymd5.'</web:Security>
        </web:WSI2_TracingColisDetaille>
        </soap:Body>
        </soap:Envelope>';
        $uri = 'http://api.mondialrelay.com/Web_Services.asmx';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: text/xml",
                "postman-token: e6bc2e27-8abd-6869-b1e3-1a41bff24787"
            ),
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$xml), true);
        $rsl = $array['soapBody']['WSI2_TracingColisDetailleResponse']['WSI2_TracingColisDetailleResult']['STAT'];
        $label_status = '';
        if (strpos(',1,24,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,', ','.$rsl.',') !== false) {
            $status = $array['soapBody']['WSI2_TracingColisDetailleResponse']['WSI2_TracingColisDetailleResult']['STAT'];
            $date = date("Y-m-d H:i:s");
            $label_status = 'wrong number';
            $file_label = dirname(__FILE__).'/labelcode_Mondial.txt';
            $labels = explode(PHP_EOL, Tools::file_get_contents($file_label));
            foreach ($labels as $label) {
                if (strpos($label, "{$status}_") !== false) {
                    $label_status = str_replace("{$status}_", '', $label);
                    break;
                }
            }
            $id_status = TrackingModel::searchIdStatus(10, $status);
            $events = array(
                array(
                    'event_code' => $status,
                    'event_description' => $label_status,
                    'event_date' => $date,
                    'id_status' => $id_status,
                )
            );
        } else {
            $result = $array['soapBody']['WSI2_TracingColisDetailleResponse'];
            $result = $result['WSI2_TracingColisDetailleResult']['Tracing']['ret_WSI2_sub_TracingColisDetaille'];
            $events = array();
            if (is_array($result)) {
                foreach ($result as $r) {
                    if ($r['Libelle'] && $r['Libelle'] != 'AVISAGE PAR EMAIL') {
                        $status = $r['Libelle'];
                        $date = $r['Date'].' '.$r['Heure'];
                        $id_status = TrackingModel::searchIdStatus(10, $status);
                        $date = DateTime::createFromFormat('d/m/y H:i', $date)->format('Y-m-d H:i:s');
                        $events[] = array(
                            'event_code' => $status,
                            'event_description' => $status,
                            'event_date' => $date,
                            'id_status' => $id_status,
                        );
                    }
                }
            } else {
                if ($result['Libelle']) {
                    $status = $result['Libelle'];
                    $date = $result['Date'].' '.$result['Heure'];
                    $date = DateTime::createFromFormat('d/m/y H:i', $_date)->format('Y-m-d H:i:s');
                    $id_status = TrackingModel::searchIdStatus(10, $status);
                    $events[] = array(
                        'event_code' => $status,
                        'event_description' => $status,
                        'event_date' => $date,
                        'id_status' => $id_status,
                    );
                }
            }
        }

        $server = (strpos(',1,24,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,', ','.$status.',') !== false ? 0 : 1);
        $status = new Status(array(
            'id_order' => $this->id_order,
            'success' => $server == 0,
            'status' => $status,
            'desc' => $label_status,
            'date' => $date,
            'id_status' => $id_status,
            'events' => $events
        ));
        return $status;
    }
}
