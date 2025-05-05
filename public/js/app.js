document.addEventListener('DOMContentLoaded', function() {
    console.log('Lokanta Sipariş JS Yüklendi - v2');

    const updateMessageDiv = document.getElementById('update-message');
    const orderTableBody = document.querySelector('.orders-table tbody');

    // --- Sipariş Verme İşlemi (Müşteri Tarafı - Değişiklik Yok) --- //
    const orderForms = document.querySelectorAll('.order-form');
    const orderMessageDiv = document.getElementById('order-message');

    orderForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showMessage(orderMessageDiv, '', '');
            const productId = this.dataset.productId;
            const quantityInput = this.querySelector('input[name="quantity"]');
            const quantity = quantityInput ? quantityInput.value : 1;
            const button = this.querySelector('button');
            const originalButtonText = button.textContent;
            button.disabled = true;
            button.textContent = 'İşleniyor...';

            fetch('/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(orderMessageDiv, `Siparişiniz alındı: ${data.message}`, 'success');
                    if (quantityInput) quantityInput.value = 1;
                } else {
                    showMessage(orderMessageDiv, `Hata: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Sipariş verme hatası:', error);
                showMessage(orderMessageDiv, 'Bir sunucu hatası oluştu. Lütfen tekrar deneyin.', 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalButtonText;
            });
        });
    });

    // --- Admin Panel - Yeni Durum Güncelleme (Action Butonları) --- //
    if (orderTableBody) {
        orderTableBody.addEventListener('click', function(e) {
            // Sadece .action-btn sınıfına sahip butonlara tıklandığında çalış
            if (!e.target.matches('.action-btn')) {
                // Eğer ikon (<i>) tıklandıysa, parent butonu hedef al
                if (e.target.closest('.action-btn')) {
                    handleStatusUpdate(e.target.closest('.action-btn'));
                }
                return; // Buton veya içindeki ikon değilse çık
            }
            handleStatusUpdate(e.target); // Butona tıklandıysa doğrudan işle
        });
    }

    function handleStatusUpdate(button) {
        showMessage(updateMessageDiv, '', ''); // Önceki mesajı temizle

        const orderId = button.dataset.orderId;
        const nextStatus = button.dataset.nextStatus;
        const row = button.closest('tr'); // İlgili satırı bul
        const currentButtons = row.querySelectorAll('.action-btn');

        // Butonları geçici olarak devre dışı bırak ve loading göster
        currentButtons.forEach(btn => btn.disabled = true);
        const originalButtonHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('/admin/orders/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_id: orderId,
                status: nextStatus // Controller'a hedef durumu gönderiyoruz
            })
        })
        .then(response => {
            if (!response.ok) {
                // HTTP hata durumu varsa JSON'u okumayı dene
                return response.json().then(errData => {
                    throw new Error(errData.message || `HTTP Error ${response.status}`);
                }).catch(() => {
                    // JSON okunamadıysa genel hata fırlat
                    throw new Error(`HTTP Error ${response.status}`);
                });
            }
            return response.json(); // Başarılı yanıtı işle
        })
        .then(data => {
            if (data.success) {
                showMessage(updateMessageDiv, `Sipariş #${orderId}: ${data.message}`, 'success');

                // Eğer sipariş arşivlendiyse (teslim edildi veya iptal edildi)
                if (data.is_archived) {
                    // Satırı tablodan kaldır (animasyonla daha şık olabilir)
                     row.style.transition = 'opacity 0.5s ease-out';
                     row.style.opacity = '0';
                     setTimeout(() => row.remove(), 500);
                } else {
                    // Durum arşivlenmediyse, UI'ı güncelle
                    updateOrderRowUI(row, data.new_status);
                    // Başarılı işlem sonrası butonları tekrar aktif et (hata durumunda da aktifleşecek)
                    // row.querySelectorAll('.action-btn').forEach(btn => btn.disabled = false);
                    // button.innerHTML = originalButtonHTML; // Bunu finally'ye taşıyalım
                }
            } else {
                // Controller'dan { success: false, message: '...' } geldiyse
                showMessage(updateMessageDiv, `Hata: ${data.message}`, 'error');
                // Hata durumunda butonları tekrar aktif et
                // currentButtons.forEach(btn => btn.disabled = false);
                // button.innerHTML = originalButtonHTML; // Bunu finally'ye taşıyalım
            }
        })
        .catch(error => {
            console.error('Durum güncelleme hatası:', error);
            showMessage(updateMessageDiv, `Hata: ${error.message || 'Bir sunucu hatası oluştu.'}`, 'error');
             // Hata durumunda butonları tekrar aktif et
             // currentButtons.forEach(btn => btn.disabled = false);
             // button.innerHTML = originalButtonHTML; // Bunu finally'ye taşıyalım
        })
        .finally(() => {
             // İşlem bitince (başarılı veya hatalı), hala DOM'da olan butonları aktif et
             // row.querySelectorAll('.action-btn').forEach(btn => btn.disabled = false);
             // Tıklanan butonu eski haline getir (eğer satır silinmediyse)
             if (document.body.contains(button)) {
                 // Butonları genel olarak enable etmeyelim, sadece UI güncellenince yenileri aktif olur.
                 // Sadece tıklanan butonu eski haline getirelim, eğer bir hata oluştuysa.
                 const rowStillExists = document.body.contains(row);
                 if (rowStillExists && !row.style.opacity) { // Satır silinmiyorsa ve başarılı değilse
                    currentButtons.forEach(btn => btn.disabled = false); // Hata durumunda tümünü aktif et
                    button.innerHTML = originalButtonHTML;
                 } 
             }
        });
    }

    // Sipariş satırının durumunu ve butonlarını güncelleyen yardımcı fonksiyon
    function updateOrderRowUI(row, newStatus) {
        const statusBadge = row.querySelector('.status-badge');
        const actionsCell = row.querySelector('.order-actions');
        const orderId = row.dataset.orderId;

        // 1. Durum rozetini güncelle
        if (statusBadge) {
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            statusBadge.className = `status-badge status-${newStatus}`; // Sınıfları tamamen değiştir
        }

        // 2. Aksiyon butonlarını temizle ve yenilerini ekle
        if (actionsCell) {
            actionsCell.innerHTML = ''; // Mevcut butonları temizle
            let buttonsHTML = '';

            if (newStatus === 'bekliyor') { // Gerçi buraya bekliyor gelmemeli ama yine de
                 buttonsHTML += ` <button class="btn btn-sm btn-success action-btn" data-order-id="${orderId}" data-next-status="preparing"><i class="fas fa-check"></i> Kabul Et</button>`;
            }
            if (newStatus === 'preparing') {
                 buttonsHTML += ` <button class="btn btn-sm btn-info action-btn" data-order-id="${orderId}" data-next-status="ready"><i class="fas fa-box-open"></i> Hazır</button>`;
            }
            if (newStatus === 'ready') {
                 buttonsHTML += ` <button class="btn btn-sm btn-primary action-btn" data-order-id="${orderId}" data-next-status="delivered"><i class="fas fa-truck"></i> Teslim Edildi</button>`;
            }
           
            // İptal butonu (aktif durumlar için)
            if (['bekliyor', 'preparing', 'ready'].includes(newStatus)) {
                 buttonsHTML += ` <button class="btn btn-sm btn-danger action-btn" data-order-id="${orderId}" data-next-status="cancelled"><i class="fas fa-times"></i> İptal Et</button>`;
            }
            
            actionsCell.innerHTML = buttonsHTML.trim(); // Yeni butonları ekle
        }
    }

    // --- Yardımcı Mesaj Gösterme Fonksiyonu --- //
    function showMessage(element, message, type) {
        if (!element) return;
        element.textContent = message;
        element.className = 'message'; // Önceki sınıfları temizle
        if (message) {
            element.classList.add(type === 'success' ? 'message-success' : 'message-error');
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
        }
    }

    // --- Admin Kategori Ekleme (Değişiklik Yok) --- //
    const categoryForm = document.getElementById('add-category-form');
    const categoryMessageDiv = document.getElementById('category-message');

    if (categoryForm && categoryMessageDiv) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showMessage(categoryMessageDiv, '', ''); // Clear previous message
            const categoryNameInput = this.querySelector('#category_name');
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ekleniyor...';

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(new FormData(this)).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(categoryMessageDiv, data.message, 'success');
                    categoryNameInput.value = ''; // Input'u temizle
                    // TODO: Product formlarındaki kategori dropdown'larını güncelle (opsiyonel)
                } else {
                    showMessage(categoryMessageDiv, data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Kategori ekleme hatası:', error);
                showMessage(categoryMessageDiv, 'Bir sunucu hatası oluştu.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // --- Admin Ürün Ekleme/Güncelleme (Değişiklik Yok) --- //
    const productForm = document.getElementById('product-form');
    const productMessageDiv = document.getElementById('product-message');

    if (productForm && productMessageDiv) {
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showMessage(productMessageDiv, '', '');
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;

                if (!response.ok) {
                    const error = (data && data.message) || response.statusText;
                    let errorsList = '';
                    if(data && data.errors && Array.isArray(data.errors)) {
                         errorsList = '<ul>' + data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                    }
                    throw new Error(error + errorsList);
                }
                return data;
            })
            .then(data => {
                if (data && data.success) {
                    showMessage(productMessageDiv, data.message, 'success');
                    if (this.action.includes('/store')) { 
                        this.reset();
                    }
                } else {
                    let errMsg = (data && data.message) ? data.message : 'Beklenmeyen sunucu yanıtı veya işlem başarısız.';
                    if(data && data.errors && Array.isArray(data.errors)) {
                         errMsg += '<ul>' + data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                    }
                    showMessage(productMessageDiv, errMsg, 'error');
                }
            })
            .catch(error => {
                console.error('Ürün kaydetme hatası:', error);
                showMessage(productMessageDiv, error.message || 'Bir sunucu hatası oluştu.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // --- Admin Ürün Silme --- //
    const productTableBody = document.querySelector('.orders-table tbody'); // Ürün tablosu için de aynı selectör kullanılabilir
    const productListMessageDiv = document.getElementById('product-message') || document.getElementById('update-message'); // Mesaj alanı ortak olabilir veya ayrı bir ID verilebilir

    if (productTableBody) {
        productTableBody.addEventListener('click', function(e) {
            let deleteButton;
            // Tıklanan element buton mu veya içindeki ikon mu?
            if (e.target.matches('button.product-delete-btn')) {
                deleteButton = e.target;
            } else if (e.target.closest('button.product-delete-btn')) {
                deleteButton = e.target.closest('button.product-delete-btn');
            }

            if (!deleteButton) {
                return; // Silme butonu değilse çık
            }

            const productId = deleteButton.dataset.productId;
            const productName = deleteButton.dataset.productName || 'Bu ürün'; // İsim yoksa genel ifade
            const row = deleteButton.closest('tr');

            // Kullanıcıdan onay al
            if (!confirm(`'${productName}' ürününü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`)) {
                return; // Kullanıcı iptal etti
            }

            // Mesaj alanını temizle
            if(productListMessageDiv) showMessage(productListMessageDiv, '', '');

            // Butonu geçici olarak devre dışı bırak
            const originalButtonHTML = deleteButton.innerHTML;
            deleteButton.disabled = true;
            deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // AJAX isteği gönder
            fetch('/admin/products/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', // Form verisi gibi gönderiyoruz
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}` // product_id'yi body'de gönder
            })
            .then(response => {
                // Hata durumunda da JSON dönebiliriz (Controller'da tanımladık)
                 return response.json().then(data => ({ ok: response.ok, status: response.status, data }));
            })
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    // Başarılı silme
                    if(productListMessageDiv) showMessage(productListMessageDiv, data.message, 'success');
                    // Satırı tablodan kaldır
                     if (row) {
                         row.style.transition = 'opacity 0.5s ease-out';
                         row.style.opacity = '0';
                         setTimeout(() => row.remove(), 500);
                     }
                } else {
                    // Hata durumu (API'den veya HTTP'den)
                    const errorMessage = data?.message || `Ürün silinirken bir hata oluştu (HTTP ${status}).`;
                    if(productListMessageDiv) showMessage(productListMessageDiv, errorMessage, 'error');
                    // Butonu tekrar aktif et
                    deleteButton.disabled = false;
                    deleteButton.innerHTML = originalButtonHTML;
                }
            })
            .catch(error => {
                console.error('Ürün silme AJAX hatası:', error);
                 if(productListMessageDiv) showMessage(productListMessageDiv, 'İstek gönderilirken bir ağ hatası oluştu.', 'error');
                 // Butonu tekrar aktif et
                 deleteButton.disabled = false;
                 deleteButton.innerHTML = originalButtonHTML;
            });
        });
    }

    // --- Admin Gün Sonu İşlemi Onayı --- //
    const endOfDayForm = document.getElementById('end-of-day-form');
    if (endOfDayForm) {
        endOfDayForm.addEventListener('submit', function(e) {
            const confirmation = confirm(
                'GÜN SONU UYARISI!\n\n' +
                'Bu işlem tüm teslim edilmiş ve iptal edilmiş siparişleri bir rapor dosyasına kaydedip indirecek, ' +
                'ardından TÜM sipariş kayıtlarını (order ve orderitem) veritabanından SİLECEKTİR.\n\n' +
                'Bu işlem geri alınamaz! Emin misiniz?'
            );
            
            if (!confirmation) {
                e.preventDefault(); // Form gönderimini engelle
                 // Opsiyonel: kullanıcıya iptal edildiği bilgisini verebiliriz
                 const infoArea = document.createElement('p');
                 infoArea.textContent = 'Gün sonu işlemi iptal edildi.';
                 infoArea.style.marginTop = '10px';
                 infoArea.style.color = 'var(--text-secondary)';
                 // Eski info mesajını sil (varsa)
                 const oldInfo = this.querySelector('.cancel-info');
                 if(oldInfo) oldInfo.remove();
                 infoArea.classList.add('cancel-info');
                 this.appendChild(infoArea);
                 setTimeout(() => infoArea.remove(), 4000); // Mesajı 4sn sonra kaldır
            }
            // Kullanıcı onaylarsa form normal şekilde submit edilir.
        });
    }

    // --- Admin Kategori Silme --- //
    const categoryListContainer = document.getElementById('category-list-container');
    const categoryDeleteMessageDiv = document.getElementById('category-delete-message');

    if (categoryListContainer && categoryDeleteMessageDiv) {
        categoryListContainer.addEventListener('click', function(e) {
            let deleteButton;
            // Tıklanan element buton mu veya içindeki ikon mu?
            if (e.target.matches('button.category-delete-btn')) {
                deleteButton = e.target;
            } else if (e.target.closest('button.category-delete-btn')) {
                deleteButton = e.target.closest('button.category-delete-btn');
            }

            if (!deleteButton) {
                return; // Silme butonu değilse çık
            }

            const categoryId = deleteButton.dataset.categoryId;
            const categoryName = deleteButton.dataset.categoryName || 'Bu kategori';
            const listItem = deleteButton.closest('li[data-category-id]');

            // Onay al
            if (!confirm(`'${categoryName}' kategorisini silmek istediğinizden emin misiniz? Bu kategoriye atanmış ürün varsa silme işlemi başarısız olacaktır.`)) {
                return; // Kullanıcı iptal etti
            }

            // Mesajı temizle
            showMessage(categoryDeleteMessageDiv, '', '');

            // Butonu geçici olarak devre dışı bırak
            const originalButtonHTML = deleteButton.innerHTML;
            deleteButton.disabled = true;
            deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

             // AJAX isteği gönder
            fetch('/admin/categories/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `category_id=${encodeURIComponent(categoryId)}`
            })
            .then(response => {
                 return response.json().then(data => ({ ok: response.ok, status: response.status, data }));
            })
            .then(({ ok, status, data }) => {
                 if (ok && data.success) {
                    // Başarılı silme
                    showMessage(categoryDeleteMessageDiv, data.message, 'success');
                    // Listeden kaldır
                     if (listItem) {
                         listItem.style.transition = 'opacity 0.5s ease-out';
                         listItem.style.opacity = '0';
                         setTimeout(() => listItem.remove(), 500);
                         // Eğer liste boşaldıysa, container'ı gizleyebiliriz (opsiyonel)
                         if (!categoryListContainer.querySelector('li')) {
                             // categoryListContainer.closest('.card').style.display = 'none';
                         }
                     }
                } else {
                    // Hata durumu
                    const errorMessage = data?.message || `Kategori silinirken bir hata oluştu (HTTP ${status}).`;
                    showMessage(categoryDeleteMessageDiv, errorMessage, 'error');
                    // Butonu tekrar aktif et
                    deleteButton.disabled = false;
                    deleteButton.innerHTML = originalButtonHTML;
                }
            })
             .catch(error => {
                console.error('Kategori silme AJAX hatası:', error);
                 showMessage(categoryDeleteMessageDiv, 'İstek gönderilirken bir ağ hatası oluştu.', 'error');
                 // Butonu tekrar aktif et
                 deleteButton.disabled = false;
                 deleteButton.innerHTML = originalButtonHTML;
            });
        });
    }

}); // End of DOMContentLoaded


// --- Yeni Sipariş Satırı Oluşturma Yardımcı Fonksiyonu (GLOBAL SCOPE) --- //
function createOrderRowHTML(order) {
    // order objesi prepareOrdersData'dan gelen formatta olmalı
    let itemsHTML = '(Ürün bilgisi yok)';
    if (order.items && order.items.length > 0) {
         itemsHTML = '<ul>';
         order.items.forEach(item => {
             // Basic escaping for product name - replace with a more robust one if needed
             const productName = String(item.product_name || '').replace(/[&<>"'`]/g, char => ({'&' : '&amp;', '<' : '&lt;', '>' : '&gt;', '"' : '&quot;', "'" : '&#39;', '`' : '&#96;'}[char]));
             itemsHTML += `<li>${productName} (${item.quantity} Adet) - ${parseFloat(item.item_total).toFixed(2)} TL</li>`;
         });
         itemsHTML += '</ul>';
    }

    let actionsHTML = '';
    if (order.status === 'bekliyor') {
         actionsHTML += `<button class="btn btn-sm btn-success action-btn" data-order-id="${order.id}" data-next-status="preparing"><i class="fas fa-check"></i> Kabul Et</button>`;
         actionsHTML += ` <button class="btn btn-sm btn-danger action-btn" data-order-id="${order.id}" data-next-status="cancelled"><i class="fas fa-times"></i> İptal Et</button>`;
    } 

    // Basic escaping for other fields
    const orderId = order.id; // Assumed safe (integer)
    const customerInfo = String(order.customer_info || '').replace(/[&<>"'`]/g, char => ({'&' : '&amp;', '<' : '&lt;', '>' : '&gt;', '"' : '&quot;', "'" : '&#39;', '`' : '&#96;'}[char]));
    const totalPrice = parseFloat(order.total_price).toFixed(2);
    const createdAt = String(order.created_at || '').replace(/[&<>"'`]/g, char => ({'&' : '&amp;', '<' : '&lt;', '>' : '&gt;', '"' : '&quot;', "'" : '&#39;', '`' : '&#96;'}[char])); // Basic date format escape
    const status = String(order.status || '').replace(/[&<>"'`]/g, char => ({'&' : '&amp;', '<' : '&lt;', '>' : '&gt;', '"' : '&quot;', "'" : '&#39;', '`' : '&#96;'}[char])); // Escape status just in case
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);

    return `
        <tr data-order-id="${orderId}">
            <td data-label="ID">${orderId}</td>
            <td data-label="Müşteri">${customerInfo}</td>
            <td data-label="Ürünler">${itemsHTML}</td>
            <td data-label="Tutar"><strong>${totalPrice} TL</strong></td>
            <td data-label="Zaman">${createdAt}</td>
            <td data-label="Durum" class="order-status">
                <span class="status-badge status-${status}">${statusText}</span>
            </td>
            <td data-label="İşlem" class="order-actions">
                ${actionsHTML.trim()}
            </td>
        </tr>
    `;
} 