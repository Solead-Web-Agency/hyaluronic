{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
<script type="text/javascript">
    {literal}
    function runCronjobManually(e){
      e.preventDefault();
      let button = $('#eglSubmitRunCron');
      let input = $('#ETS_GEO_CRONJOB_TOKEN');
      $.ajax({
        url : button.data('base'),
        type: 'json',
        method : 'POST',
        data: {
          ajax: 1,
          secure: input.val(),
        },
        beforeSend: function (){
          button.addClass('loading').prop('disabled', true);
        },
        success: function (res){
          if(res.success){
            $('.egl-warning-cronjob').detach();
            $.growl.notice({title: '',message: res.message});
          }else{
            $.growl.error({title: '',message: res.message});
          }
        },
        complete: function (){
          button.removeClass('loading').prop('disabled', false);
        },
      });
    }
    function updateNewToken(){
      let button = $('#eglSubmitUpdateToken');
      let link =  $('#eglSubmitRunCron');
      let input = $('#ETS_GEO_CRONJOB_TOKEN');
      let hintSecure = $('.egl-cronjob-secure-value');
      $.ajax({
        url: '',
        method: 'POST',
        data : {submitUpdateToken: 1},
        type: 'json',
        beforeSend: function (){
          button.addClass('loading').prop('disabled', true);
        },
        success: function (res){
          input.val(res.value);
          hintSecure.html(res.value);
          link.attr('href', `${link.data('base')}?secure=${res.value}`);
        },
        complete: function (){
          button.removeClass('loading').prop('disabled', false);
        }
      });
    }
    {/literal}
</script>
<div class="panel ets-geo-panel{if isset($class)} {$class|escape:'html':'UTF-8'}{/if}">
    <div class="panel-heading">
        <span class="heading_title">{l s='Cronjob settings' mod='ets_geolocation'}</span>
    </div>
    <div class="panel-content panel-content-cronjob">
      <div class="col-lg-12">
        <p class="alert alert-info">{l s='Automatically update GeoLiteCity IP database (using cronjob)' mod='ets_geolocation'}</p>
        <p class="egl-text-strong mb-10"><span style="color: red;">*</span> {l s='Some important notes before setting Cronjob:' mod='ets_geolocation'}</p>
        <ul>
          <li>{l s='Cronjob frequency should be at once per month, the recommended frequency is once per week' mod='ets_geolocation'}</li>
          <li>{l s='How to setup a cronjob is different depending on your server. If you\'re using a cPanel hosting, watch this video for more reference:' mod='ets_geolocation'}
            <a href="https://www.youtube.com/watch?v=bmBjg1nD5yA" target="_blank" rel="noreferrer noopener">https://www.youtube.com/watch?v=bmBjg1nD5yA</a> <br>
              {l s='You can also contact your hosting provider to ask them for support on setting up the cronjob' mod='ets_geolocation'}
          </li>
        </ul>
        <p class="egl-text-strong egl-block mb-15"><span style="color: red;">*</span>  {l s='Set up a cronjob as below on your server to automatically update your IP database file.' mod='ets_geolocation'}</p>
        <p class="mb-15 egl-block"><span class="egl-text-bg-light-gray">0 0 * * 0 {$phpBin|escape:'html':'UTF-8'}  {$cronjobFile|escape:'html':'UTF-8'} secure=<span class="egl-cronjob-secure-value">{$cronjobToken|escape:'html':'UTF-8'}</span></span></p>
        <p class="egl-text-strong mb-10"><span style="color: red;">*</span> {l s='Execute the cronjob manually by clicking on the button below' mod='ets_geolocation'}</p>
        <a href="{$linkCronjob|escape:'html':'UTF-8'}?secure={$cronjobToken|escape:'html':'UTF-8'}" id="eglSubmitRunCron" onclick="runCronjobManually(event)" target="_blank" data-base="{$linkCronjob|escape:'html':'UTF-8'}" class="btn btn-default btn-sm mb-10 js-egl-test-cronjob"><i></i>{l s='Execute cronjob manually' mod='ets_geolocation'}</a>
        <div class="form-group">
          <div id="conf_id_ETS_GEO_CRONJOB_TOKEN">
            <label class="control-label col-lg-3">
                {l s='Secure token to run cronjob' mod='ets_geolocation'}
            </label>
            <div class="col-lg-9" style="display: flex; flex-wrap: wrap;">
              <input name="ETS_GEO_CRONJOB_TOKEN" id="ETS_GEO_CRONJOB_TOKEN" value="{$cronjobToken|escape:'html':'UTF-8'}" type="text" size="12" style="width: 160px; margin-right: 10px;" />
              <button id="eglSubmitUpdateToken" class="btn btn-default" type="button" onclick="updateNewToken()">{l s='Update' mod='ets_geolocation'}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>