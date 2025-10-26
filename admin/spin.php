<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                $stmt = $db->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
                
                // Update spin settings
                $settings = [
                    'spin_interval' => $_POST['spin_interval'],
                    'spin_daily_limit' => $_POST['spin_daily_limit']
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt->execute([$value, $key]);
                }
                
                $success = "Spin settings updated successfully!";
                break;
                
            case 'update_block':
                $stmt = $db->prepare("UPDATE spin_config SET reward_value = ?, probability = ?, is_active = ?, allow_double = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['reward_value'],
                    $_POST['probability'],
                    isset($_POST['is_active']) ? 1 : 0,
                    isset($_POST['allow_double']) ? 1 : 0,
                    $_POST['block_id']
                ]);
                $success = "Spin block updated successfully!";
                break;
                
            case 'add_block':
                $stmt = $db->prepare("INSERT INTO spin_config (block_number, label, reward_value, probability, color, is_active, allow_double) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['block_number'],
                    $_POST['label'],
                    $_POST['reward_value'],
                    $_POST['probability'],
                    $_POST['color'],
                    isset($_POST['is_active']) ? 1 : 0,
                    isset($_POST['allow_double']) ? 1 : 0
                ]);
                $success = "New spin block added successfully!";
                break;
        }
    }
}

// Get spin settings
$stmt = $db->prepare("SELECT * FROM settings WHERE `key` IN ('spin_interval', 'spin_daily_limit')");
$stmt->execute();
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$spinInterval = $settingsData['spin_interval'] ?? 3600;
$spinDailyLimit = $settingsData['spin_daily_limit'] ?? 10;

// Get spin blocks
$blocks = $db->query("SELECT * FROM spin_config ORDER BY block_number")->fetchAll();

// Get spin statistics
$stmt = $db->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_spins,
        SUM(reward_amount) as total_rewards
    FROM user_spins
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$spinStats = $stmt->fetchAll();

// Get reward distribution
$rewardDist = $db->query("
    SELECT 
        sc.label,
        COUNT(*) as spin_count,
        SUM(us.reward_amount) as total_value
    FROM user_spins us
    JOIN spin_config sc ON us.reward_won = sc.label
    WHERE us.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    GROUP BY sc.label
    ORDER BY total_value DESC
")->fetchAll();
?>

<div class="page-header">
    <h2><i class="fas fa-circle-notch"></i> Spin Wheel Settings</h2>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#settings">
            <i class="fas fa-cog"></i> General Settings
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#blocks">
            <i class="fas fa-th"></i> Wheel Blocks
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#analytics">
            <i class="fas fa-chart-pie"></i> Analytics
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Settings Tab -->
    <div class="tab-pane fade show active" id="settings">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-cog"></i> Spin Wheel Configuration</h5>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Spin Interval (seconds)</label>
                        <input type="number" name="spin_interval" class="form-control" value="<?php echo $spinInterval; ?>" required>
                        <small class="text-muted">Time between free spins (3600 = 1 hour)</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Daily Spin Limit</label>
                        <input type="number" name="spin_daily_limit" class="form-control" value="<?php echo $spinDailyLimit; ?>" required>
                        <small class="text-muted">Maximum spins per day (0 = unlimited)</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-gradient">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Blocks Tab -->
    <div class="tab-pane fade" id="blocks">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-th"></i> Wheel Blocks Configuration</h5>
                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addBlockModal">
                    <i class="fas fa-plus"></i> Add Block
                </button>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Probability Tips:</strong> Total probability should equal 100%. 
                Higher probability = more chance to win. Example: 30% means 3 out of 10 spins will land here.
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Block #</th>
                            <th>Label</th>
                            <th>Reward Value</th>
                            <th>Probability</th>
                            <th>Color</th>
                            <th>Double Reward</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalProb = 0;
                        foreach ($blocks as $block): 
                            $totalProb += $block['probability'];
                        ?>
                        <tr>
                            <td><?php echo $block['block_number']; ?></td>
                            <td><strong><?php echo htmlspecialchars($block['label']); ?></strong></td>
                            <td><span class="badge bg-success"><?php echo number_format($block['reward_value']); ?> coins</span></td>
                            <td>
                                <span class="badge bg-info"><?php echo $block['probability']; ?>%</span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?php echo $block['color']; ?>">
                                    <?php echo $block['color']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $block['allow_double'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $block['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $block['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='editBlock(<?php echo json_encode($block); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Probability:</strong></td>
                            <td colspan="5">
                                <span class="badge bg-<?php echo $totalProb == 100 ? 'success' : 'warning'; ?> fs-6">
                                    <?php echo $totalProb; ?>% <?php echo $totalProb != 100 ? '⚠️ Should be 100%' : '✓'; ?>
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Analytics Tab -->
    <div class="tab-pane fade" id="analytics">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="stat-card">
                    <h5 class="mb-3"><i class="fas fa-chart-line"></i> Spin Statistics (Last 7 Days)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Spins</th>
                                    <th>Rewards Given</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($spinStats as $stat): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($stat['date'])); ?></td>
                                    <td><?php echo number_format($stat['total_spins']); ?></td>
                                    <td><?php echo number_format($stat['total_rewards']); ?> coins</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="stat-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie"></i> Reward Distribution (Last 7 Days)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Reward</th>
                                    <th>Times Won</th>
                                    <th>Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rewardDist as $dist): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dist['label']); ?></strong></td>
                                    <td><?php echo number_format($dist['spin_count']); ?></td>
                                    <td><?php echo number_format($dist['total_value']); ?> coins</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Block Modal -->
<div class="modal fade" id="editBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_block">
                <input type="hidden" name="block_id" id="edit_block_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Spin Block</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Label</label>
                        <input type="text" id="edit_label" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label>Reward Value (coins) *</label>
                        <input type="number" name="reward_value" id="edit_reward_value" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Probability (%) *</label>
                        <input type="number" name="probability" id="edit_probability" class="form-control" step="0.1" min="0" max="100" required>
                        <small class="text-muted">Total probability across all blocks should equal 100%</small>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="allow_double" class="form-check-input" id="edit_allow_double">
                        <label class="form-check-label" for="edit_allow_double">Allow Double Reward (Watch Ad)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Update Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Block Modal -->
<div class="modal fade" id="addBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_block">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Spin Block</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Block Number *</label>
                        <input type="number" name="block_number" class="form-control" min="1" max="12" required>
                        <small class="text-muted">Position on the wheel (1-12)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Label *</label>
                        <input type="text" name="label" class="form-control" placeholder="e.g., 50 Coins" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Reward Value (coins) *</label>
                        <input type="number" name="reward_value" class="form-control" step="0.01" value="50" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Probability (%) *</label>
                        <input type="number" name="probability" class="form-control" step="0.1" min="0" max="100" value="10" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Color *</label>
                        <input type="color" name="color" class="form-control" value="#FFD700" required>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="new_is_active" checked>
                        <label class="form-check-label" for="new_is_active">Active</label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="allow_double" class="form-check-input" id="new_allow_double" checked>
                        <label class="form-check-label" for="new_allow_double">Allow Double Reward</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBlock(block) {
    document.getElementById('edit_block_id').value = block.id;
    document.getElementById('edit_label').value = block.label;
    document.getElementById('edit_reward_value').value = block.reward_value;
    document.getElementById('edit_probability').value = block.probability;
    document.getElementById('edit_is_active').checked = block.is_active == 1;
    document.getElementById('edit_allow_double').checked = block.allow_double == 1;
    
    new bootstrap.Modal(document.getElementById('editBlockModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
