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

use Nukium\GLS\Common\Entity\CacheInterface;
use Nukium\GLS\Common\Service\Repository\Cache\CacheRepositoryFactory;
use Nukium\GLS\Common\Service\Repository\Cache\CacheRepositoryInterface;

class CacheLoader
{
    private static $instance = null;

    protected $cacheRepository;

    protected $cache = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                CacheRepositoryFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        CacheRepositoryInterface $cacheRepository
    ) {
        $this->cacheRepository = $cacheRepository;
    }

    public function getCache($footprint)
    {
        if (!array_key_exists($footprint, $this->cache)) {
            $this->cache[$footprint] = $this->cacheRepository->findOneByFootprint($footprint);
        }

        return $this->cache[$footprint];
    }

    public function setCache(CacheInterface $cache)
    {
        $footprint = $cache->getMyFootprint();

        $this->cache[$footprint] = $cache;
    }

    public function removeCache(CacheInterface $cache)
    {
        $footprint = $cache->getMyFootprint();

        unset($this->cache[$footprint]);
    }
}
