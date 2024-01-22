<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UzNLN1NwcHBqaWNPQ2hBQWRNS0tOT3gxZlZYbkVCdmkvL0dIWkNTYU5BemxhWmYrUmNhR1hTOFVDUCs4VUcxZXh2VFVQZUt1RU9kMFRwQTZseVZwR2pGaDgwYS9NMW04L3hsTDdPQzI0MkJjVk9JbmFMWndmTWEvUC9EZ2hUNUVMVXp6ckNJZDYwb2JaalljdFJzSWdK*/
namespace Aws;

/**
 * Loads JSON files and compiles them into PHP arrays.
 *
 * @internal Please use json_decode instead.
 * @deprecated
 */
class JsonCompiler
{
    const CACHE_ENV = 'AWS_PHP_CACHE_DIR';

    /**
     * Loads a JSON file from cache or from the JSON file directly.
     *
     * @param string $path Path to the JSON file to load.
     *
     * @return mixed
     */
    public function load($path)
    {
        return load_compiled_json($path);
    }
}
