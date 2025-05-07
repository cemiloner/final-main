<?php

namespace App\Controllers;

use App\Core\BaseController;
use \RedBeanPHP\R as R;

class UserAuthController extends BaseController
{
    /**
     * Displays the user login form.
     */
    public function showUserLoginForm(): void
    {
        if (self::isUserLoggedIn()) {
            $this->redirect('/menu');
        }
        $this->view('auth/userlogin', ['pageTitle' => 'Müşteri Girişi'], 'main');
    }

    /**
     * Handles the user login process.
     */
    public function loginUser(): void
    {
        if (self::isUserLoggedIn()) {
            $this->redirect('/menu');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/userlogin');
            return;
        }

        // Use a generic identifier for login: username or phone_number
        $identifier = $this->sanitize($_POST['login_identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Kullanıcı adı/telefon ve şifre alanları zorunludur.'];
            $this->redirect('/userlogin');
            return;
        }

        // Find user by username or phone number, ensuring they are a customer
        $user = R::findOne('user', '(username = ? OR phone_number = ?) AND user_type = ?', [$identifier, $identifier, 'customer']);

        if ($user && password_verify($password, $user->password)) {
            // Login successful
            $_SESSION['current_user_id'] = $user->id;
            $_SESSION['current_username'] = $user->username;
            $_SESSION['current_user_role'] = 'customer'; // Explicitly set role
            
            session_regenerate_id(true); // Security measure

            // DEBUGGING: Log session state after login
            error_log('[DEBUG UserAuth] Login successful. Session state: ' . print_r($_SESSION, true));
            
            // Check for a redirect URL from session (e.g., if they tried to access a protected page)
            $redirectUrl = $_SESSION['redirect_to_after_login'] ?? '/menu';
            unset($_SESSION['redirect_to_after_login']);

            $_SESSION['js_redirect_url'] = $redirectUrl;
            error_log('[UserAuthController loginUser SUCCESS] js_redirect_url set to: ' . $redirectUrl);
        } else {
            // Login failed
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Geçersiz kullanıcı adı/telefon veya şifre, ya da kullanıcı tipi uygun değil.'];
            $this->redirect('/userlogin');
            return;
        }
    }

    /**
     * Logs the current user out.
     */
    public function logoutUser(): void
    {
        unset($_SESSION['current_user_id']);
        unset($_SESSION['current_username']);
        unset($_SESSION['current_user_role']);
        
        session_regenerate_id(true);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Başarıyla çıkış yaptınız.'];
        $this->redirect('/userlogin');
    }

    /**
     * Checks if a regular user is logged in.
     */
    public static function isUserLoggedIn(): bool
    {
        // DEBUGGING: Log session state at the beginning of isUserLoggedIn
        error_log('[DEBUG UserAuth] isUserLoggedIn check. Session state: ' . print_r($_SESSION, true));
        error_log('[DEBUG UserAuth] isset current_user_id: ' . (isset($_SESSION['current_user_id']) ? 'true' : 'false'));
        error_log('[DEBUG UserAuth] isset current_user_role: ' . (isset($_SESSION['current_user_role']) ? 'true' : 'false'));
        if (isset($_SESSION['current_user_role'])) {
            error_log('[DEBUG UserAuth] current_user_role value: ' . $_SESSION['current_user_role']);
            error_log('[DEBUG UserAuth] current_user_role === \'customer\': ' . ($_SESSION['current_user_role'] === 'customer' ? 'true' : 'false'));
        }

        return isset($_SESSION['current_user_id']) && isset($_SESSION['current_user_role']) && $_SESSION['current_user_role'] === 'customer';
    }

    /**
     * Gets the logged-in user's data.
     * Returns an array with user data or null if not logged in.
     */
    public static function getLoggedInUser(): ?array
    {
        if (!self::isUserLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['current_user_id'],
            'username' => $_SESSION['current_username'],
            'role' => $_SESSION['current_user_role']
            // Add other fields if needed and stored in session
        ];
    }

    /**
     * If user is not logged in, redirects to the user login page.
     * Stores the intended URL to redirect back after login.
     */
    public static function requireUserLogin(): void
    {
        if (!self::isUserLoggedIn()) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Bu sayfayı görüntülemek için giriş yapmalısınız.'];
            $_SESSION['redirect_to_after_login'] = $_SERVER['REQUEST_URI'] ?? '/menu';
            // Use direct header for static method redirect
            // header('Location: /userlogin');
            // exit;
            $_SESSION['js_redirect_url'] = '/userlogin';
        }
    }

    /**
     * Displays the user registration form.
     */
    public function showUserRegistrationForm(): void
    {
        if (self::isUserLoggedIn()) {
            $this->redirect('/menu');
            return;
        }
        // Pass old input and errors to the view if they exist
        $data = [
            'pageTitle' => 'Müşteri Kayıt Ol',
            'old' => $_SESSION['old_form_data'] ?? [],
            'errors' => $_SESSION['validation_errors'] ?? []
        ];
        unset($_SESSION['old_form_data']);
        unset($_SESSION['validation_errors']);

        $this->view('auth/userregister', $data, 'main');
    }

    /**
     * Handles the user registration process.
     */
    public function registerUser(): void
    {
        if (self::isUserLoggedIn()) {
            $this->redirect('/menu');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/userregister');
            return;
        }

        $username = $this->sanitize($_POST['username'] ?? '');
        $phone_number = $this->sanitize($_POST['phone_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $_SESSION['old_form_data'] = $_POST; // Store for pre-filling form
        $errors = [];

        // Validation
        if (empty($username)) {
            $errors['username'] = 'Kullanıcı adı zorunludur.';
        }
        if (empty($phone_number)) {
            $errors['phone_number'] = 'Telefon numarası zorunludur.';
        } elseif (!preg_match('/^\+905[0-9]{9}$/', $phone_number)) {
            $errors['phone_number'] = 'Telefon numarası +905XXXXXXXXX formatında olmalıdır.';
        }
        if (empty($password)) {
            $errors['password'] = 'Şifre zorunludur.';
        }
        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Şifreler eşleşmiyor.';
        }

        // Check for duplicate username or phone number
        if (empty($errors)) {
            $existingUserByUsername = R::findOne('user', 'username = ?', [$username]);
            if ($existingUserByUsername) {
                $errors['username'] = 'Bu kullanıcı adı zaten kayıtlı.';
            }
            $existingUserByPhone = R::findOne('user', 'phone_number = ?', [$phone_number]);
            if ($existingUserByPhone) {
                $errors['phone_number'] = 'Bu telefon numarası zaten kayıtlı.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Lütfen formdaki hataları düzeltin.'];
            $this->redirect('/userregister');
            return;
        }

        // Create user
        $user = R::dispense('user');
        $user->username = $username;
        $user->phone_number = $phone_number;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->user_type = 'customer'; // Default new registrations to customer
        $user->created_at = date('Y-m-d H:i:s');
        
        try {
            R::store($user);
            unset($_SESSION['old_form_data']); // Clear form data on success
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Başarıyla kayıt oldunuz! Şimdi giriş yapabilirsiniz.'];
            
            $_SESSION['js_redirect_url'] = '/userlogin'; // Set for JS redirect
            error_log('[UserAuthController registerUser SUCCESS] js_redirect_url set to: /userlogin');
            
        } catch (\Exception $e) {
            // Log error: error_log("User registration failed: " . $e->getMessage());
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.'];
            $this->redirect('/userregister');
            return; // Ensure script termination after redirect
        }
    }
}

?> 