{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*}

{extends file=$smarty.const._PS_THEME_DIR_|cat:'templates/checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
  <div class="alert alert-danger">{$bpbc_error_message|escape:'htmlall':'utf-8'}</div>
{/block}
