<?php

declare(strict_types=1);
$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);

use App\EstoreScraper;
use App\CsvProducer;

require_once __DIR__ . '/../vendor/autoload.php';
require(APP_PATH . 'EstoreScraper.php');
require(APP_PATH . 'CsvProducer.php');

$scraper = new EstoreScraper();

$arr = $scraper->getProductsWithPagination('http://estoremedia.space/DataIT/index.php?page=3');

print_r($arr);

CsvProducer::produceCSV("products.csv", $arr);
