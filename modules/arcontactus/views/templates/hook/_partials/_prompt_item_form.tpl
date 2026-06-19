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
<div class="modal fade" id="arcontactus-prompt-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="arcontactus-prompt-modal-title"></h4>
            </div>
            <form class="form-horizontal form" id="arcontactus-prompt-form" onsubmit="arCU.prompt.save(); return false;">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" name="id" id="arcontactus_prompt_id" data-default="" class="arcontactus-control"/>
                            <input type="hidden" name="id_lang" value="{$defaultFormLanguage|intval}" id="arcontactus_prompt_id_lang" data-serializable="true" class="arcontactus-control"/>
                                    
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="arcontactus_message">{l s='Message' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    {foreach $languages as $language}
                                        <div class="translatable-field row lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                            <div class="col-lg-10">
                                                <textarea name="message_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="arcontactus_prompt_message_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
                                                          data-lang="{$language.id_lang|escape:'htmlall':'UTF-8'}" data-serializable="true" data-default="" class="arcontactus_prompt_message arcontactus-control"></textarea>
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
                                <label class="control-label col-lg-3" for="arcontactus_prompt_id_shop">{l s='Shop context' mod='arcontactus'}</label>
                                <div class="col-lg-9">
                                    <select id="arcontactus_prompt_id_shop" name="id_shop" class="form-control arcontactus-control" data-serializable="true" data-default="0">
                                        <option value="0">{l s='All shops' mod='arcontactus'}</option>
                                        {foreach from=$shops key=id item=shop}
                                            <option value="{$shop.id_shop|escape:'htmlall':'UTF-8'}">{$shop.name|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    <div class="errors"></div>
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