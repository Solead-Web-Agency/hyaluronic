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
        document.title = '{l s='Analysis' mod='pricestracker'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
    });
</script>


<br /><br />
<p>
    {if $version eq 'MEGA'}<a href="{$lien}&amp;tableaubord" style="text-decoration:underline">{l s='Dashboard' mod='pricestracker'}</a> - {/if}
    <a href="{$lien}&amp;analyse" style="text-decoration:underline">{l s='Analysis' mod='pricestracker'}</a> - 
    {if $version eq 'MEGA'}<a href="{$lien}&amp;proximite" style="text-decoration:underline">{l s='Proximity' mod='pricestracker'}</a> - {/if}
    <a href="{$lien}&amp;croisement" style="text-decoration:underline">{l s='Product matching' mod='pricestracker'}</a> - 
    <a style="text-decoration:underline" href="{$lien}&amp;comparaison">{l s='Competitors\' prices' mod='pricestracker'}</a>
    {if $version eq 'MEGA'} - <a href="{$lien}&amp;rapideMarketplace" style="text-decoration:underline">{l s='Rapid Pricing' mod='pricestracker'}</a>{/if}
    </p>

{if !Configuration::get('PS_SHOP_ENABLE')}
<div class="error">{l s='Your shop is in Maintenance Mode. Think about add your IP in the whitelist of Prestashop, to allow the Java program.' mod='pricestracker'} <a href="{$lien}&analyse&ajIPmaintenance">{l s='Add my IP in whitelist of maintenance' mod='pricestracker'}</a></div>
{/if}


<!-- PricesTracker -->
<form action="#applet" method="post">
  <fieldset>
	<legend>{l s='Analysis' mod='pricestracker'}</legend>
	<p><a href="{$lien}&concurrents" class="button">{l s='Manage competitors' mod='pricestracker'}</a>
{if $modeTest}    
<br />
<br />
<a href="https://www.storeinterfacer.com/pricestracker.php" target="_blank" class="button">{l s='Buy the module' mod='pricestracker'}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{l s='Note: you are testing PricesTracker version' mod='pricestracker'} {$version2} {if $stats}{l s='with' mod='pricestracker'}{else}{l s='without' mod='pricestracker'}{/if} {l s='Statistics Extension' mod='pricestracker'}
{/if}
    
    <a style="float:right" href="{$lien}&contact" class="button">{l s='Request assistance by idIA Tech on this part' mod='pricestracker'}</a>
        <a style="float:right;margin-left:10px; margin-right:10px" href="{if (_PS_VERSION_ < '1.5')}http://{$_SERVER['HTTP_HOST']}{else}{Context::getContext()->shop->getBaseURL(true)}{/if}modules/pricestracker/{l s='Manuel_en.pdf' mod='pricestracker'}" class="button">{l s='User Guide' mod='pricestracker'}</a>

    
    







    
    </p>
    <table width="100%" border="0">
  <tbody>
    <tr>
      <td width="60%">
	<h3> {l s='Steps of the analysis' mod='pricestracker'}	:</h3>
	<ol style="list-style:decimal; margin-left:50px"><li>{l s='Browse sites and extract information of products' mod='pricestracker'}</li>
	  <li>{l s='Save information of products' mod='pricestracker'}</li>
	  <li>{l s='Make textual connections with products of the catalog' mod='pricestracker'}</li>
    </ol></td>
      <td><h3> {l s='After the analysis' mod='pricestracker'}	:</h3>
	<ol style="list-style:decimal; margin-left:50px"><li>{l s='Go in Product Matching, check all products of your competitor are visible and prices are correct, click on corresponding product' mod='pricestracker'}</li>
	  <li>{l s='Check regularly your prices on Competitors\' prices' mod='pricestracker'}</li>
	  <li>{l s='Make statistics, use pricing rules, check history...' mod='pricestracker'}</li>
    </ol></td>
      <td><!-- popup-->


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
<div style="text-align: right; width: 114px;">
      <a href="https://www.youtube.com/watch?{if $is_francais}v=Ps2ok-8KWeU&index=2&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=i8e9p8Dhx6M&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" />
    </a>
</div>

<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}Ps2ok-8KWeU?index=2&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}i8e9p8Dhx6M?si=P5qHW4_2ObnaSH99{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=Ps2ok-8KWeU&index=2&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}v=i8e9p8Dhx6M&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
  </div>

</div>
{/if}

<!--Fin popup-->
</td>
    </tr>
  </tbody>
</table>

	<p>&nbsp;</p>
    
    


	<h3>{l s='Special options' mod='pricestracker'}</h3>
<p>
  <label style="text-align:left; width:100%">
    <input name="forcerMaj" type="checkbox" id="forcerMaj" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $forcerMaj || !Tools::isSubmit('okApplet')}checked="checked"{/if} />
    {l s='COMPLETE ANALYSIS : Verify the existence of new products of competitors / Force update  all products of competitors (requires to browse the whole websites)' mod='pricestracker'}
    <div style="margin-left:50px; color:#666; font-weight:normal">{l s='If not checked, only products already controlled will be re-checked. It is the PARTIAL ANALYSIS' mod='pricestracker'}</div>
    </label>
  <br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="pasRapprochementsTextes" type="checkbox" id="pasRapprochementsTextes" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $pasRapprochementsTextes}checked="checked"{/if} />
    {l s='Do not calculate textual similarities' mod='pricestracker'}</label><br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="seulementProximite" type="checkbox" id="seulementProximite" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $seulementProximite}checked="checked"{/if} />
    {l s='Calculate textual similarities only' mod='pricestracker'}</label><br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="exclureNomIdentique" type="checkbox" id="exclureNomIdentique" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $exclureNomIdentique}checked="checked"{/if} />
    {l s='Do not add product if the name already exists' mod='pricestracker'}</label><br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="historique" type="checkbox" id="historique" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $historique}checked="checked"{/if} />
    {l s='Update price history' mod='pricestracker'}</label><br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="exectuerRegles" type="checkbox" id="exectuerRegles" {if $version2 eq 'Silver'}disabled="disabled"{/if} value="1" {if $exectuerRegles}checked="checked"{/if} />
    {l s='Execute pricing rules' mod='pricestracker'}</label><br class="clear" />
  <label style="text-align:left; width:100%">
    <input name="logFichier" type="checkbox" id="logFichier" value="1" {if $logFichier}checked="checked"{/if} />
    {l s='Write the log in a file on Desktop' mod='pricestracker'}</label>
</p>
<p>&nbsp;    </p>
    <p>
      <input type="submit" name="okApplet" id="okApplet" value="{l s='Activate this configuration' mod='pricestracker'}" class="button" />
    </p>
    <p><a name="applet"></a>
  <!--{l s='Note 0: You must allow the Java applet to run. A warning is possible because the applet is not signed on your site.' mod='pricestracker'}
  <br class="clear" />-->
  {l s='Note 1: The analysis can be long. It is recommended to execute it during the night.' mod='pricestracker'}
  <br class="clear" />
{l s='Note 2:  The applet runs on your computer. The server is almost not solicited during the operation (only for information sharing with the database).' mod='pricestracker'}      <br class="clear" />
{l s='Note 3: If SmartScreen block the execution, click on "Other information" and Execute.' mod='pricestracker'}  </p>
      

    <p>&nbsp;</p>


{if !$isMac}
<h2>{l s='Launch your analysis' mod='megaimporter'}</h2> 
1) <a style="text-decoration:underline" href="https://www.idia-tech.com/grimport-crawler/installer-grimport.exe">{l s='Install PricesTracker Crawler' mod='megaimporter'}</a><br />
2) {l s='Execute your' mod='megaimporter'}  <strong><a style="text-decoration:underline" href="{$lien}&grl&testExtraction={$testExtraction|replace:'\\':'\\\\'|urlencode}&argument={if !($forcerMaj || !Tools::isSubmit('okApplet'))}partial,{/if}{if $seulementProximite}justsimilarities,{/if}{if $pasRapprochementsTextes}nosimilarities,{/if}{if !$logFichier}nolog,{/if}{if !$historique}nohistory,{/if}{if !$exclureNomIdentique}includeSameName,{/if}{if !$exectuerRegles}norules,{/if}">{l s='personnal launcher' mod='megaimporter'} {if $testExtraction} {l s='for the test' mod='megaimporter'}{else} {l s='for a general crawling' mod='megaimporter'}{/if}</a></strong><br /><br />


     <a href="https://www.idia-tech.com/tutorials.php" target="_blank" class="button" style="text-decoration:underline">{l s='Video tutorial' mod='megaimporter'}</a> <br />
<br />
<div align="center" style="vertical-align:middle"><a href="https://www.storeinterfacer.com" target="_blank"><img src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/societe.png" border="0" align="middle" style="vertical-align:middle" /></a> <span style="font-size: 18px; font-weight: bold;">{l s='Produced by' mod='pricestracker'}  <a style="text-decoration:underline" href="https://www.storeinterfacer.com" target="_blank">idIA Tech</a></span> 
</div>
<br />

<!--<h3>{l s='Alternative method' mod='megaimporter'}</h3> 
{/if}


{if $okApplet}

<!--<applet id="applet" archive="applet.jar, commons-codec-1.6.jar, commons-logging-1.1.3.jar, fluent-hc-4.3.1.jar, httpclient-4.3.1.jar, httpclient-cache-4.3.1.jar, httpcore-4.3.jar, httpmime-4.3.1.jar, jsoup-1.7.3.jar, groovy-all-2.2.1.jar, java-sizeof-0.0.2.jar, json-simple-1.1.1.jar" codebase="{$lienArchive}" code="main/Applet_VeilleConcurentielle.class" width="100%" height="500">
  <param name="lien" value="{$lienApplet}">
<param name="pasRapprochementsTextes" value="{$pasRapprochementsTextes}">
<param name="seulementProximite" value="{$seulementProximite}">
<param name="testExtraction" value="{$testExtraction}">
<param name="forcerMaj" value="{$forcerMaj}">
<param name="logFichier" value="{$logFichier}">
<param name="historique" value="{$historique}">
<param name="exclureNomIdentique" value="{$exclureNomIdentique}">
{l s='Java is not allowed or installed !' mod='pricestracker'}
</applet>
-->
<!--{l s='OR' mod='pricestracker'}<br />
<input type="submit" name="okJNLP" id="okJNLP" value="{l s='Lunch the analysis with an alternative method to avoid memory problems' mod='pricestracker'}" class="button" onclick="$('#applet').remove() ; $('#applet').remove(); $('#applet').remove()" />

{l s='Your Java version must be up to date' mod='pricestracker'}.<br /><br />

{l s='To increase the Java memory' mod='pricestracker'} :<br />
{l s='Control Panel> Java (Classic View)> Java tab> Display> Settings ... In the colone Parameters... write' mod='pricestracker'} <em>-Xms256m -Xmx1024m</em><br />
<br />
{l s='If you have a problem when executing' mod='pricestracker'} :<br />
{l s='Control Panel> Java (Classic View)> Security tab> Add your site in exeptions (with http://)' mod='pricestracker'}<br />{/if}

	<h4>{l s='Run the analysis' mod='pricestracker'}</h4>
<p>
1) {l s='Uninstall all previous Java version and install' mod='megaimporter'} <a href="https://www.oracle.com/fr/java/technologies/javase-jdk11-downloads.html" target="_blank" style="text-decoration:underline">JDK11</a><br />
2) {l s='Put this 2 files in the same folder' mod='pricestracker'} : <a href="https://www.idia-tech.com/grimport-crawler/run.jar" style="text-decoration:underline">run.jar</a>, {if $testExtraction}<strong>{/if}<a href="{$lien}&bat&testExtraction={$testExtraction|replace:'\\':'\\\\'|urlencode}&argument={if !($forcerMaj || !Tools::isSubmit('okApplet'))}partial,{/if}{if $seulementProximite}justsimilarities,{/if}{if $pasRapprochementsTextes}nosimilarities,{/if}{if !$logFichier}nolog,{/if}{if !$historique}nohistory,{/if}{if !$exclureNomIdentique}includeSameName,{/if}{if !$exectuerRegles}norules,{/if}" style="text-decoration:underline">LAUNCH.{if !$isMac}bat{else}sh{/if}{if $testExtraction} {l s='for the test' mod='pricestracker'}{else} {l s='for a general crawling' mod='pricestracker'}{/if}</a>{if $testExtraction}</strong>{/if}<br />
{if !$isMac}
3) {l s='Double click on' mod='pricestracker'} LAUNCH.bat
{else}
3) {l s='Open a Terminal' mod='pricestracker'} <br />
4)   {l s='Enter' mod='pricestracker'} &quot;sh &quot;&nbsp; ({l s='sh  followed by a space without the quotation marks' mod='pricestracker'}) <br />
5)   {l s='Drag and drop the LAUNCH.sh into the  terminal, this will write the LAUNCH.sh path after the command' mod='pricestracker'} <br />
6)   {l s='Press Enter' mod='pricestracker'} <br />
{l s='Some Macs display  the error "run.jar  not found". If  so, open the LAUNCH.sh with a text editor, locate run.jar and add its absolute  path (its location on the disk) in front of it so that it is located in a  non-relative way.' mod='pricestracker'}      
    {/if}</p><br />-->
</fieldset>
</form>
<!-- PricesTracker -->