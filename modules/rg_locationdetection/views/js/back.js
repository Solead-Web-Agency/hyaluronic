/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

function inputPopup(action) {
    if ($('.redirection_popup').length) {
        $('.redirection_popup')[action]('fast');
    /* PS 1.5 code */
    } else if ($('input[name="use_popup"]').parent('.margin-form').length) {
        $('input[name="use_popup"]').parent('.margin-form').next().next('.margin-form')[action]('fast');
        $('input[name="use_popup"]').parent('.margin-form').next().next().next().next('.margin-form')[action]('fast');
    }
}

$(document).ready(function() {
    var content,
        name;

    if (typeof(languages) != 'undefined' && $('a.load-demo-content').length) {
        if ($('[name^="popup_"]').length) {
            name = 'popup';
            content = {
                en: '<div class="container rg-demo-popup-content"><div class="row"><div class="col-xs-12"><div class="header"><h3>Welcome,</h3></div><div class="content"><p>We have detected you visit us from (COUNTRY_NAME)...</p><p class="congrats">Good news!</p><p>Now we have an exclusive shop for you.</p></div><div class="footer"><a href="(REDIRECTION_URL)">Click here to visit our shop from (COUNTRY_NAME)<img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)"></a></div></div></div></div>',
                fr: '<div class="container rg-demo-popup-content"><div class="row"><div class="col-xs-12"><div class="header"><h3>Bienvenue,</h3></div><div class="content"><p>Nous avons détecté que vous nous rendez visite du (COUNTRY_NAME)...</p><p class="congrats">Bonnes nouvelles!</p><p>Maintenant, nous avons une boutique exclusive pour vous.</p></div><div class="footer"><a href="(REDIRECTION_URL)">Cliquez ici pour visiter notre boutique en provenance du (COUNTRY_NAME)<img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)"></a></div></div></div></div>',
                es: '<div class="container rg-demo-popup-content"><div class="row"><div class="col-xs-12"><div class="header"><h3>Bienvenido,</h3></div><div class="content"><p>Hemos detectado que nos visita desde (COUNTRY_NAME)...</p><p class="congrats">¡Buenas noticias!</p><p>Ahora tenemos una tienda exclusiva para usted.</p></div><div class="footer"><a href="(REDIRECTION_URL)">Haga clic aqui para visitar nuestra tienda de (COUNTRY_NAME)<img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)"></a></div></div></div></div>'
            };
        } else if ($('[name^="content_"]').length) {
            name = 'content';
            content = {
                en: '<div class="rg-demo-infobar-content"><h3>This is an important message</h3><p>Our systems have detected that you are visiting us from <strong>(COUNTRY_NAME)</strong>, welcome to our demo store.</p><p><img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)" /></p></div>',
                fr: '<div class="rg-demo-infobar-content"><h3>Ceci est un message important</h3><p>Nos systèmes ont détecté que vous nous rendez visite au <strong>(COUNTRY_NAME)</strong>, bienvenue sur notre boutique de démonstration.</p><p><img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)" /></p></div>',
                es: '<div class="rg-demo-infobar-content"><h3>Este es un mensaje importante</h3><p>Nuestros sistemas han detectado que nos visita desde <strong>(COUNTRY_NAME)</strong>, bienvenida a nuestra tienda de demostración.</p><p><img src="(COUNTRY_FLAG_URL)" alt="(COUNTRY_NAME)" /></p></div>'
            };
        }
    }

    $('a.load-demo-content').click(function(e) {
        e.preventDefault();

        if (confirm('The current content will be lost, do you want to continue?') == true) {
            $.each(languages, function(index, value) {
                if ($('textarea[name="'+name+'_'+value.id_lang+'"]').attr('id') != 'undefined') {
                    var idTiny = $('textarea[name="'+name+'_'+value.id_lang+'"]').attr('id');

                    if (typeof(content[value.iso_code]) != 'undefined') {
                        tinyMCE.get(idTiny).setContent(content[value.iso_code]);
                    } else if (value.iso_code == 'ag' || value.iso_code == 'cb' || value.iso_code == 'mx') {
                        tinyMCE.get(idTiny).setContent(content.es);
                    } else {
                        tinyMCE.get(idTiny).setContent(content.en);
                    }
                }
            });
        }
    });

    /* PS 1.5 code required */
    if ($('[name="use_popup"]').length) {
        if (!$('[name="use_popup"]').prop('checked')) {
            inputPopup('hide');
        }
    }

    $('[name="use_popup"]').on('change', function(event) {
        if ($('[name="use_popup"]').prop('checked')) {
            inputPopup('show');
        } else {
            inputPopup('hide');
        }
    });
});
