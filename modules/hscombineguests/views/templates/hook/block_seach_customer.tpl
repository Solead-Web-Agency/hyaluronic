{**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
 <script type="text/javascript">
    combineGuestUrl = {$combine_guest_url|escape:'quotes':'UTF-8'};
    $(document).ready(function() {
        $('#container-customer').each(function() {
            $(this).find('.block_combine').insertBefore(this);
         });
         new HsCombineGuests({
             combineGuestUrl: combineGuestUrl,
             idCustomer: {$id_customer|intval}
         }).handleEvent();
     });
 </script>
{if $is_prestashop_178}
</div>
  <div class="row">
     <div class="col block_combine block_combine_176">
        <div class="card">
            <h3 class="card-header">
            <i class="material-icons">group</i>
            {l s='Combine guests' mod='hscombineguests'}
            </h3>
            <div class="card-body">
                <div class="col-md-offset-3 col-lg-6">
                    <div class="input-group search search-with-icon">
                        <input class='search_customer' type="text" placeholder="{l s='Search for customers/guests' mod='hscombineguests'}"  />
                    </div>
                </div>
                <div class="combine_guest"></div>
            </div>
        </div>
    </div>
 {elseif $is_prestashop_176}
     <div class="col block_combine block_combine_176">
        <div class="card">
            <h3 class="card-header">
            <i class="material-icons">group</i>
            {l s='Combine guests' mod='hscombineguests'}
            </h3>
            <div class="card-body">
                <div class="col-md-offset-3 col-lg-6">
                    <div class="input-group search search-with-icon">
                        <input class='search_customer' type="text" placeholder="{l s='Search for customers/guests' mod='hscombineguests'}"  />
                    </div>
                </div>
                <div class="combine_guest"></div>
            </div>
        </div>
    </div>
    </div>
  <div class="row">
{elseif $is_prestashop_1617}
</div>
<div class="block_combine row">
    <div class="col-lg-12">
        <div class="panel form-horizontal cleafix">
            <div class="panel-heading"><i class="icon-user"></i> &nbsp; {l s='Combine guests' mod='hscombineguests'}</div>
            <div class="form-group">
                <div class="col-md-offset-3 col-lg-5">
                    <div class="input-group">
                        <input class='search_customer' type="text" placeholder="{l s='Search for customers/guests' mod='hscombineguests'}"  />
                        <span class="input-group-addon"><i class="icon-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="combine_guest row"></div>
        </div>
    </div>
</div>
<div class="row">
{else}
    &nbsp;</div>
    <div class ="block_combine">
        <fieldset id="customer_part">
            <legend><img src="../img/admin/tab-customers.gif">{l s='Combine guests' mod='hscombineguests'}</legend>
            <label>{l s='Search customers' mod='hscombineguests'}</label>
            <div class="margin-form">
                <input class='search_customer' type="text" value=""  placeholder="{l s='Search customers by typing the first letters of his/her name' mod='hscombineguests'}"/>
            </div>
            <div class="combine_guest"></div>
        </fieldset>
{/if}