{*
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*}

<div class="panel panel-default">
    <div class="panel-heading">{l s='Usage data' mod='chatgptpro'}</div>
    <div class="panel-body">

      <div class="container">
        <p>{l s='Current usage USD:' mod='chatgptpro'} <span class="label label-success">{$usage_data.current_usage_usd}</span></p>     
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>aggregation_timestamp</th>
              <th>n_requests</th>
              <th>operation</th>
              <th>snapshot_id</th>
              <th>n_context</th>
              <th>n_context_tokens_total</th>
              <th>n_context_tokens_total</th>
              <th>n_generated_tokens_total</th>
            </tr>
          </thead>
          <tbody>
            {if isset($usage_data.data) && $usage_data.data|@count>0}
              {foreach from=$usage_data.data item=item}
              <tr>
                  <td>{$item.aggregation_timestamp}</td>
                  <td>{$item.n_requests}</td>
                  <td>{$item.operation}</td>
                  <td>{$item.snapshot_id}</td>
                  <td>{$item.n_context}</td>
                  <td>{$item.n_context_tokens_total}</td>
                  <td>{$item.n_context_tokens_total}</td>
                  <td>{$item.n_generated_tokens_total}</td>
              </tr>
              {/foreach}
            {else}
              <tr><td colspan="8">{l s='No data available. Pick another date.' mod='chatgptpro'}</td></tr>
            {/if}
          </tbody>
        </table>
      </div>

    </div>
</div>

      