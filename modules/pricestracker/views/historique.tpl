<!-- * PROPERTY
*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the idIA Tech License
 * You can't :
 * 1) Modify the source code
 * 2) Sell or distribute this module without our agreement
 * 3) Use this module with more than one domain (1 licence = 1 domain)
 * 4) Divert the source code
 *
 * @author    	idIA Tech
 * @copyright   Copyright (c) 2013 idIA Tech (https://www.idia-tech.com)
 * @license     https://www.storeinterfacer.com/conditions-generales.php idIA Tech (Proprietary)
 * @category    main
-->

<style type="text/css">
fieldset {
position: relative !important;
padding: 20px !important;
margin-bottom: 20px !important;
border: 1px solid #E6E6E6 !important;
background-color: #FFF !important;
border-radius: 5px !important;
box-shadow: 0px 2px 0px rgba(0, 0, 0, 0.1), 0px 0px 0px 3px #FFF inset !important;	
}
legend {
font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
font-weight: 400;
font-size: 14px;
text-overflow: ellipsis;
white-space: nowrap;
color: #555;	
border: 1px solid #E6E6E6 !important;
background-color: #FFF !important;
border-radius: 5px !important;
}
</style>

<script>
$(document).ready(function() {
        document.title = '{l s='History of prices' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Price history' mod='pricestracker'}</legend>
	<p>
    
<a href="{$productPS->getLink()}" target="_blank" style="font-size:16px">{$productPS->name[$langue]} <img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/external.png" border="0" align="middle" style="vertical-align:middle" /></a>  <br />
Ref : {$productPS->reference}<br />
{l s='Price' mod='pricestracker'} : {$productPS->getPrice()}
    
    </p>
	<p>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://rawgithub.com/highslide-software/export-csv/master/export-csv.js"></script>

<div id="historique" style="height:500px"></div>
<script>
{literal}
$(function () {
    var chart;
    $(document).ready(function() {
        $('#historique').highcharts({
            chart: {
                zoomType: 'x',
                type: 'areaspline'
            },
            title: {
                text: null
            },
            exporting: {
                enabled: true
            },
            xAxis: {
                type: 'datetime',
				startOnTick:false
            },
            tooltip: {
				useHTML:true,
                formatter: function() {
                        return ''+
                        Highcharts.dateFormat('%e. %b %Y', this.x) +' : '+ 
						(this.y.toFixed(2).replace('.',',')+"{/literal}{$devise}{literal}");
                }
            },
            credits: {
                enabled: false
            },
            plotOptions: {
                areaspline: {
                    fillOpacity: -1,
					marker:{ radius:3 }
                }
            },
            series: [
			
{/literal}


{assign var="prix_min" value=-1}
{assign var="id_PE_courant" value=-1}

{foreach item=h from=$historique}

	{if $id_PE_courant!=$h.id_produits_etrangers}
		{if $id_PE_courant!=-1}  ] },  {/if}
	{ 
	{if $h.id_produits_etrangers==0}fillOpacity: 0.3,{/if}
	name: '{if $h.id_produits_etrangers==0}{$productPS->name[$langue]|addslashes} ({l s='Your product' mod='pricestracker'}){else}{$nomsPE[$h.id_produits_etrangers].nom|addslashes} ({$nomsPE[$h.id_produits_etrangers].concurrent|addslashes}){/if}'
	,data: [
	{/if}

	{
		x:{$h.date*1000},
		y:{$h.prix}
	},

	{assign var="id_PE_courant" value=$h.id_produits_etrangers}
	{if $prix_min==-1 || $h.prix<$prix_min}
		{assign var="prix_min" value=$h.prix}
	{/if}
{/foreach} 

 
{literal}
				]   }
			 ],
			yAxis: { // left y axis
                title: {
                    text: null
                },
				endOnTick :false,
				min:{/literal}{$prix_min-0.1}{literal},
				startOnTick: false,
                labels: {
                    formatter: function() {
                        return this.value.toFixed(2) +'{/literal}{$devise}{literal}'
                    }
                },
                showFirstLabel: false
            }
        });
    });
});
{/literal}
</script>
    
    </p>
  </fieldset>
</form>
<!-- PricesTracker -->