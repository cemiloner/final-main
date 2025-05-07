<?php

namespace App\Core;

abstract class BaseController
{
    /**
     * View dosyasını yükler ve verileri iletir.
     *
     * @param string $view View dosyasının adı (views/ klasörü altında, .php uzantısız)
     * @param array $data View dosyasına gönderilecek veriler ['key' => value]
     * @param string $layout Ana layout dosyası (varsa)
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Verileri view içinde erişilebilir değişkenlere dönüştür
        extract($data);

        // View dosyasının tam yolu (ROOT_PATH kullanarak)
        $viewFile = ROOT_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            // Layout kullanılacaksa
            $layoutFile = ROOT_PATH . '/views/layouts/' . $layout . '.php';

            if ($layout && file_exists($layoutFile)) {
                ob_start();
                require $viewFile;
                $content = ob_get_clean(); // View içeriğini yakala
                require $layoutFile; // Layout'u yükle (içinde $content kullanılabilir)
            } else {
                // Layout yoksa veya bulunamazsa doğrudan view'ı yükle
                require $viewFile;
            }
        } else {
            // View dosyası bulunamazsa hata ver
            // Daha iyi hata yönetimi eklenebilir
            echo "Error: View file '{$viewFile}' not found.";
        }
    }

    /**
     * JSON response döndürür.
     *
     * @param array $data Gönderilecek veri
     * @param int $statusCode HTTP status kodu (varsayılan 200)
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Kullanıcıyı belirtilen URL'ye yönlendirir.
     *
     * @param string $url Yönlendirilecek URL
     */
    protected function redirect(string $url): void
    {
        $_SESSION['js_redirect_url'] = $url;
        error_log('[BaseController] js_redirect_url set to: ' . $url);
    }

    // Input sanitizasyonu için basit bir yardımcı (gerektiğinde genişletilebilir)
    protected function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        } elseif (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
} 