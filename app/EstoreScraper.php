<?php

declare(strict_types=1);

namespace App;

use DOMDocument;

class EstoreScraper
{
    public function getProduct(string $id): array
    {
        $html = HtmlProducer::getHtml('http://estoremedia.space/DataIT/product.php?id=' . $id);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $finder = new \DomXPath($dom);

        $price = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/h5/span')[0]->nodeValue;
        $name = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/p/text()')[0]->nodeValue;
        $priceOld = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/h5/del')[0]->nodeValue ?? null;
        $img = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/img')[0]->getAttribute('src');
        $json = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/script')[0]->nodeValue;
        $ratingWithNumber = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[2]/small')[0]->nodeValue;
        ['rating' => $rating, 'number' => $number] = $this->parseRatingString($ratingWithNumber);

        return [
            'price' => $price,
            'old price' => $priceOld,
            'img url' => $img,
            'rating' => $rating,
            'number' => $number,
            'hidden data' =>  $this->parseJson($json, $name)
        ];
    }

    public function getProductsWithPagination(string $url, bool $download = false): array
    {
        $pages = $this->getPages($url);
        $output = [];
        foreach ($pages as $page) {
            $output = array_merge($output, $this->getProducts($page, $download));
        }
        return $output;
    }

    public function getProducts(string $url, bool $download = false): array
    {
        $html = HtmlProducer::getHtml($url, $download);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $nodes = $this->getNodesByClassName('card h-100', $dom);

        $rawHtmlProducts = [];
        foreach ($nodes as $node) {
            $rawHtmlProducts[] = $node->ownerDocument->saveHTML($node);
        }

        return $this->getOutput($rawHtmlProducts);
    }

    private function getPages(string $url): array
    {
        $html = Htmlproducer::getHtml($url);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $nodes = $this->getNodesByClassName('page-link next', $dom);
        $pages = [];
        foreach ($nodes as $node) {
            // to do zmienić to 
            $pages[] = $url . 'index.php?page=' . $node->getAttribute('data-page');
        }
        return $pages;
    }

    private function getOutput(array $rawHtmlProducts): array
    {
        foreach ($rawHtmlProducts as $product) {
            $dom = new DOMDocument();
            $dom->loadHTML(mb_convert_encoding($product, 'HTML-ENTITIES', 'UTF-8'));
            $output[] = $this->getProductParams($dom);
        }
        return $output;
    }

    private function getProductParams(DOMDocument $dom): array
    {
        $ratingWithNumber = $dom->getElementsByTagName('small')[0]->nodeValue;
        ['rating' => $rating, 'number' => $number] = $this->parseRatingString($ratingWithNumber);
        return [
            'name' => $dom->getElementsByTagName('a')[1]->getAttribute('data-name'),
            'url' => $dom->getElementsByTagName('a')[1]->getAttribute('href'),
            'img' => $dom->getElementsByTagName('img')[0]->getAttribute('src'),
            'price' => $dom->getElementsByTagName('h5')[0]->nodeValue,
            'rating' => $rating,
            'number of ratings' => $number
        ];
    }

    private function getNodesByClassName(string $className, DOMDocument $dom): \DOMNodeList
    {
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$className')]");
        return $nodes;
    }

    private function parseRatingString(String $ratingString): array
    {
        $ratingString = trim($ratingString);
        [$rating, $number] = explode(' ', $ratingString, 2);

        $number = str_replace(array('(', ')'), '', $number);
        $rating = count(array_keys(unpack("C*", $rating), '133'));
        return [
            'rating' => $rating,
            'number' => $number
        ];
    }

    private function parseJson(string $json, string $name): array
    {
        $decoded = json_decode($json);
        $parsedVariants = [];
        foreach ($decoded->products->variants as $number => $variant) {
            $parsedVariants[] =  [
                'name' => $name . "#" . $number,
                'price' => $variant->price,
                'price old' => $variant->price_old
            ];
        }
        return [
            'product code' => $decoded->products->code,
            'variants' => $parsedVariants
        ];
    }
}
