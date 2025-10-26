<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $db->prepare("INSERT INTO games (name, description, icon, game_url, reward, play_limit, limit_type, ad_unit_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['icon'],
                    $_POST['game_url'],
                    $_POST['reward'],
                    $_POST['play_limit'],
                    $_POST['limit_type'],
                    $_POST['ad_unit_id'] ?? null,
                    isset($_POST['is_active']) ? 1 : 0
                ]);
                $success = "Game added successfully!";
                break;
                
            case 'edit':
                $stmt = $db->prepare("UPDATE games SET name = ?, description = ?, icon = ?, game_url = ?, reward = ?, play_limit = ?, limit_type = ?, ad_unit_id = ?, is_active = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['icon'],
                    $_POST['game_url'],
                    $_POST['reward'],
                    $_POST['play_limit'],
                    $_POST['limit_type'],
                    $_POST['ad_unit_id'] ?? null,
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['game_id']
                ]);
                $success = "Game updated successfully!";
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM games WHERE id = ?");
                $stmt->execute([$_POST['game_id']]);
                $success = "Game deleted successfully!";
                break;
        }
    }
}

// Get all games
$games = $db->query("SELECT g.*, COUNT(DISTINCT ug.user_id) as total_players, SUM(ug.plays_count) as total_plays FROM games g LEFT JOIN user_games ug ON g.id = ug.game_id GROUP BY g.id ORDER BY g.created_at DESC")->fetchAll();

// Get ad units for selection
$adUnits = $db->query("SELECT u.id, u.name, n.name as network_name FROM ad_units u JOIN ad_networks n ON u.network_id = n.id WHERE u.placement = 'game' AND u.status = 1")->fetchAll();
?>

<div class="page-header">
    <h2><i class="fas fa-gamepad"></i> Game Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addGameModal">
        <i class="fas fa-plus"></i> Add New Game
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
        <table class="table table-hover" id="gamesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Reward</th>
                    <th>Play Limit</th>
                    <th>Total Players</th>
                    <th>Total Plays</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td><?php echo $game['id']; ?></td>
                    <td><i class="<?php echo htmlspecialchars($game['icon']); ?> fa-2x"></i></td>
                    <td><?php echo htmlspecialchars($game['name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($game['description'], 0, 50)); ?>...</td>
                    <td><span class="badge bg-success"><?php echo number_format($game['reward']); ?> coins</span></td>
                    <td>
                        <?php 
                        if ($game['limit_type'] === 'unlimited') {
                            echo '<span class="badge bg-info">Unlimited</span>';
                        } else {
                            echo '<span class="badge bg-warning">' . $game['play_limit'] . ' per ' . $game['limit_type'] . '</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo number_format($game['total_players']); ?></td>
                    <td><?php echo number_format($game['total_plays']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $game['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $game['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='editGame(<?php echo json_encode($game); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this game?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
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
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Game Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Lucky Slots" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Icon (Font Awesome class) *</label>
                            <input type="text" name="icon" class="form-control" placeholder="e.g., fas fa-dice" required>
                            <small class="text-muted">Visit fontawesome.com for icon names</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief game description"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label>Game URL *</label>
                        <input type="url" name="game_url" class="form-control" placeholder="https://example.com/game" required>
                        <small class="text-muted">External game URL that will open when user plays</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Reward (coins) *</label>
                            <input type="number" name="reward" class="form-control" value="10" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Limit Type *</label>
                            <select name="limit_type" class="form-select" required>
                                <option value="unlimited">Unlimited</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Play Limit</label>
                            <input type="number" name="play_limit" class="form-control" value="5" min="0">
                            <small class="text-muted">0 = unlimited</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Pre-roll Ad Unit</label>
                        <select name="ad_unit_id" class="form-select">
                            <option value="">-- No Ad --</option>
                            <?php foreach ($adUnits as $unit): ?>
                            <option value="<?php echo $unit['id']; ?>">
                                <?php echo htmlspecialchars($unit['network_name'] . ' - ' . $unit['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="gameActive" checked>
                        <label class="form-check-label" for="gameActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add Game</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Game Modal -->
<div class="modal fade" id="editGameModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="game_id" id="edit_game_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Game Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Icon (Font Awesome class) *</label>
                            <input type="text" name="icon" id="edit_icon" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label>Game URL *</label>
                        <input type="url" name="game_url" id="edit_game_url" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Reward (coins) *</label>
                            <input type="number" name="reward" id="edit_reward" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Limit Type *</label>
                            <select name="limit_type" id="edit_limit_type" class="form-select" required>
                                <option value="unlimited">Unlimited</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Play Limit</label>
                            <input type="number" name="play_limit" id="edit_play_limit" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Pre-roll Ad Unit</label>
                        <select name="ad_unit_id" id="edit_ad_unit_id" class="form-select">
                            <option value="">-- No Ad --</option>
                            <?php foreach ($adUnits as $unit): ?>
                            <option value="<?php echo $unit['id']; ?>">
                                <?php echo htmlspecialchars($unit['network_name'] . ' - ' . $unit['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Update Game</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGame(game) {
    document.getElementById('edit_game_id').value = game.id;
    document.getElementById('edit_name').value = game.name;
    document.getElementById('edit_icon').value = game.icon;
    document.getElementById('edit_description').value = game.description;
    document.getElementById('edit_game_url').value = game.game_url;
    document.getElementById('edit_reward').value = game.reward;
    document.getElementById('edit_limit_type').value = game.limit_type;
    document.getElementById('edit_play_limit').value = game.play_limit;
    document.getElementById('edit_ad_unit_id').value = game.ad_unit_id || '';
    document.getElementById('edit_is_active').checked = game.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editGameModal')).show();
}

$(document).ready(function() {
    $('#gamesTable').DataTable({
        order: [[0, 'desc']]
    });
});
</script>

<?php require_once 'footer.php'; ?>
