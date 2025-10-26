<?php
require_once '../config.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $withdrawalId = $_POST['withdrawal_id'] ?? 0;
    
    if ($action === 'approve' && $withdrawalId) {
        $transactionId = sanitizeInput($_POST['transaction_id'] ?? '');
        $paymentProof = sanitizeInput($_POST['payment_proof'] ?? '');
        
        $stmt = $db->prepare("UPDATE withdrawals SET status = 'approved', processed_at = NOW(), transaction_id = ?, payment_proof = ? WHERE id = ?");
        $stmt->execute([$transactionId, $paymentProof, $withdrawalId]);
        $success = "Withdrawal approved successfully";
    } elseif ($action === 'reject' && $withdrawalId) {
        $adminNote = sanitizeInput($_POST['admin_note'] ?? '');
        
        $db->beginTransaction();
        
        // Get withdrawal details
        $stmt = $db->prepare("SELECT user_id, amount FROM withdrawals WHERE id = ?");
        $stmt->execute([$withdrawalId]);
        $withdrawal = $stmt->fetch();
        
        // Refund coins
        updateUserCoins($withdrawal['user_id'], $withdrawal['amount'], true);
        addTransaction($withdrawal['user_id'], 'admin_credit', $withdrawal['amount'], "Withdrawal #{$withdrawalId} rejected - refund");
        
        // Update withdrawal status
        $stmt = $db->prepare("UPDATE withdrawals SET status = 'rejected', processed_at = NOW(), admin_note = ? WHERE id = ?");
        $stmt->execute([$adminNote, $withdrawalId]);
        
        $db->commit();
        $success = "Withdrawal rejected and coins refunded";
    }
}

// Get withdrawals
$stmt = $db->query("SELECT w.*, u.username, u.first_name, u.telegram_id 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    ORDER BY 
                        CASE WHEN w.status = 'pending' THEN 0 ELSE 1 END,
                        w.created_at DESC");
$withdrawals = $stmt->fetchAll();
?>

<div class="page-header">
    <h2><i class="fas fa-wallet"></i> Withdrawal Management</h2>
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
                    <th>User</th>
                    <th>Amount</th>
                    <th>USD Value</th>
                    <th>Payment Method</th>
                    <th>Payment Details</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($withdrawals as $w): 
                    $usdValue = $w['amount'] * (float)getSetting('coin_to_usd_rate', 0.001);
                    $details = json_decode($w['payment_details'], true);
                ?>
                <tr>
                    <td><?php echo $w['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($w['username'] ?: $w['first_name']); ?><br>
                        <small class="text-muted"><?php echo $w['telegram_id']; ?></small>
                    </td>
                    <td><?php echo number_format($w['amount'], 2); ?></td>
                    <td>$<?php echo number_format($usdValue, 2); ?></td>
                    <td><?php echo $w['payment_method']; ?></td>
                    <td>
                        <small>
                            <?php 
                            if (is_array($details)) {
                                foreach ($details as $key => $value) {
                                    echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
                                }
                            } else {
                                echo htmlspecialchars($w['payment_details']);
                            }
                            ?>
                        </small>
                    </td>
                    <td>
                        <?php 
                        $badgeClass = $w['status'] === 'pending' ? 'warning' : ($w['status'] === 'approved' ? 'success' : 'danger');
                        echo "<span class='badge bg-{$badgeClass}'>" . ucfirst($w['status']) . "</span>";
                        ?>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($w['created_at'])); ?></td>
                    <td>
                        <?php if ($w['status'] === 'pending'): ?>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-success" onclick="approveWithdrawal(<?php echo $w['id']; ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rejectWithdrawal(<?php echo $w['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php elseif ($w['status'] === 'approved'): ?>
                            <button class="btn btn-sm btn-info" onclick="viewProof(<?php echo $w['id']; ?>, '<?php echo htmlspecialchars($w['payment_proof'] ?? ''); ?>', '<?php echo htmlspecialchars($w['transaction_id'] ?? ''); ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        <?php else: ?>
                            <small class="text-muted"><?php echo htmlspecialchars($w['admin_note']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Withdrawal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="withdrawal_id" id="approve_withdrawal_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" class="form-control" name="transaction_id" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Proof URL (optional)</label>
                        <input type="text" class="form-control" name="payment_proof" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Withdrawal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="withdrawal_id" id="reject_withdrawal_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Coins will be refunded to user's account.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" name="admin_note" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject & Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveWithdrawal(id) {
    document.getElementById('approve_withdrawal_id').value = id;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectWithdrawal(id) {
    document.getElementById('reject_withdrawal_id').value = id;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function viewProof(id, proof, txId) {
    alert('Transaction ID: ' + txId + '\nPayment Proof: ' + (proof || 'N/A'));
}
</script>

<?php require_once 'footer.php'; ?>
