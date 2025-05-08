<?php
// Admin Dashboard View
?>
<h2><?php echo htmlspecialchars($pageTitle ?? 'Yönetim Paneli'); ?></h2>

<div class="dashboard-container card">
    <div class="card-body">
        <p>Yönetim paneline hoş geldiniz.</p>
        <div class="dashboard-links">
            <a href="/admin/orders" class="btn btn-primary"><i class="fas fa-list-alt"></i> Aktif Siparişler</a>
            <a href="/admin/orders/archived" class="btn btn-secondary"><i class="fas fa-archive"></i> Arşivlenmiş Siparişler</a>
            <a href="/admin/products" class="btn btn-info"><i class="fas fa-box"></i> Ürün Yönetimi</a>
            <a href="/admin/tables" class="btn btn-info"><i class="fas fa-chair"></i> Masa Yönetimi</a>
            <a href="/admin/feedback" class="btn btn-info"><i class="fas fa-comments"></i> Geri Bildirimler</a>
            <a href="/logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>

        <hr style="margin: 30px 0;">

        <!-- Aktif Siparişler Bölümü -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h5><i class="fas fa-sync fa-spin" id="loading-indicator" style="display: none; margin-right: 8px;"></i> Aktif Siparişler (Otomatik Güncellenir)</h5>
            </div>
            <div class="card-body" style="padding: 0;">
                <div id="active-orders-message" class="message" style="display: none; margin: 15px;"></div>
                <div class="table-responsive"> <!-- Küçük ekranlar için kaydırma -->
                    <table class="orders-table" style="width: 100%; margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Müşteri / Masa</th>
                                <th>Ürünler</th>
                                <th>Tutar</th>
                                <th>Zaman</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="active-orders-tbody">
                            <!-- JavaScript burayı dolduracak -->
                            <tr><td colspan="7" style="text-align: center; padding: 20px;">Aktif siparişler yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Aktif Siparişler Bölümü -->

        <hr style="margin: 30px 0;">

        <div>
            <h3>Gün Sonu İşlemi</h3>
            <?php if ($activeOrderCount > 0): ?>
                 <p style="color: var(--color-warning); font-weight: bold;">
                    <i class="fas fa-exclamation-triangle"></i> Aktif sipariş (<?php echo $activeOrderCount; ?> adet) bulunmaktadır. Günü bitirmeden önce tüm siparişlerin 'Teslim Edildi' veya 'İptal Edildi' durumunda olması gerekir.
                </p>
                 <form id="end-of-day-form" style="margin-top: 15px;"> <?php // Action ve method gereksiz ?>
                    <button type="button" class="btn btn-warning" disabled><i class="fas fa-calendar-check"></i> Günü Bitir ve Raporu İndir</button>
                </form>
            <?php else: ?>
                <p style="color: var(--error-color); font-weight: bold;">
                    UYARI: Bu işlem, tüm sipariş kayıtlarını (teslim edilen ve iptal edilen) içeren bir raporu indirmenizi sağlar ve ardından TÜM siparişleri sistemden SİLER. Bu işlem geri alınamaz.
                </p>
                <form id="end-of-day-form" action="/admin/end-of-day" method="POST" style="margin-top: 15px;">
                     <?php // CSRF token eklemek iyi bir pratik olurdu, şimdilik atlıyoruz ?>
                     <button type="submit" class="btn btn-warning"><i class="fas fa-calendar-check"></i> Günü Bitir ve Raporu İndir</button>
                </form>
             <?php endif; ?>
        </div>

    </div>
</div> 