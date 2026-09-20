<?php

namespace App\Support;

class VideoSource
{
    public static function fromUrl(?string $url): ?array
    {
        $url = trim($url ?? '');
        if (! filter_var($url, FILTER_VALIDATE_URL)) return null;
        $parts = parse_url($url);
        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) return null;
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $id = null;
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = trim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            if (preg_match('~^/(?:embed|shorts|live)/([^/]+)/?$~', $path, $match)) $id = $match[1];
            elseif ($path === '/watch') $id = $query['v'] ?? null;
        }
        if (is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/D', $id)) {
            return ['type' => 'embed', 'url' => 'https://www.youtube-nocookie.com/embed/'.$id.'?rel=0'];
        }
        if (preg_match('/\.(mp4|webm|mov)$/i', $path)) return ['type' => 'file', 'url' => $url];

        return null;
    }
}
