<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;

class MenuController extends BaseController
{
    /**
     * Menü sayfasını gösterir.
     */
    public function index(): void
    {
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

        $this->view('menu', [
            'pageTitle' => 'Menü',
            'categories' => $categories,
            'productsByCategory' => $productsByCategory
        ]);
    }
}

?> 