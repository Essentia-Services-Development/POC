<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UkF1UUU0NTFyOUdvWnNxMmxvcXlLcjZSMjFxdnE0bzY3SDJRVm12c01nUGQzcjdYNTVpY05tdWc3eEJIZzlsSmFMbFZEWXdZZnJ2VVl0b25haDkzUXRiQmxJK0FBU3VWT0dicEg0U05TcVF4R25zRWpPZVN3S0g3L1JZVHNUY2dSQ0xzV2dwc3krQ01YSFZKVmpwWmFD*/
namespace Aws;

use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class Psr16CacheAdapter implements CacheInterface
{
    /** @var SimpleCacheInterface */
    private $cache;

    public function __construct(SimpleCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        $this->cache->set($key, $value, $ttl);
    }

    public function remove($key)
    {
        $this->cache->delete($key);
    }
}
