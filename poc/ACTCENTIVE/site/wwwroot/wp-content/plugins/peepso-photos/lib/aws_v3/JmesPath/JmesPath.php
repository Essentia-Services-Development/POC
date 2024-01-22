<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VGdCcjNnMys1ZlJVNWd3TWZrVlBGK2IxQzAwaHo4RjhQL1pycWI5NUtYai9lelkzQlVsVHJabUhUYlVZdCtNcllDQjRGOE8zMXRPTUJDOUJhNWtEWkNybytHQk1HeUtaNDFKQVlzZTJzRkFITWdsQVlFU1NpRzlhcEpzNXA5WkcwPQ==*/
namespace JmesPath;

/**
 * Returns data from the input array that matches a JMESPath expression.
 *
 * @param string $expression Expression to search.
 * @param mixed $data Data to search.
 *
 * @return mixed
 */
if (!function_exists(__NAMESPACE__ . '\search')) {
    function search($expression, $data)
    {
        return Env::search($expression, $data);
    }
}
