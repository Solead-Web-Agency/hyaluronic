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
	<legend>{l s='Programmed threshold' mod='pricestracker'}</legend>

 

    <label>{l s='Programmed threshold' mod='pricestracker'} :</label>
    <div class="margin-form">
            <textarea name="codeSeuil" cols="70" rows="5" id="nom">{$codeSeuil|htmlspecialchars}</textarea><br />
{l s='Input variables : $product =  Prestashop object Product (with class Product)' mod='pricestracker'}<br />
    {l s='Use return to return the value of the threshold' mod='pricestracker'}<br />
{l s='Don\'t forget the final semicolon (;)' mod='pricestracker'}</div>
		


		




    <input type="submit" name="submit" id="submit" value="{l s='Submit' mod='pricestracker'}" class="button" style="margin-left:250px" />
   
   
   
</fieldset>
 
 

</form>


{if $version2 eq 'MEGA'}
  <fieldset>
	<legend>{l s='Function for the threshold' mod='pricestracker'}</legend>
		  

    <table width="100%" border="0" cellspacing="5">
  <tr>
    <td width="1" valign="top">
    <form action="" method="post">

    <input name="fonction" type="text" id="fonction" value="" size="70" /><br />
<br />
        <input type="submit" name="okFonction" id="okFonction" value="{l s='OK' mod='pricestracker'}" class="button" style="margin-left:70px" />
        
      </form>
</td>
    <td valign="top" style="padding-left:70px">
<a href="#" onclick="$('#fonction').val('DIV(5)')">{l s='Divide my price by 5' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('MULT(5)')">{l s='Multiply my price by 5' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('ADD(5)')">{l s='Add 5 to my price' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('SUB(5)')">{l s='Subtract 5 to my price' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('DIV_COMPETITOR(5)')">{l s='Divide the competitor\'s price by 5' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('MULT_COMPETITOR(5)')">{l s='Multiply the competitor\'s price by 5' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('ADD_COMPETITOR(5)')">{l s='Add 5 to the competitor\'s price' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('SUB_COMPETITOR(5)')">{l s='Subtract 5 to the competitor\'s price' mod='pricestracker'}</a><br />
<a href="#" onclick="$('#fonction').val('COMB(1000)')">{l s='My  price of combination (id_attribute)' mod='pricestracker'}</a></td>
  </tr>
</table>


            
  </fieldset>
{/if}
  
  
<form action="https://www.storeinterfacer.com/pricestracker_regle.php" method="post">

    <fieldset>
	<legend>{l s='Ask to idIA Tech to write the threshold' mod='pricestracker'}</legend>
	  <label>{l s='What do you want the threshold to do?' mod='pricestracker'} :</label>
		  <div class="margin-form">
   	<textarea name="message" cols="70" rows="5" id="message"></textarea>
    		</div>
    
    
        <br />

        <input type="submit" name="submit" id="submit" value="{l s='Send' mod='pricestracker'}" class="button" style="margin-left:250px" />
    </fieldset>

</form>
<!-- PricesTracker -->