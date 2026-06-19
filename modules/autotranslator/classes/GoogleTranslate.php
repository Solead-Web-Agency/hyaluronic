<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class GoogleTranslate extends TranslationProvider
{
    public function __construct($saved_data)
    {
        parent::__construct($saved_data);
        $this->data = array (
            'name' => 'Google Cloud Translate',
            'fields' => array(
                'api_key' => array(
                    'type' => 'text',
                ),
            ),
            'links' => array(
                'pricing' => 'https://cloud.google.com/translate/pricing',
            ),
        );
        $this->supported_languages = array(
            'af', 'am', 'ar', 'az', 'be', 'bg', 'bn', 'bs', 'ca', 'ceb', 'co', 'cs', 'cy', 'da', 'de', 'el',
            'en', 'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'fy', 'ga', 'gd', 'gl', 'gu', 'ha', 'haw', 'hi',
            'hmn', 'hr', 'ht', 'hu', 'hy', 'id', 'ig', 'is', 'it', 'iw', 'ja', 'jw', 'ka', 'kk', 'km', 'kn',
            'ko', 'ku', 'ky', 'la', 'lb', 'lo', 'lt', 'lv', 'mg', 'mi', 'mk', 'ml', 'mn', 'mr', 'ms', 'mt',
            'my', 'ne', 'nl', 'no', 'ny', 'pa', 'pl', 'ps', 'pt', 'ro', 'ru', 'sd', 'si', 'sk', 'sl', 'sm',
            'sn', 'so', 'sq', 'sr', 'st', 'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'tl', 'tr', 'uk', 'ur',
            'uz', 'vi', 'xh', 'yi', 'yo', 'zh', 'zh-TW', 'zu',
        );
        $this->supported_languages[] = 'he'; // not included in getSupportedLanguages(), but it is an alias for iw
        $this->iso_substitutions['tw'] = 'zh-TW';
        $this->yearly_trial = '300 USD';
        $this->api_url = 'https://translation.googleapis.com/language/translate/v2';
    }

    public function getTranslation($content, $from, $to)
    {
        $data = array(
            'url' => $this->api_url,
            'get_fields' => array(
                'key' => $this->getCredentials('api_key'),
            ),
            'post_fields' => array(
                'q' => $content,
                'source' => $from,
                'target' => $to,
                'format' => 'html',
            ),
            'headers' => array(
                'Accept: application/json',
                'Content-Type: application/json',
            ),
        );
        $response = $this->curlRequest($data);
        if (!$this->detectPossibleErrors($response)) {
            return array_column($response['data']['translations'], 'translatedText');
        }
    }

    public function detectPossibleErrors($response)
    {
        if (isset($response['error'])) {
            $this->errors[] = $response['error']['message'];
        } elseif (!isset($response['data']['translations'][0]['translatedText'])) {
            $this->errors[] = 'error';
        }
        return $this->errors;
    }

    public function getSupportedLanguages()
    {
        $data = array(
            'url' => $this->api_url.'/languages',
            'get_fields' => array(
                'key' => $this->getCredentials('api_key'),
            ),
        );
        $response = $this->curlRequest($data);
        return isset($response['data']['languages']) ? array_column($response['data']['languages'], 'language')
        : 'error';
    }
}
