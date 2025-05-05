<?php

echo "PDO Extension Loaded: " . (extension_loaded('PDO') ? 'Yes' : 'No') . "<br>";
echo "PDO SQLite Driver Loaded: " . (extension_loaded('pdo_sqlite') ? 'Yes' : 'No') . "<br><br>";

if (extension_loaded('pdo_sqlite')) {
    echo "PDO SQLite Drivers:<br>";
    print_r(PDO::getAvailableDrivers());
} else {
    echo "Cannot list drivers because pdo_sqlite is not loaded.";
}

echo "<hr>";
phpinfo();

?> 