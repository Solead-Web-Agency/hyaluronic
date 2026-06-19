<?php
/**
 * Cron module
 *
 * @author    Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license   commercial license see license.txt
 * @category  Prestashop
 * @category  Module
 * @link      http://www.gnu.org/licenses/lgpl.html logo license
 * @link      http://www.icon-king.com/ - David Vignoni - logo author
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
require _PS_MODULE_DIR_.'cron/vendor/autoload.php';

class Cron extends Samdha\Cron\Module
{
    public function __construct()
    {
        $this->author = 'Samdha';
        $this->tab = 'administration';
        $this->version = '2.3.0';
        $this->module_key = '';
        $this->name = 'cron';

        parent::__construct();

        $this->displayName = $this->l('Crontab for Prestashop');
        $this->description = $this->l('Allows modules to schedule jobs to run automatically at a certain time or date.');
    }
}
