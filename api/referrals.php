<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
}

try {
    // Get user referral code
    $stmt = $db->prepare("SELECT referral_code FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'User not found'], 404);
    }
    
    // Get referral list
    $stmt = $db->prepare("
        SELECT r.*, u.username, u.first_name, u.telegram_id, r.created_at, r.status
        FROM referrals r
        JOIN users u ON r.referred_id = u.id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$userId]);
    $referrals = $stmt->fetchAll();
    
    $referralList = [];
    foreach ($referrals as $ref) {
        $referralList[] = [
            'id' => $ref['id'],
            'username' => $ref['username'] ?: $ref['first_name'],
            'telegram_id' => $ref['telegram_id'],
            'status' => $ref['status'],
            'tasks_completed' => (int)$ref['tasks_completed'],
            'reward_given' => (bool)$ref['reward_given'],
            'joined_at' => $ref['created_at']
        ];
    }
    
    // Get stats
    $totalReferrals = count($referrals);
    $approvedReferrals = count(array_filter($referrals, fn($r) => $r['status'] === 'approved'));
    $pendingReferrals = $totalReferrals - $approvedReferrals;
    
    // Calculate total earnings
    $referralReward = (float)getSetting('referral_reward', 100);
    $totalEarnings = $approvedReferrals * $referralReward;
    
    // Get referral link - Use Telegram bot link instead of website
    $botUsername = str_replace('@', '', BOT_USERNAME);
    $referralLink = "https://t.me/{$botUsername}?start=" . $user['referral_code'];
    $shareMessage = "?? Join CoinTap Pro & Start Earning!\n\n";
    $telegramShareLink = "https://t.me/share/url?url=" . urlencode($referralLink) . "&text=" . urlencode($shareMessage);
    
    jsonResponse([
        'success' => true,
        'referral_code' => $user['referral_code'],
        'referral_link' => $referralLink,
        'telegram_share_link' => $telegramShareLink,
        'stats' => [
            'total' => $totalReferrals,
            'approved' => $approvedReferrals,
            'pending' => $pendingReferrals,
            'total_earnings' => $totalEarnings
        ],
        'referrals' => $referralList,
        'unlock_condition' => (int)getSetting('referral_unlock_tasks', 1),
        'reward_per_referral' => $referralReward
    ]);
    
} catch (Exception $e) {
    error_log("Referrals Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to fetch referrals'], 500);
}
?>
