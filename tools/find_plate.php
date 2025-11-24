<?php
// Quick helper to find cars by plate using different normalizations
// Usage: php tools\find_plate.php "VLZ-12375"
$plateInput = $argv[1] ?? '';
if (empty($plateInput)) {
    echo "Usage: php tools\\find_plate.php \"PLATE_NUMBER\"\n";
    exit(1);
}
$plateRaw = trim($plateInput);
$normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plateRaw));

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

echo "Searching for plate: $plateRaw (normalized: $normalized)\n\n";

// 1) Exact match
$stmt = $pdo->prepare('SELECT id, make, model, plate_number, vin, created_at FROM cars WHERE plate_number = ?');
$stmt->execute([$plateRaw]);
$rowsExact = $stmt->fetchAll();
if ($rowsExact) {
    echo "Exact matches (plate_number = '$plateRaw'):\n";
    foreach ($rowsExact as $r) {
        echo "ID={$r['id']} | {$r['make']} {$r['model']} | Plate={$r['plate_number']} | VIN={$r['vin']} | Created={$r['created_at']}\n";
    }
    echo "\n";
}

// 2) Upper/space/hyphen normalized match
$stmt = $pdo->prepare("SELECT id, make, model, plate_number, vin, created_at FROM cars WHERE REPLACE(UPPER(plate_number), '-', '') = ? OR REPLACE(UPPER(plate_number), ' ', '') = ?");
$stmt->execute([$normalized, $normalized]);
$rowsNorm = $stmt->fetchAll();
if ($rowsNorm) {
    echo "Normalized matches (remove spaces/hyphens, uppercase):\n";
    foreach ($rowsNorm as $r) {
        echo "ID={$r['id']} | {$r['make']} {$r['model']} | Plate={$r['plate_number']} | VIN={$r['vin']} | Created={$r['created_at']}\n";
    }
    echo "\n";
}

// 3) LIKE matches (partial)
$stmt = $pdo->prepare("SELECT id, make, model, plate_number, vin, created_at FROM cars WHERE UPPER(plate_number) LIKE ?");
$like = '%' . substr($normalized, 0, 3) . '%';
$stmt->execute([$like]);
$rowsLike = $stmt->fetchAll();
if ($rowsLike) {
    echo "Partial LIKE matches (first 3 chars):\n";
    foreach ($rowsLike as $r) {
        echo "ID={$r['id']} | {$r['make']} {$r['model']} | Plate={$r['plate_number']} | VIN={$r['vin']} | Created={$r['created_at']}\n";
    }
    echo "\n";
}

if (empty($rowsExact) && empty($rowsNorm) && empty($rowsLike)) {
    echo "No matches found for $plateRaw\n";
}

echo "Done.\n";
