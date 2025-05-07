document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const cartSidebar = document.querySelector('.cart-sidebar');
    const floatingButton = document.querySelector('.floating-cart-button');
    const closeSidebarButton = document.querySelector('.close-sidebar');
    const cartContent = document.querySelector('.cart-content');
    const tableSelect = document.getElementById('selected_table_id');
    const orderMessageDiv = document.getElementById('order-message');

    let clientCartItems = [];
    let selectedTableId = null;

    // Ensure this script only runs on the menu page if body.menu-page class exists
    if (document.body.classList.contains('menu-page')) {
        initializeMenuPageCart();
    }

    function initializeMenuPageCart() {
        if (tableSelect) {
            selectedTableId = tableSelect.value;
            tableSelect.addEventListener('change', function() {
                selectedTableId = this.value;
                const errorEl = document.getElementById('table-selection-error');
                if (errorEl) {
                    errorEl.style.display = selectedTableId ? 'none' : 'block';
                }
            });
        }

        if (floatingButton) {
            floatingButton.addEventListener('click', function() {
                cartSidebar.classList.add('active');
                floatingButton.style.display = 'none';
                updateCartDisplay();
            });
        }

        if (closeSidebarButton) {
            closeSidebarButton.addEventListener('click', function() {
                cartSidebar.classList.remove('active');
                if (floatingButton) floatingButton.style.display = 'flex';
            });
        }

        document.addEventListener('click', function(event) {
            if (cartSidebar && floatingButton && !cartSidebar.contains(event.target) && !floatingButton.contains(event.target)) {
                if (cartSidebar.classList.contains('active')) {
                    cartSidebar.classList.remove('active');
                    floatingButton.style.display = 'flex';
                }
            }
        });

        document.querySelectorAll('form.order-form').forEach(form => {
            form.addEventListener('submit', handleAddItemToClientCart);
        });

        updateCartDisplay();
    }

    function handleAddItemToClientCart(event) {
        event.preventDefault();
        const form = event.target;
        const productId = form.dataset.productId;
        const quantityInput = form.querySelector('input[name="quantity"]');
        const quantity = parseInt(quantityInput.value);

        const productItemDiv = form.closest('.product-item');
        const productName = productItemDiv.querySelector('.product-details h4').textContent;
        const productPriceText = productItemDiv.querySelector('.product-details strong').textContent;
        const productPrice = parseFloat(productPriceText.replace(' TL', '').replace(',', '.'));

        if (quantity <= 0) {
            showOrderMessage('Lütfen geçerli bir miktar girin.', 'error');
            return;
        }
        
        const existingItemIndex = clientCartItems.findIndex(item => item.id === productId);
        if (existingItemIndex > -1) {
            clientCartItems[existingItemIndex].quantity += quantity;
        } else {
            clientCartItems.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: quantity
            });
        }
        updateCartDisplay();
        showOrderMessage(`${productName} sepete eklendi.`, 'success');
    }

    function updateCartDisplay() {
        if (!cartContent) return;
        cartContent.innerHTML = '';

        if (clientCartItems.length === 0) {
            cartContent.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Sepetiniz boş</p>
                </div>
            `;
            return;
        }

        let totalAmount = 0;
        clientCartItems.forEach(item => {
            const itemSubtotal = item.price * item.quantity;
            totalAmount += itemSubtotal;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.innerHTML = `
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>${item.quantity} adet x ${item.price.toFixed(2)} TL</p>
                    <p class="subtotal">Ara Toplam: ${itemSubtotal.toFixed(2)} TL</p>
                </div>
                <button class="remove-item-btn" data-item-id="${item.id}" aria-label="Ürünü Kaldır">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            cartContent.appendChild(itemDiv);

            itemDiv.querySelector('.remove-item-btn').addEventListener('click', function() {
                removeFromClientCart(item.id);
            });
        });

        const totalDiv = document.createElement('div');
        totalDiv.className = 'cart-total';
        totalDiv.innerHTML = `
            <h4>Toplam: ${totalAmount.toFixed(2)} TL</h4>
        `;
        cartContent.appendChild(totalDiv);

        if (clientCartItems.length > 0) {
            const placeOrderButton = document.createElement('button');
            placeOrderButton.className = 'btn btn-success btn-block checkout-btn';
            placeOrderButton.textContent = 'Siparişi Oluştur';
            placeOrderButton.addEventListener('click', placeFullOrder);
            cartContent.appendChild(placeOrderButton);
        }
    }

    function removeFromClientCart(itemId) {
        clientCartItems = clientCartItems.filter(item => item.id !== itemId);
        updateCartDisplay();
        showOrderMessage('Ürün sepetten kaldırıldı.', 'info');
    }

    async function placeFullOrder() {
        if (clientCartItems.length === 0) {
            showOrderMessage('Sepetiniz boş. Lütfen ürün ekleyin.', 'error');
            return;
        }

        if (tableSelect && !selectedTableId) {
            showOrderMessage('Lütfen sipariş için bir masa seçin.', 'error', true);
            if (cartSidebar.classList.contains('active')) {
            } else {
                if (floatingButton) floatingButton.click();
            }
            return;
        }
        
        const orderData = {
            table_id: selectedTableId,
            items: clientCartItems,
        };

        try {
            const response = await fetch('/order/create_full', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (result.success) {
                clientCartItems = [];
                updateCartDisplay();
                showOrderMessage(result.message || 'Siparişiniz başarıyla oluşturuldu!', 'success');
                if (cartSidebar.classList.contains('active')) {
                    closeSidebarButton.click();
                }
            } else {
                showOrderMessage(result.message || 'Sipariş oluşturulurken bir hata oluştu.', 'error');
            }
        } catch (error) {
            console.error('Error placing order:', error);
            showOrderMessage('Sipariş sırasında bir ağ hatası oluştu. Lütfen tekrar deneyin.', 'error');
        }
    }

    function showOrderMessage(message, type = 'info', focusTable = false) {
        if (orderMessageDiv) {
            orderMessageDiv.textContent = message;
            orderMessageDiv.className = `alert alert-${type}`;
            orderMessageDiv.style.display = 'block';
            setTimeout(() => {
                orderMessageDiv.style.display = 'none';
            }, 5000);
        } else {
            alert(message);
        }
        
        if (focusTable && tableSelect) {
            tableSelect.focus();
            const tableSection = document.getElementById('table-selection-section');
            if (tableSection) {
                tableSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
});
