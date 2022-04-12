<?php

declare(strict_types=1);
$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);
require(APP_PATH . 'EstoreScraper.php');

$scraper = new EstoreScraper();

echo '<pre>';
print_r($scraper->getProductsWithPagination('http://estoremedia.space/DataIT/index.php?page=3'));
echo '</pre>';
