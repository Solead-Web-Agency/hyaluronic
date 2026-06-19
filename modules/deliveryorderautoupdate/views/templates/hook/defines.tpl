{* NOTICE OF LICENSE
 * @copyright  2007-2023 Helloshop
 * @author     Tofel Dahhaoui
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}

<script type="text/javascript">
    var delivery_token = '{$delivery_token|escape:'html':'UTF-8'}';
    var secure_key = '{$secure_key|escape:'html':'UTF-8'}';
    var url_ajax = '{$link->getAdminLink('AdmindeliveryorderautoupdateAjax', true)|escape:'html':'UTF-8'|htmlspecialchars_decode}';
</script>
<style type="text/css">
    .delivery-icon {
        position: relative;
        width: 30px;
        height: 30px;
        color: #fff;
        top: -50%;
    }
    .reverse {
        transform: scale(-1, 1);
    }
    .dl-detail {
        height: 30px;
        width: 0;
        position: absolute;
        left: 24px;
        top: 0;
		line-height:13px;
        -webkit-transition: width 0.3s;
        transition: width 0.3s;
        overflow: hidden;
        border-top-right-radius: 8px;
        text-indent: 8px;
        border-bottom-right-radius: 8px;
        z-index: 10;
		padding-top: 2px;
    }
    .delivery-icon:hover .dl-detail {
        width:180px;
    }
    .dl-icon span {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 30px;
        height: 30px;
        padding: 5px;
        border-radius: 8px;
    }
    .dl-icon img {
        max-height: 20px;
        max-width: 20px;
    }
    @media (min-width: 992px) {
        .track {
            position: absolute;
            top: 50%;
            width: 30px;
            height: 30px;
        }
        .track:nth-of-type(1) {
            right: 0;
        }
        .track:nth-of-type(2) {
            right: 30px;
        }
        .track:nth-of-type(3) {
            right: 60px;
        }
        .track:nth-of-type(4) {
            right: 90px;
        }
        .track:nth-of-type(1n+5) {
            display: none;
        }
    }
    @media (max-width: 991px) {
        .delivery-icon:hover .dl-detail {
            width: inherit;
        }
        .track {
            display: block;
            float: none;
            height: initial;
            margin-top: 0;
        }
        .dl-detail {
            width: auto;
            height: 30px;
            position: initial;
        }
        .delivery-icon {
            width: unset;
            height: unset;
            position: initial;
        }
        .dl-icon {
            float:left;
        }
        .dl-icon span{
            border-radius: 8px 0 0 8px;
        }
        .delivery-icon:hover .dl-detail {
        }
    }
</style>