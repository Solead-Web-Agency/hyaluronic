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

class AdminEtsSeoUrlRedirectController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->table = 'ets_seo_redirect';
        $this->className = 'EtsSeoRedirect';
        $this->bootstrap  = true;
  
        parent::__construct();

        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'setting'=> array(
                'title' => $this->l('Settings'),
                'fields' => $seoDef->fields_config()['url_redirect_setting'],
                'icon'=> '',
                'submit' => array(
                    'title'=> $this->l('Save')
                )
            )
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'=> '',
                'icon' => 'icon-trash'
            )
        );
        $this->fields_value['id_shop'] = $this->context->shop->id;
        $this->fields_list = array(
            'id_ets_seo_redirect' => array(
                'title'=> $this->l('ID'),
                'align' => 'center',
                'filter_type' => 'int',
                'remove_onclick' => true
            ),
            'name' => array(
                'title'=> $this->l('Redirect name'),
                'align' => 'center',
                'remove_onclick' => true
            ),
            'url' => array(
                'title'=> $this->l('Source URL'),
                'align' => 'center',
                'float' => true,
                'remove_onclick' => true
            ),
            'target' => array(
                'title'=> $this->l('Target URL'),
                'align' => 'center',
                'float' => true,
                'remove_onclick' => true
            ),
            'type' => array(
                'title'=> $this->l('Redirect type'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'type',
                'list' => array(
                    '301' => $this->l('301'),
                    '302' => $this->l('302'),
                    '303' => $this->l('303'),
                )
            ),
            'active' => array(
                'title'=> $this->l('Active'),
                'align' => 'center',
                'type' => 'bool',
                'filter_key' => 'active',
                'active' => 'status',
                'remove_onclick' => true
            )
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => Tools::getIsset('updateets_seo_redirect') ? $this->l('Edit').(($id_redirect = (int)Tools::getValue('id_ets_seo_redirect')) ? ' #'.(int)$id_redirect : '') : $this->l('Add new'),
                'icon'=> ''
            ),
            'input' => array(
                array(
                    'type'=> 'hidden',
                    'name' => 'id_shop',
                    'required'=> true,
                    'validate' => 'isString',
                ),
                array(
                    'type'=> 'text',
                    'name' => 'name',
                    'label'=> $this->l('Redirect name (optional)'),
                    'validate'=> 'isString',
                ),
                array(
                    'type'=> 'text',
                    'name' => 'url',
                    'label'=> $this->l('Source URL'),
                    'prefix' => $this->context->shop->getBaseURL(true, true),
                    'required'=> true,
                    'validate'=> 'isString',
                ),
                array(
                    'type'=> 'text',
                    'name' => 'target',
                    'label'=> $this->l('Target URL'),
                    'required'=> true,
                    'validate'=> 'isAbsoluteUrl',
                ),
                array(
                    'type' => 'select',
                    'name' => 'type',
                    'label' => $this->l('Redirect type'),
                    'validate'=> 'isString',
                    'default_value' => '301',
                    'options'=> array(
                        'id' => 'type',
                        'name' => 'name',
                        'query'=> array(
                            array(
                                'name'=> $this->l('301 Moved Permanently (recommended once you have gone live)'),
                                'type' => '301'
                            ),
                            array(
                                'name'=> $this->l('302 Moved Temporarily (recommended while setting up your store)'),
                                'type' => '302'
                            ),
                            array(
                                'name'=> $this->l('303 Do not link to the newly uploaded resources (for advanced user only)'),
                                'type' => '303'
                            ),
                        )
                    )
                ),

                array(
                    'type'=> 'switch',
                    'name' => 'active',
                    'validate'=> 'isBool',
                    'is_bool'=>true,
                    'label'=> $this->l('Active'),
                    'values' => array(
                        array(
                            'id' => 'ets_seo_redirect_active_1',
                            'value'=> 1
                        ),
                        array(
                            'id' => 'ets_seo_redirect_active_0',
                            'value'=> 0
                        ),
                    ),
                    'default_value' => '1',
                ),

            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $this->actions = array('edit', 'delete');
        $this->_where = ' AND `id_shop`='.$this->context->shop->id;

        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }

    public function initPageHeaderToolbar()
    {
       
        if($this->display !== 'add')
        {
            $this->page_header_toolbar_btn['new_redirect'] = array(
                'href' => self::$currentIndex.'&addets_seo_redirect&token='.$this->token,
                'desc' => $this->l('Add new url redirect'),
                'icon' => 'process-icon-new'
            );
        }

        
        parent::initPageHeaderToolbar();
    }

    public function getList($id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false)
    {
        parent::getList($id_lang ,$order_by, $order_way, $start, $limit, $id_lang_shop);
        foreach($this->_list as &$item)
        {
            $item['url'] = $this->context->shop->getBaseURL(true, true).$item['url'];
            $item['url'] = $this->module->getLinkUrlRedirect($item, 'url');
            $item['target'] = $this->module->getLinkUrlRedirect($item);

        }
    }

    public function postProcess()
    {
        if(isset($this->context->cookie->ets_seo_redirect_errors))
        {
            $this->errors = $this->context->cookie->__get('ets_seo_redirect_errors');
            $this->context->cookie->__unset('ets_seo_redirect_errors');
        }
        if(Tools::isSubmit('submitAddets_seo_redirect'))
        {
            $id = (int)Tools::getValue('id_ets_seo_redirect');
            if(isset($this->fields_form['input']) && $this->fields_form['input'])
            {
                foreach($this->fields_form['input'] as $config)
                {
                    if(isset($config['required']) && $config['required'] && !Tools::getValue($config['name']))
                    {
                        $this->errors[] = $this->l('The').' '.$config['name'].' '.$this->l('is required');
                    }
                    if($config['name'] == 'target' && !Validate::isAbsoluteUrl(Tools::getValue($config['name'])))
                    {
                        $this->errors[] = $this->l('The').' '.$config['name'].' '.$this->l('must be an url');
                    }
                }
            }
            if(!$this->errors)
            {
                $url = ($url = Tools::getValue('url')) && Validate::isCleanHtml($url) ? $url : '';
                $url = ltrim($url, ' /');
                $_POST['url'] = $url;
                if($url)
                {
                    $filter_id = $id ? " AND id_ets_seo_redirect != ".(int)$id : '';
                    if(Db::getInstance()->getValue("SELECT `url` FROM `"._DB_PREFIX_."ets_seo_redirect` WHERE url = '".(string)$url."'".(string)$filter_id))
                    {
                        $this->errors[] = $this->l('The url has been taken.');
                    }
                }
                $full_url = $this->context->shop->getBaseURL(true, true).$url;
                $target = ($target = Tools::getValue('target')) && Validate::isCleanHtml($target) ? $target : '';
                if($target && $full_url == trim($target))
                {
                    $this->errors[] = $this->l('The target can not be the same target url');
                }
            }


            if($this->errors)
            {
                if($id)
                {
                    $link = $this->context->link->getAdminLink('AdminEtsSeoUrlRedirect', true).'&updateets_seo_redirect&id_ets_seo_redirect='.$id;
                }
                else{
                    $link = $this->context->link->getAdminLink('AdminEtsSeoUrlRedirect', true).'&addets_seo_redirect';
                }
                $this->context->cookie->__set('ets_seo_redirect_errors', $this->errors[0]);
                $this->context->cookie->__set('ets_seo_redirect_values', Tools::jsonEncode(array(
                    'name' => ($name = Tools::getValue('name')) && Validate::isCleanHtml($name) ? $name : '',
                    'url' => ($url = Tools::getValue('url', '')) && Validate::isCleanHtml($url) ? $url : '',
                    'target' => ($target = Tools::getValue('target', '')) && Validate::isCleanHtml($target) ? $target : '',
                    'type' => ($type = Tools::getValue('type', '')) && Validate::isCleanHtml($type) ? $type : '',
                    'active' => ($active = Tools::getValue('active', '')) && Validate::isCleanHtml($active) ? $active : '',
                    'id_shop' => ($id_shop = Tools::getValue('id_shop', '')) && Validate::isCleanHtml($id_shop) ? $id_shop : '',
                )));
                Tools::redirect($link);
                exit();
            }
        }
        return parent::postProcess();
    }

    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        $helper->title = $this->l('URL redirects');
    }

    public function renderList()
    {
        $form = parent::renderOptions();
        $this->display = 'list';
        $this->initToolbar();
        if(isset($this->toolbar_btn['save']))
        {
            unset($this->toolbar_btn['save']);
        }
        return $form.parent::renderList();
    }
    public function renderOptions()
    {
        $this->fields_options = array();
        return parent::renderOptions();
    }

}