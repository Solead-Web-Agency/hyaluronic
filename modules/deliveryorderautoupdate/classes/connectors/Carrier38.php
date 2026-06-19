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

class Carrier38 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $apikey = Configuration::get('HL_CARRIER38_id1');
        $apipassword = Configuration::get('HL_CARRIER38_id2');
        $accountnumber = Configuration::get('HL_CARRIER38_id3');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://digitalapi.auspost.com.au/shipping/v1/track?tracking_ids='.$this->tracking_number.'',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Account-Number: 0000123456",
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: ". base64_encode($apikey.':'.$apipassword)
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
