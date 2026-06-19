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

class GlsErrorValue
{
    const TYPE_INVALID_FIELD = 'INVALID_FIELD_VALUE';
    const TYPE_INVALID_PARCELSHOP = 'INVALID_PARCELSHOP_ID';
    const TYPE_NOT_AVAILABLE_SHIPMENT = 'NO_PRODUCT_OF_TYPE_AVAILABLE_FOR_SHIPMENT';
    const TYPE_REQUIRED_PARAMETER = 'MANDATORY_PARAMETER_NOT_SET';

    const ARG_CONTACT_ID = 'Shipment.Shipper.ContactID';
    const ARG_EMAIL = 'consignee.email';
    const ARG_RELAIS_EMAIL = 'CONSIGNEE_ADDRESS_EMAIL_MANDATORY_SHOPDELIVERY_FRANCE';
    const ARG_ADDRESS = 'Shipment.Consignee.Address';
    const ARG_REFERENCE = 'Shipment.ShipmentUnitNumber';
    const ARG_WEIGHT_TOO_LOW = 'shipmentunit.weight.toolow';
    const ARG_WEIGHT_TOO_HIGH = 'shipmentunit.weight.toohigh';
    const ARG_INVALID_ZIPCODE1 = 'consignee.zip';
    const ARG_INVALID_ZIPCODE2 = 'Shipment.Consignee.Address.ZIPCode';
}
