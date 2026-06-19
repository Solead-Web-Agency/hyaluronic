<?php
/**
 * 2007-2022 ETS-Soft
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
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class EtsAbancartEmailTemplate extends ObjectModel
{
    public $id_ets_abancart_email_template;
    public $id_shop;
    public $thumbnail;
    public $template_type;
    public $type_of_campaign;
    public $is_init;
    public $name;
    public $folder_name;
    public $temp_path;
    public static $definition = array(
        'table' => 'ets_abancart_email_template',
        'primary' => 'id_ets_abancart_email_template',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'thumbnail' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'template_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'is_init' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'type_of_campaign' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'folder_name' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            // Lang fields
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true),
            'temp_path' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
        )
    );

    public function delete()
    {
        if ($this->deleteTemplateFiles()) {
            return parent::delete();
        }
        return false;
    }

    public static function getTemplates($id = null, $template_type = null, $type_of_campaign = null, Context $context = null)
    {
        if (!$context)
            $context = Context::getContext();
        $dq = new DbQuery();
        $dq
            ->select('a.*,b.*')
            ->from('ets_abancart_email_template', 'a')
            ->leftJoin('ets_abancart_email_template_lang', 'b', 'a.id_ets_abancart_email_template=b.id_ets_abancart_email_template AND b.id_lang=' . (int)$context->language->id)
            ->where('a.id_shop=' . (int)$context->shop->id);
        $valueOnly = $id && Validate::isUnsignedInt($id);
        if ($valueOnly) {
            $dq
                ->where('a.id_ets_abancart_email_template=' . (int)$id);
        }
        if ($template_type && Validate::isCleanHtml($template_type)) {
            $dq
                ->where('(a.template_type=\'' . pSQL($template_type) . '\' OR template_type=\'both\')');
        }
        if ($type_of_campaign && Validate::isCleanHtml($type_of_campaign)) {
            $dq
                ->where('a.type_of_campaign=\'' . pSQL($type_of_campaign) . '\'');
        }
        if ($valueOnly) {
            return Db::getInstance()->getValue($dq);
        }
        $templates = Db::getInstance()->executeS($dq);
        if ($templates) {
            foreach ($templates as &$template) {
                if ($template['thumbnail'])
                    $template['thumbnail_url'] = $context->shop->getBaseURL() . 'img/ets_abandonedcart/mails/' . $template['folder_name'] . '/' . $template['thumbnail'];
                else
                    $template['thumbnail_url'] = '';
            }
        }

        return $templates;
    }

    public static function formatEmailTemplate($content)
    {
        $content = str_replace('{shop_name}', Context::getContext()->shop->name, $content);
        $content = str_replace('{shop_url}', Context::getContext()->shop->getBaseURL(true), $content);
        $logo = '';
        if (false !== Configuration::get('PS_LOGO_MAIL') && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, Context::getContext()->shop->id))) {
            $logo = _PS_IMG_ . Configuration::get('PS_LOGO_MAIL', null, null, Context::getContext()->shop->id);
        } elseif (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, Context::getContext()->shop->id))) {
            $logo = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, Context::getContext()->shop->id);
        }
        $content = str_replace('{shop_logo}', $logo, $content);
        return $content;
    }

    public static function isTemplateNameExists($name)
    {
        return Db::getInstance()->getValue("SELECT folder_name FROM `" . _DB_PREFIX_ . "ets_abancart_email_template` WHERE folder_name='" . pSQL($name) . "' OR id_ets_abancart_email_template=" . (int)$name) ? true : false;
    }

    public static function addNewLanguage($language)
    {
        if (!$language instanceof Language || $language->id <= 0)
            return false;

        $res = Db::getInstance()->executeS('
            SELECT et.*, etl.temp_path 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_email_template` et
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_email_template_lang` etl ON(et.id_ets_abancart_email_template  = etl.id_ets_abancart_email_template AND etl.id_lang=' . (int)$language->id . ') 
            WHERE etl.id_ets_abancart_email_template is NOT NULL
        ');

        if ($res) {
            $query = 'UPDATE `' . _DB_PREFIX_ . 'ets_abancart_email_template_lang` SET `temp_path`="index_' . pSQL($language->iso_code) . '.html" WHERE `id_lang`=' . (int)$language->id . ' AND `id_ets_abancart_email_template`=';
            $queries = [];
            foreach ($res as $row) {

                if (trim($row['folder_name']) == '' ||
                    trim($row['temp_path']) == ''
                ) {
                    continue;
                }

                if (file_exists(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $row['folder_name'] . '/' . $row['temp_path'])
                    && !preg_match('/^index_' . $language->iso_code . '\.html$/', $row['temp_path'])
                    && !file_exists(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $row['folder_name'] . '/index_' . $language->iso_code . '.html')
                ) {
                    @copy(
                        _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $row['folder_name'] . '/' . $row['temp_path']
                        , _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $row['folder_name'] . '/' . preg_replace('/^(index_)([a-z]+?)(\.html)$/', '$1' . $language->iso_code . '$3', $row['temp_path'])
                    );
                }

                $queries[] = $query . (int)$row['id_ets_abancart_email_template'];
            }
            if ($queries)
                return Db::getInstance()->execute(implode(';', $queries));
        }

        return true;
    }

    public function updateContentToSave()
    {
        if ($this->id) {
            $folderName = $this->folder_name ?: $this->id;
            $mailPath = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName;
            $languages = Language::getLanguages(false);
            $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
            $temDirDefault = isset($this->temp_path[$idLangDefault]) ? $mailPath . '/' . $this->temp_path[$idLangDefault] : '';
            $contentDefaultRequest = Tools::getValue('email_content_' . $idLangDefault);
            $contentDefault = $contentDefaultRequest ? self::formatContentBeforeSave($contentDefaultRequest, $this) : '';
            $cache = $mailPath . '/email_editing.ets';
            $commentCode = $mailPath . '/key_editing.json';
            $contentCache = file_exists($cache) ? Tools::file_get_contents($cache) : '';
            $contentCommentCode = file_exists($commentCode) ? Tools::file_get_contents($commentCode) : '';
            $commentCodeJson = $contentCommentCode ? Tools::jsonDecode($contentCommentCode, true) : array();
            foreach ($languages as $lang) {

                if (isset($this->temp_path[$lang['id_lang']]) && $this->temp_path[$lang['id_lang']]) {

                    $templateName = preg_replace('/^(index_)([a-z]+?)(\.html)$/', '$1' . $lang['iso_code'] . '$3', $this->temp_path[$lang['id_lang']]);
                    $temDir = $mailPath . '/' . $templateName;

                    #Notice: Email content may have  special characters and Validate::cleanHtml can not detect right
                    $emailContent = ($emailContent = Tools::getValue('email_content_' . $lang['id_lang'])) ? self::formatContentBeforeSave($emailContent, $this) : '';
                    if (file_exists($temDir)) {
                        if ($contentCache) {
                            $content = $contentCache;
                        } else {
                            $content = Tools::file_get_contents($temDir);
                            $bodyCode = array();

                            $content = self::replaceCommentCode($content, $bodyCode);
                        }
                        preg_match('/<body[^>]*>(.*?)<\s*\/body>/s', $content, $matches);

                        if ($matches && isset($matches[1])) {
                            $content = preg_replace('/(<body[^>]*>)(.*?)(<\s*\/body>)/s', '$1' . $emailContent . '$3', $content);
                        } else {
                            $content = $emailContent;
                        }
                        $bodyCode = $commentCodeJson ?: (isset($bodyCode) ? $bodyCode : array());
                        foreach ($bodyCode as $kc => $code) {
                            $content = str_replace('<!--%--comment' . $kc . '--%-->', $code, $content);
                        }

                        file_put_contents($temDir, $content);
                    } elseif ($temDirDefault) {
                        if (file_exists($temDirDefault)) {
                            if ($contentCache) {
                                $content = $contentCache;
                            } else {
                                $content = Tools::file_get_contents($temDirDefault);
                                $content = self::formatContentBeforeSave($content, $this);
                                $bodyCode = array();

                                $content = self::replaceCommentCode($content, $bodyCode);
                            }

                            preg_match('/<body[^>]*>(.*?)<\s*\/body>/s', $content, $matches);
                            if ($matches && isset($matches[1])) {
                                $content = preg_replace('/(<body[^>]*>)(.*?)(<\s*\/body>)/s', '$1' . ($emailContent ?: $contentDefault) . '$3', $content);
                            } else {
                                $content = $emailContent ?: $contentDefault;
                            }
                            $bodyCode = $commentCodeJson ?: (isset($bodyCode) ? $bodyCode : array());
                            foreach ($bodyCode as $kc => $code) {
                                $content = str_replace('<!--%--comment' . $kc . '--%-->', $code, $content);
                            }
                            file_put_contents($temDir, $content);
                        } else {
                            file_put_contents($temDir, $emailContent ?: $contentDefault);
                        }
                    }
                }
            }
            if (file_exists($cache)) {
                unlink($cache);
            }
            if (file_exists($commentCode)) {
                unlink($commentCode);
            }
        } else {
            $folderName = 'template_' . time() . rand(1111, 999999);
            $this->folder_name = $folderName;
            mkdir(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName, 0755);
            @copy(dirname(__FILE__) . '/index.php', _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName . '/index.php');
            $languages = Language::getLanguages(false);
            $contentPostDefault = ($contentPostDefault = Tools::getValue('email_content_' . Configuration::get('PS_LANG_DEFAULT'))) && Validate::isCleanHtml($contentPostDefault) ? $contentPostDefault : '';
            foreach ($languages as $lang) {
                $contentPost = ($contentPost = Tools::getValue('email_content_' . $lang['id_lang'])) && Validate::isCleanHtml($contentPost) ? $contentPost : '';
                file_put_contents(_ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName . '/index_' . $lang['iso_code'] . '.html', $contentPost ?: $contentPostDefault);
                $this->temp_path[$lang['id_lang']] = 'index_' . $lang['iso_code'] . '.html';
            }
        }
    }

    public static function getBodyMailTemplate($filePath, EtsAbancartEmailTemplate $object)
    {
        if (is_file($filePath) && file_exists($filePath)) {
            $content = Tools::file_get_contents($filePath);
            $bodyCode = array();
            $content = self::replaceCommentCode($content, $bodyCode);

            file_put_contents(dirname($filePath) . '/email_editing.ets', $content);
            file_put_contents(dirname($filePath) . '/key_editing.json', Tools::jsonEncode($bodyCode));
            preg_match('/<body[^>]*>(.*?)<\/body>/s', $content, $matches);
            if ($matches && isset($matches[1])) {
                return self::formatContentToShow($matches[1], $object);
            }
            return self::formatContentToShow($content, $object);
        }
        return '';
    }

    public static function formatContentToShow($content, EtsAbancartEmailTemplate $object)
    {
        $content = self::addDomainForUrls($content, $object);
        $content = self::hideSpecialCodeInContent($content);
        return $content;
    }

    public static function formatContentBeforeSave($content, EtsAbancartEmailTemplate $object)
    {
        $content = self::removeDomainForImgUrl($content, $object);
        $content = self::showSpecialCodeInContent($content);
        return $content;
    }


    public static function addDomainForUrls($content, EtsAbancartEmailTemplate $object)
    {
        $shopUrl = $object->getBaseUrlForEmailTemplate();
        $content = preg_replace('/(src\s*=\s*")\s*(?!http)([^\/][^"]*?)\s*(")/', '$1' . $shopUrl . '/$2$3', $content);
        //Replace for style in html
        $content = preg_replace('/(background\s*=\s*"\s*)((?!http)([^"]*?)\.(?:jpg|png|gif)\s*)(")/', '$1' . $shopUrl . '/$2$4', $content);
        //Replace for <style>
        $content = preg_replace('/(url\(\s*(["\']?))((?!http)([^"]*?)\.(?:jpg|png|gif))((\2)\s*\))/', '$1' . $shopUrl . '/$3$5', $content);

        return $content;
    }

    public static function hideSpecialCodeInContent($content)
    {
        $content = preg_replace('/(<!\[if[^<>\[\]]+?\]>)/', '<!--{{$1}}-->', $content);
        $content = preg_replace('/(<!\[endif\]>)/', '<!--{{$1}}-->', $content);
        return $content;
    }


    public static function showSpecialCodeInContent($content)
    {
        $content = preg_replace('/<!\-\-\{\{(.*?)\}\}\-\->/', '$1', $content);
        return $content;
    }

    public static function removeDomainForImgUrl($content, EtsAbancartEmailTemplate $object)
    {
        $shopUrl = $object->getBaseUrlForEmailTemplate();
        $content = preg_replace('/(<img.*)(\ssrc=")(' . addcslashes($shopUrl, '/') . ')([^"]*?)("[^<>]*\/>)/', '$1$2$4$5', $content);
        return $content;
    }

    public function deleteTemplateFiles()
    {
        if ($this->id) {
            $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($this->folder_name ?: $this->id);
            if (is_dir($mailDir)) {
                EtsAbancartTools::deleteAllDataInFolder($mailDir);
                return true;
            }
        }
        return false;
    }

    public static function removeLinkInContentMail($filePath)
    {
        if (file_exists($filePath) && is_writable($filePath)) {
            $content = Tools::file_get_contents($filePath);
            $content = preg_replace('/(<a [^>]*)(href=")([^"]+?)(">)/', '$1$2#$4', $content);
            return file_put_contents($filePath, $content) ? true : false;
        }
        return false;
    }

    public static function updateOldEmailTemplate()
    {
        $templates = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "ets_abancart_email_template`");
        $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_;
        foreach ($templates as $template) {
            $tempLangs = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "ets_abancart_email_template_lang` WHERE id_ets_abancart_email_template=" . (int)$template['id_ets_abancart_email_template']);
            $folderName = $template['id_ets_abancart_email_template'];
            if (is_dir($mailDir . '/' . $folderName)) {
                $folderName = 'template_' . time() . rand(111111, 999999);
            }
            mkdir($mailDir . '/' . $folderName, 0755);
            @copy(dirname(__FILE__) . '/index.php', $mailDir . '/' . $folderName . '/index.php');
            @copy(_PS_MODULE_DIR_ . 'ets_abandonedcart/views/img/upload/' . $template['thumbnail'], $mailDir . '/' . $folderName . '/' . $template['thumbnail']);
            if ($tempLangs) {
                foreach ($tempLangs as $tempLang) {
                    @file_put_contents($mailDir . '/' . $folderName . '/index_' . $tempLang['id_lang'] . '.html', $tempLang['email_content']);
                    Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_email_template_lang` SET temp_path='index_" . $tempLang['id_lang'] . ".html' WHERE id_ets_abancart_email_template=" . (int)$template['id_ets_abancart_email_template'] . " AND id_lang=" . (int)$tempLang['id_lang']);
                }
            }
            Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_abancart_email_template` SET folder_name='" . pSQL($folderName) . "' WHERE id_ets_abancart_email_template=" . (int)$template['id_ets_abancart_email_template']);
        }
    }

    public static function createContentEmailToSend($emailContent, $id_reminder, $id_lang)
    {
        $reminder = new EtsAbancartReminder($id_reminder);
        if ($reminder && $reminder->id && $reminder->id_ets_abancart_email_template) {
            $emailTemp = new EtsAbancartEmailTemplate($reminder->id_ets_abancart_email_template);
            if ($emailTemp && $emailTemp->id) {
                $folderName = $emailTemp->folder_name ?: $emailTemp->id;
                $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
                $tempPath = isset($emailTemp->temp_path[$id_lang]) ? $emailTemp->temp_path[$id_lang] : $emailTemp->temp_path[$idLangDefault];
                $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . $folderName . '/' . $tempPath;
                if (file_exists($mailDir)) {
                    $content = Tools::file_get_contents($mailDir);
                    $bodyCode = array();
                    $content = self::replaceCommentCode($content, $bodyCode);
                    preg_match('/<body[^>]*>(.*?)<\s*\/body>/s', $content, $matches);
                    if ($matches && isset($matches[1])) {
                        $content = preg_replace('/(<body[^>]*>)(.*?)(<\s*\/body>)/s', '$1' . $emailContent . '$3', $content);
                    } else {
                        return $emailContent;
                    }
                    foreach ($bodyCode as $kc => $code) {
                        $content = str_replace('<!--%--comment' . $kc . '--%-->', $code, $content);
                    }
                    return $content;
                }
            }
        }
        return $emailContent;
    }

    public function getEmailContent()
    {
        if ($this->id) {
            $context = Context::getContext();
            $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($this->folder_name ?: $this->id) . '/' . $this->temp_path[$context->language->id];
            if (file_exists($mailDir)) {
                return self::getBodyMailTemplate($mailDir, $this);
            }
        }
        return '';
    }

    public static function replaceCommentCode($content, &$commentCode)
    {
        $countCode = 0;
        return preg_replace_callback('/<!-.*?-->/s', function ($data) use (&$commentCode, &$countCode) {
            if ($commentCode || $data) {
                //
            }
            $keyCode = '<!--%--comment' . $countCode . '--%-->';
            //$bodyCode[$countCode] = $data[0];
            $countCode++;
            return $keyCode;
        }, $content);
    }

    public static function getMaxId()
    {
        return (int)Db::getInstance()->getValue("SELECT MAX(id_ets_abancart_email_template) FROM `" . _DB_PREFIX_ . "ets_abancart_email_template`");
    }

    public static function getTemplateFile($dir)
    {
        $globFiles = glob($dir . '/*');
        if (!$globFiles) {
            return array();
        }
        $files = array();
        $allowExts = array('html', 'htm');
        foreach ($globFiles as $item) {
            if (is_dir($item)) {
                $gf2 = glob($item . '/*');
                if ($gf2) {
                    foreach ($gf2 as $i) {
                        if (is_file($i) && file_exists($i)) {
                            $ext = pathinfo($i, PATHINFO_EXTENSION);
                            if (in_array($ext, $allowExts)) {
                                $files[] = $i;
                            }
                        }
                    }
                }
            } else {
                $ext = pathinfo($item, PATHINFO_EXTENSION);
                if (in_array($ext, $allowExts)) {
                    $files[] = $item;
                }
            }
        }
        return $files;
    }

    public function getBaseUrlForEmailTemplate()
    {
        $temPath = is_array($this->temp_path) ? $this->temp_path[Context::getContext()->language->id] : $this->temp_path;
        $fileName = basename($temPath);
        $baseDir = str_replace($fileName, '', $temPath);
        return rtrim(Context::getContext()->shop->getBaseURL(true) . 'img/ets_abandonedcart/mails/' . $this->folder_name . '/' . $baseDir, '/');
    }

    public static function getSubject($id)
    {
        $subjects = [
            '1,5' => [
                'en' => 'Looks like you forgot something, complete your purchase!',
                'es' => 'Parece que olvidaste algo, ¡completar tu compra!',
                'fr' => 'On dirait que vous avez oublié quelque chose, finaliser votre achat !',
                'it' => 'Sembra che tu abbia dimenticato qualcosa, completa l\'acquisto!',
            ],
            '2,3,4' => [
                'en' => 'Empty your cart with 20% off',
                'es' => 'Vacíe su carrito con un 20% de descuento',
                'fr' => 'Videz votre panier avec 20% de réduction',
                'it' => 'Svuota il carrello con il 20% di sconto',
            ],
            '6,9' => [
                'en' => 'Thank you for joining us!',
                'es' => 'Gracias por unirte a nosotros!',
                'fr' => 'Merci de nous avoir rejoint !',
                'it' => 'Grazie per esserti unito a noi!',
            ],
            '7,8,10' => [
                'en' => 'Thank you for joining us! Here a little gift for you',
                'es' => '¡Gracias por estar con nosotros! Aquí un regalito para ti',
                'fr' => 'Merci de nous avoir rejoint ! Voici un petit cadeau pour vous',
                'it' => 'Grazie per esserti unito a noi! Ecco un piccolo regalo per te',
            ],
            '11' => [
                'en' => 'Thank you for your order!',
                'es' => '¡Gracias por su pedido! ',
                'fr' => 'Nous vous remercions de votre commande !',
                'it' => 'Grazie per il vostro ordine!',
            ],
            '12,13' => [
                'en' => 'Congratulations, you\'re now on our email list!',
                'es' => '¡Felicitaciones, ahora estás en nuestra lista de correo electrónico!',
                'fr' => 'Félicitations, vous êtes maintenant sur notre liste de diffusion!',
                'it' => 'Congratulazioni, ora sei nella nostra lista di e-mail!',
            ],
            '14' => [
                'en' => 'This gift is to thank you!',
                'es' => '¡Este regalo es para agradecerte!',
                'fr' => 'Ce cadeau est pour vous remercier !',
                'it' => 'Questo regalo è per ringraziarti!',
            ],
            '15,16' => [
                'en' => 'We miss you so much!',
                'es' => '¡Te extrañamos mucho!',
                'fr' => 'Tu nous manques tellement !',
                'it' => 'Ci manchi tanto!',
            ]
        ];

        foreach ($subjects as $key => $subject) {
            if (in_array($id, explode(',', $key))) {
                return $subject;
            }
        }

        return [];
    }

}