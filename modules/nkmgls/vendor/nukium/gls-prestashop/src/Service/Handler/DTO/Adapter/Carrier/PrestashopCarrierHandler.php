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

namespace Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Carrier;

use Carrier as PrestashopCarrier;
use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Carrier\CarrierHandler;
use Nukium\GLS\Common\Value\GlsValue;
use Nukium\PrestaShop\GLS\Service\Adapter\Config\PrestashopConfig;
use Nukium\PrestaShop\GLS\Service\Helper\ModuleHelper;
use Nukium\PrestaShop\GLS\Service\Repository\Carrier\PrestashopCarrierRepository;

class PrestashopCarrierHandler extends CarrierHandler
{
    private static $instance;

    protected $moduleHelper;

    protected $config;

    protected $carrierRepository;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                ModuleHelper::getInstance(),
                PrestashopConfig::getInstance(),
                PrestashopCarrierRepository::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        ModuleHelper $moduleHelper,
        PrestashopConfig $config,
        PrestashopCarrierRepository $carrierRepository
    ) {
        parent::__construct();

        $this->moduleHelper = $moduleHelper;
        $this->config = $config;
        $this->carrierRepository = $carrierRepository;
    }

    public function adapt($carrier)
    {
        $idCarrier = (int) $carrier->id;
        $carrierCode = $this->resolve($idCarrier);

        $adaptedCarrier = $this->create();

        if ($carrierCode === null) {
            return $adaptedCarrier
                ->setIsGls(false)
            ;
        }

        return $adaptedCarrier
            ->setIsGls(true)
            ->setCode($carrierCode)
        ;
    }

    public function refreshCarriersData($carrierNames)
    {
        try {
            $shipImgDir = _PS_SHIP_IMG_DIR_;
            $carriers = $this->carrierRepository->findGlsCarriers();
            $moduleDir = $this->moduleHelper->getLocalPath();
            $resolveImg = [
                GlsValue::GLS_RELAIS => 'glsrelais.jpg',
                GlsValue::GLS_CHEZ_VOUS => 'glschezvous.jpg',
                GlsValue::GLS_CHEZ_VOUS_PLUS => 'glschezvousplus.jpg',
                GlsValue::GLS_AVANT_13H => 'gls13h.jpg',
            ];

            foreach ($carriers as $c) {
                $idCarrier = (int) $c['id_carrier'];
                $prestashopCarrier = new PrestashopCarrier($idCarrier);
                $adaptedCarrier = $this->adapt($prestashopCarrier);
                $carrierCode = $adaptedCarrier->getCode();
                $carrierDefinition = $this->moduleHelper->getCarrierDefinition($carrierCode);

                if (
                    $prestashopCarrier->name !== $carrierNames[$carrierCode]
                ) {
                    continue;
                }

                $resolvedFilename = $resolveImg[$carrierCode];
                $oldLogo = "{$shipImgDir}{$idCarrier}.jpg";
                $newLogo = "{$moduleDir}views/img/admin/{$resolvedFilename}";

                if (
                    file_exists($oldLogo) &&
                    file_exists($newLogo)
                ) {
                    copy($newLogo, $oldLogo);
                }

                $prestashopCarrier->name = $carrierDefinition['name'];

                foreach (\Language::getLanguages(true) as $language) {
                    $idLang = $language['id_lang'];
                    $isoCode = $language['iso_code'];

                    if (!isset($carrierDefinition['delay'][$isoCode])) {
                        $isoCode = 'fr';
                    }

                    $prestashopCarrier->delay[$idLang] = $carrierDefinition['delay'][$isoCode];
                }

                $prestashopCarrier->update();
            }
        } catch (\Exception $e) {
            return;
        }
    }

    protected function resolve($idCarrier)
    {
        $relais = (int) $this->config->get('GLS_GLSRELAIS_ID');
        $chezVous = (int) $this->config->get('GLS_GLSCHEZVOUS_ID');
        $chezVousPlus = (int) $this->config->get('GLS_GLSCHEZVOUSPLUS_ID');
        $avant13h = (int) $this->config->get('GLS_GLS13H_ID');

        $resolve = [
            $relais => GlsValue::GLS_RELAIS,
            $chezVous => GlsValue::GLS_CHEZ_VOUS,
            $chezVousPlus => GlsValue::GLS_CHEZ_VOUS_PLUS,
            $avant13h => GlsValue::GLS_AVANT_13H,
        ];

        if (!isset($resolve[$idCarrier])) {
            return null;
        }

        return $resolve[$idCarrier];
    }
}
