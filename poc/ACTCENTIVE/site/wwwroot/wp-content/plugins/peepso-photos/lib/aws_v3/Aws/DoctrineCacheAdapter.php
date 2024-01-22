<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3l0TG1tSy9NWkF2Z0ZEcklaNmdEOFhsZkh4eFJocDhTUit0VStpMVBuZHlWT0YrR3R5WVNBNWdMdXVTeS9tR3htYUhCS0xkOTBudHdJWUJVYm8vSWlYZUdSUnlQQVFkanpYdFhBT3BpcmJsR1laQ1NMNXpVemoyUkUzWXYxUHhBTmF4RFpxL0JUUDN3cFNUb2ZuVHhz*/
namespace Aws;

use Doctrine\Common\Cache\Cache;

class DoctrineCacheAdapter implements CacheInterface, Cache
{
    /** @var Cache */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    public function fetch($key)
    {
        return $this->get($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        return $this->cache->save($key, $value, $ttl);
    }

    public function save($key, $value, $ttl = 0)
    {
        return $this->set($key, $value, $ttl);
    }

    public function remove($key)
    {
        return $this->cache->delete($key);
    }

    public function delete($key)
    {
        return $this->remove($key);
    }

    public function contains($key)
    {
        return $this->cache->contains($key);
    }

    public function getStats()
    {
        return $this->cache->getStats();
    }
}
