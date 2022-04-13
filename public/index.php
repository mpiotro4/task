<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\EstoreScraper;
use App\CsvProducer;

$scraper = new EstoreScraper();

$arr = $scraper->getProductsWithPagination(url: 'http://estoremedia.space/DataIT/', download: true);

echo "<pre>";
print_r($arr);
echo "</pre>";
CsvProducer::produceCSV("products.csv", $arr);
