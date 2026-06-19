<?php
 /**
 * NOTICE OF LICENSE 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2023 MigrationPro
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @package   MigrationPro: OpenCart to PrestaShop Migrate tool
 */

class EDClient
{
    // * Request Action const

    const ACTION_CHECK_CONNECT = 'check';
    const ACTION_QUERY = 'query';
    const ACTION_FILE_CONTENT = 'file';

    // * Request vars:

    protected $url;
    protected $token;
    protected $method = 'POST';
    protected $action;
    protected $charset = 'utf8';
    protected $timeout = 5;
    protected $postdata = '';
    protected $debug = false;
    protected $json_encode = false;


    // * Response vars:

    protected $status;
    protected $content = '';
    protected $message;

    // --- Constructor / destructor:

    public function __construct($url, $token)
    {
        $this->url = $url;
        $this->token = $token;
    }

    // --- Query execution methods:

    public function check()
    {
        $this->method = 'GET';
        $this->action = self::ACTION_CHECK_CONNECT;
        $result = $this->doRequest();

        return $result;
    }

    public function query($action = self::ACTION_QUERY)
    {
        $this->method = 'POST';
        $this->action = $action;
        $result = $this->doRequest();

        return $result;
    }

    public function file()
    {
        ddd(__FILE__ . __CLASS__ . __METHOD__);
    }

    // --- Response accessors:

    public function getStatus()
    {
        return $this->status;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getMessage()
    {
        return $this->message;
    }

    // --- Configuration methods:

    public function setCharset($string)
    {
        $this->charset = $string;
    }

    public function setPostData($postdata)
    {
        $this->postdata = $postdata;
    }

    public function setTimeout($time)
    {
        $this->timeout = $time;
    }

    public function debugOn()
    {
        $this->debug = true;
    }

    public function debugOff()
    {
        $this->debug = false;
    }

    public function json_encodeOn()
    {
        $this->json_encode = true;
    }

    public function json_encodeOff()
    {
        $this->json_encode = false;
    }

    // --- Internal helper methods:

    protected function debug($msg, $object)
    {
        if ($this->debug) {
            $context = Context::getContext();
            $module_path = MigrationPro::mpConfigure('migrationpro_module_path', 'get');
            $context->smarty->assign('msg', $msg);
            $context->smarty->assign('object', $object);
            $output = $context->smarty->fetch($module_path . 'views/templates/admin/debug.tpl');
            print_r($output);
        }
    }

    protected function doRequest()
    {
        if ($this->debug) {
            $this->debug('URL', $this->url);
            $this->debug('Token', $this->token);
        }
        $request = null;
        if (!self::isEmpty($this->postdata)) {
            $this->debug('Request Content', $this->postdata);
//            $request = $this->builtRequest();
//            $this->debug('Request', $request);
        }
        $url = $this->url . '?action=' . $this->action . '&token=' . $this->token;
//        $response = Tools::file_get_contents($url, false, $request, $this->timeout);
        $response = $this->fileGetContentsCurl($url);

        $this->debug('Response', $response);

        if ($response === false) {
            if (!in_array(ini_get('allow_url_fopen'), array('On', 'on', '1'))) {
                $this->message = 'PHP Fopen (allow_url_fopen) must be On';
            } elseif (!function_exists('curl_init')) {
                $this->message = 'PHP Curl must be enabled.';
            } elseif (!function_exists('base64_decode')) {
                $this->message = 'PHP base64_decode must be enabled.';
            } elseif (!function_exists('base64_encode')) {
                $this->message = 'PHP base64_encode must be enabled.';
            }

            return false;
        }
        $responseArray = json_decode(base64_decode($response), true);
        // [For PrestaShop Team] - decode used only for minimized data transfer from source cart. Like text and media
        // files
        $this->debug('Response Content', $responseArray);
        $this->status = $responseArray['status'];
        $this->content = $responseArray['content'];
        $this->message = $responseArray['message'];
        if ($this->status === 'error') {
            $this->message = $responseArray['message'];

            return false;
        }
        // * Reset all the variables that should not persist between requests:
        $this->postdata = '';

        return true;
    }

    public function fileGetContentsCurl($url)
    {
        if ($this->json_encode) {
            $this->postdata = json_encode($this->postdata);
        }
        $data = array('json_encode' => $this->json_encode, 'char_set' => base64_encode($this->charset), 'query' => base64_encode($this->postdata));
        // [For PrestaShop Team] - decode used only for minimized data transfer from source cart. Like text and media
        // files
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, str_replace('&amp;', '&', http_build_query($data)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
        // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36');


        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

//    protected function builtRequest()
//    {
//        if ($this->serialize) {
//            $this->postdata = serialize($this->postdata);
//        }
//
//        $postdata = http_build_query(
//            array(
//                'serialize' => $this->serialize,
//                'char_set' => base64_encode($this->charset),
//                'query' => base64_encode($this->postdata)
//            )
//        );
           // [For PrestaShop Team] - decode used only for minimized data transfer from source cart. Like text and media
           // files
//        $array = array(
//            'http' => array(
//                'method'  => 'POST',
//                'header'=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n",
//                'content' => $postdata
//                // [For PrestaShop Team] - encode used only for minimized data to transfer source cart.
//                // Like long sql queries to get information from source code.
//            ));
//
//        return @stream_context_create($array);
//    }

    public static function isEmpty($field)
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            return ($field === '' || $field === null || $field === array() || $field === 0 || $field === '0');
        } else {
            return empty($field);
        }
    }
}
