<?php

declare(strict_types=1);

namespace App;

class CsvProducer
{
    static public function produceCSV($file_name, $arr)
    {

        $has_header = false;

        $f = @fopen($file_name, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }

        foreach ($arr as $c) {
            $fp = fopen($file_name, "a");

            if (!$has_header) {
                fputcsv($fp, array_keys($c));
                $has_header = true;
            }

            fputcsv($fp, $c);
            fclose($fp);
        }
    }
}
