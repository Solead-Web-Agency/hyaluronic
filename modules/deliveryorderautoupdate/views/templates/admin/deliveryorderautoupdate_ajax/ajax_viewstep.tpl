{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
<style type="text/css">

.action-truck8 {
    background: #73c9e8 none repeat scroll 0 0;
}
.left-content .title{
    background:#99CCFF;
    text-transform:uppercase;
    font-size:12px;
    font-weight:bold;
    padding:5px;
    display:flex;

    -webkit-box-align: space-between;
    -webkit-align-items: space-between;
    -ms-flex-align: space-between;
    justify-content: space-between;

    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
}
.left-content .title span{
    display:inline-block;
    background:#008000;
    padding:5px;
    border-radius:5px;
}
.left-content .step-list .item-rows {
    display:flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    padding:6px 0;
}
.left-content .step-list .item-rows .icon {
    position:relative;
}
.left-content .step-list .item-rows .icon span {
    width:45px;
    height:45px;
    line-height:46px;
    text-align:center;
    border-radius:50%;
    border:1px solid #ebebeb;
    position:relative;
    z-index:2;
    display:block;
    color:#fff;
}
.left-content .step-list .item-rows .icon span img{
    vertical-align: unset;
    width: 100%;
    padding: 8px;
}
.left-content .step-list .item-rows .icon:before {
    content:"";
    position:absolute;
    top:-15px;
    left:50%;
    height:calc(100% + 30px);
    width:2px;
    display:block;
    background:#ebebeb;
    z-index:1;
}
#steps_conf .title {
    color:#fff;
}
.left-content .step-list .item-rows .text {
    padding-left:15px;
}
.left-content .step-list .item-rows .text span {
    display:block;
}
</style>
<div id="steps_conf">
        <div class="left-content">
            {if $history_left}
            <div class="title">
                {assign var=event_status value="_"|explode:$history_left[0]['result']}
                {l s='Last Step' mod='deliveryorderautoupdate'}: <span style="background:{$statuses[$history_left[0]['event_code']]->color|escape:'htmlall':'UTF-8'}">{$event_status[1]|escape:'htmlall':'UTF-8'}</span>
            </div>
            <div class="step-list">
            {foreach $history_left as $i => $hs}
            {assign var=event_code_left value="_"|explode:$hs.result}
            <div class="item-rows">
                <div class="flex-box item-rows">
                    <div class="icon">
                        {if $i == 0}
                        <span class="wrapper" style="background:{$statuses[$event_code_left[0]]->color|escape:'htmlall':'UTF-8'}">
                            <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/{$statuses[$event_code_left[0]]->id_status|escape:'htmlall':'UTF-8'}.png"  />
                        </span>
                        {else}
                        <span class="wrapper" style="background:#C8C8C8">
                            <img src="{$url_root|escape:'htmlall':'UTF-8'}modules/deliveryorderautoupdate/views/img/steps/check.png"  />
                        </span>
                        {/if}
                    </div>
                    <div class="text">
                        <div style="font-weight:bold; text-align: left"> {$event_code_left[1]|escape:'htmlall':'UTF-8'} </div>
                        <div>
                            <span class="date">{$hs.step_date|escape:'htmlall':'UTF-8'}</span><span class="time">{$hs.step_time|escape:'htmlall':'UTF-8'}</span>
                        </div>
                    </div>
                </div>
            </div>
            {/foreach}
            </div>
            {else}
            <div class="title">
                {l s='Last Step' mod='deliveryorderautoupdate'}: <span style="background:#a59fb5">{l s='No status yet' mod='deliveryorderautoupdate'}</span>
            </div>
            {/if}
        </div>
    </div>