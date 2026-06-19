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

<div class="grc-product-summary" 
    data-product-id="{$productId|escape:'htmlall':'UTF-8'}" 
    {if $productSku}data-product-sku="{$productSku|escape:'htmlall':'UTF-8'}"{/if} 
    style="display: none;">
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        let widgetSummary = document.querySelector('div.grc-product-summary');
        if (document.getElementsByTagName('h1').length) {
            let firstH1 = document.getElementsByTagName('h1')[0];
            firstH1.parentNode.insertBefore(widgetSummary, firstH1.nextSibling);
        }
        widgetSummary.style.display = "block";
    });
</script>