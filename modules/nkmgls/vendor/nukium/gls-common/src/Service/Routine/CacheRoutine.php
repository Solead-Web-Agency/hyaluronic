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

namespace Nukium\GLS\Common\Service\Routine;

use Nukium\GLS\Common\Entity\CacheInterface;
use Nukium\GLS\Common\Service\Adapter\EntityManager\EntityManagerFactory;
use Nukium\GLS\Common\Service\Adapter\EntityManager\EntityManagerInterface;
use Nukium\GLS\Common\Service\DataLoader\CacheLoader;
use Nukium\GLS\Common\Service\Handler\Entity\Cache\CacheHandler;
use Nukium\GLS\Common\Service\Handler\Entity\Cache\CacheHandlerFactory;

class CacheRoutine
{
    private static $instance = null;

    protected $cacheHandler;

    protected $cacheLoader;

    protected $entityManager;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                CacheHandlerFactory::getInstance(),
                CacheLoader::getInstance(),
                EntityManagerFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        CacheHandler $cacheHandler,
        CacheLoader $cacheLoader,
        EntityManagerInterface $entityManager
    ) {
        $this->cacheHandler = $cacheHandler;
        $this->cacheLoader = $cacheLoader;
        $this->entityManager = $entityManager;
    }

    public function get($params)
    {
        $cached = $this->load($params);
        $rawData = $cached->getMyData();

        if ($rawData === null) {
            return null;
        }

        return json_decode($rawData, true);
    }

    public function update(
        $params,
        $data,
        \DateTimeInterface $ttl
    ) {
        try {
            $cached = $this->load($params)
                ->setMyData($data)
                ->setMyTtl($ttl)
            ;

            $this->entityManager->save($cached);
        } catch (\Exception $e) {
            return;
        }
    }

    public function remove($params)
    {
        $cached = $this->load($params);

        $this->cacheLoader->removeCache($params);

        $this->entityManager->remove($cached);
    }

    protected function load($params)
    {
        $footprint = $this->generateFootprint($params);
        $cached = $this->cacheLoader->getCache($footprint);

        if (
            $cached === null ||
            !$this->checkAndHandleTtl($cached)
        ) {
            $cached = $this->cacheHandler->create()
                ->setMyFootprint($footprint)
            ;

            $this->cacheLoader->setCache($cached);
        }

        return $cached;
    }

    protected function checkAndHandleTtl(CacheInterface $cached)
    {
        $date = new \DateTime();

        if ($cached->getMyIsStored() && $cached->getMyTtl() < $date) {
            $this->entityManager->remove($cached);

            return false;
        }

        return true;
    }

    protected function generateFootprint($params)
    {
        return hash(
            'sha256',
            json_encode($params)
        );
    }
}
