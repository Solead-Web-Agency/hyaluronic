{*
* 2017 Azelab
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
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<div class="row">
    <div class="col-sm-8 arcu-feature-list">
        <div class="row">
            <div class="col-sm-4">
                {l s='Name' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                {$model->name|escape:'htmlall':'UTF-8'}
            </div>
        </div>
        {if $model->email}
            <div class="row">
                <div class="col-sm-4">
                    {l s='Email' mod='arcontactus'}
                </div>
                <div class="col-sm-8">
                    <a href="mailto:{$model->email|escape:'htmlall':'UTF-8'}" target="_blank">{$model->email|escape:'htmlall':'UTF-8'}</a>
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-sm-4">
                {l s='Phone' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                {$model->phone|escape:'htmlall':'UTF-8'}
            </div>
        </div>
        {if $model->referer}
            <div class="row">
                <div class="col-sm-4">
                    {l s='Referer' mod='arcontactus'}
                </div>
                <div class="col-sm-8">
                    <a href="{$model->referer|escape:'htmlall':'UTF-8'}" target="_blank">{$model->referer|escape:'htmlall':'UTF-8'}</a>
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-sm-4">
                {l s='Created at' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                {$model->created_at|escape:'htmlall':'UTF-8'}
            </div>
        </div>
        {if $model->updated_at and $model->updated_at != '0000-00-00 00:00:00'}
        <div class="row">
            <div class="col-sm-4">
                {l s='Updated at' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                {$model->updated_at|escape:'htmlall':'UTF-8'}
            </div>
        </div>
        {/if}
        <div class="row">
            <div class="col-sm-4">
                {l s='Status' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                <a href="#" data-status="0" data-id="{$model->id|intval}" onclick="arCU.callback.toggle({$model->id|intval},0); return false;" style="margin-right: 3px" class="arcu-status-label label {if $model->status == 0}label-danger{else}label-default{/if}">
                    {l s='New' mod='arcontactus'}
                </a>

                <a href="#" data-status="1" data-id="{$model->id|intval}" onclick="arCU.callback.toggle({$model->id|intval},1); return false;" style="margin-right: 3px" class="arcu-status-label label {if $model->status == 1}label-success{else}label-default{/if}">
                    {l s='Done' mod='arcontactus'}
                </a>

                <a href="#" data-status="2" data-id="{$model->id|intval}" onclick="arCU.callback.toggle({$model->id|intval},2); return false;" class="arcu-status-label label {if $model->status == 2}label-warning{else}label-default{/if}">
                    {l s='Ignored' mod='arcontactus'}
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4">
                {l s='Comment' mod='arcontactus'}
            </div>
            <div class="col-sm-8">
                <textarea rows="10" class="form-control" id="arcu-comment">{$model->comment|escape:'htmlall':'UTF-8'}</textarea>
                <div class="text-right" style="margin-top: 2px">
                    <button class="btn btn-success" onclick="arCU.callback.saveComment({$model->id|intval}, $('#arcu-comment').val());">{l s='Save' mod='arcontactus'}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="arcu-qr-code-buttons">
            <button class="btn btn-default {if $channel == 'phone'}btn-success{/if}" data-channel="phone" type="button" onclick="arCU.reloadQRCode('{$links.phone|escape:'htmlall':'UTF-8'}', '{l s='Scan QR code on mobile device to direct call' mod='arcontactus'}', 'phone', '{$model->id|intval}')">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="phone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-phone fa-w-16 fa-3x"><path fill="currentColor" d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z" class=""></path></svg>
                {l s='Phone call' mod='arcontactus'}
            </button>
            <button class="btn btn-default {if $channel == 'whatsapp'}btn-success{/if}" data-channel="whatsapp" type="button" onclick="arCU.reloadQRCode('{$links.whatsApp|escape:'htmlall':'UTF-8'}', '{l s='Scan QR code on mobile device to WhatsApp chat' mod='arcontactus'}', 'whatsapp', '{$model->id|intval}')">
                <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="whatsapp" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-whatsapp fa-w-14 fa-3x"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" class=""></path></svg>
                {l s='WhatsApp' mod='arcontactus'}
            </button>
            <button class="btn btn-default {if $channel == 'viber'}btn-success{/if}" data-channel="viber" type="button" onclick="arCU.reloadQRCode('{$links.viber|escape:'htmlall':'UTF-8'}', '{l s='Scan QR code on mobile device to Viber chat' mod='arcontactus'}', 'viber', '{$model->id|intval}')">
                <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="viber" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-viber fa-w-16 fa-3x"><path fill="currentColor" d="M444 49.9C431.3 38.2 379.9.9 265.3.4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117 224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4 125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1 4.8-11-5.7 0-6.9.4-85.7.4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9.4 143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9.6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4.7-12.9 27.9 1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5.9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1 8.6-12.9 8.7-12.9.1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7.5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8 33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41 33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8 67.3 84.3 84.3c0 0 9.1.8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z" class=""></path></svg>
                {l s='Viber' mod='arcontactus'}
            </button>
        </div>
        <img src="{$qrcodeFile|escape:'htmlall':'UTF-8'}" id="arcu-qr-code" style="border: 1px solid #DDD; max-width: 100%" />
        <p>
            {l s='Scan this QR code to perform action on mobile device' mod='arcontactus'}
        </p>
    </div>
</div>