<h2>Sepetiniz</h2>

<?php if (empty($products)): ?>
    <p>Sepetiniz boş.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Ürün</th>
                <th>Adet</th>
                <th>Birim Fiyat</th>
                <th>Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                    <td><?php echo number_format($product['price'], 2); ?> TL</td>
                    <td><?php echo number_format($product['total'], 2); ?> TL</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>