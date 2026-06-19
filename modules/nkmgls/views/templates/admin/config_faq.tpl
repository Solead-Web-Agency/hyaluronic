{*
*  Module made by Nukium
*
*  @author    Nukium
*  @copyright 2023 Nukium SAS
*  @license   All rights reserved
*
* ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
* ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
* ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
* ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
* ██   ████  ██████  ██   ██ ██  ██████  ██      ██
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*}
<div class="support">
	<h2 class="text-primary text-uppercase">{l s='Debug procedure' mod='nkmgls'}</h2>
    <p>{l s='In case of issues with the GLS module, here is a list of actions to follow that will help you to fix your problem quickly if it is not due to the module.' mod='nkmgls'}</p>
    <h2 class="text-primary text-uppercase">{l s='Frequently Asked Questions' mod='nkmgls'}</h2>
    <ul>
    	<li><a href="#issue1">{l s='I disabled a GLS service but it is still displayed to the customer to deliver his order.' mod='nkmgls'}</a></li>
        <li><a href="#issue2">{l s='The GLS Relais service isn\'t displayed even though it\'s activated.' mod='nkmgls'}</a></li>
        <li><a href="#issue3">{l s='The GLS service "Chez vous +" is unavailable for customers wishing to be delivered outside of France.' mod='nkmgls'}</a></li>
        <li><a href="#issue4">{l s='I wish to apply a different shipping cost for customers being delivered in Corsica.' mod='nkmgls'}</a></li>
        <li><a href="#issue5">{l s='The GLS Relais map isn\'t displayed.' mod='nkmgls'}</a></li>
        <li><a href="#issue6">{l s='How to update the module?' mod='nkmgls'}</a></li>
	</ul>
    <h3 id="issue1">{l s='I disabled a GLS service but it is still displayed to the customer to deliver his order.' mod='nkmgls'}</h3>
    <p>{l s='Check that the carrier associated with this GLS service is disabled from the list of' mod='nkmgls'} <a href="{$carrier_link|escape:'quotes':'UTF-8'}">{l s='Carriers' mod='nkmgls'}</a>.</p>
    <h3 id="issue2">{l s='The GLS Relais service isn\'t displayed even though it\'s activated.' mod='nkmgls'}</h3>
    <p>
		{l s='Check the webservice login/password entered in the configuration of the GLS module from GLS Account tab.' mod='nkmgls'}<br>
        {l s='If the problem persists, in the GLS module configuration, Carriers tab, create a new carrier for the GLS Relais service by clicking on the "Create this carrier" button.' mod='nkmgls'}<br>
        {l s='Remember to set the shipping costs for this new carrier.' mod='nkmgls'}
	</p>
    <h3 id="issue3">{l s='The GLS service "Chez vous +" is unavailable for customers wishing to be delivered outside of France.' mod='nkmgls'}</h3>
	<p>
		{l s='The carrier "GLS Chez vous +" must have a shipping cost configured for the concerned zone otherwise it won\'t be displayed to the customer to deliver his order.' mod='nkmgls'}<br>
    	{l s='Verify that' mod='nkmgls'} <a href="{$carrier_link|escape:'quotes':'UTF-8'}">{l s='Zones containing delivery countries' mod='nkmgls'}</a> {l s='are enabled and a shipping cost is set.' mod='nkmgls'}<br>
    	{l s='The countries outside of France available for the GLS "Chez vous +" service are: Austria, Belgium, Germany, Denmark, Spain, Luxembourg, Netherlands and Poland.' mod='nkmgls'}<br>
    	{l s='If needed, you can create a restricted delivery zone under' mod='nkmgls'} <a href="{$zones_link|escape:'quotes':'UTF-8'}">{l s='Zones' mod='nkmgls'}</a>.<br>
    	{l s='Please notice that the modification of zones has an impact on all your carriers because they are common to them.' mod='nkmgls'}
	</p>
    <h3 id="issue4">{l s='I wish to apply a different shipping cost for customers being delivered in Corsica.' mod='nkmgls'}</h3>
    <p>
		{l s='PrestaShop doesn\'t distinguish between France and Corsica by default.' mod='nkmgls'}<br>
        {l s='To be able to manage this particular case, it\'s necessary to add a Corsica zone and Corsica country in it.' mod='nkmgls'}<br>
        {l s='You can take the data from France to create the Corsica country, be careful however to enter a unique ISO code and different (eg COR).' mod='nkmgls'}<br>
        {l s='If you use taxes, you will also need to create one or more tax rules to include Corsica.' mod='nkmgls'}
	</p>
    <h3 id="issue5">{l s='The GLS Relais map isn\'t displayed.' mod='nkmgls'}</h3>
    <p>
		{l s='A plugin might conflict with GLS because it also uses the Google Maps API.' mod='nkmgls'}<br>
        {l s='Try not to include the Google Maps API script by disabling the option in the GLS Account tab.' mod='nkmgls'}
    </p>
    <h3 id="issue6">{l s='How to update the module?' mod='nkmgls'}</h3>
    <p>
		{l s='The update of the module proceeds as follows:' mod='nkmgls'}<br>
    </p>
    <ol>
        <li>{l s='Go to the "Modules > Modules & Services" tab of your back-office and click on "Upload a module"' mod='nkmgls'}</li>
        <li>{l s='Select the ZIP archive corresponding to the new version of the module on your computer, the update will start automatically' mod='nkmgls'}</li>
        <li>{l s='The module is now up to date, your configuration has been preserved' mod='nkmgls'}</li>
    </ol>
</div>