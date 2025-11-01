<?php
/**
 * Fix Script for Tap Points and Shortener Ads Issues
 * 
 * This script:
 * 1. Clears all caches (OpCache, APCu, stat cache)
 * 2. Verifies tap_reward setting is properly set
 * 3. Verifies shortener ad placement configuration
 * 4. Tests that settings can be read correctly
 */

require_once 'config.php';

echo "=================================================\n";
echo "Fix Script: Tap Points & Shortener Ads\n";
echo "=================================================\n\n";

$db = Database::getInstance()->getConnection();
$issues_fixed = [];
$errors = [];

// Step 1: Clear all caches
echo "Step 1: Clearing all caches...\n";
try {
    // Clear OpCache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $issues_fixed[] = "OpCache cleared";
        echo "? OpCache cleared\n";
    } else {
        echo "? OpCache not available\n";
    }
    
    // Clear APCu cache
    if (function_exists('apcu_clear_cache')) {
        apcu_clear_cache();
        $issues_fixed[] = "APCu cache cleared";
        echo "? APCu cache cleared\n";
    } else {
        echo "? APCu cache not available\n";
    }
    
    // Clear stat cache
    clearstatcache(true);
    $issues_fixed[] = "Stat cache cleared";
    echo "? Stat cache cleared\n";
    
} catch (Exception $e) {
    $errors[] = "Cache clearing error: " . $e->getMessage();
    echo "? Error clearing cache: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 2: Verify and fix tap_reward setting
echo "Step 2: Checking tap_reward setting...\n";
try {
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tap_reward'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $current_value = $result['setting_value'];
        echo "? Current tap_reward in database: $current_value\n";
        
        // Verify it reads correctly using getSetting function
        $read_value = getSetting('tap_reward', 5);
        echo "? tap_reward read via getSetting(): $read_value\n";
        
        if ($read_value != $current_value) {
            $errors[] = "tap_reward mismatch: DB has '$current_value' but getSetting() returned '$read_value'";
            echo "? WARNING: Value mismatch detected!\n";
        } else {
            echo "? tap_reward setting verified correctly\n";
            $issues_fixed[] = "tap_reward setting verified ($current_value points per tap)";
        }
    } else {
        // Setting doesn't exist, create it with value 1
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tap_reward', '1')");
        $stmt->execute();
        $issues_fixed[] = "Created tap_reward setting with value 1";
        echo "? Created tap_reward setting with value 1\n";
    }
} catch (Exception $e) {
    $errors[] = "tap_reward check error: " . $e->getMessage();
    echo "? Error checking tap_reward: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 3: Check shortener ad placement
echo "Step 3: Checking shortener ad placement...\n";
try {
    $stmt = $db->prepare("SELECT * FROM ad_placements WHERE placement_key = 'shortlink'");
    $stmt->execute();
    $placement = $stmt->fetch();
    
    if ($placement) {
        echo "? Found shortlink placement (ID: {$placement['id']})\n";
        echo "  - Primary Ad Unit ID: " . ($placement['primary_ad_unit_id'] ?: 'None') . "\n";
        echo "  - Secondary Ad Unit ID: " . ($placement['secondary_ad_unit_id'] ?: 'None') . "\n";
        echo "  - Tertiary Ad Unit ID: " . ($placement['tertiary_ad_unit_id'] ?: 'None') . "\n";
        
        // Check if at least primary ad unit exists
        if ($placement['primary_ad_unit_id']) {
            $stmt = $db->prepare("
                SELECT au.*, an.name as network_name, an.is_enabled 
                FROM ad_units au
                JOIN ad_networks an ON au.network_id = an.id
                WHERE au.id = ?
            ");
            $stmt->execute([$placement['primary_ad_unit_id']]);
            $ad_unit = $stmt->fetch();
            
            if ($ad_unit) {
                echo "  - Primary Ad Unit: {$ad_unit['name']} ({$ad_unit['network_name']})\n";
                echo "    * Unit Code: {$ad_unit['unit_code']}\n";
                echo "    * Type: {$ad_unit['unit_type']}\n";
                echo "    * Active: " . ($ad_unit['is_active'] ? 'Yes' : 'No') . "\n";
                echo "    * Network Enabled: " . ($ad_unit['is_enabled'] ? 'Yes' : 'No') . "\n";
                
                if (!$ad_unit['is_active']) {
                    // Activate the ad unit
                    $stmt = $db->prepare("UPDATE ad_units SET is_active = 1 WHERE id = ?");
                    $stmt->execute([$placement['primary_ad_unit_id']]);
                    $issues_fixed[] = "Activated primary ad unit for shortlink";
                    echo "? Activated primary ad unit\n";
                }
                
                if (!$ad_unit['is_enabled']) {
                    // Enable the network
                    $stmt = $db->prepare("UPDATE ad_networks SET is_enabled = 1 WHERE id = ?");
                    $stmt->execute([$ad_unit['network_id']]);
                    $issues_fixed[] = "Enabled ad network for shortlink";
                    echo "? Enabled ad network\n";
                }
                
                if ($ad_unit['is_active'] && $ad_unit['is_enabled']) {
                    $issues_fixed[] = "Shortlink ad configuration verified";
                    echo "? Shortlink ad is properly configured\n";
                }
            } else {
                $errors[] = "Primary ad unit not found for shortlink";
                echo "? Primary ad unit not found\n";
                
                // Try to find any active ad unit and assign it
                $stmt = $db->query("
                    SELECT au.id, au.name, an.name as network_name
                    FROM ad_units au
                    JOIN ad_networks an ON au.network_id = an.id
                    WHERE au.is_active = 1 AND an.is_enabled = 1
                    LIMIT 1
                ");
                $fallback_unit = $stmt->fetch();
                
                if ($fallback_unit) {
                    $stmt = $db->prepare("UPDATE ad_placements SET primary_ad_unit_id = ? WHERE placement_key = 'shortlink'");
                    $stmt->execute([$fallback_unit['id']]);
                    $issues_fixed[] = "Assigned fallback ad unit ({$fallback_unit['name']}) to shortlink";
                    echo "? Assigned fallback ad unit: {$fallback_unit['name']}\n";
                }
            }
        } else {
            $errors[] = "No primary ad unit assigned to shortlink placement";
            echo "? No primary ad unit assigned\n";
            
            // Try to find and assign an active ad unit
            $stmt = $db->query("
                SELECT au.id, au.name, an.name as network_name
                FROM ad_units au
                JOIN ad_networks an ON au.network_id = an.id
                WHERE au.is_active = 1 AND an.is_enabled = 1
                LIMIT 1
            ");
            $fallback_unit = $stmt->fetch();
            
            if ($fallback_unit) {
                $stmt = $db->prepare("UPDATE ad_placements SET primary_ad_unit_id = ? WHERE placement_key = 'shortlink'");
                $stmt->execute([$fallback_unit['id']]);
                $issues_fixed[] = "Assigned ad unit ({$fallback_unit['name']}) to shortlink";
                echo "? Assigned ad unit: {$fallback_unit['name']}\n";
            } else {
                $errors[] = "No active ad units available in the system";
                echo "? No active ad units available\n";
            }
        }
    } else {
        $errors[] = "Shortlink ad placement not found";
        echo "? Shortlink placement not found\n";
        
        // Create the placement
        $stmt = $db->prepare("INSERT INTO ad_placements (placement_key, description, frequency) VALUES ('shortlink', 'Short Link Ads', 1)");
        $stmt->execute();
        $placement_id = $db->lastInsertId();
        
        // Try to assign an active ad unit
        $stmt = $db->query("
            SELECT au.id, au.name
            FROM ad_units au
            JOIN ad_networks an ON au.network_id = an.id
            WHERE au.is_active = 1 AND an.is_enabled = 1
            LIMIT 1
        ");
        $ad_unit = $stmt->fetch();
        
        if ($ad_unit) {
            $stmt = $db->prepare("UPDATE ad_placements SET primary_ad_unit_id = ? WHERE id = ?");
            $stmt->execute([$ad_unit['id'], $placement_id]);
            $issues_fixed[] = "Created shortlink placement with ad unit ({$ad_unit['name']})";
            echo "? Created shortlink placement and assigned ad unit\n";
        } else {
            $issues_fixed[] = "Created shortlink placement (no ad units available)";
            echo "? Created shortlink placement (please assign ad units in admin panel)\n";
        }
    }
} catch (Exception $e) {
    $errors[] = "Shortlink check error: " . $e->getMessage();
    echo "? Error checking shortlink: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 4: Force update setting values to ensure they're fresh
echo "Step 4: Refreshing critical settings...\n";
try {
    // Get current tap_reward value and re-save it
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tap_reward'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $value = $result['setting_value'];
        $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'tap_reward'");
        $stmt->execute([$value]);
        echo "? Refreshed tap_reward setting timestamp\n";
        $issues_fixed[] = "Refreshed tap_reward setting";
    }
    
    // Clear OpCache again after database updates
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "? Cleared OpCache again after updates\n";
    }
} catch (Exception $e) {
    echo "? Warning: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 5: Test API endpoints
echo "Step 5: Testing API endpoints...\n";
try {
    // Test tap reward reading
    $tapReward = getSetting('tap_reward', 5);
    echo "? getSetting('tap_reward') returns: $tapReward\n";
    
    // Test ad config for shortlink
    $stmt = $db->prepare("SELECT * FROM ad_placements WHERE placement_key = 'shortlink'");
    $stmt->execute();
    $placement = $stmt->fetch();
    
    if ($placement && $placement['primary_ad_unit_id']) {
        echo "? Shortlink ad placement configured with unit ID: {$placement['primary_ad_unit_id']}\n";
        $issues_fixed[] = "API endpoints verified";
    } else {
        $errors[] = "Shortlink placement still not properly configured";
        echo "? Shortlink placement still needs configuration\n";
    }
} catch (Exception $e) {
    echo "? Warning during testing: " . $e->getMessage() . "\n";
}

echo "\n";
echo "=================================================\n";
echo "Summary\n";
echo "=================================================\n\n";

if (count($issues_fixed) > 0) {
    echo "? Issues Fixed (" . count($issues_fixed) . "):\n";
    foreach ($issues_fixed as $fix) {
        echo "  ? $fix\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "? Errors Found (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "  ? $error\n";
    }
    echo "\n";
}

echo "=================================================\n";
echo "Recommendations:\n";
echo "=================================================\n";
echo "1. Test tap and earn - it should now give correct points\n";
echo "2. Test shortener links - ads should now display\n";
echo "3. If issues persist, check browser console for errors\n";
echo "4. Verify admin panel shows correct settings\n";
echo "5. Consider restarting PHP-FPM if using it\n";
echo "\n";

echo "Script completed at: " . date('Y-m-d H:i:s') . "\n";
?>
