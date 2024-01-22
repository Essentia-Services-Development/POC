<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UnRXRVZjanJBMHo3M1dyRWVGSWd1YVkyTzJiTkJVMTRZQWh3VWZ2SHVkRklaYVo2OXdTTlVsZUR1aUNMMERwaDNnUHdMTU1JbHQxL3hmNDNEaWp6eGJmSmhnNWpLU3JIczNJMzN4OU9NNFgxSEErU0YyWTVYTmFLMGgxbk8rREVSNzVDUEsrSHhPa05UbkhQdGo5eTRi*/
namespace Aws;

use Psr\Cache\CacheItemPoolInterface;

class PsrCacheAdapter implements CacheInterface
{
    /** @var CacheItemPoolInterface */
    private $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function get($key)
    {
        $item = $this->pool->getItem($key);

        return $item->isHit() ? $item->get() : null;
    }

    public function set($key, $value, $ttl = 0)
    {
        $item = $this->pool->getItem($key);
        $item->set($value);
        if ($ttl > 0) {
            $item->expiresAfter($ttl);
        }

        $this->pool->save($item);
    }

    public function remove($key)
    {
        $this->pool->deleteItem($key);
    }
}
