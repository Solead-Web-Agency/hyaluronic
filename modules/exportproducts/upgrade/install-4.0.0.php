<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_0_0($object)
{
  return	$object->upgradeExport_4_0_0();
}