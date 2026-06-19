<script>
    function displayModulePageTab(tab) {
        $('.module_page_tab').hide();
        $('.tab-row.active').removeClass('active');
        $('#module_page_' + tab).show();
        $('#module_page_link_' + tab).parent().addClass('active');
    }
</script>

<div class="productTabs">
    <ul class="tab nav nav-tabs">
        <li class="tab-row active">
            <a class="tab-page" id="module_page_link_formSettings" href="javascript:displayModulePageTab('formSettings');"><i class="icon-wrench"></i> {l s='Settings form' mod='hbc'}</a>
        </li>
        <li class="tab-row">
            <a class="tab-page" id="module_page_link_formExclusions" href="javascript:displayModulePageTab('formExclusions');"><i class="icon-wrench"></i> {l s='Exclusions' mod='hbc'}</a>
        </li>
        <li class="tab-row">
            <a class="tab-page" id="module_page_link_formMass" href="javascript:displayModulePageTab('formMass');"><i class="icon-wrench"></i> {l s='Mass settings' mod='hbc'}</a>
        </li>
        <li class="tab-row" style="float:right;">
            <a class="tab-page" id="module_page_link_update" href="javascript:displayModulePageTab('update');"><i class="icon-refresh"></i> {l s='Update' mod='hbc'}</a>
        </li>
    </ul>
</div>
<div id="module_page_formSettings" class="panel module_page_tab">
    {$form_settings nofilter}
</div>
<div id="module_page_formExclusions" class="panel module_page_tab" style="display: none;">
    <div class="alert alert-info">
        {l s='If you want exclude some customers or customers groups from country check - define these settings here' mod='hbc'}
    </div>
    {$form_exclusions nofilter}
</div>
<div id="module_page_formMass" class="panel module_page_tab" style="display: none;">
    <div class="alert alert-info">
        {l s='Here you can quickly generate products visibility for selected countries by associations with manufacturer and/or categories' mod='hbc'}
    </div>
    {$form_mass nofilter}
</div>
<div id="module_page_update" class="panel module_page_tab" style="display: none;">
    <div class="alert alert-info">
        {l s='This form allows to check availability of updates' mod='hbc'}
    </div>
    {$form_updates nofilter}
</div>