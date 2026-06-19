<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class steavisgarantisconsentModuleFrontController extends ModuleFrontController 
{
    public $auth = true;
    public $display_column_left = true;

    protected $_module = null;

    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();
    }
    
    public function getModule()
    {
        if (is_NULL($this->_module)) {
            $this->_module = new STEAVISGARANTIS;
        }
        
        return $this->_module;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'authentication.css');
        return true;
    }

    public function init()
    {
        parent::init();
        $this->display_column_left = false;
    }

    public function initContent()
    {
        parent::initContent();
        
        if (Configuration::get('steavisgarantis_rgpd')=="1") {
            $consent = STEAVISGARANTIS::getSagConsent((int)$this->context->customer->id);
            $this->context->smarty->assign(array(
                'steavisgarantis_customer_id' => (int)$this->context->customer->id,
                'consent' => (int)$consent,
            ));
            
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->setTemplate('customSagPage.tpl');
            }
            else {
                $this->setTemplate('module:steavisgarantis/views/templates/front/customSagPage17.tpl');
            }
        }
    }

    public function postProcess()
    {
        if (Tools::getIsset('steavisgarantis_custom')) {
            STEAVISGARANTIS::saveSagCustom($this->context->customer->id);
            Tools::redirect($this->context->link->getModuleLink('steavisgarantis', 'consent'));
        }
    }
}

