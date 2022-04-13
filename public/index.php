<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\EstoreScraper;
use App\CsvProducer;

$scraper = new EstoreScraper();

$arr = $scraper->getProductsWithPagination('http://estoremedia.space/DataIT/');

print_r($arr);

CsvProducer::produceCSV("products.csv", $arr);
