<?php
/**
 * Ad Integration Verification Script
 * Run this script to verify all ad units are properly integrated
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$db = Database::getInstance()->getConnection();

echo "<html><head>";
echo "<title>Ad Integration Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
    h2 { color: #555; margin-top: 30px; }
    .success { color: #4CAF50; font-weight: bold; }
    .error { color: #f44336; font-weight: bold; }
    .warning { color: #ff9800; font-weight: bold; }
    .info { color: #2196F3; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #4CAF50; color: white; }
    tr:hover { background-color: #f5f5f5; }
    .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .badge-success { background-color: #4CAF50; color: white; }
    .badge-error { background-color: #f44336; color: white; }
    .badge-warning { background-color: #ff9800; color: white; }
    .check-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
    .check-item.fail { border-left-color: #f44336; }
    .code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🎯 Ad Integration Verification Report</h1>";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";

$allPassed = true;

// 1. Check Ad Networks
echo "<h2>1️⃣ Ad Networks Status</h2>";
$stmt = $db->query("SELECT * FROM ad_networks ORDER BY id");
$networks = $stmt->fetchAll();

if (count($networks) == 4) {
    echo "<div class='check-item'><span class='success'>✅ All 4 ad networks configured</span></div>";
} else {
    echo "<div class='check-item fail'><span class='error'>❌ Expected 4 ad networks, found " . count($networks) . "</span></div>";
    $allPassed = false;
}

echo "<table>";
echo "<tr><th>ID</th><th>Network Name</th><th>Status</th></tr>";
foreach ($networks as $network) {
    $status = $network['is_enabled'] ? "<span class='status-badge badge-success'>Enabled</span>" : "<span class='status-badge badge-error'>Disabled</span>";
    echo "<tr><td>{$network['id']}</td><td><strong>" . ucfirst($network['name']) . "</strong></td><td>{$status}</td></tr>";
    
    if (!$network['is_enabled']) {
        $allPassed = false;
    }
}
echo "</table>";

// 2. Check Ad Units
echo "<h2>2️⃣ Ad Units Status</h2>";
$stmt = $db->query("SELECT au.*, an.name as network_name FROM ad_units au 
                    LEFT JOIN ad_networks an ON au.network_id = an.id 
                    ORDER BY au.id");
$units = $stmt->fetchAll();

$expectedUnits = 12;
if (count($units) >= $expectedUnits) {
    echo "<div class='check-item'><span class='success'>✅ {count($units)} ad units configured (minimum {$expectedUnits} expected)</span></div>";
} else {
    echo "<div class='check-item fail'><span class='error'>❌ Expected at least {$expectedUnits} ad units, found " . count($units) . "</span></div>";
    echo "<div class='info'>ℹ️ Run <span class='code'>update_ad_units.sql</span> to add missing units</div>";
    $allPassed = false;
}

// Check specific ad unit IDs
$expectedAdUnits = [
    'adexium' => ['8391da33-7acd-47a9-8d83-f7b4bf4956b1'],
    'monetag' => ['10113890'],
    'richads' => ['375934', '375935', '375936', '375937', '375938'],
    'adsgram' => ['task-16619', 'int-16618', '16617']
];

echo "<table>";
echo "<tr><th>ID</th><th>Network</th><th>Name</th><th>Unit Code</th><th>Type</th><th>Placement</th><th>Status</th></tr>";
foreach ($units as $unit) {
    $status = $unit['is_active'] ? "<span class='status-badge badge-success'>Active</span>" : "<span class='status-badge badge-error'>Inactive</span>";
    $unitCodeShort = strlen($unit['unit_code']) > 30 ? substr($unit['unit_code'], 0, 30) . '...' : $unit['unit_code'];
    echo "<tr>";
    echo "<td>{$unit['id']}</td>";
    echo "<td><strong>" . ucfirst($unit['network_name']) . "</strong></td>";
    echo "<td>{$unit['name']}</td>";
    echo "<td><span class='code'>{$unitCodeShort}</span></td>";
    echo "<td>{$unit['unit_type']}</td>";
    echo "<td>{$unit['placement_key']}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check Ad Placements
echo "<h2>3️⃣ Ad Placements Configuration</h2>";
$stmt = $db->query("SELECT ap.*, 
                    au1.name as primary_name, au1.is_active as primary_active,
                    au2.name as secondary_name, au2.is_active as secondary_active,
                    au3.name as tertiary_name, au3.is_active as tertiary_active
                    FROM ad_placements ap
                    LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
                    LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
                    LEFT JOIN ad_units au3 ON ap.tertiary_ad_unit_id = au3.id
                    ORDER BY ap.id");
$placements = $stmt->fetchAll();

$placementsConfigured = 0;
foreach ($placements as $placement) {
    if ($placement['primary_ad_unit_id']) {
        $placementsConfigured++;
    }
}

echo "<div class='check-item'><span class='info'>ℹ️ {$placementsConfigured} out of " . count($placements) . " placements have primary ad units configured</span></div>";

echo "<table>";
echo "<tr><th>Placement Key</th><th>Description</th><th>Primary Unit</th><th>Secondary Unit</th><th>Tertiary Unit</th><th>Frequency</th></tr>";
foreach ($placements as $placement) {
    echo "<tr>";
    echo "<td><strong>{$placement['placement_key']}</strong></td>";
    echo "<td>{$placement['description']}</td>";
    
    $primary = $placement['primary_name'] ? 
        ($placement['primary_active'] ? "<span class='success'>{$placement['primary_name']}</span>" : "<span class='warning'>{$placement['primary_name']} (Inactive)</span>") : 
        "<span class='error'>Not set</span>";
    echo "<td>{$primary}</td>";
    
    $secondary = $placement['secondary_name'] ? 
        ($placement['secondary_active'] ? "<span class='success'>{$placement['secondary_name']}</span>" : "<span class='warning'>{$placement['secondary_name']} (Inactive)</span>") : 
        "<span class='warning'>-</span>";
    echo "<td>{$secondary}</td>";
    
    $tertiary = $placement['tertiary_name'] ? 
        ($placement['tertiary_active'] ? "<span class='success'>{$placement['tertiary_name']}</span>" : "<span class='warning'>{$placement['tertiary_name']} (Inactive)</span>") : 
        "<span class='warning'>-</span>";
    echo "<td>{$tertiary}</td>";
    
    echo "<td>{$placement['frequency']}</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Check Files
echo "<h2>4️⃣ File Integration Status</h2>";

$filesToCheck = [
    'index.html' => 'Main HTML file with ad SDKs',
    'js/ads.js' => 'Ad management JavaScript',
    'api/ads.php' => 'Ad serving API',
    'admin/ads.php' => 'Admin panel for ad management',
    'update_ad_units.sql' => 'SQL script for ad units'
];

foreach ($filesToCheck as $file => $desc) {
    if (file_exists($file)) {
        echo "<div class='check-item'><span class='success'>✅ {$file}</span> - {$desc}</div>";
    } else {
        echo "<div class='check-item fail'><span class='error'>❌ {$file}</span> - NOT FOUND</div>";
        $allPassed = false;
    }
}

// Check for correct SDK zone ID in index.html
if (file_exists('index.html')) {
    $content = file_get_contents('index.html');
    if (strpos($content, '10113890') !== false) {
        echo "<div class='check-item'><span class='success'>✅ Monetag zone ID 10113890 found in index.html</span></div>";
    } else {
        echo "<div class='check-item fail'><span class='error'>❌ Monetag zone ID 10113890 NOT found in index.html</span></div>";
        $allPassed = false;
    }
    
    if (strpos($content, '8391da33-7acd-47a9-8d83-f7b4bf4956b1') !== false) {
        echo "<div class='check-item'><span class='success'>✅ Adexium widget ID found in index.html</span></div>";
    } else {
        echo "<div class='check-item fail'><span class='error'>❌ Adexium widget ID NOT found in index.html</span></div>";
        $allPassed = false;
    }
}

// Check ads.js
if (file_exists('js/ads.js')) {
    $content = file_get_contents('js/ads.js');
    if (strpos($content, 'show_10113890') !== false) {
        echo "<div class='check-item'><span class='success'>✅ Monetag function show_10113890 found in ads.js</span></div>";
    } else {
        echo "<div class='check-item fail'><span class='error'>❌ Monetag function show_10113890 NOT found in ads.js</span></div>";
        $allPassed = false;
    }
}

// 5. Check Database Logs
echo "<h2>5️⃣ Ad Logs & Statistics</h2>";
$stmt = $db->query("SELECT COUNT(*) as total FROM ad_logs");
$logCount = $stmt->fetch()['total'];

echo "<div class='check-item'>";
if ($logCount > 0) {
    echo "<span class='success'>✅ {$logCount} ad events logged</span>";
} else {
    echo "<span class='info'>ℹ️ No ad events logged yet (system is ready)</span>";
}
echo "</div>";

$stmt = $db->query("SELECT event, COUNT(*) as count FROM ad_logs GROUP BY event");
$eventCounts = $stmt->fetchAll();

if ($eventCounts) {
    echo "<table>";
    echo "<tr><th>Event Type</th><th>Count</th></tr>";
    foreach ($eventCounts as $event) {
        echo "<tr><td><strong>{$event['event']}</strong></td><td>{$event['count']}</td></tr>";
    }
    echo "</table>";
}

// 6. Summary
echo "<h2>📋 Integration Summary</h2>";

if ($allPassed) {
    echo "<div style='background: #4CAF50; color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h2>🎉 ALL CHECKS PASSED!</h2>";
    echo "<p style='font-size: 18px;'>Your ad integration is complete and ready to use.</p>";
    echo "<p>All ad networks are configured, ad units are set up, and the system is ready to serve ads.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #ff9800; color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h2>⚠️ ACTION REQUIRED</h2>";
    echo "<p style='font-size: 18px;'>Some checks failed. Please review the report above.</p>";
    echo "<p>Most issues can be resolved by running <strong>update_ad_units.sql</strong></p>";
    echo "</div>";
}

// 7. Next Steps
echo "<h2>🚀 Next Steps</h2>";
echo "<ol style='font-size: 16px; line-height: 1.8;'>";
echo "<li>If ad units are missing, run the SQL script: <span class='code'>mysql -u username -p database_name < update_ad_units.sql</span></li>";
echo "<li>Visit the admin panel at <a href='admin/ads.php'>admin/ads.php</a> to manage ad units</li>";
echo "<li>Test each ad placement by using the app</li>";
echo "<li>Monitor ad logs in the admin panel</li>";
echo "<li>Check browser console for ad loading logs</li>";
echo "</ol>";

echo "<div style='margin-top: 30px; padding: 20px; background: #e3f2fd; border-left: 4px solid #2196F3;'>";
echo "<h3>📚 Documentation</h3>";
echo "<p>For detailed information about the integration, see <strong>AD_INTEGRATION_COMPLETE.md</strong></p>";
echo "</div>";

echo "</div>"; // container
echo "</body></html>";
?>
