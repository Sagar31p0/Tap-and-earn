<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostic Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .check-item {
            padding: 12px;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .info {
            color: #666;
            font-size: 0.9em;
        }
        .code {
            background: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .recommendations {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .recommendations h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        .recommendations ul {
            margin-left: 20px;
        }
        .recommendations li {
            margin-bottom: 10px;
            color: #856404;
        }
        .delete-warning {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
        }
        .delete-warning strong {
            color: #721c24;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? System Diagnostic Check</h1>
        <p class="subtitle">Checking tap points and shortener ads configuration...</p>

        <?php
        require_once 'config.php';
        $db = Database::getInstance()->getConnection();
        
        $issues = [];
        $warnings = [];
        $allOk = true;
        ?>

        <!-- Check 1: Tap Reward Setting -->
        <div class="section">
            <h2>1. Tap & Earn Configuration</h2>
            
            <?php
            try {
                $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tap_reward'");
                $stmt->execute();
                $result = $stmt->fetch();
                
                $dbValue = $result ? $result['setting_value'] : null;
                $funcValue = getSetting('tap_reward', 5);
                
                if ($dbValue === null) {
                    $issues[] = "tap_reward setting not found in database";
                    $allOk = false;
                    echo '<div class="check-item"><span>Database Setting</span><span class="status error">NOT FOUND</span></div>';
                } else {
                    echo '<div class="check-item"><span>Database Value</span><span class="status ok">' . $dbValue . ' points</span></div>';
                }
                
                echo '<div class="check-item"><span>getSetting() Returns</span><span class="status ' . ($funcValue == $dbValue ? 'ok' : 'error') . '">' . $funcValue . ' points</span></div>';
                
                if ($funcValue != $dbValue) {
                    $issues[] = "tap_reward mismatch: DB has '$dbValue' but getSetting() returns '$funcValue' - CACHE ISSUE!";
                    $allOk = false;
                    echo '<div class="check-item"><span>Status</span><span class="status error">MISMATCH - CACHE PROBLEM!</span></div>';
                } else {
                    echo '<div class="check-item"><span>Status</span><span class="status ok">Correct</span></div>';
                }
                
                // Check if value is the expected 1
                if ($dbValue != '1' && $dbValue != 1) {
                    $warnings[] = "tap_reward is set to $dbValue, but you mentioned it should be 1";
                    echo '<div class="check-item"><span>Expected Value</span><span class="status warning">Should be 1?</span></div>';
                }
            } catch (Exception $e) {
                echo '<div class="check-item"><span>Error</span><span class="status error">' . htmlspecialchars($e->getMessage()) . '</span></div>';
                $issues[] = "Database query error: " . $e->getMessage();
                $allOk = false;
            }
            ?>
        </div>

        <!-- Check 2: Shortener Placement -->
        <div class="section">
            <h2>2. Shortener Ad Placement</h2>
            
            <?php
            try {
                $stmt = $db->prepare("
                    SELECT 
                        ap.*, 
                        au.name as ad_unit_name,
                        au.is_active as unit_active,
                        au.unit_code,
                        an.name as network_name,
                        an.is_enabled as network_enabled
                    FROM ad_placements ap
                    LEFT JOIN ad_units au ON ap.primary_ad_unit_id = au.id
                    LEFT JOIN ad_networks an ON au.network_id = an.id
                    WHERE ap.placement_key = 'shortlink'
                ");
                $stmt->execute();
                $placement = $stmt->fetch();
                
                if (!$placement) {
                    $issues[] = "Shortlink placement not found in database";
                    $allOk = false;
                    echo '<div class="check-item"><span>Placement</span><span class="status error">NOT FOUND</span></div>';
                } else {
                    echo '<div class="check-item"><span>Placement Found</span><span class="status ok">Yes (ID: ' . $placement['id'] . ')</span></div>';
                    
                    if ($placement['primary_ad_unit_id']) {
                        echo '<div class="check-item"><span>Primary Ad Unit</span><span class="status ok">' . htmlspecialchars($placement['ad_unit_name'] ?: 'ID: ' . $placement['primary_ad_unit_id']) . '</span></div>';
                        
                        if ($placement['ad_unit_name']) {
                            echo '<div class="check-item"><span>Ad Network</span><span class="status ok">' . htmlspecialchars($placement['network_name']) . '</span></div>';
                            echo '<div class="check-item"><span>Unit Code</span><span class="status ok">' . htmlspecialchars($placement['unit_code']) . '</span></div>';
                            
                            if (!$placement['unit_active']) {
                                $issues[] = "Primary ad unit for shortlink is INACTIVE";
                                $allOk = false;
                                echo '<div class="check-item"><span>Ad Unit Status</span><span class="status error">INACTIVE</span></div>';
                            } else {
                                echo '<div class="check-item"><span>Ad Unit Status</span><span class="status ok">Active</span></div>';
                            }
                            
                            if (!$placement['network_enabled']) {
                                $issues[] = "Ad network for shortlink is DISABLED";
                                $allOk = false;
                                echo '<div class="check-item"><span>Network Status</span><span class="status error">DISABLED</span></div>';
                            } else {
                                echo '<div class="check-item"><span>Network Status</span><span class="status ok">Enabled</span></div>';
                            }
                        } else {
                            $issues[] = "Primary ad unit ID exists but unit not found";
                            $allOk = false;
                            echo '<div class="check-item"><span>Ad Unit</span><span class="status error">UNIT NOT FOUND</span></div>';
                        }
                    } else {
                        $issues[] = "No primary ad unit assigned to shortlink placement";
                        $allOk = false;
                        echo '<div class="check-item"><span>Primary Ad Unit</span><span class="status error">NOT ASSIGNED</span></div>';
                    }
                }
            } catch (Exception $e) {
                echo '<div class="check-item"><span>Error</span><span class="status error">' . htmlspecialchars($e->getMessage()) . '</span></div>';
                $issues[] = "Database query error: " . $e->getMessage();
                $allOk = false;
            }
            ?>
        </div>

        <!-- Check 3: Cache Status -->
        <div class="section">
            <h2>3. Cache Status</h2>
            
            <?php
            $opcacheEnabled = function_exists('opcache_get_status') && opcache_get_status();
            $apcuEnabled = function_exists('apcu_cache_info');
            
            echo '<div class="check-item"><span>OpCache</span><span class="status ' . ($opcacheEnabled ? 'warning' : 'ok') . '">' . ($opcacheEnabled ? 'Enabled (May cause issues)' : 'Disabled') . '</span></div>';
            echo '<div class="check-item"><span>APCu Cache</span><span class="status ' . ($apcuEnabled ? 'warning' : 'ok') . '">' . ($apcuEnabled ? 'Enabled' : 'Disabled') . '</span></div>';
            
            if ($opcacheEnabled || $apcuEnabled) {
                $warnings[] = "Caching is enabled - settings changes may not appear immediately without cache clear";
            }
            ?>
        </div>

        <!-- Check 4: PHP Configuration -->
        <div class="section">
            <h2>4. PHP Environment</h2>
            
            <?php
            echo '<div class="check-item"><span>PHP Version</span><span class="status ok">' . phpversion() . '</span></div>';
            echo '<div class="check-item"><span>PDO Available</span><span class="status ok">Yes</span></div>';
            echo '<div class="check-item"><span>Database Connected</span><span class="status ok">Yes</span></div>';
            ?>
        </div>

        <!-- Summary and Recommendations -->
        <?php if (!$allOk || count($warnings) > 0): ?>
        <div class="recommendations">
            <h3>?? Issues Found</h3>
            
            <?php if (count($issues) > 0): ?>
            <strong>Critical Issues:</strong>
            <ul>
                <?php foreach ($issues as $issue): ?>
                <li><?php echo htmlspecialchars($issue); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php if (count($warnings) > 0): ?>
            <strong>Warnings:</strong>
            <ul>
                <?php foreach ($warnings as $warning): ?>
                <li><?php echo htmlspecialchars($warning); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <h3 style="margin-top: 20px;">?? Recommended Actions:</h3>
            <ul>
                <?php if (strpos(implode(' ', $issues), 'CACHE') !== false): ?>
                <li><strong>URGENT:</strong> Go to Admin Panel ? Settings ? Scroll down ? Click "Force Clear Cache"</li>
                <li>If that doesn't work, restart your web server or PHP-FPM service</li>
                <?php endif; ?>
                
                <?php if (strpos(implode(' ', $issues), 'shortlink') !== false): ?>
                <li>Go to Admin Panel ? Ads ? Ad Placements</li>
                <li>Check that "shortlink" placement has an active ad unit assigned</li>
                <li>Verify the ad unit and its network are both enabled</li>
                <?php endif; ?>
                
                <?php if (strpos(implode(' ', $issues), 'tap_reward') !== false && strpos(implode(' ', $issues), 'NOT FOUND') !== false): ?>
                <li>Go to Admin Panel ? Settings</li>
                <li>Set "Coins Per Tap" to 1</li>
                <li>Click "Save All Settings"</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="section" style="background: #d4edda; border-left-color: #28a745;">
            <h2 style="color: #155724;">? All Checks Passed!</h2>
            <p style="color: #155724;">Your system configuration looks good. If you're still experiencing issues, they may be client-side (browser cache, Telegram app cache).</p>
        </div>
        <?php endif; ?>

        <div class="delete-warning">
            <strong>?? SECURITY WARNING</strong>
            <p style="margin-top: 10px; color: #721c24;">Delete this diagnostic file (diagnostic_check.php) after reviewing results to prevent unauthorized access to system information.</p>
        </div>

        <p class="info" style="margin-top: 30px; text-align: center; color: #999;">
            Diagnostic run at: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
