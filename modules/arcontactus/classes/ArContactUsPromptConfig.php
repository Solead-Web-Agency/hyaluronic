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

class ArContactUsPromptConfig extends ArContactUsAbstract
{
    public $enable_prompt;
    public $prompt_position;
    public $first_delay;
    public $loop;
    public $close_last;
    public $typing_time;
    public $message_time;
    public $show_after_close;
    
    
    public function getFormTitle()
    {
        return $this->l('Prompt settings', 'ArContactUsPromptConfig');
    }
    
    public function attributeDefaults()
    {
        return array(
            'enable_prompt' => 1,
            'first_delay' => '2000',
            'prompt_position' => 'top',
            'loop' => 0,
            'close_last' => 0,
            'typing_time' => '2000',
            'message_time' => '4000',
            'show_after_close' => '0'
        );
    }
}
