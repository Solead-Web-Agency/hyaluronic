{*
* 2018 Azelab
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
<div class="modal fade" id="arcontactus-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" id="arcontactus-modal-title"></div>
            </div>
            <form class="form-horizontal form" id="arcontactus-form" onsubmit="arCU.save(); return false;">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" name="id" id="arcontactus_id" data-default="" class="arcontactus-control"/>
                            <input type="hidden" name="id_lang" value="{$defaultFormLanguage|intval}" id="arcontactus_id_lang" data-serializable="true" class="arcontactus-control"/>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_icon_type">{l s='Icon type' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_icon_type" name="icon_type" class="form-control arcontactus-control" data-serializable="true" data-default="builtin">
                                        <option value="builtin">{l s='Built-in' mod='arcontactus'}</option>
                                        <option value="fa">{l s='FontAwesome icon code' mod='arcontactus'}</option>
                                        <option value="uploaded">{l s='Uploaded image' mod='arcontactus'}</option>
                                    </select>
                                </div>
                            </div>
                            <div id="fa5">
                                <input type="hidden" id="arcontactus_icon" data-default="" name="icon" data-serializable="true" class="arcontactus-control" />
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="arcontactus_content">{l s='Search icon' mod='arcontactus'}</label>
                                    <div class="col-sm-9">
                                        <input placeholder="Search" data-default="" onkeyup="arcontactusFindIcon();" type="text" id="fa5-search" class="form-control" />
                                    </div>
                                </div>
                                <p class="text-right">
                                    {l s='Icons:' mod='arcontactus'} <span id="fa5-count">929</span>
                                </p>
                                <div class="form-group" id="fa5-container" style="overflow-y: scroll; overflow-x: hidden; max-height: 400px;"> 
                                    <ul class="list-unstyled arcu-icon-list">
                                        {foreach from=$icons key=id item=icon}
                                        <li class="col-sm-2" data-id="{$id|escape:'htmlall':'UTF-8'}">
                                            <div>
                                                {$icon nofilter}{* SVG content. Escaping will break functionality *}
                                                <div class="w-100 ph1 pv2 tc f2">
                                                    <span class="icon-title">{$id|escape:'htmlall':'UTF-8'}</span>
                                                </div>
                                            </div>
                                        </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group" id="fontawesome-group">
                                <label class="control-label col-lg-3" for="arcontactus_icon_svg">{l s='FontAwesome icon code' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <textarea id="arcontactus_icon_svg" name="icon_svg" class="form-control arcontactus-control" data-serializable="true" data-default=""></textarea>
                                    <div class="errors"></div>
                                </div>
                            </div>
                            <div class="form-group" id="uploaded-group">
                                <label class="control-label col-sm-3">{l s='Custom image' mod='arcontactus'}</label>
                                <input type="hidden" name="icon_img" id="arcontactus_icon_img" data-serializable="true" data-default="" />
                                <div class="col-sm-9 img-upload">
                                    {$iconImageUploader nofilter}
                                    <div id="arcontactus_uploaded_img_list"></div>
                                    <div class="errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_no_container">{l s='Remove circle around icon' mod='arcontactus'}</label>
                                <input type="hidden" value="" id="arcontactus_no_container" name="no_container" data-serializable="true" data-default="" />
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" id="ARCU_no_container_on" name="_no_container" value="1">
                                        <label for="ARCU_no_container_on">{l s='Yes' mod='arcontactus'}</label>
                                        <input type="radio" id="ARCU_no_container_off" name="_no_container" value="" checked="checked">
                                        <label for="ARCU_no_container_off">{l s='No' mod='arcontactus'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_color">{l s='Color' mod='arcontactus'}</label>
                                <div class="col-lg-4">
                                    <div class="input-group">
                                        <input data-hex="true" class="color mColorPickerInput mColorPicker arcontactus-control" data-default="" data-serializable="true" name="color" value="" id="arcontactus_color" type="color">
                                    </div>
                                    <div class="errors"></div>
                                    <p class="help-block">
                                        {l s='Color applicable only for SVG files (that supports color changing) and FontAwesome icon' mod='arcontactus'}
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_title">{l s='Title' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    {foreach $languages as $language}
                                        <div class="translatable-field row lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                            <div class="col-lg-10">
                                                <input name="title_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="arcontactus_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
                                                       data-lang="{$language.id_lang|escape:'htmlall':'UTF-8'}" value="" data-serializable="true" data-default="" class="arcontactus_title arcontactus-control" type="text" />
                                            </div>
                                            <div class="col-lg-2">
                                                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                            {$language.iso_code|escape:'htmlall':'UTF-8'}
                                                            <i class="icon-caret-down"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                            {foreach from=$languages item=language}
                                                            <li><a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});" data-lang="{$language.id_lang|intval}" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
                                                            {/foreach}
                                                    </ul>
                                            </div>
                                        </div>
                                    {/foreach}
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_subtitle">{l s='Subtitle' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    {foreach $languages as $language}
                                        <div class="translatable-field row lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                            <div class="col-lg-10">
                                                <input name="subtitle_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="arcontactus_subtitle_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
                                                       data-lang="{$language.id_lang|escape:'htmlall':'UTF-8'}" value="" data-serializable="true" data-default="" class="arcontactus_subtitle arcontactus-control" type="text" />
                                            </div>
                                            <div class="col-lg-2">
                                                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                            {$language.iso_code|escape:'htmlall':'UTF-8'}
                                                            <i class="icon-caret-down"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                            {foreach from=$languages item=language}
                                                            <li><a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});" data-lang="{$language.id_lang|intval}" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
                                                            {/foreach}
                                                    </ul>
                                            </div>
                                        </div>
                                    {/foreach}
                                    <div class="errors"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_product_page">{l s='Display item in product actions' mod='arcontactus'}</label>
                                <input type="hidden" value="" id="arcontactus_product_page" name="product_page" data-serializable="true" data-default="" />
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" id="ARCU_product_page_on" name="_product_page" value="1">
                                        <label for="ARCU_product_page_on">{l s='Yes' mod='arcontactus'}</label>
                                        <input type="radio" id="ARCU_product_page_off" name="_product_page" value="" checked="checked">
                                        <label for="ARCU_product_page_off">{l s='No' mod='arcontactus'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>  
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_display">{l s='Display on' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_display" name="display" class="form-control arcontactus-control" data-serializable="true" data-default="1">
                                        <option value="1">{l s='Desktop and mobile' mod='arcontactus'}</option>
                                        <option value="2">{l s='Desktop only' mod='arcontactus'}</option>
                                        <option value="3">{l s='Mobile only' mod='arcontactus'}</option>
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_registered_only">{l s='Display for' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_registered_only" name="registered_only" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        <option value="0">{l s='All users' mod='arcontactus'}</option>
                                        <option value="1">{l s='Logged-in users only' mod='arcontactus'}</option>
                                        <option value="2">{l s='Not logged-in users only' mod='arcontactus'}</option>
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3">{l s='Display for group' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    {foreach $groups as $group}
                                        <label class="control-label">
                                            <input data-serializable="true" id="arcontactus_groups_{$group.id_group}" data-default="0" name="groups[{$group.id_group}]" type="checkbox" value="1" />
                                            {$group.name}
                                        </label><br/>
                                    {/foreach}
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_type">{l s='Action' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_type" name="type" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        <option value="0">{l s='Link' mod='arcontactus'}</option>
                                        <option value="1">{l s='Integration' mod='arcontactus'}</option>
                                        <option value="2">{l s='Custom JS code' mod='arcontactus'}</option>
                                        <option value="3">{l s='Callback form' mod='arcontactus'}</option>
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group" id="arcu-link-group">
                                <label class="control-label col-lg-3" for="arcontactus_link">{l s='Link' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <input type="text" id="arcontactus_link" name="link" class="form-control arcontactus-control" data-serializable="true" data-default="" />
                                    <div class="errors"></div>
                                    <p class="help-block">
                                        {l s='You can set absolute or relative URL. Or you can use one of these tags:' mod='arcontactus'}<br/>
                                        {l s='{contact} - will be replaced to multilang contact-us form page on your site' mod='arcontactus'}<br/>
                                    </p>
                                </div>
                            </div>
                                    
                            <div class="form-group" id="arcu-target-group">
                                <label class="control-label col-lg-3" for="arcontactus_target">{l s='Target' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_target" name="target" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        <option value="0">{l s='New window' mod='arcontactus'}</option>
                                        <option value="1">{l s='Same window' mod='arcontactus'}</option>
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group" id="arcu-integration-group">
                                <label class="control-label col-lg-3" for="arcontactus_integration">{l s='Integration' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_integration" name="integration" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        {foreach from=$integrations key=id item=integration}
                                            <option value="{$id|escape:'htmlall':'UTF-8'}">{$integration|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_always">{l s='Always display this item' mod='arcontactus'}</label>
                                <input type="hidden" value="" id="arcontactus_always" name="always" data-serializable="true" />
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" id="ARCU_ALWAYS_on" name="_always" value="1" checked="checked">
                                        <label for="ARCU_ALWAYS_on">{l s='Yes' mod='arcontactus'}</label>
                                        <input type="radio" id="ARCU_ALWAYS_off" name="_always" value="">
                                        <label for="ARCU_ALWAYS_off">{l s='No' mod='arcontactus'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>        
                            
                            <div id="arcu-schedule-group">
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="arcontactus_days">{l s='Display days' mod='arcontactus'}</label>
                                    <div class="col-lg-9">
                                        <label class="cbx"><input name="d1" type="checkbox" id="arcontactus_d1" value="1" data-serializable="true" data-default="1" /> {l s='Mon' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d2" id="arcontactus_d2" value="1" data-serializable="true" data-default="1" /> {l s='Tue' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d3" id="arcontactus_d3" value="1" data-serializable="true" data-default="1" /> {l s='Wed' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d4" id="arcontactus_d4" value="1" data-serializable="true" data-default="1" /> {l s='Thu' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d5" id="arcontactus_d5" value="1" data-serializable="true" data-default="1" /> {l s='Fri' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d6" id="arcontactus_d6" value="1" data-serializable="true" data-default="1" /> {l s='Sat' mod='arcontactus'}</label>
                                        <label class="cbx"><input type="checkbox" name="d7" id="arcontactus_d7" value="1" data-serializable="true" data-default="1" /> {l s='Sun' mod='arcontactus'}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="arcontactus_time">{l s='Display time' mod='arcontactus'}</label>
                                    <div class="col-lg-5">
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <input type="text" name="time_from" id="arcontactus_time_from" data-serializable="true" data-default="00:00:00" class="form-control time-mask" />
                                            </div>
                                            <div class="col-sm-1 text-center" style="padding-top: 6px;"> - </div>
                                            <div class="col-sm-5">
                                                <input type="text" name="time_to" id="arcontactus_time_to" data-serializable="true" data-default="23:59:59" class="form-control time-mask" />
                                            </div>
                                        </div>
                                        <div class="errors"></div>
                                    </div>
                                    <div class="col-lg-4 text-right" style="padding-top: 6px;">
                                        {l s='Current server time:' mod='arcontactus'} <span id="server-time"></span>
                                    </div>
                                </div>
                            </div>
                                    
                            <div class="form-group" id="arcu-id_shop">
                                <label class="control-label col-lg-3" for="arcontactus_id_shop">{l s='Shop context' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_id_shop" name="id_shop" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        <option value="0">{l s='All shops' mod='arcontactus'}</option>
                                        {foreach from=$shops key=id item=shop}
                                            <option value="{$shop.id_shop|escape:'htmlall':'UTF-8'}">{$shop.name|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    <div class="errors"></div>
                                </div>
                            </div>
                                        
                            <div class="form-group" id="arcu-js-group">
                                <label class="control-label col-lg-3" for="arcontactus_js">{l s='Custom javascript' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <textarea type="text" id="arcontactus_js" name="js" class="form-control arcontactus-control" data-serializable="true" data-default=""></textarea>
                                    <p class="help-block">
                                        {l s='JavaScript code to run onclick. Please type here JavaScript code without "script" tag' mod='arcontactus'}<br/>
                                    </p>
                                    <div class="errors"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_enable_qr">{l s='Display QR code for this item' mod='arcontactus'}</label>
                                <input type="hidden" value="" id="arcontactus_enable_qr" name="enable_qr" data-serializable="true" data-default="" />
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" id="ARCU_enable_qr_on" name="_enable_qr" value="1">
                                        <label for="ARCU_enable_qr_on">{l s='Yes' mod='arcontactus'}</label>
                                        <input type="radio" id="ARCU_enable_qr_off" name="_enable_qr" value="" checked="checked">
                                        <label for="ARCU_enable_qr_off">{l s='No' mod='arcontactus'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>
                            <div id="arcu-qr-group">
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="arcontactus_qr_title">{l s='QR code popup title' mod='arcontactus'}</label>
                                    <div class="col-lg-9">
                                        {foreach $languages as $language}
                                            <div class="translatable-field row lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                <div class="col-lg-10">
                                                    <input name="qr_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="arcontactus_qr_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
                                                           data-lang="{$language.id_lang|escape:'htmlall':'UTF-8'}" value="" data-serializable="true" data-default="" class="arcontactus_qr_title arcontactus-control" type="text" />
                                                </div>
                                                <div class="col-lg-2">
                                                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                                                <i class="icon-caret-down"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                                {foreach from=$languages item=language}
                                                                <li><a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});" data-lang="{$language.id_lang|intval}" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
                                                                {/foreach}
                                                        </ul>
                                                </div>
                                            </div>
                                        {/foreach}
                                        <p class="help-block">
                                            {l s='Leave this field blank if you want use the same title as menu item' mod='arcontactus'}
                                        </p>
                                        <div class="errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="arcontactus_qr_link">{l s='QR code custom link' mod='arcontactus'}</label>
                                    <div class="col-lg-9">
                                        <input type="text" id="arcontactus_qr_link" name="qr_link" class="form-control arcontactus-control" data-serializable="true" data-default="" />
                                        <p class="help-block">
                                            {l s='Leave this field blank if you want use the same link as menu item' mod='arcontactus'}<br/>
                                            {l s='This field is required if "action" of this menu item is not equals "link"' mod='arcontactus'}<br/>
                                        </p>
                                        <div class="errors"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='arcontactus'}</button>
                    <button type="submit" class="btn btn-primary">{l s='Save' mod='arcontactus'}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener('load', function(){
        $('.arcu-icon-list li').click(function(){
            $('.arcu-icon-list li.active').removeClass('active');
            $(this).addClass('active');
            $('#arcontactus_icon').val($(this).data('id'));
        });
        $('#arcontactus_type').change(function(){
            arcontactusChangeType();
        });
        $('#arcontactus_icon_type').change(function(){
            arcontactusChangeIconType();
        });
        arcontactusChangeType();
        arcontactusChangeIconType();
    });
    function arcontactusChangeIconType(){
        var val = $('#arcontactus_icon_type').val();
        if (val == 'builtin'){
            $('#fa5').removeClass('hidden');
            $('#fontawesome-group').addClass('hidden');
            $('#uploaded-group').addClass('hidden');
        }else if(val == 'fa'){
            $('#fa5').addClass('hidden');
            $('#fontawesome-group').removeClass('hidden');
            $('#uploaded-group').addClass('hidden');
        } else if (val == 'uploaded'){
            $('#fa5').addClass('hidden');
            $('#fontawesome-group').addClass('hidden');
            $('#uploaded-group').removeClass('hidden');
        }
    }
    function arcontactusChangeType(){
        var val = $('#arcontactus_type').val();
        switch(val){
            case "0": // link
                $('#arcu-link-group').removeClass('hidden');
                $('#arcu-target-group').removeClass('hidden');
                $('#arcu-integration-group').addClass('hidden');
                break;
            case "1": // integration
                $('#arcu-link-group').addClass('hidden');
                $('#arcu-target-group').addClass('hidden');
                //$('#arcu-js-group').addClass('hidden');
                $('#arcu-integration-group').removeClass('hidden');
                break;
            case "2": // js
                $('#arcu-link-group').addClass('hidden');
                $('#arcu-target-group').addClass('hidden');
                //$('#arcu-js-group').removeClass('hidden');
                $('#arcu-integration-group').addClass('hidden');
                break;
            case "3": // callback
                $('#arcu-link-group').addClass('hidden');
                $('#arcu-target-group').addClass('hidden');
                //$('#arcu-js-group').addClass('hidden');
                $('#arcu-integration-group').addClass('hidden');
                break;
        }
    }
    function arcontactusFindIcon(){
        var val = $('#fa5-search').val();
        $('#fa5 .icon-title').each(function(){
            if ($(this).text().indexOf(val) !== -1){
                $(this).parents('li').removeClass('hidden');
            }else{
                $(this).parents('li').addClass('hidden');
            }
        });
        arcontactusUpdateIconsCount();
    }
    
    function arcontactusUpdateIconsCount(){
        $('#fa5-count').text($('#fa5 li:not(.hidden)').length);
    }
</script>