<?php
/**
 * Database Fix Script
 * Run this script to fix all database-related issues
 */

require_once 'config.php';

echo "?? Starting Database Fix Script...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Fix short_links ad_unit_id
    echo "?? Step 1: Fixing short_links ad_unit_id...\n";
    $stmt = $db->prepare("
        UPDATE short_links 
        SET ad_unit_id = 3 
        WHERE ad_unit_id = 12 
        AND mode = 'direct_ad'
    ");
    $affected = $stmt->execute();
    $count = $stmt->rowCount();
    echo "   ? Updated $count short links to use correct ad unit\n\n";
    
    // 2. Verify settings table has correct values
    echo "?? Step 2: Verifying settings...\n";
    
    // Check tap_reward
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tap_reward'");
    $stmt->execute();
    $tapReward = $stmt->fetchColumn();
    echo "   Current tap_reward: " . ($tapReward ?: 'NOT SET') . "\n";
    
    if (!$tapReward || $tapReward != '1') {
        echo "   ??  Setting tap_reward to 1...\n";
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('tap_reward', '1') 
            ON DUPLICATE KEY UPDATE setting_value = '1'
        ");
        $stmt->execute();
        echo "   ? tap_reward updated to 1\n";
    } else {
        echo "   ? tap_reward is correct\n";
    }
    
    // Check energy_per_tap
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'energy_per_tap'");
    $stmt->execute();
    $energyPerTap = $stmt->fetchColumn();
    echo "   Current energy_per_tap: " . ($energyPerTap ?: 'NOT SET') . "\n";
    
    if (!$energyPerTap || $energyPerTap != '3') {
        echo "   ??  Setting energy_per_tap to 3...\n";
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('energy_per_tap', '3') 
            ON DUPLICATE KEY UPDATE setting_value = '3'
        ");
        $stmt->execute();
        echo "   ? energy_per_tap updated to 3\n";
    } else {
        echo "   ? energy_per_tap is correct\n";
    }
    
    // Check tap_ad_frequency
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tap_ad_frequency'");
    $stmt->execute();
    $tapAdFreq = $stmt->fetchColumn();
    echo "   Current tap_ad_frequency: " . ($tapAdFreq ?: 'NOT SET') . "\n";
    
    if (!$tapAdFreq || $tapAdFreq != '3') {
        echo "   ??  Setting tap_ad_frequency to 3...\n";
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('tap_ad_frequency', '3') 
            ON DUPLICATE KEY UPDATE setting_value = '3'
        ");
        $stmt->execute();
        echo "   ? tap_ad_frequency updated to 3\n";
    } else {
        echo "   ? tap_ad_frequency is correct\n";
    }
    
    echo "\n";
    
    // 3. Verify ad_placements configuration
    echo "?? Step 3: Verifying ad placements...\n";
    $stmt = $db->query("
        SELECT 
            ap.placement_key,
            ap.primary_ad_unit_id,
            au1.name as primary_name,
            an1.name as primary_network,
            ap.secondary_ad_unit_id,
            au2.name as secondary_name,
            an2.name as secondary_network
        FROM ad_placements ap
        LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
        LEFT JOIN ad_networks an1 ON au1.network_id = an1.id
        LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
        LEFT JOIN ad_networks an2 ON au2.network_id = an2.id
        WHERE ap.placement_key IN ('tap', 'spin', 'shortlink', 'task')
        ORDER BY ap.placement_key
    ");
    $placements = $stmt->fetchAll();
    
    foreach ($placements as $placement) {
        echo "\n   ?? " . strtoupper($placement['placement_key']) . " Placement:\n";
        echo "      Primary: " . ($placement['primary_name'] ?: 'NONE') . 
             " (" . ($placement['primary_network'] ?: 'N/A') . ")\n";
        echo "      Secondary: " . ($placement['secondary_name'] ?: 'NONE') . 
             " (" . ($placement['secondary_network'] ?: 'N/A') . ")\n";
    }
    
    echo "\n";
    
    // 4. Check short_links status
    echo "?? Step 4: Checking short_links status...\n";
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN ad_unit_id IS NULL THEN 1 ELSE 0 END) as null_ad_unit,
            SUM(CASE WHEN ad_unit_id = 3 THEN 1 ELSE 0 END) as monetag_ad,
            SUM(CASE WHEN ad_unit_id = 12 THEN 1 ELSE 0 END) as adsgram_ad
        FROM short_links
    ");
    $stats = $stmt->fetch();
    
    echo "   Total short links: " . $stats['total'] . "\n";
    echo "   Using placement config (NULL): " . $stats['null_ad_unit'] . "\n";
    echo "   Using Monetag (ID 3): " . $stats['monetag_ad'] . "\n";
    echo "   Using Adsgram (ID 12): " . $stats['adsgram_ad'] . " " . 
         ($stats['adsgram_ad'] > 0 ? "?? SHOULD BE 0!" : "?") . "\n";
    
    echo "\n";
    
    // 5. Summary
    echo "???????????????????????????????????????\n";
    echo "? DATABASE FIX COMPLETE!\n";
    echo "???????????????????????????????????????\n\n";
    
    echo "?? SUMMARY OF FIXES:\n";
    echo "1. ? Database name corrected in config.php\n";
    echo "2. ? Short links updated to use correct ad units\n";
    echo "3. ? Settings verified and corrected\n";
    echo "4. ? Monetag multiple ad prevention added\n\n";
    
    echo "??  NEXT STEPS:\n";
    echo "1. Clear browser cache and cookies\n";
    echo "2. Test tap functionality (should show 1 point per tap)\n";
    echo "3. Test shortener links (should show ads properly)\n";
    echo "4. Check if Monetag runs only once per action\n";
    echo "5. Verify database password in config.php is correct\n\n";
    
} catch (Exception $e) {
    echo "? ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
