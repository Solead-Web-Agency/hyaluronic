{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{foreach $stats as $iso_code => $percentage}
	{if isset($stats_not_supported[$iso_code])}{$lvl = 'not-supported'}
	{else if $percentage > 99}{$lvl = 100}{else if $percentage > 59}{$lvl = 60}
	{else if $percentage > 29}{$lvl = 30}{else if $percentage > 0}{$lvl = 1}
	{else}{$lvl = 0}{/if}
	<span class="stats-{$lvl|escape:'html':'UTF-8'}">
		{$iso_code|upper|escape:'html':'UTF-8'}: {$percentage|intval}%
	</span>
{/foreach}
{* since 3.0.0 *}
