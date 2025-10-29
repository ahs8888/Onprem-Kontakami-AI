<?php

if (! function_exists('user')) {
    function user(): mixed
    {
        return session(config('services.session-user-prefix'));
    }
}


if (!function_exists('is_request_in')) {
    function is_request_in($allowedUrl, $url)
    {
            foreach ($allowedUrl as $string) {
                if (str_contains($url, $string)) {
                    return true;
                }
            }
            return false;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
        {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            $bytes = max($bytes, 0);
            $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
            $power = min($power, count($units) - 1);

            $bytes /= pow(1024, $power);

            return round($bytes, $precision) . ' ' . $units[$power];
        }
}


