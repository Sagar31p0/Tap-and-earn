<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Generate unique short code
                $shortCode = substr(md5(uniqid()), 0, 8);
                
                $stmt = $db->prepare("INSERT INTO short_links (short_code, original_url, mode, task_id, ad_unit_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $shortCode,
                    $_POST['original_url'],
                    $_POST['mode'],
                    $_POST['task_id'] ?? null,
                    $_POST['ad_unit_id'] ?? null,
                    isset($_POST['is_active']) ? 1 : 0
                ]);
                
                $success = "Short link created successfully! Code: " . $shortCode;
                break;
                
            case 'edit':
                $stmt = $db->prepare("UPDATE short_links SET original_url = ?, mode = ?, task_id = ?, ad_unit_id = ?, is_active = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['original_url'],
                    $_POST['mode'],
                    $_POST['task_id'] ?? null,
                    $_POST['ad_unit_id'] ?? null,
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['link_id']
                ]);
                $success = "Short link updated successfully!";
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM short_links WHERE id = ?");
                $stmt->execute([$_POST['link_id']]);
                $success = "Short link deleted successfully!";
                break;
        }
    }
}

// Get all short links
$links = $db->query("
    SELECT sl.*, t.title as task_title, u.name as ad_unit_name
    FROM short_links sl
    LEFT JOIN tasks t ON sl.task_id = t.id
    LEFT JOIN ad_units u ON sl.ad_unit_id = u.id
    ORDER BY sl.created_at DESC
")->fetchAll();

// Get tasks for dropdown
$tasks = $db->query("SELECT id, title FROM tasks WHERE is_active = 1")->fetchAll();

// Get ad units for dropdown
$adUnits = $db->query("SELECT u.id, u.name, n.name as network_name FROM ad_units u JOIN ad_networks n ON u.network_id = n.id WHERE u.placement = 'shortlink' AND u.status = 1")->fetchAll();

// Get base URL
$baseUrl = 'https://reqa.antipiracyforce.org/test';
?>

<div class="page-header">
    <h2><i class="fas fa-link"></i> URL Shortener Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createLinkModal">
        <i class="fas fa-plus"></i> Create Short Link
    </button>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> How URL Shortener Works</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-video"></i> Mode 1: Direct Ad</h6>
                    <p class="small text-muted">
                        User clicks link → Video ad plays → Automatically redirects to original URL after ad completion
                    </p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-tasks"></i> Mode 2: Task + Video</h6>
                    <p class="small text-muted">
                        User clicks link → Completes task → "Watch Video" button appears → Ad plays → Redirects to original URL
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stat-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> Short Links</h5>
    
    <div class="table-responsive">
        <table class="table table-hover" id="linksTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Code</th>
                    <th>Original URL</th>
                    <th>Mode</th>
                    <th>Task</th>
                    <th>Ad Unit</th>
                    <th>Clicks</th>
                    <th>Conversions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                <tr>
                    <td><?php echo $link['id']; ?></td>
                    <td>
                        <code><?php echo $link['short_code']; ?></code>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyLink('<?php echo $baseUrl; ?>/r/<?php echo $link['short_code']; ?>')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </td>
                    <td>
                        <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank" class="text-truncate d-block" style="max-width: 200px;">
                            <?php echo htmlspecialchars($link['original_url']); ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $link['mode'] === 'direct_ad' ? 'info' : 'warning'; ?>">
                            <?php echo $link['mode'] === 'direct_ad' ? 'Direct Ad' : 'Task + Video'; ?>
                        </span>
                    </td>
                    <td><?php echo $link['task_title'] ? htmlspecialchars($link['task_title']) : '-'; ?></td>
                    <td><?php echo $link['ad_unit_name'] ? htmlspecialchars($link['ad_unit_name']) : '-'; ?></td>
                    <td><?php echo number_format($link['click_count']); ?></td>
                    <td><?php echo number_format($link['conversion_count']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $link['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $link['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='editLink(<?php echo json_encode($link); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this link?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
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

<!-- Create Link Modal -->
<div class="modal fade" id="createLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Create Short Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Original URL *</label>
                        <input type="url" name="original_url" class="form-control" placeholder="https://example.com/destination" required>
                        <small class="text-muted">The final destination URL where users will be redirected</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Mode *</label>
                        <select name="mode" id="create_mode" class="form-select" onchange="toggleModeFields('create')" required>
                            <option value="direct_ad">Direct Ad (Ad → Redirect)</option>
                            <option value="task_video">Task + Video (Task → Ad → Redirect)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="create_task_field" style="display: none;">
                        <label>Select Task</label>
                        <select name="task_id" class="form-select">
                            <option value="">-- No Task --</option>
                            <?php foreach ($tasks as $task): ?>
                            <option value="<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Task that user must complete before watching ad</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Ad Unit *</label>
                        <select name="ad_unit_id" class="form-select" required>
                            <option value="">-- Select Ad Unit --</option>
                            <?php foreach ($adUnits as $unit): ?>
                            <option value="<?php echo $unit['id']; ?>">
                                <?php echo htmlspecialchars($unit['network_name'] . ' - ' . $unit['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Ad that will be shown before redirect</small>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="create_active" checked>
                        <label class="form-check-label" for="create_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Create Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Link Modal -->
<div class="modal fade" id="editLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="link_id" id="edit_link_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Short Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Short Code</label>
                        <input type="text" id="edit_short_code" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label>Original URL *</label>
                        <input type="url" name="original_url" id="edit_original_url" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Mode *</label>
                        <select name="mode" id="edit_mode" class="form-select" onchange="toggleModeFields('edit')" required>
                            <option value="direct_ad">Direct Ad</option>
                            <option value="task_video">Task + Video</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="edit_task_field" style="display: none;">
                        <label>Select Task</label>
                        <select name="task_id" id="edit_task_id" class="form-select">
                            <option value="">-- No Task --</option>
                            <?php foreach ($tasks as $task): ?>
                            <option value="<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Ad Unit *</label>
                        <select name="ad_unit_id" id="edit_ad_unit_id" class="form-select" required>
                            <option value="">-- Select Ad Unit --</option>
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
                    <button type="submit" class="btn btn-gradient">Update Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModeFields(prefix) {
    const mode = document.getElementById(prefix + '_mode').value;
    const taskField = document.getElementById(prefix + '_task_field');
    
    if (mode === 'task_video') {
        taskField.style.display = 'block';
    } else {
        taskField.style.display = 'none';
    }
}

function editLink(link) {
    document.getElementById('edit_link_id').value = link.id;
    document.getElementById('edit_short_code').value = link.short_code;
    document.getElementById('edit_original_url').value = link.original_url;
    document.getElementById('edit_mode').value = link.mode;
    document.getElementById('edit_task_id').value = link.task_id || '';
    document.getElementById('edit_ad_unit_id').value = link.ad_unit_id || '';
    document.getElementById('edit_is_active').checked = link.is_active == 1;
    
    toggleModeFields('edit');
    
    new bootstrap.Modal(document.getElementById('editLinkModal')).show();
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    });
}

$(document).ready(function() {
    $('#linksTable').DataTable({
        order: [[0, 'desc']]
    });
});
</script>

<?php require_once 'footer.php'; ?>
