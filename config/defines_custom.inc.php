<?php
// Compat PS9 : constantes legacy attendues par d'anciens modules mais supprimées/non définies en PS9.
if (!defined('_PS_MODE_DEV_')) { define('_PS_MODE_DEV_', false); }
if (!defined('_CAN_LOAD_FILES_')) { define('_CAN_LOAD_FILES_', true); }
if (!defined('_PS_PROD_IMG_DIR_')) { define('_PS_PROD_IMG_DIR_', dirname(__DIR__) . '/img/p/'); }
