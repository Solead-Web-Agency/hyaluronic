{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}
 
 <div id="warning">
    <div class="swal-icon swal-icon--warning">
        <span class="swal-icon--warning__body">
          <span class="swal-icon--warning__dot"></span>
      </span>
  </div>
  <div class="swal-title">{l s='Trial period has expired' mod='deliveryorderautoupdate'}</div>
  <div class="bootstrap"><a class="btn btn-primary" style="text-transform: none;font-size: 14px;" target="_blank" href="https://helloshop.com/{$lang|escape:'html':'UTF-8'}/modules-pour-prestashop/2-module-tracking-center-pour-prestashop.html">{l s='Buy module now' mod='deliveryorderautoupdate'}</a></div>
</div>
<script type="text/javascript">
    swal({
        content: document.getElementById('warning'),
        button: false
    })
</script>