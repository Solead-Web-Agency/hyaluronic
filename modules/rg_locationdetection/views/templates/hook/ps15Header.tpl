{**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 *}

<script type="text/javascript">
    var page_name = "{$page_name|escape:'htmlall':'UTF-8'}",
        rgld_public_key = "{$rgld_public_key|escape:'htmlall':'UTF-8'}",
        rgld_path = "{$rgld_path|escape:'htmlall':'UTF-8'}";
    {if isset($rgld_popup)}
        var rgld_popup = "{$rgld_popup nofilter}";
    {/if}
    {if isset($rgld_infobar_position) && $rgld_infobar_position}
        var rgld_infobar_position = "{$rgld_infobar_position|escape:'htmlall':'UTF-8'}";
    {/if}
</script>
