<?php
/**
 * Cron module
 * Cron is a time-based job scheduler in Unix-like computer operating systems.
 * This module automaticaly executes jobs like Cron
 *
 * Add a cron job
 * Module::getInstanceByName('cron')->addCron($this->id, 'myMethod', '5 * * * *');
 *
 * last parameter details :
 * .---------------- minute (0 - 59)
 * |  .------------- hour (0 - 23)
 * |  |  .---------- day of month (1 - 31)
 * |  |  |  .------- month (1 - 12)
 * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
 * |  |  |  |  |
 * *  *  *  *  *
 *
 * remarks :
 * It accepts the standard crontab format except steps ('/') :  0-50/5 * * * * isn't valid
 *
 * exemples :
 * - 1 0 * * * : 00:01 of every day of the month, of every day of the week
 * - 15 3 * * 1-5 : every weekday morning at 3:15 am
 * - 0 0 1,15-17 * * : the first, fifteenth, sixteenth and seventeenth of each month at 00:00
 * - 0 0 * * 1 : every Monday at 00:00
 *
 * Delete a job
 * Module::getInstanceByName('cron')->deleteCron($this->id, 'myMethod');
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license commercial license see license.txt
 * @category Prestashop
 * @category Module
 * @author logo Alessandro Rei http://www.kde-look.org/content/show.php/Dark-Glass+reviewed?content=67902
 * @license logo http://www.gnu.org/copyleft/gpl.html GPLv3
 * @version 2.0.0
**/
namespace Samdha\Cron;

class Module extends \Samdha\Module\Module
{
    public $short_name = 'cron';
    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '1.4.0.0', '<')) {
            $this->tab = 'Tools';
        }

        parent::__construct();

        $this->cron = new Cron($this);
        $this->tools = new Tools($this);
    }

    /**
     * install the module
     *
     * create table in BDD
     * hook the module to footer
    **/
    public function install()
    {
        return ($this->samdha_tools->executeSQLFile(self::INSTALL_SQL_FILE)
            && parent::install()
            && $this->registerHook('footer'));
    }

    /**
     * module uninstallation
     */
    public function uninstall()
    {
        return ($this->samdha_tools->executeSQLFile(self::UNINSTALL_SQL_FILE)
                && parent::uninstall());
    }

    /**
    * set default config
    **/
    public function getDefaultConfig()
    {
        return array(
            '_method'   => 'traffic',
            '_test'     => 0,
            '_lasttime' => 0,
            '_lasttest' => 0,
            '_token'    => null,
        );
    }

    /**
     * display admin form
     *
     * @param string $token
     * @return string The from
     */
    public function displayForm($token)
    {
        $tabs = array();
        $tabs[] = array('href' => '#tabParameters', 'display_name' => $this->l('Parameters', 'module'));
        $tabs[] = array('href' => '#tabJobs', 'display_name' => $this->l('Jobs', 'module'));

        $methods = array(
            'traffic' => $this->l('Shop traffic', 'module'),
            'crontab' => $this->l('Server crontab', 'module'),
            'webcron' => $this->l('Webcron service', 'module'),
        );

        $sql = '
            SELECT c.*, m.name
            FROM `'._DB_PREFIX_.'cron` c
            LEFT JOIN `'._DB_PREFIX_.'module` m ON m.`id_module` = c.`id_module`';
        $crons = \Db::getInstance()->executeS($sql);
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'cron_url`';
        $crons_url = \Db::getInstance()->executeS($sql);

        $cron_url = $this->getCronURL();
        $cron_command = $this->getCronCommand();

        $this->smarty->assign(array(
            'tabs'            => $tabs,
            'methods'         => $methods,
            'php_dir'         => $this->tools->getPHPExecutableFromPath(),
            'cron_url'        => $cron_url,
            'cron_command'    => $cron_command,
            'testing'         => $this->cronExists($this->id, 'test'),
            'crons'           => $crons,
            'crons_url'       => $crons_url,
        ));
        // Display Form
        return parent::displayForm($token);
    }

    public function postProcess($token)
    {
        if ($id_cron = \Tools::getValue('delete')) {
            if ($this->cron->deleteByID($id_cron)) {
                \Tools::redirectAdmin(\AdminController::$currentIndex.'&configure='.$this->name.'&conf=1&token='.$token);
            } else {
                $this->errors[] = $this->l('Unable to delete this task.', 'module');
            }
        }

        if ($id_cron_url = \Tools::getValue('delete_url')) {
            if ($this->cron->deleteURLByID($id_cron_url)) {
                \Tools::redirectAdmin(\AdminController::$currentIndex.'&configure='.$this->name.'&conf=1&token='.$token);
            } else {
                $this->errors[] = $this->l('Unable to delete this task.', 'module');
            }
        }

        if (\Tools::isSubmit('submitAddCron')) {
            if ($this->cron->addURL(\Tools::getValue('cron_url'), \Tools::getValue('cron_mhdmd'))) {
                \Tools::redirectAdmin(\AdminController::$currentIndex.'&configure='.$this->name.'&conf=3&token='.$token);
            } else {
                $this->errors[] = $this->l('Unable to add this task.', 'module');
            }
        }

        if (\Tools::isSubmit('saveSettings')) {
            $cron_test = $this->cron->exists($this->id, 'test');
            $setting = \Tools::getValue('setting');
            if ($cron_test != $setting['_test']) {
                if ($setting['_test']) {
                    $this->addTest();
                } else {
                    $this->deleteTest();
                }
            }
        }

        $cron_test = $this->cron->exists($this->id, 'test');
        if ($cron_test) {
            if ($this->config->_lasttest) {
                $text = $this->l('Last test have been successfully executed on', 'module').' ';
                $text .= \Tools::DisplayDate(
                    date('Y-m-d H:i:s', $this->config->_lasttest),
                    $this->context->cookie->id_lang,
                    true
                );
                $this->confirmations[] = $text;
            } else {
                $this->errors[] = $this->l('Test have not been executed yet.', 'module');
            }
        }

        if (!$this->config->_token) {
            $this->config->_token = \Tools::passwdGen();
        }

        return parent::postProcess($token);
    }

    /**
     * add a false picture to run task in background
    **/
    public function hookFooter($params)
    {
        if (\Configuration::get('cron_method') == 'traffic' &&
            (!\Configuration::get('cron_lasttime') ||
             (\Configuration::get('cron_lasttime') + 60 <= time())
            )) {
                return '<img
                    src="'.$this->getCronURL().'&time='.time().'"
                    alt=""
                    width="0"
                    height="0"
                    style="border:none;margin:0; padding:0"
                />';
        }
    }

    /**
     * add a cron job
     *
     * usage Module::getInstanceByName('cron')->addCron($this->id, 'myMethod', '5 * * * *');
     *
     * $mhdmd details :
     * .---------------- minute (0 - 59)
     * |  .------------- hour (0 - 23)
     * |  |  .---------- day of month (1 - 31)
     * |  |  |  .------- month (1 - 12)
     * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
     * |  |  |  |  |
     * *  *  *  *  *
     *
     * @param int $id_module Module ID
     * @param string $method method of the module to call
     * @param string $mhdmd when call this cron
     * @return boolean
    **/
    public function addCron($id_module, $method, $mhdmd = '0 * * * *')
    {
        return $this->cron->add($id_module, $method, $mhdmd);
    }

    /**
     * delete a cron job
     *
     * @param int $id_module Module ID
     * @param string $method method of the module to call
     * @return boolean
    **/
    public function deleteCron($id_module, $method)
    {
        return $this->cron->delete($id_module, $method);
    }

    /**
     * delete a cron job
     *
     * @param int $id_cron cron job ID
     * @return boolean
    **/
    public function deleteCronByID($id_cron)
    {
        return $this->cron->deleteByID($id_cron);
    }

    /**
     * test if a cron job exists
     *
     * @param int $id_module Module ID
     * @param string $method method of the module to call
     * @return boolean
    **/
    public function cronExists($id_module, $method)
    {
        return $this->cron->exists($id_module, $method);
    }

    /**
     * add a cron job
     *
     * usage Module::getInstanceByName('cron')->addURLCron($url', '5 * * * *');
     *
     * $mhdmd details :
     * .---------------- minute (0 - 59)
     * |  .------------- hour (0 - 23)
     * |  |  .---------- day of month (1 - 31)
     * |  |  |  .------- month (1 - 12)
     * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 )
     * |  |  |  |  |
     * *  *  *  *  *
     *
     * @param string $url url to visit
     * @param string $mhdmd when call this cron
     * @return boolean
    **/
    public function addCronURL($url, $mhdmd = '0 * * * *')
    {
        return $this->cron->addURL($url, $mhdmd);
    }

    /**
     * delete a cron job
     *
     * @param int $id_module Module ID
     * @param string $method method of the module to call
     * @return boolean
    **/
    public function deleteCronURL($url)
    {
        return $this->cron->deleteURL($url);
    }

    /**
     * delete a cron job
     *
     * @param int $id_cron cron job ID
     * @return boolean
    **/
    public function deleteURLByID($id_cron_url)
    {
        return $this->cron->deleteURLByID($id_cron_url);
    }

    /**
     * test if a cron job exists
     *
     * @param int $id_module Module ID
     * @param string $method method of the module to call
     * @return boolean
    **/
    public function cronURLExists($url)
    {
        return $this->cron->URLExists($url);
    }

    /**
     * execute cron jobs
     * invalide job will be deleted
     *
     * @return void
    **/
    public function runJobs()
    {
        if ($this->active
            && ($this->config->_lasttime + 60 <= time())) {
            $this->config->_lasttime = time();
            return $this->cron->runJobs();
        }
    }

    /**
     * tests method
     * to show how to add/delete jobs
    **/
    public function addTest()
    {
        $this->config->_lasttest = 0;
        return $this->addCron(
            $this->id,
            'test',
            '* * * * *'
        );
    }

    public function deleteTest()
    {
        $this->config->_lasttest = 0;
        return $this->deleteCron(
            $this->id,
            'test'
        );
    }

    public function test()
    {
        // store the last time the test was executed
        $this->config->_lasttest = time();
        return '['.date('r')."]\tTest Ok".PHP_EOL;
    }

    public function getCronURL()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $result = $this->context->link->getModuleLink(
                $this->name,
                'cron',
                array('token' => $this->config->_token)
            );
        } else {
            $result = $this->samdha_tools->getHttpHost(true).$this->_path.'cron.php?token='.$this->config->_token;
        }
        return $result;
    }

    public function getCronCommand()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $result = '"'._PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'index.php"';
            $result .= ' "fc=module&module='.$this->name.'&controller=cron&token='.$this->config->_token.'"';
        } else {
            $result = '"'._PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'cron.php" ';
            $result .= ' "token='.$this->config->_token.'"';
        }
        return $result;
    }
}
