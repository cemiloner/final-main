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

<!-- Sağ Alt Köşe "Sepeti Görüntüle" Butonu -->
<button id="toggle-cart-btn" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; padding: 15px; background-color: #007bff; color: white; border: none; border-radius: 50%; width: 60px; height: 60px; font-size: 24px; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center;">
    🛒
</button>

<!-- Sepet Sidebar -->
<div id="cart-sidebar" style="position: fixed; top: 0; right: -350px; /* Başlangıçta gizli */ width: 350px; height: 100%; background-color: #f8f9fa; box-shadow: -2px 0 5px rgba(0,0,0,0.1); z-index: 1050; padding: 20px; transition: right 0.3s ease-in-out; display: flex; flex-direction: column; font-family: sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 15px;">
        <h3 style="margin: 0;">Siparişlerim</h3>
        <button id="close-cart-btn" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <div id="cart-items-list" style="flex-grow: 1; overflow-y: auto;">
        <!-- Sepet öğeleri buraya JavaScript ile eklenecek -->
        <p id="empty-cart-message" style="text-align: center; color: #6c757d; margin-top: 20px;">Sepetiniz şu anda boş.</p>
    </div>
    <div style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 15px;">
        <h4>Toplam: <span id="cart-total">0.00</span> TL</h4>
        <button id="checkout-btn" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top:10px;">
            Siparişi Tamamla (Geçici)
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleCartBtn = document.getElementById('toggle-cart-btn');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartItemsList = document.getElementById('cart-items-list');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const cartTotalSpan = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');


    let cart = []; // Sepet verilerini tutacak dizi

    function toggleSidebar() {
        if (cartSidebar.style.right === '-350px' || cartSidebar.style.right === '') {
            cartSidebar.style.right = '0px';
        } else {
            cartSidebar.style.right = '-350px';
        }
    }

    toggleCartBtn.addEventListener('click', toggleSidebar);
    closeCartBtn.addEventListener('click', toggleSidebar);

    function renderCart() {
        cartItemsList.innerHTML = ''; 
        let total = 0;

        if (cart.length === 0) {
            if(emptyCartMessage && !cartItemsList.contains(emptyCartMessage)) { // Sadece listede yoksa ekle
                 cartItemsList.appendChild(emptyCartMessage);
            }
        } else {
            if (emptyCartMessage && cartItemsList.contains(emptyCartMessage)) {
                 cartItemsList.removeChild(emptyCartMessage);
            }
            cart.forEach((item, index) => {
                const listItem = document.createElement('div');
                listItem.style.display = 'flex';
                listItem.style.justifyContent = 'space-between';
                listItem.style.alignItems = 'center';
                listItem.style.padding = '10px 0';
                listItem.style.borderBottom = '1px solid #eee';
                listItem.dataset.itemId = item.id; // Eşleşme için ID

                const itemInfo = document.createElement('div');
                itemInfo.style.flexGrow = '1';

                const itemName = document.createElement('span');
                itemName.textContent = item.name;
                itemName.style.display = 'block'; // Alt alta gelmesi için
                
                const itemPriceDetails = document.createElement('small');
                itemPriceDetails.textContent = `Adet: ${item.quantity} x ${item.price.toFixed(2)} TL`;
                itemPriceDetails.style.color = '#6c757d';

                itemInfo.appendChild(itemName);
                itemInfo.appendChild(itemPriceDetails);

                const itemSubtotal = document.createElement('span');
                itemSubtotal.textContent = `${(item.price * item.quantity).toFixed(2)} TL`;
                itemSubtotal.style.minWidth = '60px';
                itemSubtotal.style.textAlign = 'right';
                itemSubtotal.style.margin = '0 10px';
                
                const controlsDiv = document.createElement('div');
                controlsDiv.style.display = 'flex';
                controlsDiv.style.alignItems = 'center';

                const removeBtn = document.createElement('button');
                removeBtn.textContent = '−'; // Daha kibar eksi
                removeBtn.style.backgroundColor = '#ffc107';
                removeBtn.style.color = 'black';
                removeBtn.style.border = 'none';
                removeBtn.style.borderRadius = '4px';
                removeBtn.style.padding = '3px 8px';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.marginRight = '5px';
                removeBtn.addEventListener('click', () => updateItemQuantity(item.id, item.quantity - 1));

                const addBtn = document.createElement('button');
                addBtn.textContent = '+';
                addBtn.style.backgroundColor = '#28a745';
                addBtn.style.color = 'white';
                addBtn.style.border = 'none';
                addBtn.style.borderRadius = '4px';
                addBtn.style.padding = '3px 8px';
                addBtn.style.cursor = 'pointer';
                addBtn.addEventListener('click', () => updateItemQuantity(item.id, item.quantity + 1));
                
                controlsDiv.appendChild(removeBtn);
                controlsDiv.appendChild(addBtn);

                listItem.appendChild(itemInfo);
                listItem.appendChild(itemSubtotal);
                listItem.appendChild(controlsDiv);
                cartItemsList.appendChild(listItem);
                total += item.price * item.quantity;
            });
        }
        cartTotalSpan.textContent = total.toFixed(2);
        checkoutBtn.disabled = cart.length === 0; // Sepet boşsa butonu devre dışı bırak
        checkoutBtn.style.opacity = cart.length === 0 ? 0.7 : 1;
        checkoutBtn.style.cursor = cart.length === 0 ? 'not-allowed' : 'pointer';
    }

    function updateItemQuantity(productId, newQuantity) {
        const itemIndex = cart.findIndex(item => item.id === productId);
        if (itemIndex > -1) {
            if (newQuantity <= 0) {
                cart.splice(itemIndex, 1); // Miktar 0 veya altına düşerse ürünü sil
            } else {
                cart[itemIndex].quantity = newQuantity;
            }
            renderCart();
        }
    }
    
    document.querySelectorAll('form.order-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); 

            const productId = this.dataset.productId;
            const productItemElement = this.closest('.product-item');

            if (!productItemElement) {
                console.error('Product item element not found for form:', this);
                return;
            }

            const productNameElement = productItemElement.querySelector('.product-details h4');
            const productPriceElement = productItemElement.querySelector('.product-details strong');
            
            const productName = productNameElement ? productNameElement.textContent.trim() : 'Bilinmeyen Ürün';
            const productPriceText = productPriceElement ? productPriceElement.textContent.replace(/\s*TL/g, '').replace(/,/g, '.').trim() : '0'; // Virgülü noktaya çevir ve boşlukları temizle
            const productPrice = parseFloat(productPriceText);
            
            const quantityInput = this.querySelector('input[name="quantity"]');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

            if (!productId) {
                console.error('Product ID not found for form:', this);
                return;
            }
            if (isNaN(productPrice)) {
                console.error('Product price is not a number for:', productName, '; Extracted text:', productPriceText);
                return;
            }
            if (isNaN(quantity) || quantity < 1) {
                console.error('Invalid quantity for:', productName, quantityInput ? quantityInput.value : 'N/A');
                return;
            }

            const existingItemIndex = cart.findIndex(item => item.id === productId);
            if (existingItemIndex > -1) {
                cart[existingItemIndex].quantity += quantity;
            } else {
                cart.push({ 
                    id: productId, 
                    name: productName, 
                    price: productPrice, 
                    quantity: quantity 
                });
            }
            
            renderCart();
            if (cartSidebar.style.right === '-350px' || cartSidebar.style.right === '') {
                 toggleSidebar();
            }
             if (quantityInput) quantityInput.value = '1'; // Adet inputunu sıfırla
        });
    });

    checkoutBtn.addEventListener('click', async function() { 
        if (cart.length === 0) {
            alert('Sepetiniz boş!');
            return;
        }

        const selectedTableId = document.getElementById('selected_table_id').value;
        if (!selectedTableId) {
            alert('Lütfen sipariş için bir masa seçin!');
            document.getElementById('table-selection-error').style.display = 'block';
            document.getElementById('selected_table_id').focus();
            return;
        } else {
            document.getElementById('table-selection-error').style.display = 'none';
        }

        this.disabled = true;
        this.textContent = 'Sipariş İşleniyor...';
        let allSuccessful = true;
        let errors = [];
        const originalCartState = JSON.parse(JSON.stringify(cart)); // Hata durumunda geri yüklemek için
        let processedItems = 0;

        for (const item of cart) {
            try {
                const response = await fetch('/order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: parseInt(item.id),
                        quantity: item.quantity,
                        table_id: parseInt(selectedTableId)
                    })
                });

                const result = await response.json();
                processedItems++;

                if (!response.ok || !result.success) {
                    allSuccessful = false;
                    errors.push(`- ${item.name} (Adet: ${item.quantity}): ${result.message || 'Bilinmeyen bir hata oluştu.'}`);
                    console.error(`Sipariş hatası (${item.name}):`, result);
                } else {
                    console.log(`${item.name} başarıyla sipariş edildi:`, result);
                }
            } catch (error) {
                processedItems++;
                allSuccessful = false;
                errors.push(`- ${item.name} (Adet: ${item.quantity}): Sunucuya ulaşılamadı veya bir ağ hatası oluştu.`);
                console.error(`Fetch hatası (${item.name}):`, error);
            }
            // Her istekten sonra kısa bir bekleme, sunucuyu yormamak için (opsiyonel)
            // await new Promise(resolve => setTimeout(resolve, 100)); 
        }

        this.disabled = false;
        this.textContent = 'Siparişi Tamamla'; // Buton metnini güncelledim

        if (allSuccessful) {
            alert('Tüm ürünler başarıyla sipariş edildi!');
            cart = []; 
            renderCart();
            if (cartSidebar.style.right === '0px') {
                toggleSidebar(); 
            }
        } else {
            let errorMsg = 'Sipariş sırasında bazı hatalar oluştu:\n\n' + errors.join('\n');
            if (processedItems !== originalCartState.length) {
                errorMsg += '\n\nNot: Tüm ürünler işlenememiş olabilir. Lütfen sepeti kontrol edin.';
            }
            alert(errorMsg);
            // Hata durumunda sepeti eski haline getirmek yerine, kullanıcıya durumu bildirip
            // başarılı olanları sepetten kaldırma veya kullanıcıya seçim bırakma daha iyi olabilir.
            // Şimdilik sepeti olduğu gibi bırakıyoruz ki kullanıcı neyin hatalı olduğunu görsün.
            // Eğer istenirse, sadece başarılı olanlar sepetten çıkarılabilir.
        }
    });
    
    renderCart(); // Sayfa yüklendiğinde sepeti ilk kez render et
});
</script> 