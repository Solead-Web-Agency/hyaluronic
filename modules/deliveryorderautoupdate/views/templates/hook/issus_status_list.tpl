{* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Helloshop
* Use, copy, modification or distribution of this source file without written
* license agreement from the Helloshop is strictly forbidden.
* In order to obtain a license, please contact us: modules@helloshop.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par Helloshop
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Helloshop est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la Helloshop a l'adresse:
*                  modules@helloshop.com
* ...........................................................................
* @copyright  2007-2023 Helloshop
* @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<h3>{l s='Issue #%1$d history' sprintf=$id mod='deliveryorderautoupdate'}</h3>
<table class="issue_status_history">
    {foreach $list as $item}
    <tr>
        <td>{$item.date|escape:'html':'UTF-8'}</td>
        <td>
            <div>{$item.status_name|escape:'html':'UTF-8'}</div>
            <div class="small">{$item.detail|escape:'html':'UTF-8'}</div>
    </td>
    </tr>
    {/foreach}
</table>