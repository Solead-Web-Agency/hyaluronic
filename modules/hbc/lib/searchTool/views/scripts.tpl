{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA.PL VATEU: PL9730945634
* @copyright 2010-2023 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* Search Tool
* version 1.7.1
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<script>
    {literal}
    function SearchToolRemoveItem(css, what, where, id) {
        var current = $('input[name="' + where + '"]').val();
        var current_exploded = current.split(",");
        current_exploded.forEach(function (item, index, object) {
            if (item === id) {
                object.splice(index, 1);
            }
        });
        $('input[name="' + where + '"]').val(current_exploded);
        $('.' + css).remove();
    }

    function SearchToolFormatItem(what, where, data) {
        return '<div class="clearfix ' + what + where + data.id + '"><span class="btn btn-default" onclick="SearchToolRemoveItem(\'' + what + '' + where + '' + data.id + '\',\'' + what + '\',\'' + where + '\',\'' + data.id + '\');"><i class="icon-remove"></i></span> #' + data.id + ' - ' + data.name + '</div>';
    }

    $(document).ready(function () {
        var link = "{/literal}{$SearchToolLink}{literal}";
        var lang = {/literal}{Context::getContext()->language->id}{literal};
        $(".searchToolInput").each(function () {
            var searchInput = $(this);
            $(this).autocomplete(
                link, {
                    minChars: 1,
                    max: 15,
                    width: 500,
                    selectFirst: false,
                    scroll: false,
                    dataType: "json",
                    formatItem: function (data, i, max, value, term) {
                        return value;
                    },
                    parse: function (data) {
                        var mytab = new Array();
                        for (var i = 0; i < data.length; i++) {
                            if (typeof data[i].id_customer !== 'undefined') {
                                data[i].id = data[i].id_customer;
                                data[i].name = data[i].firstname+' '+data[i].lastname+' '+data[i].email;
                            }
                            if (typeof data[i].id_manufacturer !== 'undefined') {
                                data[i].id = data[i].id_manufacturer;
                            }
                            if (typeof data[i].id_product !== 'undefined') {
                                data[i].id = data[i].id_product;
                            }
                            if (typeof data[i].id_category !== 'undefined') {
                                data[i].id = data[i].id_category;
                            }
                            if (typeof data[i].id_supplier !== 'undefined') {
                                data[i].id = data[i].id_supplier;
                            }
                            if (typeof data[i].id_cms_category !== 'undefined') {
                                data[i].id = data[i].id_cms_category;
                            }
                            if (typeof data[i].id_group !== 'undefined') {
                                data[i].id = data[i].id_group;
                            }
                            if (typeof data[i].id_feature_value !== 'undefined') {
                                data[i].id = data[i].id_feature_value;
                                data[i].name = data[i].feature_name+': '+data[i].value;
                            }
                            if (typeof data[i].id_attribute !== 'undefined') {
                                data[i].id = data[i].id_attribute;
                                data[i].name = data[i].attribute_name+': '+data[i].name;
                            }
                            if (typeof data[i].id_cms !== 'undefined') {
                                data[i].id = data[i].id_cms;
                                data[i].name = data[i].meta_title;
                            }
                            mytab[mytab.length] = {
                                data: data[i],
                                value: '#' + data[i].id + ' - ' + data[i].name
                            };
                        }
                        return mytab;
                    },
                    extraParams: {
                        searchType: searchInput.data('type'),
                        limit: 20,
                        id_lang: lang,
                        showCounter: searchInput.data('showcounter'),
                    }
                }
            ).result(function (event, data, formatted) {
                if (+data.id > 0) {
                    if (searchInput.data('replacementtype') == 'replace') {
                        $('input[name="' + searchInput.data('resultinput') + '"]').val(data.id);
                        if ($('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').length) {
                            $('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').html(SearchToolFormatItem(searchInput.data('type'), searchInput.data('resultinput'), data));
                        }
                    } else {
                        var current = $('input[name="' + searchInput.data('resultinput') + '"]').val();
                        var current_exploded = current.split(",");
                        current_exploded.push(data.id);
                        var filtered_current_exploded = current_exploded.filter(function (e) {
                            return e
                        });
                        $('input[name="' + searchInput.data('resultinput') + '"]').val(filtered_current_exploded.join(","));
                        if ($('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').length) {
                            $('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').append(SearchToolFormatItem(searchInput.data('type'), searchInput.data('resultinput'), data));
                        }
                    }

                    if (searchInput.data('combinations') == "1" && searchInput.data('type') == "product") {
                        $.ajax({
                            type: "POST",
                            url: "{/literal}{$SearchToolLink}{literal}",
                            data: "getCombinations=1&searchByID="+data.id+"&combinationsClass="+searchInput.data('combinations-class'),
                            beforeSend: function(){
                                $('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').prepend("<div class='loaderSearchTool'><div class='loader-wheel'></div><div class='loader-text'></div></div>");
                            },
                            success: function(dat) {
                                if (dat.length == 0) {
                                    $('input[name="' + searchInput.data('combinations-class') + '"]').val(0);
                                }
                                $('.' + searchInput.data('combinations-class') + 'selectedCombinations').remove();
                                $('.' + searchInput.data('resultinput') + '_' + searchInput.data('type') + 'sBox').append(dat);
                                $('.loaderSearchTool').fadeOut("100", function(){$(this).remove()});
                            }
                        });
                    }
                }
            });
        });
    });
    {/literal}
</script>


<style>
    #fieldset_0, #fieldset_1_1 {
        clear: both; display:block; overflow:hidden;
    }
    .loaderSearchTool {
        width: 300px;
        background: #efefef;
        border-radius: 10px;
        text-align: center;
        padding: 10px;
        margin-bottom: 20px;
    }

    .loader-wheel {
        animation: spin 1s infinite linear;
        border: 2px solid rgba(30, 30, 30, 0.5);
        border-left: 4px solid #fff;
        border-radius: 50%;
        height: 50px;
        margin: 10px auto;
        width: 50px;
    }

    .loader-text {
        font-family: arial, sans-serif;
    }

    .loader-text:after {
        content: '{l s='Waiting for combinations' mod='hbc'}';
        animation: load 2s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

</style>