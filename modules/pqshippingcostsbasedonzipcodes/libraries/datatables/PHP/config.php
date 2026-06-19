<?php if (!defined('DATATABLES')) exit(); // Ensure being used in DataTables env.

require_once(dirname(__FILE__).'/../../../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../../../init.php');
ini_set('max_execution_time', '2880');
$module = Module::getInstanceByName('pqshippingcostsbasedonzipcodes');
#$filename = pathinfo(__FILE__, PATHINFO_FILENAME);

// Enable error reporting for debugging (remove for production)
error_reporting(E_ALL);
ini_set('display_errors', '1');


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Database user / pass
 */

$db_server = _DB_SERVER_;
if (strstr($db_server, ':'))
{
	$db_server_exp = explode(':', $db_server);
	$host = $db_server_exp[0];
	$port = $db_server_exp[1];
}
else
{
	$host = _DB_SERVER_;
	$port = '';
}

$sql_details = array(
	"type" => "Mysql",  // Database type: "Mysql", "Postgres", "Sqlite" or "Sqlserver"
	"user" => _DB_USER_,       // Database user name
	"pass" => _DB_PASSWD_,       // Database password
	"host" => $host,       // Database host
	"port" => $port,       // Database connection port (can be left empty for default)
	"db"   => _DB_NAME_,       // Database name
	"dsn"  => "charset=utf8"        // PHP DSN extra information. Set as `charset=utf8` if you are using MySQL
);


