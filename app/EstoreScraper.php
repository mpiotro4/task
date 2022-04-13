<?php

declare(strict_types=1);

namespace App;

class EstoreScraper
{
    public function getProductsWithPagination($url, $download = false)
    {
        $pages = $this->getPages($url);
        $output = [];
        foreach ($pages as $page) {
            $output = array_merge($output, $this->getProducts($page, $download));
        }
        return $output;
    }

    public function getProducts($url, $download = false)
    {
        $html = $this->getHtml($url, $download);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $nodes = $this->getNodesByClassName('card h-100', $dom);

        $rawHtmlProducts = [];
        foreach ($nodes as $node) {
            $rawHtmlProducts[] = $node->ownerDocument->saveHTML($node);
        }

        return $this->getOutput($rawHtmlProducts);
    }

    private function getPages($url)
    {
        $html = $this->getHtml($url);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $nodes = $this->getNodesByClassName('page-link next', $dom);
        $pages = [];
        foreach ($nodes as $node) {
            // to do zmienić to 
            $pages[] = $url . 'index.php?page=' . $node->getAttribute('data-page');
        }
        return $pages;
    }

    private function downloadHtml($url)
    {
        $fileName = str_replace(['/', ':', '?'], '', $url);
        if (!file_exists($fileName)) {
            $out = $this->getHtml($url);
            $fp = fopen($fileName, 'w+');
            fwrite($fp, $out);
            fclose($fp);
        };
        return file_get_contents($fileName);
    }

    private function getHtml($url, $download = false)
    {
        if ($download) return $this->downloadHtml($url);

        libxml_use_internal_errors(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    private function getOutput($rawHtmlProducts)
    {
        foreach ($rawHtmlProducts as $product) {
            $dom = new \DOMDocument();
            $dom->loadHTML(mb_convert_encoding($product, 'HTML-ENTITIES', 'UTF-8'));
            $output[] = $this->getProductParams($dom);
        }
        return $output;
    }

    private function getProductParams($dom)
    {
        $ratingWithNumber = trim($dom->getElementsByTagName('small')[0]->nodeValue);
        [$rating, $number] = explode(' ', $ratingWithNumber, 2);
        $number = str_replace(array('(', ')'), '', $number);
        return [
            'name' => $dom->getElementsByTagName('a')[1]->getAttribute('data-name'),
            'url' => $dom->getElementsByTagName('a')[1]->getAttribute('href'),
            'img' => $dom->getElementsByTagName('img')[0]->getAttribute('src'),
            'price' => $dom->getElementsByTagName('h5')[0]->nodeValue,
            'rating' => $rating,
            'number of ratings' => $number
        ];
    }

    private function getNodesByClassName($className, $dom)
    {
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$className')]");
        return $nodes;
    }
}
