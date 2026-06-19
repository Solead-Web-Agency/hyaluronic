{**
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA PL MILOSZ MYSZCZUK VATEU: PL9730945634
* @copyright 2010-2024 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<div class="card">
    <div class="card-body">
    <div class="card-title">{l s='Define country restrictions' mod='hbc'}</div>
    <div style="padding:10px">
        <input type="hidden" name="savehbchidden" value="1"/>
        <div class="bootstrap">
            <div class="alert alert-info">
                {l s='Select countries where module will hide item you currently edit' mod='hbc'}<br/>
                {l s='Module will check the visitor origin with geolocation tools and depending on used settings here hide the product or not.' mod='hbc'}<br/>
            </div>
        </div>
        {if Tools::isSubmit("actionhbc")}
            {if Tools::getValue("actionhbc")=="savehbc"}
                <div class="bootstrap">
                    <div class="alert alert-success">
                        {l s='Changes saved' mod='hbc'}
                    </div>
                </div>
            {/if}
        {/if}
        <input type="hidden" name="actionhbc" value="savehbc"/>
        <table id="hbcTable" class="hbcTable table table-responsive table-striped">
            <tr>
                <th colspan="1">
                    <input type="text" id="hbcInput" onkeyup="mySearchFunction()" placeholder="{l s='Search for country ...' mod='hbc'}">
                </th>
                <th>
                    <input type="checkbox" name="selectAll" class="hbcSelectAll"/>
                </th>
            </tr>
            {foreach Country::getCountries(Context::getContext()->language->id) as $country}
                <tr>
                    <td>
                        {$country.name}
                    </td>
                    <td>
                        <input {if isset($restrictions[$country.id_country])} checked="checked" {/if}type="checkbox" name="selectedCountriesHbc[]" value="{$country.id_country}"/>
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
    </div>
</div>

<style>
    .hbcTable tr td {
        cursor:pointer;
    }
    .hbcTable tr:hover td {
        background:#fffdc7;
    }
</style>

<script>
    function mySearchFunction() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("hbcInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("hbcTable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    $(function () {
        $(".hbcTable tr td").on("click", function (e) {
            if (!$(e.target).is(':checkbox')) {
                var $checkbox = $(this).parent().find("input[type='checkbox']");
                $checkbox.click();
            }
            $(this).toggleClass('selected');
        });

        $('.hbcSelectAll').click(function(){
            if ($(this).is(':checked')) {
                $("#hbcTable td input").prop("checked", true);
            } else {
                $("#hbcTable td input").prop("checked", false);
            }
        });
    });
</script>
