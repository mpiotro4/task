<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\EstoreScraper;

$estoreScraper = new EstoreScraper();

echo "to jest produkt id=";
if (isset($_GET['id'])) echo $_GET['id'];
$estoreScraper->getProduct($_GET['id']);
echo '<br>';
