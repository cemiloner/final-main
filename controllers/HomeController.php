<?php

namespace App\Controllers;

use App\Core\BaseController;
// use \RedBeanPHP\R as R; // RedBeanPHP artık kullanılmıyor
use \PDO;
use \PDOException;

class HomeController extends BaseController
{
    private ?PDO $db = null; // PDO bağlantısı için özellik

    // PDO bağlantısını alacak yardımcı metod (veya constructor'da alınabilir)
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
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Varsayılan fetch modu
                    PDO::ATTR_EMULATE_PREPARES => false, // Gerçek prepared statement kullan
                ]);
            } catch (PDOException $e) {
                error_log("PDO Connection Error in HomeController: " . $e->getMessage());
                // Burada null dönecek, çağıran metod kontrol etmeli
                return null;
            }
        }
        return $this->db;
    }

    /**
     * Displays the homepage.
     */
    public function index(): void
    {
        // Flash mesajlarını view'e gönder (varsa)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']);
        }

        $this->view('home', [
            'pageTitle' => 'Ana Sayfa - Pastane Sipariş',
            'flash_message' => $flashMessage,
            'errors' => $_SESSION['feedback_errors'] ?? [],
            'old' => $_SESSION['old_feedback_data'] ?? []
        ]);
        unset($_SESSION['feedback_errors']);
        unset($_SESSION['old_feedback_data']);
    }

    /**
     * Handles feedback form submission.
     */
    public function submitFeedback(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/home');
            exit;
        }

        $name = $this->sanitize($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $message = $this->sanitize($_POST['message'] ?? '');

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Ad alanı zorunludur.';
        }
        if (empty($email)) {
            $errors['email'] = 'E-posta alanı zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçersiz e-posta formatı.';
        }
        if (empty($message)) {
            $errors['message'] = 'Mesaj alanı zorunludur.';
        }

        if (!empty($errors)) {
            $_SESSION['feedback_errors'] = $errors;
            $_SESSION['old_feedback_data'] = $_POST; // Hatalı girişi tekrar doldurmak için
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Lütfen formdaki hataları düzeltin.'];
        } else {
            // Hata yoksa PDO ile veritabanına ekle
            $pdo = $this->getDbConnection();
            if ($pdo) {
                try {
                    $sql = "INSERT INTO feedback (name, email, message, created_at) VALUES (:name, :email, :message, :created_at)";
                    $stmt = $pdo->prepare($sql);
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindValue(':created_at', date('Y-m-d H:i:s')); // Değeri doğrudan bağla
                    
                    $stmt->execute();
                    
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Geri bildiriminiz için teşekkür ederiz!'];
                    // Başarılı kayıttan sonra eski form verilerini temizle
                    unset($_SESSION['old_feedback_data']); 

                } catch (PDOException $e) {
                    error_log('Feedback submission PDO error: ' . $e->getMessage());
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Geri bildirim gönderilirken bir veritabanı hatası oluştu.'];
                    // Hata durumunda form verilerini koru
                    $_SESSION['feedback_errors'] = $errors; // Gerçi bu blokta errors boş ama yine de ekleyelim
                    $_SESSION['old_feedback_data'] = $_POST; 
                }
            } else {
                 // Bağlantı hatası durumu
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Veritabanı bağlantı hatası nedeniyle geri bildirim gönderilemedi.'];
                 $_SESSION['feedback_errors'] = $errors;
                 $_SESSION['old_feedback_data'] = $_POST; 
            }
        }

        $this->redirect('/home#feedback-section');
        exit;
    }
} 