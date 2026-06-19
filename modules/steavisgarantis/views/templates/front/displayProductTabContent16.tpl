{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*
*}

<h3 class="page-product-heading steavisgarantis_tab" href="#ag-s" id="tab_steavisgarantis">{$reviewTabStr|escape:'htmlall':'UTF-8'}</h3>
<div id="ag-s16" class="{$sagLang|escape:'htmlall':'UTF-8'}">
<style>{literal}
.bar1 {animation-duration: 1s;  animation-name: newHeight1;  animation-iteration-count: 1;} @keyframes newHeight1 { from {height: 0%} to {height: {/literal}{$ratingValues['percent1']|escape:'htmlall':'UTF-8'}{literal}%} }
.bar2 {animation-duration: 1s;  animation-name: newHeight2;  animation-iteration-count: 1;} @keyframes newHeight2 { from {height: 0%} to {height: {/literal}{$ratingValues['percent2']|escape:'htmlall':'UTF-8'}{literal}%} }
.bar3 {animation-duration: 1s;  animation-name: newHeight3;  animation-iteration-count: 1;} @keyframes newHeight3 { from {height: 0%} to {height: {/literal}{$ratingValues['percent3']|escape:'htmlall':'UTF-8'}{literal}%} }
.bar4 {animation-duration: 1s;  animation-name: newHeight4;  animation-iteration-count: 1;} @keyframes newHeight4 { from {height: 0%} to {height: {/literal}{$ratingValues['percent4']|escape:'htmlall':'UTF-8'}{literal}%} }
.bar5 {animation-duration: 1s;  animation-name: newHeight5;  animation-iteration-count: 1;} @keyframes newHeight5 { from {height: 0%} to {height: {/literal}{$ratingValues['percent5']|escape:'htmlall':'UTF-8'}{literal}%} }
{/literal}
{literal}.steavisgarantisStar svg.note g { fill:  {/literal}{$starColor|escape:'htmlall':'UTF-8'}{literal} !important;}{/literal}
{literal}.steavisgarantisStats .item .name { color:  {/literal}{$starColor|escape:'htmlall':'UTF-8'}{literal} !important;}{/literal}
{literal}.steavisgarantisStats .item .stat .note { background:  {/literal}{$starColor|escape:'htmlall':'UTF-8'}{literal} !important;}{/literal}
</style>


{if $structuredFormat == "json-ld"}
    <script type="application/ld+json">
   {
   "@context": "http://schema.org/",
   "@type": "Product",
   "@id": "{$productUrl|escape:'htmlall':'UTF-8'}",
   "name": "{$sagProduct->name|escape:'htmlall':'UTF-8'}",
      "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "{$reviewsAverage|escape:'htmlall':'UTF-8'}",
      "reviewCount": "{$nbOfReviews|escape:'htmlall':'UTF-8'}",
      "bestRating": "5"
      }
   }
   </script>
    {foreach from=$reviews item=singleReview}
        <script type="application/ld+json">
            {
            "@context": "http://schema.org/",
            "@type": "Product",
            "@id": "{$productUrl|escape:'htmlall':'UTF-8'}",
            "name": "{$sagProduct->name|escape:'htmlall':'UTF-8'}",
            "review" : {
               "@type": "Review",
               "reviewRating": {
                     "@type": "Rating",
                     "ratingValue": "{$singleReview['rate']|escape:'htmlall':'UTF-8'}"
                     },
               "author": {
                     "@type": "Person",
                     "name": "{$singleReview['ag_reviewer_name']|escape:'htmlall':'UTF-8'}"
                     },
               "datePublished": "{$singleReview['date_time']|escape:'htmlall':'UTF-8'}",
               "reviewBody": "{$singleReview['review']|escape:'htmlall':'UTF-8'}"
            }
            }
        </script>
    {/foreach}
{/if}

{if $showStructured && $structuredFormat == "microdata"}

<div itemscope itemtype="http://schema.org/Product">
    <span style="display:none;" itemprop="name">{$sagProduct->name|escape:'htmlall':'UTF-8'}</span>
{/if}

    
<div>
<div id="ag-s">
    <div id="agWidgetMain" class="agWidget rad" >
    <div class="topBar">{l s='Reviews about this product' mod='steavisgarantis'}</div>
    <div class="inner bgGrey1" {if $structuredFormat == "microdata"}itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"{/if}>
    <div class="logoCont"><img alt="Logo Société des Avis Garantis" src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/{$sagLogo|escape:'htmlall':'UTF-8'}" width="150px" height="35px" class="logoAg">
    <a href="{$certificateUrl|escape:'htmlall':'UTF-8'}" class="agBt certificateBtn" target="_blank">{l s='Show attestation' mod='steavisgarantis'}</a>
    <p class="agReviewsLegal">
        <span>{l s='Reviews subject to control' mod='steavisgarantis'}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" height="16px" width="16px" id="ag-reviews-legal">
            <g fill="#7a7a7a"><path d="M8 2a6 6 0 1 0 6 6 6 6 0 0 0-6-6Zm0 11a5 5 0 1 1 5-5 5 5 0 0 1-5 5Z"></path><path d="M8 6.85a.5.5 0 0 0-.5.5v3.4a.5.5 0 0 0 1 0v-3.4a.5.5 0 0 0-.5-.5zM8 4.8a.53.53 0 0 0-.51.52v.08a.47.47 0 0 0 .51.47.52.52 0 0 0 .5-.5v-.12A.45.45 0 0 0 8 4.8z"></path></g>
        </svg>
    </p>
    </div>
    <div class="statCont">
        <div class="steavisgarantisStats">
        <div class="item"><span class="stat"><div class="note bar1" style="height:{$ratingValues['percent1']|escape:'htmlall':'UTF-8'}%"><span class="value">{$ratingValues['nb1']|escape:'htmlall':'UTF-8'}</span></div></span><span class="name">1&starf;</span></div>
        <div class="item"><span class="stat"><div class="note bar2" style="height:{$ratingValues['percent2']|escape:'htmlall':'UTF-8'}%"><span class="value">{$ratingValues['nb2']|escape:'htmlall':'UTF-8'}</span></div></span><span class="name">2&starf;</span></div>
        <div class="item"><span class="stat"><div class="note bar3" style="height:{$ratingValues['percent3']|escape:'htmlall':'UTF-8'}%"><span class="value">{$ratingValues['nb3']|escape:'htmlall':'UTF-8'}</span></div></span><span class="name">3&starf;</span></div>
        <div class="item"><span class="stat"><div class="note bar4" style="height:{$ratingValues['percent4']|escape:'htmlall':'UTF-8'}%"><span class="value">{$ratingValues['nb4']|escape:'htmlall':'UTF-8'}</span></div></span><span class="name">4&starf;</span></div>
        <div class="item"><span class="stat"><div class="note bar5" style="height:{$ratingValues['percent5']|escape:'htmlall':'UTF-8'}%"><span class="value">{$ratingValues['nb5']|escape:'htmlall':'UTF-8'}</span></div></span><span class="name">5&starf;</span></div>
        </div>
    </div>
    <div class="reviewCont"> <div class="reviewGlobal">
        <div class="largeNote"><big>{2 * $reviewsAverage|escape:'htmlall':'UTF-8'}</big>/10<p><br>{l s='Based on' mod='steavisgarantis'} {$nbOfReviews|escape:'htmlall':'UTF-8'} {if $nbOfReviews==1}{l s='review' mod='steavisgarantis'}{else}{l s='reviews' mod='steavisgarantis'}{/if}</p></div>
        </div></div>
        {if $structuredFormat == "microdata"}
            <meta itemprop="ratingValue" content="{$reviewsAverage|escape:'htmlall':'UTF-8'}" />
            <meta itemprop="reviewCount" content="{$nbOfReviews|escape:'htmlall':'UTF-8'}" />
            <meta itemprop="bestRating" content="5" />
        {/if}
    </div>
    <ul class="reviewList">
    {$i=0}
    {foreach from=$reviews item=singleReview}    

    <li class="bgGrey{$i % 2|escape:'htmlall':'UTF-8'}" {if $structuredFormat == "microdata"}itemprop="review" itemscope itemtype="https://schema.org/Review"{/if}>
        <div class="author" {if $structuredFormat == "microdata"}itemprop="author" itemscope itemtype="https://schema.org/Person"{/if}>
            <img alt="Image utilisateur" class="authorAvatar" width="24px" height="24px" src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/ico_user.png" />
            <span {if $structuredFormat == "microdata"}itemprop="name"{/if}>{$singleReview['ag_reviewer_name']|escape:'htmlall':'UTF-8'}</span>
            {if $singleReview['translated']}
                <img class="agFlag" src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/flag-{$singleReview['source_lang']|escape:'htmlall':'UTF-8'}.png" />
            {/if}
       <br><span class="time"><span class="published">{l s='Published' mod='steavisgarantis'} {$singleReview['date_time']|escape:'htmlall':'UTF-8'}</span>{if $singleReview['order_date']} ({l s='Order date: ' mod='steavisgarantis'}{$singleReview['order_date']|escape:'htmlall':'UTF-8'}){/if}</span></div>
    <div class="customAnswers">
    {foreach from=$singleReview['customAnswers'] item=customAnswer}    
        <span class="customAnswerLabel">
            {if $customAnswer['question_label'] == "humanHeight"}
            {l s='Height :' mod='steavisgarantis'}
            {elseif $customAnswer['question_label'] == "humanWeight"}
            {l s='Weight :' mod='steavisgarantis'}
            {else}
                {$customAnswer['question_label']|escape:'htmlall':'UTF-8'}
            {/if}
        
        </span>
        <span class="customAnswerContent">{$customAnswer['answer']|escape:'htmlall':'UTF-8'}</span>
        {if $customAnswer['unit']}
            <span class="customAnswerUnit">{$customAnswer['unit']|escape:'htmlall':'UTF-8'}</span>
        {/if}
    {/foreach}
    </div>
    {if $structuredFormat == "microdata"}
    <meta itemprop="datePublished" content="{$singleReview['date_time']|escape:'htmlall':'UTF-8'}">
    {/if}
    <div class="reviewTxt">
		<div class="steavisgarantisStar">
			<span></span>
			<div class="animate" style="width:{20 * $singleReview['rate']|escape:'htmlall':'UTF-8'}%;position:relative;overflow:hidden;">
				<svg class="note" version="1.0" xmlns="http://www.w3.org/2000/svg"
				width="250.000000pt" height="68.000000pt" viewBox="0 0 250.000000 68.000000"
				 preserveAspectRatio="xMidYMid meet">
                    <g fill="rgba(250,0,0,1)">
                        <path d="M 16.02 28.17   L 22.31 11.82   A 0.34 0.34 0.0 0 1 22.95 11.82   L 29.11 28.05   A 0.34 0.34 0.0 0 0 29.43 28.27   L 43.29 28.24   A 0.34 0.34 0.0 0 1 43.53 28.82   L 32.81 39.62   A 0.34 0.34 0.0 0 0 32.73 39.97   L 38.17 56.11   A 0.34 0.34 0.0 0 1 37.67 56.51   L 22.84 47.29   A 0.34 0.34 0.0 0 0 22.48 47.29   L 7.31 56.49   A 0.34 0.34 0.0 0 1 6.81 56.09   L 12.27 40.15   A 0.34 0.34 0.0 0 0 12.19 39.80   L 1.50 28.79   A 0.34 0.34 0.0 0 1 1.75 28.21   L 15.69 28.39   A 0.34 0.34 0.0 0 0 16.02 28.17   Z"/>
                        <path d="M 79.28 28.29   L 93.14 28.22   A 0.34 0.34 0.0 0 1 93.38 28.80   L 82.66 39.66   A 0.34 0.34 0.0 0 0 82.58 40.01   L 88.04 56.15   A 0.34 0.34 0.0 0 1 87.54 56.55   L 72.70 47.28   A 0.34 0.34 0.0 0 0 72.34 47.28   L 57.15 56.50   A 0.34 0.34 0.0 0 1 56.65 56.10   L 62.13 40.14   A 0.34 0.34 0.0 0 0 62.05 39.79   L 51.34 28.78   A 0.34 0.34 0.0 0 1 51.59 28.20   L 65.53 28.40   A 0.34 0.34 0.0 0 0 65.86 28.18   L 72.17 11.87   A 0.34 0.34 0.0 0 1 72.80 11.87   L 78.96 28.07   A 0.34 0.34 0.0 0 0 79.28 28.29   Z"/>
                        <path d="M 129.12 28.28   L 142.99 28.23   A 0.34 0.34 0.0 0 1 143.23 28.81   L 132.51 39.64   A 0.34 0.34 0.0 0 0 132.43 39.99   L 137.90 56.14   A 0.34 0.34 0.0 0 1 137.39 56.54   L 122.57 47.30   A 0.34 0.34 0.0 0 0 122.21 47.30   L 107.02 56.48   A 0.34 0.34 0.0 0 1 106.52 56.08   L 111.97 40.14   A 0.34 0.34 0.0 0 0 111.89 39.79   L 101.22 28.79   A 0.34 0.34 0.0 0 1 101.47 28.21   L 115.40 28.39   A 0.34 0.34 0.0 0 0 115.73 28.17   L 122.03 11.80   A 0.34 0.34 0.0 0 1 122.66 11.80   L 128.80 28.06   A 0.34 0.34 0.0 0 0 129.12 28.28   Z"/>
                        <path d="M 178.98 28.27   L 192.81 28.24   A 0.34 0.34 0.0 0 1 193.06 28.82   L 182.39 39.63   A 0.34 0.34 0.0 0 0 182.31 39.98   L 187.74 56.13   A 0.34 0.34 0.0 0 1 187.24 56.53   L 172.41 47.29   A 0.34 0.34 0.0 0 0 172.05 47.29   L 156.88 56.48   A 0.34 0.34 0.0 0 1 156.38 56.07   L 161.83 40.13   A 0.34 0.34 0.0 0 0 161.75 39.78   L 151.05 28.79   A 0.34 0.34 0.0 0 1 151.30 28.21   L 165.26 28.39   A 0.34 0.34 0.0 0 0 165.58 28.17   L 171.86 11.80   A 0.34 0.34 0.0 0 1 172.50 11.80   L 178.66 28.05   A 0.34 0.34 0.0 0 0 178.98 28.27   Z"/>
                        <path d="M 220.13 27.69   L 226.23 12.00   Q 226.58 11.10 226.92 12.00   L 232.93 27.72   Q 233.14 28.26 233.72 28.26   L 247.19 28.27   Q 247.99 28.27 247.43 28.84   L 237.01 39.40   A 0.90 0.89 58.3 0 0 236.80 40.32   L 242.11 56.20   Q 242.35 56.94 241.69 56.53   L 227.01 47.45   Q 226.61 47.21 226.22 47.44   L 211.29 56.43   Q 210.52 56.89 210.81 56.04   L 216.12 40.39   Q 216.27 39.94 215.94 39.60   L 205.48 28.80   Q 204.93 28.23 205.72 28.24   L 219.11 28.37   Q 219.86 28.38 220.13 27.69   Z"/>
                    </g>
				</svg>
			</div>
		</div>
        <div {if $structuredFormat == "microdata"}itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"{/if}>
     <span class="metaHide" {if $structuredFormat == "microdata"}itemprop="ratingValue"{/if}>{$singleReview['rate']|escape:'htmlall':'UTF-8'}</span>
     </div>
    <p class="" {if $structuredFormat == "microdata"}itemprop="reviewBody"{/if}>{$singleReview['review']|escape:'htmlall':'UTF-8'}{if $singleReview['translated']} <span style='font-style:italic;'>({l s='Translated review' mod='steavisgarantis'})</span>{/if}</p>
    {if $singleReview['answer_text']}
    <div class="reponse"><span><img src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/ico_pen.png" height="12">{l s='Merchant\'s answer' mod='steavisgarantis'}</span>
    <p>{$singleReview['answer_text']|escape:'htmlall':'UTF-8'}</p></div>
    {/if}
    </div>
    </li>
    {$i = $i + 1}
    {/foreach}
    </ul>

    <img id="chargement" src="{$modules_dir|escape:'htmlall':'UTF-8'}steavisgarantis/views/img/page.gif" style="display:none">
    {if $nbOfReviews > Configuration::get('steavisgarantis_maxReviewPerPage')}
        <div class="inner2">
        <a class="agBt rad4 agBtBig" href="#more-reviews" id="more-reviews"  onclick="return showMoreReviews({$nbOfReviews|escape:'html':'UTF-8'}, 2, {$maxReviewsPage|escape:'htmlall':'UTF-8'}, '{$modules_dir|escape:'html':'UTF-8'}','{$id_lang|escape:'html':'UTF-8'}');" rel="2">{l s='Show more reviews' mod='steavisgarantis'}</a>
        </div>
    {/if}
    </div>
</div>
</div>
{if $showStructured && $structuredFormat == "microdata"}
</div>
{/if}
<br><br>
</div>
<script type="text/javascript">
    var reviewTabStr = "{$reviewTabStr|escape:'htmlall':'UTF-8'}";
    var maxReviewsPage = "{$maxReviewsPage|escape:'htmlall':'UTF-8'}";

    document.addEventListener("DOMContentLoaded", (event) => {
        var legalContent = "<ul>" +
                "<li>{l s='Reviews submitted are subject to control.' mod='steavisgarantis'}</li>" +
                "<li>{l s='Reviews are listed in descending chronological order.' mod='steavisgarantis'}</li>" +
                "<li>{l s='Reviews were submitted without any reward.' mod='steavisgarantis'}</li>" +
                "<li>{l s='The maximum publication period for reviews is 30 days.' mod='steavisgarantis'}</li>" +
                "<li>{l s='The maximum retention period for reviews corresponds to the period of the merchant\'s contract.' mod='steavisgarantis'}</li>" +
            "</ul>";

        tippy('#ag-reviews-legal', {
            content: legalContent,
            allowHTML: true,
            placement: 'bottom'
        });
    });
</script>