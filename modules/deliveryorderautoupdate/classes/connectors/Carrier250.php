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

class Carrier250 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
        $soapClient = new SoapClient(dirname(__FILE__).'/shipments-tracking-api-wsdl.wsdl');

        $username = Configuration::get('HL_CARRIER250_id1');
        $password = Configuration::get('HL_CARRIER250_id2');
        $version = Configuration::get('HL_CARRIER177_id3');
        $accountnumber = Configuration::get('HL_CARRIER250_id4');
        $accountpin = Configuration::get('HL_CARRIER250_id5');
        $accountentity = Configuration::get('HL_CARRIER250_id6');
        $accountcountrycode = Configuration::get('HL_CARRIER250_id7');

        $params = array(
            'ClientInfo' => array(
                'AccountCountryCode'    => $accountcountrycode,
                'AccountEntity'         => $accountentity,
                'AccountNumber'         => $accountnumber,
                'AccountPin'            => $accountpin,
                'UserName'              => $username,
                'Password'              => $password,
                'Version'               => $version
            ),

            'Transaction' => array(
                        'Reference1'    => $this->tracking_number
                                    ),
            'Shipments' => array(
                            'XXXXXXXXXX'
                        )
        );
        $auth_call = $soapClient->TrackShipments($params);
        return $auth_call;
    }
    public function track()
    {
        $auth_call = $this->getResponse();
        try {
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'success' => true,
                'status' => $auth_call->Notifications->Notification->Code,
                'desc' => $auth_call->Notifications->Notification->Message,
            ));
        } catch (SoapFault $fault) {
            $status = new Status(array(
                'id_order' => $this->id_order_carrier,
                'success' => false,
                'status' => $fault->faultstring,
                'desc' => $fault->faultstring,
            ));
        }

        return $status;
    }
}
