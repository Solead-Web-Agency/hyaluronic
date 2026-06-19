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
{foreach $histories as $history}
<tr>
    <td>
        <div class="date">
            {str_replace(' 00:00:00', '', date($date_format_full, strtotime($history.date)))|escape:'htmlall':'UTF-8'}
        </div>
    </td>
    <td>
        <div class="status">
            {$history.status_name|escape:'htmlall':'UTF-8'}
        </div>
    </td>
    <td>
        <div class="detail">
            {$history.detail|escape:'htmlall':'UTF-8'}
        </div>
    </td>
</tr>
{/foreach}