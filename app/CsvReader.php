<?php

declare(strict_types=1);

namespace App;

class CsvReader
{
    public static function readCSV(string $fileName): array
    {
        $output = CsvReader::readFiles($fileName);
        return $output;
    }

    private static function readFiles(string $fileName): array
    {
        $output = [];
        $file = fopen($fileName, 'r');
        fgetcsv($file);
        while (($line = fgetcsv($file)) != false) {
            $line = CsvReader::parseLine($line);
            $output[] = $line;
        }
        return $output;
    }

    private static function parseLine(array $line): array
    {
        [$name, $url, $img, $price, $rating, $number] = $line;
        return [
            'name' => $name,
            'url' => $url,
            'img' => $img,
            'price' => $price,
            'rating' => $rating,
            'number of ratings' => $number
        ];
    }
}
