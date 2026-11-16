<?php

/**
 * Helper aset statis: URL dengan versi berbasis filemtime.
 *
 * Filemtime hanya di-stat sekali per path per request, dan browser bisa
 * meng-cache aset dengan aman (URL berubah hanya saat file berubah).
 */
if (! function_exists('asset_v')) {
    function asset_v(string $path): string
    {
        static $cache = [];

        if (! array_key_exists($path, $cache)) {
            $fullPath = public_path($path);
            $version = is_file($fullPath) ? filemtime($fullPath) : '1';
            $cache[$path] = asset($path).'?v='.$version;
        }

        return $cache[$path];
    }
}
