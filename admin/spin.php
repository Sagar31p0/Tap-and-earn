<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $label = sanitizeInput($_POST['label']);
        $reward = (float)$_POST['reward'];
        $probability = (float)$_POST['probability'];
        $allowDouble = isset($_POST['allow_double']) ? 1 : 0;
        $sortOrder = (int)$_POST['sort_order'];
        
        $stmt = $db->prepare("INSERT INTO spin_config (block_label, reward_value, probability, allow_double, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$label, $reward, $probability, $allowDouble, $sortOrder]);
        $success = "Spin block added successfully";
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $label = sanitizeInput($_POST['label']);
        $reward = (float)$_POST['reward'];
        $probability = (float)$_POST['probability'];
        $allowDouble = isset($_POST['allow_double']) ? 1 : 0;
        $sortOrder = (int)$_POST['sort_order'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE spin_config SET block_label = ?, reward_value = ?, probability = ?, allow_double = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$label, $reward, $probability, $allowDouble, $sortOrder, $isActive, $id]);
        $success = "Spin block updated successfully";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM spin_config WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Spin block deleted successfully";
    } elseif ($action === 'update_settings') {
        updateSetting('spin_interval_minutes', $_POST['spin_interval']);
        updateSetting('spin_daily_limit', $_POST['daily_limit']);
        $success = "Spin settings updated successfully";
    }
}

// Get spin blocks
$stmt = $db->query("SELECT * FROM spin_config ORDER BY sort_order ASC");
$spinBlocks = $stmt->fetchAll();

// Get settings
$spinInterval = getSetting('spin_interval_minutes', 60);
$dailyLimit = getSetting('spin_daily_limit', 10);

// Get statistics
$stmt = $db->query("SELECT COUNT(*) FROM user_spins");
$totalUsers = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(spins_today) FROM user_spins WHERE last_reset = CURDATE()");
$spinsToday = $stmt->fetchColumn() ?: 0;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-circle-notch"></i> Spin Wheel Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addBlockModal">
        <i class="fas fa-plus"></i> Add Spin Block
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
            <h6 class="text-muted">Total Users Spinning</h6>
            <h3><?php echo number_format($totalUsers); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Spins Today</h6>
            <h3><?php echo number_format($spinsToday); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Total Reward Value</h6>
            <h3><?php echo number_format(array_sum(array_column($spinBlocks, 'reward_value'))); ?></h3>
        </div>
    </div>
</div>

<div class="stat-card mb-4">
    <h5 class="mb-3"><i class="fas fa-cog"></i> Spin Settings</h5>
    <form method="POST">
        <input type="hidden" name="action" value="update_settings">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Spin Interval (Minutes)</label>
                    <input type="number" class="form-control" name="spin_interval" value="<?php echo $spinInterval; ?>" required>
                    <small class="text-muted">Time between each free spin</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Daily Spin Limit</label>
                    <input type="number" class="form-control" name="daily_limit" value="<?php echo $dailyLimit; ?>" required>
                    <small class="text-muted">Maximum spins per day per user</small>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Settings</button>
    </form>
</div>

<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> Spin Wheel Blocks</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Sort Order</th>
                    <th>Label</th>
                    <th>Reward Value</th>
                    <th>Probability (%)</th>
                    <th>Allow Double</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($spinBlocks as $block): ?>
                <tr>
                    <td><?php echo $block['sort_order']; ?></td>
                    <td><strong><?php echo htmlspecialchars($block['block_label']); ?></strong></td>
                    <td><?php echo number_format($block['reward_value'], 2); ?> coins</td>
                    <td><?php echo $block['probability']; ?>%</td>
                    <td>
                        <?php echo $block['allow_double'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?>
                    </td>
                    <td>
                        <?php echo $block['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick='editBlock(<?php echo json_encode($block); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBlock(<?php echo $block['id']; ?>)">
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

<!-- Add Block Modal -->
<div class="modal fade" id="addBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Spin Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Block Label</label>
                        <input type="text" class="form-control" name="label" required>
                        <small class="text-muted">Display text (e.g., "10", "100", "JACKPOT")</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reward Value (Coins)</label>
                        <input type="number" class="form-control" name="reward" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Probability (%)</label>
                        <input type="number" class="form-control" name="probability" step="0.01" max="100" required>
                        <small class="text-muted">Chance of landing on this block</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="0" required>
                        <small class="text-muted">Display order on the wheel</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="allow_double" id="allow_double_add" checked>
                        <label class="form-check-label" for="allow_double_add">
                            Allow Double Reward (when watching ad)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Block Modal -->
<div class="modal fade" id="editBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Spin Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Block Label</label>
                        <input type="text" class="form-control" name="label" id="edit_label" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reward Value (Coins)</label>
                        <input type="number" class="form-control" name="reward" id="edit_reward" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Probability (%)</label>
                        <input type="number" class="form-control" name="probability" id="edit_probability" step="0.01" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" id="edit_sort" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="allow_double" id="edit_allow_double">
                        <label class="form-check-label" for="edit_allow_double">
                            Allow Double Reward
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="edit_active">
                        <label class="form-check-label" for="edit_active">
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBlock(block) {
    document.getElementById('edit_id').value = block.id;
    document.getElementById('edit_label').value = block.block_label;
    document.getElementById('edit_reward').value = block.reward_value;
    document.getElementById('edit_probability').value = block.probability;
    document.getElementById('edit_sort').value = block.sort_order;
    document.getElementById('edit_allow_double').checked = block.allow_double == 1;
    document.getElementById('edit_active').checked = block.is_active == 1;
    new bootstrap.Modal(document.getElementById('editBlockModal')).show();
}

function deleteBlock(id) {
    if (confirm('Are you sure you want to delete this spin block?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
