<?php

use App\Core\Router;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\UserAuthController;
use App\Controllers\AdminProductController;
use App\Controllers\AdminCategoryController;
use App\Controllers\AdminTableController;
use App\Controllers\CartController;

/** @var Router $router */

// Main page (Menu)
$router->get('/', [MenuController::class, 'index']);

// Müşteri (User) Rotaları
$router->get('/menu', [MenuController::class, 'index']);
$router->post('/order', [OrderController::class, 'store']);
$router->post('/cart/add', [CartController::class, 'addToCart']);
$router->get('/cart', [CartController::class, 'viewCart']);
$router->post('/order/place', [OrderController::class, 'placeOrder']);
$router->get('/orders', [OrderController::class, 'viewOrders']);

// Müşteri (User) Giriş/Çıkış Rotaları
$router->get('/userlogin', [UserAuthController::class, 'showUserLoginForm']);
$router->post('/userlogin', [UserAuthController::class, 'loginUser']);
$router->get('/userlogout', [UserAuthController::class, 'logoutUser']);

// Müşteri (User) Kayıt Rotaları
$router->get('/userregister', [UserAuthController::class, 'showUserRegistrationForm']);
$router->post('/userregister', [UserAuthController::class, 'registerUser']);

// Admin Giriş/Çıkış Rotaları (Existing - for Admin Panel)
$router->get('/login', [AuthController::class, 'showLoginForm']); // This is ADMIN login
$router->post('/login', [AuthController::class, 'login']);       // This is ADMIN login
$router->get('/logout', [AuthController::class, 'logout']);    // This is ADMIN logout

// Admin Paneli Rotaları
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/archived', [AdminController::class, 'archivedOrders']);
$router->post('/admin/orders/update-status', [AdminController::class, 'updateOrderStatus']);
$router->post('/admin/end-of-day', [AdminController::class, 'endOfDayProcess']);

// Admin Ürün Yönetimi Rotaları
$router->get('/admin/products', [AdminProductController::class, 'index']);
$router->get('/admin/products/create', [AdminProductController::class, 'create']);
$router->post('/admin/products/store', [AdminProductController::class, 'store']);
$router->get('/admin/products/edit', [AdminProductController::class, 'edit']);
$router->post('/admin/products/update', [AdminProductController::class, 'update']);
$router->post('/admin/products/delete', [AdminProductController::class, 'delete']);

// Admin Kategori Yönetimi Rotaları
$router->post('/admin/categories/store', [AdminCategoryController::class, 'store']);
$router->post('/admin/categories/delete', [AdminCategoryController::class, 'delete']);
$router->get('/admin/categories/list-json', [AdminCategoryController::class, 'listJson']);

// Admin Masa Yönetimi Rotaları
$router->get('/admin/tables', [AdminTableController::class, 'index']);
$router->post('/admin/tables/store', [AdminTableController::class, 'store']);
$router->post('/admin/tables/toggle-status', [AdminTableController::class, 'toggleStatus']);
$router->post('/admin/tables/delete', [AdminTableController::class, 'delete']);