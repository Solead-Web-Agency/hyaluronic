<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class IBMTranslate extends TranslationProvider
{
    public function __construct($saved_data)
    {
        parent::__construct($saved_data);
        $this->data = array (
            'name' => 'Watson Ttranslate (IBM)',
            'fields' => array(
                'api_key' => array(
                    'type' => 'text',
                ),
                'url' => array(
                    'type' => 'text',
                ),
            ),
            'links' => array(
                'pricing' => 'https://cloud.ibm.com/catalog/services/language-translator',
            ),
        );
        $this->supported_languages = array(
            'af', 'ar', 'az', 'ba', 'be', 'bg', 'bn', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en', 'eo', 'es',
            'et', 'eu', 'fa', 'fi', 'fr', 'ga', 'gu', 'he', 'hi', 'hr', 'ht', 'hu', 'hy', 'is', 'it', 'ja',
            'ka', 'kk', 'km', 'ko', 'ku', 'ky', 'lt', 'lv', 'ml', 'mn', 'ms', 'mt', 'nb', 'nl', 'nn', 'pa',
            'pl', 'ps', 'pt', 'ro', 'ru', 'sk', 'sl', 'so', 'sq', 'sr', 'sv', 'ta', 'te', 'th', 'tr', 'uk',
            'ur', 'vi', 'zh', 'zh-TW'
        );
        $this->supported_models = array(
            'ar-en', 'bg-en', 'ca-es', 'cs-en', 'da-en', 'de-en', 'de-fr', 'de-it', 'el-en', 'en-ar', 'en-bg',
            'en-cs', 'en-da', 'en-de', 'en-el', 'en-es', 'en-et', 'en-fi', 'en-fr', 'en-ga', 'en-he', 'en-hi',
            'en-hr', 'en-hu', 'en-id', 'en-it', 'en-ja', 'en-ko', 'en-lt', 'en-lv', 'en-ms', 'en-nb', 'en-nl',
            'en-pl', 'en-pt', 'en-ro', 'en-ru', 'en-sk', 'en-sl', 'en-sv', 'en-th', 'en-tr', 'en-ur', 'en-vi',
            'en-zh', 'en-zh-TW', 'es-ca', 'es-en', 'es-fr', 'et-en', 'fi-en', 'fr-de', 'fr-en', 'fr-es', 'ga-en',
            'he-en', 'hi-en', 'hr-en', 'hu-en', 'id-en', 'it-de', 'it-en', 'ja-en', 'ko-en', 'lt-en', 'lv-en',
            'ms-en', 'nb-en', 'nl-en', 'pl-en', 'pt-en', 'ro-en', 'ru-en', 'sk-en', 'sl-en', 'sv-en', 'th-en',
            'tr-en', 'ur-en', 'vi-en', 'zh-TW-en', 'zh-en'
        );
        $this->iso_substitutions['tw'] = 'zh-TW';
        $this->free_limit = array('m' => '1 000 000');
        $this->api_version = '2018-05-01';
    }

    public function getModelsData($shop_languages)
    {
        $shop_models = $shop_models_sorted = array();
        do {
            $iso_1 = array_shift($shop_languages);
            $iso_1_compat = $this->compatibleISO($iso_1);
            foreach ($shop_languages as $iso_2) {
                $iso_2_compat = $this->compatibleISO($iso_2);
                $shop_models[$iso_1_compat.'-'.$iso_2_compat] = $iso_1.'→'.$iso_2;
                $shop_models[$iso_2_compat.'-'.$iso_1_compat] = $iso_2.'→'.$iso_1;
            }
        } while ($shop_languages);
        ksort($shop_models);
        foreach ($shop_models as $model_compatible => $model_orig) {
            if (in_array($model_compatible, $this->supported_models)) {
                $shop_models_sorted['supported'][] = $model_orig;
            } else {
                $shop_models_sorted['not_supported'][] = $model_orig;
            }
        }
        return $shop_models_sorted;
    }

    public function supportsModel($from, $to)
    {
        return in_array($this->compatibleISO($from).'-'.$this->compatibleISO($to), $this->supported_models);
    }

    public function getTranslation($content, $from, $to)
    {
        $data = array(
            'url' => $this->getCredentials('url').'/v3/translate',
            'get_fields' => 'version='.$this->api_version,
            'headers' => array('Content-Type: application/json'),
            'post_fields' => array('text' => $content, 'model_id' => $from.'-'.$to),
            'login:password' => 'apikey:'.$this->getCredentials('api_key'),
        );
        $response = $this->curlRequest($data);
        if (!$this->detectPossibleErrors($response)) {
            return array_column($response['translations'], 'translation');
        }
    }

    public function detectPossibleErrors($response)
    {
        if (isset($response['error'])) {
            $this->errors[] = $response['error'];
        } elseif (!isset($response['translations'][0]['translation'])) {
            $this->errors[] = 'error';
        }
        return $this->errors;
    }

    public function getSupportedLanguages()
    {
        $data = array(
            'url' => $this->getCredentials('url').'/v3/identifiable_languages',
            'get_fields' => 'version='.$this->api_version,
            'login:password' => 'apikey:'.$this->getCredentials('api_key'),
        );
        $response = $this->curlRequest($data);
        return isset($response['languages']) ? array_column($response['languages'], 'language') : 'error';
    }

    public function getAvailableModels()
    {
        $data = array(
            'url' => $this->getCredentials('url').'/v3/models',
            'get_fields' => 'version='.$this->api_version,
            'login:password' => 'apikey:'.$this->getCredentials('api_key'),
        );
        $response = $this->curlRequest($data);
        return isset($response['models']) ? array_column($response['models'], 'model_id') : 'error';
    }
}
