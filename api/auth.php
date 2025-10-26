<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate Telegram data
$telegramId = $data['telegram_id'] ?? null;
$username = $data['username'] ?? '';
$firstName = $data['first_name'] ?? '';
$lastName = $data['last_name'] ?? '';
$referralCode = $data['referral_code'] ?? null;

if (!$telegramId) {
    jsonResponse(['success' => false, 'error' => 'Telegram ID required'], 400);
}

try {
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegramId]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update last active
        $stmt = $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Update energy
        updateUserEnergy($user['id']);
        
        // Get user stats
        $stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $stats = $stmt->fetch();
        
        // Get updated user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $user = $stmt->fetch();
        
        jsonResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'telegram_id' => $user['telegram_id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'coins' => (float)$user['coins'],
                'energy' => (int)$user['energy'],
                'referral_code' => $user['referral_code'],
                'stats' => $stats ?: [
                    'total_taps' => 0,
                    'total_spins' => 0,
                    'tasks_completed' => 0,
                    'games_played' => 0,
                    'ads_watched' => 0,
                    'referrals_count' => 0
                ]
            ]
        ]);
    } else {
        // Create new user
        $newReferralCode = generateReferralCode();
        
        $stmt = $db->prepare("INSERT INTO users (telegram_id, username, first_name, last_name, referral_code) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$telegramId, $username, $firstName, $lastName, $newReferralCode]);
        $userId = $db->lastInsertId();
        
        // Create user stats
        $stmt = $db->prepare("INSERT INTO user_stats (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // Create user spins record
        $stmt = $db->prepare("INSERT INTO user_spins (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // Handle referral
        if ($referralCode) {
            $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$referralCode]);
            $referrer = $stmt->fetch();
            
            if ($referrer) {
                $stmt = $db->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
                $stmt->execute([$referrer['id'], $userId]);
                
                $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
                $stmt->execute([$referrer['id'], $userId]);
            }
        }
        
        // Get new user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        jsonResponse([
            'success' => true,
            'new_user' => true,
            'user' => [
                'id' => $user['id'],
                'telegram_id' => $user['telegram_id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'coins' => (float)$user['coins'],
                'energy' => (int)$user['energy'],
                'referral_code' => $user['referral_code'],
                'stats' => [
                    'total_taps' => 0,
                    'total_spins' => 0,
                    'tasks_completed' => 0,
                    'games_played' => 0,
                    'ads_watched' => 0,
                    'referrals_count' => 0
                ]
            ]
        ]);
    }
} catch (Exception $e) {
    error_log("Auth Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Authentication failed'], 500);
}
?>
