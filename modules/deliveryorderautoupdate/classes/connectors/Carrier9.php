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

class Carrier9 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $username = Configuration::get('HL_CARRIER9_id1');
        $password = Configuration::get('HL_CARRIER9_id2');
        $AccessLicenseNumber = Configuration::get('HL_CARRIER9_id3');

        $data= '{
        "UPSSecurity": {
        "UsernameToken": {
        "Username": "'.$username.'",
        "Password": "'.$password.'"
        },
        "ServiceAccessToken": {
        "AccessLicenseNumber": "'.$AccessLicenseNumber.'"
        }
        },
        "TrackRequest": {
        "Request": {
        "RequestOption": "1",
        "TransactionReference": {
        "CustomerContext": "Your Test Case Summary Description"
        }
        },
        "InquiryNumber": "'.$this->tracking_number.'"
        }
        }';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://onlinetools.ups.com/rest/Track",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Content-Type: text/plain"
            ),
        ));

        $response = json_Decode(curl_exec($curl), true);
        curl_close($curl);
        return $response;
    }
    public function track()
    {
        $response = $this->getResponse();
        if (isset($response['Fault'])) {
            $result = 100;
            $error_code = $response['Fault']['detail']['Errors']['ErrorDetail']['PrimaryErrorCode']['Code'];
            $error_desc = $response['Fault']['detail']['Errors']['ErrorDetail']['PrimaryErrorCode']['Description'];
            $result = TrackingModel::searchIdStatus(9, $error_code);
            $status_error = 'NumÃ©ro incorrect';
            $status = new Status(array(
                'id_order' => $this->id_order,
                'status' => $error_code,
                'desc' => $error_desc,
                'id_status' => $result
            ));
        } else {
            $id_status = 100;
            $array = $response;
            $events = $array['TrackResponse']['Shipment']['Package']['Activity'];
            $events = array_map(function ($e) {
                return array(
                    'event_code' => (string)$e['Status']['Code'],
                    'event_description' => (string)$e['Status']['Description'],
                    'event_date' => DateTime::createFromFormat('Ymd His', $e['Date'].' '.$e['Time'])->format('Y-m-d H:i:s'),
                    'id_status' => TrackingModel::searchIdStatus(9, (string)$e['Status']['Code'])
                );
            }, array_reverse($events));
            $status = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Status']['Code'];
            $carrier_xml = array();
            $id_status = TrackingModel::searchIdStatus(9, $status);
            $date = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Date'].' '
            .$array['TrackResponse']['Shipment']['Package']['Activity'][0]['Time'];
            $date = date("Y-m-d H:i:s", strtotime(date("y-m-d H:i:s", strtotime(str_replace("/", "-", $date)))));

            $result_status = $array['TrackResponse']['Shipment']['Package']['Activity'][0]['Status']['Description'];
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'success' => true,
                'status' => $status,
                'desc' => $result_status,
                'date' => $date,
                'id_status' => $id_status,
                'events' => $events
            ));
        }
        return $status;
    }
}
