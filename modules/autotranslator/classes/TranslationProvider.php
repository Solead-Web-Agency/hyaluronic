<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class TranslationProvider
{
    public function __construct($saved_data)
    {
        $this->saved_data = $saved_data;
        $this->errors = array();
        $this->supported_languages = array();
        $this->iso_substitutions = array(
            'gb' => 'en',
            'si' => 'sl', // Slovenian is represented by si in previous PS versions
            'vn' => 'vi', // Tieng Viet (Vietnamese)
            'nn' => 'no', // Nynorsk (Norwegian)
            'qc' => 'fr', // Francais CA (French)
            'br' => 'pt', // Brazilian (Portuguese)
            'mx' => 'es', // Mexican (Spanish)
            'tw' => 'zh', // Taiwanese (Chinese)
        );
        $this->processed_chars_num = 0;
        $this->request_max_length = 5000;
        $this->element_max_length = 5000;
    }

    public function getCredentials($key)
    {
        return isset($this->saved_data['credentials'][$key]) ?
        $this->saved_data['credentials'][$key] : '';
    }

    public function getNotSupportedLanguages($shop_languages)
    {
        $not_supported_languages = array();
        foreach (array_diff($shop_languages, $this->supported_languages) as $iso) {
            if (!in_array($this->compatibleISO($iso), $this->supported_languages)) {
                $not_supported_languages[] = $iso;
            }
        }
        return $not_supported_languages;
    }

    public function supportsModel($from, $to)
    {
        return in_array($this->compatibleISO($from), $this->supported_languages)
            && in_array($this->compatibleISO($to), $this->supported_languages);
    }

    public function compatibleISO($iso_code)
    {
        return isset($this->iso_substitutions[$iso_code]) ? $this->iso_substitutions[$iso_code] : $iso_code;
    }

    public function translate($content, $from, $to)
    {
        $content = $this->prepareContentForTranslation($content);
        $from = $this->compatibleISO($from);
        $to = $this->compatibleISO($to);
        foreach ($content['to_translate'] as $request_num => $to_translate) {
            $content['translated'][$request_num] = $this->getTranslation($to_translate, $from, $to);
        }
        if (!$this->errors) {
            return $this->prepareTranslationResult($content);
        }
    }

    public function prepareContentForTranslation($content)
    {
        $request_num = 0;
        $content = array(
            'is_string' => !is_array($content),
            'original' => is_array($content) ? $content : array($content),
            'to_translate' => array($request_num => array()),
            'to_translate_keys' => array($request_num => array()),
            'translated' => array(),
            'formatted_translation' => array(),
            'not_processed' => array(),
        );
        foreach ($content['original'] as $name => $value) {
            $split_value = $this->splitString($value);
            if (!is_array($split_value)) {
                unset($content['original'][$name]);
                $content['not_processed'][] = $name;
            } else {
                foreach ($split_value as $v) {
                    $this->processed_chars_num += $this->strlen($v);
                    if ($this->processed_chars_num > $this->request_max_length) {
                        $request_num++;
                    }
                    $content['to_translate'][$request_num][] = $v;
                    $content['to_translate_keys'][$request_num][] = $name;
                }
            }
        }
        return $content;
    }

    public function prepareTranslationResult($content)
    {
        foreach ($content['translated'] as $request_num => $translated_values) {
            foreach ($translated_values as $key => $value) {
                if (!isset($content['to_translate_keys'][$request_num][$key])) {
                    $this->errors[] = 'error_not_matching_keys';
                    break 2;
                }
                $name = $content['to_translate_keys'][$request_num][$key];
                $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                if (!isset($content['formatted_translation'][$name])) {
                    $content['formatted_translation'][$name] = trim($value);
                } else {
                    $content['formatted_translation'][$name] .= ' '.trim($value);
                }
            }
        }
        if (count($content['original']) != count($content['formatted_translation'])) {
            $this->errors[] = 'error';
        } else {
            if ($content['is_string']) {
                $content['formatted_translation'] = current($content['formatted_translation']);
            }
            return array(
                'result' => $content['formatted_translation'],
                'not_processed' => $content['not_processed'],
            );
        }
    }


    public function splitString($string)
    {
        $result = array();
        $iteration = $empty_element_sequence = 0;
        do {
            $iteration++;
            $element = $this->truncateString($string);
            $result[] = $element;
            $string = $this->substr($string, $this->strlen($element));
            if (!$element) {
                $empty_element_sequence++;
            } else {
                $empty_element_sequence = 0;
            }
            if ($empty_element_sequence > 3 || $iteration > 1000) {
                $result = false;
                break;
            }
        } while ($string);
        return $result;
    }

    public function truncateString($string)
    {
        if ($this->strlen($string) > $this->element_max_length) {
            $string = rtrim($this->substr($string, 0, $this->element_max_length), '<');
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $matches, PREG_SET_ORDER);
            $last_closed_html_tag = $cut_tag_position = '';
            foreach (array_reverse($matches) as $m) {
                if (!$last_closed_html_tag && $m[2] && $m[1]) {
                    $last_closed_html_tag = $m[1];
                }
                if (!$cut_tag_position && $m[0] && !$m[2]) { // cut tag
                    $cut_tag_position = Tools::strrpos($string, '<'.$m[0]);
                    $string = $this->substr($string, 0, $cut_tag_position);
                }
            }
            $possible_endings =  array('. ', '! ', '? ', ': ', ', ', '; ');
            if ($last_closed_html_tag) {
                $possible_endings[] = $last_closed_html_tag;
            }
            $closest_to_end = 0;
            foreach ($possible_endings as $e) {
                $from = Tools::strrpos($string, $e);
                if ($from !== false) {
                    $to = $from + $this->strlen($e);
                    if ($to > $closest_to_end) {
                        $closest_to_end = $to;
                    }
                }
            }
            if (!$closest_to_end) {
                // edge case: if no splitting position was found, split by space
                $closest_to_end = Tools::strrpos($string, ' ');
            }
            if ($closest_to_end) {
                $string = $this->substr($string, 0, $closest_to_end);
            }
        }
        return $string;
    }

    public function curlRequest($data, $decode_response = true)
    {
        $response = $decode_response ? array() : '';
        if (function_exists('curl_init')) {
            $session = curl_init();
            if (!empty($data['get_fields'])) {
                if (is_array($data['get_fields'])) {
                    $data['get_fields'] = http_build_query($data['get_fields']);
                }
                $data['url'] .= '?'.$data['get_fields'];
            }
            curl_setopt($session, CURLOPT_URL, $data['url']);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
            if (!empty($data['headers'])) {
                curl_setopt($session, CURLOPT_HTTPHEADER, $data['headers']);
            }
            if (!empty($data['post_fields'])) {
                if (is_array($data['post_fields'])) {
                    $data['post_fields'] = Tools::jsonEncode($data['post_fields']);
                }
                curl_setopt($session, CURLOPT_POSTFIELDS, $data['post_fields']);
            }
            if (!empty($data['login:password'])) {
                curl_setopt($session, CURLOPT_USERPWD, $data['login:password']);
            }
            $response = curl_exec($session);
            $possible_error = curl_error($session);
            curl_close($session);
            if ($possible_error) {
                $this->errors[] = $possible_error;
            } else {
                $response = $decode_response ? Tools::jsonDecode($response, true) : $response;
            }
        } else {
            $this->errors[] = 'no_curl';
        }
        return $response;
    }

    /*
    *  not using Tools::strlen, because that function applies html_entity_decode
    */
    public function strlen($str, $encoding = 'UTF-8')
    {
        return function_exists('mb_strlen') ? mb_strlen($str, $encoding) : strlen($str);
    }

    /*
    * Tools::substr uses Tools::strlen
    */
    public function substr($str, $start, $length = false, $encoding = 'UTF-8')
    {
        $length = $length === false ? $this->strlen($str) : $length;
        if (function_exists('mb_substr')) {
            return mb_substr($str, (int)$start, (int)$length, $encoding);
        }
        return substr($str, (int)$start, (int)$length, $encoding);
    }
}
