{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors
* #Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
*
* @author Samdha <contact@samdha.net>
* @copyright  Samdha
* @license    commercial license see license.txt
*}
<div id="tabParameters" class="col-lg-10 col-md-9">
    <div class="panel">
        <h3 class="tab"> <i class="icon-wrench"></i> {l s='Parameters' mod='cron'}</h3>

        <div class="{if $version_16 && $bootstrap}alert alert-info{else}hint solid_hint{/if}">
            {l s='Please choose the method used to determine when executing jobs.' mod='cron'}
            <ol style="margin-top: 1em">
            	<li style="list-style:decimal">
                    {l s='"Shop traffic" method doesn\'t need configuration but is not sure. It depends of your website frequentation so when it isn\'t visited, jobs are not executed.' mod='cron'}
                </li>
                <li style="list-style:decimal">
                    {l s='"Server crontab" is the best method but only if your server uses Linux and you have access to crontab. In that case add the line below to your crontab file.' mod='cron'}
                    <br/><pre>* * * * * {$php_dir|escape:'htmlall':'UTF-8'} {$cron_command|escape:'htmlall':'UTF-8'}</pre>
                    <p>"{$php_dir|escape:'htmlall':'UTF-8'}" {l s='is the PHP path on your server. It has been automatically detected and may be wrong. If it doesn\'t work, contact your host.' mod='cron'}</p>
                </li>
                <li style="list-style:decimal">
                    {l s='"Webcron service" is a good alternative to crontab but is often not free. Register to a webcron service and configure it to visit the URL below every minutes or the nearest.' mod='cron'}
                    <br/><pre>{$cron_url|escape:'htmlall':'UTF-8'}</pre><br/>
             		<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAErklEQVQ4T6WUeUyTZxzHv+/bgx4cpbRccogtggULDFzGlITDRUycZMumuENlcyM7Y+YfMrNljUt2uLCZLDrj/nAJcyNhKnMhuuzQxTjnOMYAC6VQFMaxcbWF0tLjffd7CzgQNEt80qdp0t/zeb7P9/d9HgZ3GadMkG3TIVLGI0osgprhIQMDMc/Axfng9Iox0WrHeNErmCEEdyeGWYHLXjoGxcNRWA0e6SKWLbnRrSyRKDVqsBKW84xOeaanbhkz/DW0UesYh76nTsJ++TL8i1nLwAQN3aiFnhYVWvviD0qStsembq4AFHEAIwY8o4CjE10NR2b02pbPCXZu3IPO2N0YW6x8Cdi0F7Kq7UhiAyiy/qX/wLDzhAqRawB+lqZvThAjoi8p4JuFpfY1v0596SgfwJmWf2B+6HU4F1QvAQ99BY1ajDyLNfTo+h2fpjHqVVTnAlie5vwS+gmOlnES2o+BpW7/tC7B+kaAw49bj2NgwZLb4MJCiC++ihSRCE/Y+o3vppa9LGIkbhJHfRHTFOBCtQD10/TRTgEFui/WQ6f9oY7jA9WnW/FnhQme4MEWpJuehPTQHqxva2SO5GwqKGZS8oEQAssCYBbgQjGBeS9BZ8kSTwgwbof5p7qRtHWO3c23cH3Bjtvg70xQbHkARnO79DPjBkM2EvWAchaMkpotD8wpp8wFlRKUdxHYJQNGx2C52jaVaph81jKMKxn7YJ8/15xmIbdP5yOro0V6LDt3VS5iwwAVD0ZBjZN4aQrm0hBC5SV/faTWQU38exKWJoczNXPymfYhXMmpCDZQMGxuBK14AYbW3/Be3kb9VkRNA5FuMH6yTEFgJRUJbCEc/aQ4XkE2SIBJLczXBvrTslzPNXehcZkVVM66G5AoCsE2mzn247WPpEmhMFN0KZ4BIibOK6AYC3eNlxLYlQnrpTHoUm6e4MCdPH0VncuaJyz74xxU6RHItTSLPzKWbsmBJhRMyE1SNgTop4QTAjY5nUQL3p8CTCnQ2XBhItXo3O8M4GdNCYbnihalImgHRa7qbSSzLDb33NBUpz++SwmlFIyb7AgjD+jksFPT5CGUDBms357l1iR3f0LvR933VrQ/Whl8N4JjxSu9KR1GhsPzvQNrKtY+tpfyRQa7KBksiZHTtaYPP/ILOs43dKVlc2/aXbgWUwbBpNuP0TKwcFHqqqANZ1HS0yapXleaEM2ocinTWwF3ByBygHe3oPuCxafTuw9zPM6+fwo9pjpQh/8bK71uc9HbgCzWL3mpb1C5R1+cSjmmOf4NJSQX1uvddNvcHT7RzIHeaTRl7MDEYuiKVswXsKNnEKNiYeidzquXSkJD4xIiIZN64fdI4AxEQzr55WGxeOaLD2sweKfae4GDqh80gAtRHxwYcCdHx8UlISpGh7HBVnDeqUDSyIvGJjNsRaagBf/roV9yqqbz79hmVEUpUapQhKvUcDoc8Nst7qyCcgry3ceKHi8uP/TWgV937azIl8tliIiIgNfrh83y+1hBcZn2vsDx8fGa8vKdjRkZmas1Gi1stl6fQhG2o7JyX/19gYXFGo1mbWlp6ddarTa8trb28PDwcM29oMJ//wLNGcAmi6ehdgAAAABJRU5ErkJggg=="> {l s='Schedule it with' mod='cron'} <a style="text-decoration:underline" href="https://www.easycron.com/cron-job-scheduler?ref=10651" target="_blank">EasyCron</a> {l s='(it\'s free)' mod='cron'}<br/><br/>
                </li>
            </ol>
        </div>

    	<form action="{$module_url|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
            <div class="form-group clear">
                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_method">{l s='Method' mod='cron'}</label>
                <div class="{if $version_16 && $bootstrap}input-group{else}margin-form{/if}">
                    <select name="setting[_method]" id="{$module_short_name|escape:'htmlall':'UTF-8'}_method">
                        {html_options options=$methods selected=$module_config._method}
                    </select>
                </div>
            </div>

            <div class="form-group clear">
                <label>{l s='Test job' mod='cron'}</label>
                <div class="margin-form">
                    <span class="radio">
                        <input type="radio" name="setting[_test]" id="{$module_short_name|escape:'htmlall':'UTF-8'}_test_on" value="1" {if $module_config._test}checked="checked"{/if} />
                        <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_test_on">{l s='Yes' mod='cron'}</label>
                        <input type="radio" name="setting[_test]" id="{$module_short_name|escape:'htmlall':'UTF-8'}_test_off" value="0" {if !$module_config._test}checked="checked"{/if} />
                        <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_test_off">{l s='No' mod='cron'}</label>
                    </span>
                    <p {if $version_16}class="help-block"{/if}>
                        {l s='To check whether the choosen method works, you can enable the test job. It should be executed every minutes and show it at the top of this form. When everything is ok you can disable it.' mod='cron'}
                    </p>
                </div>
            </div>

            <div class="clear panel-footer">
            	<input type="hidden" name="active_tab" value="tabParameters" />
                <input type="hidden" name="saveSettings" value="1">
				<p>
					<input type="submit" class="samdha_button" value="{l s='Save' mod='cron'}" />
				</p>
	        </div>
    	</form>
    </div>
</div>

<div id="tabJobs" class="col-lg-10 col-md-9">
    <div class="panel">
        <h3 class="tab"> <i class="icon-list"></i> {l s='Jobs' mod='cron'}</h3>

        {if count($crons) || count($crons_url)}
            <fieldset>
                <legend>{l s='Crons jobs' mod='cron'}</legend>
                <table class="table">
                    {if count($crons)}
                        <tr>
                            <th>{l s='Module' mod='cron'}</th>
                            <th>{l s='Method' mod='cron'}</th>
                            <th>{l s='Schedule' mod='cron'}</th>
                            <th>{l s='Last execution' mod='cron'}</th>
                            <th>{l s='Action' mod='cron'}</th>
                        </tr>

                        {foreach from=$crons item=cron}
                            <tr>
                                <td>{$cron.name|escape:'htmlall':'UTF-8'}</td>
                                <td>{$cron.method|escape:'htmlall':'UTF-8'}</td>
                                <td>{$cron.mhdmd|escape:'htmlall':'UTF-8'}</td>
                                <td>
                                    {if $cron.last_execution}
                                        {capture name=temp}{$cron.last_execution|date_format:"%Y-%m-%d %H:%M:%S"|escape:'htmlall':'UTF-8'}{/capture}
                                        {dateFormat date=$smarty.capture.temp full=1}
                                    {else}
                                        {l s='Never' mod='cron'}
                                    {/if}
                                </td>
                                <td>
                                    <a href="{$module_url|escape:'htmlall':'UTF-8'}&amp;delete={$cron.id_cron|intval}">
                                        <img src="../img/admin/delete.gif" alt="{l s='Delete' mod='cron'}" title="{l s='Delete' mod='cron'}" />
                                    </a>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    {if count($crons_url)}
                        <tr>
                            <th colspan="2">{l s='Url' mod='cron'}</th>
                            <th>{l s='Schedule' mod='cron'}</th>
                            <th>{l s='Last execution' mod='cron'}</th>
                            <th>{l s='Action' mod='cron'}</th>
                        </tr>
                        {foreach from=$crons_url item=cron}
                            <tr>
                                <td colspan="2">{$cron.url|escape:'htmlall':'UTF-8'}</td>
                                <td>{$cron.mhdmd|escape:'htmlall':'UTF-8'}</td>
                                <td>
                                    {if $cron.last_execution}
                                        {capture name=temp}{$cron.last_execution|date_format:"%Y-%m-%d %H:%M:%S"|escape:'htmlall':'UTF-8'}{/capture}
                                        {dateFormat date=$smarty.capture.temp full=1}
                                    {else}
                                        {l s='Never' mod='cron'}
                                    {/if}
                                </td>
                                <td>
                                    <a href="{$module_url|escape:'htmlall':'UTF-8'}&amp;delete_url={$cron.id_cron_url|intval}">
                                        <img src="../img/admin/delete.gif" alt="{l s='Delete' mod='cron'}" title="{l s='Delete' mod='cron'}" />
                                    </a>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                </table>
            </fieldset>
        {/if}

        <form action="{$module_url|escape:'htmlall':'UTF-8'}" method="post" id="cron_add">
            <fieldset class="space width3">
                <legend>{l s='Add a job' mod='cron'}</legend>
                <div class="{if $version_16 && $bootstrap}alert alert-info{else}hint solid_hint{/if}">
                    {l s='The URL below will be visited at the time or date specified.' mod='cron'}
                </div>

                <div class="form-group clear">
                    <label for="cron_method">{l s='URL' mod='cron'}</label>
                    <div class="margin-form">
                        <input required="required" type="url" name="cron_url" id="cron_url" value="{$smarty.post.cron_url|default:''|escape:'htmlall':'UTF-8'}" />
                    </div>
                </div>

                <div class="form-group clear">
                    <label for="cron_method">{l s='Schedule' mod='cron'}</label>
                    <div class="margin-form">
                        <input type="hidden" name="cron_mhdmd" id="cron_mhdmd" value="{$smarty.post.cron_mhdmd|default:''|escape:'htmlall':'UTF-8'}" />
                        <select name="cron_mhdmd2" id="cron_mhdmd2">
                            <option value="0 * * * *">{l s='Every hours' mod='cron'}</option>
                            <option value="0 0 * * *" selected="selected">{l s='Daily (midnight)' mod='cron'}</option>
                            <option value="0 0 * * 0">{l s='Weekly (Sunday)' mod='cron'}</option>
                            <option value="0 0 1 * *">{l s='Monthly (first)' mod='cron'}</option>
                            <option value="0 0 1 1 *" >{l s='Yearly (January 1)' mod='cron'}</option>
                            <option value="other" >{l s='Other' mod='cron'}</option>
                        </select>
                    </div>
                </div>

                <div id="cron_table" style="display: none">
                    <div class="form-group clear">
                        <label>{l s='Minutes' mod='cron'}</label>
                        <div class="margin-form">
                            <span class="radio">
                                <input type="radio" name="all_mins" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_mins_on" value="1" checked="checked" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_mins_on">{l s='All' mod='cron'}</label>
                                <input type="radio" name="all_mins" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_mins_off" value="0" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_mins_off">{l s='Selected' mod='cron'}</label>
                            </span>
                            <div class="{$module_short_name|escape:'htmlall':'UTF-8'}_all_mins_off">
                                <select multiple="multiple" size="12" name="mins" class="nochosen">
                                    {html_options options=range(0, 59)}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group clear">
                        <label>{l s='Hours' mod='cron'}</label>
                        <div class="margin-form">
                            <span class="radio">
                                <input type="radio" name="all_hours" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_hours_on" value="1" checked="checked" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_hours_on">{l s='All' mod='cron'}</label>
                                <input type="radio" name="all_hours" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_hours_off" value="0" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_hours_off">{l s='Selected' mod='cron'}</label>
                            </span>
                            <div class="{$module_short_name|escape:'htmlall':'UTF-8'}_all_hours_off">
                                <select multiple="multiple" size="12" name="hours" class="nochosen">
                                    {html_options options=range(0, 23)}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group clear">
                        <label>{l s='Days of month' mod='cron'}</label>
                        <div class="margin-form">
                            <span class="radio">
                                <input type="radio" name="all_days" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_days_on" value="1" checked="checked" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_days_on">{l s='All' mod='cron'}</label>
                                <input type="radio" name="all_days" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_days_off" value="0" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_days_off">{l s='Selected' mod='cron'}</label>
                            </span>
                            <div class="{$module_short_name|escape:'htmlall':'UTF-8'}_all_days_off">
                                <select multiple="multiple" size="12" name="days" class="nochosen">
                                    {html_options options=range(0, 31)}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group clear">
                        <label>{l s='Months' mod='cron'}</label>
                        <div class="margin-form">
                            <span class="radio">
                                <input type="radio" name="all_months" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_months_on" value="1" checked="checked" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_months_on">{l s='All' mod='cron'}</label>
                                <input type="radio" name="all_months" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_months_off" value="0" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_months_off">{l s='Selected' mod='cron'}</label>
                            </span>
                            <div class="{$module_short_name|escape:'htmlall':'UTF-8'}_all_months_off">
                                <select multiple="multiple" size="12" name="months" class="nochosen">
                                    <option value="1" >{l s='January' mod='cron'}</option>
                                    <option value="2" >{l s='February' mod='cron'}</option>
                                    <option value="3" >{l s='March' mod='cron'}</option>
                                    <option value="4" >{l s='April' mod='cron'}</option>
                                    <option value="5" >{l s='May' mod='cron'}</option>
                                    <option value="6" >{l s='June' mod='cron'}</option>
                                    <option value="7" >{l s='July' mod='cron'}</option>
                                    <option value="8" >{l s='August' mod='cron'}</option>
                                    <option value="9" >{l s='September' mod='cron'}</option>
                                    <option value="10" >{l s='October' mod='cron'}</option>
                                    <option value="11" >{l s='November' mod='cron'}</option>
                                    <option value="12" >{l s='December' mod='cron'}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group clear">
                        <label>{l s='Days of week' mod='cron'}</label>
                        <div class="margin-form">
                            <span class="radio">
                                <input type="radio" name="all_weekdays" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_weekdays_on" value="1" checked="checked" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_weekdays_on">{l s='All' mod='cron'}</label>
                                <input type="radio" name="all_weekdays" id="{$module_short_name|escape:'htmlall':'UTF-8'}_all_weekdays_off" value="0" />
                                <label for="{$module_short_name|escape:'htmlall':'UTF-8'}_all_weekdays_off">{l s='Selected' mod='cron'}</label>
                            </span>
                            <div class="{$module_short_name|escape:'htmlall':'UTF-8'}_all_weekdays_off">
                                <select multiple="multiple" size="12" name="weekdays" class="nochosen">
                                    <option value="0" >{l s='Sunday' mod='cron'}</option>
                                    <option value="1" >{l s='Monday' mod='cron'}</option>
                                    <option value="2" >{l s='Tuesday' mod='cron'}</option>
                                    <option value="3" >{l s='Wednesday' mod='cron'}</option>
                                    <option value="4" >{l s='Thursday' mod='cron'}</option>
                                    <option value="5" >{l s='Friday' mod='cron'}</option>
                                    <option value="6" >{l s='Saturday' mod='cron'}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="clear panel-footer">
            	<input type="hidden" name="active_tab" value="tabJobs" />
                <input type="hidden" name="submitAddCron" value="1">
				<p>
					<input type="submit" class="samdha_button" value="{l s='Save' mod='cron'}" />
				</p>
	        </div>
        </form>
    </div>
</div>
