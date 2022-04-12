<?php

declare(strict_types=1);

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);
define('FILES_PATH', $root . 'transaction_files' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', $root . 'views' . DIRECTORY_SEPARATOR);


require(APP_PATH . 'App.php');

echo "Curl example";
echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, "http://estoremedia.space/DataIT/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML($data);

$finder = new DomXPath($dom);
$classname = "card h-100";
$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

$rawHtmlProducts = [];

foreach ($nodes as $node) {
    $rawHtmlProducts[] = $node->ownerDocument->saveHTML($node);
}

$output = [];

foreach ($rawHtmlProducts as $product) {
    $dom2 = new DOMDocument();
    $dom2->loadHTML($product);
    $productArray = [
        'name' => $dom2->getElementsByTagName('a')[1]->getAttribute('data-name'),
        'url' => $dom2->getElementsByTagName('a')[1]->getAttribute('href'),
        'img' => $dom2->getElementsByTagName('img')[0]->getAttribute('src'),
        'price' => $dom2->getElementsByTagName('h5')[0]->nodeValue,
        'rating' => $dom2->getElementsByTagName('small')[0]->nodeValue,
        'number of ratings' => $dom2->getElementsByTagName('small')[0]->nodeValue
    ];
    $output[] = $productArray;
}

echo '<pre>';
print_r($output);
echo '</pre>';
