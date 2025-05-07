<?php

namespace App\Core;

use \RedBeanPHP\R as R;

class Database
{
    private static bool $connected = false;

    public static function connect(): void
    {
        if (self::$connected) {
            return; // Zaten bağlıysa tekrar bağlanma
        }

        // Ortam değişkenlerinden veritabanı bilgilerini al
        $host = getenv('DB_HOST') ?: 'localhost'; // Docker Compose servis adı veya varsayılan
        $port = getenv('DB_PORT') ?: '5432';
        $dbName = getenv('DB_DATABASE') ?: 'lokanta_db';
        $user = getenv('DB_USERNAME') ?: 'root'; // Varsayılan kullanıcı (güvenli değil)
        $password = getenv('DB_PASSWORD') ?: ''; // Varsayılan şifre (güvenli değil)

        // PostgreSQL için DSN (Data Source Name) oluştur
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";

        try {
            // RedBeanPHP bağlantısını kur
            // R::setup('sqlite:' . $dbPath); // Eski SQLite satırı
            R::setup($dsn, $user, $password);

            // Bağlantı başarılıysa işaretle
            self::$connected = true;

            // İlk çalıştırmada örnek veri ekle (eğer kategori yoksa)
            // Bu kısım PostgreSQL ile uyumluysa kalabilir veya güncellenebilir.
            self::seedInitialData();

            // İsteğe bağlı: Geliştirme sırasında şema değişikliklerini dondurmayı kapat
            // R::freeze(false); // Yeni tablolar/kolonlar otomatik oluşsun

            // Üretimde şemayı dondurmak iyi bir pratiktir:
            // if (getenv('APP_ENV') === 'production') {
            //     R::freeze(true);
            // }

        } catch (\Exception $e) {
            // Bağlantı hatası durumunda
            error_log("Database Connection Error: " . $e->getMessage());
            // Kullanıcıya daha genel bir hata mesajı gösterilebilir
            die("Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisi ile iletişime geçin. [Details: " . $e->getMessage() . "]");
        }
    }

    public static function isConnected(): bool
    {
        return self::$connected;
    }

    // Bağlantıyı kapatmak için (genellikle gerekli olmaz, ama eklenebilir)
    public static function close(): void
    { 
        R::close();
        self::$connected = false;
    }

    /**
     * Veritabanına başlangıç verilerini ekler (eğer boşsa).
     */
    private static function seedInitialData(): void
    {
        // Kategori tablosu boş mu kontrol et
        if (R::count('category') == 0) {
            // error_log("Veritabanı boş, başlangıç verileri ekleniyor..."); // Bu satırı kaldır

            // Kategoriler
            $catIcecek = R::dispense('category');
            $catIcecek->name = 'İçecekler';
            R::store($catIcecek);

            // Ürünler
            $pSu = R::dispense('product');
            $pSu->name = 'Su';
            $pSu->description = 'Kaynak suyu.';
            $pSu->price = 5.00;
            $pSu->category = $catIcecek; // İlişkiyi kur
            R::store($pSu);

            // error_log("Başlangıç verileri eklendi."); // Bu satırı kaldır
        }
    }
} 