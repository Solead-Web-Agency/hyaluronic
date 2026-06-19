{*
* A Route Prestashop Extension that adds secure shipping
* protection to your orders
*
* Php version 7.0^
*
* @author    Route Development Team <dev@routeapp.io>
* @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
* @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
*}

<script>
    window.addEventListener('load', function() {
        const fee = '{$route_fee|escape:'javascript':'UTF-8'}';
        let table = document.getElementById('order-items').getElementsByClassName("order-confirmation-table")[0].getElementsByTagName("table")[0];
        let row = table.insertRow(2);
        let cell1 = row.insertCell(0);
        let cell2 = row.insertCell(1);
        cell1.innerHTML = "Route Shipping Protection";
        cell2.innerHTML = fee;
    });
</script>

{if !empty($route_thankyou_page_asset) }
  {$route_thankyou_page_asset nofilter}
    <div>
        <span class="os-order-number" style="display:none;">{$order_id|escape:'javascript':'UTF-8'}</span>
    </div>
  <script>
    window.addEventListener('load', function() {
      if (window.Routeapp) {
        window.Routeapp.analytics.send({
          action: 'render',
          event_category: 'thank-you-page-asset',
          event_label: 'thank-you-page',
          value: true
        });
      }
    });
  </script>

{/if}
