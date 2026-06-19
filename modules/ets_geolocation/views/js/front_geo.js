/**
  * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (typeof PS_ALLOW_ACCENTED_CHARS_URL === 'undefined') {
  PS_ALLOW_ACCENTED_CHARS_URL = false;
}
var popup_is_load = popup_is_load || false;
var ajax_url = ajax_url || false;
var page_controller = page_controller || 'index';
if (localStorage.getItem('etsGeoIsPopupShown') === '1') {
  popup_is_load = true;
}
var FRONT_GEO = {
  init: function() {
    if (page_controller != 'block') {
      this.geo_process();
      this.isMobile();
    }
  },
  request: function(newUrl) {
    const url = newUrl || ajax_url;
    return url + (url.indexOf('?') != -1 ? '&' : '?');
  },
  geo_process: function() {
    if (!ajax_url || popup_is_load) {
      return false;
    }
    /* Custom 2026-06-06: le pays d'un visiteur ne change pas entre deux pages — une verification par 24h suffit */
    var etsGeoTs = parseInt(localStorage.getItem('etsGeoProcessedAt') || '0', 10);
    if (etsGeoTs && (Date.now() - etsGeoTs) < 86400000) {
      return false;
    }
    $.ajax({
      url: FRONT_GEO.request() + 'geo_auto_processing=1',
      type: 'post',
      dataType: 'json',
      success: function(json) {
        localStorage.setItem('etsGeoProcessedAt', String(Date.now())); /* Custom 2026-06-06 */
        console.info(json);
        if (json) {
          if (json.link_block) {
            window.location.href = json.link_block;
          } else {
            if (json.html) {
              FRONT_GEO.show_popup(json.html, 'ets_geo_popup');
            }
            if (json.link_reload) {
              setTimeout(function() {
                const link = $('[hreflang="' + json.link_reload + '"]').attr('href');
                if (typeof link !== 'undefined') {
                  window.location.href = link;
                } else {
                  window.location.reload();
                }
              }, 1500);
            }
          }
        }
      },
    });
  },
  show_popup: function(content, class_content) {
    $('body').append(content);
    $('.' + class_content + '').addClass('active');
    localStorage.setItem('etsGeoIsPopupShown', '1');
  },
  process_choose: function(state) {
    if (!state.id) {
      return state.text;
    }
    const link_image = state.element.attributes.imgflag.value;
    const $state = $(
        '<span><img src="' + link_image + '" class="img-flag" /> ' + state.text + '</span>',
    );
    return $state;
  },
  isMobile: function() {
    const $window = $(window);
    const checkWidth = function() {
      const windowsize = $window.width();
      if (windowsize < 768) {
        if ($('#mobile_top_menu_wrapper .ets_click_show').length > 0) {
          return false;
        }
        const content_move = $('.ets_click_show').clone();
        $('#mobile_top_menu_wrapper').append(content_move);
      }
    };
    checkWidth();
    $(window).resize(checkWidth);
  },
  geo_loaded: function() {
    $('.ets_geo_popup_choose.active, .ets_geo_popup.active').removeClass('active');
    if (ajax_url) {
      $.ajax({
        url: FRONT_GEO.request() + 'geo_loaded=1',
        type: 'post',
        dataType: 'json',
      });
    }
  },
};

$(document).ready(function() {
  /* Custom 2026-06-06: appel geo differe apres le chargement complet — sort du chemin critique LCP sans rien perdre */
  if (document.readyState === 'complete') {
    setTimeout(function() { FRONT_GEO.init(); }, 1500);
  } else {
    window.addEventListener('load', function() { setTimeout(function() { FRONT_GEO.init(); }, 1500); });
  }
  $(document).on('click', '.ets_geo_popup .yes_ok', function(evt) {
    evt.preventDefault();
    const btn = $(this);
    if (btn.attr('href') && !btn.hasClass('active')) {
      btn.addClass('active');
      $.ajax({
        url: FRONT_GEO.request(btn.attr('href')) + 'geo_confirm=1',
        type: 'post',
        dataType: 'json',
        success: function(json) {
          btn.removeClass('active');
          $('.ets_geo_popup_choose').removeClass('active');
          if (json.link_reload) {
            const link = $('[hreflang="' + json.link_reload + '"]').attr('href');
            if (typeof link !== 'undefined') {
              window.location.href = link;
            } else {
              window.location.reload();
            }
          }
        },
        error: function() {
          btn.removeClass('active');
        },
      });
    }
  });
  $(document).on('click', '.ets_geo_popup .no_ok, .ets_geo_close_popup:not(.is_block)', function(evt) {
    evt.preventDefault();
    if (!$('.ets_geo_popup_choose').length) {
      FRONT_GEO.geo_loaded();
    } else {
      $('.ets_geo_popup_choose.active').removeClass('active');
    }
  });
  $(document).on('click', '.ets_geo_close_popup.is_block', function(evt) {
    $('.ets_geo_popup.active').removeClass('active');
    return false;
  });
  $(document).on('click', '.ets_geo_popup_content, .select2-container', function(e) {
    e.stopPropagation();
  });
  $(document).on('click', '.ets_geo_popup_choose', function(e) {
    $('.ets_geo_popup_choose').removeClass('active');
  });
  $(document).on('click', '.ets_click_show', function(evt) {
    evt.preventDefault();
    if ($('.ets_geo_popup_choose').eq(0).length > 0) {
      $('.ets_geo_popup_choose').eq(0).addClass('active');
    } else {
      const btn = $(this);
      if ($('body.geo_location_loading').length <= 0) {
        $('body').addClass('geo_location_loading');
        $.ajax({
          url: FRONT_GEO.request() + 'choose_country=1',
          type: 'post',
          dataType: 'json',
          success: function(json) {
            $('body').removeClass('geo_location_loading');
            if (json) {
              btn.removeClass('running');
              FRONT_GEO.show_popup(json.content_pop_choose, 'ets_geo_popup_choose');
              $('.ets_chosen-select').select2({
                templateSelection: FRONT_GEO.process_choose,
                templateResult: function(option) {
                  if (!option.id) {
                    return option.text;
                  }
                  const el = $(option.element);
                  return $(`<span><img class="img-flag" src="${el.attr('imgflag')}" alt="${el.attr('isocode')}" /> ${option.text}</span>`);
                },
              });
            }
          },
          error: function() {
            $('body').removeClass('geo_location_loading');
          },
        });
      }
    }
  });
  $(document).on('click', '.ets_geo_btn_submit_apply', function(evt) {
    evt.preventDefault();
    const btn = $(this);
    if (!btn.hasClass('active')) {
      btn.addClass('active');
      $('.ets_geo_popup_choose').removeClass('active');
      $.ajax({
        url: FRONT_GEO.request() + 'geo_selected_country=1',
        data: {
          'country_id': $('.ets_chosen-select').find('option:selected').val(),
        },
        type: 'post',
        dataType: 'json',
        success: function(json) {
          btn.removeClass('active');
          if (json) {
            if (json.link_block) {
              window.location.href = json.link_block;
            }
            if (json.link_reload) {
              const link = $('[hreflang="' + json.link_reload + '"]').attr('href');
              if (typeof link !== 'undefined') {
                window.location.href = link;
              } else {
                window.location.reload();
              }
            }
          }
        },
        // dai
        error: function() {
          btn.removeClass('active');
        },
      });
    }
  });
});

(function(jQuery) {
  if (typeof eglFo === typeof undefined) {
    return;
  }
  function _hideProduct() {
    jQuery('.js-product-miniature').each((i, e) => {
      e = jQuery(e);
      const pId = e.data('idProduct').toString();
      if (eglFo.hiddenProductIds.indexOf(pId) !== -1) {
        e.find('.product-title>a').html('<div class="alert alert-danger">'+eglFo.transMsg.productNotAvail+'</div>').css('text-decoration', 'line-through!important');
        e.find('.highlighted-informations').detach();
      }
    });
  }
  function _removeAddToCart() {
    jQuery('.js-product-add-to-cart').html('<div class="alert alert-danger">'+eglFo.transMsg.productNotAvail+'</div>');
  }
  /* Register events */
  if (eglFo && eglFo.isHasHiddenProduct) {
    if (eglFo.currentController !== 'product') {
      jQuery(document).ready(_hideProduct);
      prestashop.on('updateFacets', () => {
        window.setTimeout(_hideProduct, 500);
      });
      prestashop.on('updateProductList', () => {
        window.setTimeout(_hideProduct, 500);
      });
    } else {
      if (eglFo.hiddenProductIds.indexOf(eglFo.currentProductId.toString()) !== -1) {
        jQuery(document).ready(_removeAddToCart);
      }
    }
  }
})(jQuery);
