<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 *
 * @version   release/2.3.3
 */

namespace ZendeskClasslib\Extensions\ProcessLogger;

use ZendeskClasslib\Extensions\AbstractModuleExtension;
use ZendeskClasslib\Extensions\ProcessLogger\Classes\ProcessLoggerObjectModel;
use ZendeskClasslib\Extensions\ProcessLogger\Controllers\Admin\AdminProcessLoggerController;
use Configuration;

class ProcessLoggerExtension extends AbstractModuleExtension
{
    public $name = 'process_logger';

    public $extensionAdminControllers = [
        [
            'name' => [
                'en' => 'Logger Zendesk',
                'fr' => 'Logger Zendesk',
            ],
            'class_name' => 'AdminZendeskProcessLogger',
            'parent_class_name' => 'zendesk',
            'visible' => true,
        ],
    ];

    public $objectModels = [
        ProcessLoggerObjectModel::class,
    ];

    const QUIET_MODE = 'ZENDESK_PROCESS_LOGGER_QUIET_MODE';

    const ERASING_DISABLED = 'ZENDESK_EXTLOGS_ERASING_DISABLED';

    const ERASING_DAYSMAX = 'ZENDESK_EXTLOGS_ERASING_DAYSMAX';

    public function install()
    {
        Configuration::updateGlobalValue(self::QUIET_MODE, 0);
        Configuration::updateGlobalValue(self::ERASING_DISABLED, 0);
        Configuration::updateGlobalValue(self::ERASING_DAYSMAX, 5);

        return parent::install();
    }
}
