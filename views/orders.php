<h2>Siparişleriniz</h2>

<?php if (empty($orders)): ?>
    <p>Henüz siparişiniz bulunmamaktadır.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Sipariş ID</th>
                <th>Ürünler</th>
                <th>Toplam Tutar</th>
                <th>Durum</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td>
                        <ul>
                            <?php foreach ($order['items'] as $item): ?>
                                <li><?php echo $item['product_name']; ?> (<?php echo $item['quantity']; ?> Adet)</li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td><?php echo number_format($order['total_price'], 2); ?> TL</td>
                    <td><?php echo ucfirst($order['status']); ?></td>
                    <td><?php echo $order['created_at']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>