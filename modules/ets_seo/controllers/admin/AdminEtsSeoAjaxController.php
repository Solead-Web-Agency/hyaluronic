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

class AdminEtsSeoAjaxController extends ModuleAdminController
{
    public function postProcess()
    {
        parent::postProcess();
        if((int)Tools::getIsset('etsSeoUploadSocialImage'))
        {
            if(($old_image = Tools::getValue('old_image')) && Validate::isCleanHtml($old_image))
            {
                $this->module->deleteImage($old_image);
            }
            $img_path = $this->module->uploadImage(
                'image',
                'cache'
            );

            if($img_path){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'image' => $img_path
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'image' => ''
            )));
        }

        if((int)Tools::isSubmit('etsSeoDeleteSocialImg'))
        {
            if(($img_path = Tools::getValue('img_path')) && Validate::isCleanHtml($img_path))
            {
                if($this->module->deleteImage($img_path)){
                    if((int)Tools::getValue('id') )
                    {
                        $controllerType = ($controllerType = Tools::getValue('controller_type')) && Validate::isCleanHtml($controllerType) ? $controllerType : '';
                        if($this->module->deleteDataSocialImg((int)Tools::getValue('id'), $controllerType, basename($img_path), (int)Tools::getValue('is_cms_category')))
                            die(Tools::jsonEncode(array(
                                'success' => true
                            )));
                    }
                    else{
                        die(Tools::jsonEncode(array(
                            'success' => true
                        )));
                    }

                }
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('Can not delete this image')
            )));
        }

        //Delete logo or avatar
        if((int)Tools::isSubmit('etsSeoDeleteLogoImg'))
        {
            if(($img_path = Tools::getValue('img_path')) && Validate::isCleanHtml($img_path))
            {
                if($this->module->deleteImage($img_path)){
                    if(($name = Tools::getValue('config_name')) & Validate::isCleanHtml($name))
                    {
                        Configuration::updateValue($name, null);
                    }

                    die(Tools::jsonEncode(array(
                        'success' => true
                    )));
                }
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('Can not delete this image')
            )));
        }


        //Save score init
        if((int)Tools::isSubmit('etsSeoSaveScore', false))
        {

            $id = (int)Tools::getValue('id');

            $seo_scores = ($seo_scores = Tools::getValue('seo_score')) && is_array($seo_scores) ? $seo_scores : array();
            $readability_scores = ($readability_scores = Tools::getValue('readability_score')) && is_array($readability_scores) ? $readability_scores : array();
            $content_analysis = ($content_analysis = Tools::getValue('content_analysis')) && is_array($content_analysis) ? $content_analysis : array();
            EtsSeoSetting::getInstance()->saveScore(Tools::getValue('page_type'), $id, $seo_scores, $readability_scores,$content_analysis, (int)Tools::getValue('is_cms_category'));

            die(Tools::jsonEncode(array(
                'success' => true,
                'message' => $this->l('Success')
            )));

        }

        if((bool)Tools::isSubmit('validateLinkRewrite'))
        {
            $type = ($type = Tools::getValue('type')) && Validate::isCleanHtml($type) ? $type : '';
            $link_rewrites = ($link_rewrites = Tools::getValue('link_rewrites')) && is_array($link_rewrites) ? $link_rewrites : array();
            EtsSeoSetting::checkLinkRewriteAjax($type, $link_rewrites, (int)Tools::getValue('id'), (int)Tools::getValue('is_cms_category'));
        }
    }
}