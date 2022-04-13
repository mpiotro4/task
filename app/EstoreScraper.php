<?php

declare(strict_types=1);

namespace App;

class EstoreScraper
{
    public function getProduct($id): array
    {
        $html = HtmlProducer::getHtml('http://estoremedia.space/DataIT/product.php?id=' . $id);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $finder = new \DomXPath($dom);

        $price = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/h5/span')[0]->nodeValue;
        $name = $finder->query('/html/body/div/div/div[2]/div[1]/h3')[0]->nodeValue;
        $priceOld = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/h5/del')[0]->nodeValue ?? null;
        $img = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/img')[0]->getAttribute('src');
        $json = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[1]/script')[0]->nodeValue;
        $ratingWithNumber = $finder->query('/html/body/div/div/div[2]/div[3]/div/div/div[2]/small')[0]->nodeValue;
        ['stars' => $stars, 'number' => $number] = $this->parseRatingString($ratingWithNumber);

        return [
            'price' => $price,
            'old price' => $priceOld,
            'img url' => $img,
            'stars' => $stars,
            'number' => $number,
            'hidden data' =>  $this->parseJson($json, $name)
        ];
    }

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
        $html = HtmlProducer::getHtml($url, $download);

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
        $html = Htmlproducer::getHtml($url);

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
        $ratingWithNumber = $dom->getElementsByTagName('small')[0]->nodeValue;
        ['stars' => $stars, 'number' => $number] = $this->parseRatingString($ratingWithNumber);
        return [
            'name' => $dom->getElementsByTagName('a')[1]->getAttribute('data-name'),
            'url' => $dom->getElementsByTagName('a')[1]->getAttribute('href'),
            'img' => $dom->getElementsByTagName('img')[0]->getAttribute('src'),
            'price' => $dom->getElementsByTagName('h5')[0]->nodeValue,
            'rating' => $stars,
            'number of ratings' => $number
        ];
    }

    private function getNodesByClassName($className, $dom): \DOMNodeList
    {
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$className')]");
        return $nodes;
    }

    private function parseRatingString(String $rating): array
    {
        $rating = trim($rating);
        [$stars, $number] = explode(' ', $rating, 2);

        $number = str_replace(array('(', ')'), '', $number);
        $stars = count(array_keys(unpack("C*", $stars), '133'));
        return [
            'stars' => $stars,
            'number' => $number
        ];
    }

    private function parseJson($json, $name)
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
