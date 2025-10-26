<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
$newUsersToday = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM users WHERE DATE(last_active) = CURDATE()");
$activeToday = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(coins) as total FROM users");
$totalCoins = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(total_taps) FROM user_stats");
$totalTaps = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(total_spins) FROM user_stats");
$totalSpins = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(ads_watched) FROM user_stats");
$adsWatched = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(tasks_completed) FROM user_stats");
$tasksCompleted = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT COUNT(*) FROM games WHERE is_active = 1");
$activeGames = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM tasks WHERE is_active = 1");
$activeTasks = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'");
$pendingWithdrawals = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM referrals WHERE status = 'approved'");
$totalReferrals = $stmt->fetchColumn();

// Recent users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$recentUsers = $stmt->fetchAll();

// Recent withdrawals
$stmt = $db->query("SELECT w.*, u.username, u.first_name FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC LIMIT 10");
$recentWithdrawals = $stmt->fetchAll();
?>

<div class="page-header">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-gradient text-white">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <h6 class="text-muted mb-1">Total Users</h6>
                    <h3 class="mb-0"><?php echo number_format($totalUsers); ?></h3>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> <?php echo $newUsersToday; ?> today</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-gradient text-white">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <h6 class="text-muted mb-1">Active Today</h6>
                    <h3 class="mb-0"><?php echo number_format($activeToday); ?></h3>
                    <small class="text-muted"><?php echo round($activeToday / max($totalUsers, 1) * 100, 1); ?>% of total</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-gradient text-white">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <h6 class="text-muted mb-1">Total Coins</h6>
                    <h3 class="mb-0"><?php echo number_format($totalCoins); ?></h3>
                    <small class="text-muted">Distributed</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-gradient text-white">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <h6 class="text-muted mb-1">Pending Withdrawals</h6>
                    <h3 class="mb-0"><?php echo number_format($pendingWithdrawals); ?></h3>
                    <small class="text-muted">Need action</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Taps</h6>
            <h4><?php echo number_format($totalTaps); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Spins</h6>
            <h4><?php echo number_format($totalSpins); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Ads Watched</h6>
            <h4><?php echo number_format($adsWatched); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Tasks Completed</h6>
            <h4><?php echo number_format($tasksCompleted); ?></h4>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Active Games</h6>
            <h4><?php echo number_format($activeGames); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Active Tasks</h6>
            <h4><?php echo number_format($activeTasks); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6 class="text-muted">Total Referrals</h6>
            <h4><?php echo number_format($totalReferrals); ?></h4>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-user-plus"></i> Recent Users</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Coins</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td>@<?php echo htmlspecialchars($user['username'] ?: 'N/A'); ?></td>
                            <td><?php echo number_format($user['coins'], 2); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="users.php" class="btn btn-sm btn-gradient">View All Users</a>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-wallet"></i> Recent Withdrawals</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentWithdrawals as $w): ?>
                        <tr>
                            <td><?php echo $w['id']; ?></td>
                            <td><?php echo htmlspecialchars($w['username'] ?: $w['first_name']); ?></td>
                            <td><?php echo number_format($w['amount'], 2); ?></td>
                            <td>
                                <?php 
                                $badge = $w['status'] === 'pending' ? 'warning' : ($w['status'] === 'approved' ? 'success' : 'danger');
                                echo "<span class='badge bg-{$badge}'>{$w['status']}</span>";
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($w['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="withdrawals.php" class="btn btn-sm btn-gradient">Manage Withdrawals</a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <h5 class="mb-3"><i class="fas fa-bolt"></i> Quick Actions</h5>
            <div class="row">
                <div class="col-md-2 mb-2">
                    <a href="users.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-users"></i><br>Manage Users
                    </a>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="ads.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-ad"></i><br>Configure Ads
                    </a>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="tasks.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-tasks"></i><br>Add Tasks
                    </a>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="games.php" class="btn btn-outline-warning w-100">
                        <i class="fas fa-gamepad"></i><br>Manage Games
                    </a>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="spin.php" class="btn btn-outline-danger w-100">
                        <i class="fas fa-circle-notch"></i><br>Spin Settings
                    </a>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="broadcast.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-bullhorn"></i><br>Broadcast
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
