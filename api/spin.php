<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get spin availability
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
    }
    
    try {
        // Get user spins data
        $stmt = $db->prepare("SELECT * FROM user_spins WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userSpins = $stmt->fetch();
        
        if (!$userSpins) {
            // Create record
            $stmt = $db->prepare("INSERT INTO user_spins (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            $userSpins = ['spins_today' => 0, 'last_spin' => null, 'last_reset' => null];
        }
        
        // Check if need to reset daily
        $today = date('Y-m-d');
        if ($userSpins['last_reset'] !== $today) {
            $stmt = $db->prepare("UPDATE user_spins SET spins_today = 0, last_reset = ? WHERE user_id = ?");
            $stmt->execute([$today, $userId]);
            $userSpins['spins_today'] = 0;
        }
        
        $spinInterval = (int)getSetting('spin_interval_minutes', 60) * 60;
        $dailyLimit = (int)getSetting('spin_daily_limit', 10);
        
        $canSpin = true;
        $nextSpinTime = null;
        $reason = '';
        
        // Check daily limit
        if ($userSpins['spins_today'] >= $dailyLimit) {
            $canSpin = false;
            $reason = 'Daily limit reached';
        }
        
        // Check time interval
        if ($canSpin && $userSpins['last_spin']) {
            $lastSpinTime = strtotime($userSpins['last_spin']);
            $timeSinceLastSpin = time() - $lastSpinTime;
            
            if ($timeSinceLastSpin < $spinInterval) {
                $canSpin = false;
                $nextSpinTime = $lastSpinTime + $spinInterval;
                $reason = 'Wait for cooldown';
            }
        }
        
        // Get active spin blocks for display
        $stmt = $db->prepare("SELECT block_label, reward_value, probability FROM spin_config WHERE is_active = 1 ORDER BY sort_order");
        $stmt->execute();
        $blocks = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'can_spin' => $canSpin,
            'spins_today' => (int)$userSpins['spins_today'],
            'daily_limit' => $dailyLimit,
            'next_spin_time' => $nextSpinTime,
            'reason' => $reason,
            'blocks' => $blocks
        ]);
        
    } catch (Exception $e) {
        error_log("Spin Check Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to check spin availability'], 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process spin
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $doubleReward = $data['double_reward'] ?? false;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Verify user can spin (same checks as GET)
        $stmt = $db->prepare("SELECT * FROM user_spins WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userSpins = $stmt->fetch();
        
        $today = date('Y-m-d');
        if (!$userSpins || $userSpins['last_reset'] !== $today) {
            $spinsToday = 0;
        } else {
            $spinsToday = $userSpins['spins_today'];
        }
        
        $dailyLimit = (int)getSetting('spin_daily_limit', 10);
        
        if ($spinsToday >= $dailyLimit) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'Daily limit reached'], 400);
        }
        
        // Get active spin blocks
        $stmt = $db->prepare("SELECT * FROM spin_config WHERE is_active = 1 ORDER BY sort_order");
        $stmt->execute();
        $blocks = $stmt->fetchAll();
        
        if (empty($blocks)) {
            $db->rollBack();
            jsonResponse(['success' => false, 'error' => 'No spin blocks configured'], 500);
        }
        
        // Select random block based on probability
        $totalProbability = array_sum(array_column($blocks, 'probability'));
        $random = mt_rand(0, $totalProbability * 100) / 100;
        
        $cumulativeProbability = 0;
        $selectedBlock = null;
        
        foreach ($blocks as $block) {
            $cumulativeProbability += $block['probability'];
            if ($random <= $cumulativeProbability) {
                $selectedBlock = $block;
                break;
            }
        }
        
        if (!$selectedBlock) {
            $selectedBlock = $blocks[array_rand($blocks)];
        }
        
        $reward = (float)$selectedBlock['reward_value'];
        
        if ($doubleReward && $selectedBlock['allow_double']) {
            $reward *= 2;
        }
        
        // Update user coins
        updateUserCoins($userId, $reward, true);
        
        // Update user spins
        if ($userSpins) {
            $stmt = $db->prepare("UPDATE user_spins SET spins_today = spins_today + 1, last_spin = NOW(), last_reset = ? WHERE user_id = ?");
            $stmt->execute([$today, $userId]);
        } else {
            $stmt = $db->prepare("INSERT INTO user_spins (user_id, spins_today, last_spin, last_reset) VALUES (?, 1, NOW(), ?)");
            $stmt->execute([$userId, $today]);
        }
        
        // Update stats
        $stmt = $db->prepare("UPDATE user_stats SET total_spins = total_spins + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Add transaction
        addTransaction($userId, 'spin', $reward, "Spin wheel: {$selectedBlock['block_label']}");
        
        // Get updated coins
        $stmt = $db->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $totalCoins = $stmt->fetchColumn();
        
        $db->commit();
        
        jsonResponse([
            'success' => true,
            'block' => $selectedBlock['block_label'],
            'reward' => $reward,
            'doubled' => $doubleReward && $selectedBlock['allow_double'],
            'total_coins' => (float)$totalCoins,
            'spins_today' => $spinsToday + 1
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Spin Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to process spin'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}
?>
