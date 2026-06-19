<?php
require dirname(__FILE__).'/config/config.inc.php';
$t = isset($_GET['t']) ? preg_replace('/[^a-f0-9]/','',substr((string)$_GET['t'],0,32)) : '';
if (strlen($t)===32) {
    $ip = pSQL(substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45));
    $now = date('Y-m-d H:i:s');
    Db::getInstance()->execute(
        "UPDATE "._DB_PREFIX_."hfm_email_track
         SET opened_at = IFNULL(opened_at, '$now'), open_count = open_count + 1, last_ip = '$ip'
         WHERE token = '".pSQL($t)."'");
}
// GIF transparent 1x1
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
