<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>IMG URL</th>
                <th>Price</th>
                <th>Rating</th>
                <th>Number of Ratings</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)) : ?>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td> <a href="<?= 'product.php?id' . strstr($product['url'], '=', false) ?>"><?= $product['name'] ?> </a></td>
                        <td><?= $product['url'] ?></td>
                        <td><?= $product['img'] ?></td>
                        <td><?= $product['price'] ?></td>
                        <td><?= $product['rating'] ?></td>
                        <td><?= $product['number of ratings'] ?></td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>
</body>

</html>