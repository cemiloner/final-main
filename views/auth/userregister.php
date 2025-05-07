<?php 
// Ensure $pageTitle, $errors, and $old are available, even if empty
$pageTitle = $pageTitle ?? 'Müşteri Kayıt Ol';
$errors = $errors ?? [];
$old = $old ?? [];

function display_error($field, $errors) {
    if (isset($errors[$field])) {
        echo '<small class="error-text">' . htmlspecialchars($errors[$field]) . '</small>';
    }
}

function old_value($field, $old_data, $default = '') {
    return htmlspecialchars($old_data[$field] ?? $default);
}
?>

<div class="login-container card"> <!-- Reusing login-container style for consistency -->
    <div class="card-body">
        <h2 class="text-center"><?php echo htmlspecialchars($pageTitle); ?></h2>

        <?php 
        if (isset($_SESSION['flash_message'])):
            $flash = $_SESSION['flash_message'];
        ?>
            <div class="message message-<?php echo htmlspecialchars($flash['type']); ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i> 
                <?php echo htmlspecialchars($flash['text']); ?>
            </div>
        <?php 
            unset($_SESSION['flash_message']);
        endif;
        ?>

        <form action="/userregister" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" id="username" name="username" value="<?php echo old_value('username', $old); ?>" required>
                <?php display_error('username', $errors); ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="phone_number">Telefon Numarası (+905XXXXXXXXX):</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo old_value('phone_number', $old); ?>" placeholder="+905XXXXXXXXX" required>
                <?php display_error('phone_number', $errors); ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="password">Şifre:</label>
                <input type="password" id="password" name="password" required>
                <?php display_error('password', $errors); ?>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password_confirm">Şifre Tekrar:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <?php display_error('password_confirm', $errors); ?>
            </div>

            <button type="submit" class="btn btn-primary-solid" style="width: 100%;"><i class="fas fa-user-plus"></i> Kayıt Ol</button>
        </form>

        <div class="text-center" style="margin-top: 20px;">
            <p>Zaten hesabınız var mı? <a href="/userlogin">Giriş Yapın</a></p>
        </div>
    </div>
</div> 