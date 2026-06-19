<div class="alert alert-info">
  {l s='To execute your cron tasks, please insert the following line in your cron tasks manager:' mod='exportproducts'}
  <br>
  <br>
  <ul class="list-unstyled{if $schedule_tab} schedule_tab{/if}">
    <li><code>0 * * * * curl "{$schedule_url|escape:'htmlall':'UTF-8'}"</code></li>
  </ul>
</div>