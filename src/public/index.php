<?php

declare(strict_types=1);
$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);
require(APP_PATH . 'HtmlParser.php');

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, "http://estoremedia.space/DataIT/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$html = curl_exec($ch);
curl_close($ch);

$htmlParser = new HtmlParser();

echo '<pre>';
print_r($htmlParser->getProducts($html));
echo '</pre>';
