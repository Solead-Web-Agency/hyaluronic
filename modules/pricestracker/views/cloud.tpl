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

.texte-normal
{
	font-size: 1em !important;
color: black !important;
}


</style>
<br /><br />


<script>
$(document).ready(function() {
        document.title = 'Cloud - PricesTracker';
		$('.pricestracker-menu').addClass('open');
		$('.pricestracker-menu').addClass('-active');
		
    });
</script>

{if $message}<div class="bootstrap"><div class="alert alert-success">			<button type="button" class="close" data-dismiss="alert">×</button>			{$message|htmlspecialchars}		</div></div>{/if}	


<!-- PricesTracker -->

  <fieldset>
	<legend>{l s='Cloud' mod='pricestracker'}</legend>

    <a href="{$lien}&amp;ajConcurrent&id={$concurrent.id_concurrents|htmlspecialchars}" class="button">&lt;&lt; {l s='Back to' mod='pricestracker'} {$concurrent.nom|htmlspecialchars}</a><br /><br />

    <a href="{$lien}&amp;concurrents" class="button">&lt;&lt; {l s='Back to competitors' mod='pricestracker'}</a><br />
    <br />
    <br />
    
    
    <form action="" method="post">
    <h2 style="padding-left:150px">{l s='Your Cloud account on idIA Tech' mod='pricestracker'}</h2>


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
            <a href="https://www.youtube.com/watch?{if $is_francais}v=RD06BTHWIyg&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I&index=3{else}v=53HDcZDA1Nk&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank" class="open-yt-lightbox">
        <img alt="Video tuto" src="{Tools::getAdminUrl()}modules/pricestracker/pages/images/video-tutorial.png" />
    </a>
</div>


<div class="video-container-wrapper lightbox-backdrop">
  <div class="sizewrapper box">
    <div class="close shadowed">X</div>
    <div id="videowrapper" class="shadowed">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/{if $is_francais}RD06BTHWIyg?index=3&enablejsapi=1&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I{else}53HDcZDA1Nk?si=YUATbZsSFQnaf7ed{/if}&ab_channel=idIATech" frameborder="0" id="yt-iframe" allowfullscreen></iframe>
    </div>
    <div class="go-to-yt shadowed"><a href="https://www.youtube.com/watch?{if $is_francais}v=RD06BTHWIyg&list=PLIW8G3pFHKmZk0z0uTLCi9VgwOm3RL89I&index=3{else}v=53HDcZDA1Nk&list=PLIW8G3pFHKmYyVsT1zYOtTCaafrnpr_Cq{/if}&ab_channel=idIATech" target="_blank">{l s='Video not loading? Watch on Youtube' mod='pricestracker'} ></a></div>
  </div>

</div>
{/if}

<!--Fin popup-->




<a target="_blank" href="https://www.storeinterfacer.com/achat_credits.php?typeProxy={$typeProxy}" style="margin-left:100px; text-decoration:underline">{l s='Buy credits for the idIA Tech Cloud' mod='pricestracker'}</a>
<br />
<br />
		  <label>{l s='Code Cloud on idIA Tech' mod='pricestracker'} :</label>
		  <div class="margin-form">
<input type="text" name="codeCloud" value="{$codeCloud|htmlspecialchars}" size="45" id="codeCloud" />			
		  </div>


				<br class="clear"/>
                
    <input type="submit" name="majCodeCloud" id="majCodeCloud" value="{l s='Update your code' mod='pricestracker'}" class="button" style="margin-left:250px" />


				<br class="clear"/>

		  <label>{l s='Cloud account' mod='pricestracker'} :</label>
		  <div class="margin-form texte-normal">
          {if $identification_compte}
			{$identification_compte|htmlspecialchars|nl2br}
          {else}
          	{l s='Account not found' mod='pricestracker'}
          {/if}
		  </div>


				<br class="clear"/>
		
</form>
    
    
    <form action="" method="post">
    <h2 style="padding-left:150px">{l s='Schedule' mod='pricestracker'} {$concurrent.nom|htmlspecialchars} {l s=' for COMPLETE analysis in the idIA Tech Cloud' mod='pricestracker'}</h2>

		  <label>{l s='Competitor ID' mod='pricestracker'} :</label>
		  <div class="margin-form texte-normal">
			{$concurrent.id_concurrents|htmlspecialchars}
		  </div>


				<br class="clear"/>

    <label>{l s='Minutes' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="minutes" value="{$minutes|htmlspecialchars}" size="45" id="minutes" {if !$identification_compte}disabled="disabled"{/if} />			<br />
	Ex: 0,10,20,30,40,50. * = {l s='all (in each field)' mod='pricestracker'}
    </div>
		



		  <label>{l s='Hours' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="heures" value="{$heures|htmlspecialchars}" size="120" id="heures" {if !$identification_compte}disabled="disabled"{/if} />
		  </div>



		  <label>{l s='Day of month' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="joursMois" value="{$joursMois|htmlspecialchars}" size="120" id="joursMois" {if !$identification_compte}disabled="disabled"{/if} />
		  </div>



		  <label>{l s='Day of week' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<input type="text" name="joursSemaine" value="{$joursSemaine|htmlspecialchars}" size="120" id="joursSemaine" {if !$identification_compte}disabled="disabled"{/if} />	<br />
            {l s='1 = monday, 2 = tuesday, ..., 7 = sunday' mod='pricestracker'}
		  </div>



		  <label>{l s='Month' mod='pricestracker'} :</label>
		  <div class="margin-form">
			 <input type="text" name="mois" value="{$mois|htmlspecialchars}" size="120" id="mois" {if !$identification_compte}disabled="disabled"{/if} /> 
		  </div>
          
          
		  <input name="partial" type="hidden" id="partial" value="0" />

          
    <label>{l s='Activate' mod='pricestracker'}  :</label>
		  <div class="margin-form">
			<label style="text-align:left; width:100%"><input name="actif" type="checkbox" id="actif" value="1" {if $actif}checked="checked"{/if} {if !$identification_compte}disabled="disabled"{/if} /> {l s='Yes' mod='pricestracker'} </label>
		  </div>
<br />


    <label>{l s='5 forthcoming crawlings' mod='pricestracker'} ? :</label>
		  <div class="margin-form">
			<span style="text-align:left; width:100%">
            {$prochainesProgrammationsComplete|escape:'htmlall'}
            </span>
	  </div>



				<p><br class="clear"/>
				  {assign var="messageCronPasOK" value="{l s="Wrongly formatted field. Check that the value of this field is in one of these formats:
				  * = all (every minute, every month....)
				  Number (e.g. 1) = fixed schedule (trigger only at minute 01, month 1 i.e. January, day 1 i.e. Monday)
				  Several numbers between commas (e.g. 1,2,3,4) = you define a range (e.g. start analysis only on 1, 2, 3 and 4 of the month, so nothing from 5 to 31 of the month)
				  
				  For this field, the numbers must be in the range" mod='pricestracker'}"}
				  {literal}
				  <script>
  function verifierChamp(cssSelecteur, mini, maxi)
  {
	  valeur = $(cssSelecteur).val()
	  if(valeur=="*") return true
	  else
	  {
		  auMoinsUnNombre = false
		  sousValeurs=valeur.split(",");
		  for(i in sousValeurs)
		  {
			  sousValeur = sousValeurs[i]
			  if(!/^\d+$/.test(sousValeur)) return false
			  nombre = parseInt(sousValeur);
			  if(nombre<mini || nombre > maxi) return false
			  
			  auMoinsUnNombre=true
		  }
		  
		  if(!auMoinsUnNombre) return false
	  }
	  
	  return true
  }
  
  function verifierChampAvecMessage(cssSelecteur, nomChamp, mini, maxi)
  {
	 isOK = verifierChamp(cssSelecteur, mini, maxi)
	 if(!isOK)
	 {
		 alert(nomChamp+" :\n\n{/literal}{$messageCronPasOK|json_encode|escape:'html'|replace:'&quot;':''}{literal} "+mini+" - "+maxi)
		 $(cssSelecteur).focus();
		 $(cssSelecteur).select();

	 }
	 return isOK
  }
  
  function verifierCrontab(post_id)
  {
	  if(!verifierChampAvecMessage("#minutes"+post_id, "Minutes", 0,59)) return false
	  if(!verifierChampAvecMessage("#heures"+post_id, "Hours", 0,23)) return false
	  if(!verifierChampAvecMessage("#joursMois"+post_id, "Day of month", 1,31)) return false
	  if(!verifierChampAvecMessage("#joursSemaine"+post_id, "Day of week", 1,7)) return false
	  if(!verifierChampAvecMessage("#mois"+post_id, "Month", 1,12)) return false
	  return true
  }
  </script>
				  {/literal}
				  <input type="submit" name="programmer" id="programmer" value="{l s='Schedule in the Cloud the COMPLETE analysis' mod='pricestracker'}" class="button" style="margin-left:250px" {if !$identification_compte}disabled="disabled"{/if}  onclick="return verifierCrontab('')"/><br />
  <br />
  <input type="submit" name="supprimer_programme" id="supprimer_programme" value="{l s='Delete scheduled task of the COMPLETE analysis' mod='pricestracker'}" class="button" style="margin-left:250px" {if !$identification_compte}disabled="disabled"{/if} />
				  
				  
    </p>
				<p>{l s='Example 1 : [ Minutes : 10 ; Hours : 1,13 ; Day of mounth : * ; Day of week : * ; Month : * ] = twice a day at 1.10 a.m. and 1.10 p.m. every day' mod='pricestracker'}<br />
	{l s='Example 2 : [ Minutes : 0 ; Hours : 0 ; Day of mounth : * ; Day of week : 6 ; Month : * ] = once a week at 00:00 on Saturdays' mod='pricestracker'}</p>
                <p>&nbsp;                  </p>
</form>                
                
<form action="" method="post">         
                <h2 style="padding-left:150px">{l s='Schedule' mod='pricestracker'} {$concurrent.nom|htmlspecialchars} {l s=' for PARTIAL analysis in the idIA Tech Cloud' mod='pricestracker'}</h2>
                <label>{l s='Competitor ID' mod='pricestracker'} :</label>
                <div class="margin-form texte-normal"> {$concurrent.id_concurrents|htmlspecialchars} </div>
                <br class="clear"/>
                <label>{l s='Minutes' mod='pricestracker'} :</label>
                <div class="margin-form">
                  <input type="text" name="minutes" value="{$minutes_partial|htmlspecialchars}" size="45" id="minutes2" {if !$identification_compte}disabled="disabled"{/if} />
                  <br />
                  Ex: 0,10,20,30,40,50. * = {l s='all (in each field)' mod='pricestracker'} </div>
                <label>{l s='Hours' mod='pricestracker'} :</label>
                <div class="margin-form">
                  <input type="text" name="heures" value="{$heures_partial|htmlspecialchars}" size="120" id="heures2" {if !$identification_compte}disabled="disabled"{/if} />
                </div>
                <label>{l s='Day of month' mod='pricestracker'} :</label>
                <div class="margin-form">
                  <input type="text" name="joursMois" value="{$joursMois_partial|htmlspecialchars}" size="120" id="joursMois2" {if !$identification_compte}disabled="disabled"{/if} />
                </div>
                <label>{l s='Day of week' mod='pricestracker'} :</label>
                <div class="margin-form">
                  <input type="text" name="joursSemaine" value="{$joursSemaine_partial|htmlspecialchars}" size="120" id="joursSemaine2" {if !$identification_compte}disabled="disabled"{/if} />
                  <br />
                  {l s='1 = monday, 2 = tuesday, ..., 7 = sunday' mod='pricestracker'} </div>
                <label>{l s='Month' mod='pricestracker'} :</label>
                <div class="margin-form">
                  <input type="text" name="mois" value="{$mois_partial|htmlspecialchars}" size="120" id="mois2" {if !$identification_compte}disabled="disabled"{/if} />
                </div>
                <input name="partial" type="hidden" id="partial2" value="1" />
                <label>{l s='Activate' mod='pricestracker'} ? :</label>
                <div class="margin-form">
                  <label style="text-align:left; width:100%">
                    <input name="actif" type="checkbox" id="actif" value="1" {if $actif_partial}checked="checked"{/if} {if !$identification_compte}disabled="disabled"{/if} />
                    {l s='Yes' mod='pricestracker'} </label>
                </div>
                <br />

                

    <label>{l s='5 forthcoming crawlings' mod='pricestracker'}  :</label>
		  <div class="margin-form">
			<span style="text-align:left; width:100%">
            {$prochainesProgrammationsPartiel|escape:'htmlall'}
             </span>
		  </div>

                
                <p><br class="clear"/>
                  
                  
  <input type="submit" name="programmer" id="programmer2" value="{l s='Schedule in the Cloud the PARTIAL analysis' mod='pricestracker'}" class="button" style="margin-left:250px" {if !$identification_compte}disabled="disabled"{/if}  onclick="return verifierCrontab(2)"/>
  <br />
  <br />
  <input type="submit" name="supprimer_programme" id="supprimer_programme2" value="{l s='Delete scheduled task of the PARTIAL analysis' mod='pricestracker'}" class="button" style="margin-left:250px" {if !$identification_compte}disabled="disabled"{/if} />
                </p>
                <p>{l s='Example 1 : [ Minutes : 10 ; Hours : 1,13 ; Day of mounth : * ; Day of week : * ; Month : * ] = twice a day at 1.10 a.m. and 1.10 p.m. every day' mod='pricestracker'}<br />
                  {l s='Example 2 : [ Minutes : 0 ; Hours : 0 ; Day of mounth : * ; Day of week : 6 ; Month : * ] = once a week at 00:00 on Saturdays' mod='pricestracker'}</p>
</form>                <p><br />
                  <br />
                  <br class="clear"/>
                  
                  
                </p>
<form action="" method="post">
	<h2 style="padding-left:150px">{l s='Launch once' mod='pricestracker'} "{$concurrent.nom|htmlspecialchars}" {l s='in the idIA Tech Cloud' mod='pricestracker'}</h2>
                
		  <label>{l s='Do you want to launch a partial analysis?' mod='pricestracker'} :</label>
		  <div class="margin-form">
			<label style="text-align:left; width:100%"><input name="partialLancer" type="checkbox" id="partialLancer" value="1" {if $partial}checked="checked"{/if} {if !$identification_compte}disabled="disabled"{/if} /> {l s='Yes, partial' mod='pricestracker'} </label><br />
{l s='If you do not check this box, it will be a COMPLETE analysis' mod='pricestracker'} 
		  </div>


    <input type="submit" name="lancerCloud" id="lancerCloud" value="{l s='Launch once in the Cloud' mod='pricestracker'}" class="button" style="margin-left:250px" {if !$identification_compte}disabled="disabled"{/if} />
</form>


<form action="" method="post">
 
 <a name="listeCrawlings"></a>
				<br />
				<br />
<br class="clear"/>
		

<script>
function ouvrirCrawling()
{
	optionVal=$('#crawling').val();
	if(!optionVal) return;
	
	soptionVal=optionVal.split("#")
	if(soptionVal.length>=3)
	{
		idUnique=soptionVal[0]
		idServeur=soptionVal[1]
		enCours=false
		if(soptionVal[2]) enCours=true
		
		htmlDetail='<a class="button" style="font-size: 18px;" href="javascript:void(0)" id="boutonRapport" onclick="demanderRapport('+idUnique+','+idServeur+',this)">{l s='Download the crawling report' mod='pricestracker'}</a>'
		
		if(enCours)
		{
			htmlDetail+='<br><br>			<a class="button" href="javascript:void(0)" onclick="demanderRapportMemoire('+idUnique+','+idServeur+',this)">{l s='Progression' mod='pricestracker'}</a> 			<a class="button" href="javascript:void(0)" onclick="demanderFiltrageLiens('+idUnique+','+idServeur+',0,this)">{l s='Excude links from the analysis' mod='pricestracker'}</a> 			<a class="button" href="javascript:void(0)" onclick="demanderChangerEtats('+idUnique+','+idServeur+',this)">{l s='Display links' mod='pricestracker'}</a> 			<a class="button" href="javascript:void(0)" onclick="demanderArret('+idUnique+','+idServeur+',this)">{l s='Stop crawling' mod='pricestracker'}</a> '
			
		}
		
		$('#details').html(htmlDetail)
		
		if(enCours)
		{
			//synchroniserConsole(idUnique,idServeur)
		}
	}
}
function demanderRapport(idUniqueRapport,idServeurRapport,bouton)
{
	$.ajax({
	  url: "{$lien}&cloudAjax&demandeRapport=1&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport,
	}).done(function(data) {
		idAction=data
		
		setTimeout(function() { recupererActionRapport(idAction,idServeurRapport,0,bouton,"{l s='Download the crawling report' mod='pricestracker'} ({l s='downloaded' mod='pricestracker'})") }, 10000);
		
		$('#boutonRapport').html("{l s='Report asked...' mod='pricestracker'}")

	})
}
function recupererActionRapport(idAction,idServeurRapport,tentative,bouton,texte_fin)
{
	if(tentative>30)
	{
		alert("{l s='Failed to generate the crawling report' mod='pricestracker'}")
		$('#boutonRapport').html("{l s='Download the crawling report' mod='pricestracker'} (failed)")
		return;
	}
	
	$.ajax({
	  url: "{$lien}&cloudAjax&recuperer_action="+idAction+"&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeur,
	}).done(function(data) {
		
		var donnees=JSON.parse(data)

		if(!donnees["date_cloture"]) setTimeout(function() { recupererActionRapport(idAction,idServeurRapport,tentative+1,bouton,texte_fin) }, 10000);
		else
		{
			urlRapport=donnees['url_rapport'] //+donnees['token']+".zip"
			$("#iframe_telechargement").attr("src",urlRapport)
			$(bouton).html(texte_fin)
		}
	});
}
function demanderFiltrageLiens(idUniqueRapport,idServeurRapport,tentative,bouton)
{
	if(tentative>30)
	{
		alert("{l s='Failed to generate the crawling report' mod='pricestracker'}")
		$('#boutonRapport').html("{l s='Download the crawling report' mod='pricestracker'} (failed)")
		return;
	}
	
	typeFiltrage=window.prompt("{l s='0=Include, 1=Exclude this time, 2=Exclude and keep for this crawl, 3=Exclude and always keep' mod='pricestracker'}","3")
	if(typeFiltrage)
	{
		$.ajax("{$lien}&cloudAjax&recuperer_regexUrlBloquer=1&codeCloud={$codeCloud|urlencode}&id_concurrents={$concurrent.id_concurrents}").done(function(regexUrlBloquer) 
		{
			regex=window.prompt("{l s='Regular expression for links. Example: (exclusion1)|(&exclusion2=)' mod='pricestracker'}",regexUrlBloquer)
			
			if(typeFiltrage && regex)
			{
				$(bouton).html("...")
				
				$.ajax({
				  url: "{$lien}&cloudAjax&demanderFiltrageLiens=1&codeCloud={$codeCloud|urlencode}&id_concurrents={$concurrent.id_concurrents}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport+"&regex="+escape(regex)+"&reponse="+escape(typeFiltrage),
				}).done(function(data) {
					
					idAction=data
					
					setTimeout(function() { recupererActionRapport(idAction,idServeurRapport,0,bouton,"{l s='Excude links from the analysis' mod='pricestracker'}") }, 10000);
					
					$(bouton).html("{l s='Exclusion of links...' mod='pricestracker'}")
			
				})
			}
		})
	}
}

function demanderRapportMemoire(idUniqueRapport,idServeurRapport,bouton)
{
	$(bouton).html("...")
	$.ajax({
	  url: "{$lien}&cloudAjax&demanderRapportMemoire=1&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport,
	}).done(function(data) {
		
		$(bouton).html("OK !!")
		setTimeout(function() { $(bouton).html("{l s='Progression' mod='pricestracker'}") }, 3000);

	})
}
function demanderArret(idUniqueRapport,idServeurRapport,bouton)
{
	$(bouton).html("...")
	$.ajax({
	  url: "{$lien}&cloudAjax&demanderArret=1&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport,
	}).done(function(data) {
		
		$(bouton).html("OK !!")
		setTimeout(function() {
			 $('#details').html("<div class=\"bootstrap\"><div class=\"alert alert-warning\">			<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>			{l s='No crawling selected ' mod='pricestracker'}</div></div>") 
			$('#divConsole').hide()
		}, 1000);

	})
}
function demanderChangerEtats(idUniqueRapport,idServeurRapport,bouton)
{
	afficher_liens_en_cours_crawling=0+confirm("{l s='Do you want to display the links being crawled (OK = Yes, Cancel = No)' mod='pricestracker'}")
	afficher_liens_attente=0+confirm("{l s='Do you want to display the links waiting for crawling (OK = Yes, Cancel = No)' mod='pricestracker'}")
	
	$(bouton).html("...")
	$.ajax({
	  url: "{$lien}&cloudAjax&demanderChangerEtats=1&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport+"&afficher_liens_attente="+afficher_liens_attente+"&afficher_liens_en_cours_crawling="+afficher_liens_en_cours_crawling,
	}).done(function(data) {
		
		$(bouton).html("OK !!")
		setTimeout(function() { $(bouton).html("{l s='Display links' mod='pricestracker'}") }, 3000);

	})
}

function synchroniserConsole(idUniqueRapport,idServeurRapport)
{
	$('#console').val("{l s='Synchronization with the Cloud in progress...' mod='pricestracker'}")
	$('#divConsole').show()
	
	$.ajax({
	  url: "{$lien}&cloudAjax&synchroniserConsole=1&codeCloud={$codeCloud|urlencode}&id_serveur="+idServeurRapport+"&id_crawling="+idUniqueRapport,
	}).done(function(data) {
		
	})

}
</script>


<h2 style="padding-left:150px; display:inline">{l s='List of the crawlings' mod='pricestracker'}</h2>
				<br class="clear"/>
				<br class="clear"/>
		  <div>
			<select type="text" name="crawling" id="crawling" style="width:80%" {if !$identification_compte}disabled="disabled"{/if} onchange="ouvrirCrawling()">
            	<option value="">{l s='Select a crawling' mod='pricestracker'}</option>
                {foreach name=crawlings key=k item=crawling from=$crawlings}
				
                    <option value="{$crawling->id_unique_crawling|htmlspecialchars}#{$crawling->id_serveur|htmlspecialchars}#{if !$crawling->date_fin}1{/if}">{$crawling->nom|htmlspecialchars}{if !$crawling->date_fin} ({l s='en cours' mod='pricestracker'}){/if}</option>
                {/foreach}
            
            </select> <a class="button" href="{$lien}&cloud&id={$concurrent.id_concurrents}">{l s='Refresh' mod='pricestracker'}</a>
			<br />
			<br />
<br />
		  </div>
          
          

<h2 style="padding-left:150px; display:inline">{l s='Detail of the selected crawling' mod='pricestracker'}</h2>
				<br class="clear"/>
				<br class="clear"/>
                
                <div id="details">
    <div class="bootstrap"><div class="alert alert-warning">			<button type="button" class="close" data-dismiss="alert">×</button>			{l s='No crawling selected ' mod='pricestracker'}</div></div>
    </div>
                
                <div id="divConsole" style="display:none"><br />

                <textarea id="console" style="width:100%; height:350px">{l s='Synchronization with the Cloud in progress...' mod='pricestracker'}</textarea>
                </div>
                
</form>
  </fieldset>


<iframe height="1" width="1" id="iframe_telechargement" style="color: white;"></iframe>
<!-- PricesTracker -->