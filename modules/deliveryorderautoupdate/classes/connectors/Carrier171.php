<?php
/**
* 2007-2023 Helloshop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

class Carrier170 extends deliveryorderautoupdate\Carrier
{
    public function getResponse()
    {
				$site_id = Configuration::get('HL_CARRIER171_id1');
				$password = Configuration::get('HL_CARRIER171_id2');

				$data='<?xml version="1.0" encoding="UTF-8"?>
				<req:KnownTrackingRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				 xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown.xsd" schemaVersion="1.0">
					<Request>
						<ServiceHeader>
							<MessageTime>2020-04-25T20:43:02.353+02:00</MessageTime>
							<MessageReference>TrackingRequest_Single_AWB__</MessageReference>
							<SiteID>'.$site_id.'</SiteID>
							<Password>'.$password.'</Password>
						</ServiceHeader>
					</Request>
					<LanguageCode>en</LanguageCode>
					<AWBNumber>'.$this->tracking_number.'</AWBNumber>
					<LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>
				</req:KnownTrackingRequest>';

				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => "https://xmlpi-ea.dhl.com/XMLShippingServlet",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => $data,
				  CURLOPT_HTTPHEADER => array(
				    "Content-Type: application/xml"
				  ),
				));

				$response = curl_exec($curl);

				curl_close($curl);
        return $response;
    }
}
