{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*
*}

<style>
.steavisgarantisIncludeStatus{
    height: 150px;
    padding: 0px 5px;
} 
</style>

<script>
    // Get cgvUrl from smarty context
    cgvUrl = "{$cgvUrl|escape:'htmlall':'UTF-8'}";

    {literal}
                        
            // Function to open CGV in new tab
            function openInNewTab(url) {
              var win = window.open(url, '_blank');
              win.focus();  
            }
            
            // Si on soumet le formulaire
            var createCertificate = document.querySelector("[name=\'createCertificate\']");
            createCertificate && createCertificate.addEventListener("click", function() {
                var span = document.createElement("span");
                    span.innerHTML = "Veuillez patienter...";
                    span.className = "wait";

                createCertificate.parentNode.insertBefore(span, createCertificate);
                // On le cache
                createCertificate.style.display = "none";
                
                // On ouvre les CGV dans un nouvel onglet
                openInNewTab(cgvUrl);
            });
            
            var cgv = document.querySelector("label[for=\'cgv_1\']");
            cgv && cgv.addEventListener("click", function(e) {
                if(e.target.tagName.toLowerCase() != "input") {
                    openInNewTab(cgvUrl);
                }
            });

            function toggleNewWidgets() {
                var newWidgets = document.querySelector('#steavisgarantis_newWidgets_on:checked');
                if(newWidgets) {
                    document.getElementById('steavisgarantis_maxReviewPerPage').closest('.form-group').style.display = 'none';
                    document.getElementById('wjs_on').closest('.form-group').style.display = 'none';
                    document.getElementById('showStructured_on').closest('.form-group').style.display = 'none';
                    document.getElementById('structuredFormat_microdata').closest('.form-group').style.display = 'none';
                    document.getElementById('design_1').closest('.form-group').style.display = 'none';
                    document.getElementsByClassName('stars-color-picker')[0].closest('.form-group').parentElement.parentElement.style.display = 'none';
                } else {
                    
                    document.getElementById('steavisgarantis_maxReviewPerPage').closest('.form-group').style.display = 'block';
                    document.getElementById('wjs_on').closest('.form-group').style.display = 'block';
                    document.getElementById('showStructured_on').closest('.form-group').style.display = 'block';
                    document.getElementById('structuredFormat_microdata').closest('.form-group').style.display = 'block';
                    document.getElementById('design_1').closest('.form-group').style.display = 'block';
                    document.getElementsByClassName('stars-color-picker')[0].closest('.form-group').parentElement.parentElement.style.display = 'block';
                }
            }
            
            function toggleOldOrdersMethod() {
                var oldMethod = document.querySelector('#steavisgarantis_useOldOrdersMethod_on:checked');
                if(oldMethod) {
                    document.getElementById('steavisgarantis_afterDays').closest('.form-group').style.display = 'block';
                } else {
                    document.getElementById('steavisgarantis_afterDays').closest('.form-group').style.display = 'none';
                }
            }
            
            toggleNewWidgets();
            toggleOldOrdersMethod();
            
            document.getElementById('steavisgarantis_newWidgets_on').addEventListener('change', () => {
                toggleNewWidgets();
            });

            document.getElementById('steavisgarantis_useOldOrdersMethod_on').addEventListener('change', () => {
                toggleOldOrdersMethod();
            });
            
    {/literal}
</script>
