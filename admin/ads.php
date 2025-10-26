<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_network') {
        $name = sanitizeInput($_POST['name']);
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        
        $stmt = $db->prepare("INSERT INTO ad_networks (name, is_enabled) VALUES (?, ?)");
        $stmt->execute([$name, $isEnabled]);
        $success = "Ad network added successfully";
    } elseif ($action === 'edit_network') {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE ad_networks SET name = ?, is_enabled = ? WHERE id = ?");
        $stmt->execute([$name, $isEnabled, $id]);
        $success = "Ad network updated successfully";
    } elseif ($action === 'delete_network') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM ad_networks WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Ad network deleted successfully";
    } elseif ($action === 'add_unit') {
        $networkId = (int)$_POST['network_id'];
        $name = sanitizeInput($_POST['name']);
        $unitCode = sanitizeInput($_POST['unit_code']);
        $unitType = sanitizeInput($_POST['unit_type']);
        $placementKey = sanitizeInput($_POST['placement_key']);
        
        $stmt = $db->prepare("INSERT INTO ad_units (network_id, name, unit_code, unit_type, placement_key) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$networkId, $name, $unitCode, $unitType, $placementKey]);
        $success = "Ad unit added successfully";
    } elseif ($action === 'edit_unit') {
        $id = (int)$_POST['id'];
        $networkId = (int)$_POST['network_id'];
        $name = sanitizeInput($_POST['name']);
        $unitCode = sanitizeInput($_POST['unit_code']);
        $unitType = sanitizeInput($_POST['unit_type']);
        $placementKey = sanitizeInput($_POST['placement_key']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE ad_units SET network_id = ?, name = ?, unit_code = ?, unit_type = ?, placement_key = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$networkId, $name, $unitCode, $unitType, $placementKey, $isActive, $id]);
        $success = "Ad unit updated successfully";
    } elseif ($action === 'delete_unit') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM ad_units WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Ad unit deleted successfully";
    } elseif ($action === 'update_placement') {
        $id = (int)$_POST['id'];
        $primaryUnit = $_POST['primary_unit'] ?: null;
        $secondaryUnit = $_POST['secondary_unit'] ?: null;
        $tertiaryUnit = $_POST['tertiary_unit'] ?: null;
        $frequency = (int)$_POST['frequency'];
        
        $stmt = $db->prepare("UPDATE ad_placements SET primary_ad_unit_id = ?, secondary_ad_unit_id = ?, tertiary_ad_unit_id = ?, frequency = ? WHERE id = ?");
        $stmt->execute([$primaryUnit, $secondaryUnit, $tertiaryUnit, $frequency, $id]);
        $success = "Ad placement updated successfully";
    }
}

// Get ad networks
$stmt = $db->query("SELECT * FROM ad_networks ORDER BY id ASC");
$adNetworks = $stmt->fetchAll();

// Get ad units
$stmt = $db->query("SELECT au.*, an.name as network_name FROM ad_units au 
                    LEFT JOIN ad_networks an ON au.network_id = an.id 
                    ORDER BY au.id DESC");
$adUnits = $stmt->fetchAll();

// Get ad placements
$stmt = $db->query("SELECT ap.*, 
                    au1.name as primary_unit_name,
                    au2.name as secondary_unit_name,
                    au3.name as tertiary_unit_name
                    FROM ad_placements ap
                    LEFT JOIN ad_units au1 ON ap.primary_ad_unit_id = au1.id
                    LEFT JOIN ad_units au2 ON ap.secondary_ad_unit_id = au2.id
                    LEFT JOIN ad_units au3 ON ap.tertiary_ad_unit_id = au3.id
                    ORDER BY ap.id ASC");
$adPlacements = $stmt->fetchAll();

// Statistics
$stmt = $db->query("SELECT COUNT(*) FROM ad_networks WHERE is_enabled = 1");
$activeNetworks = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM ad_units WHERE is_active = 1");
$activeUnits = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM ad_logs WHERE DATE(created_at) = CURDATE()");
$adsToday = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM ad_logs WHERE event = 'complete' AND DATE(created_at) = CURDATE()");
$completedToday = $stmt->fetchColumn();
?>

<div class="page-header">
    <h2><i class="fas fa-ad"></i> Ads Management</h2>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Active Networks</h6>
            <h3><?php echo number_format($activeNetworks); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Active Ad Units</h6>
            <h3><?php echo number_format($activeUnits); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Ads Shown Today</h6>
            <h3><?php echo number_format($adsToday); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Completed Today</h6>
            <h3><?php echo number_format($completedToday); ?></h3>
        </div>
    </div>
</div>

<!-- Ad Networks Section -->
<div class="stat-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5><i class="fas fa-network-wired"></i> Ad Networks</h5>
        <button class="btn btn-sm btn-gradient" data-bs-toggle="modal" data-bs-target="#addNetworkModal">
            <i class="fas fa-plus"></i> Add Network
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Network Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adNetworks as $network): ?>
                <tr>
                    <td><?php echo $network['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars(ucfirst($network['name'])); ?></strong></td>
                    <td>
                        <?php echo $network['is_enabled'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>'; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick='editNetwork(<?php echo json_encode($network); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteNetwork(<?php echo $network['id']; ?>)">
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

<!-- Ad Units Section -->
<div class="stat-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5><i class="fas fa-rectangle-ad"></i> Ad Units</h5>
        <button class="btn btn-sm btn-gradient" data-bs-toggle="modal" data-bs-target="#addUnitModal">
            <i class="fas fa-plus"></i> Add Ad Unit
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover data-table">
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
                <?php foreach ($adUnits as $unit): ?>
                <tr>
                    <td><?php echo $unit['id']; ?></td>
                    <td><?php echo htmlspecialchars($unit['name']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($unit['network_name'])); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($unit['unit_type']); ?></span></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($unit['placement_key']); ?></span></td>
                    <td><code><?php echo htmlspecialchars(substr($unit['unit_code'], 0, 20)); ?>...</code></td>
                    <td>
                        <?php echo $unit['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick='editUnit(<?php echo json_encode($unit); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUnit(<?php echo $unit['id']; ?>)">
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

<!-- Ad Placements Section -->
<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Ad Placements Configuration</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Placement</th>
                    <th>Description</th>
                    <th>Primary Unit</th>
                    <th>Secondary Unit</th>
                    <th>Tertiary Unit</th>
                    <th>Frequency</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adPlacements as $placement): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($placement['placement_key']); ?></strong></td>
                    <td><?php echo htmlspecialchars($placement['description']); ?></td>
                    <td><?php echo htmlspecialchars($placement['primary_unit_name'] ?: 'Not set'); ?></td>
                    <td><?php echo htmlspecialchars($placement['secondary_unit_name'] ?: 'Not set'); ?></td>
                    <td><?php echo htmlspecialchars($placement['tertiary_unit_name'] ?: 'Not set'); ?></td>
                    <td><?php echo $placement['frequency']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='editPlacement(<?php echo json_encode($placement); ?>)'>
                            <i class="fas fa-cog"></i> Configure
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Network Modal -->
<div class="modal fade" id="addNetworkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Ad Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_network">
                    <div class="mb-3">
                        <label class="form-label">Network Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_enabled" id="add_network_enabled" checked>
                        <label class="form-check-label" for="add_network_enabled">Enabled</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Network</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Network Modal -->
<div class="modal fade" id="editNetworkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ad Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_network">
                    <input type="hidden" name="id" id="edit_network_id">
                    <div class="mb-3">
                        <label class="form-label">Network Name</label>
                        <input type="text" class="form-control" name="name" id="edit_network_name" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_enabled" id="edit_network_enabled">
                        <label class="form-check-label" for="edit_network_enabled">Enabled</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Network</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Ad Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_unit">
                    <div class="mb-3">
                        <label class="form-label">Ad Network</label>
                        <select class="form-select" name="network_id" required>
                            <option value="">Select Network</option>
                            <?php foreach ($adNetworks as $network): ?>
                                <option value="<?php echo $network['id']; ?>"><?php echo ucfirst($network['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Code/ID</label>
                        <textarea class="form-control" name="unit_code" rows="3" required></textarea>
                        <small class="text-muted">Ad unit code or ID from the network</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Type</label>
                            <select class="form-select" name="unit_type" required>
                                <option value="banner">Banner</option>
                                <option value="interstitial">Interstitial</option>
                                <option value="rewarded">Rewarded Video</option>
                                <option value="native">Native</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placement Key</label>
                            <select class="form-select" name="placement_key" required>
                                <option value="tap">Tap & Earn</option>
                                <option value="spin">Spin Wheel</option>
                                <option value="game_preroll">Game Pre-roll</option>
                                <option value="task">Task Completion</option>
                                <option value="shortlink">Short Link</option>
                                <option value="wallet">Wallet</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Ad Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_unit">
                    <input type="hidden" name="id" id="edit_unit_id">
                    <div class="mb-3">
                        <label class="form-label">Ad Network</label>
                        <select class="form-select" name="network_id" id="edit_unit_network" required>
                            <?php foreach ($adNetworks as $network): ?>
                                <option value="<?php echo $network['id']; ?>"><?php echo ucfirst($network['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" id="edit_unit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Code/ID</label>
                        <textarea class="form-control" name="unit_code" id="edit_unit_code" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Type</label>
                            <select class="form-select" name="unit_type" id="edit_unit_type" required>
                                <option value="banner">Banner</option>
                                <option value="interstitial">Interstitial</option>
                                <option value="rewarded">Rewarded Video</option>
                                <option value="native">Native</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placement Key</label>
                            <select class="form-select" name="placement_key" id="edit_unit_placement" required>
                                <option value="tap">Tap & Earn</option>
                                <option value="spin">Spin Wheel</option>
                                <option value="game_preroll">Game Pre-roll</option>
                                <option value="task">Task Completion</option>
                                <option value="shortlink">Short Link</option>
                                <option value="wallet">Wallet</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="edit_unit_active">
                        <label class="form-check-label" for="edit_unit_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Placement Modal -->
<div class="modal fade" id="editPlacementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configure Ad Placement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_placement">
                    <input type="hidden" name="id" id="edit_placement_id">
                    
                    <div class="alert alert-info">
                        <strong>Placement:</strong> <span id="edit_placement_key"></span><br>
                        <strong>Description:</strong> <span id="edit_placement_desc"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Primary Ad Unit</label>
                        <select class="form-select" name="primary_unit" id="edit_placement_primary">
                            <option value="">None</option>
                            <?php foreach ($adUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name'] . ' (' . $unit['network_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Will be shown first</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Secondary Ad Unit (Fallback)</label>
                        <select class="form-select" name="secondary_unit" id="edit_placement_secondary">
                            <option value="">None</option>
                            <?php foreach ($adUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name'] . ' (' . $unit['network_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tertiary Ad Unit (Fallback)</label>
                        <select class="form-select" name="tertiary_unit" id="edit_placement_tertiary">
                            <option value="">None</option>
                            <?php foreach ($adUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name'] . ' (' . $unit['network_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ad Frequency</label>
                        <input type="number" class="form-control" name="frequency" id="edit_placement_frequency" min="1" required>
                        <small class="text-muted">Show ad every X actions (e.g., every 5 taps)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Placement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editNetwork(network) {
    document.getElementById('edit_network_id').value = network.id;
    document.getElementById('edit_network_name').value = network.name;
    document.getElementById('edit_network_enabled').checked = network.is_enabled == 1;
    new bootstrap.Modal(document.getElementById('editNetworkModal')).show();
}

function deleteNetwork(id) {
    if (confirm('Delete this ad network? All associated ad units will also be deleted.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete_network"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function editUnit(unit) {
    document.getElementById('edit_unit_id').value = unit.id;
    document.getElementById('edit_unit_network').value = unit.network_id;
    document.getElementById('edit_unit_name').value = unit.name;
    document.getElementById('edit_unit_code').value = unit.unit_code;
    document.getElementById('edit_unit_type').value = unit.unit_type;
    document.getElementById('edit_unit_placement').value = unit.placement_key;
    document.getElementById('edit_unit_active').checked = unit.is_active == 1;
    new bootstrap.Modal(document.getElementById('editUnitModal')).show();
}

function deleteUnit(id) {
    if (confirm('Delete this ad unit?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete_unit"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function editPlacement(placement) {
    document.getElementById('edit_placement_id').value = placement.id;
    document.getElementById('edit_placement_key').textContent = placement.placement_key;
    document.getElementById('edit_placement_desc').textContent = placement.description;
    document.getElementById('edit_placement_primary').value = placement.primary_ad_unit_id || '';
    document.getElementById('edit_placement_secondary').value = placement.secondary_ad_unit_id || '';
    document.getElementById('edit_placement_tertiary').value = placement.tertiary_ad_unit_id || '';
    document.getElementById('edit_placement_frequency').value = placement.frequency;
    new bootstrap.Modal(document.getElementById('editPlacementModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
