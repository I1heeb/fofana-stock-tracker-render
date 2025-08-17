<?php
// Script pour vider le cache Laravel
echo "Clearing Laravel cache...\n";

// Vider le cache de configuration
exec('php artisan config:clear', $output1);
echo "Config cache cleared\n";

// Vider le cache des vues
exec('php artisan view:clear', $output2);
echo "View cache cleared\n";

// Vider le cache des routes
exec('php artisan route:clear', $output3);
echo "Route cache cleared\n";

// Vider le cache général
exec('php artisan cache:clear', $output4);
echo "Application cache cleared\n";

echo "All caches cleared successfully!\n";
?>
