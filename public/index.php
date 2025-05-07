<?php

declare(strict_types=1);
// ob_start(); // ÇIKTI ARABELLEĞE ALMA ARTIK GEREKLİ DEĞİL

// Proje Kök Dizinini Tanımla
define('ROOT_PATH', dirname(__DIR__));

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader'ı dahil et
require_once __DIR__ . '/../vendor/autoload.php';

// Hata Yönetimi (Geliştirme vs Üretim)
ini_set('display_errors', '0'); // Hataları tarayıcıda GÖSTERME
ini_set('display_startup_errors', '0'); // Başlangıç hatalarını GÖSTERME
error_reporting(E_ALL); // Tüm hataları raporla
ini_set('log_errors', '1'); // Hataları logla
// ini_set('error_log', __DIR__ . '/../logs/php_errors.log'); // Opsiyonel: Özel log dosyası

use App\Core\Router;
use App\Core\Database;

// Veritabanı bağlantısını kur
Database::connect();

// Router'ı başlat
$router = new Router();

// Rotaları yükle
require_once __DIR__ . '/../routes/web.php';

// İsteği al ve URI'ı temizle
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Rotaları dispatch et
$router->dispatch($requestUri, $requestMethod);

// Arabellek debug kodları kaldırıldı
// $output = ob_get_contents();
// file_put_contents(__DIR__ . '/output_debug.txt', $output);
// ob_end_flush(); 
