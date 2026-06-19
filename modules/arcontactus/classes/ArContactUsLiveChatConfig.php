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

include_once dirname(__FILE__).'/ArContactUsAbstract.php';

class ArContactUsLiveChatConfig extends ArContactUsAbstract
{
    public $tawk_to_head;
    public $tawk_to_on;
    public $tawk_to_site_id;
    public $tawk_to_widget;
    public $tawk_to_userinfo;
    public $tawk_to_custom_script;
    public $hr1;
    
    public $crisp_head;
    public $crisp_on;
    public $crisp_site_id;
    public $hr2;
    
    public $intercom_head;
    public $intercom_on;
    public $intercom_app_id;
    public $hr3;
    
    public $fb_head;
    public $fb_on;
    public $fb_page_id;
    public $fb_init;
    public $fb_one_line;
    public $fb_xfbml;
    public $fb_lang;
    public $fb_version;
    public $fb_color;
    public $hr4;
    
    public $vk_head;
    public $vk_on;
    public $vk_page_id;
    public $hr5;
    
    public $zopim_head;
    public $zopim_on;
    public $zopim_id;
    public $zopim_userinfo;
    public $hr6;
    
    public $skype_head;
    public $skype_on;
    public $skype_type;
    public $skype_id;
    public $skype_message_color;
    public $hr7;
    
    public $zalo_head;
    public $zalo_on;
    public $zalo_id;
    public $zalo_welcome;
    public $zalo_width;
    public $zalo_height;
    public $hr8;
    
    public $lhc_head;
    public $lhc_on;
    public $lhc_uri;
    public $lhc_width;
    public $lhc_height;
    public $lhc_popup_width;
    public $lhc_popup_height;
    public $lhc_department;
    public $hr9;
    
    public $ss_head;
    public $ss_on;
    public $ss_key;
    public $ss_userinfo;
    public $hr10;
    
    public $lc_head;
    public $lc_on;
    public $lc_key;
    public $lc_userinfo;
    public $hr11;
    
    public $tidio_head;
    public $tidio_on;
    public $tidio_key;
    public $tidio_userinfo;
    public $hr12;
    
    public $lcp_head;
    public $lcp_on;
    public $lcp_uri;
    public $hr13;
    
    public $lz_head;
    public $lz_on;
    public $lz_id;
    
    public $jivosite_head;
    public $jivosite_on;
    public $jivosite_id;
    public $jivosite_userinfo;
    public $hr15;
    
    public $zoho_head;
    public $zoho_on;
    public $zoho_id;
    public $hr16;
    
    public $fc_head;
    public $fc_on;
    public $fc_token;
    public $fc_host;
    public $fc_userinfo;
    public $hr17;
    
    public $phplive_head;
    public $phplive_on;
    public $phplive_src;
    public $phplive_userinfo;
    public $hr18;
    
    public $paldesk_head;
    public $paldesk_on;
    public $paldesk_key;
    public $paldesk_userinfo;
    public $hr19;
    
    
    public $hubspot_head;
    public $hubspot_on;
    public $hubspot_id;
    //public $hubspot_userinfo;
    public $hr20;
    
    public $socialintents_head;
    public $socialintents_on;
    public $socialintents_id;
    public $socialintents_userinfo;
    public $hr21;
    
    public $botmake_head;
    public $botmake_on;
    public $botmake_id;
    public $hr22;
    
    public function getIntegrations()
    {
        $integrations = array();
        if ($this->isTawkToIntegrated()) {
            $integrations['tawkto'] = 'Tawk.to';
        }
        if ($this->isCrispIntegrated()) {
            $integrations['crisp'] = 'Crisp';
        }
        if ($this->isIntercomIntegrated()) {
            $integrations['intercom'] = 'Intercom';
        }
        if ($this->isFacebookChatIntegrated()) {
            $integrations['facebook'] = 'Facebook customer chat';
        }
        if ($this->isVkIntegrated()) {
            $integrations['vk'] = 'VK community messages';
        }
        if ($this->isZopimIntegrated()) {
            $integrations['zopim'] = 'Zendesk chat';
        }
        if ($this->isSkypeIntegrated()) {
            $integrations['skype'] = 'Skype web control';
        }
        if ($this->isZaloIntegrated()) {
            $integrations['zalo'] = 'Zalo chat widget';
        }
        if ($this->isLhcIntegrated()) {
            $integrations['lhc'] = 'Live helper chat';
        }
        if ($this->isSmartsuppIntegrated()) {
            $integrations['smartsupp'] = 'Smartsupp';
        }
        if ($this->isLiveChatIntegrated()) {
            $integrations['livechat'] = 'LiveChat';
        }
        if ($this->isTidioIntegrated()) {
            $integrations['tidio'] = 'Tidio';
        }
        if ($this->isLiveChatProIntegrated()) {
            $integrations['livechatpro'] = 'LiveChatPro';
        }
        if ($this->isLiveZillaIntegrated()) {
            $integrations['livezilla'] = 'LiveZilla';
        }
        if ($this->isJivositeIntegrated()) {
            $integrations['jivosite'] = 'Jivosite';
        }
        if ($this->isZohoIntegrated()) {
            $integrations['zoho'] = 'Zoho SalesIQ';
        }
        if ($this->isFreshChatIntegrated()) {
            $integrations['fc'] = 'FreshChat';
        }
        if ($this->isPhpLiveIntegrated()) {
            $integrations['phplive'] = 'PhpLive';
        }
        if ($this->isPaldeskIntegrated()) {
            $integrations['paldesk'] = 'Paldesk';
        }
        if ($this->isHubSpotIntegrated()) {
            $integrations['hubspot'] = 'Hubspot';
        }
        if ($this->isSocialintentsIntegrated()) {
            $integrations['socialintents'] = 'SocialIntents';
        }
        if ($this->isBotmaketIntegrated()) {
            $integrations['botmake'] = 'Botmake.io';
        }
        return $integrations;
    }
    
    public function isBotmaketIntegrated()
    {
        return $this->botmake_on && $this->botmake_id;
    }
    
    public function isSocialintentsIntegrated()
    {
        return $this->socialintents_on && $this->socialintents_id;
    }
    
    public function isHubSpotIntegrated()
    {
        return $this->hubspot_on && $this->hubspot_id;
    }
    
    public function isPhpLiveIntegrated()
    {
        return $this->phplive_on && $this->phplive_src;
    }
    
    public function isPaldeskIntegrated()
    {
        return $this->paldesk_on && $this->paldesk_key;
    }
    
    public function isFreshChatIntegrated()
    {
        return $this->fc_on && $this->fc_token && $this->fc_host;
    }
    
    public function isZohoIntegrated()
    {
        return $this->zoho_on && $this->zoho_id;
    }
    
    public function isJivositeIntegrated()
    {
        return $this->jivosite_on && $this->jivosite_id;
    }
    
    public function getLiveZillaId()
    {
        if (preg_match('{\?id=(.*?)$}is', $this->lz_id, $matches)) {
            return isset($matches[1])? $matches[1] : null;
        }
        return null;
    }
    
    public function isLiveZillaIntegrated()
    {
        return $this->lz_on && $this->lz_id;
    }
    
    public function isLiveChatProIntegrated()
    {
        return $this->lcp_on && $this->lcp_uri;
    }
    
    public function isTidioIntegrated()
    {
        return $this->tidio_on && $this->tidio_key;
    }
    
    public function isLiveChatIntegrated()
    {
        return $this->lc_on && $this->lc_key;
    }
    
    public function isSmartsuppIntegrated()
    {
        return $this->ss_on && $this->ss_key;
    }
    
    public function isLhcIntegrated()
    {
        return $this->lhc_on && $this->lhc_uri;
    }
    
    public function isFacebookChatIntegrated()
    {
        return $this->fb_on && $this->fb_page_id;
    }
    
    public function isTawkToIntegrated()
    {
        $id_lang = Context::getContext()->language->id;
        return $this->tawk_to_on && $this->tawk_to_site_id[$id_lang] && $this->tawk_to_widget[$id_lang];
    }
    
    public function isCrispIntegrated()
    {
        return $this->crisp_on && $this->crisp_site_id;
    }
    
    public function isIntercomIntegrated()
    {
        return $this->intercom_on && $this->intercom_app_id;
    }
    
    public function isVkIntegrated()
    {
        return $this->vk_on && $this->vk_page_id;
    }
    
    public function isZopimIntegrated()
    {
        return $this->zopim_on && $this->zopim_id;
    }
    
    public function isZendeskChat()
    {
        return strpos($this->zopim_id, '-') !== false;
    }
    
    public function isSkypeIntegrated()
    {
        return $this->skype_on && $this->skype_id;
    }
    
    public function isZaloIntegrated()
    {
        return $this->zalo_on && $this->zalo_id;
    }
    
    public function getFormTitle()
    {
        return $this->l('Live chat integrations', 'ArContactUsTawkToConfig');
    }
    
    public function attributeDefaults()
    {
        return array(
            'tawk_to_widget' => 'default',
            'zalo_height' => '420',
            'zalo_width' => '350',
            'lhc_width' => '300',
            'lhc_height' => '190',
            'lhc_popup_height' => '520',
            'lhc_popup_width' => '500',
            'lhc_department' => '1',
            'fb_one_line' => 1,
            'fb_version' => 10,
            'fc_host' => 'https://wchat.freshchat.com'
        );
    }
    
    public function rules()
    {
        return array_merge(parent::rules(), array(
            array(
                array(
                    'zalo_height',
                    'zalo_width',
                ), 'isInt', 'on' => $this->zalo_on
            ),
            array(
                array(
                    'lhc_uri'
                ), 'validateRequired', 'on' => $this->lhc_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'zalo_id'
                ), 'validateRequired', 'on' => $this->zalo_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'ss_key'
                ), 'validateRequired', 'on' => $this->ss_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lc_key'
                ), 'validateRequired', 'on' => $this->lc_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lz_id'
                ), 'validateRequired', 'on' => $this->lz_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lcp_uri',
                ), 'validateRequired', 'on' => $this->lcp_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'lhc_width',
                    'lhc_height',
                    'lhc_popup_height',
                    'lhc_popup_width',
                ), 'isInt', 'on' => $this->lhc_on
            ),
            array(
                array(
                    'tidio_key'
                ), 'validateRequired', 'on' => $this->tidio_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'jivosite_id',
                ), 'validateRequired', 'on' => $this->jivosite_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'phplive_src',
                ), 'validateRequired', 'on' => $this->phplive_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'paldesk_key',
                ), 'validateRequired', 'on' => $this->paldesk_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'hubspot_id',
                ), 'validateRequired', 'on' => $this->hubspot_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            ),
            array(
                array(
                    'socialintents_id',
                ), 'validateRequired', 'on' => $this->socialintents_on, 'message' => $this->l('Field "{label}" is required', 'ArContactUsAbstract')
            )
        ));
    }
}
