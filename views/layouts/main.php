<?php
use App\Controllers\UserAuthController; // Make controller available
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Pastane Sipariş'; ?></title>
    <!-- Favicon Links -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <!-- End Favicon Links -->
    <link rel="icon" href="/images/cake.png" type="image/png">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/"><i class="fas fa-birthday-cake"></i> Pastane Sipariş</a></h1>
            <nav>
                <a href="/menu"><i class="fas fa-book-open"></i> Menü</a>
                <?php if (UserAuthController::isUserLoggedIn()): ?>
                    <a href="/userlogout"><i class="fas fa-sign-out-alt"></i> Müşteri Çıkış</a>
                <?php else: ?>
                    <a href="/userlogin"><i class="fas fa-sign-in-alt"></i> Müşteri Girişi</a>
                <?php endif; ?>
                <a href="/login"><i class="fas fa-user-shield"></i> Admin</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <?php echo $content; // View içeriği buraya gelecek ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pastane Sipariş Sistemi</p>
    </footer>
    <script src="/js/app.js"></script>

<?php
  error_log("main.php: Checking session. \$ इतना _SESSION['js_redirect_url'] is: " . ($_SESSION['js_redirect_url'] ?? 'NOT SET AT CHECKPOINT 1'));
  // Check for JS redirect at the end of the main layout
  if (isset($_SESSION['js_redirect_url'])) {
    $js_redirect_url_global = $_SESSION['js_redirect_url'];
    error_log("main.php: \$ इतना _SESSION['js_redirect_url'] was [" . $js_redirect_url_global . "]. Unsetting it. \$js_redirect_url_global is now [" . ($js_redirect_url_global ?? 'UNSET OR NULL') . "]");
    unset($_SESSION['js_redirect_url']);
  } else {
    error_log("main.php: \$ इतना _SESSION['js_redirect_url'] was NOT SET AT CHECKPOINT 2.");
    // $js_redirect_url_global değişkeninin tanımlı olmasını sağlamak için burada boş bir değere ayarlayabiliriz.
    $js_redirect_url_global = null;
  }
?>
<?php if (isset($js_redirect_url_global) && $js_redirect_url_global): ?>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Allow a very brief moment for content to render, then redirect.
        // This helps if there are flash messages that should be seen.
        // Adjust delay as needed, or set to 0 for immediate redirect.
        setTimeout(function() {
            window.location.href = '<?php echo $js_redirect_url_global; ?>';
        }, 0); // 0ms delay, changed from 50ms
    });
</script>
<?php endif; ?>

</body>
</html> 