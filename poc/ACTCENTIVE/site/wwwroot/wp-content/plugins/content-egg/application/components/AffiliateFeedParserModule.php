<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;

/**
 * AffiliateFeedParserModule abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2023 keywordrush.com
 */
abstract class AffiliateFeedParserModule extends AffiliateParserModule
{

    const TRANSIENT_LAST_IMPORT_DATE = 'cegg_products_last_import_';
    const PRODUCTS_TTL = 43200;
    const MULTIPLE_INSERT_ROWS = 50;
    const IMPORT_TIME_LIMT = 600;
    const DATAFEED_DIR_NAME = 'cegg-datafeeds';
    const TRANSIENT_LAST_IMPORT_ERROR = 'cegg_last_import_error_';

    protected $rmdir;
    protected $product_model;

    abstract public function getProductModel();

    abstract public function getFeedUrl();

    abstract protected function feedProductPrepare(array $data);

    public function __construct($module_id = null)
    {
        parent::__construct($module_id);
        $this->product_model = $this->getProductModel();

        // download feed in background
        \add_action('cegg_' . $this->getId() . '_init_products', array(get_called_class(), 'initProducts'), 10, 1);
    }

    public static function initProducts($module_id)
    {
        $m = ModuleManager::factory($module_id);

        try
        {
            $m->maybeImportProducts();
        }
        catch (\Exception $e)
        {
            $error = $e->getMessage();
            if (!strstr($error, 'Product import is in progress'))
            {
                $m->setLastImportError($error);
            }
        }
    }

    public function requirements()
    {
        $required_version = '5.6.4';
        $mysql_version = $this->product_model->getDb()->get_var('SELECT VERSION();');
        $errors = array();

        if (version_compare($required_version, $mysql_version, '>'))
        {
            $errors[] = sprintf('You are using MySQL %s. This module requires at least <strong>MySQL %s</strong>.', $mysql_version, $required_version);
        }

        return $errors;
    }

    public function isZippedFeed()
    {
        return false;
    }

    public function maybeCreateProductTable()
    {
        if (!$this->product_model->isTableExists())
        {
            $this->dbDelta();
        }
    }

    protected function dbDelta()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = $this->product_model->getDump();
        dbDelta($sql);
    }

    public function getLastImportDate()
    {
        return \get_transient(self::TRANSIENT_LAST_IMPORT_DATE . $this->getId());
    }

    public function getLastImportError()
    {
        return \get_transient(self::TRANSIENT_LAST_IMPORT_ERROR . $this->getId());
    }

    public function setLastImportDate($time = null)
    {
        if ($time === null)
        {
            $time = time();
        }
        \set_transient(self::TRANSIENT_LAST_IMPORT_DATE . $this->getId(), $time);
    }

    public function setLastImportError($error)
    {
        \set_transient(self::TRANSIENT_LAST_IMPORT_ERROR . $this->getId(), $error);
    }

    public function maybeImportProducts()
    {
        $last_export = $this->getLastImportDate();

        // product import is in progress?
        if ($last_export && $last_export < 0)
        {
            if (time() + $last_export > static::IMPORT_TIME_LIMT)
            {
                $last_export = 0;
            }
            else
            {
                throw new \Exception('Product import is in progress. Try later.');
            }
        }

        if ($this->isImportTime())
        {
            // set in progress flag
            $this->deleteTemporaryFiles();
            $this->setLastImportDate(time() * -1);
            $this->maybeCreateProductTable();

            if (!$this->product_model->isTableExists())
            {
                throw new \Exception(sprintf('Table %s does not exist', $this->product_model->tableName()));
            }

            $this->importProducts($this->getFeedUrl());

            return true;
        }

        return false;
    }

    public static function getProductsTtl()
    {
        return \apply_filters('cegg_feed_products_ttl', self::PRODUCTS_TTL);
    }

    public function isImportTime()
    {
        $last_export = $this->getLastImportDate();
        if (!$last_export || (time() - $last_export > self::getProductsTtl()))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function importProducts($feed_url)
    {
        @set_time_limit(static::IMPORT_TIME_LIMT);
        \wp_raise_memory_limit();
        $this->setLastImportError('');
        register_shutdown_function(array($this, 'fatalHandler'));

        $this->product_model->truncateTable();
        $file = $this->downlodFeed($feed_url);
        $this->processFeed($file);

        $this->setLastImportDate();

        @unlink($file);
        if ($this->rmdir)
        {
            @rmdir($this->rmdir);
            $this->rmdir = null;
        }
    }

    protected function downlodFeed($feed_url)
    {
        if (!function_exists('\download_url'))
        {
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        }

        $tmp = \download_url($feed_url, 900);
        if (\is_wp_error($tmp))
        {
            $this->setLastImportDate(0);
            throw new \Exception(sprintf('Feed URL could not be downloaded: %s.', $tmp->get_error_message()));
        }

        if (!$this->isZippedFeed())
        {
            return $tmp;
        }
        else
        {
            return $this->unzipFeed($tmp);
        }
    }

    protected function unzipFeed($file)
    {
        if (!function_exists('\unzip_file'))
        {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        // unzip_file function requires the Filesystem API to be initialized
        global $wp_filesystem;
        if (!$wp_filesystem)
        {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            \WP_Filesystem();
        }

        $to = trailingslashit($this->getDatafeedDir()) . basename($file) . '-unzipped-dir';
        if (!$to)
        {
            throw new \Exception('Temporary directory does not exist.');
        }

        $result = \unzip_file($file, $to);
        @unlink($file);
        if (\is_wp_error($result))
        {
            $this->setLastImportDate(0);
            throw new \Exception(sprintf('Unable to unzip feed archive: %s.', $result->get_error_message()));
        }

        $scanned = array_values(array_diff(scandir($to), array('..', '.')));
        if (!$scanned || !isset($scanned[0]))
        {
            $this->setLastImportDate(0);
            throw new \Exception('Unable to find unziped feed.');
        }

        $this->rmdir = $to;

        return $to . DIRECTORY_SEPARATOR . $scanned[0];
    }

    protected function processFeed($file)
    {
        $format = $this->config('feed_format', 'csv');
        if ($format == 'xml')
        {
            $this->processFeedXml($file);
        }
        elseif ($format == 'json')
        {
            $this->processFeedJson($file);
        }
        else
        {
            $this->processFeedCsv($file);
        }
    }

    protected function processFeedCsv($file)
    {
        $encoding = $this->config('encoding', 'UTF-8');

        $handle = fopen($file, "r");
        $fields = array();
        $products = array();

        $delimer = $this->detectCsvDelimiter($file);
        $in_stock_only = $this->config('in_stock', false);
        $i = 0;
        while (($data = fgetcsv($handle, 0, $delimer)) !== false)
        {
            if ($encoding == 'ISO-8859-1')
            {
                $data = array_map('utf8_encode', $data);
            }

            if (!$fields)
            {
                $data = str_replace("\xEF\xBB\xBF", '', $data);
            }

            $data = array_map(function ($item)
            {
                return trim($item, ' \'"');
            }, $data);

            if (!$fields)
            {
                $fields = $data;
                continue;
            }

            if (count($fields) != count($data))
            {
                continue;
            }

            $data = array_combine($fields, $data);

            try
            {
                $product = $this->feedProductPrepare($data);
            }
            catch (\Exception $e)
            {
                if ($i > 10)
                {
                    continue;
                }
                $this->setLastImportError($e->getMessage());
                fclose($handle);

                return;
            }


            if (!$product)
            {
                continue;
            }

            if (!empty($product['ean']))
            {
                $product['ean'] = TextHelper::fixEan($product['ean']);
            }

            if ($in_stock_only && $product['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            {
                continue;
            }

            $products[] = $product;
            $i++;
            if ($i % static::MULTIPLE_INSERT_ROWS == 0)
            {
                $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
                $products = array();
            }
        }
        if ($products)
        {
            $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
        }
    }

    protected function processFeedXml($file)
    {
        $uniqueNode = $this->getProductNode();
        if (!$uniqueNode)
        {
            $uniqueNode = 'offer';
        }

        $streamer = \ContentEgg\application\vendor\XmlStringStreamer\XmlStringStreamer::createUniqueNodeParser($file, array('uniqueNode' => $uniqueNode));
        $in_stock_only = $this->config('in_stock', false);
        $i = 0;
        $products = array();

        libxml_use_internal_errors(true);

        $encoding = $this->config('encoding', 'UTF-8');

        while ($node_string = $streamer->getNode())
        {
            if ($encoding != 'UTF-8')
            {
                $node_string = iconv($encoding, 'UTF-8//TRANSLIT//IGNORE', $node_string);
            }

            $node = simplexml_load_string($node_string);
            if ($node === false)
            {
                $err_mess = 'Cannot load xml source.';

                if ($error = libxml_get_last_error())
                {
                    $err_mess .= $error->message;
                }

                $this->setLastImportError($err_mess);

                return;
            }

            $data = $this->mapXmlData($node);

            try
            {
                $product = $this->feedProductPrepare($data);
            }
            catch (\Exception $e)
            {

                if ($i > 10)
                {
                    continue;
                }

                $this->setLastImportError($e->getMessage());

                return;
            }

            if (!$product)
            {
                continue;
            }

            if (!empty($product['ean']))
            {
                $product['ean'] = TextHelper::fixEan($product['ean']);
            }

            if ($in_stock_only && $product['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            {
                continue;
            }

            $products[] = $product;
            $i++;
            if ($i % static::MULTIPLE_INSERT_ROWS == 0)
            {
                $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
                $products = array();
            }
        }

        if ($i == 0)
        {
            $this->setLastImportError('Product node not found.');
        }

        if ($products)
        {
            $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
        }
    }

    protected function processFeedJson($file)
    {
        $encoding = $this->config('encoding', 'UTF-8');
        $in_stock_only = $this->config('in_stock', false);

        $json = file_get_contents($file);
        $json_arr = json_decode($json, true);

        if (!$json_arr)
        {
            $this->setLastImportError(trim('Cannot decode JSON source. ' . json_last_error_msg()));

            return;
        }

        $node = $this->getProductNode();

        if (!$node && is_array($json_arr))
        {
            $node = 'offer';
            $json_arr = array($node => $json_arr);
        }

        if (!isset($json_arr[$node]) || !is_array($json_arr[$node]))
        {
            $this->setLastImportError('The product node "' . \esc_html($node) . '" does not exist.');

            return;
        }

        $i = 0;
        foreach ($json_arr[$node] as $data)
        {
            if ($encoding == 'ISO-8859-1')
            {
                $data = array_map('utf8_encode', $data);
            }

            try
            {
                $product = $this->feedProductPrepare($data);
            }
            catch (\Exception $e)
            {
                if ($i > 10)
                {
                    continue;
                }
                $this->setLastImportError($e->getMessage());

                return;
            }

            if (!$product)
            {
                continue;
            }

            if (!empty($product['ean']))
            {
                $product['ean'] = TextHelper::fixEan($product['ean']);
            }

            if ($in_stock_only && $product['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            {
                continue;
            }

            $products[] = $product;
            $i++;
            if ($i % static::MULTIPLE_INSERT_ROWS == 0)
            {
                $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
                $products = array();
            }
            $i++;
        }
        if ($products)
        {
            $this->product_model->multipleInsert($products, static::MULTIPLE_INSERT_ROWS);
        }
    }

    protected function mapXmlData($node)
    {
        $data = array();
        $mapping = $this->config('mapping');
        $fields = array_values($mapping);
        $attributes = $node->attributes();

        foreach ($fields as $field)
        {
            if (isset($attributes[$field]))
                $data[$field] = (string) $attributes[$field];
            elseif (isset($node->{$field}))
                $data[$field] = (string) $node->{$field};
            elseif ($res = $node->xpath($field))
                $data[$field] = trim(\wp_strip_all_tags((string) $res[0]));
            else
                continue;
        }

        return $data;
    }

    public function getLastImportDateReadable()
    {
        $last_import = $this->getLastImportDate();

        if (empty($last_import))
        {
            return '';
        }

        if ($last_import < 0)
        {
            return __('Product import is in progress', 'content-egg');
        }

        if (time() - $last_import <= 43200)
        {
            return sprintf(__('%s ago', '%s = human-readable time difference', 'content-egg'), \human_time_diff($last_import, time()));
        }

        return TemplateHelper::dateFormatFromGmt($last_import, true);
    }

    public function getProductCount()
    {
        return $this->product_model->count();
    }

    protected function getDatafeedDir()
    {
        $upload_dir = \wp_upload_dir();
        $datafeed_dir = $upload_dir['basedir'] . '/' . static::DATAFEED_DIR_NAME;

        if (is_dir($datafeed_dir))
        {
            return $datafeed_dir;
        }

        $files = array(
            array(
                'file' => 'index.html',
                'content' => '',
            ),
            array(
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
        );

        foreach ($files as $file)
        {
            if (\wp_mkdir_p($datafeed_dir) && !file_exists(trailingslashit($datafeed_dir) . $file['file']))
            {
                if ($file_handle = @fopen(trailingslashit($datafeed_dir) . $file['file'], 'w'))
                {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }

        if (!is_dir($datafeed_dir))
        {
            throw new \Exception('Can not create temporary directory for datafeed.');
        }

        return $datafeed_dir;
    }

    protected function detectCsvDelimiter($file)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($file, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count)
        {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    public function fatalHandler()
    {
        if (!$error = error_get_last())
        {
            return;
        }

        if (!isset($error['file']) || !strpos($error['file'], 'AffiliateFeedParserModule.php'))
        {
            return;
        }

        $message = $error['message'];
        if (strstr($message, 'Allowed memory size'))
        {
            $message .= '. ' . sprintf(__('Your data feed is too large and cannot be imported. Use a smaller feed or increase <a target="_blank" href="%s">WP_MAX_MEMORY_LIMIT</a>.', 'content-egg'), 'https://wordpress.org/support/article/editing-wp-config-php/#increasing-memory-allocated-to-php');
        }

        $this->setLastImportError($message);
    }

    public function deleteTemporaryFiles()
    {
        $dir = trailingslashit($this->getDatafeedDir());
        $parts = explode('/', $dir);
        if ($parts[count($parts) - 2] !== self::DATAFEED_DIR_NAME)
        {
            throw new \Exception('Unexpected error while cleaning temporary directory.');

            return;
        }

        $scanned = array_values(array_diff(scandir($dir), array('..', '.', 'index.html', '.htaccess')));
        if (!$scanned)
        {
            return;
        }

        global $wp_filesystem;
        if (!$wp_filesystem)
        {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            \WP_Filesystem();
        }

        foreach ($scanned as $s)
        {
            $path = $dir . $s;

            if (is_dir($path) && !preg_match('/-unzipped-dir$/', $path))
            {
                continue;
            }

            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) !== 'csv')
            {
                continue;
            }

            if ($wp_filesystem->exists($path) && time() - filemtime($path) > 3600)
            {
                $wp_filesystem->delete($path, true);
            }
        }
    }

    public function getProductNode()
    {
        $mapping = $this->config('mapping');
        if (!empty($mapping['product node']))
        {
            return $mapping['product node'];
        }
        else
        {
            return false;
        }
    }
}
