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

class Carrier13 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER13_id1');
        $password = Configuration::get('HL_CARRIER13_id2');

        $data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
         xmlns:trac="http://gls-group.eu/Tracking/">
           <soapenv:Header/>
           <soapenv:Body>
              <trac:TuDetailsRequest>
                 <trac:RefValue>'.$this->tracking_number.'</trac:RefValue>
                 <trac:Credentials>
                    <trac:UserName>'.$username.'</trac:UserName>
                    <trac:Password>'.$password.'</trac:Password>
                 </trac:Credentials>
                 <!--Zero or more repetitions:-->
                 <trac:Parameters>
                    <trac:ParamCode>LangCode</trac:ParamCode>
                    <trac:ParamValue>EN</trac:ParamValue>
                 </trac:Parameters>
              </trac:TuDetailsRequest>
           </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/Tracking",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: text/xml;charset=UTF-8",
            "Cookie: BIGipServerP_Uniportal_Soapapi=!ydwUO2IhYe4U/W/pCFzgT86OtAZzjRcj1".
            "tssFJbk3aO/w1p2Y0pzImXNz/KHUwrIJ9Y9fWpqtj7YNwQ="
          ),
        ));

        $response = curl_exec($curl);
        return $response;
    }
}
