<?php
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h3>Clearing Laravel Caches:</h3>";
    
    $r1 = $kernel->call('route:clear');
    echo "Route clear: " . ($r1 === 0 ? 'Success' : 'Error ' . $r1) . "<br>";
    
    $r2 = $kernel->call('config:clear');
    echo "Config clear: " . ($r2 === 0 ? 'Success' : 'Error ' . $r2) . "<br>";
    
    $r3 = $kernel->call('cache:clear');
    echo "Cache clear: " . ($r3 === 0 ? 'Success' : 'Error ' . $r3) . "<br>";
    
    $r4 = $kernel->call('view:clear');
    echo "View clear: " . ($r4 === 0 ? 'Success' : 'Error ' . $r4) . "<br>";
    
    echo "<h4>All caches cleared!</h4>";
} catch (\Throwable $e) {
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
