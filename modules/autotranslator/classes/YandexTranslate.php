<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class YandexTranslate extends TranslationProvider
{
    public function __construct($saved_data)
    {
        parent::__construct($saved_data);
        $this->data = array (
            'name' => 'Yandex.Translate v1.5',
            'fields' => array(
                'api_key' => array(
                    'type' => 'text',
                ),
            ),
            'links' => array(
                'pricing' => 'https://translate.yandex.com/developers/prices',
            ),
        );
        $this->supported_languages = $this->getSupportedLanguages();
        $this->api_url = 'https://translate.yandex.net/api/v1.5/tr.json/';
        $this->request_max_length = 10000;
        $this->element_max_length = 10000;
    }

    public function getTranslation($content, $from, $to)
    {
        $data = array(
            'url' => $this->api_url.'translate',
            'headers' => array('Content-Type: application/x-www-form-urlencoded'),
            'post_fields' => http_build_query(array(
                'key'    => $this->getCredentials('api_key'),
                'lang'   => $from.'-'.$to,
                'format' => 'html'
            )),
        );
        foreach ($content as $c) {
            $data['post_fields'] .= '&text='.urlencode($c); // extra characters added by urlencode are not counted
        }
        $response = $this->curlRequest($data);
        if (!$this->detectPossibleErrors($response)) {
            return $response['text'];
        }
    }

    public function detectPossibleErrors($response)
    {
        if ($response['code'] !== 200) {
            $this->errors[] = isset($response['message']) ? $response['message'] : 'unknown_error';
        } elseif (!isset($response['text'][0])) {
            $this->errors[] = 'error | no translation received from server';
        }
        return $this->errors;
    }

    public function getSupportedLanguages()
    {
        return array(
            'af', 'am', 'ar', 'az', 'ba', 'be', 'bg', 'bn', 'bs', 'ca', 'ceb','cs', 'cy', 'da', 'de', 'el',
            'en', 'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'ga', 'gd', 'gl', 'gu', 'he', 'hi', 'hr', 'ht',
            'hu', 'hy', 'id', 'is', 'it', 'ja', 'jv', 'ka', 'kk', 'km', 'kn', 'ko', 'ky', 'la', 'lb', 'lo',
            'lt', 'lv', 'mg', 'mhr', 'mi', 'mk', 'ml', 'mn', 'mr', 'mrj', 'ms', 'mt', 'my', 'ne', 'nl', 'no',
            'pa', 'pap', 'pl', 'pt', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr', 'su', 'sv', 'sw', 'ta', 'te',
            'tg', 'th', 'tl', 'tr', 'tt', 'udm', 'uk', 'ur', 'uz', 'vi', 'xh', 'yi', 'zh'
        );
        // $data = array(
        //     'url' => $this->api_url.'getLangs',
        //     'post_fields' => http_build_query(array(
        //         'key'    => $this->getCredentials('api_key'),
        //         'ui'   => 'en',
        //     )),
        // );
        // $response = $this->curlRequest($data); // '\''.implode('\', \'', $keys).'\''
        // return isset($response['langs']) ? array_keys($response['langs']) : 'error';
    }
}
