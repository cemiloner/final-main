<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Controllers\AuthController;
use \RedBeanPHP\R as R;

class AdminProductController extends BaseController
{
    public function __construct()
    {
        AuthController::requireAdmin(); // Bu controller için admin girişi zorunlu
    }

    /**
     * Ürün listesini gösterir.
     */
    public function index(): void
    {
        $products = R::findAll('product', 'ORDER BY name ASC');
        $categories = R::findAll('category', 'ORDER BY name ASC'); // Kategorileri de çek

        // Hata ayıklama için geçici: Ürün sayısını kontrol et
        // var_dump('Ürün Sayısı: ' . count($products)); 

        $this->view('admin/products/index', [
            'pageTitle' => 'Ürün Yönetimi',
            'products' => $products,
            'categories' => $categories // Kategorileri view'e gönder
        ], 'admin');
    }

    /**
     * Yeni ürün ekleme formunu gösterir.
     */
    public function create(): void
    {
        $categories = R::findAll('category', 'ORDER BY name ASC');
        $this->view('admin/products/create', [
            'pageTitle' => 'Yeni Ürün Ekle',
            'categories' => $categories
        ], 'admin');
    }

    /**
     * Yeni ürünü veritabanına kaydeder.
     */
    public function store(): void
    {
        // Form verilerini al ve temizle
        $name = $this->sanitize($_POST['name'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
        $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT); // Stok al

        // Doğrulama (Basit)
        $errors = [];
        if (empty($name)) $errors[] = 'Ürün adı boş olamaz.';
        if ($price === false || $price < 0) $errors[] = 'Geçersiz fiyat.';
        if (empty($categoryId)) $errors[] = 'Kategori seçimi zorunludur.';
        if ($stock === false || $stock < 0) $errors[] = 'Geçersiz stok miktarı.'; // Stok doğrulaması

        $category = $categoryId ? R::load('category', $categoryId) : null;
        if ($categoryId && !$category->id) $errors[] = 'Geçersiz kategori seçildi.';

        // Resim Yükleme Doğrulaması ve İşlemi
        $imagePath = null;
        $uploadError = $this->handleImageUpload('image', $imagePath); // Yardımcı metoda taşıyalım
        if ($uploadError) {
            $errors[] = $uploadError;
        }
        
        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Formda hatalar var.', 'errors' => $errors], 422); // 422 Unprocessable Entity
            } else {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect('/admin/products/create');
            }
            return;
        }

        // Yeni ürün bean'i oluştur
        $product = R::dispense('product');
        $product->name = $name;
        $product->description = $description;
        $product->price = $price;
        $product->category = $category;
        $product->stock = ($stock > 0) ? $stock : null; // 0 ise null kaydet (stok takibi yok)
        $product->image_path = $imagePath; // Resim yolunu kaydet
        $product->created_at = date('Y-m-d H:i:s');
        $product->updated_at = date('Y-m-d H:i:s');

        try {
            R::store($product);
            if ($this->isAjax()) {
                 $this->jsonResponse(['success' => true, 'message' => 'Ürün başarıyla eklendi.']);
            } else {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Ürün başarıyla eklendi.'];
                $this->redirect('/admin/products');
            }
        } catch (\Exception $e) {
            error_log("Product store error: " . $e->getMessage());
             if ($this->isAjax()) {
                 $this->jsonResponse(['success' => false, 'message' => 'Ürün eklenirken bir sunucu hatası oluştu.'], 500);
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Ürün eklenirken bir hata oluştu.'];
                $this->redirect('/admin/products/create'); // Hata durumunda forma geri dön
            }
        }
    }

    /**
     * Ürün düzenleme formunu gösterir.
     */
    public function edit(): void
    {
        $productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$productId) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Geçersiz Ürün ID.'];
            $this->redirect('/admin/products');
            return;
        }

        $product = R::findOne('product', 'id = ?', [$productId]);

        if (!$product) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Ürün bulunamadı.'];
            $this->redirect('/admin/products');
            return;
        }

        $categories = R::findAll('category', 'ORDER BY name ASC');
        $this->view('admin/products/edit', [
            'pageTitle' => 'Ürün Düzenle: ' . htmlspecialchars($product->name),
            'product' => $product,
            'categories' => $categories
        ], 'admin');
    }

    /**
     * Ürünü günceller.
     */
    public function update(): void
    {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);

        // Form verilerini al ve temizle
        $name = $this->sanitize($_POST['name'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
        $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT); // Stok al

        // Doğrulama (Basit)
        $errors = [];
        if (empty($productId)) $errors[] = 'Ürün ID eksik.';
        if (empty($name)) $errors[] = 'Ürün adı boş olamaz.';
        if ($price === false || $price < 0) $errors[] = 'Geçersiz fiyat.';
        if (empty($categoryId)) $errors[] = 'Kategori seçimi zorunludur.';
        if ($stock === false || $stock < 0) $errors[] = 'Geçersiz stok miktarı.'; // Stok doğrulaması

        $product = $productId ? R::load('product', $productId) : null;
        if ($productId && !$product->id) $errors[] = 'Güncellenecek ürün bulunamadı.';
        
        $category = $categoryId ? R::load('category', $categoryId) : null;
        if ($categoryId && !$category->id) $errors[] = 'Geçersiz kategori seçildi.';

        // Resim Yükleme Doğrulaması ve İşlemi (Eğer yeni resim yüklendiyse)
        $imagePath = $product->image_path; // Önce mevcut resmi al
        $uploadError = $this->handleImageUpload('image', $imagePath, $product->image_path); // Yardımcı metoda taşıyalım
        if ($uploadError) {
            $errors[] = $uploadError;
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Formda hatalar var.', 'errors' => $errors], 422);
            } else {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect('/admin/products/edit?id=' . $productId);
            }
            return;
        }

        // Ürün bean'ini güncelle
        $product->name = $name;
        $product->description = $description;
        $product->price = $price;
        $product->category = $category;
        $product->stock = ($stock > 0) ? $stock : null; // Stok güncelle
        $product->image_path = $imagePath; // Yeni veya eski resim yolu
        $product->updated_at = date('Y-m-d H:i:s');

        try {
            R::store($product);
             if ($this->isAjax()) {
                 $this->jsonResponse(['success' => true, 'message' => 'Ürün başarıyla güncellendi.']);
            } else {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Ürün başarıyla güncellendi.'];
                $this->redirect('/admin/products');
            }
        } catch (\Exception $e) {
            error_log("Product update error: " . $e->getMessage());
             if ($this->isAjax()) {
                 $this->jsonResponse(['success' => false, 'message' => 'Ürün güncellenirken bir sunucu hatası oluştu.'], 500);
            } else {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Ürün güncellenirken bir hata oluştu.'];
                 $this->redirect('/admin/products/edit?id=' . $productId); // Hata durumunda forma geri dön
            }
        }
    }

    /**
     * Ürünü siler.
     */
    public function delete(): void
    {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $isAjax = $this->isAjax(); // Check if it's an AJAX request

        if (!$productId) {
            $message = 'Geçersiz Ürün ID.';
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => $message], 400);
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => $message];
                $this->redirect('/admin/products');
            }
            return;
        }

        try {
            $product = R::load('product', $productId);
            if ($product->id) {
                // Bu ürünle ilişkili sipariş kalemi var mı kontrol et?
                $relatedOrderItems = R::count('orderitem', 'product_id = ?', [$productId]);

                if ($relatedOrderItems > 0) {
                    $message = 'Bu ürün aktif siparişlerde kullanıldığı için silinemez.';
                     if ($isAjax) {
                        $this->jsonResponse(['success' => false, 'message' => $message], 409); // 409 Conflict
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'text' => $message];
                    }
                } else {
                    // İlişkili sipariş yoksa sil
                    // Eski resmi sil (varsa)
                    if ($product->image_path && file_exists(ROOT_PATH . '/public' . $product->image_path)) {
                         @unlink(ROOT_PATH . '/public' . $product->image_path); // Hata kontrolü eklenebilir
                    }
                    R::trash($product);
                    $message = 'Ürün başarıyla silindi.';
                    if ($isAjax) {
                        $this->jsonResponse(['success' => true, 'message' => $message]);
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => $message];
                    }
                }
            } else {
                $message = 'Silinecek ürün bulunamadı.';
                 if ($isAjax) {
                    $this->jsonResponse(['success' => false, 'message' => $message], 404);
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => $message];
                }
            }
        } catch (\Exception $e) {
            error_log("Product delete error: " . $e->getMessage());
            $message = 'Ürün silinirken bir hata oluştu.';
             if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => $message], 500);
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => $message];
            }
        }
        
        // Sadece non-AJAX isteklerinde yönlendir
        if (!$isAjax) {
            $this->redirect('/admin/products');
        }
    }

    /**
     * AJAX isteği olup olmadığını kontrol eder.
     */
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Resim yükleme işlemini yöneten yardımcı metod.
     *
     * @param string $fileInputName Formdaki dosya inputunun adı.
     * @param string|null &$imagePath Başarılı olursa kaydedilecek resim yolu (referans).
     * @param string|null $existingImagePath Varsa, üzerine yazılacak eski resmin yolu (silmek için).
     * @return string|null Hata mesajı veya başarılıysa null.
     */
    private function handleImageUpload(string $fileInputName, ?string &$imagePath, ?string $existingImagePath = null): ?string
    {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$fileInputName];
            $uploadDir = ROOT_PATH . '/public/uploads/products/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            // Tür kontrolü
            if (!in_array($file['type'], $allowedTypes)) {
                return 'Geçersiz dosya türü. Sadece JPG, PNG, GIF kabul edilir.';
            }

            // Boyut kontrolü
            if ($file['size'] > $maxSize) {
                return 'Dosya boyutu çok büyük (Maksimum 2MB).';
            }

            // Benzersiz dosya adı oluştur
            $fileName = uniqid('product_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $destination = $uploadDir . $fileName;

            // Dosyayı taşı
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Başarılı yükleme, eski resmi sil (eğer varsa ve farklıysa)
                if ($existingImagePath && $existingImagePath !== ('/uploads/products/' . $fileName) && file_exists(ROOT_PATH . '/public' . $existingImagePath)) {
                    unlink(ROOT_PATH . '/public' . $existingImagePath);
                }
                $imagePath = '/uploads/products/' . $fileName; // Kaydedilecek göreceli yol
                return null; // Hata yok
            } else {
                return 'Dosya yüklenirken bir hata oluştu.';
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
            // Dosya seçilmiş ama yüklenememişse (NO_FILE dışındaki hatalar)
            return 'Dosya yüklenirken bir hata oluştu (Kod: ' . $_FILES[$fileInputName]['error'] . ').';
        }
        // Yeni dosya seçilmemişse, mevcut yolu koru (güncelleme için), hata yok
        return null;
    }
}
?> 