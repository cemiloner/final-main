<?php

use App\Core\Router;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\AdminProductController;
use App\Controllers\AdminCategoryController;

/** @var Router $router */

// Müşteri Paneli Rotaları
$router->get('/menu', [MenuController::class, 'index']);
$router->post('/order', [OrderController::class, 'store']);

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

// Auth Rotaları
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Ana sayfa (şimdilik menüye yönlendirebilir)
$router->get('/', [MenuController::class, 'index']);

?> 