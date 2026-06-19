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

class Ets_abandonedcartRequestModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        $id_reminder = (int)Tools::getValue('id_ets_abancart_reminder');
        if (!$id_reminder && Tools::isSubmit('has_closed')) {
            $id_reminder = (int)Tools::getValue('id');
        }
        $campaign = null;
        if ($id_reminder) {
            $reminder = new EtsAbancartReminder($id_reminder);
            if ($reminder->id) {
                $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);
            }
        }
        $both = false;
        if (Tools::isSubmit('leave')) {
            $needCart = false;
            $hasProductInCart = Configuration::get('ETS_ABANCART_HAS_PRODUCT_IN_CART');
            if ($hasProductInCart == 1) {
                $needCart = true;
            } else if ($hasProductInCart == 2) {
                $both = true;
            }
        } elseif ($id_reminder) {
            $needCart = false;
            if ($campaign && $campaign->has_product_in_cart == 1) {
                $needCart = true;
            }
            if ($campaign && $campaign->has_product_in_cart == 2) {
                $both = true;
            }
        }

        if (isset($needCart) && $needCart && !$both && (!$this->context->cart || !$this->context->cart->id || !Tools::isSubmit('favicon') && !$this->context->cart->getLastProduct())) {
            $this->toJson(array(
                'errors' => $this->module->l('Cart is not found or empty', 'request')
            ));
        } elseif (isset($needCart) && !$needCart && !$both && $this->context->cart && $this->context->cart->getLastProduct()) {
            $this->toJson(array(
                'errors' => $this->module->l('Cart must be empty', 'request')
            ));
        } elseif (Tools::isSubmit('favicon')) {
            $this->toJson(array(
                'product_total' => (int)$this->context->cart->nbProducts()
            ));
        } elseif (EtsAbancartTracking::hasCartRules(0, $this->context)) {
            $this->toJson(array(
                'errors' => $this->module->l('Voucher for abandoned cart is applied', 'request')
            ));
        }
    }

    public function cleanCookie(&$cookies)
    {
        $flag = 0;
        if (count($cookies)) {
            foreach ($cookies as $type => $cookie) {
                foreach ($cookie as $row) {
                    if (isset($row['id_ets_abancart_reminder']) && $row['id_ets_abancart_reminder'] > 0 && EtsAbancartReminder::isInvalid($row['id_ets_abancart_reminder']) || isset($row['id_ets_abancart_campaign']) && $row['id_ets_abancart_campaign'] > 0 && !EtsAbancartCampaign::isValid($row['id_ets_abancart_campaign'], $this->context)) {
                        unset($cookies[$type][$row['id_ets_abancart_reminder']]);
                        if ($flag < 1)
                            $flag = 1;
                    }
                }
            }
        }
        return $flag;
    }

    public function initContent()
    {
        parent::initContent();

        if (
            Tools::isSubmit('renderDisplay')
            && trim(($id_ets_abancart_reminder = Tools::getValue('id_ets_abancart_reminder'))) !== ''
            && Validate::isUnsignedInt($id_ets_abancart_reminder)
        ) {
            $reminder = new EtsAbancartReminder($id_ets_abancart_reminder, $this->context->cart->id_lang ?: $this->context->language->id);
            $cookies = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];
            $flag = $this->cleanCookie($cookies);
            $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);
            if ($reminder->id <= 0 || $reminder->enabled != 1 || $reminder->deleted > 0 || ($checkValidity = EtsAbancartReminder::isInvalid($reminder->id)) || $campaign->enabled < 1 || $campaign->deleted > 0 || ($checkValidity = !EtsAbancartCampaign::isValid($campaign->id))) {
                if ($flag > 0)
                    $this->context->cookie->ets_abancart_reminders = @json_encode($cookies);
                die(json_encode([
                    'errors' => $reminder->id <= 0 ? $this->module->l('Reminder does not exist.', 'request') : '',
                    'id_ets_abancart_reminder' => (int)$id_ets_abancart_reminder,
                    'id_ets_abancart_campaign' => (int)$campaign->id,
                    'redisplay' => -1,
                    'close' => 0,
                    'deleted' => $reminder->id <= 0 || $reminder->enabled != 1 || $reminder->deleted > 0 || $campaign->enabled < 1 || $campaign->deleted > 0 || isset($checkValidity),
                    'campaigns' => EtsAbancartCampaign::getCampaignsFrontEnd($this->context),
                    'cookies' => $cookies,
                    'time' => time(),
                ]));
            }

            $reminderIsRun = isset($cookies[$campaign->campaign_type][$id_ets_abancart_reminder]);

            $id_ets_abancart_display_tracking = EtsAbancartDisplayTracking::filterId($reminder->id, $this->context);
            $id_cart_rule = $id_ets_abancart_display_tracking > 0 && $reminder->discount_option == 'auto' ? EtsAbancartDisplayTracking::getVoucher($id_ets_abancart_display_tracking, (int)$this->context->cart->id) : 0;

            $cart_rule = new CartRule($id_cart_rule);
            if ((int)$cart_rule->id <= 0 && $reminder->discount_option == 'auto' && $campaign->has_product_in_cart == EtsAbancartCampaign::HAS_SHOPPING_CART_YES) {
                if ($this->context->cart->id > 0)
                    $cart_rule = $this->module->addCartRule($reminder, $this->context->cart->id_customer);
            } elseif ($reminder->discount_option == 'fixed') {
                $idCartRule = CartRule::getIdByCode(trim($reminder->discount_code));
                if ($idCartRule)
                    $cart_rule = new CartRule($idCartRule);
            }

            $vars = array(
                'cart' => $this->context->cart,
                'campaign_type' => $campaign->campaign_type,
                'cart_rule' => $cart_rule
            );

            $content = $this->module->doShortCode($reminder->content, $campaign->campaign_type, $cart_rule, $this->context, $reminder->id);

            $id_ets_abancart_display_tracking = EtsAbancartDisplayTracking::saveData($reminder->id, $id_ets_abancart_display_tracking, $reminderIsRun, $this->context);
            if ($id_ets_abancart_display_tracking > 0 && $cart_rule->id > 0 && trim($reminder->discount_option) == 'auto')
                EtsAbancartDisplayTracking::setVoucher($id_ets_abancart_display_tracking, (int)$this->context->cart->id, $cart_rule->id, $reminder->allow_multi_discount);
            EtsAbancartDisplayTracking::writeLog($id_ets_abancart_display_tracking, $reminder->id, $cart_rule->id, 1, 0, $this->context);

            $json_data = [
                'type' => $campaign->campaign_type,
                'html' => $campaign->campaign_type == 'browser' ? $this->formatTextContent($content) : $content,
                'code' => $vars['cart_rule']->code
            ];
            if ($campaign->campaign_type == 'popup' || $campaign->campaign_type == 'browser') {
                $json_data['title'] = $reminder->title;
                if ($campaign->campaign_type == 'popup') {
                    $json_data['header_bg'] = $reminder->header_bg;
                    $json_data['header_text_color'] = $reminder->header_text_color;
                    $json_data['header_font_size'] = $reminder->header_font_size;
                    $json_data['header_height'] = $reminder->header_height;
                    $json_data['popup_width'] = (int)$reminder->popup_width;
                    $json_data['popup_height'] = (int)$reminder->popup_height;
                    $json_data['popup_body_bg'] = $reminder->popup_body_bg;
                    $json_data['border_radius'] = (int)$reminder->border_radius;
                    $json_data['border_color'] = $reminder->border_color;
                    $json_data['border_width'] = (int)$reminder->border_width;
                    $json_data['close_btn_color'] = $reminder->close_btn_color;
                    $json_data['vertical_align'] = $reminder->vertical_align;
                    $json_data['overlay_bg'] = $reminder->overlay_bg;
                    $json_data['overlay_bg_opacity'] = $reminder->overlay_bg_opacity;
                    $json_data['font_size'] = (int)$reminder->font_size;
                    $json_data['padding'] = (int)$reminder->padding;
                }
            }
            if ($campaign->campaign_type == 'bar') {
                $json_data['text_color'] = $reminder->text_color;
                $json_data['background_color'] = $reminder->background_color;
                $json_data['popup_width'] = $reminder->popup_width;
                $json_data['popup_height'] = $reminder->popup_height;
                $json_data['border_radius'] = $reminder->border_radius;
                $json_data['border_width'] = $reminder->border_width;
                $json_data['border_color'] = $reminder->border_color;
                $json_data['font_size'] = $reminder->font_size;
                $json_data['close_btn_color'] = $reminder->close_btn_color;
                $json_data['padding'] = $reminder->padding;
                $json_data['vertical_align'] = $reminder->vertical_align;
            } elseif ($campaign->campaign_type == 'browser') {
                $json_data['icon'] = $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/' . $reminder->icon_notify;
            }
            $cookies[$campaign->campaign_type][$id_ets_abancart_reminder] = [
                'type' => $campaign->campaign_type,
                'id_ets_abancart_reminder' => (int)$id_ets_abancart_reminder,
                'id_ets_abancart_campaign' => (int)$campaign->id,
                'lifetime' => EtsAbancartReminder::getLifeTime($id_ets_abancart_reminder, $campaign->id, $this->context),
                'redisplay' => (float)$reminder->redisplay > 0 ? $reminder->redisplay * 60 : -1,
                'deleted' => 0,
                'time' => time(),
            ];
            $this->context->cookie->ets_abancart_reminders = @json_encode($cookies);
            $json_data['cookies'] = $cookies;
            $json_data['campaigns'] = EtsAbancartCampaign::getCampaignsFrontEnd($this->context);

            $this->toJson($json_data);
        } elseif (
            ((int)Tools::getValue('redisplay') > 0 || (int)Tools::getValue('closed') > 0)
            && trim(($type = Tools::getValue('type'))) !== ''
            && in_array($type, [EtsAbancartCampaign::CAMPAIGN_TYPE_POPUP, EtsAbancartCampaign::CAMPAIGN_TYPE_BAR, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER])
            && trim(($id_ets_abancart_reminder = Tools::getValue('id'))) !== ''
            && Validate::isUnsignedInt($id_ets_abancart_reminder)
        ) {
            $reminder = new EtsAbancartReminder($id_ets_abancart_reminder);
            $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);
            $newCookies = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];

            $this->cleanCookie($newCookies);

            $redisplay = (int)Tools::getValue('redisplay', 0);
            $closed = (int)Tools::getValue('closed', 0);
            if (trim($type) == EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER) {
                $redisplay = $closed = -1;
            }
            $vars = [];
            $vars['id_ets_abancart_reminder'] = (int)$id_ets_abancart_reminder;
            $vars['type'] = $type;
            $vars['id_ets_abancart_campaign'] = (int)$campaign->id;
            $vars['closed'] = $closed;
            $vars['redisplay'] = $vars['closed'] < 1 && $reminder->redisplay != null && $redisplay > 0 ? $reminder->redisplay * 60 : -1;
            $vars['deleted'] = $reminder->deleted;
            $vars['time'] = time();
            $newCookies[$type][$reminder->id] = $vars;

            if ($reminder->enabled != 1 || $reminder->deleted > 0 || $campaign->enabled < 1 || $campaign->deleted > 0) {
                $vars['redisplay'] = -1;
                unset($newCookies[$type][$reminder->id]);
            }
            $this->context->cookie->ets_abancart_reminders = json_encode($newCookies);
            $vars['cookies'] = $newCookies;

            EtsAbancartDisplayTracking::writeLog(0, $reminder->id, 0, 0, 1, $this->context);

            $this->toJson($vars);

        } elseif (
            Tools::isSubmit('add_cart_rule')
            && ($discount_code = trim(Tools::getValue('discount_code'))) !== ''
            && Validate::isCleanHtml($discount_code)
        ) {
            $cart_rule = new CartRule((int)CartRule::getIdByCode($discount_code));
            $error_msg = Hook::exec('cartRuleCheckValidity', ['display_error' => true, 'id_cart_rule' => $cart_rule->id], $this->module->id);
            if (trim($error_msg) !== '')
                $this->errors[] = $error_msg;
            else {
                $checkValidity = $cart_rule->checkValidity($this->context);
                if ($checkValidity === true || empty($checkValidity))
                    $this->context->cart->addCartRule($cart_rule->id);
                elseif ($checkValidity !== false)
                    $this->errors[] = $checkValidity;
            }

            $this->toJson(array(
                'errors' => $this->errors && is_array($this->errors) ? implode($this->module->displayText('', 'br'), $this->errors) : false,
                'link_checkout' => $this->context->link->getPageLink(($this->module->is17 ? 'cart' : 'order' . ((int)Configuration::get('PS_ORDER_PROCESS_TYPE') ? '-opc' : '')), (int)Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) . ($this->module->is17 ? '?action=show' : ''),
            ));

        } elseif (Tools::isSubmit('leave_closed')) {
            $newCookies = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];
            $vars = [
                'id_ets_abancart_reminder' => 0,
                'id_ets_abancart_campaign' => 0,
                'type' => 'leave',
                'redisplay' => 1,
                'closed' => 0,
                'deleted' => 0,
                'time' => time(),
            ];
            if (!isset($newCookies['leave'][0])) {
                $newCookies['leave'][0] = $vars;
                $this->context->cookie->ets_abancart_reminders = @json_encode($newCookies);
            }

            EtsAbancartDisplayTracking::writeLog(0, 0, 0, 0, 1, $this->context);

            $this->toJson($vars);
        } elseif (Tools::isSubmit('leave')) {
            if ((int)Configuration::get('ETS_ABANCART_HAS_PRODUCT_IN_CART') == EtsAbancartCampaign::HAS_SHOPPING_CART_YES && count($this->context->cart->getProducts()) < 1) {
                $this->toJson(array(
                    'id_ets_abancart_reminder' => 0,
                    'id_ets_abancart_campaign' => 0,
                    'redisplay' => -1,
                ));
            }
            $reminderIsRun = false;
            $ets_abancart_reminders = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];
            if (count($ets_abancart_reminders) > 0 && isset($ets_abancart_reminders['leave'][0]) && $ets_abancart_reminders['leave'][0]) {
                $reminderIsRun = true;
            }
            $id_ets_abancart_display_tracking = EtsAbancartDisplayTracking::filterId(0, $this->context);
            $id_cart_rule = EtsAbancartDisplayTracking::getVoucher($id_ets_abancart_display_tracking, (int)$this->context->cart->id);

            $customer = new Customer((int)$this->context->cart->id_customer);
            $currency = Currency::getCurrencyInstance($this->context->cart->id_currency ?: Configuration::get('PS_CURRENCY_DEFAULT'));
            $id_group = isset($customer->id) && $customer->id ? Customer::getDefaultGroupId((int)$customer->id) : (int)Group::getCurrent()->id;
            $group = new Group($id_group);
            $useTax = $group->price_display_method ? false : true;

            $discount_option = Configuration::get('ETS_ABANCART_DISCOUNT_OPTION');
            $cart_rule = new CartRule((int)$id_cart_rule);
            if ((int)$cart_rule->id <= 0 && $discount_option == 'auto' && (int)Configuration::get('ETS_ABANCART_HAS_PRODUCT_IN_CART') == 1) {
                if ($this->context->cart->id > 0) {
                    $reminder = new EtsAbancartReminder();
                    $reminder->free_shipping = (int)Configuration::get('ETS_ABANCART_FREE_SHIPPING');
                    $reminder->id_currency = (int)Configuration::get('ETS_ABANCART_ID_CURRENCY');
                    $reminder->quantity = (int)Configuration::get('ETS_ABANCART_QUANTITY');
                    $reminder->quantity_per_user = (int)Configuration::get('ETS_ABANCART_QUANTITY_PER_USER');
                    $reminder->reduction_product = -2;
                    $reminder->discount_name = Configuration::get('ETS_ABANCART_DISCOUNT_NAME', $this->context->language->id);
                    $adt = Configuration::get('ETS_ABANCART_APPLY_DISCOUNT_TO');
                    if ($adt != 'selection') {
                        $reminder->selected_product = null;
                    }
                    if ($adt == 'order') {
                        $reminder->reduction_product = 0;
                    } elseif ($adt == 'specific') {
                        $reminder->reduction_product = (int)Configuration::get('ETS_ABANCART_REDUCTION_PRODUCT');
                    } elseif ($adt == 'cheapest') {
                        $reminder->reduction_product = -1;
                    } elseif ($adt == 'selection') {
                        $reminder->reduction_product = -2;
                        $selectedProducts = Configuration::get('ETS_ABANCART_SELECTED_PRODUCT');
                        if ($selectedProducts)
                            $selectedProducts = explode(',', $selectedProducts);
                        if ($selectedProducts && is_array($selectedProducts)) {
                            $products = array_map('intval', $selectedProducts);
                            $reminder->selected_product = implode(',', $products);
                        } else
                            $reminder->selected_product = null;
                    }
                    $reminder->reduction_exclude_special = (int)Configuration::get('ETS_ABANCART_REDUCTION_EXCLUDE_SPECIAL');
                    if ((int)Configuration::get('ETS_ABANCART_SEND_A_GIFT')) {
                        $reminder->gift_product = (int)Configuration::get('ETS_ABANCART_GIFT_PRODUCT');
                        $reminder->gift_product_attribute = (int)Configuration::get('ETS_ABANCART_GIFT_PRODUCT_ATTRIBUTE');
                    }
                    $reminder->allow_multi_discount = (int)Configuration::get('ETS_ABANCART_ALLOW_MULTI_DISCOUNT');
                    $reminder->reduction_percent = 0;
                    $reminder->reduction_amount = 0;
                    if ((string)Configuration::get('ETS_ABANCART_APPLY_DISCOUNT') == 'percent')
                        $reminder->reduction_percent = (float)Configuration::get('ETS_ABANCART_REDUCTION_PERCENT');
                    if ((string)Configuration::get('ETS_ABANCART_APPLY_DISCOUNT') == 'amount')
                        $reminder->reduction_amount = (float)Configuration::get('ETS_ABANCART_REDUCTION_AMOUNT');
                    $reminder->reduction_tax = (int)Configuration::get('ETS_ABANCART_REDUCTION_TAX');
                    $reminder->apply_discount_in = (int)Configuration::get('ETS_ABANCART_APPLY_DISCOUNT_IN');
                    $reminder->apply_discount = (string)Configuration::get('ETS_ABANCART_APPLY_DISCOUNT');
                    $cart_rule = $this->module->addCartRule($reminder, $this->context->cart->id_customer);
                }
            } elseif ($discount_option == 'fixed') {
                $idCartRule = CartRule::getIdByCode(trim(Configuration::get('ETS_ABANCART_DISCOUNT_CODE')));
                if ($idCartRule)
                    $cart_rule = new CartRule(trim($idCartRule));
            }

            $JSON = array(
                '[total_cart]' => Tools::displayPrice(($total_cart = $this->context->cart->getOrderTotal($useTax)), $currency),
                '[total_products_cost]' => Tools::displayPrice($this->context->cart->getOrderTotal($useTax, Cart::ONLY_PRODUCTS), $currency),
                '[total_shipping_cost]' => Tools::displayPrice(($shipping_code = $this->context->cart->getOrderTotal($useTax, Cart::ONLY_SHIPPING))),
                '[total_tax]' => Tools::displayPrice($useTax ? ($total_cart - $this->context->cart->getOrderTotal(false)) : 0.00, $currency),
                '[product_list]' => $this->module->doProductSmarty($this->context->cart->getProducts(), $this->context),
                '[discount_code]' => $cart_rule->code,
                '[discount_from]' => $cart_rule->id ? date($this->context->language->date_format_lite, strtotime($cart_rule->date_from)) : '',
                '[discount_to]' => $cart_rule->id ? date($this->context->language->date_format_lite, strtotime($cart_rule->date_to)) : '',
                '[button_add_discount]' => $this->module->doSmarty(array('button_add_discount' => true, 'discount_code' => $cart_rule->code), $this->context->cart->id_lang),
            );

            $money_saved = ($cart_rule->free_shipping ? $shipping_code : 0) + $cart_rule->getContextualValue($useTax, $this->context, CartRule::FILTER_ACTION_REDUCTION);
            $JSON['[money_saved]'] = Tools::displayPrice($money_saved, $currency);
            $JSON['[total_payment_after_discount]'] = Tools::displayPrice(($total_cart - $money_saved), $currency);
            $JSON['[discount_count_down_clock]'] = $cart_rule->id ? $this->module->doSmarty(array('discount_count_down_clock' => '1', 'date_to' => $cart_rule->date_to)) : '';
            if ((float)$cart_rule->reduction_percent) {
                $JSON['[reduction]'] = $cart_rule->reduction_percent . '%';
            } elseif ((float)$cart_rule->reduction_amount) {
                $reduction_amount = Tools::convertPrice($cart_rule->reduction_amount, $cart_rule->reduction_currency, false);
                $JSON['[reduction]'] = Tools::displayPrice(Tools::ps_round(Tools::convertPrice($reduction_amount, $currency), 2), $currency) . ' ' . ($cart_rule->reduction_tax ? $this->module->l('(tax incl.)', 'request') : $this->module->l('(tax excl.)', 'request'));
            } elseif ($cart_rule->free_shipping) {
                $JSON['[reduction]'] = $this->module->l('Free shipping', 'request');
            } else
                $JSON['[reduction]'] = $this->module->l('None', 'request');

            $id_ets_abancart_display_tracking = EtsAbancartDisplayTracking::saveData(0, $id_ets_abancart_display_tracking, $reminderIsRun, $this->context);
            if ($id_ets_abancart_display_tracking > 0 && $cart_rule->id > 0 && trim($discount_option) == 'auto')
                EtsAbancartDisplayTracking::setVoucher($id_ets_abancart_display_tracking, (int)$this->context->cart->id, $cart_rule->id, (int)Configuration::get('ETS_ABANCART_ALLOW_MULTI_DISCOUNT'));

            EtsAbancartDisplayTracking::writeLog($id_ets_abancart_display_tracking, 0, $cart_rule->id, 1, 0, $this->context);

            $JSON['background_color'] = Configuration::get('ETS_ABANCART_POPUP_BG_COLOR');
            $JSON['popup_width'] = Configuration::get('ETS_ABANCART_POPUP_WIDTH');
            $JSON['popup_height'] = Configuration::get('ETS_ABANCART_POPUP_HEIGHT');
            $JSON['border_radius'] = Configuration::get('ETS_ABANCART_BORDER_RADIUS');
            $JSON['border_width'] = Configuration::get('ETS_ABANCART_BORDER_WIDTH');
            $JSON['border_color'] = Configuration::get('ETS_ABANCART_BORDER_COLOR');
            $JSON['font_size'] = Configuration::get('ETS_ABANCART_FONT_SIZE');
            $JSON['close_btn_color'] = Configuration::get('ETS_ABANCART_CLOSE_BTN_COLOR');
            $JSON['padding'] = Configuration::get('ETS_ABANCART_PADDING');
            $JSON['vertical_align'] = Configuration::get('ETS_ABANCART_VERTICLE_ALIGN');
            $JSON['overlay_bg'] = Configuration::get('ETS_ABANCART_OVERLAY_BG');
            $JSON['overlay_bg_opacity'] = Configuration::get('ETS_ABANCART_OVERLAY_BG_OPACITY');

            $ets_abancart_reminders['leave'][0] = [
                'id_ets_abancart_reminder' => 0,
                'id_ets_abancart_campaign' => 0,
                'type' => 'leave',
                'redisplay' => 1,
                'closed' => 0,
                'deleted' => 0,
                'time' => time(),
            ];
            $this->context->cookie->ets_abancart_reminders = json_encode($ets_abancart_reminders);

            $this->toJson($JSON);
        } elseif (Tools::isSubmit('offLeave')) {
            $this->context->cookie->offLeave = time();
            $ets_abancart_reminders = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];
            $vars = [
                'id_ets_abancart_reminder' => 0,
                'id_ets_abancart_campaign' => 0,
                'type' => 'leave',
                'redisplay' => -1,
                'closed' => 1,
                'deleted' => 0,
                'time' => time(),
            ];
            $ets_abancart_reminders['leave'][0] = $vars;
            $this->context->cookie->ets_abancart_reminders = json_encode($ets_abancart_reminders);
            $vars['off'] = true;

            EtsAbancartDisplayTracking::writeLog(0, 0, 0, 0, 1, $this->context);

            $this->toJson($vars);
        }
        $this->toJson($this->module->l('No request', 'request'));
    }

    public function formatTextContent($content)
    {
        $content = trim(strip_tags($content));
        $content = preg_replace('/\n\r/', ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        return $content;
    }

    public function toJson($jsonData)
    {
        die(Tools::jsonEncode($jsonData));
    }
}
