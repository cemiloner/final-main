<?php

namespace App\Controllers;

use App\Core\BaseController;

class AuthController extends BaseController
{
    private const ADMIN_USERNAME = 'admin'; // Basit kullanıcı adı
    private const ADMIN_PASSWORD = 'password123'; // Güvensiz! Gerçek projede hashlenmeli.

    /**
     * Giriş formunu gösterir.
     */
    public function showLoginForm(): void
    {
        // Eğer zaten giriş yapmışsa admin paneline yönlendir
        if ($this->isAdminLoggedIn()) {
            $this->redirect('/admin/orders');
            return;
        }
        $this->view('auth/login', ['pageTitle' => 'Admin Girişi'], 'main'); // Basit bir login view kullanalım
    }

    /**
     * Giriş işlemini yapar.
     */
    public function login(): void
    {
        if ($this->isAdminLoggedIn()) {
            $this->redirect('/admin/orders');
            return;
        }

        $username = $this->sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Şifreyi sanitize etmiyoruz (karşılaştırma için ham hali lazım)

        if ($username === self::ADMIN_USERNAME && $password === self::ADMIN_PASSWORD) {
            // Giriş başarılı, session başlat
            $_SESSION['is_admin'] = true;
            session_write_close(); // Session yazmayı kapat
            header('Location: /admin/orders'); // Doğrudan yönlendirme
            exit; // Betiği sonlandır
        } else {
            // Giriş başarısız
            $_SESSION['login_error'] = 'Geçersiz kullanıcı adı veya şifre.';
            session_write_close(); // Session yazmayı kapat
            header('Location: /login'); // Doğrudan yönlendirme
            exit; // Betiği sonlandır
        }
    }

    /**
     * Çıkış işlemini yapar.
     */
    public function logout(): void
    {
        session_unset(); // Tüm session değişkenlerini sil
        session_destroy(); // Session'ı yok et
        $this->redirect('/login'); // Giriş sayfasına yönlendir
    }

    /**
     * Adminin giriş yapıp yapmadığını kontrol eder.
     */
    public static function isAdminLoggedIn(): bool
    { return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Admin değilse giriş sayfasına yönlendirir.
     * Controller içinde veya router'da middleware olarak kullanılabilir.
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdminLoggedIn()) {
            // Hata mesajı ayarlanabilir
            $_SESSION['auth_error'] = 'Bu sayfaya erişmek için giriş yapmalısınız.';
            header('Location: /login'); // BaseController'daki redirect burada kullanılamaz (statik metod)
            exit;
        }
    }
}

?> 