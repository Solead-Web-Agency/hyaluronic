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

class ArContactUsButtonConfig extends ArContactUsAbstract
{
    public $mode;
    public $button_icon_type;
    public $button_icon;
    public $button_icon_svg;
    public $button_icon_img;
    public $button_icon_preview;
    public $button_color;
    public $button_size;
    public $button_icon_size;
    public $position;
    public $animation;
    public $x_offset;
    public $y_offset;
    public $pulsate_speed;
    public $icon_speed;
    public $icon_animation_pause;
    public $text;
    public $drag;
    
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array(
            array(
                'button_icon_preview',
            ), $this->button_icon_img? 'safe' : 'unsafe'
        );
        return $rules;
    }
    
    public function getFormTitle()
    {
        return $this->l('Button settings', 'ArContactUsButtonConfig');
    }
    
    public function attributeDefaults()
    {
        return array(
            'button_icon_type' => 'builtin',
            'button_icon' => 'hangouts',
            'button_size' => 'large',
            'button_icon_size' => 24,
            'button_color' => '#008749',
            'position' => 'right',
            'x_offset' => '20',
            'y_offset' => '20',
            'pulsate_speed' => 2000,
            'icon_speed' => 800,
            'icon_animation_pause' => 2000,
            'text' => $this->l('Contact us', 'ArContactUsButtonConfig'),
            'drag' => 0,
        );
    }
    
    public function htmlFields()
    {
        $fields = parent::htmlFields();
        $fields['button_icon'] = $this->module->render('_partials/_icons.tpl', array(
            'icons' => $this->getIcons(),
            'currentValue' => $this->button_icon,
            'name' => 'ARCUB_BUTTON_ICON'
        ));
        $fields['button_icon_preview'] = $this->module->render('_partials/_img_preview.tpl', array(
            'img' => $this->button_icon_img,
            'uploadsUrl' => $this->module->getUploadsUrl()
        ));
        return $fields;
    }
    
    public function htmlAllowedFields()
    {
        return array(
            'button_icon_svg' => true
        );
    }
}
