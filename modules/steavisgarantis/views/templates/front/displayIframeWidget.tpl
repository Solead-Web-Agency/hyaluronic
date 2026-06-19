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


<style>
{literal}
    * {-webkit-font-smoothing: antialiased;}
    /*# WIDGET LATERAL*/
    #steavisgarantis{ display:block; width:100%; max-width:100%;  line-height:0; text-align:center; padding-bottom:18px} 
    #steavisgarantis {
    display:inline-block;
    margin: 15px 0px;
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#fefefe+7,fefefe+28,f3f3f3+52 */
    background: #ffffff; /* Old browsers */
    -moz-box-shadow:inset 0px 0px 0px 1px #f2f2f2; box-shadow:inset 0px 0px 0px 1px #f2f2f2;}
    
    .agWidget { color:#111111; font-family: 'Open Sans', sans-serif; font-weight:400}
    .rad{-moz-border-radius: 8px;-webkit-border-radius:8px; border-radius:8px;}
    /*boutons*/
    .agBt { display: inline-block; 
    background:#1c5399;
    border:1px solid #1c5399;
    color:#ffffff !important;
    font-size:10px; line-height:10px; letter-spacing:1px; text-transform:uppercase; text-align:center; padding:4px 10px; width:auto; text-decoration: none !important; }
    .agBt:hover { background:none; color:#1c5399 !important; text-decoration:none !important;}

    .agBtBig {font-size:11px; line-height:11px; padding:5px 14px;}
    .rad{-moz-border-radius: 8px;-webkit-border-radius:8px; border-radius:8px;}
    .rad4{-moz-border-radius:4px;-webkit-border-radius:4px; border-radius:4px;}
    /*background*/
    .bgGrey1{ background:#f9f9f9}
    .bgGrey2{ background:#f3f3f3}
    /*transition*/
    .agBt { -webkit-transition: background 0.4s ease; -moz-transition: background 0.4s ease;
    -ms-transition: background 0.4s ease;-o-transition: background 0.4s ease; transition: background 0.4s ease;}
{/literal}
</style>

<div id="steavisgarantis" class="agWidget rad {$sagLang|escape:'htmlall':'UTF-8'}" >
    <iframe width="200px" height="470px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
            src="{$domain|escape:'htmlall':'UTF-8'}/wp-content/plugins/ag-core/widgets/iframe/2/v/?id={$shopID|escape:'htmlall':'UTF-8'}">
    </iframe>
</div>