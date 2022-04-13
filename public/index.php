<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\EstoreScraper;
use App\CsvProducer;
use App\CsvReader;

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
define('VIEWS_PATH', $root . 'views' . DIRECTORY_SEPARATOR);

$estoreScraper = new EstoreScraper(url: 'http://estoremedia.space/DataIT/');
$srapedData = $estoreScraper->getProductsWithPagination(download: true);
CsvProducer::produceCSV("products.csv", $srapedData);

echo '<br>';
echo "Prasowanie produktów zakończone, wynik zapisany do pliku 'products.csv' w katalogu public";
echo '<br>';

$products = CsvReader::readCSV('products.csv');

require VIEWS_PATH . 'table.php';
