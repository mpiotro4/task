<?php

declare(strict_types=1);

namespace App;

class EstoreScraper
{
    public function getProduct($id)
    {
        $html = HtmlProducer::getHtml('http://estoremedia.space/DataIT/product.php?id=' . $id);

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $wholeColumn = $this->getNodesByClassName('col-lg-9', $dom);
        $price = $this->getNodesByClassName('price', $dom);
        $pricePromo = $this->getNodesByClassName('price-promo', $dom);
        $priceOld = $this->getNodesByClassName('price-old', $dom);
        $img = $dom->getElementsByTagName('img')[0]->getAttribute('src');
        $json = $dom->getElementsByTagName('script')[1]->nodeValue;
        $ratingWithNumber = trim($dom->getElementsByTagName('small')[0]->nodeValue);


        echo '<br>';
        echo 'price: ' . $price[0]->nodeValue ?? '' . '<br>';
        echo '<br>';
        echo 'promo price: ';
        echo $pricePromo[0]->nodeValue ?? '' . '<br>';
        echo 'old price: ';
        echo $priceOld[0]->nodeValue ?? '' . '<br>';
        echo 'img url: ' . $img;
        echo '<br>';
        echo 'json: ' . $json;
        echo '<br>';
        echo 'rating: ' . $ratingWithNumber;
        // foreach ($nodes as $node) {
        //     echo "<br>";
        //     echo $node->nodeValue;
        //     echo "<br>";
        // }
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
