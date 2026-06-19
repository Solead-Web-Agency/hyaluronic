{*
* 2007-2013 PrestaShop
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


<div class="infor_template" style="color: green; font-size: 13px; text-align: center; font-weight: bold;"></div>
<div class="tab-content">
	<div id="order_reported-html" class="tab-pane active">
		<div id="deliveryorderautoupdate-email" class="email-collapse panel-collapse collapse in">
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Email Object' mod='deliveryorderautoupdate'}</label>
				<div class="col-lg-6" style="display: flex">
					<div>
					<select name="HL_TRACKING_EMAIL_SUBJECT">
						<option value="fixed" {if $subjectType=='fixed'}selected{/if}>{l s='Fixed' mod='deliveryorderautoupdate'}</option>
						<option value="status" {if $subjectType=='status'}selected{/if}>{l s='Shipping status' mod='deliveryorderautoupdate'}</option>
					</select>
					</div>
					<div style="flex: 1">
					<input type="text" value="{$subject_email|escape:'htmlall':'UTF-8'}" name="object_email" disabled class="form-control">
					</div>
				</div>
				<div class="col-lg-3">
					<button class="btn btn-default edit_subject" label-edit="{l s='Edit' mod='deliveryorderautoupdate'}" label-save="{l s='Save' mod='deliveryorderautoupdate'}" {if $subjectType=='status'}style="display:none"{/if}>{l s='Edit' mod='deliveryorderautoupdate'}</button>
				</div>
			</div>
			{$html_email|escape:'html':'UTF-8'|htmlspecialchars_decode}
		</div>
	</div>
</div>
<input type="hidden" value="/mails/{$lang|escape:'htmlall':'UTF-8'}/{$name|escape:'htmlall':'UTF-8'}" name="link_" />