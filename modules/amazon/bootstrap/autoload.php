<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
spl_autoload_register(function($class_name) {
    // As we focus only on module's folder, not necessary to be an array...
    $foldersPath = array(
        _PS_MODULE_DIR_.'amazon/classes/seller_partner',
        _PS_MODULE_DIR_.'amazon/classes/exceptions',
    );

    foreach ($foldersPath as $folderPath) {
        $recursiveIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursiveIterator as $file) {
            /** @var SplFileObject $file */
            if ($file->isDir()) {
                continue;
            }

            if (Tools::strtolower($file->getBasename('.php')) == Tools::strtolower($class_name)) {
                require_once $file->getRealPath();

                // We found the class, no need to loop anymore
                break 2;
            }
        }
    }
});
