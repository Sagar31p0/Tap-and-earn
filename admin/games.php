<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name']);
        $icon = sanitizeInput($_POST['icon']);
        $gameUrl = sanitizeInput($_POST['game_url']);
        $reward = (float)$_POST['reward'];
        $playLimitType = $_POST['play_limit_type'];
        $playLimit = (int)$_POST['play_limit'];
        $adNetwork = sanitizeInput($_POST['ad_network']);
        $adUnitId = sanitizeInput($_POST['ad_unit_id']);
        
        $stmt = $db->prepare("INSERT INTO games (name, icon, game_url, reward, play_limit_type, play_limit, ad_network, ad_unit_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $icon, $gameUrl, $reward, $playLimitType, $playLimit, $adNetwork, $adUnitId]);
        $success = "Game added successfully";
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $icon = sanitizeInput($_POST['icon']);
        $gameUrl = sanitizeInput($_POST['game_url']);
        $reward = (float)$_POST['reward'];
        $playLimitType = $_POST['play_limit_type'];
        $playLimit = (int)$_POST['play_limit'];
        $adNetwork = sanitizeInput($_POST['ad_network']);
        $adUnitId = sanitizeInput($_POST['ad_unit_id']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE games SET name = ?, icon = ?, game_url = ?, reward = ?, play_limit_type = ?, play_limit = ?, ad_network = ?, ad_unit_id = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $gameUrl, $reward, $playLimitType, $playLimit, $adNetwork, $adUnitId, $isActive, $id]);
        $success = "Game updated successfully";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM games WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Game deleted successfully";
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE games SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Game status updated";
    }
}

// Get games
$stmt = $db->query("SELECT g.*, COUNT(ug.id) as total_plays FROM games g 
                    LEFT JOIN user_games ug ON g.id = ug.game_id 
                    GROUP BY g.id ORDER BY g.id DESC");
$games = $stmt->fetchAll();

// Get ad networks
$stmt = $db->query("SELECT * FROM ad_networks WHERE is_enabled = 1");
$adNetworks = $stmt->fetchAll();

// Statistics
$stmt = $db->query("SELECT COUNT(*) FROM games WHERE is_active = 1");
$activeGames = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM user_games");
$totalPlays = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(reward) FROM games WHERE is_active = 1");
$totalRewards = $stmt->fetchColumn() ?: 0;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-gamepad"></i> Games Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addGameModal">
        <i class="fas fa-plus"></i> Add Game
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
            <h6 class="text-muted">Active Games</h6>
            <h3><?php echo number_format($activeGames); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Total Game Plays</h6>
            <h3><?php echo number_format($totalPlays); ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted">Total Rewards Available</h6>
            <h3><?php echo number_format($totalRewards); ?> coins</h3>
        </div>
    </div>
</div>

<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> All Games</h5>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Reward</th>
                    <th>Play Limit</th>
                    <th>Ad Network</th>
                    <th>Total Plays</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td><?php echo $game['id']; ?></td>
                    <td><i class="fas <?php echo htmlspecialchars($game['icon']); ?> fa-2x"></i></td>
                    <td><?php echo htmlspecialchars($game['name']); ?></td>
                    <td><?php echo number_format($game['reward'], 2); ?> coins</td>
                    <td>
                        <?php 
                        if ($game['play_limit_type'] === 'unlimited') {
                            echo '<span class="badge bg-success">Unlimited</span>';
                        } else {
                            echo $game['play_limit'] . ' / ' . ucfirst($game['play_limit_type']);
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($game['ad_network'] ?: 'None'); ?></td>
                    <td><?php echo number_format($game['total_plays']); ?></td>
                    <td>
                        <?php echo $game['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick='editGame(<?php echo json_encode($game); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="toggleGame(<?php echo $game['id']; ?>)">
                                <i class="fas fa-toggle-on"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteGame(<?php echo $game['id']; ?>)">
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

<!-- Add Game Modal -->
<div class="modal fade" id="addGameModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Game</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Game Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (FontAwesome class)</label>
                            <input type="text" class="form-control" name="icon" placeholder="fa-gamepad" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Game URL</label>
                        <input type="url" class="form-control" name="game_url" required>
                        <small class="text-muted">Full URL to the game</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reward (Coins)</label>
                            <input type="number" class="form-control" name="reward" step="0.01" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Play Limit Type</label>
                            <select class="form-select" name="play_limit_type" required>
                                <option value="unlimited">Unlimited</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Play Limit Count</label>
                            <input type="number" class="form-control" name="play_limit" value="0">
                            <small class="text-muted">0 for unlimited</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad Network</label>
                            <select class="form-select" name="ad_network">
                                <option value="">None</option>
                                <?php foreach ($adNetworks as $network): ?>
                                    <option value="<?php echo $network['name']; ?>"><?php echo ucfirst($network['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Unit ID</label>
                        <input type="text" class="form-control" name="ad_unit_id" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Game</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Game Modal -->
<div class="modal fade" id="editGameModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Game</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Game Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (FontAwesome class)</label>
                            <input type="text" class="form-control" name="icon" id="edit_icon" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Game URL</label>
                        <input type="url" class="form-control" name="game_url" id="edit_url" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reward (Coins)</label>
                            <input type="number" class="form-control" name="reward" id="edit_reward" step="0.01" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Play Limit Type</label>
                            <select class="form-select" name="play_limit_type" id="edit_limit_type" required>
                                <option value="unlimited">Unlimited</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Play Limit Count</label>
                            <input type="number" class="form-control" name="play_limit" id="edit_limit" value="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad Network</label>
                            <select class="form-select" name="ad_network" id="edit_network">
                                <option value="">None</option>
                                <?php foreach ($adNetworks as $network): ?>
                                    <option value="<?php echo $network['name']; ?>"><?php echo ucfirst($network['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Unit ID</label>
                        <input type="text" class="form-control" name="ad_unit_id" id="edit_ad_unit">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="edit_active">
                        <label class="form-check-label" for="edit_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Game</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGame(game) {
    document.getElementById('edit_id').value = game.id;
    document.getElementById('edit_name').value = game.name;
    document.getElementById('edit_icon').value = game.icon;
    document.getElementById('edit_url').value = game.game_url;
    document.getElementById('edit_reward').value = game.reward;
    document.getElementById('edit_limit_type').value = game.play_limit_type;
    document.getElementById('edit_limit').value = game.play_limit;
    document.getElementById('edit_network').value = game.ad_network || '';
    document.getElementById('edit_ad_unit').value = game.ad_unit_id || '';
    document.getElementById('edit_active').checked = game.is_active == 1;
    new bootstrap.Modal(document.getElementById('editGameModal')).show();
}

function toggleGame(id) {
    if (confirm('Toggle game status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="toggle"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteGame(id) {
    if (confirm('Are you sure you want to delete this game?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
