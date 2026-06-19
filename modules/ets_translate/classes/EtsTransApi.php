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

class EtsTransApi
{
    public static $instance = null;
    protected $googleTransApiKey = null;
    protected $apiType = null;
    public function __construct()
    {
        $this->googleTransApiKey = Configuration::get('ETS_TRANS_GOOGLE_API_KEY');
        $this->apiType = 'google';
    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new EtsTransApi();
        }
        return self::$instance;
    }

    public function request($type, $uri, $params = array(), $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        if($headers){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        if($params && Tools::strtoupper($type) === 'POST'){
            curl_setopt($ch, CURLOPT_POSTFIELDS, preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($params)));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function googleTrans($source, $target, $textContent)
    {
        if(!$textContent){
            return array(
                'errors' => false,
                'data' => array()
            );
        }
        if(!$this->googleTransApiKey)
        {
            return array(
                'errors' => 'No Google translate API key found.',
                'message' => 'No Google translate API key found.',
                'data' => array()
            );
        }

        if(!is_array($textContent))
        {
            $textContent = array($textContent);
        }
        if($source && $source !== 'en')
            $source = explode('-',Language::getLanguageCodeByIso($source))[0];
        if($target !== 'fr')
            $target = explode('-',Language::getLanguageCodeByIso($target))[0];
        $queryUrl = 'key='.$this->googleTransApiKey;
        $params = array('q' => $textContent, 'target' => $target,'format'=> 'html');
        if($source){
            $params['source'] = $source;
        }
        $res = $this->request(
            'POST',
            'https://www.googleapis.com/language/translate/v2?'.$queryUrl,
            $params,
            array("X-HTTP-Method-Override: GET")
        );
        if($res){
            $jsonResponse = Tools::jsonDecode($res, true);
            if(isset($jsonResponse['data']) && isset($jsonResponse['data']['translations'])){
                $translated = array();
                $detectSourceLang = array();
                foreach ($jsonResponse['data']['translations'] as $item){
                    $translated[] = htmlspecialchars_decode($item['translatedText'], ENT_QUOTES);
                    if(!$source){
                        $detectSourceLang[] = isset($item['detectedSourceLanguage']) ? $item['detectedSourceLanguage'] : null;
                    }
                }
                return array(
                    'errors' => false,
                    'data' => $translated,
                    'detectedSourceLanguage' => $detectSourceLang
                );
            }
            if(isset($jsonResponse['error']) && isset($jsonResponse['error']['message'])){
                return array(
                    'errors' => true,
                    'message' => $jsonResponse['error']['message'],
                    'data' => array(),
                );
            }
        }
        return array(
            'errors' => true,
            'data' => array()
        );
    }

    public function translate($source, $target, $textContent, $pageType = null)
    {
        $isUseAppendWords = $this->isUseAppendContextualWords($pageType, $source);
        $textContent = $this->formatBeforeTranslate($textContent, $isUseAppendWords);
        $textChunk = array_chunk($textContent, 100);
        $resultTrans = array(
            'errors' => false,
            'message' => '',
            'data' => array(),
            'detectedSourceLanguage' => array(),
        );
        foreach ($textChunk as $textItems){
            $resultItem = $this->googleTrans($source, $target, $textItems);
            if(isset($resultItem['errors']) && $resultItem['errors']){
                $resultTrans['errors'] = true;
                $resultTrans['message'] = isset($resultItem['message']) ? $resultItem['message'] : 'Error';
                break;
            }
            else{
                $resultTrans['data'] = array_merge($resultTrans['data'], $resultItem['data']);
                if(!$source && isset($resultItem['detectedSourceLanguage'])){
                    $resultTrans['detectedSourceLanguage'] = array_merge($resultTrans['detectedSourceLanguage'], $resultItem['detectedSourceLanguage']);
                }
            }
        }

        $resultTrans['data'] = $this->formatAfterTranslate($resultTrans['data'], $textContent, $source, $target, $isUseAppendWords);
        return $resultTrans;
    }

    public function validateApiKey($key)
    {
        $this->googleTransApiKey = $key;
        $res = $this->googleTrans('en', 'fr', 'hi');
        if($res && !$res['errors'] && $res['data']){
            return true;
        }
        return false;
    }

    public function formatBeforeTranslate($textTrans, $isAppendContextualWord = false){
        $listExcludes = $this->getExcludeWords();
        foreach ($textTrans as $key=>$text){
            if($listExcludes){
                $excludeRegex = array();
                $replacements = array();
                foreach ($listExcludes as $word){
                    $excludeRegex[] = '/(?:^|\s)('.$word.')(?:$|\s)/i';
                    $replacements[] = '<span class="notranslate exclude">$1</span>';
                }
                $textTrans[$key] = preg_replace($excludeRegex, $replacements, $text);
                if($isAppendContextualWord){
                    if($maxLength = (int)Configuration::get('ETS_TRANS_MAX_WORD_APPEND_CONTEXT_WORD')){
                        if($this->countWords($text) <= $maxLength){
                            $contextualWords = Configuration::get('ETS_TRANS_CONTEXT_WORDS');
                            $textTrans[$key] = $textTrans[$key].'<span class="notranslate contextual">'.strip_tags($contextualWords).'</span>';
                        }
                    }
                    else{
                        $contextualWords = Configuration::get('ETS_TRANS_CONTEXT_WORDS');
                        $textTrans[$key] = $textTrans[$key].'<span class="notranslate contextual">'.strip_tags($contextualWords).'</span>';
                    }
                }
            }
        }

        return $textTrans;
    }
    public function formatAfterTranslate($textTrans, $textOriginal=  array(),$langSource, $langTarget, $isAppendContextualWord = false){

        foreach ($textTrans as $key=>$text){
            $textTrans[$key] = preg_replace('/<span class="notranslate exclude">(.*?)<\/span>/', '$1', $text);
            if($isAppendContextualWord) {
                $textTrans[$key] = preg_replace('/<span class="notranslate contextual">(.*?)<\/span>/', '', $textTrans[$key]);
                //Re-translate if response data is empty
                if(!trim($textTrans[$key])){
                    $res = $this->googleTrans($langSource, $langTarget, array(preg_replace('/<span class="notranslate contextual">(.*?)<\/span>/', '', $textOriginal[$key])));
                    if((!isset($res['errors']) || !$res['errors']) && isset($res['data']) && $res['data']){
                        $textTrans[$key] = $res['data'][0];
                    }
                }
                if($textOriginal[$key] && Tools::substr($textOriginal[$key], -1) !== ' '){
                    $textTrans[$key] = rtrim($textTrans[$key]);
                }
            }
        }

        return $textTrans;
    }

    public function getExcludeWords()
    {
        $excludeWords = Configuration::get('ETS_TRANS_EXCLUDE_WORDS');
        if($excludeWords) {
            $listExcludes = explode("\r\n", $excludeWords);
            foreach ($listExcludes as $k=>$w){
                $listExcludes[$k] = trim($w);
            }
        }
        else{
            $listExcludes = array();
        }
        $listExcludes = array_merge($listExcludes, array('%s', '%d'));
        return $listExcludes;
    }

    public function getTotalFeeTranslate($nbChar)
    {
        switch ($this->apiType){
            case 'google':
                if(($configRate = Configuration::get('ETS_TRANS_RATE_GOOGLE')) && Tools::strlen($configRate)){
                    $perChar = (float)$configRate / 1000000;
                    return (int)$nbChar * $perChar;
                }
                return (int)$nbChar * 0.00002;
        }
        return 0;
    }

    public static function getLangCodeFromIdLang($idLang)
    {
        $isoCode = Language::getIsoById($idLang);
        if(!$isoCode){
            return null;
        }
        return explode('-',Language::getLanguageCodeByIso($isoCode))[0];
    }

    public function isUseAppendContextualWords($pageType, $langSource)
    {
        if(!(int)Configuration::get('ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD', Language::getIdByIso($langSource))){
            return false;
        }
        $catalog = array('catalog', 'product', 'category', 'manufacturer', 'supplier');
        $page = array('cms', 'cms_category', 'page');
        $inter = array('theme', 'module', 'email', 'inter', 'sfmodule', 'back', 'others');
        $configPageToAppend = Configuration::get('ETS_TRANS_PAGE_APPEND_CONTEXT_WORD',  Language::getIdByIso($langSource));
        $pageToAppend = $configPageToAppend ? explode(',', $configPageToAppend) : array();
        if(in_array('catalog', $pageToAppend) && in_array($pageType, $catalog))
            return true;
        if(in_array('page', $pageToAppend) && in_array($pageType, $page))
            return true;
        if(in_array('inter', $pageToAppend) && in_array($pageType, $inter))
            return true;
        if(in_array($pageType, $pageToAppend)){
            return true;
        }
        return false;
    }

    public function countWords($text)
    {
        $text = trim(strip_tags($text));
        preg_match_all('/\S+/', $text, $matches);
        return $matches ? count($matches) : 0;
    }
}