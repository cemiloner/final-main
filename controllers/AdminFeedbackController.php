<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Controllers\AuthController; // For requireAdmin()
// use \RedBeanPHP\R as R; // RedBeanPHP artık kullanılmıyor
use \PDO;
use \PDOException;
include("admin/feedback.php");
class AdminFeedbackController extends BaseController
{
    private ?PDO $db = null;

    public function __construct()
    {
        AuthController::requireAdmin(); // Bu controller için admin girişi zorunlu
    }

    // PDO bağlantısını alacak yardımcı metod
    private function getDbConnection(): ?PDO
    {
        if ($this->db === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $dbName = getenv('DB_DATABASE') ?: 'lokanta_db';
            $user = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";

            try {
                $this->db = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // View ile uyumlu olması için OBJ olarak fetch edelim
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log("PDO Connection Error in AdminFeedbackController: " . $e->getMessage());
                return null;
            }
        }
        return $this->db;
    }

    /**
     * Displays the feedback list page.
     */
    public function index(): void
    {
        $feedbacks = []; // Başlangıçta boş dizi
        $error = null; // Hata mesajı için

        $pdo = $this->getDbConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
                $feedbacks = $stmt->fetchAll(); // FETCH_OBJ varsayılan olduğu için direkt çalışır
            } catch (PDOException $e) {
                error_log("Error fetching feedback: " . $e->getMessage());
                $error = "Geri bildirimler alınırken bir hata oluştu.";
            }
        } else {
            $error = "Veritabanı bağlantısı kurulamadı.";
        }

        $this->view('admin/feedback', [
            'pageTitle' => 'Müşteri Geri Bildirimleri',
            'feedbacks' => $feedbacks,
            'error_message' => $error // Hata mesajını view'e gönder
        ], 'admin'); // 'admin' layout'unu kullan
    }

    /**
     * Deletes all feedback entries.
     * This method should be called via a POST request.
     */
    public function deleteAll(): void
    {
        AuthController::requireAdmin(); // Ensure admin is logged in

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Optionally handle non-POST requests, e.g., redirect or show error
            $_SESSION['error_message'] = "Geçersiz istek yöntemi.";
            echo '<script>window.location.replace("/feedback")</script>';
            exit();
        }

        $pdo = $this->getDbConnection();
        if (!$pdo) {
            $_SESSION['error_message'] = "Veritabanı bağlantısı kurulamadı.";
            echo '<script>window.location.replace("/feedback")</script>';
            exit();
        }

        try {
            // Directly execute the delete query
            $sql = "DELETE FROM feedback"; // Assumes table name is 'feedback'
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Tüm geri bildirimler başarıyla silindi.";
            } else {
                // This case might be rare if execute() throws an exception on major failure
                $_SESSION['error_message'] = "Geri bildirimler silinirken bir sorun oluştu (işlem başarısız).";
            }
        } catch (PDOException $e) {
            error_log("PDOException while deleting all feedbacks: " . $e->getMessage());
            $_SESSION['error_message'] = "Veritabanı hatası oluştu: Geri bildirimler silinemedi. Detay: " . $e->getMessage();
        } catch (Exception $e) { // Catch any other unexpected exceptions
            error_log("Unexpected error during deleteAllFeedbacks: " . $e->getMessage());
            $_SESSION['error_message'] = "Beklenmedik bir genel hata oluştu: Geri bildirimler silinemedi. Detay: " . $e->getMessage();
        }
        
        header("Location: /admin/feedback");
        exit;
    }
}
?> 