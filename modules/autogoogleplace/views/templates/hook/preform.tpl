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

	{if !empty($err_conf)}
		<div class="alert alert-warning">
			<ul>
				{foreach $err_conf as $c}
					<li>{$c|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul>
		</div>
	{/if}

	<div class="well">

		{if empty($key)}
		
			<h2>{l s='Installation' mod='autogoogleplace'}</h2>
			<p>{l s='To get an Google API Key, this is really simple :' mod='autogoogleplace'}</p>

			<ul>
				<li>1 - {l s='Go to this Google Developper Console page : ' mod='autogoogleplace'}<a target="_blank" href="https://cloud.google.com/maps-platform/#get-started">{l s='Click here' mod='autogoogleplace'}</a></li>
				<li>2 - {l s='Click on "Places" and follow the instructions.' mod='autogoogleplace'}</li>
				<li>3 - {l s='That\'s it, you have your API, just past it here !' mod='autogoogleplace'}</li>
			</ul>

		{else}

			<div id="gmaps-api-results">
				<h2>{l s='API Status Connexion Check :' mod='autogoogleplace'}</h2>
				<div id="gmaps-api-generic-success" style="display: none;" class="alert alert-success">{l s='API seems connected properly and working fine.' mod='autogoogleplace'}</div>
				<div id="gmaps-api-generic-error" style="display: none;" class="alert alert-warning">{l s='API Cannot load properly. Permissions for this domain, API not activated or lack of billing might be the root cause of this.' mod='autogoogleplace'}</div>
				<div id="gmaps-api-loading">{l s='Loading ...' mod='autogoogleplace'}</div>
			</div>
		
			<div id="gmap-faq" style="display: none; padding: 30px;">
				
				<h3>{l s='Troubleshoot' mod='autogoogleplace'}</h3>

				<ul>
					<li>1 - <a href="https://console.cloud.google.com/project/_/billing/enable?redirect=https%3A%2F%2Fdevelopers.google.com%2Fplaces%2Fweb-service%2Fusage-and-billing%3FdialogOnLoad%3Dbilling-enabled&authuser=0">{l s='Billing have to be enabled on your Google API account since mid-2018. Please check this using this link.' mod='autogoogleplace'}</a></li>
					<li>2 - {l s='Some street names details are not provided by Google, the module cannot provide more informations than it displays.' mod='autogoogleplace'}</li>
					<li>3 - <a href="https://console.cloud.google.com/apis/credentials?authuser=0">{l s='Your API Key need to autorize your website URL, please check this point in Google Console by clicking here.' mod='autogoogleplace'}</a> {l s='The best way is to leave it empty or is to indicate something like :' mod='autogoogleplace'} "{$secure_shop_url}" {l s='or' mod='autogoogleplace'} "{$secure_domain_url}"</li>
					<li>4 - <a href="https://cloud.google.com/maps-platform/">{l s='APIs (Place API, Maps Javascript API) may not be active by default, please follow this link to create a functional API key.' mod='autogoogleplace'}</a></li>
				</ul>

				<p><strong><i>{l s='Please note that the free limit is up to 250 000 asking per month and you must have activated Google Maps (and Places) Javascript API. It must be activated by default when you first login.' mod='autogoogleplace'}</i></strong></p>

			</div>
		
			<input type="text" id="gmap-test-input" name="gmap-test-input" style="display: none;" />

		{/if}
		

	</div>
	
</div>