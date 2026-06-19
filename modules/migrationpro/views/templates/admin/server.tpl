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

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

define('MP_TOKEN', "[[[[[[sample_token]]]]]]]");




error_reporting(1);

if (!isset($_SERVER)) {
    $_GET = &$HTTP_GET_VARS;
    $_POST = &$HTTP_POST_VARS;
    $_ENV = &$HTTP_ENV_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

define('MPROOT_BASE_NAME', basename(getcwd()));
define('MPCONNECTOR_BASE_DIR', dirname(__FILE__));
define('MPSTORE_BASE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

class MPServer {

    var $action = null;
    var $adapter = null;
    var $response = null;

    function __construct() {
        $this->action = $this->_getAction();
        $this->adapter = $this->_getAdapter();
        $this->response = $this->_getResponse();
    }

    function run() {
        if(empty($_GET)){
            echo "<h3>MigrationPro: Bridge connector module ready to work!</h3>";
            return ;
        }
        if (!$this->_checkToken()) {
            $this->response->error('The bridge connector module generated for [[[[[[old_domain]]]]]]]! Please, download again bridge connector module and install to the Source shop (old shop) and try connection again.', null);
            return;  
        }

        $this->action->setConnector($this);
        $this->action->run();
    }

    function _getAdapter() {
        $adapter = new MPServerAdapter();
        return $adapter;
    }

    function _getResponse() {
        $response = new MPServerResponse();
        return $response;
    }

    function _getAction() {
        $action = new MPServerAction();
        return $action;
    }

    function _checkToken() {
        if (isset($_GET['token']) && $_GET['token'] == MP_TOKEN) {
            return true;
        } else {
            return false;
        }
    }

}

class MPServerAction {

    var $type = null;
    var $connector = null;

    function __construct() {

    }

    function setConnector($connector) {
        $this->connector = $connector;
    }

    function _getActionType($action_type) {
        $action = null;
        $action_type = strtolower($action_type);
        $class_name = __CLASS__ . ucfirst($action_type);
        if (class_exists($class_name)) {
            $action = new $class_name();
        }
        return $action;
    }

    function run() {
        if (isset($_GET['action']) && $action = $this->_getActionType($_GET['action'])) {
            $action->setConnector($this->connector);
            $action->run();
        } else {
            $response = $this->connector->response;
            $response->createResponse('error', 'Action not found !', null);
            return;
        }
    }

    function _getResponse() {
        return $this->connector->response;
    }

    function _getAdapter() {
        return $this->connector->adapter;
    }

    function _getCart() {
        $adapter = $this->_getAdapter();
        $cart = $adapter->getCart();
        return $cart;
    }

}

class MPServerActionCheck extends MPServerAction {

    function __construct() {
        parent::__construct();
    }

    function run() {
        $adapter = $this->_getAdapter();
        $cart = $this->_getCart();
        $obj['cms'] = $adapter->detectCartType();
        $response = $this->_getResponse();
        if ($cart) {
            $obj['image_category'] = $cart->imageDirCategory;
            $obj['image_carrier'] = $cart->imageDirCarrier;
            $obj['image_product'] = $cart->imageDirProduct;
            $obj['image_manufacturer'] = $cart->imageDirManufacturer;
            $obj['image_supplier'] = $cart->imageDirSupplier;
            $obj['image_employee'] = $cart->imageDirEmployee;
            $obj['table_prefix'] = $cart->tablePrefix;
            $obj['version'] = $cart->version;
            $obj['charset'] = $cart->char_set;
            $obj['blowfish_key'] = $cart->blowfish_key;
            $obj['cookie_key'] = $cart->cookie_key;
            $connect = $cart->connect();
            if ($connect && $char_set = $this->_checkDatabaseExist($connect)) {
                if($obj['charset'] == ''){
                    $obj['charset'] = $char_set;
                }
                $obj['connect'] = array(
                    'result' => 'success',
                    'msg' => 'Successful connect to database !'
                );
            } else {
                $obj['connect'] = array(
                    'result' => 'error',
                    'msg' => 'Not connected to database!'
                );
            }
        }
//        $response = $this->_getResponse();
//        $response->success('Successful check CMS !', array($cart));
//        return;
        $response->success('Successful check CMS !', $obj);
        return;
    }

    function _checkDatabaseExist($connect){
        $query = "SHOW VARIABLES LIKE \"ch%\"";
        $rows = array();
        $char = null;

        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            if(function_exists('mysqli_connect')){
                $result = @mysqli_query($connect, $query);
                while ($row = @mysqli_fetch_array($result)) {
                    $rows[] = $row;
                }
            }else{
                $rows = $connect->query($query)->fetch();
            }
        } else {
            if(function_exists('mysql_connect')){
                $result = @mysql_query($query,$connect);
            while ($row = @mysql_fetch_array($result)) {
                $rows[] = $row;
            }
            }elseif (function_exists('mysqli_connect')){
                $result = @mysqli_query($connect, $query);
            while ($row = @mysqli_fetch_array($result)) {
                $rows[] = $row;
            }
            }else{
                $rows = $connect->query($query)->fetch();
            }
        }
        foreach($rows as $row){
            if($row['Variable_name'] == 'character_set_database'){
                $char_set = $row['Value'];
            }
            if(strpos($row['Value'], 'utf8') !== false){
                $char = 'utf8';
                break ;
            }
        }
        if(!$char){ $char = $char_set;}
        return $char;
    }

}

class MPServerActionFile extends MPServerAction {

    function __construct() {
        parent::__construct();
    }

    function run() {
        $obj = array();
        $response = $this->_getResponse();

        $attachmentFileName = base64_decode($_REQUEST['query']);
        if(is_string($attachmentFileName)){
            $path = MPSTORE_BASE_DIR.$attachmentFileName;
            if(file_exists($path)){
                $content = @file_get_contents($path);
                $obj[] = $content;
            }
        }
        $response->success(null, $obj);
        return ;
    }

}

class MPServerActionQuery extends MPServerAction {

    function __construct() {
        parent::__construct();
    }

    function run() {
        $obj = array();
        $response = $this->_getResponse();
        $cart = $this->_getCart();
        if ($cart) {
            $connect = $cart->connect();
            if ($connect && isset($_REQUEST['query'])) {
                if(isset($_REQUEST['char_set'])){
                    $char_set = base64_decode($_REQUEST['char_set']);
                    if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                        if(function_exists('mysqli_connect')){
                            @mysqli_query($connect, "SET NAMES " . @mysqli_real_escape_string($connect, $char_set));
                            @mysqli_query($connect, "SET CHARACTER SET " . @mysqli_real_escape_string($connect, $char_set));
                            @mysqli_query($connect, "SET CHARACTER_SET_CONNECTION=" . @mysqli_real_escape_string($connect, $char_set));
                        }else{
                            $connect->exec("SET NAMES " .$char_set);
                            $connect->exec("SET CHARACTER SET " . $char_set);
                            $connect->exec("SET CHARACTER_SET_CONNECTION=" . $char_set);
                        }

                    } else {
                        if(function_exists('mysql_connect')){
                            @mysql_query("SET NAMES " . @mysql_real_escape_string($char_set), $connect);
                            @mysql_query("SET CHARACTER SET " . @mysql_real_escape_string($char_set), $connect);
                            @mysql_query("SET CHARACTER_SET_CONNECTION=" . @mysql_real_escape_string($char_set), $connect);

                        }elseif (function_exists('mysqli_connect')){
                            @mysqli_query($connect, "SET NAMES " . @mysqli_real_escape_string($connect, $char_set));
                            @mysqli_query($connect, "SET CHARACTER SET " . @mysqli_real_escape_string($connect, $char_set));
                            @mysqli_query($connect, "SET CHARACTER_SET_CONNECTION=" . @mysqli_real_escape_string($connect, $char_set));

                        }else{
                            $connect->exec("SET NAMES " .$char_set);
                            $connect->exec("SET CHARACTER SET " . $char_set);
                            $connect->exec("SET CHARACTER_SET_CONNECTION=" . $char_set);
                        }

                    }
                }

//                $log_file = fopen(__DIR__ . "/log_file.txt", "a+") or die("Unable to open file!");
//                fwrite($log_file, " ============================= " . "\n\r");

                $query = base64_decode($_REQUEST['query']);

                if(isset($_REQUEST['json_encode']) && $_REQUEST['json_encode']){
                    $query = json_decode($query, true);
                      foreach($query as $key => $string){
//                        fwrite($log_file, $key . ": " . $string . "\n\r");
                        if(isset($query['groupedqueriesconfiguration'])  && $key === 'groupedqueriesconfiguration') continue;
                        if(isset($query['groupedqueriesconfiguration'][$key]))
                            $obj[$key] = $this->_getData($string, $connect , isset($query['groupedqueriesconfiguration'][$key]),
                                isset($query['groupedqueriesconfiguration'][$key]) ? $query['groupedqueriesconfiguration'][$key] : null );
                        else
                            $obj[$key] = $this->_getData($string, $connect, false, null);
                    }

                } else {
//                    fwrite($log_file, $query . "\n\r");
                    $obj = $this->_getData($query, $connect);
                }
//                fclose($log_file);
                $response->success(null, $obj);

                return;
            } else {
                $response->error('Can\'t connect to database or could not run query!', null);
                return;
            }
        } else {
            $response->error('CMS cart not found!', null);
            return;
        }
    }

    function _getData($query, $connect,  $IsGrouped = false, $GroupKey = null){
        $rows = array();
        $oldIdProduct = -1;
        $productGroupedItems = array();
        $IsHaveResult = false;

        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            if(function_exists('mysqli_connect')){
                $res = @mysqli_query($connect, $query);

                while($row = @mysqli_fetch_assoc($res)){
                    if ($IsGrouped)
                    {
                        if($oldIdProduct > 0 && $oldIdProduct  != $row[$GroupKey])
                        {
                            $rows[$oldIdProduct] = $productGroupedItems;

                            $productGroupedItems = array();
                        }
                        $productGroupedItems[] = $row;
                        $oldIdProduct  = $row[$GroupKey];
                    }
                    else
                    {
                        $rows[] = $row;
                    }
                    if(!$IsHaveResult) {
                        $IsHaveResult = true;
                    }

                }

                if ($IsGrouped)
                {
                    if( $IsHaveResult && !isset($rows[$oldIdProduct]))
                    {

                        $rows[$oldIdProduct] = $productGroupedItems;
                        $productGroupedItems = array();

                    }
                }
            }else{
                $q = $connect->query($query);
                while($row = $q->fetch(PDO::FETCH_ASSOC)){

                    if ($IsGrouped)
                    {
                        if($oldIdProduct > 0 && $oldIdProduct  != $row[$GroupKey])
                        {
                            $rows[$oldIdProduct] = $productGroupedItems;

                            $productGroupedItems = array();

                        }
                        $productGroupedItems[] = $row;
                        $oldIdProduct  = $row[$GroupKey];
                    }
                    else
                    {
                        $rows[] = $row;
                    }

                    if(!$IsHaveResult)
                        $IsHaveResult = true;
                }

                if ($IsGrouped)
                {
                    if( $IsHaveResult && !isset($rows[$oldIdProduct]))
                    {
                        $rows[$oldIdProduct] = $productGroupedItems;
                        $productGroupedItems = array();
                    }
                }
            }
        } else {
            if(function_exists('mysql_connect')){
                $res = @mysql_query($query, $connect);
            while($row = @mysql_fetch_array($res, MYSQL_ASSOC)){
                if ($IsGrouped)
                {
                    if($oldIdProduct > 0 && $oldIdProduct  != $row[$GroupKey])
                    {
                        $rows[$oldIdProduct] = $productGroupedItems;
                        $productGroupedItems = array();

                    }

                    $productGroupedItems[] = $row;
                    $oldIdProduct  = $row[$GroupKey];

                }
                else
                {
                    $rows[] = $row;
                }

                if(!$IsHaveResult)
                    $IsHaveResult = true;
            }

            if ($IsGrouped)
            {
                if( $IsHaveResult && !isset($rows[$oldIdProduct]))
                {
                    $rows[$oldIdProduct] = $productGroupedItems;
                    $productGroupedItems = array();

                }
            }
            }elseif (function_exists('mysqli_connect')){
                $res = @mysqli_query($connect, $query);

                while($row = @mysqli_fetch_assoc($res)){
                    if ($IsGrouped)
                    {
                        if($oldIdProduct > 0 && $oldIdProduct  != $row[$GroupKey])
                        {
                            $rows[$oldIdProduct] = $productGroupedItems;

                            $productGroupedItems = array();
                        }
                        $productGroupedItems[] = $row;
                        $oldIdProduct  = $row[$GroupKey];
                    }
                    else
                    {
                        $rows[] = $row;
                    }
                    if(!$IsHaveResult) {
                        $IsHaveResult = true;
                    }

                }
                if ($IsGrouped)
                {
                    if( $IsHaveResult && !isset($rows[$oldIdProduct]))
                    {

                        $rows[$oldIdProduct] = $productGroupedItems;
                        $productGroupedItems = array();

                    }
                }
            }else{

                $q = $connect->query($query);
                while($row = $q->fetch(PDO::FETCH_ASSOC)){

                    if ($IsGrouped)
                    {
                        if($oldIdProduct > 0 && $oldIdProduct  != $row[$GroupKey])
                        {
                            $rows[$oldIdProduct] = $productGroupedItems;

                            $productGroupedItems = array();

                        }

                        $productGroupedItems[] = $row;
                        $oldIdProduct  = $row[$GroupKey];
                    }
                    else
                    {
                        $rows[] = $row;
                    }

                    if(!$IsHaveResult)
                        $IsHaveResult = true;
                }

                if ($IsGrouped)
                {
                    if( $IsHaveResult && !isset($rows[$oldIdProduct]))
                    {
                        $rows[$oldIdProduct] = $productGroupedItems;
                        $productGroupedItems = array();

                    }
                }
            }

        }
        return $rows;
    }
}

class MPServerAdapter {

    var $cart = null;
    var $Host = 'localhost';
    var $Port = '3306';
    var $Username = 'root';
    var $Password = '';
    var $Dbname = '';
    var $tablePrefix = '';
    var $imageDir = '';
    var $imageDirCategory = '';
    var $imageDirCarrier = '';
    var $imageDirProduct = '';
    var $imageDirManufacturer = '';
    var $imageDirSupplier = '';
    var $imageDirEmployee = '';
    var $version = '';
    var $char_set = '';
    var $blowfish_key = '';
    var $cookie_key = '';

    function __construct() {

    }

    function getCart() {
        $cart_type = $this->detectCartType();
        $this->cart = $this->_getCartType($cart_type);
        return $this->cart;
    }

    function _getCartType($cart_type) {
        $cart = null;
        $cart_type = strtolower($cart_type);
        $class_name = __CLASS__ . ucfirst($cart_type);
        if (class_exists($class_name)) {
            $cart = new $class_name();
        }
        return $cart;
    }

    function detectCartType() {

        if (file_exists(MPSTORE_BASE_DIR . 'config/settings.inc.php') || file_exists(MPSTORE_BASE_DIR . 'app/config/parameters.php')) {
            return 'prestashop';
        }

        return 'Not detect cart !';
    }

    function setHostPort($source) {
        $source = trim($source);

        if ($source == '') {
            $this->Host = 'localhost';
            return;
        }

        $conf = explode(':', $source);
        if (isset($conf[0]) && isset($conf[1])) {
            $this->Host = $conf[0];
            $this->Port = $conf[1];
        } elseif ($source[0] == '/') {
            $this->Host = 'localhost';
            $this->Port = $source;
        } else {
            $this->Host = $source;
        }
    }

    function connect()
    {
        $triesCount = 10;
        $link = null;

        while (!$link) {
            if (!$triesCount--) {
                break;
            }
            if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                if(function_exists('mysqli_connect')){
                    $link = @mysqli_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);

                    return $link;
                }else{
                    $link = $this->conPDO(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);

                    return $link ;
                }

            } else {
                if(function_exists('mysql_connect')){
                    $link = @mysql_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_);
                if (!$link) {
                    sleep(2);
                }

                if ($link) {
                    @mysql_select_db(_DB_NAME_, $link);
                    return $link;
                     }
                }elseif (function_exists('mysqli_connect')){

                    $link = @mysqli_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);

                    return $link;

                }else{
                    $link = $this->conPDO(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);

                    return $link ;
                }

            }


        }

        return $link;
    }

    protected static function conPDO($host, $user, $password, $dbname, $timeout = 5)
    {
        $dsn = 'mysql:';
        if ($dbname) {
            $dsn .= 'dbname=' . $dbname . ';';
        }
        if (preg_match('/^(.*):([0-9]+)$/', $host, $matches)) {
            $dsn .= 'host=' . $matches[1] . ';port=' . $matches[2];
        } elseif (preg_match('#^.*:(/.*)$#', $host, $matches)) {
            $dsn .= 'unix_socket=' . $matches[1];
        } else {
            $dsn .= 'host=' . $host;
        }
        $dsn .= ';charset=utf8';

        return new PDO($dsn, $user, $password, array(PDO::ATTR_TIMEOUT => $timeout, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
    }

    function getCartVersionFromDb($field, $tableName, $where)
    {
        $_link = null;
        $version = '';

        $_link = $this->connect();
        if (!$_link) {
            return $version;
        }

        $sql = 'SELECT ' . $field . ' AS version FROM ' . $this->tablePrefix . $tableName . ' WHERE ' . $where;

        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $query = mysqli_query($_link, $sql);

            if ($query !== false) {
                $row = mysqli_fetch_assoc($query);

                $version = $row['version'];
            }
        } else {
            $query = mysql_query($sql, $_link);

            if ($query !== false) {
                $row = mysql_fetch_assoc($query);

                $version = $row['version'];
            }
        }

        return $version;
    }

}

class MPServerAdapterPrestashop extends MPServerAdapter {

    function __construct() {
        parent::__construct();
        if (file_exists(MPSTORE_BASE_DIR . '/app/config/parameters.php')) {
            $config = require_once MPSTORE_BASE_DIR . '/app/config/parameters.php';
            $database_host = $config['parameters']['database_host'];
            if (!empty($config['parameters']['database_port'])) {
                $database_host .= ':'. $config['parameters']['database_port'];
            }
            define('_DB_SERVER_', $database_host);
            define('_DB_NAME_', $config['parameters']['database_name']);
            define('_DB_USER_', $config['parameters']['database_user']);
            define('_DB_PASSWD_', $config['parameters']['database_password']);
            define('_DB_PREFIX_',  $config['parameters']['database_prefix']);
            define('_MYSQL_ENGINE_',  $config['parameters']['database_engine']);
            define('_PS_CACHING_SYSTEM_',  $config['parameters']['ps_caching']);
            if (array_key_exists('cookie_key', $config['parameters'])) {
                define('_COOKIE_KEY_', $config['parameters']['cookie_key']);
            }
            define('_PS_VERSION_', '1.7.0.0');
        } else {
            @require_once MPSTORE_BASE_DIR . '/config/settings.inc.php';
        }

        if (defined('_DB_SERVER_')) {
            $this->setHostPort(_DB_SERVER_);
        } else {
            $this->setHostPort(DB_HOSTNAME);
        }

        if (defined('_DB_USER_')) {
            $this->Username = _DB_USER_;
        } else {
            $this->Username = DB_USERNAME;
        }

        $this->Password = _DB_PASSWD_;

        if (defined('_DB_NAME_')) {
            $this->Dbname = _DB_NAME_;
        } else {
            $this->Dbname = DB_DATABASE;
        }
        $this->tablePrefix = _DB_PREFIX_;
        $this->imageDir = '/img/';
        $this->imageDirCategory = $this->imageDir . 'c/';
        $this->imageDirCarrier = $this->imageDir . 's/';
        $this->imageDirProduct = $this->imageDir . 'p/';
        $this->imageDirManufacturer = $this->imageDir . 'm/';
        $this->imageDirSupplier = $this->imageDir . 'su/';
        $this->imageDirEmployee = $this->imageDir . 'e/';
        $this->version = _PS_VERSION_;
        $this->cookie_key = _COOKIE_KEY_;
    }

}

class MPServerResponse {

    function __construct() {

    }

    function createResponse($result, $msg, $obj) {
        $response = array();
        $response['status'] = $result;
        $response['message'] = $msg;
        $response['content'] = $obj;
        echo base64_encode(json_encode($response));
        return;
    }

    function error($msg = null, $obj = null) {
        $this->createResponse('error', $msg, $obj);
    }

    function success($msg = null, $obj = null) {
        $this->createResponse('success', $msg, $obj);
    }

}
$connector = new MPServer();
$connector->run();
