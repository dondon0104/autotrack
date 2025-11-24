<?php
/**
 * Sample Data Seeder for Car Rental System
 * Run this from command line: php tools/seed_sample_data.php
 */

// Database configuration - update these if needed
$dbHost = 'localhost';
$dbName = 'car_rentaldb';
$dbUser = 'root';
$dbPass = '';

try {
    echo "Connecting to database...\n";
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected successfully!\n\n";

    // Check existing data
    $stmt = $pdo->query("SELECT COUNT(*) FROM rentals");
    $existingRentals = $stmt->fetchColumn();
    
    if ($existingRentals > 10) {
        echo "Database already has $existingRentals rentals. Skipping seeding.\n";
        echo "To reseed, run: DELETE FROM payments; DELETE FROM rentals;\n";
        exit;
    }

    echo "Step 1: Checking existing cars and users...\n";
    
    // Get cars
    $stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' LIMIT 20");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cars)) {
        echo "❌ No cars found. Please add some cars first via the admin panel.\n";
        exit;
    }
    echo "✅ Found " . count($cars) . " cars.\n";

    // Get users
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' LIMIT 20");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ No users found. Please register some users first.\n";
        exit;
    }
    echo "✅ Found " . count($users) . " users.\n\n";

    echo "Step 2: Creating 30 sample rentals with payments...\n";

    $paymentMethods = ['paymongo', 'gcash', 'cash'];
    $statuses = ['completed', 'completed', 'completed', 'active', 'pending', 'cancelled'];
    
    $rentalsCreated = 0;
    $paymentsCreated = 0;

    for ($i = 0; $i < 30; $i++) {
        // Random user and car
        $user = $users[array_rand($users)];
        $car = $cars[array_rand($cars)];
        
        // Random dates within last 90 days
        $daysAgo = rand(1, 90);
        $rentalDays = rand(1, 14);
        $rentalStart = date('Y-m-d', strtotime("-$daysAgo days"));
        $rentalEnd = date('Y-m-d', strtotime("$rentalStart +$rentalDays days"));
        $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
        
        // Calculate amounts
        $dailyRate = $car['daily_rate'];
        $totalDays = $rentalDays;
        $subtotal = $dailyRate * $totalDays;
        $taxRate = 12;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;
        
        // Random status
        $status = $statuses[array_rand($statuses)];
        
        // Insert rental
        $stmt = $pdo->prepare("
            INSERT INTO rentals (user_id, car_id, rental_start, rental_end, daily_rate, total_days, 
                                subtotal, tax_rate, tax_amount, total_amount, status, 
                                pickup_location, return_location, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user['id'], $car['id'], $rentalStart, $rentalEnd, $dailyRate, $totalDays,
            $subtotal, $taxRate, $taxAmount, $totalAmount, $status,
            'Main Office', 'Main Office', 'Sample rental for testing',
            $createdAt, $createdAt
        ]);
        
        $rentalId = $pdo->lastInsertId();
        $rentalsCreated++;
        
        // Create payment(s) for completed and active rentals
        if ($status === 'completed' || $status === 'active') {
            $paymentAmount = ($status === 'completed') ? $totalAmount : ($totalAmount * 0.5);
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paymentDate = date('Y-m-d H:i:s', strtotime($createdAt . ' +1 hour'));
            
            $stmt = $pdo->prepare("
                INSERT INTO payments (rental_id, user_id, amount, payment_method, payment_status,
                                     transaction_id, payment_date, notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $rentalId, $user['id'], $paymentAmount, $paymentMethod, 'completed',
                'SEED_' . strtoupper(uniqid()), $paymentDate, 'Sample payment',
                $paymentDate, $paymentDate
            ]);
            
            $paymentsCreated++;
            
            // Add final payment for completed rentals
            if ($status === 'completed' && $paymentAmount < $totalAmount) {
                $remainingAmount = $totalAmount - $paymentAmount;
                $secondPaymentDate = date('Y-m-d H:i:s', strtotime($rentalEnd . ' -1 day'));
                
                $stmt->execute([
                    $rentalId, $user['id'], $remainingAmount, $paymentMethods[array_rand($paymentMethods)], 
                    'completed', 'SEED_' . strtoupper(uniqid()), $secondPaymentDate, 'Final payment',
                    $secondPaymentDate, $secondPaymentDate
                ]);
                
                $paymentsCreated++;
            }
        }
        
        // Progress indicator
        if (($i + 1) % 5 == 0) {
            echo "  • Created " . ($i + 1) . "/30 rentals...\n";
        }
    }

    echo "\n✅ Seeding completed successfully!\n";
    echo "═══════════════════════════════════\n";
    echo "📊 Rentals created: $rentalsCreated\n";
    echo "💰 Payments created: $paymentsCreated\n";
    echo "\n🎉 You can now view reports at:\n";
    echo "   http://localhost/web2/final_project/car_rental/admin/reports\n\n";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

