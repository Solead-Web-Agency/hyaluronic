<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
class CGHelper
{
    private $logger;

    private $id_shop_group;
    private $id_shop;

    public function __construct()
    {
        $this->id_shop_group = (int) \Shop::getContextShopGroupID();
        $this->id_shop = \Context::getContext()->shop->id;
    }

    public function log($message, $level = FileLogger::DEBUG)
    {
        if (!$this->logger) {
            if (defined('CARTSGURU_DEBUG_MODE') && CARTSGURU_DEBUG_MODE === 1) {
                $constructLevel = FileLogger::DEBUG;
            } else {
                $constructLevel = FileLogger::ERROR;
            }

            $this->logger = new FileLogger($constructLevel);

            switch (true) {
                case version_compare(_PS_VERSION_, '1.7.4', '>='):
                    $loggerFolder = '/var/logs';
                    break;
                case version_compare(_PS_VERSION_, '1.7.0', '>=') && version_compare(_PS_VERSION_, '1.7.4', '<'):
                    $loggerFolder = '/app/logs';
                    break;
                case version_compare(_PS_VERSION_, '1.7.0', '<'):
                    $loggerFolder = '/log';
                    break;
                default:
                    $loggerFolder = null;
            }

            $this->logger->setFilename(_PS_ROOT_DIR_ . $loggerFolder . '/cartsguru_' . date('Ymd') . '.log');
        }

        try {
            $this->logger->log($message, $level);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    public function postAsync($url, $jsonParams)
    {
        $headers = "Content-Type: application/json\r\n";
        $headers .= 'Content-Length: ' . Tools::strlen($jsonParams) . "\r\n";
        $headers .= "Cache-Control: no-cache\r\n";
        $headers .= "Connection: Close\r\n\r\n";

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => $jsonParams,
                'timeout' => 1,
            ],
        ];

        try {
            if (version_compare(_PS_VERSION_, '1.6.1.21', '>=')) {
                return $this->getPostResultCurl($url, $opts);
            } else {
                return $this->getPostResultWebsocket($url, $opts);
            }
        } catch (LogicException $err) {
            $this->log('Helper/Data - postAsync - url: ' . $err->getMessage(), FileLogger::ERROR);

            return false;
        }
    }

    public function postSync($url, $jsonParams)
    {
        $headers = "Content-Type: application/json\r\n";
        $headers .= 'Content-Length: ' . Tools::strlen($jsonParams) . "\r\n";
        $headers .= "Cache-Control: no-cache\r\n";
        $headers .= "Connection: Close\r\n\r\n";

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => $jsonParams,
                'timeout' => 30,
            ],
        ];

        try {
            if (version_compare(_PS_VERSION_, '1.6.1.21', '>=')) {
                return $this->getPostResultCurl($url, $opts, true);
            } else {
                return $this->getPostResultWebsocket($url, $opts, true);
            }
        } catch (LogicException $err) {
            $this->log('Helper/Data - postSync - url: ' . $err->getMessage(), FileLogger::ERROR);

            return false;
        }
    }

    /**
     * Get Hmac Key.
     *
     * @param $siteId string
     * @param $authKey string
     *
     * @return string
     */
    public function getHmac($siteId, $authKey)
    {
        return $this->hashHmac($siteId, $authKey);
    }

    /**
     * Get Cart Token for recover url.
     *
     * @param $cartId int
     * @param $authKey string
     *
     * @return string
     */
    public function getCartToken($cartId, $authKey)
    {
        $this->log('Get Cart Token');

        return $this->hashHmac($cartId, $authKey);
    }

    public function getUrl()
    {
        $original_url = _PS_BASE_URL_ . $_SERVER['REQUEST_URI'];
        $parsed = parse_url($original_url);
        $query = $parsed['query'];

        parse_str($query, $params);
        unset($params['auth']);

        return _PS_BASE_URL_ . '/index.php?' . http_build_query($params);
    }

    public function getLastOrderId($email, $shopId)
    {
        $query = 'SELECT o.id_order
                    FROM ' . _DB_PREFIX_ . 'orders o
                    LEFT JOIN ' . _DB_PREFIX_ . 'customer c on (c.id_customer = o.id_customer)
                    WHERE c.email = "' . pSQL($email) . '" AND o.id_shop = ' . $shopId . '
                    ORDER BY o.id_order DESC
                    LIMIT 1';

        if ($data = Db::getInstance()->ExecuteS($query)) {
            return $data[0]['id_order'];
        }

        return false;
    }

    public function base64encode($data = '')
    {
        return preg_replace('!\\r?\\n!', '', mb_convert_encoding($data, 'BASE64', 'UTF-8'));
    }

    /**
     * Check in the Raw data is disabled when we return a Cart, Contact or Order.
     * 
     * @return bool
     */
    public function isRawEnabled()
    {
        return Configuration::get('CARTS_GURU_SETTINGS_RAW_ENABLED', false, $this->id_shop_group, $this->id_shop);
    }

    /**
     * Check in the Segmentation data is disabled when we return a Cart or Order.
     * 
     * @return bool
     */
    public function isProductCatalogEnabled()
    {
        return Configuration::get('CARTS_GURU_SETTINGS_PRODUCT_CATALOG_ENABLED', false, $this->id_shop_group, $this->id_shop);
    }

    private function getPostResultCurl($url, $opts, $isSync = false)
    {
        // $url = str_replace("integration.carts.guru", "integration-beta.carts.guru", $url);

        if (!function_exists('curl_init')) {
            throw new \LogicException('cUrl library isn´t loaded.');
        }

        Tools::refreshCACertFile();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, $opts['http']['timeout']);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_CAINFO, _PS_CACHE_CA_CERT_FILE_);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);

        if (!$isSync) {
            curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        }

        if ($opts != null) {
            if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post') {
                curl_setopt($curl, CURLOPT_POST, true);
                if (isset($opts['http']['content'])) {
                    $httpContent = json_decode($opts['http']['content'], true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($httpContent));
                }
            }
        }

        if (!$isSync) {
            try {
                $content = curl_exec($curl);
            } catch (\Exception $e) {
            }
        } else {
            $content = curl_exec($curl);
        }

        if (false === $content && _PS_MODE_DEV_) {
            $errorMessage = sprintf(
                'file_get_contents_curl failed to download %s : (error code %d) %s',
                $url,
                curl_errno($curl),
                curl_error($curl)
            );

            throw new \LogicException($errorMessage);
        }

        curl_close($curl);

        if (!$isSync) {
            return;
        }

        if (false === $content) {
            throw new \LogicException('Url not return data.');
        }

        return json_decode($content);
    }

    private function getPostResultWebsocket($url, $opts, $isSync = false)
    {
        // $url = str_replace("integration.carts.guru", "integration-beta.carts.guru", $url);

        $parts = parse_url($url);

        if ($parts === false) {
            throw new Exception('Unable to parse URL');
        }

        $host = isset($parts['host']) ? $parts['host'] : null;

        if ($parts['scheme'] === 'https') {
            $port = isset($parts['port']) ? $parts['port'] : 443;
        } else {
            $port = isset($parts['port']) ? $parts['port'] : 80;
        }

        $path = isset($parts['path']) ? $parts['path'] : '/';

        $query = isset($parts['query']) ? $parts['query'] : '';

        parse_str($query, $queryParts);

        if ($host === null) {
            throw new Exception('Unknown host');
        }

        $connection = fsockopen((($parts['scheme'] === 'https') ? 'ssl://' : '') . $host, $port, $errno, $errstr, 30);

        if ($connection === false) {
            throw new Exception('Unable to connect to ' . $host);
        }

        // Build request
        $request = 'POST ' . $path;

        if ($queryParts) {
            $request .= '?' . http_build_query($queryParts);
        }

        $request .= ' HTTP/1.1' . "\r\n";
        $request .= 'Host: ' . $host . "\r\n";

        $body = $opts['http']['content'];

        if ($body) {
            $request .= 'Content-Type: application/json' . "\r\n";
            $request .= 'Content-Length: ' . strlen($body) . "\r\n";
        }

        $request .= 'Connection: Close' . "\r\n\r\n";
        $request .= $body;

        $content = false;

        // Send request to server
        if (!$isSync) {
            try {
                fwrite($connection, $request);
            } catch (\Exception $e) {
            }
        } else {
            fwrite($connection, $request);
        }

        if ($isSync) {
            // First read until the end of the response header, look for blank line
            while ($line = fgets($connection)) {
                $line = trim($line);
                if ($line == '') {
                    break;
                }
            }
            // Read the body of the response
            while ($line = fgets($connection)) {
                $content .= $line;
            }
        }

        fclose($connection);

        if (!$isSync) {
            return;
        }

        return json_decode($content);
    }

    private function hashHmac($message, $key)
    {
        $hashFn = function ($message) {
            return hash(CartsGuru::CARTSGURU_HMAC_ALGORITHM, $message, true);
        };

        $hashBlockSizeInBytes = 64;

        if (Tools::strlen($key) > $hashBlockSizeInBytes) {
            $key = $hashFn($key);
        }

        if (Tools::strlen($key) < $hashBlockSizeInBytes) {
            $key = $key . str_repeat(chr(0x00), $hashBlockSizeInBytes - Tools::strlen($key));
        }

        $outerKeyPad = str_repeat(chr(0x5C), $hashBlockSizeInBytes);
        $innerKeyPad = str_repeat(chr(0x36), $hashBlockSizeInBytes);

        return bin2hex($hashFn(($outerKeyPad ^ $key) . $hashFn(($innerKeyPad ^ $key) . $message)));
    }
}
