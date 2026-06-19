{**
* OrderEdit
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2021 silbersaiten
* @version   2.0.7
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}

{foreach $discounts as $discount}
    <tr>
        <td colspan="4" style="padding:0.6em 0.4em;text-align:right">{$voucher} {$discount.name}</td>
        <td style="padding:0.6em 0.4em;text-align:right">
            {if $discount.value != 0.00}-{/if}
            {Tools::displayPrice($discount['value'], $orderedit_currency, false)}
        </td>
    </tr>
{/foreach}
