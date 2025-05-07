<?php

namespace App\Controllers;


use App\Core\BaseController;
// Removed: use \RedBeanPHP\R as R;
include("admin.php");
include("logout.php");
class AuthController extends BaseController
{
    // Restored hardcoded admin credentials
    private const ADMIN_USERNAME = 'admin'; 
    private const ADMIN_PASSWORD = 'sifre123';  

    /**
     * Giriş formunu gösterir.
     */
    public function showLoginForm(): void
    {
        $data = ['pageTitle' => 'Admin Girişi'];

        if (self::isAdminLoggedIn()) {
            // Instead of PHP redirect, set a flag for the view to handle with JS
            $data['redirectToAdminAfterDelay'] = true;
            // No PHP redirect here: // $this->redirect('/admin'); 
            // The return; is also not strictly needed anymore if not redirecting, 
            // but keeping it doesn't harm if we imagine other logic might be added.
        }
        
        $this->view('auth/login', $data, 'main'); 
    }

    /**
     * Giriş işlemini yapar.
     */
    public function login(): void
    {
        if (self::isAdminLoggedIn()) {
            $this->redirect('/admin');
            exit; // Ensure script termination
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            exit; // Ensure script termination
        }

        $username = $this->sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Password is not sanitized for comparison

        if ($username === self::ADMIN_USERNAME && $password === self::ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            session_regenerate_id(true); // Regenerate session ID for security
            
            // -- Add these lines for debugging --
            error_log('DEBUG: Admin login success. Session is_admin set to: ' . (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'true' : 'false (or not set)'));
            error_log('DEBUG: Session ID after regenerate: ' . session_id());
            error_log('DEBUG: Attempting redirect to /admin from AuthController.');
            // -- End of added lines --
            header("Location:/admin");
           // echo '<script>window.location.replace("http://localhost:8000/admin")</script>';   // Redirect is not working, so we use header instead of $this->redirect  
            exit(); // Crucial: ensure no further script execution interferes with redirect
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Geçersiz kullanıcı adı veya şifre.'];
            // -- Add this line for debugging failed attempts --
            error_log('DEBUG: Admin login failed. Username entered: [' . $username . '], Password entered: [hidden]');
            // -- End of added line --
            $this->redirect('/login');
            exit; // Crucial: ensure no further script execution interferes with redirect
        }
    }

    /**
     * Çıkış işlemini yapar.
     */
    public function logout(): void
    {
        unset($_SESSION['is_admin']);
        session_regenerate_id(true);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Başarıyla çıkış yaptınız.'];
        $this->redirect('/login');
        exit; // Ensure no further script execution after redirect
    }

    /**
     * Adminin giriş yapıp yapmadığını kontrol eder.
     */
    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Admin değilse giriş sayfasına yönlendirir.
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdminLoggedIn()) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Bu sayfaya erişmek için giriş yapmalısınız.'];
            // In a static method, BaseController's redirect isn't available directly via $this
            // So we use a direct header call.
            header('Location: /login'); 
            exit;
        }
    }
    
    // Removed: showRegistrationForm(), register(), isLoggedIn(), getCurrentUser(), getCurrentUserType(), requireLogin()
}

?> 