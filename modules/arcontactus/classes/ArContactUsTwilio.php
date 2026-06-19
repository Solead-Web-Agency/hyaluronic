<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'/ArContactUsTools.php';

class ArContactUsTwilio
{
    protected $apiKey;
    protected $authToken;
    protected $phone;
    
    const URL = 'https://api.twilio.com/2010-04-01/Accounts/{apikey}/Messages.json';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    
    public function __construct($apiKey, $authToken)
    {
        $this->apiKey = $apiKey;
        $this->authToken = $authToken;
    }
    
    public function sendSMS($body, $fromPhone, $toPhone)
    {
        return $this->sendRequest(self::URL, array(
            '{apikey}' => $this->apiKey
        ), array(
            'From' => $fromPhone,
            'To' => $toPhone,
            'Body' => $body
        ), self::METHOD_POST);
    }
    
    protected function sendRequest($url, $urlParams, $data = array(), $method = self::METHOD_GET)
    {
        $url = $this->buildUrl($url, $urlParams);
        $auth = base64_encode("{$this->apiKey}:{$this->authToken}"); // base64_encode is forbidden, but its really need to be base64 encoded to pass twilio auth
        if ($method == self::METHOD_POST) {
            $headers = array(
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Authorization: Basic ' . $auth
            );
            $headers = implode(PHP_EOL, $headers) . PHP_EOL;
        }
        
        $context = stream_context_create(array(
            'http' => array(
                'header' => $headers,
                'method' => $method,
                'content' => $method == self::METHOD_GET? http_build_query($data) : http_build_query($data),
            ),
        ));
        if ($res = Tools::file_get_contents($url, false, $context)) {
            if ($json = ArContactUsTools::jsonDecode($res)) {
                return $json;
            }
        }
        return false;
    }
    
    protected function buildUrl($baseUrl, $params)
    {
        return strtr($baseUrl, $params);
    }
}
