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

class EtsAbancartReminder extends ObjectModel
{
    //general.
    public $id_ets_abancart_reminder;
    public $id_ets_abancart_campaign;
    public $date_add;
    // Status:
    public $enabled;
    public $deleted;
    //frequency.
    public $day;
    public $hour;
    public $minute;
    public $second;
    public $redisplay;
    public $delay_popup_based_on;
    //discount.
    public $free_shipping;
    public $discount_option;
    public $discount_code;
    public $apply_discount;
    public $discount_name;
    public $reduction_percent;
    public $reduction_amount;
    public $id_currency;
    public $reduction_tax;
    public $apply_discount_in;
    public $enable_count_down_clock;
    public $allow_multi_discount;

    public $quantity;
    public $quantity_per_user;
    public $reduction_product;
    public $selected_product;
    public $reduction_exclude_special;
    public $gift_product;
    public $gift_product_attribute;
    public $send_repeat_email;
    //public $send_email_now;
    public $schedule_time;
    //template.
    //email
    public $id_ets_abancart_email_template;
    public $title;
    public $content;
    //bar
    public $text_color;
    public $background_color;
    //browser.
    public $icon_notify;

    //Popup
    public $header_bg;
    public $header_text_color;
    public $header_height;
    public $header_font_size;
    public $popup_width;
    public $border_radius;
    public $popup_body_bg;
    public $border_width;
    public $border_color;
    public $font_size;
    public $close_btn_color;
    public $padding;
    public $popup_height;
    public $vertical_align;
    public $overlay_bg;
    public $overlay_bg_opacity;

    const CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION = 1;//afterUpdateCustomer
    const CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION = 2;// afterOrder
    const CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME = 3;
    const CUSTOMER_EMAIL_SEND_RUN_NOW = 4;
    const CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER = 5;//afterUpdateCustomer
    const CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN = 6;

    const REMINDER_STATUS_DRAFT = 0;
    const REMINDER_STATUS_RUNNING = 1;// Pending| Running
    const REMINDER_STATUS_STOP = 2;
    const REMINDER_STATUS_FINISHED = 3;
    const DELAY_PAGE_LOAD = 0;
    const DELAY_CART_CREATION_TIME = 1;

    public static $definition = array(
        'table' => 'ets_abancart_reminder',
        'primary' => 'id_ets_abancart_reminder',
        'multilang' => true,
        'fields' => array(
            //general.
            'id_ets_abancart_campaign' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'enabled' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'deleted' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            //frequency.
            'day' => array('type' => self::TYPE_STRING),
            'hour' => array('type' => self::TYPE_STRING),
            'minute' => array('type' => self::TYPE_STRING),
            'second' => array('type' => self::TYPE_STRING),
            'redisplay' => array('type' => self::TYPE_STRING),
            'delay_popup_based_on' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            //discount.
            'free_shipping' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'discount_option' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'discount_code' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'apply_discount' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'reduction_percent' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'reduction_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reduction_tax' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'apply_discount_in' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'enable_count_down_clock' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            //Add
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'quantity_per_user' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'reduction_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'selected_product' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'reduction_exclude_special' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'gift_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'gift_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'send_repeat_email' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            //'send_email_now' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'schedule_time' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true),
            'allow_multi_discount' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            //email
            'id_ets_abancart_email_template' => array('type' => self::TYPE_INT),
            //bar.
            'text_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32),
            'background_color' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32),
            //browser.
            'icon_notify' => array('type' => self::TYPE_STRING),
            'header_bg' => array('type' => self::TYPE_STRING),
            'popup_width' => array('type' => self::TYPE_INT),
            'popup_height' => array('type' => self::TYPE_INT),
            'border_radius' => array('type' => self::TYPE_INT),
            'border_width' => array('type' => self::TYPE_INT),
            'border_color' => array('type' => self::TYPE_STRING),
            'popup_body_bg' => array('type' => self::TYPE_STRING),
            'header_text_color' => array('type' => self::TYPE_STRING),
            'header_height' => array('type' => self::TYPE_INT),
            'header_font_size' => array('type' => self::TYPE_INT),
            'font_size' => array('type' => self::TYPE_INT),
            'close_btn_color' => array('type' => self::TYPE_STRING),
            'padding' => array('type' => self::TYPE_INT),
            'vertical_align' => array('type' => self::TYPE_STRING),
            'overlay_bg' => array('type' => self::TYPE_STRING),
            'overlay_bg_opacity' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),

            // Lang fields
            'title' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
            'discount_name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 254),
        )
    );

    public static function setNoVoucher($id_ets_abancart_campaign)
    {
        if (!$id_ets_abancart_campaign || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;

        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ets_abancart_reminder` SET `discount_option`=\'no\' 
            WHERE `discount_option`=\'auto\' AND `id_ets_abancart_campaign`=' . (int)$id_ets_abancart_campaign . ' 
        ');
    }

    public static function getReminders($id_ets_abancart_campaign, $context = null)
    {
        if ($id_ets_abancart_campaign < 1 || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;
        if ($context == null)
            $context = Context::getContext();
        $dq = new DbQuery();
        $dq
            ->select('ar.*, arl.title')
            ->from('ets_abancart_reminder', 'ar')
            ->leftJoin('ets_abancart_reminder_lang', 'arl', 'ar.id_ets_abancart_reminder=arl.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_campaign', 'ac', 'ac.id_ets_abancart_campaign=ar.id_ets_abancart_campaign')
            ->where('arl.id_lang=' . (int)$context->language->id)
            ->where('ac.id_shop=' . (int)$context->shop->id)
            ->where('ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign);

        return Db::getInstance()->executeS($dq);
    }

    public function delete()
    {
        $this->deleted = 1;
        return $this->update();
    }

    public function update($null_values = false)
    {
        if (!$null_values)
            $null_values = true;

        if ($this->schedule_time == '0000-00-00') {
            $this->schedule_time = null;
        }

        $keep_old_status = $this->deleted ? -1 : self::getStatusById($this->id);

        if ($res = parent::update($null_values)) {
            if ($this->deleted || $this->enabled == self::REMINDER_STATUS_STOP) {
                $res &= EtsAbancartIndex::deleteIndex($this->id_ets_abancart_campaign, $this->id);
                $res &= EtsAbancartIndexCustomer::deleteIndex($this->id_ets_abancart_campaign, $this->id);
            } else
                $this->addCustomerIndexNow($keep_old_status);
        }

        return $res;
    }

    public static function getStatusById($id_ets_abancart_reminder)
    {
        if (!$id_ets_abancart_reminder || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;
        return (int)Db::getInstance()->getValue('SELECT `enabled` FROM `' . _DB_PREFIX_ . 'ets_abancart_reminder` WHERE `id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder);
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (!$null_values)
            $null_values = true;
        if ($exec = parent::add($auto_date, $null_values)) {
            $exec &= $this->addCustomerIndexNow(self::REMINDER_STATUS_DRAFT);
        }

        return $exec;
    }

    public function addCustomerIndexNow($old_status = null)
    {
        $campaign = new EtsAbancartCampaign($this->id_ets_abancart_campaign);
        if ($campaign->email_timing_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW && (int)$this->enabled == self::REMINDER_STATUS_FINISHED && $old_status == self::REMINDER_STATUS_DRAFT) {
            $customers = Customer::getCustomers(true);
            foreach ($customers as $customer) {
                if (!EtsAbancartUnsubscribers::isUnsubscribe((int)$customer['id_customer'])) {
                    return EtsAbancartIndexCustomer::addCustomerIndex(new Customer((int)$customer['id_customer'])
                        , $this->id_ets_abancart_campaign
                        , false
                        , false
                        , false
                        , false
                        , false
                        , $this->id
                        , true
                    );
                }
            }
        }

        return true;
    }

    /**
     * @param $campaign_ids 1,2,3..
     * @param Context $context
     * @return string
     */
    public static function getSQLReminders($campaign_ids, $context = null, $exclude_cookie = true)
    {
        if (!is_array($campaign_ids) || !count($campaign_ids) || !Validate::isArrayWithIds($campaign_ids))
            return false;

        if ($context == null)
            $context = Context::getContext();

        $excludeIdsAll = [];
        if ($exclude_cookie) {
            $abandonedCookies = isset($context->cookie->ets_abancart_reminders) ? json_decode($context->cookie->ets_abancart_reminders, true) : [];
            $campaigns = [EtsAbancartCampaign::CAMPAIGN_TYPE_POPUP, EtsAbancartCampaign::CAMPAIGN_TYPE_BAR, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER];
            if ($campaigns) {
                foreach ($campaigns as $campaign) {
                    if (isset($abandonedCookies[$campaign]) && $abandonedCookies[$campaign])
                        foreach ($abandonedCookies[$campaign] as $reminder)
                            $excludeIdsAll[] = (int)$reminder['id_ets_abancart_reminder'];
                }
            }
        }

        $productInCart = count($context->cart->getProducts()) > 0;

        $query = '
            SELECT ar.id_ets_abancart_reminder 
            , ((86400*IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0) + 60*IFNULL(ar.minute, 0) + IFNULL(ar.second, 0)) - IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ' AND ar.delay_popup_based_on = 1 AND ' . (int)$productInCart . ', ' . (int)(time() - strtotime($context->cart->date_add)) . ', 0)) as `lifetime`
            , ac.campaign_type
            FROM `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign
            WHERE ar.enabled=1 AND ar.deleted=0
                AND ac.enabled=1 AND ac.deleted=0
                AND ar.id_ets_abancart_campaign IN (' . implode(',', $campaign_ids) . ')
                AND IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ', ' . (int)$productInCart . ', 1)
                ' . ($excludeIdsAll ? ' AND ar.id_ets_abancart_reminder NOT IN (' . implode(',', $excludeIdsAll) . ')' : '') . '
        ';
        return Db::getInstance()->executeS($query);
    }

    public static function getLifeTime($id_ets_abancart_reminder, $id_ets_abancart_campaign = 0, $context = null)
    {
        if (!$id_ets_abancart_reminder || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return 0;

        if ($context == null)
            $context = Context::getContext();

        if ($id_ets_abancart_campaign <= 0) {
            $reminder = new EtsAbancartReminder($id_ets_abancart_reminder);
            $id_ets_abancart_campaign = $reminder->id_ets_abancart_campaign;
        }

        $productInCart = count($context->cart->getProducts()) > 0;
        $query = '
            SELECT ((86400*IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0) + 60*IFNULL(ar.minute, 0) + IFNULL(ar.second, 0)) - IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ' AND ar.delay_popup_based_on = 1 AND ' . (int)$productInCart . ', ' . (int)(time() - strtotime($context->cart->date_add)) . ', 0))
            FROM `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign
            WHERE ar.enabled=1 AND ar.deleted=0 AND ac.enabled=1 AND ac.deleted=0
                AND ar.id_ets_abancart_reminder = ' . (int)$id_ets_abancart_reminder . '
                AND ac.id_ets_abancart_campaign = ' . (int)$id_ets_abancart_campaign . '
                AND IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ', ' . (int)$productInCart . ', 1)
        ';
        return Db::getInstance()->getValue($query);
    }

    public static function campaignValid($type, $email_timing_option = 0)
    {
        if (trim($type) == '' || !Validate::isCleanHtml($type))
            return false;

        return (int)Db::getInstance()->getValue('
			SELECT COUNT(ar.id_ets_abancart_reminder)
            FROM `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
			WHERE ac.id_ets_abancart_campaign is NOT NULL' . ($email_timing_option > 0 ? ' AND ac.email_timing_option=' . (int)$email_timing_option : '') . '
			    AND ac.campaign_type = \'' . pSQL($type) . '\'
			    AND ac.enabled = 1
			    AND ac.deleted = 0
			    AND ar.enabled = 1
			    AND ar.deleted = 0
		') > 0 ? 1 : 0;
    }

    public static function getTotalReminder($id_campaign = 0)
    {
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) as total_reminder FROM `" . _DB_PREFIX_ . "ets_abancart_reminder` WHERE 1 " . ($id_campaign ? " AND id_ets_abancart_campaign=" . (int)$id_campaign : ""));
    }

    public static function getNextMailTime($id_cart, $fieldValue = true, $idLang = 0)
    {
        if ($idLang == 0) {
            $idLang = Context::getContext()->language->id;
        }
        $dq = new DbQuery();
        $dq
            ->select('FROM_UNIXTIME(UNIX_TIMESTAMP(ai.cart_date_add) + 86400 * IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0), \'%Y-%m-%d %H:%i:%s\') `next_mail_time`')
            ->from('ets_abancart_index', 'ai')
            ->leftJoin('ets_abancart_reminder', 'ar', 'ai.id_ets_abancart_reminder = ar.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_campaign', 'ac', 'ac.id_ets_abancart_campaign = ai.id_ets_abancart_campaign')
            ->where('ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->where('ar.id_ets_abancart_reminder > 0')
            ->where('ai.id_cart=' . (int)$id_cart)
            ->orderBy('`next_mail_time` ASC');
        if (!$fieldValue) {
            $dq
                ->select('ar.id_ets_abancart_reminder')
                ->select('arl.title `reminder_name`')
                ->select('ac.campaign_type')
                ->leftJoin('ets_abancart_reminder_lang', 'arl', 'arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$idLang)
                ->where('ai.id_cart');

            return Db::getInstance()->executeS($dq);
        }
        return Db::getInstance()->getValue($dq);
    }

    public static function isInvalid($id_ets_abancart_reminder)
    {
        if (!$id_ets_abancart_reminder || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return true;
        $dq = new DbQuery();
        $dq
            ->select('id_ets_abancart_reminder')
            ->from('ets_abancart_reminder', 'ar')
            ->innerJoin('ets_abancart_campaign', 'ac', 'ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign')
            ->where('ar.id_ets_abancart_reminder=' . (int)$id_ets_abancart_reminder)
            ->where('ac.enabled = 0 OR ac.deleted = 1 OR ar.enabled != 1 OR ar.deleted = 1');
        return (bool)Db::getInstance()->getValue($dq);
    }

    public static function hasVoucherInReminder($id_ets_abancart_campaign, $id_ets_abancart_reminder = 0)
    {
        if (!$id_ets_abancart_campaign || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;

        $query = '
            SELECT COUNT(ar.`id_ets_abancart_reminder`)
            FROM `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (ar.`id_ets_abancart_reminder` = arl.`id_ets_abancart_reminder`)
            WHERE ar.`id_ets_abancart_campaign` = ' . (int)$id_ets_abancart_campaign . '
                AND ar.`discount_option` != \'fixed\'
                AND arl.`content` REGEXP \'\[discount_(code|from|to)|reduction|money_saved|total_payment_after_discount|button_add_discount|show_discount_box|discount_count_down_clock\]\'
                ' . ($id_ets_abancart_reminder > 0 ? ' AND `ar.id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder : '') . '
        ';

        return (int)Db::getInstance()->getValue($query);
    }
}