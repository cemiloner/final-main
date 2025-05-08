<?php
use App\Controllers\UserAuthController; // Make controller available
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Hello Pastane' : 'Hello Pastane'; ?></title>
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
    <style>
        /* Basic styles for toggle button and nav visibility - move to style.css later if preferred */
        #nav-toggle-btn { display: none; } /* Initially hidden, shown via media query */
        header nav#main-nav { display: block; } /* Initially visible on desktop */

        @media (max-width: 768px) { /* Adjust breakpoint as needed */
            header .container {
                position: relative; /* Ensure button positioning is relative to container */
            }
            #nav-toggle-btn {
                display: block;
                position: absolute;
                top: 50%;
                right: var(--spacing-unit);
                transform: translateY(-50%);
                background: none;
                border: none;
                font-size: 1.8rem; /* Adjust size */
                cursor: pointer;
                color: var(--accent-color);
                padding: 5px;
                z-index: 1100;
            }
            header nav#main-nav {
                display: none; /* Hide nav by default on small screens */
                position: absolute;
                top: 100%; /* Position below the header */
                left: 0;
                width: 100%;
                background-color: var(--container-bg);
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
                padding: var(--spacing-unit);
                border-top: 1px solid var(--border-color);
                flex-direction: column; /* Stack links vertically */
                align-items: flex-start; /* Align links to the left */
            }
            header nav#main-nav.nav-active {
                display: flex; /* Show nav when active */
            }
             header nav#main-nav a {
                margin-left: 0; /* Reset margin */
                padding: 0.75rem 0; /* Add padding for vertical links */
                width: 100%; /* Make links full width */
                border-bottom: 1px solid var(--border-color); /* Separator */
            }
             header nav#main-nav a:last-child {
                border-bottom: none; /* Remove border from last link */
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/"><i class="fas fa-birthday-cake"></i> Hello Pastane</a></h1>
            <!-- Add Menu Toggle Button -->
            <button id="nav-toggle-btn" aria-label="Menüyü Aç/Kapat" aria-expanded="false">
                <i class="fas fa-bars"></i> <!-- Hamburger Icon -->
            </button>
            <nav id="main-nav"> <!-- Add ID to nav -->
                <a href="/menu"><i class="fas fa-book-open"></i> Menü</a>
                <?php 
                // Get the current path without query string
                // This variable is used below as well
                $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                
                // Show Feedback link on all customer pages except /home
                if ($currentPath !== 'home' && $currentPath !== ''): // Also exclude root path which might be home
                ?>
                <a href="/home"><i class="fas fa-comment-dots"></i> Görüşlerinizi Bildirin</a>
                <?php endif; ?>
                
                <?php if (UserAuthController::isUserLoggedIn()): ?>
                    <a href="/userlogout"><i class="fas fa-sign-out-alt"></i> Müşteri Çıkış</a>
                <?php else: ?>
                    <a href="/userlogin"><i class="fas fa-sign-in-alt"></i> Müşteri Girişi</a>
                <?php endif; ?>
                <?php 
                // Show Admin link only on the userlogin page
                if ($currentPath === 'userlogin'): 
                ?>
                <a href="/login" aria-label="Admin Paneli"><i class="fas fa-user-shield"></i></a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <?php echo $content; // View içeriği buraya gelecek ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Hello Pastane</p>
    </footer>
    <script src="/js/app.js"></script>

    <!-- Add JS for Nav Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggleBtn = document.getElementById('nav-toggle-btn');
            const mainNav = document.getElementById('main-nav');

            if (navToggleBtn && mainNav) {
                navToggleBtn.addEventListener('click', function() {
                    const isExpanded = mainNav.classList.toggle('nav-active');
                    this.setAttribute('aria-expanded', isExpanded);
                    // Optional: Change icon to 'X' when open
                    const icon = this.querySelector('i');
                    if (isExpanded) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                // Optional: Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideNav = mainNav.contains(event.target);
                    const isClickOnToggle = navToggleBtn.contains(event.target) || event.target === navToggleBtn;
                    
                    if (mainNav.classList.contains('nav-active') && !isClickInsideNav && !isClickOnToggle) {
                        mainNav.classList.remove('nav-active');
                        navToggleBtn.setAttribute('aria-expanded', 'false');
                        const icon = navToggleBtn.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
            }
        });
    </script>
    <!-- End JS for Nav Toggle -->

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