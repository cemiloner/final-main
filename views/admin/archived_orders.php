<?php 
/**
 * Admin - Arşivlenmiş Siparişler Görünümü
 */
?>
<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<p><a href="/admin/orders">Aktif Siparişlere Geri Dön</a></p>

<div class="orders-table-container card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <p style="padding: 15px;">Gösterilecek arşivlenmiş sipariş bulunamadı.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Müşteri</th>
                        <th>Masa</th>
                        <th>Ürünler</th>
                        <th>Tutar</th>
                        <th>Zaman</th>
                        <th>Son Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="ID"><?php echo $order['id']; ?></td>
                            <td data-label="Müşteri"><?php echo $order['customer_info']; // Sanitized in controller ?></td>
                            <td data-label="Masa"><?php echo $order['table_name'] ?? 'N/A'; ?></td>
                            <td data-label="Ürünler">
                                <?php if (!empty($order['items'])): ?>
                                    <ul>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li>
                                                <?php echo $item['product_name']; // Sanitized in controller ?> 
                                                (<?php echo $item['quantity']; ?> Adet) -
                                                <?php echo htmlspecialchars(number_format((float)$item['item_total'], 2)); ?> TL
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span>- Yok -</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Tutar"><strong><?php echo htmlspecialchars(number_format((float)$order['total_price'], 2)); ?> TL</strong></td>
                            <td data-label="Zaman"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td data-label="Son Durum" class="order-status">
                                <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 