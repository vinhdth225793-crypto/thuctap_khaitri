<?php

namespace App\Support;

class OnlineMeetingUrl
{
    public static function normalize(?string $url): ?string
    {
        if (! filled($url)) {
            return $url;
        }

        $url = trim($url);
        $parts = parse_url($url);

        if (! is_array($parts) || ! self::isGoogleMeetHost($parts['host'] ?? null)) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $path = '/' . ltrim($parts['path'] ?? '', '/');

        return rtrim($scheme . '://' . $parts['host'] . $path, '/');
    }

    public static function meetingCode(?string $url): ?string
    {
        $normalizedUrl = self::normalize($url);

        if (! filled($normalizedUrl)) {
            return null;
        }

        $path = (string) parse_url($normalizedUrl, PHP_URL_PATH);
        $code = trim(basename($path), '/');

        return $code !== '' ? $code : null;
    }

    public static function isGoogleMeetUrl(?string $url): bool
    {
        if (! filled($url)) {
            return false;
        }

        $parts = parse_url(trim($url));

        return is_array($parts) && self::isGoogleMeetHost($parts['host'] ?? null);
    }

    public static function isGoogleMeetHost(?string $host): bool
    {
        return strtolower((string) $host) === 'meet.google.com';
    }
}
