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

<br /><br />


<!-- PricesTracker -->
<form action="" method="post">
  <fieldset>
	<legend>{l s='Upgrade' mod='pricestracker'} </legend>
	<h3>{l s='This version does not have some options. To use them you can go higher.' mod='pricestracker'}</h3>
	<p>
 {l s='To upgrade your version, please contact us' mod='pricestracker'} : <a href="mailto:contact@idia-tech.com">contact@idia-tech.com.com</a>
	<p><br />{Context::getContext()->context->language->id)}{Language::getIsoById(Context::getContext()->context->language->id)}
<img src="https://www.storeinterfacer.com/images/pricestracker/comparatif-versions{if Language::getIsoById(Context::getContext()->context->language->id)!='fr'}_en{/if}.png" /></p>
  </fieldset>
</form>
<!-- PricesTracker -->