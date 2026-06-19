<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class pscartabandonmentproFrontCAPCronJobModuleFrontController extends ModuleFrontController
{
    public $callFromCLI;
    public $module;
    public $context;
    public $debug = false;
    public $emailTest;
    public $isTest;

    public function __construct($isCLI = false, $emailTest = false)
    {
        $this->callFromCLI = $isCLI;
        $this->emailTest = $emailTest;

        /**
         * Check if we do a send test
         */
        if (!$emailTest) {
            $this->isTest = false;
        } else {
            $this->isTest = true;
        }

        /**
         * Check it's CLI
         */
        if ($this->callFromCLI) {
            $this->module = Module::getInstanceByName('pscartabandonmentpro');
            $this->context = Context::getContext();
        } else {
            parent::__construct();
        }
    }

    /**
     * Cron task main method
     *
     */
    public function initContent()
    {
        if (!$this->canExecuteCronTask()) {
            $errorMessage = 'CartAbandonmentPro : Wrong Token';
            PrestaShopLoggerCore::addLog($errorMessage, 2);
            $this->showSmartyTemplate();
            return false;
        }

        $oCartRemindersInfos = new CartReminderInfo;
        $bGetOnlyActivesReminders = true;
        $reminderList = $oCartRemindersInfos->getReminderList($bGetOnlyActivesReminders);

        // if reminder list is empty, we stop the cron task
        if (empty($reminderList)) {
            $errorMessage = 'CartAbandonmentPro : There were no reminderList';
            $this->getErrorMsg($errorMessage, 1);
            $this->showSmartyTemplate();
            return false;
        }

        $aAbandonedCarts = $this->getAllAbandonedCartForCronTask($reminderList);
        // if there is no abandoned carts, we stop the cron task
        if (empty($aAbandonedCarts)) {
            $errorMessage = 'CartAbandonmentPro : There were no abandoned cart';
            $this->getErrorMsg($errorMessage, 1);
            $this->showSmartyTemplate();
            return false;
        }
        
        $oReminderInfos = new CartReminderInfo;
        $oProductInfos = new CartReminderProductInfo;
        
        // For each abandoned carts of each reminder id
        foreach ($aAbandonedCarts as $reminderId => $aCustomerData) {
            
            // get the current reminder discount informations to set, later, the customer discount parameters
            $aReminderDiscount = $oReminderInfos->getDiscountInfos($reminderId);
            $aReminderTemplateAppearance = $oReminderInfos->getEmailTemplateAppearance($reminderId);
            $aReminderTemplateDatas = $oReminderInfos->getEmailTemplateDatas($reminderId);

            foreach ($aCustomerData as $key => $customer) {
                $id_customer = (int) $customer['id_customer'];

                if (!$this->checkIfEmailIsValid($customer['email'])) {
                    $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
                    unset($aAbandonedCarts[$reminderId][$key]);
                    continue;
                }
                
                $aReminderTemplateDatasForUser = $this->getTemplateDatasForUser($customer['id_lang'], $aReminderTemplateDatas);
                
                // If there is not template for the user, do not treat him
                if (!$aReminderTemplateDatasForUser) {
                    $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
                    unset($aAbandonedCarts[$reminderId][$key]);
                    continue;
                }
                
                $aCustomerProductsInCart = $oProductInfos->getProductsFromCartId($customer['id_cart']);
                
                // if $aCustomerProductsInCart is false, the customer has no product in cart
                if (!$aCustomerProductsInCart) {
                    $errorMessage = 'CartAbandonmentPro ['.$reminderId.'] : No product found in cart for customer #'.$id_customer;
                    $this->getErrorMsg($errorMessage, 2);
                    $this->showSmartyTemplate();
                    $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
                    unset($aAbandonedCarts[$reminderId][$key]);
                    continue;
                }
                
                $aCustomerDiscount = $this->getCustomerDiscountParametersFromReminder($customer, $aReminderDiscount);

                // if $aCustomerDiscount is empty, there is a critical error
                if (empty($aCustomerDiscount)) {
                    $errorMessage = 'CartAbandonmentPro ['.$reminderId.'] : Not able to find a discount for customer #'.$id_customer;
                    $this->getErrorMsg($errorMessage, 2);
                    $this->showSmartyTemplate();
                    $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
                    unset($aAbandonedCarts[$reminderId][$key]);
                    continue;
                }

                $customerCreatedDiscount = $this->createPrestashopCustomerDiscount($id_customer, $aCustomerDiscount);

                // if $customerCreatedDiscount is false, the discount has not been created
                if (!$customerCreatedDiscount) {
                    $errorMessage = 'CartAbandonmentPro ['.$reminderId.'] : Not able to find a discount for customer #'.$id_customer;
                    $this->getErrorMsg($errorMessage, 2);
                    $this->showSmartyTemplate();
                    $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
                    unset($aAbandonedCarts[$reminderId][$key]);
                    continue;
                }

                // if we do a debug, we don't add the customer in the treated list and we don't send any mail
                if ($this->debug) {
                    continue;
                }
                
                $bCustomerSendMail = $this->sendCustomerEmail($customer, $aReminderTemplateAppearance, $aReminderTemplateDatasForUser, $customerCreatedDiscount, $aCustomerProductsInCart);
                
                // if $bCustomerSendMail is false, the email has not been send
                if (!$bCustomerSendMail) {
                    $errorMessage = 'CartAbandonmentPro ['.$reminderId.'] : Mail has not been send for customer #'.$id_customer;
                    $this->getErrorMsg($errorMessage, 2);
                    $this->showSmartyTemplate();
                    unset($aAbandonedCarts[$reminderId][$key]);
                }

                $this->setCustomerReminderTreated($id_customer, $reminderId, $customer['id_cart']);
            }
        }

        if ($this->callFromCLI) {
            echo "Finished\n";
        }
            
        $this->showSmartyTemplate($aAbandonedCarts);
    }

    /**
     * Get the error message, log it into Prestashop and show it on the CLI if necessary.
     *
     * @param  string $errorMessage
     * @param  int $iType
     *
     * @return void
     */
    private function getErrorMsg($errorMessage, $iType)
    {
        //save data in Prestashop Logger
        PrestaShopLoggerCore::addLog($errorMessage, $iType);

        if ($this->callFromCLI) {
            echo $errorMessage."\n";
        }
    }

    /**
     * Check if we can execute the cron task
     * If CLI , we can
     * If token is good, we can
     *
     * @return bool
     */
    private function canExecuteCronTask()
    {
        // Check if we used the cli
        if ($this->callFromCLI) {
            return true;
        }

        // Check if the token is good
        if (Tools::getValue('token') === $this->module->cron_token) {
            return true;
        }

        return false;
    }

    /**
     * We check if a template is available for the user's language. If it doesn't we get the default 'ENGLISH' template. Else we return false
     *
     * @param  int $iLangId
     * @param  array $aTemplateDatas
     *
     * @return array|bool
     */
    private function getTemplateDatasForUser($iLangId, $aTemplateDatas) 
    {
        // If a language exist for the same language of the user, return this template
        if (!empty($aTemplateDatas[$iLangId])) {
            return $aTemplateDatas[$iLangId];
        }

        // We check all the array to find a template in "lang_iso" : en
        // "en" is the default language
        foreach ($aTemplateDatas as $id_lang => $template) {
            if ($template['lang_iso'] == 'en') {
                return $template;
            }
        }

        return false;
    }

    /**
     * Show the CRON Sended template with smarty assign
     *
     * @param  array $aAbandonedCarts
     *
     */
    private function showSmartyTemplate($aAbandonedCarts = array())
    {
        $this->context->smarty->assign(array(
            'sendList' => $aAbandonedCarts,
        ));

        $this->template = _PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/front/cron_sent.tpl';
    }

    /**
     * Get all abandoned Cart for all customers having an a cart_id without an order for each reminder
     *
     * @param  array $reminderList
     *
     * @return array $aAbandonedCarts
     */
    private function getAllAbandonedCartForCronTask($reminderList)
    {
        $aAbandonedCarts = array();
        $oCustomersInfos = new CartReminderCustomerInfo;
        $oReminderInfos = new CartReminderInfo;

        foreach ($reminderList as $reminderKey => $reminderValue) {

            $iReminderId = $reminderValue['id_cart_abandonment'];
            $iFrequency = $reminderValue['cart_frequency_number'];

            // If debug active, it make all actual carts abandoned
            if ($this->debug) {
                $iFrequency = -9999999;
            }

            // if cart_frequency_type is "day" switch to hours
            if ($reminderValue['cart_frequency_type'] === 'day') {
                $iFrequency = $iFrequency * 24;
            }

            $aTargetProfile = $oReminderInfos->getTargetInfos($iReminderId);

            // If it's a test, force email choice
            if ($this->isTest) {
                $aCustomerAbandonedCart = $oCustomersInfos->getAbandonedCartForTestUser();
            } else {
                $aCustomerAbandonedCart = $oCustomersInfos->getAbandonedCart($iReminderId, $iFrequency, $aTargetProfile);
            }

            // We save the datas only if $aCustomerAbandonedCart is not empty
            if (!empty($aCustomerAbandonedCart)) {
                $aAbandonedCarts[$iReminderId] = $aCustomerAbandonedCart;
            }
        }

        return $aAbandonedCarts;
    }

    /**
     * Create a discount for a customer
     *
     * @param  int $id_customer
     * @param  array $aCustomerDiscount
     *
     * @return array|bool $discount
     */
    private function createPrestashopCustomerDiscount($id_customer, $aCustomerDiscount)
    {
        $type = $aCustomerDiscount['discount_value_type'];

        // if type is "no_discount" we don't create a discount we return "no_discount"
        if ($type === 'no_discount') {
            return $type;
        }

        // Set all necessary variables from $aCustomerDiscount
        $value = $aCustomerDiscount['discount_value'];
        $validity = $aCustomerDiscount['discount_validity'];
        $min = $aCustomerDiscount['discount_from'];
        $now = time();
        $code = 'CAV'.Tools::substr(sha1(microtime()), 6, 5);

        // Create a discount for a customer
        $discount = new CartRule();
        $discount->code = $code;
        $discount->name[Configuration::get('PS_LANG_DEFAULT')] = $code;
        $discount->quantity = 1;
        $discount->quantity_per_user = 1;
        $discount->active = true;
        $discount->reduction_tax = (bool)$aCustomerDiscount['discount_ttc'];
        $discount->shop_restriction = true;
        $discount->highlight = false;
        $discount->partial_use = 0;
        $discount->cart_rule_restriction = !(bool)$aCustomerDiscount['discount_cumulate'];
        $discount->date_from = date('Y-m-d H:i:s', $now);
        $discount->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * $validity));
        $discount->description = 'Cart Abandonment Pro '.$code;

        if ($this->isTest) {
            $discount->active = false;
            $discount->id_customer = 0;
        } else {
            $discount->active = true;
            $discount->id_customer = (int)$id_customer;
        }

        // Check the type of the discount freeshipping OR percentage OR amount and create the specific discount
        if ($type == 'freeshipping') {
            $discount->free_shipping = true;
        } else {
            $discount->free_shipping = false;    
        }

        $discount->minimum_amount = $min;
        $discount->minimum_amount_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');

        if ($type == 'amount') {
            $discount->reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
            $discount->reduction_amount = $value;
        } elseif ($type == 'percentage') {
            $discount->reduction_percent = $value;
        }

        $discount->save();

        Db::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'cart_rule_shop` (`id_cart_rule`, `id_shop`)
            VALUES ('.(int)$discount->id.', '.(int)Shop::getContextShopId().')');

        return $discount;
    }

    /**
     * Get the right discount for the customer according to his cart
     *
     * @param  array $customer
     * @param  array $aReminderDiscount
     *
     * @return array $aCustomerDiscount
     */
    private function getCustomerDiscountParametersFromReminder($customer, $aReminderDiscount)
    {
        $aCustomerDiscount = array();
        $iDiscountRangesAmount = count($aReminderDiscount);
        
        // If there is no discount range, we just return the only available discount : $aReminderDiscount[0]
        if ($iDiscountRangesAmount === 1) {
            return $aReminderDiscount[0];
        }

        // If there is many ranges, we need to find the right range for the customer
        // The customer cart value must be between discount_from AND discount_to
        $customerCartAmount = $customer['cart_value'];

        foreach ($aReminderDiscount as $key => $aRangeDiscount) {
            // Current loop range
            $iCurrentDiscountRange = $key + 1;

            $iFromValue = $aRangeDiscount['discount_from'];

            // We add 0.99 to have for example 
            // From 1 to 5.99  &  From 6 to xx.xx
            $fToValue = $aRangeDiscount['discount_to'] + 0.99;

            if ($this->weApplyThisRange($customerCartAmount, $iFromValue, $fToValue, $iCurrentDiscountRange, $iDiscountRangesAmount)) {
                $aCustomerDiscount = $aRangeDiscount;
                break;
            } 
        }

        return $aCustomerDiscount;
    }

    /**
     * Tell if we apply a discount range with those parameters
     *
     * @param  float $customerCartAmount
     * @param  float $iFromValue
     * @param  float $fToValue
     * @param  int $iCurrentDiscountRange
     * @param  int $iDiscountRangesAmount
     *
     * @return bool
     */
    private function weApplyThisRange($customerCartAmount, $iFromValue, $fToValue, $iCurrentDiscountRange, $iDiscountRangesAmount) 
    {
        if ($customerCartAmount >= $iFromValue 
            && $customerCartAmount <= $fToValue 
            && $iCurrentDiscountRange < $iDiscountRangesAmount) {
            return true;
        } 

        if ($customerCartAmount >= $iFromValue 
            && $iCurrentDiscountRange == $iDiscountRangesAmount) {
            return true;
        }

        return false;
    }

    /**
     * Set the customer as 'treated' for the current reminder
     *
     * @param  int $id_customer
     * @param  int $reminderId
     * @param  int $id_cart
     *
     * @return bool
     */
    private function setCustomerReminderTreated($id_customer, $reminderId, $id_cart)
    {
        if ($this->isTest) {
            return true;
        }

        $data = array(
            'id_customer' => (int) $id_customer,
            'id_cart_abandonment' => (int) $reminderId,
            'id_cart' => (int) $id_cart,
            'send_date' => date('Y-m-d')
        );

        if (!Db::getInstance()->insert('cart_abandonment_customer_send', $data)) {
            $errorMessage = 'CartAbandonmentPro ['.$reminderId.'] : The reminder is not set as treated for customer #'.$id_customer;
            $this->getErrorMsg($errorMessage, 3);
            return false;
        }

        return true;
    }

    /**
     * Send the customer Email
     *
     * @param  array $aCustomer
     * @param  array $aReminderTemplateAppearance
     * @param  array $aReminderTemplateDatas
     *
     * @return bool
     */
    private function sendCustomerEmail($aCustomer, $aReminderTemplateAppearance, $aReminderTemplateDatas, $customerCreatedDiscount, $aCustomerProductsInCart)
    {
        $iCustomerId = (int)$aCustomer['id_customer'];
        $iReminderId = (int)$aReminderTemplateAppearance['id_cart_abandonment'];
        $iCartId = (int)$aCustomer['id_cart'];
        $iLangId = (int)$aCustomer['id_lang'];

        // clean the template discount data
        if ($customerCreatedDiscount == 'no_discount') {
            $aReminderTemplateDatas['email_discount'] = '';
        }

        // prepare principal content text for email
        $sEmailContentReworked = $this->emailContentReplaceOccurences(
            $aCustomer, 
            $customerCreatedDiscount, 
            $aReminderTemplateDatas['email_content'], 
            $aReminderTemplateDatas['email_unsubscribe_text'],
            $aCustomerProductsInCart,
            $iReminderId
        );

        // prepare principal discount text for email
        $sEmailDiscountReworked = $this->emailContentReplaceOccurences(
            $aCustomer, 
            $customerCreatedDiscount, 
            $aReminderTemplateDatas['email_discount'], 
            $aReminderTemplateDatas['email_unsubscribe_text'],
            $aCustomerProductsInCart,
            $iReminderId
        );

        // prepare unsubscribe content for email
        $sEmailUnsubscribeReworked = $this->emailContentReplaceOccurences(
            $aCustomer, 
            $customerCreatedDiscount, 
            $aReminderTemplateDatas['email_unsubscribe'], 
            $aReminderTemplateDatas['email_unsubscribe_text'],
            $aCustomerProductsInCart,
            $iReminderId
        );

        // prepare unsubscribe content for email
        $subjectReworked = $this->emailContentReplaceOccurences(
            $aCustomer, 
            $customerCreatedDiscount, 
            $aReminderTemplateDatas['email_subject'], 
            null,
            $aCustomerProductsInCart,
            $iReminderId
        );

        $aShowElement = $this->showReassuranceAndLinkElements($aReminderTemplateDatas);
        
        $oFrontRouter = new CartReminderFrontControllerRouter;

        // prepare variables to be replaced in the email template
        $template_vars = array(
            '{primary_color}' => $aReminderTemplateAppearance['primary_color'],
            '{secondary_color}' => $aReminderTemplateAppearance['secondary_color'],
            '{email_subject}' => $subjectReworked,
            '{email_content}' => $sEmailContentReworked,
            '{email_discount_show}' => $aShowElement['email_discount_show'],
            '{email_discount}' => $sEmailDiscountReworked,
            '{email_link_facebook}' => $aReminderTemplateDatas['email_link_facebook'],
            '{email_link_twitter}' => $aReminderTemplateDatas['email_link_twitter'],
            '{email_link_instagram}' => $aReminderTemplateDatas['email_link_instagram'],
            '{email_link_facebook_show}' => $aShowElement['email_link_facebook_show'],
            '{email_link_twitter_show}' => $aShowElement['email_link_twitter_show'],
            '{email_link_instagram_show}' => $aShowElement['email_link_instagram_show'],
            '{email_reassurance_div_width}' => $this->setReassuranceBlockDivWidth($aShowElement),
            '{email_reassurance_text1}' => $aReminderTemplateDatas['email_reassurance_text1'],
            '{email_reassurance_text2}' => $aReminderTemplateDatas['email_reassurance_text2'],
            '{email_reassurance_text3}' => $aReminderTemplateDatas['email_reassurance_text3'],
            '{email_reassurance_text1_show}' => $aShowElement['email_reassurance_text1_show'],
            '{email_reassurance_text2_show}' => $aShowElement['email_reassurance_text2_show'],
            '{email_reassurance_text3_show}' => $aShowElement['email_reassurance_text3_show'],
            '{email_reassurance_img1}' => $this->module->ps_url.$aReminderTemplateDatas['email_reassurance_img1'],
            '{email_reassurance_img2}' => $this->module->ps_url.$aReminderTemplateDatas['email_reassurance_img2'],
            '{email_reassurance_img3}' => $this->module->ps_url.$aReminderTemplateDatas['email_reassurance_img3'],
            '{email_cta_show}' => $aShowElement['email_cta_show'],
            '{email_cta}' => $this->getCtaLink($iCustomerId, $iReminderId, $iCartId, $aReminderTemplateDatas['email_cta']),
            '{email_unsubscribe}' => $sEmailUnsubscribeReworked,
            '{visualized_image}' => $this->getPixelVisualizedEmail($iCustomerId, $iReminderId, $iCartId),
            '{img_url}' => $this->module->ps_host.$this->module->img_path,
            '{shop_logo}' => $this->module->ps_url.'/img/'.Configuration::get('PS_LOGO'),
            '{shop_url_redirect}' => $oFrontRouter->getShopUrlController($iCustomerId, $iReminderId, $iCartId),
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
            '{shop_addr1}' => Configuration::get('PS_SHOP_ADDR1'),
            '{shop_addr2}' => Configuration::get('PS_SHOP_ADDR2'),
            '{shop_zipcode}' => Configuration::get('PS_SHOP_CODE'),
            '{shop_city}' => Configuration::get('PS_SHOP_CITY'),
            '{shop_country}' => Configuration::get('PS_SHOP_COUNTRY'),
            '{shop_phone}' => Configuration::get('PS_SHOP_PHONE'),
            '{shop_fax}' => Configuration::get('PS_SHOP_FAX'),
        );

        $template_path = _PS_MODULE_DIR_.'pscartabandonmentpro/mails/';

        if ($this->isTest) {
            $sendToEmail = $this->emailTest;
        } else {
            $sendToEmail = $aCustomer['email'];
        }

        return Mail::Send(
            $aCustomer['id_lang'],
            $aReminderTemplateAppearance['model_name'],
            $subjectReworked,
            $template_vars,
            $sendToEmail,
            null,
            null,
            null,
            null,
            null,
            $template_path,
            null,
            Shop::getContextShopID()
        );
    }

    /**
     * Check if email is valid. Add the email address in the Prestashop Logs if the email address is wrong
     *
     * @param  string $sEmail
     *
     * @return bool
     */
    private function checkIfEmailIsValid($sEmail)
    {
        if (filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        
        $errorMessage = 'CartAbandonmentPro : '.$sEmail.' is not a valid email';
        //save data in Prestashop Logger
        PrestaShopLoggerCore::addLog($errorMessage, 3);

        return false;
    }

    /**
     * Set the block div width according to the number of Reassurance elements shown
     *
     * @param  array $aShowElement
     *
     * @return float
     */
    private function setReassuranceBlockDivWidth($aShowElement) 
    {
        $iElem1 = ($aShowElement['email_reassurance_text1_show'] == 'none')? 0 : 1;
        $iElem2 = ($aShowElement['email_reassurance_text2_show'] == 'none')? 0 : 1;
        $iElem3 = ($aShowElement['email_reassurance_text3_show'] == 'none')? 0 : 1;

        $iFinalElem = ($iElem1 + $iElem2 + $iElem3);

        // Prevent division by zero
        if ($iFinalElem == 0) {
            return 100;
        }

        $fPercent = 100 / ($iFinalElem);

        return $fPercent;
    }

    /**
     * Return the cart link in a HTML tag
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    private function getCartLink($iCustomerId, $iReminderId, $iCartId)
    {
        $oFrontRouter = new CartReminderFrontControllerRouter;

        // get cart URL for this customer
        $sCartUrlToken =  $oFrontRouter->getShopCartController($iCustomerId, $iReminderId, $iCartId);
        $sCartLink = '<a href="'.$sCartUrlToken.'" target="_blank">'.$this->module->l('Cart link').'</a>';

        return $sCartLink;
    }

    /**
     * Return the Cart CTA link in a HTML tag
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    private function getCtaLink($iCustomerId, $iReminderId, $iCartId, $ctaText)
    {
        $oFrontRouter = new CartReminderFrontControllerRouter;

        // get cart URL for this customer
        $sCartUrlToken =  $oFrontRouter->getShopCartController($iCustomerId, $iReminderId, $iCartId);
        $sCartLink = '<a href="'.$sCartUrlToken.'" target="_blank">'.$ctaText.'</a>';

        return $sCartLink;
    }

    /**
     * Get a content with elements needed to be replaced and return final content
     *
     * @param  array $aCustomer
     * @param  array $aCustomerDiscount
     * @param  string $sContent
     * @param  string $unsubscribeSpecificText
     * @param  array $aCustomerProductsInCart
     * @param  int $iReminderId
     *
     * @return string
     */
    private function emailContentReplaceOccurences($aCustomer, $aCustomerDiscount, $sContent, $unsubscribeSpecificText, $aCustomerProductsInCart, $iReminderId) 
    {
        $oFrontRouter = new CartReminderFrontControllerRouter;
        $context = Context::getContext();

        // Prepare the datas to include in the final datas
        $sFirstName = $aCustomer['firstname'];
        $sLastName = $aCustomer['lastname'];
        $sGender = $aCustomer['gender'];
        $iCartId = $aCustomer['id_cart'];
        
        $sCartTemplate = $this->getCustomerCartTemplate($aCustomer, $iReminderId, $iCartId, $aCustomerProductsInCart);
        $iCartNbProducts = count($aCustomerProductsInCart);
        
        // get unsubscribe URL for this customer
        if (null !== $unsubscribeSpecificText) {
            $sUnsubscribeUrlToken = $oFrontRouter->getUnsubscribeController($aCustomer['id_customer'], $iReminderId, $iCartId);
            $sSpecificText = (!empty($unsubscribeSpecificText)) ? $unsubscribeSpecificText : $this->module->l('Unsubscribe');
            $sUnsubscribe = '<a href="'.$sUnsubscribeUrlToken.'" target="_blank">'.$sSpecificText.'</a>';
            $sContent = str_replace('{unsubscribe}', $sUnsubscribe, $sContent);
        }

        // get shop URL for this customer
        $sShopUrlToken =  $oFrontRouter->getShopUrlController($aCustomer['id_customer'], $iReminderId, $iCartId);
        $sShopLink = '<a href="'.$sShopUrlToken.'" target="_blank">'.$this->module->l('Shop link').'</a>';

        // get cart URL for this customer
        $sCartLink = $this->getCartLink($aCustomer['id_customer'], $iReminderId, $iCartId);
        
        // Replace all occurences
        $sContent = str_replace('{first_name}', $sFirstName, $sContent);
        $sContent = str_replace('{last_name}', $sLastName, $sContent);
        $sContent = str_replace('{gender}', $sGender, $sContent);
        $sContent = str_replace('{cart}', $sCartTemplate, $sContent);
        $sContent = str_replace('{nb_product}', $iCartNbProducts, $sContent);
        $sContent = str_replace('{shop_link}', $sShopLink, $sContent);
        $sContent = str_replace('{cart_link}', $sCartLink, $sContent);

        // Show discount part only if there is a discount
        if ($aCustomerDiscount !== 'no_discount') {
            $sDiscountCode = '<span class="discount-code">'.$aCustomerDiscount->code.'</span>';

            if ($aCustomerDiscount->reduction_amount !== NULL) {
                $sDiscountValue = Tools::displayPrice($aCustomerDiscount->reduction_amount, $this->getCurrencyInfos($iCartId));
            } else {
                // is it a freeshipping ?
                if ($aCustomerDiscount->free_shipping) {
                    $sDiscountValue = $this->module->l('freeshipping');
                } else {
                    $sDiscountValue = $aCustomerDiscount->reduction_percent.'%';
                }
            }

            $discountDateValidity = new DateTime($aCustomerDiscount->date_to);
            $sDiscountValidity = $discountDateValidity->format('Y-m-d');
            
            $sContent = str_replace('{discount_code}', $sDiscountCode, $sContent);
            $sContent = str_replace('{discount_value}', $sDiscountValue, $sContent);
            $sContent = str_replace('{discount_validity}', $sDiscountValidity, $sContent);
        }

        return $sContent;
    }

    /**
     * getCustomerCartTemplate
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     * @param  array $aCustomerProductsInCart
     *
     * @return string $template
     */
    private function getCustomerCartTemplate($aCustomer, $iReminderId, $iCartId, $aCustomerProductsInCart) 
    {
        $oProductInfo = new CartReminderProductInfo();

        $aProductList = $this->changeProductLinkCart(
            $aCustomer['id_customer'],
            $iReminderId, 
            $iCartId,
            $oProductInfo->prepareProductListForTemplate($aCustomerProductsInCart, $aCustomer['id_shop'], $aCustomer['id_lang'])
        );

        $this->context->smarty->assign(array(
            'aProducts' => $aProductList,
            'currency' => $this->getCurrencyInfos($iCartId)->sign,
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.'/'.$this->module->name.'/views/templates/admin/ajax/cart.tpl');
    }

    /**
     * get currency sign from cart id
     *
     * @param  int $iCartId
     *
     * @return string
     */
    private function getCurrencyInfos($iCartId) 
    {
        $oCart = new Cart($iCartId);

        $id_currency = $oCart->id_currency;
        $id_lang = $oCart->id_lang;
        $id_shop = $oCart->id_shop;

        $currency = new Currency($id_currency, $id_lang, $id_shop);

        return $currency;
    }

    /**
     * Set if an element is display or not in the HTML
     *
     * @param  array $aReminderTemplateDatas
     *
     * @return array $aShowElement
     */
    private function showReassuranceAndLinkElements($aReminderTemplateDatas)
    {
        $aShowElement = array();
        $aElements = array(
            'email_discount' => array(
                'key' => 'email_discount_show',
                'display' => 'table'
            ),
            'email_link_facebook' => array(
                'key' => 'email_link_facebook_show',
                'display' => 'inline-block'
            ),
            'email_link_twitter' => array(
                'key' => 'email_link_twitter_show',
                'display' => 'inline-block'
            ),
            'email_link_instagram' => array(
                'key' => 'email_link_instagram_show',
                'display' => 'inline-block'
            ),
            'email_reassurance_text1' => array(
                'key' => 'email_reassurance_text1_show',
                'display' => 'inline-block'
            ),
            'email_reassurance_text2' => array(
                'key' => 'email_reassurance_text2_show',
                'display' => 'inline-block'
            ),
            'email_reassurance_text3' => array(
                'key' => 'email_reassurance_text3_show',
                'display' => 'inline-block'
            ),
            'email_cta' => array(
                'key' => 'email_cta_show',
                'display' => 'table'
            )
        );

        // Set if the element is not displayed or if it must be displayed 
        foreach ($aElements as $key => $aValue) {
            if (empty($aReminderTemplateDatas[$key])) {
                $aShowElement[$aValue['key']] = 'none';
            } else {
                $aShowElement[$aValue['key']] = $aValue['display'];
            }
        }

        return $aShowElement;
    }


    /**
     * Return the image pixel to know if the customer read the email
     *
     * @param  int $iReminderId
     * @param  int $iCartId
     *
     * @return string
     */
    private function getPixelVisualizedEmail($iCustomerId, $iReminderId, $iCartId)
    {
        $oFrontRouter = new CartReminderFrontControllerRouter;

        // Intialize the URL parameters
        $aParams = array(
            'id_customer' => $iCustomerId,
            'id_reminder' => $iReminderId,
            'id_cart' => $iCartId,
            'token' =>  sha1($iCartId.$iReminderId.$this->module->name.'visualized')
        );

        $visualizeUrl = $oFrontRouter->getModulelink('emailVisualize', $aParams);

        return '<img width="1" height="1" src="'.$visualizeUrl.'"/>';
    }

    /**
     * Change the product link to get an historic from the product link clicked
     *
     * @param  int $iCustomerId
     * @param  int $iReminderId
     * @param  int $iCartId
     * @param  array $aCustomerProductsInCart
     *
     * @return array $aCustomerProductsInCart
     */
    private function changeProductLinkCart($iCustomerId, $iReminderId, $iCartId, $aCustomerProductsInCart) 
    {
        $oFrontRouter = new CartReminderFrontControllerRouter;

        foreach ($aCustomerProductsInCart as $key => $aProductInfos) {
            $aCustomerProductsInCart[$key]['link'] = $oFrontRouter->getShopProductController($iCustomerId, $iReminderId, $iCartId, $aProductInfos['id_product']);
        }

        return $aCustomerProductsInCart;
    }
}