<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is deprecated controller for CRON job for import. Use import controller instead.
 */
class ElegantalEasyImportCronModuleFrontController extends ModuleFrontController
{

    public function display()
    {
        $url = $this->module->getControllerUrl('import', array('id' => Tools::getValue('id_elegantaleasyimport'), 'secure_key' => Tools::getValue('secure_key')));
        Tools::redirect($url);
    }
}
