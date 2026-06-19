<?php
/**
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
 */

namespace Nukium\GLS\Common\Value;

class GlsValue
{
    const LEGACY_RELAY_API = 'http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/ParcelShopSearch/wsdl/2010_01_ParcelShopSearch.wsdl';
    const LEGACY_REST_API_URL = 'https://api.gls-group.eu/public/v1/';
    const SHIPIT_REST_API_URL = 'https://shipit-wbm-fr01.gls-group.eu:443/';

    const TRACKING_URL = 'https://gls-group.eu/FR/fr/suivi-colis?match=';

    const LIMIT_RELAY = 10;
    const RELAY_XL_PREFIX = 'XL';

    const GLS_RELAIS = 'GLS_RELAIS';
    const GLS_CHEZ_VOUS = 'GLS_CHEZ_VOUS';
    const GLS_CHEZ_VOUS_PLUS = 'GLS_CHEZ_VOUS_PLUS';
    const GLS_AVANT_13H = 'GLS_AVANT_13H';

    const PRODUCT_PARCEL = 'PARCEL';
    const PRODUCT_EXPRESS = 'EXPRESS';

    const PRODUCT_CODE = [
        '01' => 'EuroBusinessParcel',
        '02' => 'BusinessParcel',
        '17' => 'ShopDeliveryService',
        '18' => 'FlexDeliveryService',
        '19' => 'FlexDeliveryService',
        '16' => 'ExpressParcelGuaranted',
        '26' => 'InternationalShopDeliveryService',
    ];

    const LABEL_PRODUCTS = [
        self::GLS_RELAIS => self::PRODUCT_PARCEL,
        self::GLS_CHEZ_VOUS => self::PRODUCT_PARCEL,
        self::GLS_CHEZ_VOUS_PLUS => self::PRODUCT_PARCEL,
        self::GLS_AVANT_13H => self::PRODUCT_EXPRESS,
    ];

    const LABEL_SERVICES = [
        self::GLS_RELAIS => 'service_shopdelivery',
        self::GLS_CHEZ_VOUS_PLUS => 'service_flexdelivery',
        self::GLS_AVANT_13H => 'service_1300',
    ];

    const TRACKING_STATES_ADAPTER = [
        'IN_DELIVERY' => 'INDELIVERY',
        'HUB' => 'INTRANSIT',
        'DELEVERY_DEPOT' => 'INWAREHOUSE',
        'DELIVERED' => 'DELIVERED',
    ];
}
