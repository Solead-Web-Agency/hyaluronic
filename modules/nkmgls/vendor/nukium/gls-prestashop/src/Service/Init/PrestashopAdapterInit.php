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

namespace Nukium\PrestaShop\GLS\Service\Init;

use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\GLS\Common\Service\Adapter\EntityManager\EntityManagerFactory;
use Nukium\GLS\Common\Service\Adapter\Shop\ShopFactory;
use Nukium\GLS\Common\Service\Adapter\Translator\TranslatorFactory;
use Nukium\GLS\Common\Service\Adapter\Utility\UtilityFactory;
use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Address\AddressHandlerFactory;
use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Carrier\CarrierHandlerFactory;
use Nukium\GLS\Common\Service\Handler\Entity\Cache\CacheHandlerFactory;
use Nukium\GLS\Common\Service\Init\AdapterInitInterface;
use Nukium\GLS\Common\Service\Repository\Cache\CacheRepositoryFactory;
use Nukium\PrestaShop\GLS\Service\Adapter\Config\PrestashopConfig;
use Nukium\PrestaShop\GLS\Service\Adapter\EntityManager\PrestashopEntityManager;
use Nukium\PrestaShop\GLS\Service\Adapter\Shop\PrestashopShop;
use Nukium\PrestaShop\GLS\Service\Adapter\Translator\PrestashopTranslator;
use Nukium\PrestaShop\GLS\Service\Adapter\Utility\PrestashopUtility;
use Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Address\PrestashopAddressHandler;
use Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Carrier\PrestashopCarrierHandler;
use Nukium\PrestaShop\GLS\Service\Handler\Entity\Cache\PrestashopCacheHandler;
use Nukium\PrestaShop\GLS\Service\Repository\Cache\PrestashopCacheRepository;

class PrestashopAdapterInit implements AdapterInitInterface
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
    }

    public function init()
    {
        CacheRepositoryFactory::setInstance(PrestashopCacheRepository::getInstance());
        AddressHandlerFactory::setInstance(PrestashopAddressHandler::getInstance());
        CarrierHandlerFactory::setInstance(PrestashopCarrierHandler::getInstance());
        CacheHandlerFactory::setInstance(PrestashopCacheHandler::getInstance());

        ConfigFactory::setInstance(PrestashopConfig::getInstance());
        EntityManagerFactory::setInstance(PrestashopEntityManager::getInstance());
        ShopFactory::setInstance(PrestashopShop::getInstance());
        TranslatorFactory::setInstance(PrestashopTranslator::getInstance());
        UtilityFactory::setInstance(PrestashopUtility::getInstance());
    }
}
