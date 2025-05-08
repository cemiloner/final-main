<?php
// Flash mesajları göster
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    echo '<div class="message message-' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form id="add-category-form" action="/admin/categories/store" method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex-grow: 1;">
                <label for="category_name" style="margin-bottom: 0;">Yeni Kategori Ekle:</label>
                <input type="text" id="category_name" name="category_name" placeholder="Kategori Adı" required style="margin-bottom: 0;">
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Ekle</button>
        </form>
        <div id="category-message" class="message" style="display: none; margin-top: 10px;"></div>
    </div>
</div>

<?php // Mevcut Kategoriler Listesi ?>
<?php if (!empty($categories)): ?>
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h5>Mevcut Kategoriler</h5>
    </div>
    <div class="card-body" id="category-list-container">
        <ul class="category-list" style="list-style: none; padding: 0;">
            <?php foreach ($categories as $category): ?>
                <li data-category-id="<?php echo $category->id; ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--secondary-color);">
                    <span><?php echo htmlspecialchars($category->name); ?></span>
                    <button type="button" class="btn btn-danger btn-sm category-delete-btn" 
                            data-category-id="<?php echo $category->id; ?>" 
                            data-category-name="<?php echo htmlspecialchars($category->name); ?>" 
                            title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
         <div id="category-delete-message" class="message" style="display: none; margin-top: 10px;"></div>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    <a href="/admin/products/create" class="btn btn-success"><i class="fas fa-plus"></i> Yeni Ürün Ekle</a>
</div>

<div class="orders-table-container card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($products)): ?>
            <p style="padding: 15px;">Gösterilecek ürün bulunamadı.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fotoğraf</th>
                        <th>Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat (TL)</th>
                        <th>Stok</th>
                        <th>Açıklama</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr data-product-row-id="<?php echo $product->id; // JS ile satırı bulmak için ?>">
                            <td data-label="ID"><?php echo $product->id; ?></td>
                            <td data-label="Fotoğraf">
                                <?php if ($product->image_path): ?>
                                    <img src="<?php echo htmlspecialchars($product->image_path); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td data-label="Adı"><?php echo htmlspecialchars($product->name); ?></td>
                            <td data-label="Kategori"><?php echo $product->category ? htmlspecialchars($product->category->name) : '-'; ?></td>
                            <td data-label="Fiyat"><?php echo htmlspecialchars(number_format((float)$product->price, 2)); ?></td>
                            <td data-label="Stok"><?php echo isset($product->stock) ? $product->stock : 'Takip Yok'; ?></td>
                            <td data-label="Açıklama"><?php echo htmlspecialchars(substr($product->description, 0, 50)) . (strlen($product->description) > 50 ? '...' : ''); ?></td>
                            <td data-label="İşlemler">
                                <a href="/admin/products/edit?id=<?php echo $product->id; ?>" class="btn btn-primary btn-sm" title="Düzenle"><i class="fas fa-edit"></i></a>
                                <!-- Eski Form yerine AJAX Butonu -->
                                <button type="button" class="btn btn-danger btn-sm product-delete-btn" 
                                        data-product-id="<?php echo $product->id; ?>" 
                                        data-product-name="<?php echo htmlspecialchars($product->name); ?>" 
                                        title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php /* Silinecek Test İçeriği Başlangıcı 
<p>Admin Ürünler Sayfası Test İçeriği</p>

<?php 
if (isset($products)) {
    echo "<p>Toplam Ürün Sayısı: " . count($products) . "</p>";
    if (!empty($products)) {
        echo "<ul>";
        foreach ($products as $product) {
            echo "<li>ID: " . $product->id . " - Adı: " . htmlspecialchars($product->name) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Ürün verisi view'e gelmedi.</p>";
}
?> 
   Silinecek Test İçeriği Sonu */ ?> 