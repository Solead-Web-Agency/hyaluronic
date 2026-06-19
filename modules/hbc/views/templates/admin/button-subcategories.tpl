{**
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
* @copyright 2010-2024 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER
* support@mypresta.eu
*}

<script>
    function checkAllSubcats(parentTree) {
        $('#' + parentTree + ' input[type=checkbox]:checked').each(function (e) {
            $(this).parent().parent().find('input[type=checkbox]').attr('checked', true);
            $(this).parent().parent().find('.tree-item-name').addClass('tree-selected');
        });
    }

    function uncheckAllsubcats(parentTree) {
        $('#' + parentTree + ' input[type=checkbox]:checked').each(function (e) {
            $(this).parent().parent().find('input[type=checkbox]').attr('checked', false);
            $(this).parent().parent().find('.tree-item-name').removeClass('tree-selected');
        });
    }
</script>
<div class="row">
    <div class="form-group margin-form ">
        <div class="col-lg-9">
            <span onclick="checkAllSubcats('hbc-categories-tree'); return false;"
                  id="check-all-associated-subcategories-tree-bulk" class="btn btn-default">
                    <i class="icon-check-sign"></i>	{l s='Check all subcategories of checked categories' mod='hbc'}
                </span>
            <span onclick="uncheckAllsubcats('hbc-categories-tree'); return false;"
                  id="uncheck-all-associated-subcategories-tree-bulk" class="btn btn-default">
                    <i class="icon-check-empty"></i> {l s='Uncheck all subcategories of checked categories' mod='hbc'}
                </span>
        </div>
    </div>
</div>