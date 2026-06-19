<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

include_once dirname(__FILE__).'../../../arcontactus.php';
include_once dirname(__FILE__).'../../../classes/ArContactUsTwilio.php';
include_once dirname(__FILE__).'../../../classes/ArContactUsTelegram.php';
include_once dirname(__FILE__).'/../../sdk/phpqrcode/qrlib.php';
include_once dirname(__FILE__).'../../../classes/ArContactUsTools.php';

/**
 * @property ArContactUs $module
 */
class ArContactUsAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $errors = array();
    protected $json;


    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        if (!$this->isAjax()) {
            return Tools::redirect('/');
        }
        $phone = Tools::getValue('phone');
        $phone = trim($phone);
        $action = Tools::getValue('action');
        
        if ($action == 'getQRCode') {
            $data = Tools::getValue('data');
            $path = $this->module->getUploadPath();
            $filename = md5($data) . '.png';
            QRcode::png($data, $path . $filename, QR_ECLEVEL_L, 10);
            die(ArContactUsTools::jsonEncode(array(
                'qrcodeFile' => $this->module->getUploadsUrl() . $filename
            )));
        }
        
        if ($this->isValid() && $phone) {
            $name = Tools::getValue('name');
            $mail = Tools::getValue('email');
            $referer = $_SERVER['HTTP_REFERER'];
            if ($name) {
                $name = strip_tags($name);
            }
            if ($mail && $this->module->getCallbackConfigModel()->email_field) {
                $mail = $this->filterEmail($mail);
            }
            
            $model = ArContactUsCallback::addCallback(Context::getContext()->customer->id, $phone, $name, $referer, $mail);
            $pushRes = null;
            $emailSend = null;
            if ($this->module->isOnesignalInstalled() && $this->module->getCallbackConfigModel()->onesignal) {
                $pushRes = $this->module->sendPush($phone, $name, $referer, $mail);
            }
            if ($this->module->getCallbackConfigModel()->email) {
                $emailSend = $this->module->sendEmail($phone, $name, $referer, $mail);
            }
            $twilio = $this->sendTwilio($phone, $name, $referer, $mail);
            $tg = $this->sendTelegram($phone, $name, $referer, $mail);
            die(ArContactUsTools::jsonEncode(array(
                'success' => 1,
                'model' => AR_CONTACTUS_DEBUG? $model : null,
                'push' => AR_CONTACTUS_DEBUG? $pushRes : null,
                'email' => AR_CONTACTUS_DEBUG? $emailSend : null,
                'twilio' => AR_CONTACTUS_DEBUG? $twilio : null,
                'tg' => AR_CONTACTUS_DEBUG? $tg : null,
                'reCaptcha' => AR_CONTACTUS_DEBUG? $this->json : null
            )));
        } elseif (Tools::isEmpty($phone)) {
            $this->errors[] = $this->module->l('Please fill phone field');
        }
        
        die(ArContactUsTools::jsonEncode(array(
            'success' => 0,
            'errors' => $this->errors,
            'reCaptcha' => AR_CONTACTUS_DEBUG? $this->json : null
        )));
    }
    
    protected function sendTelegram($phone, $name, $referer, $mail)
    {
        if (!$this->module->getCallbackConfigModel()->tg_chat_id ||
                !$this->module->getCallbackConfigModel()->tg_token ||
                !$this->module->getCallbackConfigModel()->tg_text ||
                !$this->module->getCallbackConfigModel()->tg) {
            return false;
        }
        $return = array();
        if (strpos($this->module->getCallbackConfigModel()->tg_chat_id, ',') !== false) {
            $chatIds = explode(',', $this->module->getCallbackConfigModel()->tg_chat_id);
            foreach ($chatIds as $chatId) {
                $return[] = $this->sendTelegramMessage($phone, $name, $referer, $mail, $chatId);
            }
            return $return;
        }
        $this->sendTelegramMessage($phone, $name, $referer, $mail, $this->module->getCallbackConfigModel()->tg_chat_id);
    }
    
    protected function sendTelegramMessage($phone, $name, $referer, $mail, $chatId)
    {
        $telegram = new ArContactUsTelegram($this->module->getCallbackConfigModel()->tg_token, trim($chatId));
        $id_lang = Context::getContext()->language->id;
        $message = strtr($this->module->getCallbackConfigModel()->tg_text[$id_lang], array(
            '{phone}' => $phone,
            '{name}' => $name,
            '{referer}' => $referer,
            '{email}' => $mail,
            '{site}' => $this->module->getBaseURL(),
        ));
        return $telegram->send($message);
    }
    
    protected function sendTwilio($phone, $name, $referer, $mail)
    {
        if (!$this->module->getCallbackConfigModel()->twilio ||
                !$this->module->getCallbackConfigModel()->twilio_api_key ||
                !$this->module->getCallbackConfigModel()->twilio_auth_token ||
                !$this->module->getCallbackConfigModel()->twilio_message ||
                !$this->module->getCallbackConfigModel()->twilio_phone ||
                !$this->module->getCallbackConfigModel()->twilio_tophone
            ) {
            return false;
        }
        $twilio = new ArContactUsTwilio($this->module->getCallbackConfigModel()->twilio_api_key, $this->module->getCallbackConfigModel()->twilio_auth_token);
        $fromPhone = $this->module->getCallbackConfigModel()->twilio_phone;
        $toPhone = $this->module->getCallbackConfigModel()->twilio_tophone;
        $message = strtr($this->module->getCallbackConfigModel()->twilio_message, array(
            '{site}' => $this->module->getBaseURL(),
            '{phone}' => $phone,
            '{name}' => $name,
            '{email}' => $mail,
            '{referer}' => $referer,
        ));
        
        $res = $twilio->sendSMS($message, $fromPhone, $toPhone);
        return $res;
    }


    protected function isValid()
    {
        $action = Tools::getValue('action');
        $key = Tools::getValue('key');
        
        if ($this->module->getCallbackConfigModel()->email_field) {
            $email = $this->filterEmail(Tools::getValue('email'));
            $emailValid = $this->isValidEmail($email);
            if (!$emailValid) {
                $this->errors[] = sprintf($this->module->l('Entered value "%s" is not a valid email address'), $email);
            }
        } else {
            $emailValid = true;
        }
        
        if ($this->module->getCallbackConfigModel()->name) {
            $name = $this->filterName(Tools::getValue('name'));
            $nameValid = $this->isNameValid($name);
        } else {
            $nameValid = true;
        }
        
        return $action == 'callback' && $this->isValidKey($key) && $nameValid && $emailValid && $this->isValidRecaptcha();
    }

    protected function isValidKey($key)
    {
        if ($key == Configuration::get('arcukey')) {
            return true;
        }
        $this->errors[] = $this->module->l('Invalid security token. Please refresh the page.');
        return false;
    }
    
    public function filterEmail($email)
    {
        $email = trim($email);
        return strip_tags($email);
    }
    
    public function filterName($name)
    {
        return trim($name);
    }
    
    public function isNameValid($name)
    {
        if (!$this->module->getCallbackConfigModel()->name_validation) {
            return true;
        }
        if ($this->module->getCallbackConfigModel()->name_max_len) {
            $len = mb_strlen($name, 'utf-8');
            if ($len > $this->module->getCallbackConfigModel()->name_max_len) {
                $this->errors[] = sprintf($this->module->l('Entered value "%s" is longer then %s symbols'), $name, $this->module->getCallbackConfigModel()->name_max_len);
                return false;
            }
        }
        if ($this->module->getCallbackConfigModel()->name_filter_laters) {
            if (!preg_match('/^[\p{Latin}\p{Cyrillic}\p{Armenian}\p{Hebrew}\p{Arabic}\p{Syriac}\p{Thaana}\p{Devanagari}\p{Bengali}\p{Gurmukhi}\p{Gujarati}\p{Oriya}\p{Tamil}\p{Telugu}\p{Kannada}\p{Malayalam}\p{Sinhala}\p{Thai}\p{Lao}\p{Tibetan}\p{Myanmar}\p{Georgian}\p{Ethiopic}\p{Cherokee}\p{Ogham}\p{Runic}\p{Tagalog}\p{Hanunoo}\p{Buhid}\p{Tagbanwa}\p{Khmer}\p{Mongolian}\p{Limbu}\p{Tai_Le}\p{Hiragana}\p{Katakana}\p{Bopomofo}\s0-9A-Za-zА-Яа-я]+$/iu', $name)) {
                $this->errors[] = sprintf($this->module->l('Entered value %s is not correct. Please use leters and numbers only'), $name);
                return false;
            }
        }
        
        return true;
    }
    
    public function isValidEmail($email)
    {
        if ($this->module->getCallbackConfigModel()->email_required && empty($email)) {
            return false;
        } elseif (!$this->module->getCallbackConfigModel()->email_required && empty($email)) {
            return true;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }


    protected function isValidRecaptcha()
    {
        if ($this->module->isReCaptchaIntegrated()) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'content' => http_build_query(array(
                        'secret' => $this->module->getCallbackConfigModel()->secret,
                        'response' => Tools::getValue('gtoken')
                    ))
                ),
            ));
            $data = Tools::file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $json = ArContactUsTools::jsonDecode($data, true);
            $this->json = $json;
            if (isset($json['success']) && $json['success']) {
                if (isset($json['score']) && ($json['score'] < 0.3)) {
                    $this->errors[] = $this->module->l('Bot activity detected!');
                    return false;
                }
            } else {
                $this->addReCaptchaErrors($json['error-codes']);
                return false;
            }
        }
        return true;
    }
    
    protected function addReCaptchaErrors($errors)
    {
        $reCaptchaErrors = $this->module->getReCaptchaErrors();
        if ($errors) {
            foreach ($errors as $error) {
                if (isset($reCaptchaErrors[$error])) {
                    $this->errors[] = $reCaptchaErrors[$error];
                } else {
                    $this->errors[] = $error;
                }
            }
        }
    }
    
    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return  false;
    }

    public function getTemplateVarProduct()
    {
    }
}
