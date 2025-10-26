<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $title = sanitizeInput($_POST['title']);
        $message = sanitizeInput($_POST['message']);
        $imageUrl = sanitizeInput($_POST['image_url']);
        $videoUrl = sanitizeInput($_POST['video_url']);
        $ctaText = sanitizeInput($_POST['cta_text']);
        $ctaUrl = sanitizeInput($_POST['cta_url']);
        $segment = $_POST['segment'];
        $status = $_POST['status'];
        
        $stmt = $db->prepare("INSERT INTO broadcasts (title, message, image_url, video_url, cta_text, cta_url, segment, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $imageUrl, $videoUrl, $ctaText, $ctaUrl, $segment, $status]);
        
        $broadcastId = $db->lastInsertId();
        
        // If status is 'sent', send immediately
        if ($status === 'sent') {
            sendBroadcast($broadcastId, $segment, $message, $imageUrl, $videoUrl);
        }
        
        $success = "Broadcast created successfully";
    } elseif ($action === 'send') {
        $id = (int)$_POST['id'];
        
        // Get broadcast details
        $stmt = $db->prepare("SELECT * FROM broadcasts WHERE id = ?");
        $stmt->execute([$id]);
        $broadcast = $stmt->fetch();
        
        if ($broadcast) {
            sendBroadcast($id, $broadcast['segment'], $broadcast['message'], $broadcast['image_url'], $broadcast['video_url']);
            
            // Update status
            $stmt = $db->prepare("UPDATE broadcasts SET status = 'sent' WHERE id = ?");
            $stmt->execute([$id]);
            
            $success = "Broadcast sent successfully";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM broadcasts WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Broadcast deleted successfully";
    }
}

// Function to send broadcast
function sendBroadcast($broadcastId, $segment, $message, $imageUrl = '', $videoUrl = '') {
    $db = Database::getInstance()->getConnection();
    
    // Get users based on segment
    switch ($segment) {
        case 'active':
            $stmt = $db->query("SELECT telegram_id FROM users WHERE DATE(last_active) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND is_banned = 0");
            break;
        case 'inactive':
            $stmt = $db->query("SELECT telegram_id FROM users WHERE DATE(last_active) < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND is_banned = 0");
            break;
        case 'all':
        default:
            $stmt = $db->query("SELECT telegram_id FROM users WHERE is_banned = 0");
            break;
    }
    
    $users = $stmt->fetchAll();
    $sentCount = 0;
    
    // Send to each user via Telegram Bot API
    foreach ($users as $user) {
        $telegramId = $user['telegram_id'];
        
        // Build message
        $sendUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $params = [
            'chat_id' => $telegramId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        // Send image if provided
        if (!empty($imageUrl)) {
            $sendUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto";
            $params = [
                'chat_id' => $telegramId,
                'photo' => $imageUrl,
                'caption' => $message,
                'parse_mode' => 'HTML'
            ];
        }
        
        // Send video if provided
        if (!empty($videoUrl)) {
            $sendUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendVideo";
            $params = [
                'chat_id' => $telegramId,
                'video' => $videoUrl,
                'caption' => $message,
                'parse_mode' => 'HTML'
            ];
        }
        
        // Send via cURL
        $ch = curl_init($sendUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        
        if ($result) {
            $sentCount++;
        }
        
        // Small delay to avoid rate limiting
        usleep(50000); // 50ms delay
    }
    
    // Update sent count
    $stmt = $db->prepare("UPDATE broadcasts SET sent_count = ? WHERE id = ?");
    $stmt->execute([$sentCount, $broadcastId]);
    
    return $sentCount;
}

// Get broadcasts
$stmt = $db->query("SELECT * FROM broadcasts ORDER BY created_at DESC");
$broadcasts = $stmt->fetchAll();

// Statistics
$stmt = $db->query("SELECT COUNT(*) FROM broadcasts WHERE status = 'sent'");
$totalSent = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(sent_count) FROM broadcasts WHERE status = 'sent'");
$totalMessages = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE is_banned = 0");
$totalUsers = $stmt->fetchColumn();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-bullhorn"></i> Broadcast Messages</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createBroadcastModal">
        <i class="fas fa-plus"></i> Create Broadcast
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Total Users</h6>
            <h3><?php echo number_format($totalUsers); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Broadcasts Sent</h6>
            <h3><?php echo number_format($totalSent); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Messages Delivered</h6>
            <h3><?php echo number_format($totalMessages); ?></h3>
        </div>
    </div>
</div>

<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> All Broadcasts</h5>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Segment</th>
                    <th>Sent Count</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($broadcasts as $broadcast): ?>
                <tr>
                    <td><?php echo $broadcast['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($broadcast['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars(substr($broadcast['message'], 0, 50)) . '...'; ?></td>
                    <td><span class="badge bg-info"><?php echo ucfirst($broadcast['segment']); ?></span></td>
                    <td><?php echo number_format($broadcast['sent_count']); ?></td>
                    <td>
                        <?php 
                        $badge = $broadcast['status'] === 'draft' ? 'secondary' : ($broadcast['status'] === 'sent' ? 'success' : 'warning');
                        echo "<span class='badge bg-{$badge}'>" . ucfirst($broadcast['status']) . "</span>";
                        ?>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($broadcast['created_at'])); ?></td>
                    <td>
                        <div class="btn-group">
                            <?php if ($broadcast['status'] === 'draft'): ?>
                                <button class="btn btn-sm btn-success" onclick="sendBroadcast(<?php echo $broadcast['id']; ?>)">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-info" onclick='viewBroadcast(<?php echo json_encode($broadcast); ?>)'>
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBroadcast(<?php echo $broadcast['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Broadcast Modal -->
<div class="modal fade" id="createBroadcastModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Broadcast</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Broadcast Title</label>
                        <input type="text" class="form-control" name="title" required>
                        <small class="text-muted">Internal title for reference</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                        <small class="text-muted">Supports HTML formatting</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image URL (Optional)</label>
                            <input type="url" class="form-control" name="image_url" placeholder="https://...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Video URL (Optional)</label>
                            <input type="url" class="form-control" name="video_url" placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Button Text (Optional)</label>
                            <input type="text" class="form-control" name="cta_text" placeholder="Click Here">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Button URL (Optional)</label>
                            <input type="url" class="form-control" name="cta_url" placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Segment</label>
                            <select class="form-select" name="segment" required>
                                <option value="all">All Users</option>
                                <option value="active">Active Users (last 7 days)</option>
                                <option value="inactive">Inactive Users (7+ days)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="draft">Save as Draft</option>
                                <option value="sent">Send Now</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Broadcast Modal -->
<div class="modal fade" id="viewBroadcastModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Broadcast Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Title</h6>
                <p id="view_title"></p>
                
                <h6>Message</h6>
                <p id="view_message" style="white-space: pre-wrap;"></p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Segment</h6>
                        <p id="view_segment"></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Sent Count</h6>
                        <p id="view_sent_count"></p>
                    </div>
                </div>
                
                <div id="view_media"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function sendBroadcast(id) {
    if (confirm('Send this broadcast now? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="send"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteBroadcast(id) {
    if (confirm('Delete this broadcast?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function viewBroadcast(broadcast) {
    document.getElementById('view_title').textContent = broadcast.title;
    document.getElementById('view_message').textContent = broadcast.message;
    document.getElementById('view_segment').textContent = broadcast.segment.charAt(0).toUpperCase() + broadcast.segment.slice(1);
    document.getElementById('view_sent_count').textContent = broadcast.sent_count;
    
    let mediaHtml = '';
    if (broadcast.image_url) {
        mediaHtml += '<h6>Image</h6><img src="' + broadcast.image_url + '" class="img-fluid mb-3">';
    }
    if (broadcast.video_url) {
        mediaHtml += '<h6>Video</h6><p><a href="' + broadcast.video_url + '" target="_blank">' + broadcast.video_url + '</a></p>';
    }
    if (broadcast.cta_text && broadcast.cta_url) {
        mediaHtml += '<h6>Call to Action</h6><p>' + broadcast.cta_text + ' - <a href="' + broadcast.cta_url + '" target="_blank">' + broadcast.cta_url + '</a></p>';
    }
    
    document.getElementById('view_media').innerHTML = mediaHtml;
    
    new bootstrap.Modal(document.getElementById('viewBroadcastModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
