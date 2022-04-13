<?php

declare(strict_types=1);

namespace App;

class HtmlProducer
{
    public static function downloadHtml(string $url): string
    {
        $fileName = str_replace(['/', ':', '?'], '', $url);
        if (!file_exists($fileName)) {
            $out = HtmlProducer::getHtml($url);
            $fp = fopen($fileName, 'w+');
            fwrite($fp, $out);
            fclose($fp);
        };
        return file_get_contents($fileName);
    }

    public static function getHtml(string $url, bool $download = false): string
    {
        if ($download) return HtmlProducer::downloadHtml($url);

        libxml_use_internal_errors(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
}
