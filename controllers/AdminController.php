<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;
use App\Controllers\AuthController;

class AdminController extends BaseController
{
    private $activeStatuses = ['bekliyor', 'preparing', 'ready'];
    private $archivedStatuses = ['delivered', 'cancelled'];
    private $allStatuses = ['bekliyor', 'preparing', 'ready', 'delivered', 'cancelled']; // For validation

    public function __construct()
    {
        // Bu controller'daki tüm action'lar için admin girişi gerekli
        AuthController::requireAdmin();
    }

    /**
     * Admin ana sayfasını (dashboard) gösterir.
     */
    public function dashboard(): void
    {
        // Aktif sipariş sayısını kontrol et
        $statusPlaceholders = R::genSlots($this->activeStatuses);
        $activeOrderCount = R::count('order', "status IN ($statusPlaceholders)", $this->activeStatuses);

        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Paneli',
            'activeOrderCount' => $activeOrderCount // Sayıyı view'e gönder
        ], 'admin'); // Admin layout kullan
    }

    /**
     * Admin - Aktif siparişler sayfasını gösterir.
     */
    public function orders(): void
    {
        // Sadece aktif durumdakileri çek
        $statusPlaceholders = R::genSlots($this->activeStatuses);
        $ordersBeans = R::findAll('order', "status IN ($statusPlaceholders) ORDER BY created_at DESC", $this->activeStatuses);
        
        $ordersData = $this->prepareOrdersData($ordersBeans);

        $this->view('admin/orders', [
            'pageTitle' => 'Admin - Aktif Siparişler',
            'orders' => $ordersData // İşlenmiş veriyi view'e gönder
        ], 'admin');
    }

    /**
     * Admin - Arşivlenmiş siparişler sayfasını gösterir.
     */
    public function archivedOrders(): void
    {
        // Sadece arşivlenmiş durumdakileri çek
        $statusPlaceholders = R::genSlots($this->archivedStatuses);
        $ordersBeans = R::findAll('order', "status IN ($statusPlaceholders) ORDER BY created_at DESC", $this->archivedStatuses);
        
        $ordersData = $this->prepareOrdersData($ordersBeans);

        $this->view('admin/archived_orders', [
            'pageTitle' => 'Admin - Arşivlenmiş Siparişler',
            'orders' => $ordersData // İşlenmiş veriyi view'e gönder
        ], 'admin');
    }


    /**
     * Sipariş bean'lerini view için uygun dizi formatına dönüştürür.
     */
    private function prepareOrdersData(array $ordersBeans): array
    {
        $ordersData = [];
        foreach ($ordersBeans as $orderBean) {
            // performansı artırmak için eager loading deneyebiliriz ama RedbeanPHP'de doğrudan yok
            // N+1 sorgu problemi olabilir. Çok fazla sipariş varsa optimizasyon gerekebilir.
            $orderItems = R::findAll('orderitem', 'order_id = ?', [$orderBean->id]);
            $itemsDetails = [];
            $totalPrice = 0;
            foreach ($orderItems as $item) {
                $product = R::load('product', $item->product_id); // İlişkili ürünü yükle
                $itemData = [
                    'product_name' => $product->id ? htmlspecialchars($product->name) : '[Ürün Silinmiş]',
                    'product_exists' => (bool)$product->id,
                    'quantity' => $item->quantity,
                    'price_per_item' => $item->price_per_item, // Sipariş anındaki fiyatı kullan
                    'item_total' => $product->id ? ($item->price_per_item * $item->quantity) : 0
                ];
                $itemsDetails[] = $itemData;
                $totalPrice += $itemData['item_total'];
            }
            
            $ordersData[] = [
                'id' => $orderBean->id,
                'customer_info' => htmlspecialchars($orderBean->customer_info),
                'status' => $orderBean->status,
                'created_at' => $orderBean->created_at,
                'items' => $itemsDetails,
                // Toplam fiyatı doğrudan bean'den almak daha doğru olabilir, eğer OrderController'da doğru hesaplanıyorsa
                'total_price' => $orderBean->total_price // $totalPrice yerine bean'deki değeri kullanalım
            ];
        }
        return $ordersData;
    }

    /**
     * Sipariş durumunu günceller (AJAX ile çağrılır).
     */
    public function updateOrderStatus(): void
    {
        // Giriş kontrolü __construct içinde yapıldı
        
        // Gelen JSON verisini al
        $input = json_decode(file_get_contents('php://input'), true);

        // Doğrulama ve sanitizasyon
        $orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
        $newStatus = $this->sanitize($input['status'] ?? ''); // Basit sanitize
        // $allowedStatuses = ['preparing', 'ready', 'delivered', 'cancelled']; // Eski
        
        if (!$orderId || !in_array($newStatus, $this->allStatuses)) { // Tüm geçerli durumları kontrol et
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz sipariş ID veya durum ('.$newStatus.').'], 400);
            return;
        }

        try {
            // Siparişi bul
            $order = R::findOne('order', ' id = ? ', [$orderId]);

            if (!$order) {
                $this->jsonResponse(['success' => false, 'message' => 'Sipariş bulunamadı.'], 404);
                return;
            }

            // Mevcut durum ve yeni durum arasındaki geçişi doğrula (isteğe bağlı ama önerilir)
            $currentStatus = $order->status;
            $isValidTransition = $this->validateStatusTransition($currentStatus, $newStatus);
            
            if (!$isValidTransition) {
                 $this->jsonResponse(['success' => false, 'message' => 'Geçersiz durum geçişi: ' . $currentStatus . ' -> ' . $newStatus], 400);
                 return;
            }

            // Durumu güncelle ve kaydet
            $order->status = $newStatus;
            R::store($order);

            // Arşivlenmiş duruma geçtiyse, özel bir flag gönderelim (JS'in satırı kaldırması için)
            $isArchived = in_array($newStatus, $this->archivedStatuses);

            $this->jsonResponse([
                'success' => true, 
                'message' => 'Durum güncellendi: ' . $newStatus, 
                'new_status' => $newStatus,
                'is_archived' => $isArchived 
            ]);

        } catch (\Exception $e) {
            error_log("Order status update error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Durum güncellenirken bir hata oluştu.'], 500);
        }
    }

    /**
     * İki durum arasındaki geçişin geçerli olup olmadığını kontrol eder.
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // İptal her zaman mümkün (aktif durumlardan)
        if ($newStatus === 'cancelled' && in_array($currentStatus, $this->activeStatuses)) {
            return true;
        }
        
        // Diğer geçerli geçişler
        $transitions = [
            'bekliyor' => ['preparing'],
            'preparing' => ['ready'],
            'ready' => ['delivered']
            // delivered ve cancelled'dan başka geçiş yok
        ];

        return isset($transitions[$currentStatus]) && in_array($newStatus, $transitions[$currentStatus]);
    }

    /**
     * Gün sonu işlemini gerçekleştirir: Rapor oluşturur, indirir ve siparişleri siler.
     */
    public function endOfDayProcess(): void
    {
        // Yetki kontrolü __construct içinde yapıldı

        // === BAŞLANGIÇ KONTROLÜ: Aktif sipariş var mı? ===
        $statusPlaceholders = R::genSlots($this->activeStatuses);
        $activeOrderCount = R::count('order', "status IN ($statusPlaceholders)", $this->activeStatuses);

        if ($activeOrderCount > 0) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Gün sonu işlemi yapılamaz. Tamamlanmamış (aktif) siparişler bulunmaktadır.'];
            $this->redirect('/admin');
            exit;
        }
        // === KONTROL SONU ===

        try {
            // 1. Tüm siparişleri çek (ilişkili ürünlerle birlikte)
            // Aktif sipariş olmaması gerektiği için sadece arşivlenmişleri çekmek de yeterli olabilir,
            // ama güvenlik için tümünü çekip filtrelemek daha sağlam.
            $allOrders = R::findAll('order');
            
            $deliveredOrdersData = [];
            $cancelledOrdersData = [];
            $totalRevenue = 0.0;

            foreach ($allOrders as $orderBean) {
                // Her siparişin kalemlerini çek (N+1 sorgu riski - optimizasyon gerekebilir)
                $orderItems = R::findAll('orderitem', 'order_id = ?', [$orderBean->id]);
                $itemsDetailsText = "";
                $orderTotalFromItems = 0.0;

                foreach ($orderItems as $item) {
                    $product = R::load('product', $item->product_id);
                    $productName = $product->id ? $product->name : '[Silinmiş Ürün]';
                    $itemTotal = $product->id ? ($item->price_per_item * $item->quantity) : 0;
                    $orderTotalFromItems += $itemTotal;
                    $itemsDetailsText .= sprintf(
                        "    - %s (%d Adet x %.2f TL) = %.2f TL\n",
                        $productName,
                        $item->quantity,
                        $item->price_per_item,
                        $itemTotal
                    );
                }
                
                $orderData = [
                    'id' => $orderBean->id,
                    'customer_info' => $orderBean->customer_info,
                    'created_at' => $orderBean->created_at,
                    'items_text' => empty(trim($itemsDetailsText)) ? "    (Ürün bilgisi yok)\n" : $itemsDetailsText,
                    'total_price' => $orderBean->total_price // Bean'deki kaydedilmiş toplamı kullanalım
                ];

                if ($orderBean->status === 'delivered') {
                    $deliveredOrdersData[] = $orderData;
                    $totalRevenue += (float)$orderBean->total_price;
                } elseif ($orderBean->status === 'cancelled') {
                    $cancelledOrdersData[] = $orderData;
                }
                // Diğer durumlar (bekliyor, preparing, ready) rapora dahil edilmiyor
            }

            // 2. Rapor içeriğini oluştur
            $reportDate = date('Y-m-d H:i:s');
            $reportContent = "LOKANTA SİPARİŞ SİSTEMİ - GÜN SONU RAPORU\n";
            $reportContent .= "=============================================\n";
            $reportContent .= "Rapor Tarihi: {$reportDate}\n";
            $reportContent .= "=============================================\n\n";

            $reportContent .= "--- TESLİM EDİLEN SİPARİŞLER (" . count($deliveredOrdersData) . " Adet) ---\n";
            if (!empty($deliveredOrdersData)) {
                foreach ($deliveredOrdersData as $order) {
                    $reportContent .= sprintf(
                        "Sipariş ID: %d | Müşteri: %s | Tarih: %s | Toplam: %.2f TL\n%s",
                        $order['id'],
                        $order['customer_info'],
                        $order['created_at'],
                        $order['total_price'],
                        $order['items_text']
                    );
                    $reportContent .= "---------------------------------------------\n";
                }
            } else {
                $reportContent .= "(Bugün teslim edilen sipariş yok)\n";
            }
            $reportContent .= "\n";

            $reportContent .= "--- İPTAL EDİLEN SİPARİŞLER (" . count($cancelledOrdersData) . " Adet) ---\n";
             if (!empty($cancelledOrdersData)) {
                foreach ($cancelledOrdersData as $order) {
                    $reportContent .= sprintf(
                        "Sipariş ID: %d | Müşteri: %s | Tarih: %s | Tutar: %.2f TL\n%s",
                        $order['id'],
                        $order['customer_info'],
                        $order['created_at'],
                        $order['total_price'],
                        $order['items_text']
                    );
                     $reportContent .= "---------------------------------------------\n";
                }
            } else {
                $reportContent .= "(Bugün iptal edilen sipariş yok)\n";
            }
            $reportContent .= "\n";

            $reportContent .= "--- GÜNLÜK ÖZET ---\n";
            $reportContent .= sprintf("Toplam Hasılat (Teslim Edilenler): %.2f TL\n", $totalRevenue);
            // Şimdilik Brüt Gelir = Hasılat varsayıyoruz, maliyet hesaplaması yok.
            $reportContent .= sprintf("Toplam Brüt Gelir (Tahmini): %.2f TL\n", $totalRevenue); 
            $reportContent .= "=============================================\n";

            // 3. İndirme başlıklarını ayarla ve içeriği gönder
            // ÖNEMLİ: Başlıklar gönderildikten sonra veritabanı silme işlemi yapılmalı!
            $filename = "gun_sonu_raporu_" . date('Y_m_d') . ".txt";
            header('Content-Description: File Transfer');
            header('Content-Type: text/plain; charset=utf-8'); // UTF-8 ekledik
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . mb_strlen($reportContent, 'UTF-8')); // mb_strlen kullandık
            flush(); // PHP buffer'ını gönder
            
            echo $reportContent;
            
            // 4. TÜM Siparişleri ve Sipariş Kalemlerini Sil
            // Bu işlem geri alınamaz!
            R::wipe('orderitem'); // Önce ilişkili kalemleri sil
            R::wipe('order');     // Sonra ana siparişleri sil
            
            exit; // İşlem bitti, script sonlansın

        } catch (\Exception $e) {
            // Hata durumunda kullanıcıya bilgi ver (indirme başlamadıysa)
            error_log("End of Day Process Error: " . $e->getMessage());
            // İndirme başlıkları gönderilmediyse hata mesajı gösterebiliriz.
            if (!headers_sent()) {
                 // Basit bir hata sayfası veya mesajı gösterilebilir.
                 // Bu senaryoda admin dashboard'a hata mesajıyla yönlendirmek daha iyi olabilir.
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Gün sonu işlemi sırasında bir hata oluştu: ' . $e->getMessage()];
                 $this->redirect('/admin');
                 exit;
            } else {
                // Başlıklar zaten gönderildiyse yapacak pek bir şey yok, loglama yeterli.
                exit;
            }
        }
    }
}

?> 