{*
* 2003-2018 Business Tech
*
* @author Business Tech SARL <http://www.businesstech.fr/en/contact-us>
* @copyright  2003-2018 Business Tech SARL
*}

{if !empty($bDisplay)
	&& !empty($iConversionId)
	&& !empty($sConversionLabel)
	&& !empty($fTotalPaid)
}
	<!-- START - Google Adwords Conversion Tracking Pro - Conversion Code -->
	{literal}
	<script type="text/javascript" data-keepinline="true" async src="https://www.googletagmanager.com/gtag/js?id={/literal}{$iConversionId}{literal}"></script>
	<script type="text/javascript" data-keepinline="true" >
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments)};
		gtag('js', new Date());
		gtag('config', '{/literal}{$iConversionId}{literal}');
		gtag('event', 'conversion', {'send_to': '{/literal}{$iConversionId}{literal}/{/literal}{$sConversionLabel|escape:'htmlall':'UTF-8'}{literal}',
			'value': {/literal}{$fTotalPaid|floatval}{literal},
			'currency': '{/literal}{if !empty($sCurrency)}{$sCurrency|escape:'htmlall':'UTF-8'}{else}USD{/if}{literal}',
			'transaction_id': '{/literal}{if !empty($iTransactionId)}{$iTransactionId|escape:'htmlall':'UTF-8'}{else}0{/if}{literal}'
		});
	</script>
	{/literal}
	<!-- END - Google Adwords Conversion Tracking Pro - Conversion Code -->
{/if}