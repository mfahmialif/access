<?php
$controllers = glob(__DIR__ . '/app/Http/Controllers/*.php');

$search = <<<EOT
        if (!request()->user()?->isSuperadmin()) {
            \$userUnits = request()->user()?->units()->pluck('units.id')->toArray() ?? [];
            \$query->where(function (\$q) use (\$userUnits) {
                \$q->whereIn('unit_id', \$userUnits)->orWhereNull('unit_id');
            });
        }
EOT;

$replace = "";

foreach ($controllers as $path) {
    $content = file_get_contents($path);
    if (strpos($content, trim(explode("\n", $search)[0])) !== false) {
        // More robust replacement
        $content = preg_replace('/[ \t]*if \(!request\(\)->user\(\)\?->isSuperadmin\(\)\) \{.*?\}[ \t]*\r?\n?/s', '', $content);
        file_put_contents($path, $content);
        echo "Reverted " . basename($path) . "\n";
    }
}
