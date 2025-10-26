<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    
    if ($action === 'ban' && $userId) {
        $stmt = $db->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        $success = "User banned successfully";
    } elseif ($action === 'unban' && $userId) {
        $stmt = $db->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
        $stmt->execute([$userId]);
        $success = "User unbanned successfully";
    } elseif ($action === 'delete' && $userId) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $success = "User deleted successfully";
    } elseif ($action === 'adjust_coins' && $userId) {
        $amount = (float)$_POST['amount'];
        $type = $_POST['type'];
        $description = sanitizeInput($_POST['description'] ?? 'Admin adjustment');
        
        if ($type === 'add') {
            updateUserCoins($userId, $amount, true);
            addTransaction($userId, 'admin_credit', $amount, $description);
        } else {
            updateUserCoins($userId, $amount, false);
            addTransaction($userId, 'admin_debit', -$amount, $description);
        }
        $success = "Coins adjusted successfully";
    }
}

// Get users
$stmt = $db->query("SELECT u.*, us.total_taps, us.total_spins, us.tasks_completed, us.ads_watched 
                    FROM users u 
                    LEFT JOIN user_stats us ON u.id = us.user_id 
                    ORDER BY u.id DESC");
$users = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-users"></i> User Management</h2>
    <button class="btn btn-gradient" onclick="exportUsers()">
        <i class="fas fa-download"></i> Export Users
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Telegram ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Coins</th>
                    <th>Taps</th>
                    <th>Spins</th>
                    <th>Tasks</th>
                    <th>Ads</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['telegram_id']; ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td>@<?php echo htmlspecialchars($user['username'] ?: 'N/A'); ?></td>
                    <td><?php echo number_format($user['coins'], 2); ?></td>
                    <td><?php echo number_format($user['total_taps'] ?: 0); ?></td>
                    <td><?php echo number_format($user['total_spins'] ?: 0); ?></td>
                    <td><?php echo number_format($user['tasks_completed'] ?: 0); ?></td>
                    <td><?php echo number_format($user['ads_watched'] ?: 0); ?></td>
                    <td>
                        <?php if ($user['is_banned']): ?>
                            <span class="badge bg-danger">Banned</span>
                        <?php else: ?>
                            <span class="badge bg-success">Active</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick="adjustCoins(<?php echo $user['id']; ?>)">
                                <i class="fas fa-coins"></i>
                            </button>
                            <?php if ($user['is_banned']): ?>
                                <button class="btn btn-sm btn-success" onclick="unbanUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-warning" onclick="banUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
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

<!-- Adjust Coins Modal -->
<div class="modal fade" id="adjustCoinsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust User Coins</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="adjust_coins">
                    <input type="hidden" name="user_id" id="adjust_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="add">Add Coins</option>
                            <option value="deduct">Deduct Coins</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" value="Admin adjustment" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Coins</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function adjustCoins(userId) {
    document.getElementById('adjust_user_id').value = userId;
    new bootstrap.Modal(document.getElementById('adjustCoinsModal')).show();
}

function banUser(userId) {
    if (confirm('Are you sure you want to ban this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="ban"><input name="user_id" value="' + userId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function unbanUser(userId) {
    if (confirm('Are you sure you want to unban this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="unban"><input name="user_id" value="' + userId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to DELETE this user? This action cannot be undone!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="user_id" value="' + userId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function exportUsers() {
    window.location.href = 'export_users.php';
}
</script>

<?php require_once 'footer.php'; ?>
