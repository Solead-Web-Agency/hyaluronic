/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Software License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 *
 *  @author    Peter Sliacky (Prestasmart)
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Last tested on 22.03.2024 with mijoravenipak v1.1.4 by mijora.lt */

// var tc_tmjs = null;

// var tc_venipak_custom_modal = function() {
//     let mjvp_map_container =  document.getElementById('mjvp-pickup-select-modal');
//     let tmjs = null;

//     if (typeof(mjvp_map_container) != 'undefined' && mjvp_map_container != null) {
//         tc_tmjs = new TerminalMappingMjvp('https://venipak.uat.megodata.com/ws');
//         tmjs = tc_tmjs;
//         tmjs.setImagesPath(mjvp_imgs_url);
//         tmjs.setTranslation(mjvp_terminal_select_translates);

//         tmjs.dom.setContainerParent(document.getElementById('mjvp-pickup-select-modal'));
//         // tmjs.terminals_cache = null;
//         tmjs.init({
//             country_code: mjvp_country_code,
//             identifier: '',
//             isModal: true,
//             hideContainer: false,
//             hideSelectBtn: false,
//             postal_code: mjvp_postal_code,
//             city: mjvp_city
//         });

//         tmjs.sub('tmjs-ready', function(data) {
//             let selected_terminal = document.getElementById("mjvp-selected-terminal")?.value;
//             let selected_location = tmjs.map?.getLocationById(parseInt(selected_terminal));
//             if (typeof(selected_location) != 'undefined' && selected_location != null) {
//                 tmjs.publish('terminal-selected', selected_location);
//                 document.querySelector('.tmjs-selected-terminal').innerHTML = '<span class="mjvp-tmjs-terminal-name">' + selected_location.name + '</span> <span class="mjvp-tmjs-terminal-address">(' + selected_location.address + ')</span> <span class="mjvp-tmjs-terminal-comment">' + selected_location.city + '.</span>';
//             }
//         });
//         tmjs.sub('terminal-selected', function(data) {
//             document.getElementById("mjvp-selected-terminal").value = data.id;
//             mjvp_registerSelection('mjvp-selected-terminal');
//             tmjs.publish('close-map-modal');
//             document.querySelector('.tmjs-selected-terminal').innerHTML = '<span class="mjvp-tmjs-terminal-name">' + data.name + '</span> <span class="mjvp-tmjs-terminal-address">(' + data.address + ')</span> <span class="mjvp-tmjs-terminal-comment">' + data.city + '.</span>';
//         });
//     }

//     if (typeof window['venipak_custom_modal'] !== 'undefined') {
//           window['venipak_custom_modal'].tmjs = tmjs;
//     }
// }

checkoutShippingParser.mijoravenipak = {

  after_load_callback: function(deliveryOptionIds) {

    // check every 500ms at most for 3 seconds when length > 0 and if so, call venipak_custom_modal()
    const conditionallyInitializeMJVP = () => {
      let counter = 0;
      let checkInterval = setInterval(function() {
          if (mjvp_terminals.length > 0) {
              venipak_custom_modal();
              clearInterval(checkInterval);
          } else {
              console.log('[tc-mijoravenipak] mjvp_terminals empty, wait 500ms...');
              counter++;
              if (counter >= 6) {
                  clearInterval(checkInterval);
              }
          }
      }, 500);
    }

    if (typeof venipak_custom_modal === 'function') {
      setTimeout(conditionallyInitializeMJVP, 500);
    }
  },

}
