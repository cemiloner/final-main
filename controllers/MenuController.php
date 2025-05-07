<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;
use App\Controllers\UserAuthController;

class MenuController extends BaseController
{
    /**
     * Menü sayfasını gösterir.
     */
    public function index(): void
    {
        UserAuthController::requireUserLogin();

        // Kategorileri çek
        $categories = R::findAll('category', 'ORDER BY name ASC');
        
        // Ürünleri kategorilerine göre gruplayarak çek
        $products = R::findAll('product', 'ORDER BY name ASC');
        $productsByCategory = [];
        foreach ($products as $product) {
            // İlişkili kategori nesnesini değil, ID'sini kullanmak daha verimli olabilir
            $categoryId = $product->category_id; 
            if ($categoryId) {
                 if (!isset($productsByCategory[$categoryId])) {
                    $productsByCategory[$categoryId] = [];
                }
                $productsByCategory[$categoryId][] = $product;
            }
        }

        // Aktif masaları çek
        $activeTables = R::findAll('table', 'is_active = ? ORDER BY name ASC', [true]);

        $this->view('menu', [
            'pageTitle' => 'Menü',
            'categories' => $categories,
            'productsByCategory' => $productsByCategory,
            'activeTables' => $activeTables // Aktif masaları view'e gönder
        ]);
    }
}

?> 