{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-2021 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<script>
    $(document).ready(function () {
        $('input[name=\"wtd\"]').keyup(function () {
            $(this).val($(this).val().replace(/,/g, '.').replace(/[^\d.-]/g, ''));
        });
        $('#wtd').change(function(){
            returnValuePrefix();
        });
        returnValuePrefix();
    });

    function returnValuePrefix()
    {
        if ($('#wtd').find(":selected").val() == 1 || $('#wtd').find(":selected").val() == 2) {
            $("#pbc_form #value").parent().find('.input-group-addon').html('%');
            $('.exchangeRatesInfo').hide();
        } else if ($('#wtd').find(":selected").val() == 3 || $('#wtd').find(":selected").val() == 4) {
            $("#pbc_form #value").parent().find('.input-group-addon').html('{Context::getContext()->currency->iso_code}');
            $('.exchangeRatesInfo').show();
        }
    }
</script>