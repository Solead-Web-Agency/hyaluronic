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
*  @copyright  2018 Azelab
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*}
<table id="arcontactus-table" class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th style="width: 120px">{l s='Position' mod='arcontactus'}</th>
            <th>{l s='Icon' mod='arcontactus'}</th>
            <th>{l s='Color' mod='arcontactus'}</th>
            <th>{l s='Title' mod='arcontactus'}</th>
            <th>{l s='Type' mod='arcontactus'}</th>
            <th>{l s='Display time' mod='arcontactus'}</th>
            <th>{l s='Device' mod='arcontactus'}</th>
            <th>{l s='Show to users' mod='arcontactus'}</th>
            <th style="text-align: center">{l s='Show in product' mod='arcontactus'}</th>
            <th style="text-align: center">{l s='Show in menu' mod='arcontactus'}</th>
            <th style="text-align: center">{l s='QR code' mod='arcontactus'}</th>
            {if $multishop}
                <th>{l s='Shop context' mod='arcontactus'}</th>
            {/if}
            <th style="width: 120px"></th>
        </tr>
    </thead>
    <tbody>
        {if $models}
            {foreach $models as $model}
                <tr data-id="{$model.id_contactus|escape:'htmlall':'UTF-8'}">
                    <td class="pointer dragHandle center positionImage">
                        <div class="dragGroup">
                            <div class="positions">
                                {$model.position|escape:'htmlall':'UTF-8'}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span>
                            {$model.icon_content nofilter}{* contain svg code *}
                        </span>
                    </td>
                    <td>
                        <span class="lbl-color" style="background: {$model.color|escape:'htmlall':'UTF-8'}">{$model.color|escape:'htmlall':'UTF-8'}</span>
                    </td>
                    <td>
                        {$model.title|escape:'htmlall':'UTF-8'}<br/>
                        <small>{$model.subtitle|escape:'htmlall':'UTF-8'}</small>
                    </td>
                    <td>
                        {if $model.type == 0}
                            {if $model.link == '{contact}'}
                                {l s='Contact form' mod='arcontactus'}
                            {else}
                                <a href="{$model.link|escape:'htmlall':'UTF-8'}" target="_blank">
                                    {$model.link|escape:'htmlall':'UTF-8'}
                                </a>
                            {/if}
                        {elseif $model.type == 1}
                            {l s='Integration' mod='arcontactus'}:{$model.integration|escape:'htmlall':'UTF-8'}
                        {elseif $model.type == 2}
                            {l s='Custom JS code' mod='arcontactus'}
                        {elseif $model.type == 3}
                            {l s='Callback form' mod='arcontactus'}
                        {/if}
                    </td>
                    <td>
                        {if $model.always == 1}
                            <span style="color: #00a426" title="{l s='Always display this item' mod='arcontactus'}">
                                <svg aria-hidden="true" data-prefix="fal" data-icon="clock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-clock fa-w-16 fa-3x"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm216 248c0 118.7-96.1 216-216 216-118.7 0-216-96.1-216-216 0-118.7 96.1-216 216-216 118.7 0 216 96.1 216 216zm-148.9 88.3l-81.2-59c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h14c6.6 0 12 5.4 12 12v146.3l70.5 51.3c5.4 3.9 6.5 11.4 2.6 16.8l-8.2 11.3c-3.9 5.3-11.4 6.5-16.8 2.6z"></path></svg>
                            </span>
                        {else}
                            <span style="color: #ff8400" title="{l s='Display this item by schedule' mod='arcontactus'}">
                                <svg aria-hidden="true" data-prefix="fal" data-icon="clock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-clock fa-w-16 fa-3x"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm216 248c0 118.7-96.1 216-216 216-118.7 0-216-96.1-216-216 0-118.7 96.1-216 216-216 118.7 0 216 96.1 216 216zm-148.9 88.3l-81.2-59c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h14c6.6 0 12 5.4 12 12v146.3l70.5 51.3c5.4 3.9 6.5 11.4 2.6 16.8l-8.2 11.3c-3.9 5.3-11.4 6.5-16.8 2.6z"></path></svg>
                            </span>
                        {/if}
                    </td>
                    <td style="text-align: center;">
                        {if $model.display == 1}
                            <span style="color: #00a426" title="{l s='displays on desktop and mobile' mod='arcontactus'}"> 
                                <svg style="display: inline-block" aria-hidden="true" data-prefix="far" data-icon="desktop-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-desktop-alt fa-w-18 fa-3x"><path fill="currentColor" d="M528 0H48C21.5 0 0 21.5 0 48v288c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM48 54c0-3.3 2.7-6 6-6h468c3.3 0 6 2.7 6 6v234H48V54zm432 434c0 13.3-10.7 24-24 24H120c-13.3 0-24-10.7-24-24s10.7-24 24-24h98.7l18.6-55.8c1.6-4.9 6.2-8.2 11.4-8.2h78.7c5.2 0 9.8 3.3 11.4 8.2l18.6 55.8H456c13.3 0 24 10.7 24 24z" class=""></path></svg>
                                <svg title="displays on mobile" style="display: inline-block" aria-hidden="true" data-prefix="fas" data-icon="mobile-android-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-mobile-android-alt fa-w-10 fa-3x"><path fill="currentColor" d="M272 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h224c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-64 452c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12v8zm64-80c0 6.6-5.4 12-12 12H60c-6.6 0-12-5.4-12-12V60c0-6.6 5.4-12 12-12h200c6.6 0 12 5.4 12 12v312z" class=""></path></svg>
                            </span>
                        {elseif $model.display == 2}
                            <span style="color: #7c529d" title="{l s='displays on desktop only' mod='arcontactus'}">
                                <svg aria-hidden="true" data-prefix="far" data-icon="desktop-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-desktop-alt fa-w-18 fa-3x"><path fill="currentColor" d="M528 0H48C21.5 0 0 21.5 0 48v288c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM48 54c0-3.3 2.7-6 6-6h468c3.3 0 6 2.7 6 6v234H48V54zm432 434c0 13.3-10.7 24-24 24H120c-13.3 0-24-10.7-24-24s10.7-24 24-24h98.7l18.6-55.8c1.6-4.9 6.2-8.2 11.4-8.2h78.7c5.2 0 9.8 3.3 11.4 8.2l18.6 55.8H456c13.3 0 24 10.7 24 24z" class=""></path></svg>
                            </span>
                        {elseif $model.display == 3}
                            <span style="color: #ff8400" title="{l s='displays on mobile only' mod='arcontactus'}">
                                <svg aria-hidden="true" data-prefix="fas" data-icon="mobile-android-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-mobile-android-alt fa-w-10 fa-3x"><path fill="currentColor" d="M272 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h224c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-64 452c0 6.6-5.4 12-12 12h-72c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12v8zm64-80c0 6.6-5.4 12-12 12H60c-6.6 0-12-5.4-12-12V60c0-6.6 5.4-12 12-12h200c6.6 0 12 5.4 12 12v312z" class=""></path></svg>
                            </span>
                        {/if}
                    </td>
                    <td style="text-align: center;">
                        {if ($model.registered_only == 0)}
                            <span style="color: #00a426" title="{l s='show for all users' mod='arcontactus'}"> 
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="users" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-users fa-w-20 fa-3x"><path fill="currentColor" d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z" class=""></path></svg>
                            </span>
                        {elseif $model.registered_only == 1}
                            <span style="color: #7c529d" title="{l s='show to logged-in users only' mod='arcontactus'}">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-tie" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-user-tie fa-w-14 fa-3x"><path fill="currentColor" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm95.8 32.6L272 480l-32-136 32-56h-96l32 56-32 136-47.8-191.4C56.9 292 0 350.3 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-72.1-56.9-130.4-128.2-133.8z" class=""></path></svg>
                            </span>
                        {elseif $model.registered_only == 2}
                            <span style="color: #ff8400" title="{l s='show to logged-out users only' mod='arcontactus'}">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-secret" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-user-secret fa-w-14 fa-3x"><path fill="currentColor" d="M383.9 308.3l23.9-62.6c4-10.5-3.7-21.7-15-21.7h-58.5c11-18.9 17.8-40.6 17.8-64v-.3c39.2-7.8 64-19.1 64-31.7 0-13.3-27.3-25.1-70.1-33-9.2-32.8-27-65.8-40.6-82.8-9.5-11.9-25.9-15.6-39.5-8.8l-27.6 13.8c-9 4.5-19.6 4.5-28.6 0L182.1 3.4c-13.6-6.8-30-3.1-39.5 8.8-13.5 17-31.4 50-40.6 82.8-42.7 7.9-70 19.7-70 33 0 12.6 24.8 23.9 64 31.7v.3c0 23.4 6.8 45.1 17.8 64H56.3c-11.5 0-19.2 11.7-14.7 22.3l25.8 60.2C27.3 329.8 0 372.7 0 422.4v44.8C0 491.9 20.1 512 44.8 512h358.4c24.7 0 44.8-20.1 44.8-44.8v-44.8c0-48.4-25.8-90.4-64.1-114.1zM176 480l-41.6-192 49.6 32 24 40-32 120zm96 0l-32-120 24-40 49.6-32L272 480zm41.7-298.5c-3.9 11.9-7 24.6-16.5 33.4-10.1 9.3-48 22.4-64-25-2.8-8.4-15.4-8.4-18.3 0-17 50.2-56 32.4-64 25-9.5-8.8-12.7-21.5-16.5-33.4-.8-2.5-6.3-5.7-6.3-5.8v-10.8c28.3 3.6 61 5.8 96 5.8s67.7-2.1 96-5.8v10.8c-.1.1-5.6 3.2-6.4 5.8z" class=""></path></svg>
                            </span>
                        {/if}
                    </td>
                    <td style="text-align: center;">
                        {if $model.product_page}
                            <a href="#" onclick="arCU.toggleProduct({$model.id_contactus|intval}); return false;" class="label label-success">
                                {l s='Yes' mod='arcontactus'}
                            </a>
                        {else}
                            <a href="#" onclick="arCU.toggleProduct({$model.id_contactus|intval}); return false;" class="label label-danger">
                                {l s='No' mod='arcontactus'}
                            </a>
                        {/if}
                    </td>
                    <td style="text-align: center;">
                        {if $model.status}
                            <a href="#" onclick="arCU.toggle({$model.id_contactus|intval}); return false;" class="label label-success">
                                {l s='Yes' mod='arcontactus'}
                            </a>
                        {else}
                            <a href="#" onclick="arCU.toggle({$model.id_contactus|intval}); return false;" class="label label-danger">
                                {l s='No' mod='arcontactus'}
                            </a>
                        {/if}
                    </td>
                    <td style="text-align: center;">
                        {if $model.data.enable_qr}
                            <span class="ar-qr-preview" onclick="arCU.arCuShowQRCode({$model.id_contactus|intval}); return false;" title="{l s='Show QR code for this menu item' mod='arcontactus'}">
                                <svg aria-hidden="true" focusable="false" data-prefix="fal" data-icon="qrcode" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-qrcode fa-w-14 fa-3x"><path fill="currentColor" d="M0 224h192V32H0v192zM32 64h128v128H32V64zm224-32v192h192V32H256zm160 160H288V64h128v128zM0 480h192V288H0v192zm32-160h128v128H32V320zM64 96h64v64H64V96zm320 64h-64V96h64v64zM64 352h64v64H64v-64zm352-64h32v128H320v-32h-32v96h-32V288h96v32h64v-32zm0 160h32v32h-32v-32zm-64 0h32v32h-32v-32z" class=""></path></svg>
                            </span>
                        {/if}
                    </td>
                    {if $multishop}
                        <td>
                            {if $model.id_shop == 0}
                                {l s='All shops' mod='arcontactus'}
                            {else}
                                {if isset($shops[$model.id_shop])}
                                    <a href="{$link->getPageLink("index", true, null, null, false, $model.id_shop)|escape:'htmlall':'UTF-8'}" target="_blank">
                                        {$shops[$model.id_shop].name|escape:'htmlall':'UTF-8'}
                                    </a>
                                {else}
                                    [{l s='Deleted' mod='arcontactus'}]
                                {/if}
                            {/if}
                        </td>
                    {/if}
                    <td>
                        <div class="btn-group pull-right">
                            <a href="#" title="{l s='Edit' mod='arcontactus'}" onclick="arCU.edit({$model.id_contactus|intval}); return false;" class="edit btn btn-default" data-id="{$model.id_contactus|intval}">
                                <i class="icon-pencil"></i> {l s='Edit' mod='arcontactus'}
                            </a>
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-caret-down"></i>&nbsp;
                            </button>
                            <ul class="dropdown-menu">
                                <li>    
                                    <a href="#" title="{l s='Delete' mod='arcontactus'}" onclick="arCU.remove({$model.id_contactus|intval}); return false;" data-id="{$model.id_contactus|intval}" class="delete">
                                        <i class="icon-trash"></i> {l s='Delete' mod='arcontactus'}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="7">
                    {l s='No items found' mod='arcontactus'}
                </td>
            </tr>
        {/if}
    </tbody>
</table>