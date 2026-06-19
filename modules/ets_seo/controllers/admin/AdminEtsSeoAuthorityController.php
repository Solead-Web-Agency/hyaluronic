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

class AdminEtsSeoAuthorityController extends ModuleAdminController
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->bootstrap  = true;
        parent::__construct();

        $seoDef = Ets_Seo_Define::getInstance();
        $this->fields_options = array(
            'general' => array(
                'title' => $this->l('Authority'),
                'fields' => $seoDef->fields_config()['search_general'],
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );
        if (!Module::isEnabled('ets_seo'))
        {
            $this->warnings[] = $this->l('You must enable module SEO Audit to configure its features');
        }
    }


    public function postProcess()
    {

        if(Tools::isSubmit('submitOptionsconfiguration'))
        {
            if(Tools::getValue('ETS_SEO_SITE_OF_PERSON_OR_COMP') == 'COMPANY' && (!($orgName = Tools::getValue('ETS_SEO_SITE_ORIG_NAME')) || !Validate::isCleanHtml($orgName)))
            {
                $this->errors[] = $this->l('The Organization name is required.');
            }
            elseif(Tools::getValue('ETS_SEO_SITE_OF_PERSON_OR_COMP') == 'PERSON' && (!($personName = Tools::getValue('ETS_SEO_SITE_PERSON_NAME')) || !Validate::isCleanHtml($personName)))
            {
                $this->errors[] = $this->l('The Name is required.');
            }
            else{
                $this->setDefaultValue('ETS_SEO_SITE_ORIG_LOGO');
                $this->setDefaultValue('ETS_SEO_SITE_PERSON_AVATAR');
                if(isset($_FILES['ETS_SEO_SITE_ORIG_LOGO']))
                {
                    $this->checkImageUploaded('ETS_SEO_SITE_ORIG_LOGO');
                }
                if(isset($_FILES['ETS_SEO_SITE_PERSON_AVATAR']))
                {
                    $this->checkImageUploaded('ETS_SEO_SITE_PERSON_AVATAR');
                }
            }

        }

        return parent::postProcess();

    }

    public function renderOptions()
    {
        $this->context->smarty->assign(array(
            'ETS_SEO_SITE_OF_PERSON_OR_COMP' => Configuration::get('ETS_SEO_SITE_OF_PERSON_OR_COMP')
        ));
        return parent::renderOptions();
    }

    protected function checkImageUploaded($file)
    {
        $image = $_FILES[$file];
        if(!$image['name'] || $image['error'] > 0)
        {
            return false;
        }

        $allowExtentions = array('png', 'jpg', 'jpeg', 'gif');
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        if(!in_array($ext, $allowExtentions))
        {
            $this->errors[] = $file =='ETS_SEO_SITE_PERSON_AVATAR' ? $this->l('The avatar must be a image') : $this->l('The organization logo must be a image');
        }
        elseif(ImageManager::validateUpload($image, 2097152))
        {
            $this->errors[] = $file =='ETS_SEO_SITE_PERSON_AVATAR' ? $this->l('The avatar logo too large. Maximum size: 2M') :  $this->l('The organization logo too large. Maximum size: 2M');
        }
        else{

            if(Configuration::get($file) && file_exists(_PS_ROOT_DIR_.'/img/social/'.Configuration::get($file)))
            {
                unlink(_PS_ROOT_DIR_.'/img/social/'.Configuration::get($file));
            }
            $this->uploadLogoImage($image, $file);
        }
    }

    protected function uploadLogoImage($image, $name)
    {

        if(!$image['name'] || $image['error'] > 0)
        {
            return false;
        }
        $image_name = time().rand(11111, 99999).'.'.pathinfo($image['name'], PATHINFO_EXTENSION);

        if(move_uploaded_file($image['tmp_name'], _PS_ROOT_DIR_.'/img/social/'.$image_name))
        {
            Configuration::updateValue($name, $image_name);
            $_POST[$name] = $image_name;
            return true;
        }
        return false;
    }

    protected function setDefaultValue($key)
    {
        $requestKey = ($requestKey = Tools::getValue($key)) && Validate::isCleanHtml($requestKey) ? $requestKey : '';
        if(Configuration::get($key) && !$requestKey){
            $_POST[$key] = Configuration::get($key);
        }
    }

}