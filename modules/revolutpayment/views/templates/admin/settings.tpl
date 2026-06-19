{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    Revolut
* @copyright Since 2020 Revolut
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<div class="panel tab-pane fade {if empty($section ) || $section=='settings'}active in{/if}" id="settings" role="tabpanel" aria-labelledby="settings-tab">
    <div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Enable Card Payments?' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="REVOLUT_CARD_METHOD_ENABLE" id="REVOLUT_CARD_METHOD_ENABLE_on"
                               value="1" {if $REVOLUT_CARD_METHOD_ENABLE == 1}checked="checked"{/if} />
						<label for="REVOLUT_CARD_METHOD_ENABLE_on">{l s='Yes' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_CARD_METHOD_ENABLE" id="REVOLUT_CARD_METHOD_ENABLE_off"
                               value="0" {if $REVOLUT_CARD_METHOD_ENABLE == 0}checked="checked"{/if} />
						<label for="REVOLUT_CARD_METHOD_ENABLE_off">{l s='No' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='This controls whether or not "Revolut Card Payments" is enabled within Prestashop.' mod='revolutpayment'}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Enable Revolut Pay?' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="REVOLUT_PAY_METHOD_ENABLE" id="REVOLUT_PAY_METHOD_ENABLE_on" value="1"
                               {if $REVOLUT_PAY_METHOD_ENABLE == 1}checked="checked"{/if} />
						<label for="REVOLUT_PAY_METHOD_ENABLE_on">{l s='Yes' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_PAY_METHOD_ENABLE" id="REVOLUT_PAY_METHOD_ENABLE_off"
                               value="0" {if $REVOLUT_PAY_METHOD_ENABLE == 0}checked="checked"{/if} />
						<label for="REVOLUT_PAY_METHOD_ENABLE_off">{l s='No' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='This controls whether or not "Revolut Pay Button" is enabled within Prestashop.' mod='revolutpayment'}</p>
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-lg-3 required">{l s='Title' mod='revolutpayment'}</label>
                <div class="col-lg-9">
                    <div class="form-group">
                        {foreach from=$languages item=language}
                            <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                 {if $language.id_lang != $id_current_lang}style="display: none;"{/if}>
                                <div class="col-lg-9">
                                    <input type="text"
                                           name="REVOLUT_P_TITLE_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                           id="REVOLUT_P_TITLE_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                           value="{$REVOLUT_P_TITLE[$language.id_lang]|escape:'htmlall':'UTF-8'}"
                                           class="" size="20" required="required"/>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1"
                                            data-toggle="dropdown">
                                        {$language.iso_code|escape:'htmlall':'UTF-8'}
                                        <i class="icon-caret-down"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        {foreach from=$languages item=lang}
                                            <li>
                                                <a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});"
                                                   tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    <p class="help-block">{l s='This controls the title which the user sees during checkout.' mod='revolutpayment'}</p>
                </div>

            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Description' mod='revolutpayment'}</label>
                <div class="col-lg-9">
                    <div class="form-group">
                        {foreach from=$languages item=language}
                            <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                 {if $language.id_lang != $id_current_lang}style="display: none;"{/if}>
                                <div class="col-lg-9">
                                    <input type="text"
                                           name="REVOLUT_P_DESCRIPTION_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                           id="REVOLUT_P_DESCRIPTION_{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                           value="{$REVOLUT_P_DESCRIPTION[$language.id_lang]|escape:'htmlall':'UTF-8'}"
                                           class="" size="20" required="required"/>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1"
                                            data-toggle="dropdown">
                                        {$language.iso_code|escape:'htmlall':'UTF-8'}
                                        <i class="icon-caret-down"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        {foreach from=$languages item=lang}
                                            <li>
                                                <a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});"
                                                   tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    <p class="help-block">{l s='This controls the description which the user sees during checkout.' mod='revolutpayment'}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Mode' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch switch-mode fixed-width-lg">
						<input type="radio" name="REVOLUT_P_SANDBOX" id="REVOLUT_P_SANDBOX_sandbox" value="1"
                               {if $REVOLUT_P_SANDBOX == 1}checked="checked"{/if} />
						<label for="REVOLUT_P_SANDBOX_sandbox">{l s='Sandbox' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_P_SANDBOX" id="REVOLUT_P_SANDBOX_live" value="0"
                               {if $REVOLUT_P_SANDBOX == 0}checked="checked"{/if} />
						<label for="REVOLUT_P_SANDBOX_live">{l s='Live' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='Place the payment gateway in Sandbox mode to test the integration. You can find information about how to do this' mod='revolutpayment'}
                        <a target="_blank"
                           href="https://developer.revolut.com/docs/merchant-api/#revolut-widget-sandbox-environment">{l s='here' mod='revolutpayment'}</a>
                    </p>
                </div>
            </div>

            <div id="REVOLUT_P_APIKEY_content" {if $REVOLUT_P_SANDBOX != 1}style="display: none;"{/if}>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API Key (sandbox mode)' mod='revolutpayment'}</label>
                    <div class="col-lg-9">
                        <input type="password" name="REVOLUT_P_APIKEY" id="REVOLUT_P_APIKEY"
                               value="{$REVOLUT_P_APIKEY|escape:'htmlall':'UTF-8'}" class="" size="20"/>
                        <p class="help-block">{l s='API Key from your Merchant settings on Revolut.' mod='revolutpayment'}</p>
                    </div>
                </div>
            </div>

            <div id="REVOLUT_P_APIKEY_LIVE_content" {if $REVOLUT_P_SANDBOX == 1}style="display: none;"{/if}>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='API Key (live mode)' mod='revolutpayment'}</label>
                    <div class="col-lg-9">
                        <input type="password" name="REVOLUT_P_APIKEY_LIVE" id="REVOLUT_P_APIKEY_LIVE"
                               value="{$REVOLUT_P_APIKEY_LIVE|escape:'htmlall':'UTF-8'}" class="" size="20"/>
                        <p class="help-block">{l s='API Key from your Merchant settings on Revolut.' mod='revolutpayment'}</p>
                    </div>
                </div>
            </div>

            <div class="form-group form-checkbox">
                <label class="control-label col-lg-3">&nbsp;</label>
                <div class="col-lg-9">
                    <input type="checkbox" value="1" name="REVOLUT_P_AUTHORIZE_ONLY"
                           id="REVOLUT_P_AUTHORIZE_ONLY"
                           {if $REVOLUT_P_AUTHORIZE_ONLY == 1}checked="checked"{/if} />
                    <label for="REVOLUT_P_AUTHORIZE_ONLY">{l s='Enable "Authorize Only" mode. This allows the payment to be captured up to 7 days after the user has placed the order (e.g. when the goods are shipped or received). If unchecked, Revolut will try to authorize and capture all payments.' mod='revolutpayment'}</label>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Card Widget Type' mod='revolutpayment'}</label>
                <div class="col-lg-9">
                    <select name="REVOLUT_P_WIDGET_TYPE" class="fixed-width-xl">
                        {foreach from=$payment_widget_types key=id item='type'}
                            <option value="{$id|intval}"
                                    {if $REVOLUT_P_WIDGET_TYPE == $id}selected="selected"{/if}>{$type|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <p class="help-block">
                        {l s='Default widget type : Direct : The card widget will appear under the payment button' mod='revolutpayment'}
                        <br/>
                        {if !$isPs17}
                            {l s='Payment page: The payment button will redirect customer to a new page' mod='revolutpayment'}
                            <br/>
                        {/if}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Order Status for completed orders' mod='revolutpayment'}</label>
                <div class="col-lg-9">
                    <select name="REVOLUT_P_WEBHOOK_STATUS_AUTHORISED" class="fixed-width-xl">
                        {foreach from=$order_statuses item='order_status'}
                            <option value="{$order_status.id|intval}"
                                    {if $REVOLUT_P_WEBHOOK_STATUS_AUTHORISED == $order_status.id}selected="selected"{/if}>{$order_status.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <p class="help-block">{l s='Default status : Payment accepted' mod='revolutpayment'}
                        <br/>{l s='This status will be automatically set to orders that have been completed on Revolut\'s site. This information can only be received if the Webhook has been properly set'  mod='revolutpayment'}
                    </p>
                </div>
            </div>
        </div>

        <div class="panel-footer">
            <button type="submit" value="1" id="configuration_form_submit_btn" name="submitrevolutpayment"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='revolutpayment'}
            </button>
        </div>
    </div>
</div>