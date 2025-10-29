<?php
/**
 * Ad Units Database Fix Script
 * This script fixes the unit_code values in ad_units table
 * Run this once to clean up the ad unit codes
 */

require_once 'config.php';

echo "🔧 Starting Ad Units Fix Script...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    
    // Fix Adexium Interstitial (Unit 1)
    echo "📝 Fixing Adexium unit (ID: 1)...\n";
    $stmt = $db->prepare("UPDATE ad_units SET unit_code = ? WHERE id = 1 AND network_id = 1");
    $stmt->execute(['ef364bbc-e2b8-434c-8b52-c735de561dc7']);
    echo "   ✅ Adexium unit code updated to: ef364bbc-e2b8-434c-8b52-c735de561dc7\n\n";
    
    // Fix Monetag Interstitial (Unit 2)
    echo "📝 Fixing Monetag unit (ID: 2)...\n";
    $stmt = $db->prepare("UPDATE ad_units SET unit_code = ? WHERE id = 2 AND network_id = 2");
    $stmt->execute(['10055887']);
    echo "   ✅ Monetag unit code updated to: 10055887\n\n";
    
    $db->commit();
    
    // Verify the changes
    echo "🔍 Verifying changes...\n\n";
    $stmt = $db->prepare("
        SELECT au.id, an.name as network, au.name as unit_name, au.unit_code, au.unit_type 
        FROM ad_units au
        JOIN ad_networks an ON au.network_id = an.id
        WHERE au.id IN (1, 2)
    ");
    $stmt->execute();
    $units = $stmt->fetchAll();
    
    foreach ($units as $unit) {
        echo "   ID: {$unit['id']}\n";
        echo "   Network: {$unit['network']}\n";
        echo "   Unit Name: {$unit['unit_name']}\n";
        echo "   Unit Code: {$unit['unit_code']}\n";
        echo "   Type: {$unit['unit_type']}\n\n";
    }
    
    echo "✅ Ad units fixed successfully!\n";
    echo "\n🎯 You can now test the ads on the tap and earn page.\n";
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
