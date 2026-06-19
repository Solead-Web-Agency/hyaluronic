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

class AdminEtsSeoSocialFacebookController extends ModuleAdminController
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
            'facebook_setting' => array(
                'title' => $this->l('Facebook settings'),
                'fields' => $seoDef->fields_config()['facebook_setting'],
                'icon'=> '',
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
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
            
            $this->setDefaultValue('ETS_SEO_FACEBOOK_FP_IMG_URL');
            $this->setDefaultValue('ETS_SEO_FACEBOOK_DEFULT_IMG_URL');
            $fbImgUrl = ($fbImgUrl = Tools::getValue('ETS_SEO_FACEBOOK_FP_IMG_URL')) && Validate::isCleanHtml($fbImgUrl) ? $fbImgUrl : '';
            if(isset($_FILES['ETS_SEO_FACEBOOK_FP_IMG_URL']) && $fbImgUrl)
            {
                $this->checkImageUploaded('ETS_SEO_FACEBOOK_FP_IMG_URL');
            }
            $defaultImgUrl = ($defaultImgUrl = Tools::getValue('ETS_SEO_FACEBOOK_DEFULT_IMG_URL')) && Validate::isCleanHtml($defaultImgUrl) ? $defaultImgUrl : '';
            if(isset($_FILES['ETS_SEO_FACEBOOK_DEFULT_IMG_URL'])&& $defaultImgUrl)
            {
                $this->checkImageUploaded('ETS_SEO_FACEBOOK_DEFULT_IMG_URL');
            }
        }
        parent::postProcess();
        

    }

    protected function checkImageUploaded($file_name)
    {
        $image = $_FILES[$file_name];
        if(!$image['name'] || $image['error'] > 0)
        {
            return false;
        }
        $allowExtentions = array('png', 'jpg', 'jpeg', 'gif');
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        if(!in_array($ext, $allowExtentions))
        {
            $this->errors[] = $file_name =='ETS_SEO_FACEBOOK_DEFULT_IMG_URL' ? $this->l('The image default must be a image') : $this->l('The frontpage image logo must be a image');
        }
        elseif(ImageManager::validateUpload($image, 2097152))
        {
            $this->errors[] = $file_name =='ETS_SEO_FACEBOOK_DEFULT_IMG_URL' ? $this->l('The image default logo too large. Maximum size: 2M') :  $this->l('The frontpage image logo too large. Maximum size: 2M');
        }
        else{
            
            if(Configuration::get($file_name) && file_exists(_PS_ROOT_DIR_.'/img/social/'.Configuration::get($file_name)))
            {
               unlink(_PS_ROOT_DIR_.'/img/social/'.Configuration::get($file_name));
                
            }
            $this->uploadLogoImage($image, $file_name);
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
        $requestValue = Tools::getValue($key);
        if(!is_array($requestValue) && !Validate::isCleanHtml($requestValue)){
            $requestValue = '';
        }
        if(Configuration::get($key) && !$requestValue){
            $_POST[$key] = Configuration::get($key);
        }
    }

}