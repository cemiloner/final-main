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
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="card">
    <div class="card-body">
        <form id="product-form" action="/admin/products/store" method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="name">Ürün Adı:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="description">Açıklama:</label>
                <textarea id="description" name="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="price">Fiyat (TL):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($formData['price'] ?? '0.00'); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="category_id">Kategori:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Kategori Seçin --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category->id; ?>" <?php echo (isset($formData['category_id']) && $formData['category_id'] == $category->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="stock">Stok Adedi:</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($formData['stock'] ?? '0'); ?>">
                <small class="text-secondary">Boş bırakılırsa veya 0 girilirse stok takibi yapılmaz.</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="image">Ürün Fotoğrafı:</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif">
            </div>

            <a href="/admin/products" class="btn btn-secondary">İptal</a>
            <button type="submit" class="btn btn-success" style="margin-left: 10px;"><i class="fas fa-save"></i> Kaydet</button>
        </form>
    </div>
</div> 