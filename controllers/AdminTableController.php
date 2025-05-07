<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Controllers\AuthController;
use \RedBeanPHP\R as R;

class AdminTableController extends BaseController
{
    public function __construct()
    {
        AuthController::requireAdmin(); // Bu controller için admin girişi zorunlu
    }

    /**
     * Masa listesini gösterir.
     */
    public function index(): void
    {
        $tables = R::findAll('table', 'ORDER BY name ASC');

        $this->view('admin/tables/index', [
            'pageTitle' => 'Masa Yönetimi',
            'tables' => $tables
        ], 'admin');
    }

    /**
     * Yeni masayı veritabanına kaydeder (AJAX ile çağrılması beklenir).
     */
    public function store(): void
    {
        $tableName = trim($this->sanitize($_POST['table_name'] ?? ''));

        if (empty($tableName)) {
            $this->jsonResponse(['success' => false, 'message' => 'Masa adı boş olamaz.'], 400);
            return;
        }

        // Aynı isimde başka bir masa var mı kontrol et
        $existingTable = R::findOne('table', 'name = ?', [$tableName]);
        if ($existingTable) {
            $this->jsonResponse(['success' => false, 'message' => 'Bu isimde bir masa zaten mevcut.'], 409); // 409 Conflict
            return;
        }

        try {
            $table = R::dispense('table');
            $table->name = $tableName;
            $table->is_active = true; // Varsayılan olarak aktif
            $table->created_at = date('Y-m-d H:i:s');
            $table->updated_at = date('Y-m-d H:i:s');
            $id = R::store($table);

            // Başarılı yanıt için yeni oluşturulan masa verilerini de gönderelim
            $newTableData = [
                'id' => $id,
                'name' => htmlspecialchars($table->name),
                'is_active' => $table->is_active,
                // JavaScript tarafında HTML oluşturmak için gerekebilecek diğer alanlar
            ];

            $this->jsonResponse(['success' => true, 'message' => 'Masa başarıyla eklendi.', 'new_table' => $newTableData]);
        } catch (\Exception $e) {
            error_log("Table store error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Masa eklenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Bir masayı siler (AJAX ile çağrılması beklenir).
     */
    public function delete(): void
    {
        $tableId = filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$tableId) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz Masa ID.'], 400);
            return;
        }

        try {
            $table = R::load('table', $tableId);

            if (!$table->id) {
                $this->jsonResponse(['success' => false, 'message' => 'Silinecek masa bulunamadı.'], 404);
                return;
            }

            // Bu masaya atanmış aktif sipariş var mı kontrol et
            // (status 'delivered' veya 'cancelled' olmayan siparişler)
            $activeOrderCount = R::count('order', 'table_id = ? AND status NOT IN (\'delivered\', \'cancelled\')', [$tableId]);

            if ($activeOrderCount > 0) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Bu masaya atanmış (' . $activeOrderCount . ' adet) aktif sipariş bulunduğu için silinemez.'
                ], 409); // Conflict
                return;
            }

            R::trash($table); // Masayı sil
            $this->jsonResponse(['success' => true, 'message' => 'Masa başarıyla silindi.']);

        } catch (\Exception $e) {
            error_log("Table delete error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Masa silinirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Bir masanın aktif/pasif durumunu değiştirir (AJAX ile çağrılması beklenir).
     */
    public function toggleStatus(): void
    {
        $tableId = filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$tableId) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz Masa ID.'], 400);
            return;
        }

        try {
            $table = R::load('table', $tableId);

            if (!$table->id) {
                $this->jsonResponse(['success' => false, 'message' => 'Durumu değiştirilecek masa bulunamadı.'], 404);
                return;
            }

            $table->is_active = !$table->is_active; // Durumu tersine çevir
            $table->updated_at = date('Y-m-d H:i:s');
            R::store($table);

            $this->jsonResponse([
                'success' => true, 
                'message' => 'Masa durumu başarıyla güncellendi.', 
                'new_status' => $table->is_active, // Yeni durumu JS'e bildir
                'table_id' => $table->id
            ]);

        } catch (\Exception $e) {
            error_log("Table toggle status error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Masa durumu güncellenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    // Diğer metodlar (edit, update, delete, toggleStatus) buraya eklenecek
}

?> 