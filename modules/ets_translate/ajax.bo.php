<?php
/**
 * 2007-2020 ETS-Soft
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
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2020 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/ajax.init.php');

$context = Context::getContext();
$et = Module::getInstanceByName('ets_translate');

if(!$context->employee || !$context->employee->id || Tools::getValue('token') !== Tools::getAdminTokenLite('AdminModules'))
{
    return false;
}

if(Tools::isSubmit('etsTransGetFormTranslate')){
    $form = $et->getFormTrans(Tools::getValue('pageId'), Tools::getValue('pageType'));
    if($form){
        die(Tools::jsonEncode(array(
            'success' => true,
            'form' => $form,
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => false,
        'form' => '',
    )));
}
if(Tools::isSubmit('etsTransTranslatePage')){
    $formData = Tools::getValue('formData');
    $pageType = Tools::getValue('pageType');
    $isDetailPage = (int)Tools::getValue('isDetailPage');
    $result = $et->translateDataPage($formData, $pageType, $isDetailPage);

    die(Tools::jsonEncode(array(
        'success' => $result['errors'] ? false : true,
        'errors' => $result['errors'],
        'trans_data'=> $result['data'],
        'no_trans' => isset($result['noTrans']) && $result['noTrans'] ? true : false,
        'message' => isset($result['message']) ? $result['message'] : 'Error unknown!'
    )));
}
if(Tools::isSubmit('etsTransPauseTranslate')){
    $transInfo = Tools::getValue('transInfo');
    if($et->saveDataAfterPause($transInfo)){
        die(Tools::jsonEncode(array(
            'success' => true,
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => false,
    )));
}

if(Tools::isSubmit('etsTransDeleteDataPause')){
    if($et->deleteDataPause(Tools::getValue('pageType'), Tools::getValue('selected_theme'))){
        die(Tools::jsonEncode(array(
            'success' => true,
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => false,
    )));
}
if(Tools::isSubmit('etsTransAnalyzing')){
    $isLocalization = (int)Tools::getValue('isLocalization');
    if($isLocalization){
        $resultAnalysis = $et->analyzeBeforeTranslateLz(
            Tools::getValue('pageType'),
            Tools::getValue('formData'),
            Tools::getValue('step'),
            Tools::getValue('selected'),
            Tools::getValue('sfType'),
            (int)Tools::getValue('isLoadFile'),
            (int)Tools::getValue('resetData')
            );
    }
    else
        $resultAnalysis = $et->analyzeBeforeTranslate(Tools::getValue('pageType'),Tools::getValue('formData'), Tools::getValue('offset'));
    if($resultAnalysis && (!isset($resultAnalysis['errors']) || !$resultAnalysis['errors'])){
        die(Tools::jsonEncode(array(
            'success' => true,
            'data' => $resultAnalysis
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => false,
        'errors' => isset($resultAnalysis['errors']) ? $resultAnalysis['errors'] : 'Has an error',
        'data' => array()
    )));
}

if(Tools::isSubmit('etsTransAllLoadFile')){
    $result = $et->loadFileTranslateAll(Tools::getValue('formData'), Tools::getValue('pageType'));
    if(isset($result['errors']) && $result['errors']){
        die(Tools::jsonEncode(array(
            'success' => false,
            'errors' => isset($result['message']) ? $result['message'] : 'Error'
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => true,
        'total_item' => isset($result['total_item']) ? $result['total_item'] : 0,
    )));

}
if(Tools::isSubmit('etsTransTransAll')){
    $result = $et->translateAllWebData(Tools::getValue('formData'), Tools::getValue('pageType'));
    if(!isset($result['errors']) || !$result['errors']){
        if(isset($result['stop_translate']) && $result['stop_translate']){
            $ec = EtsTransConfig::getInstance();
            $ec->deletePauseData(Tools::getValue('pageType', 'all'));
        }
        die(Tools::jsonEncode(array(
            'success' => true,
            'data' => $result
        )));
    }
    die(Tools::jsonEncode(array(
        'success' => false,
        'errors' => isset($result['message']) ? $result['message'] : 'Error'
    )));
}

if(Tools::isSubmit('etsTransAnalyzingAllPage')){
    if($isInit = Tools::getValue('init'))
    {
        $et->analyzingAllPage(Tools::getValue('pageType'), Tools::getValue('formData'), Tools::getValue('offset'), true);
        die(Tools::jsonEncode(array(
            'success' => true,
            'after_init' => 1
        )));
    }
    else{
        $resultAnalysis = $et->analyzingAllPage(Tools::getValue('pageType'),Tools::getValue('formData'), Tools::getValue('offset'), false);
        die(Tools::jsonEncode(array(
            'success' => true,
            'data' => $resultAnalysis
        )));
    }
}

if(Tools::isSubmit('etsTransMegamenu') || Tools::isSubmit('etsTransBlog') || Tools::isSubmit('etsTransModulePc')){
    $formData = Tools::getValue('formData');
    $isDetailPage = 1;
    $pageType = Tools::isSubmit('etsTransMegamenu') ? 'megamenu' : 'blog';
    if(Tools::isSubmit('etsTransModulePc')){
        $pageType = 'pc';
    }
    if(Tools::getValue('isTransAll') || (isset($formData['page_id']) && $formData['page_id'] && !isset($formData['col_data']) && in_array($pageType, array('megamenu', 'blog', 'pc')))){
        if($pageType == 'megamenu'){
            $result = $et->translateAllMegamenu($formData);
        }
        elseif($pageType == 'blog'){
            $result = $et->translateAllBlog($formData);
        }
        elseif($pageType == 'pc'){
            $result = $et->translateAllModulePc($formData);
        }
        die(Tools::jsonEncode(array(
            'success' => isset($result['errors']) && $result['errors'] ? false : true,
            'errors' => isset($result['errors']) && $result['errors'] ? true : false,
            'data'=> $result,
            'no_trans' => false,
            'message' => isset($result['message']) ? $result['message'] : ''
        )));
    }
    else{
        $result = $et->translateDataPage($formData, $pageType, $isDetailPage);
        die(Tools::jsonEncode(array(
            'success' => $result['errors'] ? false : true,
            'errors' => $result['errors'],
            'trans_data'=> $result['data'],
            'no_trans' => isset($result['noTrans']) && $result['noTrans'] ? true : false,
            'message' => isset($result['message']) ? $result['message'] : ''
        )));
    }
}

