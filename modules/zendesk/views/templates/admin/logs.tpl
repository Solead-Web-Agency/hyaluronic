{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>Logs files
    </div>
    <div class="panel-body">
        <ul id="yp-fileslist">
            {foreach from=$logs_files item=logs_files_amonth  key=a_month}    
                <li>
                    <span class="caretyp">{$a_month|escape:'htmlall':'UTF-8'}</span>
                    <ul class="nestedyp">
                        {foreach from=$logs_files_amonth item=log_file}
                            <li><a href="{$logs_url|escape:'htmlall':'UTF-8'}&show_log_files&display_file={$a_month|escape:'htmlall':'UTF-8'}/{$log_file|escape:'htmlall':'UTF-8'}">
                                <p>{$log_file|escape:'htmlall':'UTF-8'}</p>
                            </a></li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    </div>
    <div class="panel-body">
    <form action="{$ajax_remove_old_logs|escape:'htmlall':'UTF-8'}" id="formRemoveLogs" method="POST">
      <input type="hidden" name="submitDeleteOldLogs" />
      <div class="form-group mt-3 row yp_row_remove">
          <label class="control-label col-lg-2">
            {l s='Remove logs files older that' mod='zendesk'}
          </label>
          <div class="col-lg-1 input-group">
              <input type="text" class="form-control" name="remove_from_days" value="60" />
              <div class="input-group-addon" id="yp_addon_days">
                {l s='days' mod='zendesk'}
              </div>
          </div>
          <a href="#formRemoveLogs" type="submit" class="btn btn-default">
            <i class="icon-trash"></i></i>
          </a>
      </div>
      <div class="form-group yp_row_remove">
          <label class="control-label col-lg-2"></label>
          <div class="col-lg-12 input-group">
            <p class="help-block">
              {l s='Remove files before the number of days filled here. Fill 0 to remove all logs from files.' mod='zendesk'}
            </p>
          </div>
      </div>
    </div>
</div>

{if isset($logfile_content)}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>Content of file {$logfile_name|escape:'htmlall':'UTF-8'}
    </div>
    <div class="panel-body">
      <textarea style="resize: vertical;height:200px;">
        {$logfile_content|escape:'htmlall':'UTF-8'}
      </textarea>
    </div>
</div>
{/if}

<script type="text/javascript">
    var toggler = $(".caretyp").click(function() {
        this.parentElement.querySelector(".nestedyp").classList.toggle("activeyp");
        this.classList.toggle("caretyp-down");
    });
</script>

<style>
/* Remove margins and padding from the parent ul */
ul#yp-fileslist, #yp-fileslist ul {
    list-style-type: none!important;
}

#yp-fileslist {
  margin: 0;
  padding: 0;  
}

/* Style the caret/arrow */
.caretyp {
  cursor: pointer;
  user-select: none; /* Prevent text selection */
}

/* Create the caret/arrow with a unicode, and style it */
.caretyp::before {
  content: "\25B6";
  color: black;
  display: inline-block;
  margin-right: 6px;
}

/* Rotate the caret/arrow icon when clicked on (using JavaScript) */
.caretyp-down::before {
  transform: rotate(90deg);
}

/* Hide the nested list */
.nestedyp {
  display: none;
}

/* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
.activeyp {
  display: block;
}

.yp_row_remove {
  display: flex;
  align-items: center;
}

.yp_row_remove a {
  margin-left:5px;
}

.yp_row_remove [name="remove_from_days"} {
  padding:8px;
  width:65px;
}

#yp_addon_days {
  border-left:none;
  padding:6px;
}
</style>