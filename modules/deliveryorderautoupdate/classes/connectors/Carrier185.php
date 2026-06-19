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

class Carrier185 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $user = Configuration::get('HL_CARRIER185_id1');
        $password = Configuration::get('HL_CARRIER185_id2');

        $data='<?xml version="1.0" encoding="UTF-8"?>
        <trackingrequest>
         <user>'.$user.'</user>
         <password>'.$password.'</password>
         <trackingnumbers>
         <trackingnumber>'.$this->tracking_number.'</trackingnumber>
         </trackingnumbers>
        </trackingrequest>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://apps.geopostuk.com/trackingcore/ie/parcels",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: text/xml",
            "Cookie: ak_bmsc=C327E2870BA52C2533CFFDE04E49196C02175C05025400000E02295F93AD9276~pl6SfvDKascXznajHRhsdXyT957pKPI".
            "fDpWVsJAVeF2m+xobmFv/MO6fcsu9C+YvDwlYuQ6fXV00iB+Squwzhlxl5at4c6omuhFX6B/sLEQqMn2Il9hAC+mIniFtv7as9H6HS6Ik4W9Qw59".
            "Ze26+3JMiFwsPstvfiuDqBNEC7f0DN/rtrdQjqfMcWKJe1A4/Flbu4UIFPqi/CIB53ZFrRY7DRKlXXpLd1AiL1Kic8kKsw=; X-Mapping-mhdpb".
            "jif=250FAD21CC968A87638098272F020F60; bm_sv=2D09517B6B485A7268D494D054884431~Gb6jQRcOJgGEOWjLqG9nykW+5ZDwf2yFfzT".
            "E2L2mMpP+IO6nmvcAjS/Nf8K3QvVuulLvFQUghxvph9k2b80aopHYbZJIqw/FgDO2o55GvUGbMS912Lp8N+pm9UruUGbe2T97OPJkzV7bVacfYW9".
            "U14uW/NXsjqBOGukC2mxxPiw="
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $xml = simplexml_load_string($response);
        $array = json_decode(json_encode((array)$xml), true);
        $rs = $array['trackingdetails'];
        $id_status = 100;
        $label_status = '';
        $success = false;
        if (!isset($rs['trackingdetail'])) {
            $success = false;
            $status = $array['error'];
            $date = date("Y-m-d H:i:s");
        } else {
            $rs = $rs['trackingdetail'];
            if (isset($rs['error'])) {
                $success = false;
                $status = $rs['error'];
                $date = date("Y-m-d H:i:s");
                $label_status = 'wrong number';
            } else {
                $result = $rs['trackingevents']['trackingevent'];
                if (count($result)) {
                    $event = reset($result);
                    $status = $event['code'];
                    $date = $event['date'];
                    $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));
                    $label_status = $event['type'];
                    $success = true;
                }
            }
        }

        if ($status) {
            $id_status = TrackingModel::searchIdStatus(184, $status);
        }

        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $label_status,
            'date' => $date,
            'id_status' => $id_status,
        ));
        return $status;
    }
}
