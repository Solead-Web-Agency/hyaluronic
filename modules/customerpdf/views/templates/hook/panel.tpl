{*
* Panneau Documents PDF clients - utilise par les hooks
* displayAdminCustomers et displayAdminOrderSide
*}
<div class="panel" id="customerpdf-panel">
    <div class="panel-heading">
        <i class="icon-file-text"></i> {l s='Documents PDF du client' mod='customerpdf'}
        <span class="badge">{$customerpdf_files|@count}</span>
    </div>

    {if $customerpdf_flash}
        <div class="alert alert-{$customerpdf_flash_type|escape:'html':'UTF-8'}">
            {$customerpdf_flash|escape:'html':'UTF-8'}
        </div>
    {/if}

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Document' mod='customerpdf'}</th>
                    {if !$customerpdf_compact}<th>{l s='Taille' mod='customerpdf'}</th>{/if}
                    <th>{l s='Ajoute le' mod='customerpdf'}</th>
                    <th class="text-right">{l s='Actions' mod='customerpdf'}</th>
                </tr>
            </thead>
            <tbody>
                {if $customerpdf_files}
                    {foreach from=$customerpdf_files item=f}
                        <tr>
                            <td>
                                <span class="label {$f.type_class|escape:'html':'UTF-8'}">{$f.type_label|escape:'html':'UTF-8'}</span>
                                <i class="icon-file-text"></i>
                                {$f.original_name|escape:'html':'UTF-8'}
                            </td>
                            {if !$customerpdf_compact}<td>{$f.size_human|escape:'html':'UTF-8'}</td>{/if}
                            <td>{dateFormat date=$f.date_add full=0}</td>
                            <td class="text-right">
                                <a class="btn btn-default btn-xs" href="{$f.view_link|escape:'html':'UTF-8'}" target="_blank" title="{l s='Voir' mod='customerpdf'}">
                                    <i class="icon-eye"></i>
                                </a>
                                <a class="btn btn-default btn-xs" href="{$f.download_link|escape:'html':'UTF-8'}" title="{l s='Telecharger' mod='customerpdf'}">
                                    <i class="icon-download"></i>
                                </a>
                                <a class="btn btn-danger btn-xs" href="{$f.delete_link|escape:'html':'UTF-8'}" title="{l s='Supprimer' mod='customerpdf'}"
                                   onclick="return confirm('{l s='Supprimer ce document ?' mod='customerpdf' js=1}');">
                                    <i class="icon-trash"></i>
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="{if $customerpdf_compact}3{else}4{/if}" class="text-muted">
                            {l s='Aucun document pour ce client.' mod='customerpdf'}
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>

    <form action="{$customerpdf_action_url|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data" class="form-inline customerpdf-form">
        <input type="hidden" name="action" value="upload" />
        <input type="hidden" name="id_customer" value="{$customerpdf_id_customer|intval}" />
        {if $customerpdf_id_order}
            <input type="hidden" name="id_order" value="{$customerpdf_id_order|intval}" />
        {/if}
        <div class="form-group">
            <select name="doc_type" class="form-control fixed-width-xl">
                {foreach from=$customerpdf_doc_types key=tkey item=tlabel}
                    <option value="{$tkey|escape:'html':'UTF-8'}"{if $tkey == 'facture'} selected="selected"{/if}>{$tlabel|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <input type="file" name="pdf_file" accept="application/pdf,.pdf" required="required" />
        </div>
        <button type="submit" name="submitUploadPdf" class="btn btn-primary">
            <i class="icon-upload"></i> {l s='Ajouter un PDF' mod='customerpdf'}
        </button>
        <p class="help-block">{l s='Format accepte : PDF. Taille max : 10 Mo.' mod='customerpdf'}</p>
    </form>
</div>
