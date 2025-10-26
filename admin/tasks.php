<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $url = sanitizeInput($_POST['url']);
        $reward = (float)$_POST['reward'];
        $icon = sanitizeInput($_POST['icon']);
        $type = $_POST['type'];
        $adNetwork = sanitizeInput($_POST['ad_network'] ?? '');
        
        $stmt = $db->prepare("INSERT INTO tasks (title, description, url, reward, icon, type, ad_network) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $url, $reward, $icon, $type, $adNetwork]);
        $success = "Task created successfully";
    } elseif ($action === 'update') {
        $taskId = $_POST['task_id'];
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $url = sanitizeInput($_POST['url']);
        $reward = (float)$_POST['reward'];
        $icon = sanitizeInput($_POST['icon']);
        $type = $_POST['type'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE tasks SET title = ?, description = ?, url = ?, reward = ?, icon = ?, type = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $description, $url, $reward, $icon, $type, $isActive, $taskId]);
        $success = "Task updated successfully";
    } elseif ($action === 'delete') {
        $taskId = $_POST['task_id'];
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $success = "Task deleted successfully";
    }
}

$stmt = $db->query("SELECT * FROM tasks ORDER BY sort_order ASC, id DESC");
$tasks = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-tasks"></i> Task Management</h2>
    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="fas fa-plus"></i> Add New Task
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
                    <th>Title</th>
                    <th>Type</th>
                    <th>Reward</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Completions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): 
                    $stmt = $db->prepare("SELECT COUNT(*) FROM user_tasks WHERE task_id = ? AND status = 'completed'");
                    $stmt->execute([$task['id']]);
                    $completions = $stmt->fetchColumn();
                ?>
                <tr>
                    <td><?php echo $task['id']; ?></td>
                    <td>
                        <i class="<?php echo $task['icon']; ?>"></i>
                        <?php echo htmlspecialchars($task['title']); ?>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $task['type'] === 'daily' ? 'info' : 'primary'; ?>">
                            <?php echo ucfirst($task['type']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($task['reward'], 2); ?> coins</td>
                    <td><small><?php echo htmlspecialchars(substr($task['url'], 0, 30)); ?>...</small></td>
                    <td>
                        <?php if ($task['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo number_format($completions); ?></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick='editTask(<?php echo json_encode($task); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTask(<?php echo $task['id']; ?>)">
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

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (Font Awesome)</label>
                            <input type="text" class="form-control" name="icon" value="fas fa-tasks" required>
                            <small class="text-muted">Example: fas fa-star</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL *</label>
                        <input type="url" class="form-control" name="url" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reward (coins) *</label>
                            <input type="number" class="form-control" name="reward" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="one_time">One-Time</option>
                                <option value="daily">Daily</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ad Network</label>
                            <select class="form-select" name="ad_network">
                                <option value="">Default</option>
                                <option value="adexium">Adexium</option>
                                <option value="monetag">Monetag</option>
                                <option value="adsgram">Adsgram</option>
                                <option value="richads">Richads</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTaskForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" class="form-control" name="icon" id="edit_icon" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL *</label>
                        <input type="url" class="form-control" name="url" id="edit_url" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reward *</label>
                            <input type="number" class="form-control" name="reward" id="edit_reward" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" id="edit_type" required>
                                <option value="one_time">One-Time</option>
                                <option value="daily">Daily</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTask(task) {
    document.getElementById('edit_task_id').value = task.id;
    document.getElementById('edit_title').value = task.title;
    document.getElementById('edit_description').value = task.description || '';
    document.getElementById('edit_url').value = task.url;
    document.getElementById('edit_reward').value = task.reward;
    document.getElementById('edit_icon').value = task.icon;
    document.getElementById('edit_type').value = task.type;
    document.getElementById('edit_is_active').checked = task.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editTaskModal')).show();
}

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input name="action" value="delete"><input name="task_id" value="' + taskId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
