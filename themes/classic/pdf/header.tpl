{**
 * [HFM] En-tête des PDF (facture, avoir, bon de livraison) sur le modèle de Filler Distribution :
 * logo + titre/date/numéro, puis l'identité légale du vendeur (PS_SHOP_DETAILS : raison sociale,
 * SIRET, APE, RCS, TVA intracommunautaire) — mentions obligatoires d'une facture.
 *}
<table style="width: 100%">
<tr>
	<td style="width: 50%">
		{if $logo_path}
			<img src="{$logo_path}" style="width:{$width_logo}px; height:{$height_logo}px;" />
		{/if}
	</td>
	<td style="width: 50%; text-align: right;">
		<table style="width: 100%">
			<tr>
				<td style="font-weight: bold; font-size: 14pt; color: #444; width: 100%;">{if isset($header)}{$header|escape:'html':'UTF-8'|upper}{/if}</td>
			</tr>
			<tr>
				<td style="font-size: 14pt; color: #9E9F9E">{$date|escape:'html':'UTF-8'}</td>
			</tr>
			<tr>
				<td style="font-size: 14pt; color: #9E9F9E">{$title|escape:'html':'UTF-8'}</td>
			</tr>
		</table>
	</td>
</tr>
{if !empty($shop_details)}
<tr>
	<td style="width: 60%; font-size: 7.5pt; color: #444; line-height: 1.4;">
		<br />{$shop_details|escape:'html':'UTF-8'|nl2br}
	</td>
	<td style="width: 40%;">&nbsp;</td>
</tr>
{/if}
</table>
