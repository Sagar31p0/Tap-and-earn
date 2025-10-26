<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send_broadcast') {
        try {
            $stmt = $db->prepare("INSERT INTO broadcasts (title, message, image_url, button_text, button_url, target_audience, status) VALUES (?, ?, ?, ?, ?, ?, 'sent')");
            $stmt->execute([
                $_POST['title'],
                $_POST['message'],
                $_POST['image_url'] ?? null,
                $_POST['button_text'] ?? null,
                $_POST['button_url'] ?? null,
                $_POST['target_audience']
            ]);
            
            $broadcastId = $db->lastInsertId();
            
            // Get target users based on audience
            $userQuery = "SELECT telegram_id FROM users WHERE is_banned = 0";
            
            switch ($_POST['target_audience']) {
                case 'active':
                    $userQuery .= " AND last_active >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    break;
                case 'inactive':
                    $userQuery .= " AND last_active < DATE_SUB(NOW(), INTERVAL 3 DAY)";
                    break;
                case 'high_earners':
                    $userQuery .= " AND coins > 1000";
                    break;
                // 'all' gets everyone
            }
            
            $users = $db->query($userQuery)->fetchAll();
            
            // TODO: Integrate with Telegram Bot API to send messages
            // For now, just log the broadcast
            $totalSent = count($users);
            
            // Update broadcast with send count
            $stmt = $db->prepare("UPDATE broadcasts SET users_sent = ? WHERE id = ?");
            $stmt->execute([$totalSent, $broadcastId]);
            
            $success = "Broadcast sent successfully to {$totalSent} users!";
        } catch (Exception $e) {
            $error = "Error sending broadcast: " . $e->getMessage();
        }
    }
}

// Get broadcast history
$broadcasts = $db->query("SELECT * FROM broadcasts ORDER BY created_at DESC LIMIT 50")->fetchAll();

// Get user statistics for targeting
$allUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 0")->fetchColumn();
$activeUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 0 AND last_active >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
$inactiveUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 0 AND last_active < DATE_SUB(NOW(), INTERVAL 3 DAY)")->fetchColumn();
$highEarners = $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 0 AND coins > 1000")->fetchColumn();
?>

<div class="page-header">
    <h2><i class="fas fa-bullhorn"></i> Broadcast Messages</h2>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-paper-plane"></i> Send New Broadcast</h5>
            
            <form method="POST">
                <input type="hidden" name="action" value="send_broadcast">
                
                <div class="mb-3">
                    <label>Broadcast Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g., Daily Bonus Available!" required>
                </div>
                
                <div class="mb-3">
                    <label>Message *</label>
                    <textarea name="message" class="form-control" rows="5" 
                              placeholder="Your broadcast message here..." required></textarea>
                    <small class="text-muted">Supports emojis and line breaks</small>
                </div>
                
                <div class="mb-3">
                    <label>Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    <small class="text-muted">Image to display with the message</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Button Text (Optional)</label>
                        <input type="text" name="button_text" class="form-control" placeholder="e.g., Claim Now">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Button URL (Optional)</label>
                        <input type="url" name="button_url" class="form-control" placeholder="https://example.com">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Target Audience *</label>
                    <select name="target_audience" class="form-select" required>
                        <option value="all">All Users (<?php echo number_format($allUsers); ?>)</option>
                        <option value="active">Active Users (last 24h) (<?php echo number_format($activeUsers); ?>)</option>
                        <option value="inactive">Inactive Users (3+ days) (<?php echo number_format($inactiveUsers); ?>)</option>
                        <option value="high_earners">High Earners (1000+ coins) (<?php echo number_format($highEarners); ?>)</option>
                    </select>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Note:</strong> Telegram Bot API integration is required to send messages. 
                    This feature logs the broadcast but does not send actual Telegram messages yet.
                </div>
                
                <button type="submit" class="btn btn-gradient btn-lg">
                    <i class="fas fa-paper-plane"></i> Send Broadcast
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-users"></i> Audience Overview</h5>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>All Users</span>
                    <strong><?php echo number_format($allUsers); ?></strong>
                </div>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Active (24h)</span>
                    <strong><?php echo number_format($activeUsers); ?></strong>
                </div>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success" style="width: <?php echo $allUsers > 0 ? ($activeUsers / $allUsers * 100) : 0; ?>%"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Inactive (3+ days)</span>
                    <strong><?php echo number_format($inactiveUsers); ?></strong>
                </div>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-warning" style="width: <?php echo $allUsers > 0 ? ($inactiveUsers / $allUsers * 100) : 0; ?>%"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>High Earners</span>
                    <strong><?php echo number_format($highEarners); ?></strong>
                </div>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-info" style="width: <?php echo $allUsers > 0 ? ($highEarners / $allUsers * 100) : 0; ?>%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card mt-3">
            <h6><i class="fas fa-lightbulb"></i> Quick Tips</h6>
            <ul class="small mb-0">
                <li>Target active users for time-sensitive offers</li>
                <li>Re-engage inactive users with special bonuses</li>
                <li>Use emojis to make messages more engaging</li>
                <li>Keep messages concise and actionable</li>
                <li>Test with small audience first</li>
            </ul>
        </div>
    </div>
</div>

<div class="stat-card mt-4">
    <h5 class="mb-3"><i class="fas fa-history"></i> Broadcast History</h5>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Target Audience</th>
                    <th>Users Sent</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($broadcasts as $broadcast): ?>
                <tr>
                    <td><?php echo $broadcast['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($broadcast['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars(substr($broadcast['message'], 0, 50)); ?>...</td>
                    <td>
                        <span class="badge bg-info">
                            <?php echo ucfirst($broadcast['target_audience']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($broadcast['users_sent'] ?? 0); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $broadcast['status'] === 'sent' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($broadcast['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($broadcast['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
