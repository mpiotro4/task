<?php

declare(strict_types=1);

// Your Code
function getTransactionData(string $inputDirPath): array
{
    $files = getFiles($inputDirPath);
    $transactionData = readFiles($files, 'parseLine');
    return $files;
}

function getTotals(array $transactionData): array
{
    $totalIncome = array_reduce($transactionData, fn($prev, $cur) => $cur['amount'] > 0 ? $cur['amount'] + $prev : $prev);
    $totalExpense = array_reduce($transactionData, fn($prev, $cur) => $cur['amount'] < 0 ? $cur['amount'] + $prev : $prev);
    return [
        'totalIncome' => $totalIncome,
        'totalExpense' => $totalExpense,
        'netTotal' => $totalIncome + $totalExpense
    ];
}

function getFiles(string $inputDirPath): array
{
    $files = array_filter(scandir($inputDirPath), fn($var) => !is_dir($inputDirPath . $var));
    return array_map(fn($var) => $inputDirPath . $var, $files);
}

function readFiles(array $files, ?callable $lineParser = null): array
{
    $output = [];
    foreach ($files as $file) {
        $file = fopen($file, 'r');
        fgetcsv($file);
        while (($line = fgetcsv($file)) != false) {
            if (!is_null($lineParser)) {
                $line = parseLine($line);
            }
            $output[] = $line;
        }
    }
    return $output;
}

function parseLine(array $line): array
{
    [$date, $checkNumber, $description, $amount] = $line;
    $amount = (float)str_replace(['$', ','], '', $amount);
    return [
        'date' => $date,
        'checkNumber' => $checkNumber,
        'description' => $description,
        'amount' => $amount,
    ];
}

function prettyPrint(array $array): void
{
    echo "<pre>";
    print_r($array);
    echo "<pre>";
}