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
{if $issue}
{if !isset($hideEdit) || !$hideEdit}
<div class="issue col-sm-6" id_issue="{$issue.id_issue|escape:'htmlall':'UTF-8'}">
    <div style="display: flex;">
        <label>{l s='Issue' mod='deliveryorderautoupdate'}:</label>
        <div class="editable edit_issue" id_issue="{$issue.issue_type|escape:'htmlall':'UTF-8'}" style="position:relative;margin-left: 10px;flex:1">
            <span class="issue_name">{$issue.issue|escape:'htmlall':'UTF-8'}</span>
            <i class="icon-pencil openmenu"></i>
        </div>
    </div>
</div>
<div class="status col-sm-6" id_issue="{$issue.id_issue|escape:'htmlall':'UTF-8'}">
    <div style="display: flex;">
        <label>{l s='Current status' mod='deliveryorderautoupdate'}:</label>
        <div class="editable edit_issue_status" id_issue_status="{if $issue.histories && count($issue.histories)}{$issue.histories[0].status|escape:'htmlall':'UTF-8'}{/if}" style="position:relative;margin-left: 10px;flex:1">
            <span class="status_name">{if $issue.histories && count($issue.histories)}{$issue.histories[0].status_name|escape:'htmlall':'UTF-8'}{/if}</span>
            <i class="icon-pencil openmenu"></i>
        </div>
    </div>
</div>
{/if}
 <table style="width: 100%" class="issue-list table dl_dashboard table_form">
    <thead>
        <tr>
            <th>{l s='Date' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Status' mod='deliveryorderautoupdate'}</th>
            <th>{l s='Detail' mod='deliveryorderautoupdate'}</th>
        </tr>
    </thead>
    <tbody>
        {if count($issue.histories)}
        {include file='../hook/issue-row.tpl' histories=$issue.histories}
        {else}
        <tr class="no-status"><td colspan="2">{l s='No status' mod='deliveryorderautoupdate'}</td></tr>
        {/if}
    </tbody>
</table>
{else}
<button class="btn btn-default create_issue">{l s='Create an issue' mod='deliveryorderautoupdate'}</button>
{/if}