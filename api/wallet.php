<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get wallet info
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
    }
    
    try {
        // Get user balance
        $stmt = $db->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'User not found'], 404);
        }
        
        // Get payment methods
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY id");
        $stmt->execute();
        $paymentMethods = $stmt->fetchAll();
        
        // Get withdrawal history
        $stmt = $db->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$userId]);
        $withdrawals = $stmt->fetchAll();
        
        // Get settings
        $minWithdrawal = (float)getSetting('min_withdrawal', 10);
        $coinToUsdRate = (float)getSetting('coin_to_usd_rate', 0.001);
        
        $usdValue = $user['coins'] * $coinToUsdRate;
        
        jsonResponse([
            'success' => true,
            'balance' => [
                'coins' => (float)$user['coins'],
                'usd_value' => round($usdValue, 2)
            ],
            'min_withdrawal' => $minWithdrawal,
            'coin_to_usd_rate' => $coinToUsdRate,
            'payment_methods' => $paymentMethods,
            'withdrawals' => array_map(function($w) {
                return [
                    'id' => $w['id'],
                    'amount' => (float)$w['amount'],
                    'payment_method' => $w['payment_method'],
                    'status' => $w['status'],
                    'created_at' => $w['created_at'],
                    'processed_at' => $w['processed_at'],
                    'transaction_id' => $w['transaction_id']
                ];
            }, $withdrawals)
        ]);
        
    } catch (Exception $e) {
        error_log("Wallet Get Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch wallet info'], 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Request withdrawal
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $amount = $data['amount'] ?? null;
    $paymentMethod = $data['payment_method'] ?? null;
    $paymentDetails = $data['payment_details'] ?? null;
    
    if (!$userId || !$amount || !$paymentMethod || !$paymentDetails) {
        jsonResponse(['success' => false, 'error' => 'All fields required'], 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Get user balance
        $stmt = $db->prepare("SELECT coins FROM users WHERE id = ? AND is_banned = 0");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'User not found or banned'], 404);
        }
        
        $minWithdrawal = (float)getSetting('min_withdrawal', 10);
        
        if ($amount < $minWithdrawal) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => "Minimum withdrawal is {$minWithdrawal} coins"], 400);
        }
        
        if ($user['coins'] < $amount) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Insufficient balance'], 400);
        }
        
        // Verify payment method exists
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE name = ? AND is_active = 1");
        $stmt->execute([$paymentMethod]);
        $method = $stmt->fetch();
        
        if (!$method) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Invalid payment method'], 400);
        }
        
        // Create withdrawal request
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, payment_details, status) 
                             VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$userId, $amount, $paymentMethod, json_encode($paymentDetails)]);
        $withdrawalId = $db->lastInsertId();
        
        // Deduct coins
        updateUserCoins($userId, $amount, false);
        
        // Add transaction
        addTransaction($userId, 'withdrawal', -$amount, "Withdrawal request #{$withdrawalId}");
        
        $db->commit();
        
        jsonResponse([
            'success' => true,
            'message' => 'Withdrawal request submitted',
            'withdrawal_id' => $withdrawalId,
            'status' => 'pending'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Wallet Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to process withdrawal'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}
?>
