<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Paneli'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><a href="/admin"><i class="fas fa-user-shield"></i> Admin Paneli</a></h1>
            <nav>
                <a href="/admin"><i class="fas fa-tachometer-alt"></i> Panel</a>
                <a href="/admin/orders"><i class="fas fa-clipboard-list"></i> Siparişler</a>
                <a href="/admin/products"><i class="fas fa-box-open"></i> Ürünler</a>
                <a href="/admin/tables"><i class="fas fa-chair"></i> Masa Yönetimi</a>
                <a href="/"><i class="fas fa-home"></i> Siteye Dön</a>
                <a href="/logout"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <?php echo $content; // View içeriği buraya gelecek ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Pastane Sipariş Sistemi - Admin</p>
    </footer>
    <script src="/js/app.js"></script>
</body>
</html> 