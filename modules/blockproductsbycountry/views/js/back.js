/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

$(function() {
    $(document).on('click', '.bpbc-block_products_link', function(e) {
        e.preventDefault();
        swal(products_link_title, products_link_message.replace(new RegExp('&quot;', 'g'), '"'));
    });
    $(document).on('click', '.bpbc-info_link', function(e) {
        e.preventDefault();
        swal({
            title: $('.bpbc-info-html').data('title'),
            text: $('.bpbc-info-html').html(),
            html: true,
        });
    });
    $(document).ready(function() {
        var bpbc_interval = setInterval(function() {
            if (typeof $.fn.multiSelect === 'undefined' || !$(document).find('.bpbc_multiselect').length) {
                return;
            }
            clearInterval(bpbc_interval);
            $('.bpbc_multiselect').multiSelect({
                selectableHeader: "<label for='multiselectAll' />"+multiselect_all_label+"</label><input id='multiselectAll' type='text' class='search-input form-control' autocomplete='off' placeholder='"+search_label+"'><br />",
                selectionHeader: "<label for='multiselectSelected' />"+multiselect_selected_label+"</label><input id='multiselectSelected' type='text' class='search-input form-control' autocomplete='off' placeholder='"+search_label+"'><br />",
                afterInit: function(ms){
                  var that = this,
                      $selectableSearch = that.$selectableUl.prev().prev(),
                      $selectionSearch = that.$selectionUl.prev().prev(),
                      selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                      selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';
              
                  that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                  .on('keydown', function(e){
                    if (e.which === 40){
                      that.$selectableUl.focus();
                      return false;
                    }
                  });
              
                  that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                  .on('keydown', function(e){
                    if (e.which == 40){
                      that.$selectionUl.focus();
                      return false;
                    }
                  });
                },
                afterSelect: function(){
                  this.qs1.cache();
                  this.qs2.cache();
                },
                afterDeselect: function(){
                  this.qs1.cache();
                  this.qs2.cache();
                }
            });
        }, 300);
    });
});

document.addEventListener("DOMContentLoaded", function() { 
    var multiselect_js = document.createElement('script');
    multiselect_js.setAttribute('src', multiselect_js_path);
    $('head').append(multiselect_js);
    
    var quicksearch_js = document.createElement('script');
    quicksearch_js.setAttribute('src', quicksearch_js_path);
    $('head').append(quicksearch_js);
});