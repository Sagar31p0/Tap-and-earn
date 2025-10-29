<?php
// Test Database Connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Load config
require_once 'config.php';

echo "<h3>Configuration Check</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_PASS: " . (DB_PASS == 'your_password_here' ? '<span style="color:red">NOT CONFIGURED</span>' : '<span style="color:green">SET</span>') . "<br>";

echo "<h3>Connection Test</h3>";
try {
    $db = Database::getInstance()->getConnection();
    echo "<span style='color:green;'>✓ Database connection successful!</span><br>";
    
    // Test query
    $result = $db->query("SELECT 1")->fetch();
    echo "<span style='color:green;'>✓ Test query successful!</span><br>";
    
    // Check if users table exists
    echo "<h3>Table Check</h3>";
    $tables = ['users', 'user_stats', 'user_spins', 'tasks', 'referrals'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "<span style='color:green;'>✓ Table '$table' exists ($count rows)</span><br>";
        } catch (Exception $e) {
            echo "<span style='color:red;'>✗ Table '$table' missing or error: " . $e->getMessage() . "</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span style='color:red;'>✗ Connection failed: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>Recommendations</h3>";
if (DB_PASS == 'your_password_here') {
    echo "<p style='color:red;'><strong>⚠️ IMPORTANT:</strong> Update the database password in config.php!</p>";
}
echo "<p>If tables are missing, run database.sql to create them.</p>";
?>
