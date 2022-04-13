<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\EstoreScraper;

$estoreScraper = new EstoreScraper();

echo "<pre>";
echo "id: ";
if (isset($_GET['id'])) echo $_GET['id'];
echo "<br>";
print_r($estoreScraper->getProduct($_GET['id']));
echo "</pre>";
echo '<br>';
