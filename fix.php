<?php
// Handle AJAX request first, before any HTML output
if (isset($_GET['action']) && $_GET['action'] === 'fix') {
    header('Content-Type: application/json');
    
    try {
        require_once 'config.php';
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        $changes = [];
        
        // Fix 1: Tap ad frequency
        $stmt = $db->prepare("UPDATE settings SET setting_value = '2', updated_at = NOW() WHERE setting_key = 'tap_ad_frequency'");
        $stmt->execute();
        $changes[] = "Tap ad frequency: 5 → 2 taps";
        
        // Fix 2: Spin daily limit
        $stmt = $db->prepare("UPDATE settings SET setting_value = '500', updated_at = NOW() WHERE setting_key = 'spin_daily_limit'");
        $stmt->execute();
        $changes[] = "Spin daily limit: 10 → 500 spins";
        
        // Fix 3: Tap placement frequency
        $stmt = $db->prepare("UPDATE ad_placements SET frequency = 2 WHERE placement_key = 'tap'");
        $stmt->execute();
        $changes[] = "Tap placement frequency: 5 → 2";
        
        // Fix 4: Create Watch Ad task
        $stmt = $db->prepare("SELECT id FROM tasks WHERE url = '#watch-ad' AND ad_network = 'adsgram'");
        $stmt->execute();
        $existingTask = $stmt->fetch();
        
        if (!$existingTask) {
            $stmt = $db->prepare("
                INSERT INTO tasks (title, description, url, reward, icon, type, is_active, sort_order, ad_network, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'Watch Ad & Earn',
                'Watch a video advertisement and earn coins instantly',
                '#watch-ad',
                50.00,
                'fas fa-video',
                'daily',
                1,
                1,
                'adsgram'
            ]);
            $changes[] = "Created new 'Watch Ad' task with Adsgram";
        } else {
            $changes[] = "Watch Ad task already exists (no change needed)";
        }
        
        // Fix 5: Enable all ad networks
        $stmt = $db->prepare("UPDATE ad_networks SET is_enabled = 1 WHERE name IN ('adexium', 'monetag', 'adsgram', 'richads')");
        $stmt->execute();
        $changes[] = "Enabled all ad networks (Adexium, Monetag, Adsgram, Richads)";
        
        // Fix 6: Activate critical ad units
        $stmt = $db->prepare("UPDATE ad_units SET is_active = 1 WHERE id IN (1, 2, 3, 4, 5, 6, 7)");
        $stmt->execute();
        $changes[] = "Activated all configured ad units";
        
        $db->commit();
        
        // Verification
        $verification = '<table>';
        $verification .= '<tr><th>Setting</th><th>Value</th></tr>';
        
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('tap_ad_frequency', 'spin_daily_limit', 'spin_interval_minutes')");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($settings as $setting) {
            $label = ucwords(str_replace('_', ' ', $setting['setting_key']));
            $verification .= "<tr><td>{$label}</td><td><strong>{$setting['setting_value']}</strong></td></tr>";
        }
        
        $verification .= '</table>';
        
        echo json_encode([
            'success' => true,
            'changes' => $changes,
            'verification' => $verification
        ]);
        
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Panel Settings</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .issue {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .issue.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .issue h3 {
            color: #dc3545;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .issue.success h3 {
            color: #28a745;
        }
        .issue p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        .result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .result h2 {
            margin-bottom: 10px;
            font-size: 20px;
        }
        .result ul {
            list-style: none;
            padding-left: 0;
        }
        .result li {
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        .result li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning strong {
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 14px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Admin Panel Settings</h1>
        <p class="subtitle">This tool will fix all connectivity issues between admin panel and bot features</p>
        
        <div class="warning">
            <strong>⚠️ Important:</strong> This script will update database settings to fix all issues. Make sure you have a backup before proceeding.
        </div>
        
        <h2 style="margin: 20px 0 15px 0; color: #333;">Issues Found:</h2>
        
        <div class="issue">
            <h3>❌ Issue 1: Tap Ad Frequency</h3>
            <p>You set 2 taps in admin panel, but the value in database is still 5. Users are seeing ads after 5 taps instead of 2.</p>
        </div>
        
        <div class="issue">
            <h3>❌ Issue 2: Spin Daily Limit</h3>
            <p>You set daily 500 spins, but database shows 10. Users can only spin 10 times per day instead of 500.</p>
        </div>
        
        <div class="issue">
            <h3>❌ Issue 3: Tap Placement Frequency</h3>
            <p>Ad placement frequency is set to 5, needs to match tap ad frequency of 2 taps.</p>
        </div>
        
        <div class="issue">
            <h3>⚠️ Issue 4: Task System</h3>
            <p>Normal URL tasks are working, but no special "Watch Ad" task exists for Adsgram task ads.</p>
        </div>
        
        <div class="issue">
            <h3>⚠️ Issue 5: Ad Networks</h3>
            <p>Ad placements are configured but integration needs verification. Adexium, Monetag, and Richads should show properly after fix.</p>
        </div>
        
        <button class="btn" onclick="fixIssues()">🚀 Fix All Issues Now</button>
        
        <div class="result" id="result"></div>
    </div>
    
    <script>
        async function fixIssues() {
            const btn = document.querySelector('.btn');
            btn.disabled = true;
            btn.textContent = '⏳ Fixing issues...';
            
            try {
                const response = await fetch('fix.php?action=fix', {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                
                if (data.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h2>✅ All Issues Fixed Successfully!</h2>
                        <ul>
                            ${data.changes.map(change => `<li>${change}</li>`).join('')}
                        </ul>
                        <h3 style="margin-top: 20px;">Verification Results:</h3>
                        ${data.verification}
                    `;
                    btn.textContent = '✅ Fixed Successfully';
                    btn.style.background = '#28a745';
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `
                    <h2>❌ Error</h2>
                    <p>${error.message}</p>
                `;
                btn.disabled = false;
                btn.textContent = '🔄 Try Again';
            }
        }
    </script>
</body>
</html>
