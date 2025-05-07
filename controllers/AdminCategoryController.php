<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Controllers\AuthController;
use \RedBeanPHP\R as R;

class AdminCategoryController extends BaseController
{
    public function __construct()
    {
        AuthController::requireAdmin(); // Admin girişi zorunlu
    }

    /**
     * Tüm kategorileri JSON formatında listeler (AJAX).
     */
    public function listJson(): void
    {
        try {
            $categories = R::findAll('category', 'ORDER BY name ASC');
            
            // Veriyi JavaScript'in beklediği formata dönüştür (isteğe bağlı ama iyi pratik)
            $formattedCategories = [];
            foreach ($categories as $category) {
                $formattedCategories[] = [
                    'id' => $category->id,
                    'name' => htmlspecialchars($category->name ?? '', ENT_QUOTES, 'UTF-8') // Basic XSS prevention
                ];
            }

            $this->jsonResponse([
                'success' => true, 
                'categories' => $formattedCategories
            ]);

        } catch (\Exception $e) {
            error_log("Category listJson error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Kategoriler listelenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Yeni kategoriyi kaydeder.
     */
    public function store(): void
    {
        $name = $this->sanitize($_POST['category_name'] ?? '');

        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Kategori adı boş olamaz.'], 400);
            return;
        }
        
        try {
            $existing = R::findOne('category', ' name = ? ', [$name]);
            if ($existing) {
                $this->jsonResponse(['success' => false, 'message' => 'Bu isimde bir kategori zaten var.'], 409); // 409 Conflict
                return;
            }

            $category = R::dispense('category');
            $category->name = $name;
            $id = R::store($category);
            $this->jsonResponse(['success' => true, 'message' => 'Kategori başarıyla eklendi.', 'new_category' => ['id' => $id, 'name' => $name]]);
        
        } catch (\Exception $e) {
            error_log("Category store error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Kategori eklenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Kategoriyi siler (AJAX).
     */
    public function delete(): void
    {
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$categoryId) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz Kategori ID.'], 400);
            return;
        }

        try {
            $category = R::load('category', $categoryId);

            if (!$category->id) {
                 $this->jsonResponse(['success' => false, 'message' => 'Silinecek kategori bulunamadı.'], 404);
                 return;
            }

            // Bu kategoriye atanmış ürün var mı kontrol et
            $productCount = R::count('product', ' category_id = ? ', [$categoryId]);

            if ($productCount > 0) {
                 $this->jsonResponse(['success' => false, 'message' => 'Bu kategoriye atanmış (' . $productCount . ' adet) ürün bulunduğu için silinemez.'], 409); // Conflict
                 return;
            }

            // Kategori silinebilir
            R::trash($category);
            $this->jsonResponse(['success' => true, 'message' => 'Kategori başarıyla silindi.']);

        } catch (\Exception $e) {
             error_log("Category delete error: " . $e->getMessage());
             $this->jsonResponse(['success' => false, 'message' => 'Kategori silinirken bir sunucu hatası oluştu.'], 500);
        }
    }
    
    /**
     * AJAX isteği olup olmadığını kontrol eder (Bu aslında BaseController'a taşınabilir).
     */
    private function isAjax(): bool
    {
        // Note: This check might be better placed in BaseController if used elsewhere
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
?> 