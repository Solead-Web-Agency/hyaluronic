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
{assign var=color_header value="#F0F0F0"}
{assign var=color_border value="#000000"}
{assign var=color_border_lighter value="#CCCCCC"}
{* {assign var=color_line_even value="#FFFFFF"}
{assign var=color_line_odd value="#F9F9F9"} *}
{assign var=font_size_text value="9pt"}
{assign var=font_size_h1 value="14pt"}
{assign var=font_size_header value="9pt"}
{assign var=font_size_product value="9pt"}
{assign var=height_header value="20px"}

<style>
    .h1 {
		vertical-align: middle;
		text-align: center;
		font-weight: bold;
        font-size: {$font_size_h1|escape:'htmlall':'UTF-8'};
        color: #444;
    }

	table, th, td {
		{* margin: 0!important; *}
		{* padding: 0!important; *}
		vertical-align: middle;
		font-size: {$font_size_text|escape:'htmlall':'UTF-8'};
		white-space: nowrap;
	}

	th.product {
		border-bottom: 1px solid {$color_border|escape:'htmlall':'UTF-8'};
	}

	tr.product td {
		border-bottom: 1px solid {$color_border_lighter|escape:'htmlall':'UTF-8'};
	}

	td.product {
		vertical-align: middle;
		font-size: {$font_size_product|escape:'htmlall':'UTF-8'};
	}

	th.header {
		font-size: {$font_size_header|escape:'htmlall':'UTF-8'};
		height: {$height_header|escape:'htmlall':'UTF-8'};
		background-color: {$color_header|escape:'htmlall':'UTF-8'};
		vertical-align: middle;
		text-align: center;
		font-weight: bold;
	}

	th.header-right {
		font-size: {$font_size_header|escape:'htmlall':'UTF-8'};
		height: {$height_header|escape:'htmlall':'UTF-8'};
		background-color: {$color_header|escape:'htmlall':'UTF-8'};
		vertical-align: middle;
		text-align: right;
		font-weight: bold;
	}

	td.signature {
		border-bottom: 1px solid {$color_border|escape:'htmlall':'UTF-8'};
	}

	.left {
		text-align: left;
	}

	.right {
		text-align: right;
	}

	.center {
		text-align: center;
	}

	.bold {
		font-weight: bold;
	}

	.border {
		border: 1px solid black;
	}

	.small {
		font-size:small;
	}

    .big,
	tr.big td{
		font-size: 110%;
	}
</style>
