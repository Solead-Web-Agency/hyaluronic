{if Configuration::get('PH_BLOG_DISPLAY_MORE')}
    {* Custom 2026-06-06: titre dans le texte du lien (span masqué visuellement) -> lien descriptif pour SEO Google *}
    <a href="{$post.url}" class="text-muted simpleblog__listing__post__wrapper__content__readmore">
        <span class="text-underline">{l s='Read more' d='Modules.Simpleblog.Shop'}</span>{if isset($post.title)}<span style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap"> : {$post.title|strip_tags|escape:'html':'UTF-8'}</span>{/if} <i class="fa fa-chevron-right text-smaller"></i>
    </a>
{/if}