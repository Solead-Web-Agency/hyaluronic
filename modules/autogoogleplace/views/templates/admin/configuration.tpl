{**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2016 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 *}
 
<div class="bootstrap panel">
		
	<div class="well">
		<h1 class="text-center">{l s='Auto Google Place' mod='autogoogleplace'}</h1>
	</div>

	<form action="{$link_form|escape:'htmlall':'UTF-8'}" method="post">
		<fieldset style="max-width: 700px; margin: 2%; padding:1%;">
			<label for="autogoogleplace_check_all" style="width: 100%;"><span>{l s='Google Map API Key' mod='autogoogleplace'}</span>
				<input class="form-control" placeholder="{l s='Enter your API Key here' mod='autogoogleplace'}" type="input" name="autogoogleplace_key" value="{$key|escape:'htmlall':'UTF-8'}"></label>
				<div class="clearfix"></div>
				<hr>

				<div class="alert alert-info">

					{if empty($key)}
					
						<h2>{l s='Installation' mod='autogoogleplace'}</h2>
						<p>{l s='To get an Google API Key, this is really simple :' mod='autogoogleplace'}</p>

						<ul>
							<li>1 - {l s='Go to this Google Developper Console page : ' mod='autogoogleplace'}<a target="_blank" href="https://cloud.google.com/maps-platform/#get-started">{l s='Click here' mod='autogoogleplace'}</a></li>
							<li>2 - {l s='Click on "Places" and follow the instructions.' mod='autogoogleplace'}</li>
							<li>3 - {l s='That\'s it, you have your API, just past it here !' mod='autogoogleplace'}</li>
						</ul>

					{else}
					
						<h2>{l s='Troubleshoot' mod='autogoogleplace'}</h2>

						<ul>
							<li>1 - <a href="https://console.cloud.google.com/project/_/billing/enable?redirect=https%3A%2F%2Fdevelopers.google.com%2Fplaces%2Fweb-service%2Fusage-and-billing%3FdialogOnLoad%3Dbilling-enabled&authuser=0">{l s='Billing have to be enabled on your Google API account since mid-2018. Please check this using this link.' mod='autogoogleplace'}</a></li>
							<li>2 - {l s='Some street names details are not provided by Google, the module cannot provide more informations than it displays.' mod='autogoogleplace'}</li>
							<li>3 - <a href="https://console.cloud.google.com/apis/credentials?authuser=0">{l s='Your API Key need to autorize your website URL, please check this point in Google Console by clicking here.' mod='autogoogleplace'}</a> {l s='The best way is to leave it empty or is to indicate something like :' mod='autogoogleplace'} {$secure_shop_url}</li>
							<li>4 - <a href="https://cloud.google.com/maps-platform/?hl=fr&authuser=0">{l s='APIs may not be active by default, please follow this link to create a functional API key.' mod='autogoogleplace'}</a></li>
						</ul>
					
					{/if}
					
					<p><strong><i>{l s='Please note that the free limit is up to 250 000 asking per month and you must have activated Google Maps (and Places) Javascript API. It must be activated by default when you first login.' mod='autogoogleplace'}</i></strong></p>

				</div>

		</fieldset>
		<fieldset style="max-width: 700px; margin: 2%; padding:1%;">
			<h3>{l s='Where do you want to display automatic suggestion' mod='autogoogleplace'}</h3>
			<p class="alert alert-info">{l s='Please note that you must register a module page in Preference > SEO & URLs if you want to see it here' mod='autogoogleplace'}</p>

			<label for="autogoogleplace_check_all" style="width: 100%;"><span>{l s='Check all' mod='autogoogleplace'}</span>
				<input type="checkbox" name="autogoogleplace_check_all" value="1" class="check"></label>
			<div class="clearfix"></div>
			<p for="autogoogleplace_meta">{l s='Indicate the pages that you do not want to include automatic address suggestions from Google Map :' mod='autogoogleplace'}</p>
			<hr>
			<ul class="list-unstyled">
				{foreach from=$metas item=meta}
					<li class="col-md-4 text-left" style="margin-top: 2%;">
						<input type="checkbox" class="autogoogleplace_metas" name="autogoogleplace_meta[]"{if in_array($meta.id_meta, $included_metas)} checked="checked"{/if} value="{$meta.id_meta|intval}" /> {$meta.title|escape:'htmlall':'UTF-8'} [{$meta.page|escape:'htmlall':'UTF-8'}]
					</li>
				{/foreach}
			</ul>
			<br/>
		</fieldset>
		<fieldset style="max-width: 700px; margin: 2%; padding:1%;">

			<div style="clear:both;"></div>
			<h2>{l s='Options' mod='autogoogleplace'}</h2>
			<div class="clearfix clear"></div>
			<div>
				<hr>
				<label for="autogoogleplace_custom_checkout">{l s='Do you use custom checkout module ?' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_custom_checkout" {if (bool) Configuration::get('AUTOGOOGLEPLACE_CUSTOM_CHECKOUT') == true} checked="checked"{/if} value="1" />
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_active_address2">{l s='Complete second address field' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_active_address2" {if (bool) Configuration::get('AUTOGOOGLEPLACE_ACTIVE_ADDRESS2') == true} checked="checked"{/if} value="1" />
				<small>{l s='Usefull for certain countries.' mod='autogoogleplace'}</small>
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_disable_backoffice">{l s='Disable autocompletion in Back Office' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_disable_backoffice" {if (bool) Configuration::get('AUTOGOOGLEPLACE_DISABLE_BACKOFFICE') == true} checked="checked"{/if} value="1" />
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_load_maps">{l s='Force Google Maps HTML loading' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_load_maps" {if (bool) Configuration::get('AUTOGOOGLEPLACE_LOAD_MAPS') == true} checked="checked"{/if} value="1" />
				<small>{l s='If nothing appears in address field.' mod='autogoogleplace'}</small>
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_force_15">{l s='Force 1.5 Theme mode (not recommanded) ?' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_force_15" {if (bool) Configuration::get('AUTOGOOGLEPLACE_FORCE_15') == true} checked="checked"{/if} value="1" />
				<small>{l s='Can be usefull if your key is entered but it still does not work on old themes configuration' mod='autogoogleplace'}</small>
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_disable_browser_autocomplete">{l s='Disable Google Chrome autocomplete on address line ?' mod='autogoogleplace'} </label>
				<input type="checkbox" name="autogoogleplace_disable_browser_autocomplete" {if (bool) Configuration::get('AUTOGOOGLEPLACE_DISABLE_BROWSER_AUTOCOMPLETE') == true} checked="checked"{/if} value="1" />
			</div>
			<div>
				<hr>
				<label for="autogoogleplace_limit_countries">{l s='Limit address suggestions by countries (up to five) ?' mod='autogoogleplace'} </label>

				<ul class="list-unstyled">
					{foreach from=$countries item=country}
						<li class="col-md-4 text-left" style="margin-top: 2%;">
							<input type="checkbox" class="autogoogleplace_countries" name="autogoogleplace_country[]"{if in_array($country.iso_code|strtolower, $excluded_countries)} checked="checked"{/if} value="{$country.iso_code|strtolower}" /> {$country.name|escape:'htmlall':'UTF-8'}
						</li>
					{/foreach}
				</ul>
			</div>

			<div class="clearfix"></div>
				<hr>
			<p class="text-center">
				<input type="submit" style="margin: 2%;" class="btn btn-default button" name="SubmitAutogoogleplace" value="{l s='Confirm' mod='autogoogleplace'}" />
			</p>

		</fieldset>

	</form>

	{literal}
	<script type="text/javascript">
	$(document).ready(function() {
		
		if ($('.autogoogleplace_metas:checked').length == $('.autogoogleplace_metas').length) {
			$('.check').parent('label').children('span').html("{l s='uncheck all' mod='autogoogleplace'}");
		}
		
		$('.check').toggle(function() {
			$('.autogoogleplace_metas').attr('checked', 'checked');
			$(this).parent('label').children('span').html("{l s='Uncheck all' mod='autogoogleplace'}");
		}, function() {
			$('.autogoogleplace_metas').removeAttr('checked');
			$(this).parent('label').children('span').html("{l s='Check all' mod='autogoogleplace'}");
		});

		function checkCountriesLimit() {

			if ($('.autogoogleplace_countries:checked').length >= 5) {

				$('.autogoogleplace_countries').not(':checked').prop('disabled', true);

			} else {

				$('.autogoogleplace_countries').not(':checked').prop('disabled', false);
			
			}

		}

		// Check on page ready
		checkCountriesLimit();

		$(document).on('click', '.autogoogleplace_countries', function(){
			checkCountriesLimit();
		});

	});
	</script>
	{/literal}

</div>
