<?php
/**
 * Diagnostic and Fix Script
 * Run this to diagnose and fix tap points and shortener ad issues
 */

require_once 'config.php';

echo "=== DIAGNOSTIC AND FIX SCRIPT ===\n\n";

$db = Database::getInstance()->getConnection();

// 1. Check tap_reward setting
echo "1. Checking tap_reward setting...\n";
$stmt = $db->query("SELECT * FROM settings WHERE setting_key = 'tap_reward'");
$tapRewardSetting = $stmt->fetch();
if ($tapRewardSetting) {
    echo "   Database value: " . $tapRewardSetting['setting_value'] . "\n";
} else {
    echo "   NOT FOUND in database!\n";
}

$cachedValue = getSetting('tap_reward', 5);
echo "   Cached/Retrieved value: $cachedValue\n";

if ($tapRewardSetting && $tapRewardSetting['setting_value'] != $cachedValue) {
    echo "   ??  MISMATCH DETECTED! Cache is stale.\n";
} else {
    echo "   ? Values match.\n";
}
echo "\n";

// 2. Check other important settings
echo "2. Checking other tap settings...\n";
$stmt = $db->query("SELECT * FROM settings WHERE setting_key IN ('energy_per_tap', 'tap_ad_frequency', 'energy_recharge_interval')");
while ($row = $stmt->fetch()) {
    $cached = getSetting($row['setting_key'], 'NOT_SET');
    echo "   {$row['setting_key']}: DB={$row['setting_value']}, Cached={$cached}";
    if ($row['setting_value'] != $cached) {
        echo " ??  MISMATCH";
    }
    echo "\n";
}
echo "\n";

// 3. Check shortlink ad configuration
echo "3. Checking shortlink ad placement...\n";
$stmt = $db->query("SELECT * FROM ad_placements WHERE placement_key = 'shortlink'");
$shortlinkPlacement = $stmt->fetch();

if (!$shortlinkPlacement) {
    echo "   ? ERROR: No shortlink placement found!\n";
} else {
    echo "   ? Shortlink placement found (ID: {$shortlinkPlacement['id']})\n";
    echo "   Primary ad unit ID: {$shortlinkPlacement['primary_ad_unit_id']}\n";
    echo "   Secondary ad unit ID: {$shortlinkPlacement['secondary_ad_unit_id']}\n";
    echo "   Tertiary ad unit ID: {$shortlinkPlacement['tertiary_ad_unit_id']}\n";
    
    // Check each ad unit
    foreach (['primary_ad_unit_id', 'secondary_ad_unit_id', 'tertiary_ad_unit_id'] as $key) {
        if ($shortlinkPlacement[$key]) {
            $stmt = $db->prepare("
                SELECT au.*, an.name as network_name, an.is_enabled 
                FROM ad_units au
                JOIN ad_networks an ON au.network_id = an.id
                WHERE au.id = ?
            ");
            $stmt->execute([$shortlinkPlacement[$key]]);
            $unit = $stmt->fetch();
            
            if ($unit) {
                $status = ($unit['is_active'] && $unit['is_enabled']) ? '?' : '?';
                echo "   $status Unit {$unit['id']}: {$unit['name']} ({$unit['network_name']}) - Active: {$unit['is_active']}, Network Enabled: {$unit['is_enabled']}\n";
            }
        }
    }
}
echo "\n";

// 4. Check ad_networks status
echo "4. Checking ad networks...\n";
$stmt = $db->query("SELECT * FROM ad_networks");
while ($row = $stmt->fetch()) {
    $status = $row['is_enabled'] ? '?' : '?';
    echo "   $status {$row['name']}: " . ($row['is_enabled'] ? 'Enabled' : 'DISABLED') . "\n";
}
echo "\n";

// 5. Clear all caches
echo "5. Clearing caches...\n";
$cleared = clearAllCache(false); // Don't clear sessions yet
foreach ($cleared as $cache) {
    echo "   ? Cleared: $cache\n";
}
echo "\n";

// 6. Test tap_reward after cache clear
echo "6. Testing tap_reward after cache clear...\n";
$newCachedValue = getSetting('tap_reward', 5);
echo "   Retrieved value: $newCachedValue\n";
if ($tapRewardSetting && $tapRewardSetting['setting_value'] == $newCachedValue) {
    echo "   ? SUCCESS! Values now match.\n";
} else {
    echo "   ??  Still have mismatch. DB={$tapRewardSetting['setting_value']}, Retrieved={$newCachedValue}\n";
}
echo "\n";

// 7. Fix energy_recharge_interval if it's too high
echo "7. Checking energy_recharge_interval...\n";
$stmt = $db->query("SELECT * FROM settings WHERE setting_key = 'energy_recharge_interval'");
$intervalSetting = $stmt->fetch();
if ($intervalSetting && $intervalSetting['setting_value'] > 3600) {
    echo "   ??  Energy recharge interval is too high: {$intervalSetting['setting_value']} seconds\n";
    echo "   Fixing to 300 seconds (5 minutes)...\n";
    updateSetting('energy_recharge_interval', 300);
    echo "   ? Fixed!\n";
} else {
    echo "   ? Energy recharge interval is OK: {$intervalSetting['setting_value']} seconds\n";
}
echo "\n";

// 8. Test API endpoint for shortlink
echo "8. Testing shortlink ad API endpoint...\n";
try {
    $stmt = $db->prepare("SELECT * FROM ad_placements WHERE placement_key = ?");
    $stmt->execute(['shortlink']);
    $placementConfig = $stmt->fetch();
    
    if (!$placementConfig) {
        echo "   ? Placement 'shortlink' not found\n";
    } else {
        echo "   ? Placement found\n";
        
        // Get primary ad unit
        if ($placementConfig['primary_ad_unit_id']) {
            $stmt = $db->prepare("
                SELECT au.*, an.name as network_name, an.is_enabled 
                FROM ad_units au
                JOIN ad_networks an ON au.network_id = an.id
                WHERE au.id = ? AND au.is_active = 1 AND an.is_enabled = 1
            ");
            $stmt->execute([$placementConfig['primary_ad_unit_id']]);
            $adUnit = $stmt->fetch();
            
            if ($adUnit) {
                echo "   ? Primary ad unit is active and ready:\n";
                echo "     Network: {$adUnit['network_name']}\n";
                echo "     Unit Code: {$adUnit['unit_code']}\n";
                echo "     Unit Type: {$adUnit['unit_type']}\n";
            } else {
                echo "   ? Primary ad unit is not active or network is disabled\n";
            }
        } else {
            echo "   ? No primary ad unit configured\n";
        }
    }
} catch (Exception $e) {
    echo "   ? Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== SUMMARY ===\n";
echo "1. If tap points still show 5 instead of 1:\n";
echo "   - Go to Admin Panel > Cache Management\n";
echo "   - Click 'Force Clear Cache' with 'Also clear user sessions' checked\n";
echo "   - Or restart your PHP-FPM/Apache service\n";
echo "\n";
echo "2. If shortener ads still don't show:\n";
echo "   - Make sure ad unit codes are correct in admin panel\n";
echo "   - Test the shortlink with browser console open to see JavaScript errors\n";
echo "   - Check if the ad network SDKs are loading properly\n";
echo "\n";
echo "3. Recommended: Clear cache via Admin Panel now!\n";
echo "\n";
?>
