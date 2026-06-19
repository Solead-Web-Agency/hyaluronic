{**
 * CREA4YOU CONFIDENTIAL
 * _____________________
 *
 * [2011] - [2019] Crea4You Youness EL GHAZI
 *
 * All Rights Reserved.
 *
 * NOTICE:  All information contained herein is, and remains
 * the property of Crea4You - Youness EL GHAZI and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Crea4You Youness EL GHAZI.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Crea4You Youness EL GHAZI.
 *}

<div class="panel">
	<h3><i class="icon icon-shield"></i> {l s='Shop Protector' mod='shopprotector'}</h3>
	<p>
		<strong>{l s='Protect your shop from thefts' mod='shopprotector'}</strong><br />
		<!--	Uncomment when PRODUCT_ID is know
		<a href="https://addons.prestashop.com/fr/contactez-nous?id_product=PRODUCT_ID">
			{l s='If a relevant feature seems to be missing from this module, click here to submit your ideas' mod='shopprotector'}
		</a>
		-->
	</p>
</div>

{if !empty($success_form)}
<div class="alert alert-success">
	{l s='Settings updated' mod='shopprotector'}
</div>
{elseif isset($success_form) && $success_form === false}
<div class="alert alert-danger">
	{l s='An error occurred while updating settings, please check the form' mod='shopprotector'}
</div>
{/if}
