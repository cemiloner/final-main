document.addEventListener('DOMContentLoaded', function() {
    // Only initialize cart functionality on menu page
    if (document.querySelector('.menu-container')) {
    // Track selected table
    let selectedTableId = null;

    // Get DOM elements
    const cartSidebar = document.querySelector('.cart-sidebar');
    const floatingButton = document.querySelector('.floating-cart-button');
    const closeSidebar = document.querySelector('.close-sidebar');
    const cartContent = document.querySelector('.cart-content');
    const tableSelect = document.getElementById('selected_table_id');

    // Check if elements exist
    if (!cartSidebar || !floatingButton || !closeSidebar || !cartContent || !tableSelect) {
        console.error('Missing required DOM elements for cart functionality');
        return;
    }

    // Table selection handler
    tableSelect.addEventListener('change', function() {
        selectedTableId = this.value;
        if (selectedTableId) {
            document.getElementById('table-selection-error').style.display = 'none';
        }
    });

    // Check if user is logged in
    const userId = sessionStorage.getItem('user_id');
    if (!userId) {
        floatingButton.style.display = 'none';
        return;
    }

    // Initialize cart
    loadCartItems();
        floatingButton.style.display = 'none';
        return;
    }

    // Toggle sidebar
    floatingButton.addEventListener('click', function() {
        cartSidebar.classList.add('active');
        floatingButton.style.display = 'none';
        // Load cart items when sidebar opens
        loadCartItems();
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        if (!cartSidebar.contains(event.target) && !floatingButton.contains(event.target)) {
            cartSidebar.classList.remove('active');
            floatingButton.style.display = 'flex';
        }
    });

    // Swipe to close cart sidebar
    // let touchStartX = 0;
    // let touchEndX = 0;
    // const swipeThreshold = 50; // Minimum pixels to be considered a swipe

    // if (cartSidebar) {
    //     cartSidebar.addEventListener('touchstart', function(event) {
    //         touchStartX = event.changedTouches[0].screenX;
    //     }, { passive: true });

    //     cartSidebar.addEventListener('touchend', function(event) {
    //         touchEndX = event.changedTouches[0].screenX;
    //         handleSwipeGesture();
    //     });
    // }

    // function handleSwipeGesture() {
    //     const deltaX = touchEndX - touchStartX;
    //     // Swipe right to close (assuming sidebar is on the right)
    //     if (deltaX > swipeThreshold && cartSidebar.classList.contains('active')) {
    //         cartSidebar.classList.remove('active');
    //         if (floatingButton) floatingButton.style.display = 'flex';
    //     }
    //     // Reset touch positions
    //     touchStartX = 0;
    //     touchEndX = 0;
    // }

    // Adjust main content margin when sidebar is open
    cartSidebar.addEventListener('transitionend', function() {
        const mainContent = document.querySelector('.main-content');
        if (cartSidebar.classList.contains('active')) {
            mainContent.style.marginLeft = '350px';
        } else {
            mainContent.style.marginLeft = '0';
        }
    });

    // Handle cart updates
    function updateCartDisplay(cartItems) {
        cartContent.innerHTML = '';

        if (cartItems.length === 0) {
            cartContent.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Sepetiniz boş</p>
                </div>
            `;
            return;
        }

        cartItems.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.innerHTML = `
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>${item.quantity} adet</p>
                    <p>${item.price} TL</p>
                    <p class="subtotal">${(item.quantity * item.price).toFixed(2)} TL</p>
                </div>
                <button class="remove-item" data-item-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            cartContent.appendChild(itemDiv);

            // Add remove item functionality
            const removeButton = itemDiv.querySelector('.remove-item');
            removeButton.addEventListener('click', function() {
                removeFromCart(item.id);
            });
        });

        // Add total calculation
        const total = cartItems.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        const totalDiv = document.createElement('div');
        totalDiv.className = 'cart-total';
        totalDiv.innerHTML = `
            <h4>Toplam: ${total.toFixed(2)} TL</h4>
            <button class="checkout-btn">Siparişi Tamamla</button>
        `;
        cartContent.appendChild(totalDiv);

        // Add checkout functionality
        const checkoutBtn = totalDiv.querySelector('.checkout-btn');
        checkoutBtn.addEventListener('click', function() {
            if (confirm('Siparişinizi tamamlamak ister misiniz?')) {
                completeOrder();
            }
        });
    }

    // Load cart items from server
    async function loadCartItems() {
        try {
            const response = await fetch('/cart/items', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                updateCartDisplay(data.items);
            } else {
                cartContent.innerHTML = '<p>Sepet yüklenirken bir hata oluştu.</p>';
            }
        } catch (error) {
            console.error('Error loading cart items:', error);
            cartContent.innerHTML = '<p>Sepet yüklenirken bir hata oluştu.</p>';
        }
    }

    // Remove item from cart
    async function removeFromCart(itemId) {
        try {
            const response = await fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                loadCartItems();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error removing item:', error);
            alert('Sepetten kaldırma sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    // Complete order
    async function completeOrder() {
        try {
            const response = await fetch('/cart/complete', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Siparişiniz başarıyla tamamlandı!');
                cartSidebar.classList.remove('active');
                floatingButton.style.display = 'flex';
                loadCartItems();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error completing order:', error);
            alert('Sipariş tamamlanırken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    // Add event listener for order form submissions
    document.querySelectorAll('.order-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = this.querySelector('input[name="quantity"]').value;
            
            try {
                const response = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        table_id: selectedTableId
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    const messageDiv = document.getElementById('order-message');
                    messageDiv.innerHTML = '<div class="alert alert-success">Ürün başarıyla sepete eklendi!</div>';
                    messageDiv.style.display = 'block';
                    
                    // Hide message after 3 seconds
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 3000);

                    // Update cart count in sidebar if open
                    if (cartSidebar.classList.contains('active')) {
                        loadCartItems();
                    }
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                alert('Sepete ekleme sırasında bir hata oluştu. Lütfen tekrar deneyin.');
            }
        });
    });
});
