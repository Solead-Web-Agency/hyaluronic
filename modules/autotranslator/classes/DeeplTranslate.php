<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class DeeplTranslate extends TranslationProvider
{
    public function __construct($saved_data)
    {
        parent::__construct($saved_data);
        $this->data = array (
            'name' => 'DeepL Translator',
            'fields' => array(
                'plan' => array(
                    'type' => 'select',
                    'options' => array(
                        1 => 'deepl_free',
                        2 => 'deepl_pro',
                    ),
                ),
                'api_key' => array(
                    'type' => 'text',
                ),
            ),
            'links' => array(
                'pricing' => 'https://www.deepl.com/pro#developer',
            ),
        );
        $this->supported_languages = $this->getSupportedLanguages();
        // max request size 30kb: https://www.deepl.com/docs-api/accessing-the-api/
        // In UTF-8 each chacacter can take 1-4 bytes, but most widely used characters take less than 4b
        // so in most cases 10000 chars for text + other params in request should fit in 30kb limit
        $this->request_max_length = 10000;
        $this->element_max_length = 10000;
        $this->free_limit = array('m' => '500 000');
    }

    public function getAPIURL($ext = '')
    {
        $url = $this->getCredentials('plan') == 1 ? 'api-free.deepl.com' : 'api.deepl.com';
        return 'https://'.$url.'/v2/'.$ext;
    }

    public function getTranslation($content, $from, $to)
    {
        $from = current(explode('-', $from)); // PT-BR or other localized codes are not accepted in source_lang
        $data = array(
            'url' => $this->getAPIURL('translate'),
            'post_fields' => http_build_query(array(
                'auth_key'     => $this->getCredentials('api_key'),
                'source_lang'  => Tools::strtoupper($from),
                'target_lang'  => Tools::strtoupper($to),
                'tag_handling' => 'xml',
            )),
        );
        foreach ($content as $c) {
            $data['post_fields'] .= '&text='.urlencode($c);
        }
        $response = $this->curlRequest($data);
        if (!$this->detectPossibleErrors($response)) {
            return array_column($response['translations'], 'text');
        }
    }

    public function detectPossibleErrors($response)
    {
        if (isset($response['message'])) {
            $this->errors[] = $response['message'];
        } elseif (!isset($response['translations'][0]['text'])) {
            $this->errors[] = 'error | no translation received from server';
        }
        return $this->errors;
    }

    public function getSupportedLanguages()
    {
        $supported_languages = array(
            'bg', 'cs', 'da', 'de', 'el', 'en-gb', 'en-us', 'es', 'et', 'fi', 'fr', 'hu', 'it',
            'ja', 'lt', 'lv', 'nl', 'pl', 'pt-br', 'pt-pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'zh'
        );
        // iso_substitutions will be used only for target langs. Check first line of getTranslation()
        $this->iso_substitutions['en'] = 'en-us';
        $this->iso_substitutions['gb'] = 'en-gb';
        $this->iso_substitutions['br'] = 'pt-br';
        $this->iso_substitutions['pt'] = 'pt-pt'; // recommended by documentation instead of plain pt
        return $supported_languages;
        // $data = array(
        //     'url' => $this->getAPIURL('languages'),
        //     'post_fields' => 'type=target&auth_key='.$this->getCredentials('api_key'),
        // );
        // $response = $this->curlRequest($data);
        // $response =  array_map('strtolower', array_column($response, 'language'));
        // $response = '\''.implode('\', \'', $response).'\'';
    }
}
