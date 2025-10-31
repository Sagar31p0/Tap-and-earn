<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $shortCode = sanitizeInput($_POST['short_code']);
        $originalUrl = sanitizeInput($_POST['original_url']);
        $mode = $_POST['mode'];
        $taskId = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;
        $adUnitId = !empty($_POST['ad_unit_id']) ? (int)$_POST['ad_unit_id'] : null;
        
        // Check if short code already exists
        $stmt = $db->prepare("SELECT id FROM short_links WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        
        if ($stmt->fetch()) {
            $error = "Short code already exists. Please choose a different one.";
        } else {
            $stmt = $db->prepare("INSERT INTO short_links (short_code, original_url, mode, task_id, ad_unit_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$shortCode, $originalUrl, $mode, $taskId, $adUnitId]);
            $success = "Short link created successfully";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $shortCode = sanitizeInput($_POST['short_code']);
        $originalUrl = sanitizeInput($_POST['original_url']);
        $mode = $_POST['mode'];
        $taskId = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;
        $adUnitId = !empty($_POST['ad_unit_id']) ? (int)$_POST['ad_unit_id'] : null;
        
        // Check if short code already exists for another link
        $stmt = $db->prepare("SELECT id FROM short_links WHERE short_code = ? AND id != ?");
        $stmt->execute([$shortCode, $id]);
        
        if ($stmt->fetch()) {
            $error = "Short code already exists. Please choose a different one.";
        } else {
            $stmt = $db->prepare("UPDATE short_links SET short_code = ?, original_url = ?, mode = ?, task_id = ?, ad_unit_id = ? WHERE id = ?");
            $stmt->execute([$shortCode, $originalUrl, $mode, $taskId, $adUnitId, $id]);
            $success = "Short link updated successfully";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM short_links WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Short link deleted successfully";
    } elseif ($action === 'generate_code') {
        // Generate a random short code
        $shortCode = generateShortCode();
        echo json_encode(['success' => true, 'code' => $shortCode]);
        exit;
    }
}

// Function to generate random short code
function generateShortCode($length = 6) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Get short links
$stmt = $db->query("SELECT sl.*, 
                    t.title as task_title,
                    au.name as ad_unit_name
                    FROM short_links sl
                    LEFT JOIN tasks t ON sl.task_id = t.id
                    LEFT JOIN ad_units au ON sl.ad_unit_id = au.id
                    ORDER BY sl.created_at DESC");
$shortLinks = $stmt->fetchAll();

// Get tasks for dropdown
$stmt = $db->query("SELECT id, title FROM tasks WHERE is_active = 1 ORDER BY title ASC");
$tasks = $stmt->fetchAll();

// Get ad units for dropdown
$stmt = $db->query("SELECT au.id, au.name, an.name as network_name 
                    FROM ad_units au 
                    LEFT JOIN ad_networks an ON au.network_id = an.id
                    WHERE au.is_active = 1 
                    ORDER BY au.name ASC");
$adUnits = $stmt->fetchAll();

// Statistics
$stmt = $db->query("SELECT COUNT(*) FROM short_links");
$totalLinks = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(clicks) FROM short_links");
$totalClicks = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(conversions) FROM short_links");
$totalConversions = $stmt->fetchColumn() ?: 0;

$conversionRate = $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 2) : 0;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-link"></i> URL Shortener Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addLinkModal">
        <i class="fas fa-plus"></i> Create Short Link
    </button>
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

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Short Links</h6>
            <h3><?php echo number_format($totalLinks); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Clicks</h6>
            <h3><?php echo number_format($totalClicks); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Conversions</h6>
            <h3><?php echo number_format($totalConversions); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Conversion Rate</h6>
            <h3><?php echo $conversionRate; ?>%</h3>
        </div>
    </div>
</div>

<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> All Short Links</h5>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Code</th>
                    <th>Short URL</th>
                    <th>Original URL</th>
                    <th>Mode</th>
                    <th>Linked To</th>
                    <th>Clicks</th>
                    <th>Conversions</th>
                    <th>Rate</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shortLinks as $link): ?>
                <?php 
                $rate = $link['clicks'] > 0 ? round(($link['conversions'] / $link['clicks']) * 100, 1) : 0;
                // Generate Telegram bot URL instead of web URL
                $shortUrl = 'https://t.me/' . str_replace('@', '', BOT_USERNAME) . '?start=s_' . $link['short_code'];
                ?>
                <tr>
                    <td><?php echo $link['id']; ?></td>
                    <td><code><?php echo htmlspecialchars($link['short_code']); ?></code></td>
                    <td>
                        <a href="<?php echo $shortUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?php echo $shortUrl; ?>')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </td>
                    <td>
                        <small><?php echo htmlspecialchars(substr($link['original_url'], 0, 50)); ?>...</small>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $link['mode'] === 'task_video' ? 'info' : 'primary'; ?>">
                            <?php echo str_replace('_', ' ', ucfirst($link['mode'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        if ($link['mode'] === 'task_video' && $link['task_title']) {
                            echo '<small>Task: ' . htmlspecialchars($link['task_title']) . '</small>';
                        } elseif ($link['ad_unit_name']) {
                            echo '<small>Ad: ' . htmlspecialchars($link['ad_unit_name']) . '</small>';
                        } else {
                            echo '<small class="text-muted">None</small>';
                        }
                        ?>
                    </td>
                    <td><?php echo number_format($link['clicks']); ?></td>
                    <td><?php echo number_format($link['conversions']); ?></td>
                    <td><?php echo $rate; ?>%</td>
                    <td><?php echo date('Y-m-d', strtotime($link['created_at'])); ?></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick='editLink(<?php echo json_encode($link); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteLink(<?php echo $link['id']; ?>)">
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

<!-- Add Link Modal -->
<div class="modal fade" id="addLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Short Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Short Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="short_code" id="add_short_code" required pattern="[a-zA-Z0-9]{3,20}">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode('add_short_code')">
                                <i class="fas fa-random"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted">Alphanumeric only, 3-20 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Original URL</label>
                        <input type="url" class="form-control" name="original_url" placeholder="https://example.com" required>
                        <small class="text-muted">The destination URL</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mode</label>
                        <select class="form-select" name="mode" id="add_mode" onchange="toggleModeFields('add')" required>
                            <option value="direct_ad">Direct Ad</option>
                            <option value="task_video">Task Video</option>
                        </select>
                        <small class="text-muted">How the link should behave</small>
                    </div>
                    
                    <div class="mb-3" id="add_task_field" style="display:none;">
                        <label class="form-label">Link to Task (Optional)</label>
                        <select class="form-select" name="task_id">
                            <option value="">None</option>
                            <?php foreach ($tasks as $task): ?>
                                <option value="<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="add_ad_field">
                        <label class="form-label">Link to Ad Unit (Optional)</label>
                        <select class="form-select" name="ad_unit_id">
                            <option value="">None</option>
                            <?php foreach ($adUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name'] . ' (' . $unit['network_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Preview Bot URL:</strong> <span id="add_preview">https://t.me/<?php echo str_replace('@', '', BOT_USERNAME); ?>?start=s_[code]</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Link Modal -->
<div class="modal fade" id="editLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Short Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Short Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="short_code" id="edit_short_code" required pattern="[a-zA-Z0-9]{3,20}">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode('edit_short_code')">
                                <i class="fas fa-random"></i> Generate
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Original URL</label>
                        <input type="url" class="form-control" name="original_url" id="edit_url" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mode</label>
                        <select class="form-select" name="mode" id="edit_mode" onchange="toggleModeFields('edit')" required>
                            <option value="direct_ad">Direct Ad</option>
                            <option value="task_video">Task Video</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="edit_task_field">
                        <label class="form-label">Link to Task (Optional)</label>
                        <select class="form-select" name="task_id" id="edit_task">
                            <option value="">None</option>
                            <?php foreach ($tasks as $task): ?>
                                <option value="<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="edit_ad_field">
                        <label class="form-label">Link to Ad Unit (Optional)</label>
                        <select class="form-select" name="ad_unit_id" id="edit_ad">
                            <option value="">None</option>
                            <?php foreach ($adUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name'] . ' (' . $unit['network_name'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Preview:</strong> <span id="edit_preview"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update preview when short code changes
document.getElementById('add_short_code').addEventListener('input', function() {
    document.getElementById('add_preview').textContent = 'https://t.me/<?php echo str_replace('@', '', BOT_USERNAME); ?>?start=s_' + this.value;
});

function generateCode(fieldId) {
    const length = 6;
    const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < length; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById(fieldId).value = code;
    
    // Update preview if it's the add form
    if (fieldId === 'add_short_code') {
        document.getElementById('add_preview').textContent = 'https://t.me/<?php echo str_replace('@', '', BOT_USERNAME); ?>?start=s_' + code;
    } else {
        document.getElementById('edit_preview').textContent = 'https://t.me/<?php echo str_replace('@', '', BOT_USERNAME); ?>?start=s_' + code;
    }
}

function toggleModeFields(prefix) {
    const mode = document.getElementById(prefix + '_mode').value;
    const taskField = document.getElementById(prefix + '_task_field');
    const adField = document.getElementById(prefix + '_ad_field');
    
    if (mode === 'task_video') {
        taskField.style.display = 'block';
        adField.style.display = 'none';
    } else {
        taskField.style.display = 'none';
        adField.style.display = 'block';
    }
}

function editLink(link) {
    document.getElementById('edit_id').value = link.id;
    document.getElementById('edit_short_code').value = link.short_code;
    document.getElementById('edit_url').value = link.original_url;
    document.getElementById('edit_mode').value = link.mode;
    document.getElementById('edit_task').value = link.task_id || '';
    document.getElementById('edit_ad').value = link.ad_unit_id || '';
    document.getElementById('edit_preview').textContent = 'https://t.me/<?php echo str_replace('@', '', BOT_USERNAME); ?>?start=s_' + link.short_code;
    
    toggleModeFields('edit');
    new bootstrap.Modal(document.getElementById('editLinkModal')).show();
}

function deleteLink(id) {
    if (confirm('Are you sure you want to delete this short link?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    }, function() {
        prompt('Copy this link:', text);
    });
}
</script>

<?php require_once 'footer.php'; ?>
