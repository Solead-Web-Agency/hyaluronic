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

class Carrier66 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://onlineservices.kuehne-nagel.com/public-tracking/shipments?query=ARKU8406260",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Cookie: XSRF-TOKEN=bcf4f787-4104-4acb-a805-88b44555cf8e; TS013ffa6d=010c87a44118e9abb3fdf98ed23315f50807578626ff".
            "d87ac4dc27c50a3f0205c1f337cb94e9fa670a3e57ade601456f927743dad34584d9043184fca00e6eb32886715623"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
