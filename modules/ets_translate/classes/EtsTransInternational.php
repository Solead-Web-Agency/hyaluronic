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

class EtsTransInternational
{
    public static function getEmailSource($formData)
    {
        $data = array(
            'source' => array(),
            'target' => array(),
        );
        $transData = $formData['trans_data'];
        $transOption = $formData['trans_option'];
        $selectedTheme = $formData['selected_theme'];
        $langSource  = Language::getIsoById($formData['trans_source']);
        $langTarget  = Language::getIsoById($formData['trans_target'][0]);
        if(!isset($transData['source'])){
            return $data;
        }
        $mailExts = array('html', 'txt');
        foreach ($transData['source'] as $key=> $item){
            if((int)$formData['trans_all']){
                foreach ($mailExts as $ext){
                    $key2 = urldecode($key);
                    $key2 = str_replace(array('core_mail[', 'module_mail['), array('core_mail['.$ext.'][', 'module_mail['.$ext.']['), $key2);
                    $keyEncode = urlencode($key2);
                    $item2 = str_replace(array('core_mail|', 'module_mail|'), array('core_mail|'.$ext.'|', 'module_mail|'.$ext.'|'), $item);
                    $dataCurrent = isset($transData['target'][$formData['trans_target'][0]][$key][$ext]) ? $transData['target'][$formData['trans_target'][0]][$key][$ext] : '';
                    $dataItem = self::getEmailItemData($item2, $transOption, $langSource, $langTarget, $selectedTheme, $dataCurrent);

                    if($dataItem['isTranslatable']){
                        $data['source'][$keyEncode] = $dataItem['content'];
                        $data['target'][$formData['trans_target'][0]][$keyEncode] = 1;
                        $data['original_content'][$keyEncode] = isset($dataItem['original_content']) ? $dataItem['original_content'] : null;
                    }
                }
            }
            else{
                $dataCurrent = isset($transData['target'][$formData['trans_target'][0]][$key]) ? $transData['target'][$formData['trans_target'][0]][$key] : '';
                $dataItem = self::getEmailItemData($item, $transOption, $langSource, $langTarget[0], $selectedTheme, $dataCurrent);
                if($dataItem['isTranslatable']){
                    $data['source'][$key] = $dataItem['content'];
                    $data['target'][$formData['trans_target'][0]][$key] = 1;
                    $data['original_content'][$key] = isset($dataItem['original_content']) ? $dataItem['original_content'] : null;
                }
            }

        }
        return $data;
    }

    public static function getEmailItemData($item, $transOption, $langSource, $langTarget, $selectedTheme, $emailCurrentContent, $getTargetContent=false)
    {
        $result = array(
            'content' => '',
            'isTranslatable' => 0
        );
        $filePath = null;
        $sourcePath = null;
        $pathData =  explode('|', $item);
        $fileType = isset($pathData[1]) ? $pathData[1] : null;
        $fileName = null;
        if($pathData[0] == 'core_mail'){ // Is core Email
            $fileName = $pathData[2];
            if($selectedTheme && $selectedTheme != '0') {
                $filePath = _PS_ROOT_DIR_.'/themes/'.$selectedTheme;
            }
            else{
                $filePath = _PS_ROOT_DIR_;
            }
            $sourcePath = _PS_ROOT_DIR_;
        }
        elseif($pathData[0] == 'module_mail'){ // Is module email
            $fileName = $pathData[3];
            if($selectedTheme && $selectedTheme != '0')
            {
                $filePath = _PS_ROOT_DIR_.'/themes/'.$selectedTheme.'/modules/'.$pathData[2];
            }
            else{
                $filePath = _PS_MODULE_DIR_.$pathData[2];
            }
            $sourcePath = _PS_MODULE_DIR_.$pathData[2];
        }
        if(!$filePath){
            return $result;
        }

        $filePathSource = rtrim($sourcePath, '/ ').'/mails/'.$langSource.'/'.$fileName.'.'.$fileType;

        if(!file_exists($filePathSource)){
            return $result;
        }
        $emailContentSource = Tools::file_get_contents($filePathSource);
        if($getTargetContent){
            $filePathTarget = rtrim($filePath, '/ ').'/mails/'.$langTarget.'/'.$fileName.'.'.$fileType;
            $emailContentTarget = file_exists($filePathTarget) ? Tools::file_get_contents($filePathTarget) : '';
        }
        else
            $emailContentTarget = $emailCurrentContent;
        if ($fileType == 'html'){
            preg_match('/<body[^>]*>(.*?)<\/body>/is', $emailContentSource, $matches1);
            if($matches1 && isset($matches1[1])){
                $result['original_content'] = $emailContentSource;
                $emailContentSource = $matches1[1];
            }
            preg_match('/<body[^>]*>(.*?)<\/body>/is', $emailContentTarget, $matches2);
            if($matches2 && isset($matches2[1])){
                $emailContentTarget = $matches2[1];
            }
        }
        $emailContentTarget = trim($emailContentTarget,"\n\r ");
        $emailContentSource = trim($emailContentSource,"\n\r ");
        switch ($transOption){
            case 'both':
                if(!$emailContentTarget || $emailContentSource == $emailContentTarget){
                    $result['content'] = $emailContentSource;
                    $result['isTranslatable'] = 1;
                }
                break;
            case 'only_empty':
                if(!$emailContentTarget){
                    $result['content'] = $emailContentSource;
                    $result['isTranslatable'] = 1;
                }
                break;
            case 'same_source':
                if($emailContentSource == $emailContentTarget){
                    $result['content'] = $emailContentSource;
                    $result['isTranslatable'] = 1;
                }
                break;
            case 'all':
                $result['content'] = $emailContentSource;
                $result['isTranslatable'] = 1;
                break;
        }
        if($fileType == 'txt'){
            $result['content'] = str_replace("\r\n", "<br/>", $result['content']);
            $result['content'] = str_replace("\n", "<br/>", $result['content']);
        }

        $result['content'] = preg_replace('/(>[^<]*)(\{\w+\})([^<]*<)/', '${1}<span class="notranslate">${2}</span>${3}', $result['content']);
        return $result;
    }

    public static function getEmailTemplate($selectedTheme = false, $groupByEmail = false)
    {
        if($selectedTheme){
            //
        }
        $emailData = array();

        /*if($selectedTheme && $selectedTheme != '0'){
            $pathDir = _PS_THEME_DIR_;
        }
        else{
            $pathDir = _PS_ROOT_DIR_;
        }*/
        $pathDir = _PS_ROOT_DIR_;
        $pathDir = rtrim($pathDir, '/ ');
        $emailCoreDir = $pathDir.'/mails/en';
        $moduleDir = $pathDir.'/modules';
        if(!is_dir($emailCoreDir)){
            return $emailData;
        }
        $emailCoreList = scandir($emailCoreDir);
        if(!is_dir($emailCoreDir) || !is_dir($moduleDir)){
            return $emailData;
        }
        $extList = array('html', 'txt');

        foreach ($emailCoreList as $item){
            if($item == '.' || $item == '..'){
                continue;
            }
            $extFile = pathinfo($item, PATHINFO_EXTENSION);
            if(!in_array($extFile, $extList)){
                continue;
            }
            $key = 'core_mail['.$extFile.']['.basename($item, '.'.$extFile).']';
            $emailData[] = array(
                'file' => $item,
                'key' => $key,
                'name' => $item,
                'type' => 'core_email',
                'email_type' => $extFile
            );
        }

        foreach (scandir($moduleDir) as $item){
            if($item == '.' || $item == '..'){
                continue;
            }
            if(is_file($moduleDir.'/'.$item)){
                continue;
            }
            $moduleMailDir = $moduleDir.'/'.$item.'/mails/en';
            if(!is_dir($moduleMailDir)){
                continue;
            }

            foreach (scandir($moduleMailDir) as $mail){
                $extFile = pathinfo($mail, PATHINFO_EXTENSION);
                if(!in_array($extFile, $extList)){
                    continue;
                }
                $key = 'module_mail['.$extFile.']['.$item.'|'.basename($mail, '.'.$extFile).']';
                $emailData[] = array(
                    'file' => $mail,
                    'key' => $key,
                    'type' => 'module_mail',
                    'name' => $item,
                    'email_type' => $extFile
                );
            }
        }

        if($groupByEmail){
            $emails = array();
            foreach ($emailData as $item){
                $tmp = $item;
                $tmp['key'] = str_replace(array('[txt]', '[html]'), array('', ''), $tmp['key']);
                $tmp['file'] = str_replace('.'.$tmp['email_type'], '', $tmp['file']);
                if(!isset($emails[$tmp['key']])){
                    $emails[$tmp['key']] = $tmp;
                }
            }
            return $emails;
        }
        return $emailData;
    }

    public static function modifyResultTranslated($resultData, $emailData)
    {
        if(!$resultData)
        {
            return $resultData;
        }
        foreach ($resultData as $idLang=>&$item)
        {
            if($idLang){
                //
            }
            if(!$item || !is_array($item)){
                continue;
            }
            foreach ($item as $key=>$transItem){
                if(isset($emailData['original_content'][$key]))
                {
                    $item[$key] = preg_replace('/(<body[^>]*>)(.+?)(<\/body>)/is', '${1}'.$transItem.'${3}', $emailData['original_content'][$key]);
                }
                $item[$key] = preg_replace('/(<span class="notranslate">)(\{\w+\})(<\/span>)/', '${2}', $item[$key]);
            }
        }
        return $resultData;
    }

    public static function saveEmailTranslated($emailData, $selectedTheme)
    {
        if(!@is_array($emailData)){
            return false;
        }
        foreach ($emailData as $idLang=>$item){
            if(!is_array($item)){
                continue;
            }
            foreach ($item as $key=>$content){
                $keyCode = urldecode($key);
                $keyCode = str_replace(array('[', ']'), array('|', ''), $keyCode);
                $splitKey = explode('|', $keyCode);
                if($selectedTheme && $selectedTheme != '0')
                {
                    $pathFile = _PS_ROOT_DIR_.'/themes/'.$selectedTheme;
                }
                else{
                    $pathFile = _PS_ROOT_DIR_;
                }
                $emailType = $splitKey[0];
                $emailFormat = isset($splitKey[1]) ? $splitKey[1] : null;
                $fileName = $emailType == 'core_mail' ? $splitKey[2] : $splitKey[3];
                $moduleName = $emailType == 'module_mail' ? $splitKey[2] : null;
                self::saveEmailContent($idLang, $pathFile, $emailType, $emailFormat, $fileName, $moduleName, $content);
            }
        }
    }

    public static function saveEmailContent($idLang,$path, $emailType, $emailFormat, $fileName, $moduleName, $content)
    {
        if(!$path || !$emailType || !$emailFormat || !$fileName){
            return false;
        }
        $path = rtrim($path, '/ ');
        $isoCode = Language::getIsoById($idLang);
        if($emailFormat == 'txt'){
            $content = str_replace("<br/>", "\n", $content);
        }

        if($emailType == 'core_mail'){
            if(!@is_dir($path.'/mails/'.$isoCode)){
                @mkdir($path.'/mails/'.$isoCode);
            }
            @file_put_contents($path.'/mails/'.$isoCode.'/'.$fileName.'.'.$emailFormat, $content);
        }
        else{
            if(!$moduleName){
                return false;
            }
            if(!@is_dir($path.'/modules/'.$moduleName.'/mails')){
                return false;
            }
            if(!@is_dir($path.'/modules/'.$moduleName.'/mails/'.$isoCode)){
                @mkdir($path.'/modules/'.$moduleName.'/mails/'.$isoCode);
            }
            @file_put_contents($path.'/modules/'.$moduleName.'/mails/'.$isoCode.'/'.$fileName.'.'.$emailFormat, $content);
        }
        return true;
    }

    public static function getTextLength($dataTrans)
    {
        if(!$dataTrans || !is_array($dataTrans)){
            return 0;
        }
        $textLength = 0;
        foreach ($dataTrans as $value){
            $textLength += Tools::strlen(strip_tags($value));
        }
        return $textLength;
    }

    public static function getTotalEmailTranslate($dataEmail)
    {
        if(!$dataEmail || !is_array($dataEmail)){
            return 0;
        }
        $arrayData = array();
        foreach ($dataEmail as $items){
            foreach ($items as $key=>$val){
                if(!$val){
                    continue;
                }
                $keyMail = urldecode($key);
                $keyMail = str_replace(array('[txt]', '[html]'), array('',''), $keyMail);
                if(!in_array($keyMail, $arrayData)){
                    $arrayData[] = $keyMail;
                }
            }
        }

        return count($arrayData);
    }

    public static function analysisBeforeTranslate($pageType, $formData, $step, $selected, $sfType, $isLoadFile=0, $resetData=0)
    {
        if($pageType == 'module'){
            $transModule = new EtsTransModule($selected);
            if((int)$isLoadFile){
                if($resetData){
                    $transModule->deleteModuleCache();
                }
                $transModule->loadModuleFiles();
                return array(
                    'load_file_done' => 1
                );
            }
            $transModule->setLangSource($formData['trans_source']);
            $transModule->setLangTarget($formData['trans_target']);
            $transModule->setTransOption($formData['trans_option']);
            return $transModule->analysisModule();
        }
        elseif ($pageType == 'email'){
            $mailCheck = isset($formData['mail_option']) && is_array($formData['mail_option']) ? array_splice($formData['mail_option'], 0, 4) : array();
            return self::analysisEmail($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $mailCheck, $selected);
        }
        elseif ($pageType == 'theme'){
            $transType = 'theme';
            switch ($sfType){
                case 'themes':
                    $transType = 'theme';
                    break;
                case 'mails':
                    $transType = 'mail';
                    break;
                case 'others':
                    $transType = 'others';
                    break;
                case 'modules':
                    $transType = 'sfmodule';
                    break;
                case 'back':
                    $transType = 'back';
                    break;
            }
            $transSystem = new EtsTransNewSystem($transType);
            $transSystem->setLangSource($formData['trans_source']);
            $transSystem->setLangTarget($formData['trans_target']);
            $transSystem->setTransOption($formData['trans_option']);
            $transSystem->setSelectedName($selected);
            $transSystem->setAnalyzing(true);
            if($isLoadFile){
                $res = $transSystem->loadFileSystem($step);
                if($res === true){
                    return array(
                        'load_file_done' => 1
                    );
                }
                return array(
                    'load_file_done' => 0,
                    'stop'=>0,
                    'next_step' => $step+1,
                );
            }
            else{
                return $transSystem->analysisFiles();
            }

        }
    }

    public static function analysisEmail($langSource, $langTarget, $transOption, $mailOption, $selectedTheme)
    {
        $mailExts = array('html', 'txt');
        $nbText = 0;
        $nbChar=0;
        $nbMoney=0;
        $mailValid = array();
        $api = EtsTransApi::getInstance();
        foreach ($mailOption as $opt){
            foreach ($mailExts as $ext){
                $item = str_replace(array('[', ']'), array('|', ''), $opt);
                $item = str_replace(array('core_mail|', 'module_mail|'), array('core_mail|'.$ext.'|', 'module_mail|'.$ext.'|'), $item);
                $dataItem = self::getEmailItemData($item, $transOption, Language::getIsoById($langSource), Language::getIsoById($langTarget[0]), $selectedTheme, null, true);
                if($dataItem['isTranslatable']){
                    $nbText+=1;
                    if(!in_array($opt, $mailValid)){
                        $mailValid[] = $opt;
                    }
                    $nbChar += Tools::strlen(str_replace(array("\r", "\n"), array('', ''), strip_tags($dataItem['content'])));
                    $nbMoney += $api->getTotalFeeTranslate($nbChar);
                }
            }
        }

        return array(
            'nb_text' => $nbText,
            'nb_char' => $nbChar,
            'nb_money' => $nbMoney,
            'mail_checked' => $mailOption,
            'mail_valid' => $mailValid ?: '',
            'stop' => !$mailOption || count($mailOption) < 4 ? 1 : 0,
        );
    }
}