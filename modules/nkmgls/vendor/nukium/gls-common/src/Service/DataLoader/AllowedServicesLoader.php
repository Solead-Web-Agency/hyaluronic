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

namespace Nukium\GLS\Common\Service\DataLoader;

use Nukium\GLS\Common\Exception\GlsException;
use Nukium\GLS\Common\Service\API\AllowedServicesApi;
use Nukium\GLS\Common\Service\Handler\DTO\GLS\AllowedServicesHandler;
use Nukium\GLS\Common\Service\Routine\CacheRoutine;

class AllowedServicesLoader
{
    private static $instance = null;

    protected $allowedServicesHandler;

    protected $allowedServicesAPI;

    protected $cacheRoutine;

    protected $cache = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                AllowedServicesHandler::getInstance(),
                AllowedServicesApi::getInstance(),
                CacheRoutine::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        AllowedServicesHandler $allowedServicesHandler,
        AllowedServicesApi $allowedServicesAPI,
        CacheRoutine $cacheRoutine
    ) {
        $this->allowedServicesHandler = $allowedServicesHandler;
        $this->allowedServicesAPI = $allowedServicesAPI;
        $this->cacheRoutine = $cacheRoutine;
    }

    public function getAllowedServices($params)
    {
        $cacheKey = json_encode($params);

        if (!isset($this->cache[$cacheKey])) {
            try {
                $data = $this->load($params);
            } catch (GlsException $e) {
                $data = $e;
            }

            $this->cache[$cacheKey] = $data;
        }

        if ($this->cache[$cacheKey] instanceof GlsException) {
            throw $this->cache[$cacheKey];
        }

        return $this->cache[$cacheKey];
    }

    protected function load($params)
    {
        $cached = $this->loadFromCachedHandler($params);

        if ($cached === null) {
            $cached = $this->allowedServicesAPI->getAllowedServices($params);

            $ttl = new \DateTime('+24 hours');
            $cachedStr = json_encode($cached);

            $this->cacheRoutine->update(
                $params,
                $cachedStr,
                $ttl
            );
        }

        return $cached;
    }

    protected function loadFromCachedHandler($params)
    {
        $cached = $this->cacheRoutine->get($params);

        if ($cached === null) {
            return null;
        }

        return $this->allowedServicesHandler->load($cached);
    }
}
