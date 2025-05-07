<div class="login-container card">
    <div class="card-body">
        <h2 class="text-center"><?php echo htmlspecialchars($pageTitle ?? 'Müşteri Girişi'); ?></h2>

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

        <form action="/userlogin" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="login_identifier">Kullanıcı Adı veya Telefon Numarası:</label>
                <input type="text" id="login_identifier" name="login_identifier" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password">Şifre:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary-solid" style="width: 100%;"><i class="fas fa-sign-in-alt"></i> Giriş Yap</button>
        </form>
        
        <div class="text-center" style="margin-top: 20px;">
            <p>Hesabınız yok mu? <a href="/userregister" class="btn btn-secondary-outline" style="display: inline-block; margin-top: 10px;"><i class="fas fa-user-plus"></i> Kayıt Olun</a></p>
        </div>
    </div>
</div> 

<?php
  // THIS BLOCK IS NOW REDUNDANT AND WILL BE REMOVED
  // // Check for JS redirect
  // if (isset($_SESSION['js_redirect_url'])) {
  //   $redirect_url = $_SESSION['js_redirect_url'];
  //   unset($_SESSION['js_redirect_url']); // Unset to prevent re-redirect
  // }
?>

<?php // if (isset($redirect_url) && $redirect_url): THIS BLOCK IS NOW REDUNDANT AND WILL BE REMOVED ?>
// <script>
//     document.addEventListener('DOMContentLoaded', function() {
//         // Small delay to ensure user sees messages, if any, or just for smoother transition
//         setTimeout(function() {
//             window.location.href = '<?php echo $redirect_url; ?>';
//         }, 100); // 100ms delay
//     });
// </script>
<?php // endif; THIS BLOCK IS NOW REDUNDANT AND WILL BE REMOVED ?> 