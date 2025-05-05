<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<div id="product-message" class="message" style="display: none;"></div>

<?php
// Form hatalarını göster
if (isset($_SESSION['form_errors'])) {
    echo '<div class="message message-error"><ul>';
    foreach ($_SESSION['form_errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
    unset($_SESSION['form_errors']);
}
// Varsa, önceki form verilerini al (başarısız gönderim sonrası için)
// Yoksa, düzenlenecek ürünün verilerini kullan
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$productName = $formData['name'] ?? $product->name;
$productDesc = $formData['description'] ?? $product->description;
$productPrice = $formData['price'] ?? $product->price;
$productCatId = $formData['category_id'] ?? $product->category_id;
$productStock = $formData['stock'] ?? $product->stock ?? 0;
$productImage = $product->image_path ?? null;
?>

<div class="card">
    <div class="card-body">
        <form id="product-form" action="/admin/products/update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="name">Ürün Adı:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($productName); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="description">Açıklama:</label>
                <textarea id="description" name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($productDesc); ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="price">Fiyat (TL):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars(number_format((float)$productPrice, 2)); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="category_id">Kategori:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Kategori Seçin --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category->id; ?>" <?php echo ($productCatId == $category->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="stock">Stok Adedi:</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($productStock); ?>">
                <small class="text-secondary">0 girilirse stok takibi yapılmaz.</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="image">Ürün Fotoğrafı:</label>
                <?php if ($productImage): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo htmlspecialchars($productImage); ?>" alt="Mevcut Resim" style="max-width: 100px; max-height: 100px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <small style="display: block;">Mevcut resim. Değiştirmek için yeni dosya seçin.</small>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif">
            </div>

            <a href="/admin/products" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-success" style="margin-left: 10px;"><i class="fas fa-save"></i> Güncelle</button>
        </form>
    </div>
</div> 