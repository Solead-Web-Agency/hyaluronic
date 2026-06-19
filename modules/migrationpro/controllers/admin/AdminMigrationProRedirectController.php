<?php
 /**
 * NOTICE OF LICENSE 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2023 MigrationPro
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @package   MigrationPro: OpenCart to PrestaShop Migrate tool
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class AdminMigrationProRedirectController extends AdminController
{
    public function __construct()
    {
        if (Tools::getValue('controller') == "AdminMigrationProRedirect") {
            Tools::redirectAdmin('index.php?controller=AdminModules&configure=migrationpro&token=' . Tools::getAdminTokenLite('AdminModules'));
        }
        $this->display = 'edit';
        parent::__construct();
        $this->controller_type = 'moduleadmin'; //instead of AdminController’s admin
        $tab = new Tab($this->id); // an instance with your tab is created; if the tab is not attached to the module, the exception will be thrown
        if (!$tab->module) {
            throw new PrestaShopException('Admin tab ' . get_class($this) . ' is not a module tab');
        }
        $this->module = Module::getInstanceByName($tab->module);
        if (!$this->module->id) {
            throw new PrestaShopException("Module {$tab->module} not found");
        }
        $this->tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, Tab::getIdFromClassName('AdminMigrationRedirect'));
    }
}
