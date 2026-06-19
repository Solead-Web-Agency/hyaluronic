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
        document.title = '{l s='Configuration' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />

{if !$isHook}<div class="bootstrap"><div class="alert alert-danger">			<button type="button" class="close" data-dismiss="alert">×</button>			{l s='Caution !!! The hook displayBackOfficeTop is not active. This module will not work ! Please transplate our module on this hook' mod='pricestracker'}		</div></div>{/if}	


<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='About us' mod='pricestracker'}</legend>
    
    <!-- popup-->


<style type="text/css">
.shadowed {
  -moz-box-shadow:0px 0px 5px #444444;
  -webkit-box-shadow:0px 0px 5px #444444;
  box-shadow:0px 0px 5px #444444;
}

.video-container-wrapper {
  width: 100%;
  height: 100%;
  background: rgba(37,37,37,.7);
  text-align: center;
  position: fixed;
  top:0; left: 0;
  z-index:50;
  display: none;
  opacity: 0;
}
.video-container-wrapper:before {
  content: ' ';
  display: inline-block;
  height: 100%;
  vertical-align: middle;
}

.sizewrapper {
  width: 50%;
  /* Would normally be 1920px, limited because codepen's screen is small */
  max-width: 920px;
  vertical-align: middle;
  position: relative;
  background: rgba(255,255,255,0);
  z-index:51;
  display:none;
}

#videowrapper {
  position: relative;
  padding-top: 25px;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
}
#videowrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.close {
  position: absolute;
  right: -42px; top: 0;
  color: #fff;
  cursor:pointer;
  font-size: 24px;
  line-height: 32px;
  text-align: center;
  background-color: orange;
  width: 32px; height: 32px;
}

.go-to-yt {
  padding: 16px;
  margin: 8px 0 0;
  float: right;
  text-align: right;
  background-color: #fff;
  display: inline-block;
}
.go-to-yt a {
  text-decoration: none;
}
</style>
{if strpos(Tools::getShopDomain(), 'pbs-video.com') === false}

<div style="text-align: right;float: right; width: 114px;">
            <a href="https://www.youtube.com/watch?{if $is_francais}v=1PtxAMl0M84&index=6&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=44lt3HXxBTs&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" />
    </a>
</div>


<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}1PtxAMl0M84?index=6&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}44lt3HXxBTs?si=sSWpjUOsbWMs9j-H{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=1PtxAMl0M84&index=6&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=44lt3HXxBTs&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
  </div>

</div>
{/if}

<!--Fin popup-->



	<h2>{l s='Produced by ' mod='pricestracker'}
	  <a href="https://www.storeinterfacer.com" target="_blank" style="text-decoration:underline">idIA Tech</a></h2>





<!--{if $version2 eq 'MEGA'}
<div align="center"><a class="button" href="../modules/pricestracker/pricestracker.xpi" style="font-size:24px">{l s='Firefox addon for the Google Matching' mod='pricestracker'}</a><br /><br />

{l s='NB : it is recommanded to unactivate Instant Search in Google' mod='pricestracker'}<br>
{l s='Note : if Firefox refuse to install the module, write about:config in the address bar, find the xpinstall.signatures.required preference and put it to false' mod='pricestracker'}</div>
{/if}
-->

	<h3>{l s='E-commerce - business software - Custom applications for companies ' mod='pricestracker'}</h3>
	<h3>{l s='Software - Web - Mobile' mod='pricestracker'}</h3>
	<p>{$version_text}</p>
	<p>&nbsp;</p>
	<p>
    {if $version eq 'MEGA'}<a href="{$lien}&amp;tableaubord" style="text-decoration:underline">{l s='Dashboard' mod='pricestracker'}</a> - {/if}
    <a href="{$lien}&amp;analyse" style="text-decoration:underline">{l s='Analysis' mod='pricestracker'}</a> - 
    {if $version eq 'MEGA'}<a href="{$lien}&amp;proximite" style="text-decoration:underline">{l s='Proximity' mod='pricestracker'}</a> - {/if}
    <a href="{$lien}&amp;croisement" style="text-decoration:underline">{l s='Product matching' mod='pricestracker'}</a> - 
    <a style="text-decoration:underline" href="{$lien}&amp;comparaison">{l s='Competitors\' prices' mod='pricestracker'}</a>
    {if $version eq 'MEGA'} - <a href="{$lien}&amp;rapideMarketplace" style="text-decoration:underline">{l s='Rapid Pricing' mod='pricestracker'}</a>{/if}
    </p>
  </fieldset>

				<br class="clear"/>
                
  <fieldset>
	<legend>{l s='Configuration' mod='pricestracker'}</legend>

		  <label>{l s='Number of days by default for the update of competitors\' products' mod='pricestracker'} :</label>
		  <div class="margin-form">
	<input type="text" name="joursMaj" value="{$jours}" size="10" id="joursMaj" />
		<br />
		<label>
        <input type="checkbox" name="forcer_jours_tous" id="forcer_jours_tous" value="1" />
		Force this number of days for all current products</label>
        </div>
				<br class="clear"/>


		  <label>{l s='Number of products per page in Product Matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
	<input type="text" name="NbParPage_corr" value="{$NbParPage_corr}" size="10" id="NbParPage_corr" />
		</div>
				<br class="clear"/>


		  <label>{l s='Number of products per page in Prices of competitors' mod='pricestracker'} :</label>
		  <div class="margin-form">
	<input type="text" name="NbParPage_comp" value="{$NbParPage_comp}" size="10" id="NbParPage_comp" />
		</div>
				<br class="clear"/>



    <label>{l s='For partial updates' mod='pricestracker'} :</label>
		  <div class="margin-form">
		  <label>
		    <input type="checkbox" name="majAssociation" id="majAssociation" value="1" {if $majasso}checked="checked"{/if} />
		    {l s='Only update the products already associated' mod='pricestracker'}</label>
<br class="clear"/>
</div>
				<br class="clear"/>

                
                
		  <label>{l s='Display last imported in first in product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">

		    <input type="checkbox" name="derniers" id="derniers" value="1" {if $derniers}checked="checked"{/if} />


		  Yes</div>
				<br class="clear"/>
                
                
		  <label>{l s='Mode tax. excl.' mod='pricestracker'} :</label>
		  <div class="margin-form">

		    <input type="checkbox" name="ht" id="ht" value="1" {if $ht}checked="checked"{/if} />


		  Yes</div>
				<br class="clear"/>
                
                
                
                
		  <label>{l s='Mode wholesale price' mod='pricestracker'} : 
          <div style="font-weight: normal;font-size: 10px;"> {l s='This mode is for advanded used and require to add getPriceMin in Product.class (see with our developers)' mod='pricestracker'}
          </div></label>
		  <div class="margin-form">
		    <input type="checkbox" name="prixGros" id="prixGros" value="1" {if $prixGros}checked="checked"{/if} />
		  Yes</div>
        
				<br class="clear"/>


                
                
		  <label>{l s='Display just ensabled products' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="produitsActifs" id="produitsActifs" value="1" {if $produitsActifs}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>


                
                
		  <label>{l s='Add in exports ID of favorites and rules' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="idEnExport" id="idEnExport" value="1" {if $idEnExport}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>



                
                
		  <label>{l s='Add programmed threshold ' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="seuils" id="seuils" value="1" {if $seuils}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>

                
		  <label>{l s='Remove the product name for product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="exclureNom" id="exclureNom" value="1" {if $exclureNom}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>
                
                
		  <label>{l s='Remove the main reference for product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="exclureRef" id="exclureRef" value="1" {if $exclureRef}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>

                
                
		  <label>{l s='Remove the EAN13 for product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="exclureEan" id="exclureEan" value="1" {if $exclureEan}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>


                
                
		  <label>{l s='Remove the UPC for product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="exclureUPC" id="exclureUPC" value="1" {if $exclureUPC}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>


                
                
		  <label>{l s='Remove the supplier reference for product matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="exclureRefFournisseur" id="exclureRefFournisseur" value="1" {if $exclureRefFournisseur}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>


                
                
		  <label>{l s='Pre-filling the search in Product Matching' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    
		      <label style="width: inherit;">
		        <input type="radio" name="preremplissageRecherche" value="" id="preremplissageRecherche_0" {if $preremplissageRecherche==''}checked="checked"{/if} />
		        None (closest product)</label>
		      <label style="width: inherit;">
		        <input type="radio" name="preremplissageRecherche" value="NOM" id="preremplissageRecherche_1"  {if $preremplissageRecherche=='NOM'}checked="checked"{/if} />
	          Product name</label>
		      <label style="width: inherit;">
		        <input type="radio" name="preremplissageRecherche" value="REFERENCE" id="preremplissageRecherche_2" {if $preremplissageRecherche=='REFERENCE'}checked="checked"{/if} />
	          Product reference</label>
		      
		   </div>
				<br class="clear"/>


                
                
    <label>{l s='Do not use multi SQL (slows down the insertion of textual proximities but increases the compatibility of the module)' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <input type="checkbox" name="nomultisql" id="nomultisql" value="1" {if $nomultisql}checked="checked"{/if} />
		  Yes</div>
				<br class="clear"/>






		<input type="submit" name="config" id="config" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />


  </fieldset>

                
{if $version2 eq 'MEGA'}
				<br class="clear"/>
                
  <fieldset>
	<legend>{l s='Configuration for marketplaces (optionnal, but allows better performances)' mod='pricestracker'}</legend>

		  <label>Amazon - API_KEY (<a href="https://docs.aws.amazon.com/fr_fr/apigateway/latest/developerguide/api-gateway-setup-api-key-with-console.html#api-gateway-usage-plan-create-apikey" target="_blank">Documentation</a>) :</label>
		  <div class="margin-form">
				<input type="text" name="AWS_API_KEY" id="AWS_API_KEY" value="{Configuration::get('AMAZON_AWS_KEY_ID')}" />
		  </div>
          
          

		  <label>Amazon - API_SECRET_KEY  (<a href="https://aws.amazon.com/fr/blogs/security/wheres-my-secret-access-key/" target="_blank">Documentation</a>) :</label>
		  <div class="margin-form">
				<input type="text" name="API_SECRET_KEY" id="API_SECRET_KEY" value="{Configuration::get('AMAZON_SECRET_KEY')}" />
          <a href="https://console.aws.amazon.com/iam/home?region=eu-west-3#/users" target="_blank">Admin</a> &gt; {l s='Create user and go inside' mod='pricestracker'} &gt; {l s='Security Credentials' mod='pricestracker'} &gt; {l s='Create an access key. After implement the key and registrer you' mod='pricestracker'} <a href="https://affiliate-program.amazon.com/assoc_credentials/home" target="_blank">{l s='here' mod='pricestracker'}</a>. {l s='For new accounts, Amazon require to have 3 sales each 6 mounths.' mod='pricestracker'}</div>
          
          <br />


		  <label>CDiscount - Login for API :</label>
		  <div class="margin-form">
				<input type="text" name="cdiscount_ApiKey" id="cdiscount_ApiKey" value="{Configuration::get('cdiscount_ApiKey')}" />
                 <a href="https://seller.cdiscount.com/SellerParameters/DisplayConnection" target="_blank">{l s='Ask it' mod='pricestracker'}</a> 
		  </div>
          

		  <label>CDiscount - Password for API :</label>
		  <div class="margin-form">
				<input type="text" name="cdiscount_ApiPass" id="cdiscount_ApiPass" value="{Configuration::get('cdiscount_ApiPass')}" />
		  </div>
          
          

		  <label>Ebay - Production AppId :</label>
		  <div class="margin-form">
				<input type="text" name="Ebay_AppId" id="Ebay_AppId" value="{Configuration::get('pricestracker_Ebay_AppId')}" />
          <a href="https://developer.ebay.com/DevZone/guides/features-guide/content/basics/Call-DevelopmentKeys.html" target="_blank">{l s='Tutorial' mod='pricestracker'}</a></div>


          
          

		  <label>Ebay - Global ID (<a href="https://developer.ebay.com/devzone/finding/callref/Enums/GlobalIdList.html" target="_blank">{l s='List' mod='pricestracker'}</a>) :</label>
		  <div class="margin-form">
				<input type="text" name="Ebay_GlobalID" id="Ebay_GlobalID" value="{Configuration::get('pricestracker_Ebay_GlobalID')}" />
		  </div>
          
          

		  <label>PriceMinister - Login :</label>
		  <div class="margin-form">
				<input type="text" name="PM_login" id="PM_login" value="{Configuration::get('pricestracker_PM_login')}" />
		  </div>          
          

		  <label>PriceMinister - Token :</label>
    <div class="margin-form">
				<input type="text" name="PM_token" id="PM_token" value="{Configuration::get('pricestracker_PM_token')}" />
                 <a href="https://fr.shopping.rakuten.com/user" target="_blank">Admin</a> &gt;  {l s='Account parameters)' mod='pricestracker'}
  &gt; Tokens
		  </div>
 <br class="clear"/>





		<input type="submit" name="config" id="config" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" /> </fieldset>

{/if}
				<br class="clear"/>
                
  <fieldset>
	<legend>CRON</legend>

    <label>{l s='For the PARTIAL analysis of competitor (fast analysis on associated products). Try to set the frequency more than 150% of the time for a manual partial analysis.' mod='pricestracker'} :</label>
		  <div>export DISPLAY=:0.0 &amp;&amp; {$cronApplet} partial,nodisplay,workpath=/tmp/,</div>
				<hr class="clear"/>
    <label>{l s='For pricing rules checking. One a day for example.' mod='pricestracker'} :</label>
		  <div>{$cronRegles}
		</div>
				<hr class="clear"/>
    <label>{l s='For price history. One a day for example.' mod='pricestracker'} :</label>
		  <div>{$cronHistorique}
		</div>
				<hr class="clear"/>
    <label>{l s='For the COMPLETE analysis of competitor (usefull to find new products but more long, once a month for example). It is optionnal.' mod='pricestracker'} :</label>
		  <div>export DISPLAY=:0.0 &amp;&amp; {$cronApplet} nodisplay,workpath=/tmp/,</div>
				<br class="clear"/>
</fieldset>
 <!-- <p><br />
  <br />
  <a href="{$lien}&telechargerApplet">
  <input type="button" name="applet" id="applet" value="{l s='Update applet' mod='pricestracker'}" class="button" style="margin-left:250px" />
  </a>  </p>-->
  <p>&nbsp;</p>
  <p>
    <a href="{$lien}&clear_seuil">
    <input type="button" name="clear_seuil" id="clear_seuil" value="{l s='Clear all programmed thresholds (use in case of fatal error)' mod='pricestracker'}" class="button" style="margin-left:250px" />
</a></p>
  <p>
    <a href="{$lien}&clear_precalcul">
    <input type="button" name="clear_precalcul" id="clear_precalcul" value="{l s='Reset all pre-calculated prices for the dashboard' mod='pricestracker'}" class="button" style="margin-left:250px" />
</a></p>
</form>
<!-- PricesTracker -->
				<br class="clear"/>
				<br class="clear"/>
				<br class="clear"/>
				<br class="clear"/>
<a href="#" onclick="lien='{$lien}&grs'; window.location=lien">
<input type="button" name="dsddssd" id="dsddssd" value="{l s='Interface this site on Grimport Developper' mod='megaimporter'}" class="button" style="margin-left:250px" />
</a> 				<br class="clear"/>
