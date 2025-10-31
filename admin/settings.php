<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                // Ensure numeric values are properly formatted
                if (is_numeric($value)) {
                    $value = $value + 0; // Convert to proper numeric type
                }
                updateSetting($key, $value);
            }
        }
        
        $db->commit();
        $success = "Settings updated successfully!";
        
        // Clear any opcode cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = "Failed to update settings: " . $e->getMessage();
        error_log("Settings update error: " . $e->getMessage());
    }
}

// Get all settings
$stmt = $db->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="page-header">
    <h2><i class="fas fa-cog"></i> Global Settings</h2>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-robot"></i> Bot Information</h5>
                
                <div class="mb-3">
                    <label class="form-label">Bot Name</label>
                    <input type="text" class="form-control" name="bot_name" value="<?php echo htmlspecialchars($settings['bot_name'] ?? 'Earn Bot'); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Bot Username</label>
                    <input type="text" class="form-control" name="bot_username" value="<?php echo htmlspecialchars($settings['bot_username'] ?? '@kuchpvildybot'); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Welcome Message</label>
                    <textarea class="form-control" name="welcome_message" rows="3"><?php echo htmlspecialchars($settings['welcome_message'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Support Contact</label>
                    <input type="text" class="form-control" name="support_contact" value="<?php echo htmlspecialchars($settings['support_contact'] ?? '@support'); ?>">
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-hand-pointer"></i> Tap & Earn Settings</h5>
                
                <div class="mb-3">
                    <label class="form-label">Coins Per Tap</label>
                    <input type="number" class="form-control" name="tap_reward" step="0.01" value="<?php echo $settings['tap_reward'] ?? 5; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Energy Per Tap</label>
                    <input type="number" class="form-control" name="energy_per_tap" value="<?php echo $settings['energy_per_tap'] ?? 1; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Energy Recharge Rate (%)</label>
                    <input type="number" class="form-control" name="energy_recharge_rate" value="<?php echo $settings['energy_recharge_rate'] ?? 5; ?>">
                    <small class="text-muted">% of energy recharged per interval</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Recharge Interval (seconds)</label>
                    <input type="number" class="form-control" name="energy_recharge_interval" value="<?php echo $settings['energy_recharge_interval'] ?? 300; ?>">
                    <small class="text-muted">Default: 300 seconds (5 minutes)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Watch Ad Energy Recharge (%)</label>
                    <input type="number" class="form-control" name="watch_ad_energy" value="<?php echo $settings['watch_ad_energy'] ?? 5; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tap Ad Frequency</label>
                    <input type="number" class="form-control" name="tap_ad_frequency" value="<?php echo $settings['tap_ad_frequency'] ?? 7; ?>">
                    <small class="text-muted">Show ad every N taps</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-circle-notch"></i> Spin Wheel Settings</h5>
                
                <div class="mb-3">
                    <label class="form-label">Spin Interval (minutes)</label>
                    <input type="number" class="form-control" name="spin_interval_minutes" value="<?php echo $settings['spin_interval_minutes'] ?? 60; ?>">
                    <small class="text-muted">Time between free spins</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Daily Spin Limit</label>
                    <input type="number" class="form-control" name="spin_daily_limit" value="<?php echo $settings['spin_daily_limit'] ?? 10; ?>">
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-user-friends"></i> Referral Settings</h5>
                
                <div class="mb-3">
                    <label class="form-label">Referral Reward (coins)</label>
                    <input type="number" class="form-control" name="referral_reward" step="0.01" value="<?php echo $settings['referral_reward'] ?? 100; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Unlock Condition (tasks completed)</label>
                    <input type="number" class="form-control" name="referral_unlock_tasks" value="<?php echo $settings['referral_unlock_tasks'] ?? 1; ?>">
                    <small class="text-muted">Friend must complete N tasks</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-wallet"></i> Withdrawal Settings</h5>
                
                <div class="mb-3">
                    <label class="form-label">Minimum Withdrawal (coins)</label>
                    <input type="number" class="form-control" name="min_withdrawal" step="0.01" value="<?php echo $settings['min_withdrawal'] ?? 10; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Coin to USD Rate</label>
                    <input type="number" class="form-control" name="coin_to_usd_rate" step="0.0001" value="<?php echo $settings['coin_to_usd_rate'] ?? 0.001; ?>">
                    <small class="text-muted">1 coin = ? USD</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3"><i class="fas fa-trophy"></i> Leaderboard Settings</h5>
                
                <div class="mb-3">
                    <label class="form-label">Leaderboard Type</label>
                    <select class="form-select" name="leaderboard_type">
                        <option value="coins" <?php echo ($settings['leaderboard_type'] ?? 'coins') === 'coins' ? 'selected' : ''; ?>>Coins</option>
                        <option value="tasks" <?php echo ($settings['leaderboard_type'] ?? 'coins') === 'tasks' ? 'selected' : ''; ?>>Tasks Completed</option>
                        <option value="referrals" <?php echo ($settings['leaderboard_type'] ?? 'coins') === 'referrals' ? 'selected' : ''; ?>>Referrals</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reset Frequency</label>
                    <select class="form-select" name="leaderboard_reset">
                        <option value="daily" <?php echo ($settings['leaderboard_reset'] ?? 'monthly') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo ($settings['leaderboard_reset'] ?? 'monthly') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo ($settings['leaderboard_reset'] ?? 'monthly') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Theme Mode</label>
                    <select class="form-select" name="theme_mode">
                        <option value="auto" <?php echo ($settings['theme_mode'] ?? 'auto') === 'auto' ? 'selected' : ''; ?>>Auto</option>
                        <option value="light" <?php echo ($settings['theme_mode'] ?? 'auto') === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo ($settings['theme_mode'] ?? 'auto') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <button type="submit" name="submit" class="btn btn-gradient btn-lg px-5" id="saveBtn">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>
</form>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-broom"></i> Cache Management</h5>
            <p class="text-muted">Clear all cached data including OpCache, sessions, and temporary files to improve performance and apply changes immediately.</p>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="clearSessions">
                    <label class="form-check-label" for="clearSessions">
                        Also clear user sessions (will log out all users except you)
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-warning" id="clearCacheBtn">
                    <i class="fas fa-broom"></i> Force Clear Cache
                </button>
                <div id="cacheStatus" class="ms-3"></div>
            </div>
            
            <div id="cacheResults" class="mt-3" style="display: none;">
                <div class="alert alert-info">
                    <strong>Cache Cleared:</strong>
                    <ul id="cacheList" class="mb-0 mt-2"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add form validation and loading state
document.querySelector('form').addEventListener('submit', function(e) {
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
});

// Show success message and re-enable button after page load
window.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save All Settings';
    }
});

// Cache clearing functionality
document.getElementById('clearCacheBtn').addEventListener('click', function() {
    const btn = this;
    const statusDiv = document.getElementById('cacheStatus');
    const resultsDiv = document.getElementById('cacheResults');
    const cacheList = document.getElementById('cacheList');
    const clearSessions = document.getElementById('clearSessions').checked;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing Cache...';
    statusDiv.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Processing...</span>';
    resultsDiv.style.display = 'none';
    
    // Send AJAX request
    fetch('cache.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'clear_sessions=' + clearSessions
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-broom"></i> Force Clear Cache';
        
        if (data.success) {
            statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            
            // Show what was cleared
            cacheList.innerHTML = '';
            data.cleared.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                cacheList.appendChild(li);
            });
            resultsDiv.style.display = 'block';
            
            // Hide status after 3 seconds
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 5000);
        } else {
            statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-broom"></i> Force Clear Cache';
        statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error: ' + error.message + '</span>';
        console.error('Cache clear error:', error);
    });
});
</script>

<?php require_once 'footer.php'; ?>
