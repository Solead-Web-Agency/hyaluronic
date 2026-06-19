<?php
/**
 * Mise a jour 1.0.0 -> 1.1.0 : ajout de la colonne doc_type (type de document).
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $table = _DB_PREFIX_ . 'customer_pdf';

    // Ajoute la colonne uniquement si elle n'existe pas deja
    $exists = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . $table . '` LIKE "doc_type"');
    if (!$exists) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . $table . '` ADD `doc_type` VARCHAR(32) NOT NULL DEFAULT "autre" AFTER `original_name`'
        );
    }

    return true;
}
