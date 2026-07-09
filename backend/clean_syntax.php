<?php
$controllers = glob(__DIR__ . '/app/Http/Controllers/*.php');

$search = <<<EOT
        if (\$unitId && \$unitId !== 'all') {
            \$query->where('unit_id', \$unitId);
        }
        
);
        }
EOT;

$replace = <<<EOT
        if (\$unitId && \$unitId !== 'all') {
            \$query->where('unit_id', \$unitId);
        }
EOT;

foreach ($controllers as $path) {
    $content = file_get_contents($path);
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($path, $content);
        echo "Cleaned " . basename($path) . "\n";
    }
}
