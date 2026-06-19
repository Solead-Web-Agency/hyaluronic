<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminEtsSeoFileEditorController extends ModuleAdminController
{
    public $rb_file;
    public $rb_data;

    public function __construct()
    {
        $this->bootstrap  = true;
        if((int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP') && (int)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE'))
            $this->rb_file = _PS_ROOT_DIR_.'/_robots.txt';
        else
            $this->rb_file = _PS_ROOT_DIR_.'/robots.txt';
        $this->rb_data = Tools::getRobotsContent();
        parent::__construct();
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }
    public function renderOptions()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $robots_description = $this->l('Your robots.txt file MUST be in your website\'s root directory: ').Context::getContext()->shop->getBaseURL(true, true).$this->l('robots.txt (nowhere else)');
        $this->fields_options = array(
            'robot_txt' => array(
                'title' => $this->l('Robots.txt'),
                'description' => $robots_description,
                'fields' => $seoDef->fields_config()['robot_txt'],
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Reset robots.txt file'),
                        'type'=> 'submit',
                        'class' => 'ets-seo-btn-reset-robotstxt btn btn-default',
                        'icon' => 'process-icon-refresh',
                        'name' => 'resetRobotsTxt'
                    )
                )
            ),
        );
        if(!$this->checkConfiguration($this->rb_file))
        {
            $robots_description .="\n". $this->l('Before you can use this tool, you need to:');
            $robots_description .="\n".  $this->l('1) Create a blank robots.txt file in your root directory.');
            $robots_description .="\n".  $this->l('2) Grant it write permissions (CHMOD 666 on Unix system).');
            $robots_description = nl2br($robots_description);
            $this->fields_options['robot_txt']['fields'] = array();
            $this->fields_options['robot_txt']['description'] = $robots_description;
            $this->fields_options['reset_robot_txt']['description'] = '';
            $this->fields_options['reset_robot_txt']['submit'] = array();
            $this->fields_options['robot_txt']['buttons'] = array();
        }
        elseif(!$this->checkFileRobotsExists($this->rb_file))
        {
            $robots_description = nl2br($robots_description);
            $this->fields_options['robot_txt']['buttons'][0]['title'] = $this->l('Generate robots.txt file');
            $this->fields_options['robot_txt']['buttons'][0]['icon'] = 'process-icon-refresh';
            $this->fields_options['robot_txt']['description'] = $robots_description;
            $this->fields_options['robot_txt']['waring'] = $this->l('Robots.txt file is missing. Click on "Generate robots.txt" button to create a default robots.txt file.');
        }
        /**/
        if(!@file_exists($this->rb_file))
        {
            $this->context->smarty->assign(array(
               'ets_seo_robots_warning' => $this->l('Robots.txt file is missing. Click on "Generate robots.txt" button to create a default robots.txt file.')
            ));
        }
        if ($this->fields_options && is_array($this->fields_options)) {
            $helper = new HelperOptions($this);
            $this->setHelperDisplay($helper);
            $helper->toolbar_scroll = true;
            
            $helper->id = $this->id;
            $helper->tpl_vars = array_merge($this->tpl_option_vars, array('ets_seo_robot'=> $this->getRobotContent()));
            $options = $helper->generateOptions($this->fields_options);

            return $options;
        }
    }

    public function postProcess()
    {
        if(Tools::isSubmit('submitOptionsconfiguration'))
        {
            $content = Tools::getValue('ETS_SEO_ROBOT_TXT');
            file_put_contents($this->rb_file, $content);
            $this->confirmations[] = $this->l('The robots.txt file has been updated.');
        }
        elseif(Tools::isSubmit('resetRobotsTxt'))
        {
            if(@file_exists( _PS_ROOT_DIR_.'/_robots.txt'))
            {
                @unlink( _PS_ROOT_DIR_.'/_robots.txt');
            }
            Tools::generateRobotsFile(true);
            if((int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP'))
            {
                $this->module->setSitemap();
            }
            $this->confirmations[] = $this->l('The robots.txt file generated successfully.');
        }

    }

    protected function getRobotContent()
    {
        if(file_exists($this->rb_file)){
            $content = Tools::file_get_contents($this->rb_file);
            return $content;
        }
        return '';
        
    }

    public function checkConfiguration($file)
    {
        if (file_exists($file)) {
            return is_writable($file);
        }
        return is_writable(dirname($file));
    }
    public function checkFileRobotsExists($file)
    {
        return file_exists($file);
    }
}