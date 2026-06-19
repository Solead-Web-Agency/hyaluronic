{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 *}

{$style_tab|escape:'htmlall':'UTF-8'}


<table width="100%" id="body" border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <!-- Invoicing -->
    <tr>
        <td colspan="12">
            {$addresses_tab|escape:'htmlall':'UTF-8'}
        </td>
    </tr>

    <tr>
        <td colspan="12" height="30">&nbsp;</td>
    </tr>

    <!-- TVA Info -->
    <tr>
        <td colspan="12">
            {$summary_tab|escape:'htmlall':'UTF-8'}
        </td>
    </tr>

    <tr>
        <td colspan="12" height="20">&nbsp;</td>
    </tr>

    <!-- Product -->
    <tr>
        <td colspan="12">
            {$product_tab|escape:'htmlall':'UTF-8'}
        </td>
    </tr>

    <tr>
        <td colspan="12" height="10">&nbsp;</td>
    </tr>

    <!-- TVA -->
    <tr>
        <!-- Code TVA -->
        <td colspan="6" class="left">
         </td>
        <td colspan="1">&nbsp;</td>
        <!-- Calcule TVA -->
        <td colspan="6" rowspan="5" class="right">
            {$total_tab|escape:'htmlall':'UTF-8'}
        </td>
    </tr>

    <tr>
        <td colspan="12" height="10">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="6" class="left">
            {$payment_tab|escape:'htmlall':'UTF-8'}
        </td>
        <td colspan="1">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="6" class="left">
            {$shipping_tab|escape:'htmlall':'UTF-8'}
        </td>

        <td colspan="1">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="12" height="20">&nbsp;</td>
    </tr>

    <tr>
        <!-- Code TVA -->
        <td colspan="6" class="left">
            {$tax_tab|escape:'htmlall':'UTF-8'}
        </td>

        <td colspan="1">&nbsp;</td>
        <!-- Calcule TVA -->
    </tr>

    <tr>
        <td colspan="12" height="20">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="12">
            {$vat_tab|escape:'htmlall':'UTF-8'}
        </td>
    </tr>

    <!-- Hook -->
    {if isset($HOOK_DISPLAY_PDF)}
        <tr>
            <td colspan="12" height="30">&nbsp;</td>
        </tr>

        <tr>
            {*<td colspan="2">&nbsp;</td>*}
            <td colspan="6" rowspan="2" class="right" style="margin-right: -50px;">
                {$HOOK_DISPLAY_PDF|escape:'htmlall':'UTF-8'}
            </td>
            <td colspan="1">&nbsp;</td>
        </tr>
    {/if}

    <tr>
        <td colspan="12" height="80">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="2">&nbsp;</td>
        <td colspan="10">
            <table>
                <tr>
                    <td>
                        <p>This is customized invoice for additional fields.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

</table>
