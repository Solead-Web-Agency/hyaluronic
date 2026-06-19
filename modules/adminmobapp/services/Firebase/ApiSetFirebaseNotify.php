<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiSetFirebaseNotify extends Core
{
    public function getData()
    {
        $this->callNotify();
        $token = 'call';
        dump($token);
        exit();
    }

    public function createJWT($serviceAccountFile) {
        $key = json_decode(file_get_contents($serviceAccountFile), true);
        $now = time();
        $exp = $now + 3600; // Token valid for 1 hour
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];
        $payload = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
        ];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = '';
        openssl_sign($base64UrlHeader . '.' . $base64UrlPayload, $signature, $key['private_key'], 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
        return $jwt;
    }

    public function callNotify()
    {
        $title = 'New Customer Registered';
        $content = 'A new customer has registered on your store.';

        $url = 'https://fcm.googleapis.com/v1/projects/prestaadminapp/messages:send';

        $message = array(
            'token' => 'cCaZg7K_QU2P1GAgCIY934:APA91bEsGUN7sxNR5Y3zdFov20XiaMjEEjbQOD8JbuDhkHTvuCrMRRyDJkI2P3Bpjw3gWpGmnDijPxhzlcbt5_snnKS1bk0Qy5HCb9UUMPiTcR6kC8ComEseLB7jPbyjb-9eA8xLBHK-',
            'notification' => array(
                'title' => $title,
                'body' => $content,
            ),
            'data' => array(
                'type' => 'new_customer',
                'title' => $title,
                'id_page' => '1',
                'content_available' => "true",
                'description' => $content,
                'body' => $content,
            ),
        );

        $params = array(
            'message' => $message,
        );
        $result = $this->curlRequest($url, $params);
        return $result;
    }

    public function curlRequest($url, $params)
    {
        $serviceAccountFile = dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/prestaadminapp-firebase-adminsdk-tdwsa-93d23af145.json';
        $accessToken = $this->getAccessToken($serviceAccountFile);

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        );
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_POSTFIELDS, json_encode($params));

        $result = curl_exec($connection);
        if ($result === false) {
            die('Curl failed: ' . curl_error($connection));
        }
        curl_close($connection);
        return $result;
    }

    public function getAccessToken($serviceAccountFile) {
        $jwt = $this->createJWT($serviceAccountFile);

        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Request Error: ' . curl_error($ch));
        }
        curl_close($ch);

        $responseDecoded = json_decode($response, true);
        if (isset($responseDecoded['access_token'])) {
            return $responseDecoded['access_token'];
        } else {
            throw new Exception('Error fetching access token: ' . $response);
        }
    }
}
