<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Lokanta Sipariş'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/"><i class="fas fa-utensils"></i> Lokanta Sipariş</a></h1>
            <nav>
                <a href="/menu"><i class="fas fa-book-open"></i> Menü</a>
                <a href="/admin"><i class="fas fa-user-shield"></i> Admin</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <?php echo $content; // View içeriği buraya gelecek ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Lokanta Sipariş Sistemi</p>
    </footer>
    <script src="/js/app.js"></script>
</body>
</html> 