<div class="login-container card">
    <div class="card-body">
        <h2 class="text-center"><?php echo htmlspecialchars($pageTitle ?? 'Yönetim Paneli Girişi'); ?></h2>

        <?php 
        // General flash messages (success or error)
        if (isset($_SESSION['flash_message'])):
            $flash = $_SESSION['flash_message'];
        ?>
            <div class="message message-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i> 
                <?php echo htmlspecialchars($flash['text']); ?>
            </div>
        <?php 
            unset($_SESSION['flash_message']); // Mesajı gösterdikten sonra sil
        endif;
        
        // Remove old error message blocks for login_error and auth_error
        ?>

        <form action="/login" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password">Şifre:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-sign-in-alt"></i> Giriş Yap</button>
        </form>
    </div>

    <?php if (isset($redirectToAdminAfterDelay) && $redirectToAdminAfterDelay): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin already logged in. Redirecting to /admin in 1 second...');
            setTimeout(function() {
                window.location.href = '/admin';
            }, 1000); // 1000 milliseconds = 1 second
        });
    </script>
    <?php endif; ?>

</div> 