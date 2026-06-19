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

class ArContactUsGeneralConfig extends ArContactUsAbstract
{
    public $mobile;
    public $desktop;
    public $sandbox;
    public $allowed_ips;
    public $font_awesome;
    public $desktop_css;
    public $mobile_css;
    public $button_hook;
    
    public function attributeDefaults()
    {
        return array(
            'mobile' => 1,
            'desktop' => 1,
            'allowed_ips' => $this->getCurrentIP(),
            'button_hook' => 'displayFooter'
        );
    }
    
    public function getFormTitle()
    {
        return $this->l('General settings', 'ArContactUsGeneralConfig');
    }
}
