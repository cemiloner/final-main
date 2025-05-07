<?php

use App\Core\BaseController; // Örnek, namespace kullanımına göre değişebilir

// Layout için başlık ayarla (opsiyonel, BaseController'da yapılabilir)
// $this->pageTitle = 'Menü';

// $activeTables MenuController tarafından gönderiliyor.
?>

<h2><?php echo htmlspecialchars($pageTitle ?? 'Menü'); ?></h2>

<div id="order-message" class="message" style="display: none;">
    <!-- AJAX mesajları buraya gelecek -->
</div>

<!-- Masa Seçim Alanı -->
<div id="table-selection-section" class="card mb-3">
    <div class="card-body">
        <h3 class="card-title"><i class="fas fa-chair"></i> Masa Seçimi</h3>
        <?php if (!empty($activeTables)): ?>
            <div class="form-group">
                <label for="selected_table_id">Lütfen Sipariş İçin Bir Masa Seçin:</label>
                <select id="selected_table_id" name="selected_table_id" class="form-control">
                    <option value="">-- Masa Seçiniz --</option>
                    <?php foreach ($activeTables as $table): ?>
                        <option value="<?php echo $table->id; ?>"><?php echo htmlspecialchars($table->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <small id="table-selection-error" class="error-text" style="display: none;">Lütfen bir masa seçin.</small>
            </div>
        <?php else: ?>
            <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Şu anda aktif masa bulunmamaktadır. Lütfen bir masa eklenmesini bekleyin veya yöneticiyle iletişime geçin.</p>
        <?php endif; ?>
    </div>
</div>
<!-- /Masa Seçim Alanı -->

<div id="menu-container" <?php echo empty($activeTables) ? 'style="display:none;"' : ''; ?> > <!-- Aktif masa yoksa menüyü gizle -->
    <?php if (!empty($categories) && !empty($productsByCategory)): ?>
        <?php foreach ($categories as $category): ?>
            <?php if (!empty($productsByCategory[$category->id])): ?>
                <div class="category-section card" id="category-<?php echo $category->id; ?>">
                    <div class="card-header">
                         <h3><?php echo htmlspecialchars($category->name); ?></h3>
                    </div>
                    <div class="card-body">
                         <div class="product-list"> <?php // Eski ul yerine div ?>
                              <?php foreach ($productsByCategory[$category->id] as $product): ?>
                                 <div class="product-item"> <?php // Eski li yerine div ?>
                                     <div class="product-details">
                                          <h4><?php echo htmlspecialchars($product->name); ?></h4>
                                          <p><?php echo htmlspecialchars($product->description); ?></p>
                                          <strong><?php echo htmlspecialchars(number_format((float)$product->price, 2)); ?> TL</strong>
                                          
                                          <?php 
                                          // Stok durumunu kontrol et ve göster
                                          $stock = $product->stock; // null olabilir (takip yok) veya sayısal (0 dahil)
                                          $hasStock = !isset($stock) || $stock > 0;
                                          $isStockTracked = isset($stock);
                                          ?>
                                          
                                          <?php if ($isStockTracked): // Sadece stok takibi varsa göster ?>
                                             <span class="stock-info <?php echo $hasStock ? 'in-stock' : 'out-of-stock'; ?>">
                                                 <?php echo $hasStock ? ("Stok: " . $stock . " Adet") : "Stokta Yok"; ?>
                                             </span>
                                          <?php endif; ?>
                                     </div>
 
                                     <?php if ($hasStock): // Stok varsa veya takip edilmiyorsa formu göster ?>
                                         <form class="order-form" data-product-id="<?php echo $product->id; ?>">
                                             <label for="quantity-<?php echo $product->id; ?>">Adet:</label>
                                             <input type="number" id="quantity-<?php echo $product->id; ?>" name="quantity" value="1" min="1" <?php echo ($isStockTracked && $stock > 0) ? 'max="' . $stock . '"' : ''; // Stok takibi varsa ve >0 ise max ekle ?> style="width: 60px;">
                                             <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart"></i> Ekle</button>
                                         </form>
                                     <?php else: // Stok yoksa mesaj göster ?>
                                         <div class="out-of-stock-message">
                                             <button type="button" class="btn btn-secondary btn-sm" disabled><i class="fas fa-times-circle"></i> Stokta Yok</button>
                                         </div>
                                     <?php endif; ?>
                                 </div> <?php // product-item div sonu ?>
                              <?php endforeach; ?>
                         </div> <?php // product-list div sonu ?>
                    </div> <?php // card-body div sonu ?>
                </div> <?php // category-section card div sonu ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Gösterilecek kategori veya ürün bulunamadı.</p>
    <?php endif; ?>
</div> <?php // menu-container div sonu ?> 