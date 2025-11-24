<?php
// List all cars and detect normalized plate duplicates
// Usage: php tools\list_cars.php
$host = '127.0.0.1';
$db   = 'car_rentaldb';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "All cars (id | plate_number | vin):\n";
$stmt = $pdo->query('SELECT id, plate_number, vin, make, model FROM cars ORDER BY id DESC');
$rows = $stmt->fetchAll();
if (!$rows) {
    echo "(no rows)\n\n";
} else {
    foreach ($rows as $r) {
        echo "ID={$r['id']} | Plate={$r['plate_number']} | VIN={$r['vin']} | {$r['make']} {$r['model']}\n";
    }
    echo "\n";
}

// Check normalized duplicates
echo "Checking for normalized plate duplicates (remove hyphens/spaces, uppercase):\n";
$stmt = $pdo->query("SELECT REPLACE(UPPER(plate_number), '-', '') AS normalized, GROUP_CONCAT(CONCAT(id,':',plate_number,':',vin) SEPARATOR '; ') AS items, COUNT(*) AS cnt FROM cars GROUP BY normalized HAVING cnt > 1");
$dups = $stmt->fetchAll();
if ($dups) {
    foreach ($dups as $d) {
        echo "Normalized={$d['normalized']} | Count={$d['cnt']} | Items={$d['items']}\n";
    }
} else {
    echo "No normalized duplicates found.\n";
}

// Also show any rows where plate normalized equals the problematic plate
$target = 'VLZ-12375';
$normalizedTarget = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $target));
$stmt = $pdo->prepare("SELECT id, plate_number, vin FROM cars WHERE REPLACE(UPPER(plate_number), '-', '') = ? OR REPLACE(UPPER(plate_number), ' ', '') = ?");
$stmt->execute([$normalizedTarget, $normalizedTarget]);
$rows = $stmt->fetchAll();
if ($rows) {
    echo "\nMatches for $target (normalized $normalizedTarget):\n";
    foreach ($rows as $r) {
        echo "ID={$r['id']} | Plate={$r['plate_number']} | VIN={$r['vin']}\n";
    }
} else {
    echo "\nNo matches for $target.\n";
}

echo "Done.\n";
