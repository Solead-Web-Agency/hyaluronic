{*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}

{if isset($header_script_src) && $header_script_src}
  <script async defer type="text/javascript" src="{$header_script_src.src|escape:'javascript':'UTF-8'}"></script>
  <script async defer type="text/javascript">
    // Prestashop 1.6.X.X
    if(typeof(ajaxCart) != 'undefined') {
      // override ajaxCart.updateCart function
      var ajaxCartUpdateCartFunc = ajaxCart.updateCart;
      ajaxCart.updateCart = function(jsonData) {
        ajaxCartUpdateCartFunc(jsonData);
        $.post('/index.php?fc=module&module=cartsguru&controller=ajax&method=getTracker&token={$token|escape:"htmlall":"UTF-8"}')
          .then((resp) => {
            document.querySelector('div#tracker').innerHTML = resp.tracker;
          }
        );
      }
    }
    // Prestashop 1.7.X.X
    else if(typeof(prestashop) != 'undefined') {
      prestashop.on(
        'updateCart',
        function (event) {
          $.post('/index.php?fc=module&module=cartsguru&controller=ajax&method=getTracker&token={$token|escape:"htmlall":"UTF-8"}')
            .then((resp) => {
              document.querySelector('div#tracker').innerHTML = resp.tracker;
            }
          );
        }
      );
    }
  </script>
  <div id="tracker" style="display:none;" hidden="hidden">
    {include file="./tracker.tpl"}
  </div>
{/if}
