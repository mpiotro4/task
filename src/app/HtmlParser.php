<?php

declare(strict_types=1);


class HtmlParser
{
    public function getProducts($html)
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $finder = new DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'card h-100')]");

        $rawHtmlProducts = [];

        foreach ($nodes as $node) {
            $rawHtmlProducts[] = $node->ownerDocument->saveHTML($node);
        }

        return $this->getOutput($rawHtmlProducts);
    }

    private function getOutput($rawHtmlProducts)
    {
        foreach ($rawHtmlProducts as $product) {
            $dom = new DOMDocument();
            $dom->loadHTML(mb_convert_encoding($product, 'HTML-ENTITIES', 'UTF-8'));
            $output[] = $this->getProductParams($dom);
        }
        return $output;
    }

    private function getProductParams($dom)
    {
        $rating = $dom->getElementsByTagName('small')[0]->nodeValue;

        return [
            'name' => $dom->getElementsByTagName('a')[1]->getAttribute('data-name'),
            'url' => $dom->getElementsByTagName('a')[1]->getAttribute('href'),
            'img' => $dom->getElementsByTagName('img')[0]->getAttribute('src'),
            'price' => $dom->getElementsByTagName('h5')[0]->nodeValue,
            'rating' => $dom->getElementsByTagName('small')[0]->nodeValue,
            'number of ratings' => $dom->getElementsByTagName('small')[0]->nodeValue
        ];
    }
}
