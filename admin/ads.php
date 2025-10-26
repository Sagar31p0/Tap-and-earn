<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_network':
                $stmt = $db->prepare("INSERT INTO ad_networks (name, enabled, settings) VALUES (?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    isset($_POST['enabled']) ? 1 : 0,
                    json_encode($_POST['settings'] ?? [])
                ]);
                $success = "Ad network added successfully!";
                break;
                
            case 'toggle_network':
                $stmt = $db->prepare("UPDATE ad_networks SET enabled = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['enabled'],
                    $_POST['network_id']
                ]);
                $success = "Network status updated!";
                break;
                
            case 'add_unit':
                $stmt = $db->prepare("INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['network_id'],
                    $_POST['unit_name'],
                    $_POST['unit_code'],
                    $_POST['unit_type'],
                    $_POST['placement'],
                    isset($_POST['active']) ? 1 : 0
                ]);
                $success = "Ad unit added successfully!";
                break;
                
            case 'update_placement':
                $stmt = $db->prepare("UPDATE ad_placements SET ad_unit_id = ?, fallback_units = ? WHERE placement_key = ?");
                $stmt->execute([
                    $_POST['primary_unit'],
                    json_encode($_POST['fallback_units'] ?? []),
                    $_POST['placement_key']
                ]);
                $success = "Placement mapping updated!";
                break;
        }
    }
}

// Get all networks
$networks = $db->query("SELECT * FROM ad_networks ORDER BY name")->fetchAll();

// Get all ad units
$units = $db->query("SELECT u.*, n.name as network_name FROM ad_units u JOIN ad_networks n ON u.network_id = n.id ORDER BY u.created_at DESC")->fetchAll();

// Get placements
$placements = $db->query("SELECT * FROM ad_placements ORDER BY placement_key")->fetchAll();

// Get ad statistics
$adStats = $db->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_ads,
        SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions,
        SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
        SUM(CASE WHEN event_type = 'complete' THEN 1 ELSE 0 END) as completions
    FROM ad_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
")->fetchAll();
?>

<div class="page-header">
    <h2><i class="fas fa-ad"></i> Ad Management</h2>
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
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#networks">
            <i class="fas fa-network-wired"></i> Ad Networks
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#units">
            <i class="fas fa-puzzle-piece"></i> Ad Units
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#placements">
            <i class="fas fa-map-marker-alt"></i> Placements
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#analytics">
            <i class="fas fa-chart-line"></i> Analytics
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Networks Tab -->
    <div class="tab-pane fade show active" id="networks">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-network-wired"></i> Ad Networks</h5>
                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addNetworkModal">
                    <i class="fas fa-plus"></i> Add Network
                </button>
            </div>
            
            <div class="row">
                <?php foreach ($networks as $network): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($network['name']); ?>
                                </h5>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_network">
                                    <input type="hidden" name="network_id" value="<?php echo $network['id']; ?>">
                                    <input type="hidden" name="enabled" value="<?php echo $network['enabled'] ? 0 : 1; ?>">
                                    <button type="submit" class="btn btn-sm btn-<?php echo $network['enabled'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $network['enabled'] ? 'ON' : 'OFF'; ?>
                                    </button>
                                </form>
                            </div>
                            <p class="text-muted mt-2 mb-0">
                                Status: <span class="badge bg-<?php echo $network['enabled'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $network['enabled'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Ad Units Tab -->
    <div class="tab-pane fade" id="units">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-puzzle-piece"></i> Ad Units</h5>
                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                    <i class="fas fa-plus"></i> Add Unit
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Network</th>
                            <th>Type</th>
                            <th>Placement</th>
                            <th>Unit Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?php echo $unit['id']; ?></td>
                            <td><?php echo htmlspecialchars($unit['name']); ?></td>
                            <td><?php echo htmlspecialchars($unit['network_name']); ?></td>
                            <td><span class="badge bg-info"><?php echo $unit['unit_type']; ?></span></td>
                            <td><?php echo $unit['placement']; ?></td>
                            <td><code><?php echo htmlspecialchars(substr($unit['unit_code'], 0, 20)); ?>...</code></td>
                            <td>
                                <span class="badge bg-<?php echo $unit['status'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $unit['status'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editUnit(<?php echo $unit['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Placements Tab -->
    <div class="tab-pane fade" id="placements">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Ad Placement Mapping</h5>
            <p class="text-muted">Configure which ad units to show for each placement in the app.</p>
            
            <?php foreach ($placements as $placement): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><?php echo htmlspecialchars($placement['description']); ?> (<?php echo $placement['placement_key']; ?>)</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_placement">
                        <input type="hidden" name="placement_key" value="<?php echo $placement['placement_key']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label>Primary Ad Unit</label>
                                <select name="primary_unit" class="form-select">
                                    <option value="">-- None --</option>
                                    <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo $unit['id']; ?>" <?php echo $placement['ad_unit_id'] == $unit['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($unit['network_name'] . ' - ' . $unit['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Fallback Units (comma-separated IDs)</label>
                                <input type="text" name="fallback_units[]" class="form-control" 
                                       value="<?php echo implode(',', json_decode($placement['fallback_units'] ?? '[]', true)); ?>" 
                                       placeholder="e.g., 2,3,4">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-3">Update Placement</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Analytics Tab -->
    <div class="tab-pane fade" id="analytics">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Ad Performance (Last 7 Days)</h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Ads</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>Completions</th>
                            <th>CTR</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adStats as $stat): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($stat['date'])); ?></td>
                            <td><?php echo number_format($stat['total_ads']); ?></td>
                            <td><?php echo number_format($stat['impressions']); ?></td>
                            <td><?php echo number_format($stat['clicks']); ?></td>
                            <td><?php echo number_format($stat['completions']); ?></td>
                            <td>
                                <?php 
                                $ctr = $stat['impressions'] > 0 ? ($stat['clicks'] / $stat['impressions'] * 100) : 0;
                                echo number_format($ctr, 2); 
                                ?>%
                            </td>
                            <td>
                                <?php 
                                $completion_rate = $stat['impressions'] > 0 ? ($stat['completions'] / $stat['impressions'] * 100) : 0;
                                echo number_format($completion_rate, 2); 
                                ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Network Modal -->
<div class="modal fade" id="addNetworkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_network">
                <div class="modal-header">
                    <h5 class="modal-title">Add Ad Network</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Network Name</label>
                        <select name="name" class="form-select" required>
                            <option value="Adexium">Adexium</option>
                            <option value="Monetag">Monetag</option>
                            <option value="Adsgram">Adsgram</option>
                            <option value="Richads">Richads</option>
                            <option value="Custom">Custom Network</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="enabled" class="form-check-input" id="networkEnabled" checked>
                        <label class="form-check-label" for="networkEnabled">Enable Network</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add Network</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_unit">
                <div class="modal-header">
                    <h5 class="modal-title">Add Ad Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Network</label>
                            <select name="network_id" class="form-select" required>
                                <?php foreach ($networks as $network): ?>
                                <option value="<?php echo $network['id']; ?>"><?php echo htmlspecialchars($network['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Unit Name</label>
                            <input type="text" name="unit_name" class="form-control" placeholder="e.g., Tap Interstitial" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Unit Type</label>
                            <select name="unit_type" class="form-select" required>
                                <option value="interstitial">Interstitial</option>
                                <option value="rewarded">Rewarded</option>
                                <option value="banner">Banner</option>
                                <option value="native">Native</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Placement</label>
                            <select name="placement" class="form-select" required>
                                <option value="tap">Tap & Earn</option>
                                <option value="spin">Spin Wheel</option>
                                <option value="task">Tasks</option>
                                <option value="game">Games</option>
                                <option value="shortlink">Short Links</option>
                                <option value="energy">Energy Recharge</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Unit Code / Widget ID</label>
                        <input type="text" name="unit_code" class="form-control" 
                               placeholder="e.g., ef364bbc-e2b8-434c-8b52-c735de561dc7" required>
                        <small class="text-muted">Enter the Widget ID, Zone ID, or Block ID from your ad network</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="unitActive" checked>
                        <label class="form-check-label" for="unitActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
