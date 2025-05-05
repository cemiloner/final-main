<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;

class OrderController extends BaseController
{
    /**
     * Yeni sipariş oluşturur (AJAX ile çağrılır).
     */
    public function store(): void
    {
        // Gelen JSON verisini al
        $input = json_decode(file_get_contents('php://input'), true);

        // Basit doğrulama ve sanitizasyon
        $productId = filter_var($input['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!$productId || !$quantity) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz ürün veya adet.'], 400);
            return;
        }

        try {
            R::begin(); // Transaction başlat
            $isStockTracked = false; // Linter için başlangıç değeri ata

            // Ürünü bul ve kilitle (eğer veritabanı destekliyorsa - SQLite için FOR UPDATE yok)
            // Race condition riskini azaltmak için işlemi transaction içinde yapıyoruz.
            $product = R::findOne('product', ' id = ? ', [$productId]);
            
            if (!$product) {
                 R::rollback(); // Ürün yoksa transaction'ı geri al
                 $this->jsonResponse(['success' => false, 'message' => 'Ürün bulunamadı.'], 404);
                 return;
            }
            
            // Stok Kontrolü
            $stock = $product->stock;
            $isStockTracked = isset($stock);
            
            if ($isStockTracked) {
                if ($stock < $quantity) {
                    R::rollback(); // Yetersiz stok, transaction'ı geri al
                    $this->jsonResponse(['success' => false, 'message' => 'Yetersiz stok! Mevcut stok: ' . $stock], 400);
                    return;
                }
            }
            
            // Yeni sipariş oluştur
            $order = R::dispense('order');
            $order->customer_info = 'Placeholder Customer'; // TODO: Gerçek müşteri bilgisi eklenmeli
            $order->status = 'bekliyor'; // Başlangıç durumu: Bekliyor
            $order->created_at = date('Y-m-d H:i:s');
            $order->total_price = 0; 

            // Sipariş kalemini oluştur
            $orderItem = R::dispense('orderitem');
            $orderItem->product = $product; 
            $orderItem->quantity = $quantity;
            $orderItem->price_per_item = $product->price; 
            $order->ownOrderitemList[] = $orderItem;
            $order->total_price = $product->price * $quantity;

            // Stok Azaltma
            if ($isStockTracked) {
                $product->stock -= $quantity;
            }

            // Kaydet (Sipariş, Sipariş Kalemi ve GÜNCELLENMİŞ ÜRÜN STOĞU)
            R::store($order);
            if ($isStockTracked) { 
                R::store($product); // Güncellenmiş stoğu kaydet
            }

            R::commit(); // Transaction bitir

            $this->jsonResponse(['success' => true, 'message' => htmlspecialchars($product->name) . ' siparişi alındı.', 'order_id' => $order->id]); // Mesaj güncellendi

        } catch (\Exception $e) {
            R::rollback(); // Hata olursa geri al
            error_log("Order creation error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Sipariş oluşturulurken bir sunucu hatası oluştu.'], 500);
        }
    }
}

?> 