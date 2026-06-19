{foreach $btns as $btn}
    <a class="btn btn-default" href="{$btn.url}" target="_blank">
        <i class="{$btn.icon}"></i>
        {$btn.title}
    </a>
{/foreach}