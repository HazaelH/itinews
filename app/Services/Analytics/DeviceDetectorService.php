<?php

namespace App\Services\Analytics;

/**
 * Detector de dispositivos basado en el User-Agent, del lado del servidor.
 *
 * Se implementa de forma interna (sin dependencias externas) para evitar
 * requerir extensiones o paquetes de Composer. Devuelve el tipo de dispositivo,
 * sistema operativo, navegador y si la petición proviene de un bot.
 */
class DeviceDetectorService
{
    /**
     * Patrones de bots / crawlers más comunes.
     */
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|quora link preview|pinterest|vkshare|w3c_validator|whatsapp|telegram|lighthouse|pagespeed|pingdom|uptimerobot|curl|wget|python-requests|python-urllib|go-http-client|java\/|axios|headlesschrome|phantomjs|ahrefs|semrush|mj12bot|dotbot|petalbot|yandex|baiduspider|sogou|exabot|ia_archiver|archive\.org/i';

    /**
     * Analiza un User-Agent y devuelve los datos del dispositivo.
     *
     * @return array{device_type:string, os:?string, browser:?string, browser_version:?string, is_bot:bool}
     */
    public function parse(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return [
                'device_type' => 'unknown',
                'os' => null,
                'browser' => null,
                'browser_version' => null,
                'is_bot' => false,
            ];
        }

        $isBot = (bool) preg_match(self::BOT_PATTERN, $ua);

        $browser = $this->detectBrowser($ua);

        return [
            'device_type' => $isBot ? 'bot' : $this->detectDeviceType($ua),
            'os' => $this->detectOs($ua),
            'browser' => $browser['name'],
            'browser_version' => $browser['version'],
            'is_bot' => $isBot,
        ];
    }

    private function detectDeviceType(string $ua): string
    {
        // Tablets primero: el iPad contiene "Mobile" en su UA y Android-tablet no.
        if (preg_match('/iPad|Tablet|PlayBook|Silk|Kindle|(Android(?!.*Mobile))/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|iPhone|iPod|Windows Phone|IEMobile|BlackBerry|Opera Mini|Opera Mobi|Android/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectOs(string $ua): ?string
    {
        if (preg_match('/Windows Phone/i', $ua)) {
            return 'Windows Phone';
        }

        if (preg_match('/Android/i', $ua)) {
            return 'Android';
        }

        if (preg_match('/iPad|iPhone|iPod/i', $ua)) {
            return 'iOS';
        }

        if (preg_match('/CrOS/i', $ua)) {
            return 'Chrome OS';
        }

        if (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            return 'macOS';
        }

        if (preg_match('/Windows/i', $ua)) {
            return 'Windows';
        }

        if (preg_match('/Linux/i', $ua)) {
            return 'Linux';
        }

        if (preg_match('/FreeBSD|OpenBSD|NetBSD/i', $ua)) {
            return 'BSD';
        }

        return 'Otro';
    }

    /**
     * @return array{name:?string, version:?string}
     */
    private function detectBrowser(string $ua): array
    {
        // El orden importa: los navegadores basados en Chromium incluyen "Chrome" en su UA.
        if (preg_match('/Edg(?:e|A|iOS)?\/([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Edge', 'version' => $m[1]];
        }

        if (preg_match('/OPR\/([\d.]+)/i', $ua, $m) || preg_match('/Opera[\/ ]([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Opera', 'version' => $m[1]];
        }

        if (preg_match('/SamsungBrowser\/([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Samsung Internet', 'version' => $m[1]];
        }

        if (preg_match('/Firefox\/([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Firefox', 'version' => $m[1]];
        }

        if (preg_match('/CriOS\/([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Chrome', 'version' => $m[1]];
        }

        if (preg_match('/Chrome\/([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Chrome', 'version' => $m[1]];
        }

        if (preg_match('/Version\/([\d.]+).*Safari/i', $ua, $m)) {
            return ['name' => 'Safari', 'version' => $m[1]];
        }

        if (preg_match('/Safari/i', $ua)) {
            return ['name' => 'Safari', 'version' => null];
        }

        if (preg_match('/MSIE ([\d.]+)/i', $ua, $m)) {
            return ['name' => 'Internet Explorer', 'version' => $m[1]];
        }

        return ['name' => 'Otro', 'version' => null];
    }
}
