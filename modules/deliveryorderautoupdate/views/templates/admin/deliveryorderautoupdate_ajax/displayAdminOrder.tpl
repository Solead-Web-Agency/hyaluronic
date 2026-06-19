{* NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Helloshop
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Helloshop is strictly forbidden.
 * In order to obtain a license, please contact us: modules@helloshop.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par Helloshop
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Helloshop est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter la Helloshop a l'adresse:
 *                  modules@helloshop.com
 * ...........................................................................
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}


		<div class="left-content col-lg-3">
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
		                <span class="wrapper" style="background:#959595">
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
			{/if}
		</div>
		<div class="right-content col-lg-9">
			<ul class="nav nav-tabs" role="tablist">
				<li class="active" role="presentation"><a href="#history" aria-controls="history" role="tab" data-toggle="tab">{l s='Tracking' mod='deliveryorderautoupdate'}</a></li>
				<li role="presentation"><a href="#email_history" aria-controls="email_history" role="tab" data-toggle="tab">{l s='Emails' mod='deliveryorderautoupdate'}</a></li>
			</ul>
			<div class="tab-content" style="margin-top:20px;">
				<div role="tabpanel" class="tab-pane active" id="history">
					{include '../deliveryorderautoupdate_ajax/ajax_trackinghistory.tpl'}
				</div>
				<div role="tabpanel" class="tab-pane" id="email_history">
					{include '../deliveryorderautoupdate_ajax/ajax_emailhistory.tpl'}
				</div>
			</div>
		</div>
