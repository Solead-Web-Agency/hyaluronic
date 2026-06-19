<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

$file = Tools::getValue('url');
$file_info  = pathinfo($file);
$file_type = "application/force-download";
$file_name  = $file_info['basename'];

header('Content-type: '.$file_type);
header('Content-Disposition: attachment; filename=' . $file_name . '');
readfile(dirname(__FILE__).'/files/'.$file_name);
exit();