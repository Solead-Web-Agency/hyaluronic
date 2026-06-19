<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_3_7_0($object)
{
  return	$object->upgradeExport_3_7_0();
}


