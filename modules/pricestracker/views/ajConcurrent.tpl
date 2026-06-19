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

<script src="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/lib/codemirror.css">
<script src="{Tools::getAdminUrl()}modules/pricestracker/js/codemirror/mode/groovy/groovy.js"></script>

<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/dialog.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/jump-to-line.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/search.js"></script>
<script src="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/searchcursor.js"></script>

<link href="{Tools::getAdminUrl()}modules/megaimporter/js/codemirror/lib/dialog.css" rel="stylesheet">


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
.CodeMirror {
font-size: 12px !important;
}


</style>
<br /><br />


<script>
$(document).ready(function() {
        document.title = '{$concurrent.nom|escape:'htmlall'} - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
		
		CodeMirror.fromTextArea(document.getElementById("codeGroovy"), {
			lineNumbers: true,
      		lineWrapping: true,
			matchBrackets: true,
			mode: "text/x-groovy"
		  });

		CodeMirror.fromTextArea(document.getElementById("codeLiens"), {
			lineNumbers: true,
      		lineWrapping: true,
			matchBrackets: true,
			mode: "text/x-groovy"
		  });

		CodeMirror.fromTextArea(document.getElementById("codeFinal"), {
			lineNumbers: true,
      		lineWrapping: true,
			matchBrackets: true,
			mode: "text/x-groovy"
		  });
    });
</script>


<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Add a competitor' mod='pricestracker'}</legend>


    <a href="{$lien}&amp;concurrents" class="button">&lt;&lt; {l s='Back to competitors' mod='pricestracker'}</a>

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
            <a href="https://www.youtube.com/watch?{if $is_francais}v=7IIyyKTtT9w&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I&index=1{else}v=oVB-ZU4yvQM&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" />
    </a>
</div>


<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}7IIyyKTtT9w?index=1&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}oVB-ZU4yvQM?si=6J33A5K00sPqxAZr{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=7IIyyKTtT9w&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I&index=1{else}v=oVB-ZU4yvQM&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
  </div>

</div>
{/if}

<!--Fin popup-->


    <h2 style="padding-left:150px">{l s='General' mod='pricestracker'}</h2>

		  <label>{l s='Name' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="nom" value="{$concurrent.nom|htmlspecialchars}" size="45" id="nom" />
		  </div>
		



		  <label>{l s='URL adress' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="url" value="{$concurrent.url|htmlspecialchars}" size="120" id="url" />
		  </div>



		  <label>{l s='Order in Analysis' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="ordre" value="{$concurrent.ordre|htmlspecialchars}" size="3" id="ordre" />
		  </div>


		  <label>ID :</label>
		  <div class="margin-form">
			{$concurrent.id_concurrents|htmlspecialchars}
		  </div>
          
          
		  <label></label>
		  <div class="margin-form">
			<a href="{$lien}&amp;cloud&id={$concurrent.id_concurrents|htmlspecialchars}" class="button">{l s='Cloud settings' mod='pricestracker'}</a>
		  </div>


				<br class="clear"/>
		

<h2 style="padding-left:150px">{l s='Find information' mod='pricestracker'}</h2>

<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php" style="margin-left:100px; text-decoration:underline">{l s='Do you want a specialist of idIA Tech configure your competitors?' mod='pricestracker'}</a>

				<br class="clear"/>
				<br class="clear"/>
		  <label>{l s='Regular expression for the name of the product' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="regexTitre" value="{$concurrent.regexTitre|htmlspecialchars}" size="120" id="regexTitre" />
			<br />
			<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='How to find it ?' mod='pricestracker'}</a></div>
		
				<br class="clear"/>



		  <label>{l s='Number of the match to extract' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="masqueTitre" value="{$concurrent.masqueTitre|htmlspecialchars}" size="2" id="masqueTitre" />
			<br />
		  {l s='Number of the item bewten brackets/place of the information' mod='pricestracker'}</div>
		
	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />



		  <label>{l s='Regular expression for the price' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="regexPrix" value="{$concurrent.regexPrix|htmlspecialchars}" size="120" id="regexPrix" />
			<br />
	      <a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='How to find it ?' mod='pricestracker'}</a></div>
		
				<br class="clear"/>



		  <label>{l s='Number of the match to extract' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="masquePrix" value="{$concurrent.masquePrix|htmlspecialchars}" size="2" id="masquePrix" />
			<br />
		  {l s='Number of the item bewten brackets/place of the information' mod='pricestracker'}</div>
		
{if $version2 eq 'MEGA'}		
		
	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />




		  <label>{l s='Regular expression for the image' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="regexImage" value="{$concurrent.regexImage|htmlspecialchars}" size="120" id="regexImage" />
            
			<br />
	      <a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='How to find it ?' mod='pricestracker'}</a></div>
		
				<br class="clear"/>



		  <label>{l s='Number of the match to extract' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="masqueImage" value="{$concurrent.masqueImage|htmlspecialchars}" size="2" id="masqueImage" />
			<br />
		  {l s='Number of the item bewten brackets/place of the information' mod='pricestracker'}</div>
		
	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />
    
    
	<label>{l s='Regular expression for the reference' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="regexRef" value="{$concurrent.regexRef|htmlspecialchars}" size="120" id="regexRef" />
			<br />
	      <a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='How to find it ?' mod='pricestracker'}</a></div>
		
				<br class="clear"/>



		  <label>{l s='Number of the match to extract' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="masqueRef" value="{$concurrent.masqueRef|htmlspecialchars}" size="2" id="masqueRef" />
			<br />
		  {l s='Number of the item bewten brackets/place of the information' mod='pricestracker'}</div>
	
{/if}

	
	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />

	
		  <label>{l s='Regular expression for URLs to avoid' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="regexUrlBloquer" value="{$concurrent.regexUrlBloquer|htmlspecialchars}" size="120" id="regexPrix" /><br>
{l s='Example:' mod='pricestracker'} 
			(exclusion1)|(&amp;exclusion2=)
			<br />
			<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_url_to_avoid.php">{l s='How to find regex to avoid text ?' mod='pricestracker'}</a></div>
		
				<br class="clear"/>

	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />


    <label>{l s='In case of particular interpretation (combinations, AJAX protection, etc), write the Grimport script to extract information' mod='pricestracker'} :<br />
    <a href="https://www.idia-tech.com/grimport-documentation.php" target="_blank">{l s='Grimport Documentation' mod='pricestracker'}</a><br />
    <a href="https://www.idia-tech.com/fonctions-grimport.php" target="_blank">{l s='Grimport functions' mod='pricestracker'}</a></label>
		  <div class="margin-form">
            <textarea name="codeGroovy" cols="80" rows="6" id="codeGroovy">{$concurrent.codeGroovy|htmlspecialchars}</textarea>
			<br />
    {l s='Input variables  : code = source code of the page, regexName = name of the product obtained with the regular expression, regexPrice = price of the product obtained with the regular expression, urlPage = URL of the page' mod='pricestracker'}{if $version2 eq 'MEGA'}, regexImage, regexRef{/if}<br />
{l s='Output variables : name = new name of the product (optionnal), price = new price of the product (optionnal)' mod='pricestracker'}{if $version2 eq 'MEGA'}, reference, image{/if}<br />
{l s='Functions aviables : display(message) = display a variable in the PricesTracker log, addProduct(name,price,link,reference,image,quantity,additionalInfos) = add a competitor product (2 last fields are optional), usefull to add product\'s combinations, htmlToPrice(html) = interpret an html code to find the price, post(url,associative_array_of_variables) = send a request with the post method. Warning! The link must be unique in PricesTracker. In case of combinaison you can create a fake link using # like' mod='pricestracker'} http://site.com/page.html<em>#combinaison1</em><br />
			<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='Ask idIA Tech to program the script' mod='pricestracker'}</a></div>
				<br class="clear"/>

	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />


    <label>{l s='Write the initial Grimport script' mod='pricestracker'} :<br />
    <a href="https://www.idia-tech.com/grimport-documentation.php" target="_blank">{l s='Grimport Documentation' mod='pricestracker'}</a><br />
    <a href="https://www.idia-tech.com/fonctions-grimport.php" target="_blank">{l s='Grimport functions' mod='pricestracker'}</a></label>
		  <div class="margin-form">
            <textarea name="codeLiens" cols="80" rows="6" id="codeLiens">{$concurrent.codeLiens|htmlspecialchars}</textarea>
			<br />
    {l s='Input and output variable  : links = array of links' mod='pricestracker'}<br />
			<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='Ask idIA Tech to program the script' mod='pricestracker'}</a></div>
				<br class="clear"/>
		
		


	<hr style="background-color: #CCC;color: #CCC;" width="500" align="left" />


    <label>{l s='Write the final Grimport script' mod='pricestracker'} :<br />
    <a href="https://www.idia-tech.com/grimport-documentation.php" target="_blank">{l s='Grimport Documentation' mod='pricestracker'}</a><br />
    <a href="https://www.idia-tech.com/fonctions-grimport.php" target="_blank">{l s='Grimport functions' mod='pricestracker'}</a></label>
		  <div class="margin-form">
            <textarea name="codeFinal" cols="80" rows="6" id="codeFinal">{$concurrent.codeFinal|htmlspecialchars}</textarea>
			<br />
			<a target="_blank" href="https://www.storeinterfacer.com/pricestracker_regex.php">{l s='Ask idIA Tech to program the script' mod='pricestracker'}</a></div>
				<br class="clear"/>
		



<h2 style="padding-left:150px; display:inline">{l s='Technical parameters for the crawler' mod='pricestracker'}</h2>
&nbsp;&nbsp;<span style="color: #666">{l s='If you do not know what to put, keep the default settings' mod='pricestracker'}</span>
				<br class="clear"/>
				<br class="clear"/>
<label>HTTP-User-Agent :</label>
		  <div class="margin-form">
			<input type="text" name="httpAgent" value="{$concurrent.httpAgent|htmlspecialchars}" size="120" id="httpAgent" />
		    <br />
		  {l s='Identification of the browser to avoid Apache filtring' mod='pricestracker'}</div>
		



		  <label>{l s='Deepness' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="profondeur" value="{$concurrent.profondeur|htmlspecialchars}" size="2" id="profondeur" />
		    <br />
{l s='For exemple, if deepness is 1, we crawl the page you have indicated and pages where there is a link on this page, but no more. If it is 2, we crawl pages where there is a link of all pages with the deepness 1' mod='pricestracker'}</div>
		



		  <label>{l s='Delay between requests' mod='pricestracker'} :</label>
<div class="margin-form">
			<input type="text" name="delai" value="{$concurrent.delai|htmlspecialchars}" size="4" />
      <br />
{l s='In milliseconds. To not flood target servers' mod='pricestracker'}</div>
		



		  <label>{l s='Max URL to visit' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="maxUrl" value="{$concurrent.maxUrl|htmlspecialchars}" size="9" id="maxUrl" />
		  </div>
				<br class="clear"/>
		


	                
  {if $version2 eq 'MEGA'}		
              

		  <label>{l s='Number of task in the crawler' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="nb_taches" value="{$concurrent.nb_taches|htmlspecialchars}" size="9" id="nb_taches" />
		  </div>
				<br class="clear"/>
                
                
              

		  <label>{l s='Cookie tracking mode ' mod='pricestracker'} :</label>
		  <div class="margin-form">
		    <p>
		      <label style="display: contents;">
		        <input style="float: none; " type="radio" name="suivi_cookies" value="0" id="suivi_cookies_0" {if $concurrent.suivi_cookies eq 0} checked="checked"{/if} />
		        No tracking</label>
		      <br />
		      <label style="display: contents;">
		        <input style="float: none; " type="radio" name="suivi_cookies" value="1" id="suivi_cookies_1" {if $concurrent.suivi_cookies eq 1} checked="checked"{/if} />
		        Tracking the cookie on the browsing of pages by crawling</label>
		      <br />
		      <label style="display: contents;">
		        <input style="float: none; " name="suivi_cookies" type="radio" id="suivi_cookies_2" value="2" {if $concurrent.suivi_cookies eq 2} checked="checked"{/if} />
		        Cookie tracking on crawling and HTTP functions (post, getPage, etc.)</label>
		      <br />
		      <label style="display: contents;">
		        <input style="float: none; " type="radio" name="suivi_cookies" value="3" id="suivi_cookies_3" {if $concurrent.suivi_cookies eq 3} checked="checked"{/if} />
		        Cookie tracking on crawling and HTTP functions as well as HTTP errors</label>
		      <br />
              <label style="display: contents;">
		        <input style="float: none; " type="radio" name="suivi_cookies" value="4" id="suivi_cookies_4" {if $concurrent.suivi_cookies eq 4} checked="checked"{/if} />
                Crawling via Firefox pour prendre en compte les interactions Javascript</label>
<br />
	        </p>
		  </div>
				<br class="clear"/>
		
              

		  <label>{l s='Make a progress backup every X urls visited (0=no automatic saving)' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="urls_sav_progression" value="{$concurrent.urls_sav_progression|htmlspecialchars}" size="9" id="urls_sav_progression" />
		  </div>
				<br class="clear"/>
                
{/if}	




    <input type="submit" name="submit" id="submit" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />
    <br />
    <br />
    
    {assign var="messagePage" value="{l s='Page of the product of the competitor' mod='pricestracker'}"}
<script>
function replaceShell(txt)
{
	return txt
.replace(/\+/gi,'---PLUS----')

}
</script>
    <a class="button" target="_blank" onclick="page=prompt('{$messagePage|addslashes}'); if(!page) return false; else { $(this).attr('href','{$lien}&okApplet&analyse&testExtraction='+escape('{$id}__________'+replaceShell(page))+'#applet' ); return true; }" href="{$lien}&analyse" style="margin-left:250px">{l s='Test with a page of the competitor' mod='pricestracker'}
    </a>&nbsp;&nbsp; {l s='The competitor must be saved before' mod='pricestracker'}
  </fieldset>
</form>
<!-- PricesTracker -->