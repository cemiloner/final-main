<div class="login-container card">
    <div class="card-body">
        <h2 class="text-center"><?php echo htmlspecialchars($pageTitle); ?></h2>

        <?php 
        // Session'dan hata mesajlarını al ve göster
        if (isset($_SESSION['login_error'])):
        ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['login_error']); ?>
            </div>
        <?php 
            unset($_SESSION['login_error']); // Mesajı gösterdikten sonra sil
        endif;

        if (isset($_SESSION['auth_error'])):
        ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['auth_error']); ?>
            </div>
        <?php 
            unset($_SESSION['auth_error']); // Mesajı gösterdikten sonra sil
        endif;
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
</div> 