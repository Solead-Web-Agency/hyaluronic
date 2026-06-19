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

<div class="panel tab-pane fade {if $section=='adv-settings'}active in{/if}" id="adv-settings" role="tabpanel" aria-labelledby="adv-settings-tab">
    <div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Enable automatic refunds' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="REVOLUT_P_AUTO_REFUNDS" id="REVOLUT_P_AUTO_REFUNDS_yes" value="1"
                               {if $REVOLUT_P_AUTO_REFUNDS == 1}checked="checked"{/if} />
						<label for="REVOLUT_P_AUTO_REFUNDS_yes">{l s='Yes' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_P_AUTO_REFUNDS" id="REVOLUT_P_AUTO_REFUNDS_no" value="0"
                               {if $REVOLUT_P_AUTO_REFUNDS == 0}checked="checked"{/if} />
						<label for="REVOLUT_P_AUTO_REFUNDS_no">{l s='No' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='' mod='revolutpayment'}</p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    <div class="alert alert-info advanced-help-message">
                        <span>{l s='With this option enabled, refunding an order in Prestashop will trigger an event on Revolut\'s side.' mod='revolutpayment'}</span>
                        <br/>
                        <span>{l s='For example, by default if you make Partial refund or Product cancellation, an automatic refund will be issued on Revolut\'s side for the corresponding order and amount.' mod='revolutpayment'}</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Customize your order status' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="REVOLUT_P_CUSTOM_STATUS" id="REVOLUT_P_CUSTOM_STATUS_yes" value="1"
                               {if $REVOLUT_P_CUSTOM_STATUS == 1}checked="checked"{/if} />
						<label for="REVOLUT_P_CUSTOM_STATUS_yes">{l s='Yes' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_P_CUSTOM_STATUS" id="REVOLUT_P_CUSTOM_STATUS_no" value="0"
                               {if $REVOLUT_P_CUSTOM_STATUS == 0}checked="checked"{/if} />
						<label for="REVOLUT_P_CUSTOM_STATUS_no">{l s='No' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='You can customize your orders\' status for each possible action in the Revolut payment module.' mod='revolutpayment'}</p>
                </div>
            </div>

            <div id="REVOLUT_P_CUSTOM_STATUS_content"
                 {if $REVOLUT_P_CUSTOM_STATUS == 0}style="display: none;"{/if}>
                <div class="form-group">
                    <div class="col-lg-9 col-lg-offset-3">
                        <div class="alert alert-info advanced-help-message">
                            <span>{l s='With this option enabled, changing the status of an order in Prestashop will trigger an event on Revolut\'s side.' mod='revolutpayment'}</span>
                            <br/>
                            <span>{l s='For example, by default if you update your order status to "Refunded", an automatic refund will be issued on Revolut\'s side for the corresponding order and amount.' mod='revolutpayment'}</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Order Status for triggering the refund on Revolut' mod='revolutpayment'}</label>
                    <div class="col-lg-9">
                        <select name="REVOLUT_P_CUSTOM_STATUS_REFUND" class="fixed-width-xl">
                            {foreach from=$order_statuses item='order_status'}
                                <option value="{$order_status.id|intval}"
                                        {if $REVOLUT_P_CUSTOM_STATUS_REFUND == $order_status.id}selected="selected"{/if}>{$order_status.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">{l s='Default status : Refunded' mod='revolutpayment'}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Order Status for triggering the capture on Revolut' mod='revolutpayment'}</label>
                    <div class="col-lg-9">
                        <select name="REVOLUT_P_CUSTOM_STATUS_CAPTURE" class="fixed-width-xl">
                            {foreach from=$order_statuses item='order_status'}
                                <option value="{$order_status.id|intval}"
                                        {if $REVOLUT_P_CUSTOM_STATUS_CAPTURE == $order_status.id}selected="selected"{/if}>{$order_status.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">{l s='Default status : Payment accepted' mod='revolutpayment'}</p>
                    </div>
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