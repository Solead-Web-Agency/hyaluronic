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

class Carrier100 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $apikey = Configuration::get('HL_CARRIER100_id1');
        $password = Configuration::get('HL_CARRIER100_id2');
        $accountnumber = Configuration::get('HL_CARRIER100_id3');
        $meternumber = Configuration::get('HL_CARRIER100_id4');

        $data= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:v16="http://fedex.com/ws/track/v16">
         <soapenv:Header/>
         <soapenv:Body>
         <v16:TrackRequest>
         <v16:WebAuthenticationDetail>
         <v16:ParentCredential>
         <v16:Key>'.$apikey.'</v16:Key>
         <v16:Password>'.$password.'</v16:Password>
         </v16:ParentCredential>
         <v16:UserCredential>
         <v16:Key>'.$apikey.'</v16:Key>
         <v16:Password>'.$password.'</v16:Password>
         </v16:UserCredential>
         </v16:WebAuthenticationDetail>
         <v16:ClientDetail>
         <v16:AccountNumber>'.$accountnumber.'</v16:AccountNumber>
         <v16:MeterNumber>'.$meternumber.'</v16:MeterNumber>
         </v16:ClientDetail>
         <v16:TransactionDetail>
         <v16:CustomerTransactionId>Track By Number_v16</v16:CustomerTransactionId>
         <v16:Localization>
         <v16:LanguageCode>EN</v16:LanguageCode>
         <v16:LocaleCode>US</v16:LocaleCode>
         </v16:Localization>
         </v16:TransactionDetail>
         <v16:Version>
         <v16:ServiceId>trck</v16:ServiceId>
         <v16:Major>16</v16:Major>
         <v16:Intermediate>0</v16:Intermediate>
         <v16:Minor>0</v16:Minor>
         </v16:Version>
         <v16:SelectionDetails>
         <v16:CarrierCode>FDXE</v16:CarrierCode>
         <v16:PackageIdentifier>
         <v16:Type>TRACKING_NUMBER_OR_DOORTAG</v16:Type>
         <v16:Value>'.$this->tracking_number.'</v16:Value>
         </v16:PackageIdentifier>
         <v16:ShipmentAccountNumber/>
         <v16:SecureSpodAccount/>
         <v16:Destination>
         <v16:GeographicCoordinates>rates evertitque aequora</v16:GeographicCoordinates>
         </v16:Destination>
         </v16:SelectionDetails>
         </v16:TrackRequest>
         </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://wsbeta.fedex.com:443/web-services/track',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            'Host: ws.fedex.com',
            'Content-Type: text/xml',
            'Port: 443',
            'Accept: image/gif, image/jpeg, image/pjpeg, text/plain, text/html, */*',
            ': ',
            'Cookie: _abck=D64B431B28E21976E22FB69F5CDB1D81~-1~YAAQPpHdWD+j3UF6AQAAIEgZVAYXvPNFbQykW5/HLqVhEsqDRgurg5ZntVxuU'.
            'ZsK+QubBVIjrjY0hEGGOC+0i7FuJnn5F8WmSwi+a6rGujnEIMbtHcXrtBliEjlaaOgMSy/4LohQR4FGA4H0XbpUKnzCQfob9pwCQFVBbymJsGVh'.
            '5/IHkbYvOgG3Yj1qc47Y7T0IY2+7N7K+OrdAAQmhfgCczkDmBi644Kr28slP8sHaVtXDW8NGlfVD3uPthNs8s2qV8wC6CmMeA4cdoxjBAw0vsUBf'.
            '5pzVqHvfVPJg8Ae2L41pvD9FpcLk7QsEFjA0rmJyemaLnH4aB658xATZj7fKUw==~-1~-1~-1; aemserver=PROD-P-dotcom-c0016058.prod'.
            '.cloud.fedex.com; fdx_cbid=31717805561624893201668290355571; fdx_locale=en_US;'.
            ' isMobile=false; isTablet=false; isWireless=false; siteDC=wtc'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        $response = str_ireplace(['SOAP-ENV:'], '', $response);
        $xml = simplexml_load_string($response);
        $response = json_decode(json_encode($xml), true);
        $id_status = 0;
        if (isset($response['Body']['Fault'])) {
            $track = $response['Body']['Fault']['detail'];
            $success = false;
            $status = $track['code'];
            $desc = $track['desc'];
            $date = date('Y-m-d H:i:s');
        } else {
            $rs = $response['Body']['TrackReply']['Notifications'];
            if (isset($rs['Severity']) == 'FAILURE') {
                $success = false;
                $status = $rs['Code'];
                $desc = $rs['Message'];
                $date = date('Y-m-d H:i:s');
            } else {
                $success = true;
                $status = $rs['Code'];
                $desc = $rs['Description'];
             //   $date = date('Y-m-d H:i:s');
                $date = $rs['CreationTime'];
            }
        }

        $id_status = TrackingModel::searchIdStatus(100, $status);
        $status = new Status(array(
            'id_order' => $this->id_order_carrier,
            'success' => $success,
            'status' => $status,
            'desc' => $desc,
            'date' => $date,
            'id_status' => $id_status,
            'events' => array(
                array(
                    'event_code' => $status,
                    'event_description' => $desc,
                    'event_date' => $date,
                    'id_status' => $id_status,
                )
            )
        ));
        return $status;
    }
}
