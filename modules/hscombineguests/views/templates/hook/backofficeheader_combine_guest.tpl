{**
* Combine guests for PrestaShop
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script type="text/javascript">
    $(document).ready(function (){
        // just try to make the design more beatiful
        if ($('.table th').hasClass('actions')){
            $('.actions').prev().remove();
            $('.table thead tr:first th:last').remove();
        }
    });
</script>
<style>
    body.admincombineguest .table tbody > tr > td{
        padding: 10px 7px;
    }
</style>
