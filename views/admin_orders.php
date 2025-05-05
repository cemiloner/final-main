<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<div id="update-message" class="message" style="display: none;">
    <!-- AJAX mesajları buraya gelecek -->
</div>

<div class="orders-table-container card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <p style="padding: 15px;">Gösterilecek sipariş bulunamadı.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Müşteri</th>
                        <th>Ürünler</th>
                        <th>Tutar</th>
                        <th>Zaman</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['id']; ?>">
                            <td data-label="ID"><?php echo $order['id']; ?></td>
                            <td data-label="Müşteri"><?php echo $order['customer_info']; // Sanitized in controller ?></td>
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
                            <td data-label="Durum" class="order-status">
                                <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </span>
                            </td>
                            <td data-label="İşlem">
                                <select class="status-select" data-order-id="<?php echo $order['id']; ?>">
                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Hazırlanıyor</option>
                                    <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Hazır</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Teslim Edildi</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>İptal Edildi</option>
                                </select>
                                <button class="btn btn-primary update-status-btn" data-order-id="<?php echo $order['id']; ?>"><i class="fas fa-sync-alt"></i> Güncelle</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 