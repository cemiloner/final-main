<?php 
/**
 * Admin - Aktif Siparişler Görünümü
 */
?>
<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<div id="update-message" class="message" style="display: none;">
    <!-- AJAX mesajları buraya gelecek (genel durum güncellemeleri) -->
</div>

<div id="admin-orders-message" class="message" style="display: none; margin-bottom: 15px;">
    <!-- Tablo güncelleme mesajları buraya gelecek -->
</div>

<p><a href="/admin/orders/archived">Arşivlenmiş Siparişleri Görüntüle</a></p>

<div class="orders-table-container card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h5>Aktif Siparişler</h5>
        <i class="fas fa-sync fa-spin" id="admin-orders-loading-indicator" style="display: none; font-size: 1.2em;"></i>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <p style="padding: 15px;">Gösterilecek aktif sipariş bulunamadı.</p>
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
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody id="admin-orders-table-body">
                    <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['id']; ?>">
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
                            <td data-label="Durum" class="order-status">
                                <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </span>
                            </td>
                            <td data-label="İşlem" class="order-actions">
                                <?php $status = $order['status']; ?>
                                <?php if ($status === 'bekliyor'): ?>
                                    <button class="btn btn-sm btn-success action-btn" data-order-id="<?php echo $order['id']; ?>" data-next-status="preparing">
                                        <i class="fas fa-check"></i> Kabul Et
                                    </button>
                                <?php endif; ?>
                                <?php if ($status === 'preparing'): ?>
                                    <button class="btn btn-sm btn-info action-btn" data-order-id="<?php echo $order['id']; ?>" data-next-status="ready">
                                        <i class="fas fa-box-open"></i> Hazır
                                    </button>
                                <?php endif; ?>
                                <?php if ($status === 'ready'): ?>
                                    <button class="btn btn-sm btn-primary action-btn" data-order-id="<?php echo $order['id']; ?>" data-next-status="delivered">
                                        <i class="fas fa-truck"></i> Teslim Edildi
                                    </button>
                                <?php endif; ?>
                                
                                <?php // İptal butonu aktif durumlar için (bekliyor, preparing, ready) ?>
                                <?php if (in_array($status, ['bekliyor', 'preparing', 'ready'])): ?>
                                     <button class="btn btn-sm btn-danger action-btn" data-order-id="<?php echo $order['id']; ?>" data-next-status="cancelled">
                                        <i class="fas fa-times"></i> İptal Et
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 