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

{if strpos($smarty.server.REQUEST_URI, "/commande") !== false} 
{else} 
<div id="steavisgarantis" align="center" style="
    padding: 5px 0;
    width: 100%;
    background: white;
">
    <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
            src="{$domain|escape:'htmlall':'UTF-8'}wp-content/plugins/ag-core/widgets/iframe/2/h/?id={$shopID|escape:'htmlall':'UTF-8'}">
    </iframe>
</div>
{/if} 
