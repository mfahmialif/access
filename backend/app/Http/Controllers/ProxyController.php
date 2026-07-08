<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    /**
     * Whitelist of allowed domains for proxying.
     * Only these domains (and their subdomains) can be proxied.
     */
    private array $allowedDomains = [
        'turath.io',
        'almaany.com',
        'quran.com',
        'sunnah.com',
        'islamqa.info',
        'dorar.net',
        'shamela.ws',
        'archive.org',
        'waqfeya.net',
    ];

    /**
     * Check whether a URL can be embedded directly in an iframe,
     * or if it needs to go through the proxy.
     *
     * GET /api/proxy/check?url=https://app.turath.io/
     *
     * Returns: { "needs_proxy": true|false }
     */
    public function check(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url  = $request->input('url');
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return response()->json(['needs_proxy' => false]);
        }

        try {
            // Do a HEAD request to check response headers
            $response = Http::withOptions([
                'verify'          => false,
                'timeout'         => 8,
                'connect_timeout' => 5,
            ])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ])
            ->head($url);

            // Check X-Frame-Options header
            $xfo = strtolower($response->header('X-Frame-Options') ?? '');
            if ($xfo && ($xfo === 'deny' || $xfo === 'sameorigin')) {
                return response()->json(['needs_proxy' => true]);
            }

            // Check Content-Security-Policy frame-ancestors directive
            $csp = $response->header('Content-Security-Policy') ?? '';
            if ($csp && preg_match('/frame-ancestors\s/i', $csp)) {
                // If frame-ancestors exists and doesn't include '*', it's likely blocking us
                if (!preg_match('/frame-ancestors\s[^;]*\*/i', $csp)) {
                    return response()->json(['needs_proxy' => true]);
                }
            }

            return response()->json(['needs_proxy' => false]);

        } catch (\Throwable $e) {
            // If we can't reach it, don't proxy — let the iframe try directly
            return response()->json(['needs_proxy' => false]);
        }
    }

    /**
     * Proxy an external URL — strip frame-blocking headers so it can be embedded in an iframe.
     *
     * GET /api/proxy?url=https://app.turath.io/
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        // ── Security: only allow whitelisted domains ──
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !$this->isDomainAllowed($host)) {
            return response()->json([
                'error' => 'Domain tidak diizinkan untuk di-proxy.',
            ], 403);
        }

        try {
            // Fetch the page with a browser-like User-Agent
            $response = Http::withOptions([
                'verify'          => false,   // skip SSL verification (local/offline env)
                'timeout'         => 15,
                'connect_timeout' => 10,
            ])
            ->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id,en;q=0.9',
            ])
            ->get($url);

            $status      = $response->status();
            $contentType = $response->header('Content-Type') ?? 'text/html; charset=utf-8';
            $body        = $response->body();

            // Only process HTML responses — pass through everything else
            $isHtml = str_contains($contentType, 'text/html')
                   || str_contains($contentType, 'application/xhtml');

            if ($isHtml) {
                $body = $this->injectBaseTag($body, $url);
                $body = $this->neutralizeFrameBusting($body);
            }

            // Build response — strip all frame-blocking headers
            return response($body, $status)
                ->header('Content-Type', $contentType)
                ->header('X-Proxied-From', $host)
                // Explicitly REMOVE frame-blocking headers
                ->withoutHeader('X-Frame-Options')
                ->withoutHeader('Content-Security-Policy')
                ->withoutHeader('Content-Security-Policy-Report-Only');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error'   => 'Gagal terhubung ke situs.',
                'message' => $e->getMessage(),
            ], 502);

        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Terjadi kesalahan saat memproxy halaman.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inject a <base href> tag so that relative URLs (CSS, JS, images)
     * resolve against the original domain, not our proxy.
     */
    private function injectBaseTag(string $html, string $url): string
    {
        // Derive the base URL (scheme + host + path up to last slash)
        $parsed = parse_url($url);
        $base   = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
        if (!empty($parsed['port'])) {
            $base .= ':' . $parsed['port'];
        }
        // Keep the directory path for relative assets
        $path = $parsed['path'] ?? '/';
        if (substr($path, -1) !== '/') {
            $path = dirname($path) . '/';
        }
        $base .= $path;

        $baseTag = '<base href="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '" target="_self">';

        // Insert after <head> if it exists
        if (preg_match('/<head(\s[^>]*)?>/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            $insertPos = $match[0][1] + strlen($match[0][0]);
            return substr($html, 0, $insertPos) . "\n" . $baseTag . "\n" . substr($html, $insertPos);
        }

        // Fallback: prepend to entire HTML
        return $baseTag . "\n" . $html;
    }

    /**
     * Neutralize common JavaScript frame-busting patterns.
     * Injects a script that overrides top/parent references.
     */
    private function neutralizeFrameBusting(string $html): string
    {
        $script = <<<'JS'
<script>
(function() {
    // Neutralize frame-busting
    try {
        if (window.top !== window.self) {
            Object.defineProperty(window, '__frameProxy', { value: true, writable: false });
        }
    } catch(e) {}

    // Send activity events to parent window to prevent screensaver
    var notifyParent = function() {
        try { window.parent.postMessage('iframe_activity', '*'); } catch(e) {}
    };
    var t;
    var throttleNotify = function() {
        if (t) return;
        notifyParent();
        t = setTimeout(function(){ t = null; }, 1500);
    };
    ['mousedown', 'mousemove', 'keydown', 'touchstart', 'scroll', 'wheel', 'click'].forEach(function(ev) {
        window.addEventListener(ev, throttleNotify, { passive: true, capture: true });
    });

    // Wrap history.pushState & replaceState to prevent cross-origin SecurityError
    var origPush = history.pushState;
    var origReplace = history.replaceState;

    history.pushState = function(state, title, url) {
        try { return origPush.call(this, state, title, url); }
        catch(e) {
            try { return origPush.call(this, state, title); } catch(e2) {}
        }
    };

    history.replaceState = function(state, title, url) {
        try { return origReplace.call(this, state, title, url); }
        catch(e) {
            try { return origReplace.call(this, state, title); } catch(e2) {}
        }
    };
})();
</script>
JS;

        // Insert the script right after <head> (after our base tag)
        if (preg_match('/<head(\s[^>]*)?>/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            $insertPos = $match[0][1] + strlen($match[0][0]);
            // Skip past any existing base tag we may have added
            $afterHead = substr($html, $insertPos);
            if (preg_match('/^(\s*<base[^>]*>\s*)/i', $afterHead, $baseMatch)) {
                $insertPos += strlen($baseMatch[0]);
            }
            return substr($html, 0, $insertPos) . "\n" . $script . "\n" . substr($html, $insertPos);
        }

        return $script . "\n" . $html;
    }

    /**
     * Check if a hostname matches one of the allowed domains (including subdomains).
     */
    private function isDomainAllowed(string $host): bool
    {
        $host = strtolower($host);

        foreach ($this->allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
