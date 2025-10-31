<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['user_id'] ?? null;
$taps = $data['taps'] ?? 1;

if (!$userId) {
    jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
}

try {
    $db->beginTransaction();
    
    // Get user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_banned = 0");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $db->rollBack();
        jsonResponse(['success' => false, 'error' => 'User not found or banned'], 404);
    }
    
    // Update energy
    updateUserEnergy($userId);
    
    // Get updated energy
    $stmt = $db->prepare("SELECT energy FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentEnergy = $stmt->fetchColumn();
    
    // Get settings
    $tapReward = (float)getSetting('tap_reward', 5);
    $energyPerTap = (int)getSetting('energy_per_tap', 1);
    
    // Calculate how many taps are possible
    $maxTaps = floor($currentEnergy / $energyPerTap);
    $actualTaps = min($taps, $maxTaps);
    
    if ($actualTaps <= 0) {
        $db->rollBack();
        jsonResponse([
            'success' => false,
            'error' => 'Not enough energy',
            'energy' => (int)$currentEnergy
        ], 400);
    }
    
    // Calculate rewards
    $coinsEarned = $actualTaps * $tapReward;
    $energyUsed = $actualTaps * $energyPerTap;
    $newEnergy = $currentEnergy - $energyUsed;
    
    // Update user coins and energy
    $stmt = $db->prepare("UPDATE users SET coins = coins + ?, energy = ?, last_energy_update = NOW() WHERE id = ?");
    $stmt->execute([$coinsEarned, $newEnergy, $userId]);
    
    // Update stats
    $stmt = $db->prepare("UPDATE user_stats SET total_taps = total_taps + ? WHERE user_id = ?");
    $stmt->execute([$actualTaps, $userId]);
    
    // Add transaction
    addTransaction($userId, 'tap', $coinsEarned, "Earned from {$actualTaps} taps");
    
    // Check if ad should be shown based on tap frequency
    $tapAdFrequency = (int)getSetting('tap_ad_frequency', 7);
    
    // Get current total taps (already updated above)
    $stmt = $db->prepare("SELECT total_taps FROM user_stats WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalTaps = (int)$stmt->fetchColumn();
    
    // Show ad every N taps (when total_taps is divisible by frequency)
    // This ensures ads show at predictable intervals: 2, 4, 6, 8, etc. (if frequency=2)
    $shouldShowAd = ($tapAdFrequency > 0 && $totalTaps > 0 && ($totalTaps % $tapAdFrequency === 0));
    
    // Get updated user data
    $stmt = $db->prepare("SELECT coins, energy FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch();
    
    $db->commit();
    
    jsonResponse([
        'success' => true,
        'taps' => $actualTaps,
        'coins_earned' => $coinsEarned,
        'total_coins' => (float)$updatedUser['coins'],
        'energy' => (int)$updatedUser['energy'],
        'show_ad' => $shouldShowAd,
        'tap_reward' => $tapReward
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Tap Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to process taps'], 500);
}
?>
