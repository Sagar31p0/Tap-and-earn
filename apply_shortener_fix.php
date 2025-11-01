<?php
/**
 * Apply Shortener Ads Fix
 * 
 * This script fixes the issue where shortlink ads were not showing
 * by configuring the ad units for the shortlink placement.
 * 
 * INSTRUCTIONS:
 * 1. Access this file once via browser: https://your-domain.com/apply_shortener_fix.php
 * 2. The fix will be applied automatically
 * 3. Delete this file after successful application for security
 */

require_once 'config.php';

// Security: Only allow access from localhost or if not in production
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
if (!$isLocal && isset($_SERVER['HTTP_HOST']) && !isset($_GET['confirm'])) {
    die('For security, add ?confirm=yes to the URL to run this migration.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Shortener Ads</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #667eea; margin-top: 0; }
        .success { background: #10b981; color: white; padding: 15px; border-radius: 10px; margin: 20px 0; }
        .error { background: #ef4444; color: white; padding: 15px; border-radius: 10px; margin: 20px 0; }
        .info { background: #3b82f6; color: white; padding: 15px; border-radius: 10px; margin: 20px 0; }
        .warning { background: #f59e0b; color: white; padding: 15px; border-radius: 10px; margin: 20px 0; }
        pre { background: #f3f4f6; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .btn { 
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? Fix Shortener Ads Configuration</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check current state
            $stmt = $db->query("
                SELECT 
                    ap.id,
                    ap.placement_key,
                    ap.primary_ad_unit_id,
                    ap.secondary_ad_unit_id,
                    ap.tertiary_ad_unit_id
                FROM ad_placements ap
                WHERE ap.placement_key = 'shortlink'
            ");
            
            $currentPlacement = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$currentPlacement) {
                echo "<div class='error'>";
                echo "<strong>? Error:</strong> Shortlink placement not found in database!";
                echo "</div>";
                exit;
            }
            
            // Check if already fixed
            if ($currentPlacement['primary_ad_unit_id'] !== null) {
                echo "<div class='success'>";
                echo "<strong>? Already Fixed!</strong><br>";
                echo "The shortlink placement already has ad units configured.";
                echo "</div>";
                
                // Show current config
                $stmt = $db->query("
                    SELECT 
                        au1.name as primary_ad_name,
                        an1.name as primary_network,
                        au1.unit_code as primary_code,
                        au2.name as secondary_ad_name,
                        an2.name as secondary_network,
                        au2.unit_code as secondary_code
                    FROM ad_placements ap
                    LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
                    LEFT JOIN ad_networks an1 ON au1.network_id = an1.id
                    LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
                    LEFT JOIN ad_networks an2 ON au2.network_id = an2.id
                    WHERE ap.placement_key = 'shortlink'
                ");
                
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<div class='info'>";
                echo "<strong>Current Configuration:</strong><br><br>";
                echo "<strong>Primary Ad:</strong> {$config['primary_ad_name']} ({$config['primary_network']})<br>";
                echo "<strong>Code:</strong> {$config['primary_code']}<br><br>";
                if ($config['secondary_ad_name']) {
                    echo "<strong>Secondary Ad:</strong> {$config['secondary_ad_name']} ({$config['secondary_network']})<br>";
                    echo "<strong>Code:</strong> {$config['secondary_code']}";
                }
                echo "</div>";
                
                echo "<div class='warning'>";
                echo "<strong>?? Important:</strong> Please delete this file (apply_shortener_fix.php) for security!";
                echo "</div>";
                
            } else {
                // Apply the fix
                echo "<div class='info'>";
                echo "<strong>?? Current State:</strong> Shortlink placement has NO ad units configured<br>";
                echo "This is why ads are not showing on the shortener.";
                echo "</div>";
                
                echo "<div class='info'>";
                echo "<strong>?? Applying Fix...</strong>";
                echo "</div>";
                
                // Update the placement
                $sql = "UPDATE ad_placements 
                        SET 
                            primary_ad_unit_id = 3,
                            secondary_ad_unit_id = 9
                        WHERE placement_key = 'shortlink' AND id = 5";
                
                $result = $db->exec($sql);
                
                if ($result !== false) {
                    echo "<div class='success'>";
                    echo "<strong>? Fix Applied Successfully!</strong>";
                    echo "</div>";
                    
                    // Verify and show new config
                    $stmt = $db->query("
                        SELECT 
                            au1.name as primary_ad_name,
                            an1.name as primary_network,
                            au1.unit_code as primary_code,
                            au1.unit_type as primary_type,
                            au2.name as secondary_ad_name,
                            an2.name as secondary_network,
                            au2.unit_code as secondary_code,
                            au2.unit_type as secondary_type
                        FROM ad_placements ap
                        LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
                        LEFT JOIN ad_networks an1 ON au1.network_id = an1.id
                        LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
                        LEFT JOIN ad_networks an2 ON au2.network_id = an2.id
                        WHERE ap.placement_key = 'shortlink'
                    ");
                    
                    $config = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<div class='success'>";
                    echo "<strong>? New Configuration:</strong><br><br>";
                    echo "<strong>Primary Ad Unit:</strong><br>";
                    echo "? Name: {$config['primary_ad_name']}<br>";
                    echo "? Network: {$config['primary_network']}<br>";
                    echo "? Type: {$config['primary_type']}<br>";
                    echo "? Code: {$config['primary_code']}<br><br>";
                    echo "<strong>Secondary Ad Unit (Fallback):</strong><br>";
                    echo "? Name: {$config['secondary_ad_name']}<br>";
                    echo "? Network: {$config['secondary_network']}<br>";
                    echo "? Type: {$config['secondary_type']}<br>";
                    echo "? Code: {$config['secondary_code']}";
                    echo "</div>";
                    
                    echo "<div class='success'>";
                    echo "<strong>? Shortener should now display ads properly!</strong><br>";
                    echo "Test your shortlink to verify the fix.";
                    echo "</div>";
                    
                    echo "<div class='warning'>";
                    echo "<strong>?? IMPORTANT:</strong><br>";
                    echo "1. Delete this file (apply_shortener_fix.php) for security<br>";
                    echo "2. Test your shortlink to confirm ads are working<br>";
                    echo "3. See SHORTENER_ADS_FIX.md for detailed documentation";
                    echo "</div>";
                    
                } else {
                    echo "<div class='error'>";
                    echo "<strong>? Error:</strong> Failed to update placement";
                    echo "</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>? Error:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>
        
        <a href="/admin/" class="btn">? Back to Admin Panel</a>
    </div>
</body>
</html>
