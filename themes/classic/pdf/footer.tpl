{**
 * [HFM] Pied de page des PDF : ligne légale du vendeur (PS_SHOP_DETAILS sur une ligne) et contact.
 * La phrase « version électronique disponible dans votre compte » est retirée : le site headless
 * ne propose pas de téléchargement des factures dans le compte client.
 *}
<table style="width: 100%;">
	<tr>
		<td style="text-align: center; font-size: 6.5pt; color: #444; width: 100%; line-height: 1.4;">
			{if !empty($shop_details)}
				{$shop_details|escape:'html':'UTF-8'|replace:"\r":''|replace:"\n":' · '}<br />
			{else}
				{$shop_address|escape:'html':'UTF-8'}<br />
			{/if}
			{if !empty($shop_phone)}{l s='Tel: %s' sprintf=[$shop_phone|escape:'html':'UTF-8'] d='Shop.Pdf' pdf='true'}{/if}
			{if !empty($shop_email)} · {$shop_email|escape:'html':'UTF-8'}{/if}
			{if isset($free_text) && $free_text}<br />{$free_text|escape:'html':'UTF-8'}{/if}
		</td>
	</tr>
</table>
