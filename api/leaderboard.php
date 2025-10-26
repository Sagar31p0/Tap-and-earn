<?php
require_once '../config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$userId = $_GET['user_id'] ?? null;
$type = $_GET['type'] ?? getSetting('leaderboard_type', 'coins'); // coins, tasks, referrals

try {
    // Determine sorting column
    $sortColumn = 'u.coins';
    if ($type === 'tasks') {
        $sortColumn = 'us.tasks_completed';
    } elseif ($type === 'referrals') {
        $sortColumn = 'us.referrals_count';
    }
    
    // Get top 20 users
    $stmt = $db->prepare("
        SELECT 
            u.id, u.username, u.first_name, u.telegram_id, u.coins,
            us.tasks_completed, us.referrals_count, us.total_taps, us.total_spins
        FROM users u
        LEFT JOIN user_stats us ON u.id = us.user_id
        WHERE u.is_banned = 0
        ORDER BY {$sortColumn} DESC
        LIMIT 20
    ");
    $stmt->execute();
    $topUsers = $stmt->fetchAll();
    
    $leaderboard = [];
    $rank = 1;
    
    foreach ($topUsers as $user) {
        $value = 0;
        if ($type === 'coins') {
            $value = (float)$user['coins'];
        } elseif ($type === 'tasks') {
            $value = (int)$user['tasks_completed'];
        } elseif ($type === 'referrals') {
            $value = (int)$user['referrals_count'];
        }
        
        $leaderboard[] = [
            'rank' => $rank,
            'user_id' => $user['id'],
            'username' => $user['username'] ?: $user['first_name'],
            'value' => $value,
            'is_current_user' => $userId && $user['id'] == $userId
        ];
        $rank++;
    }
    
    // Get current user rank if not in top 20
    $userRank = null;
    $userValue = null;
    
    if ($userId) {
        $inTop20 = array_filter($leaderboard, fn($u) => $u['user_id'] == $userId);
        
        if (empty($inTop20)) {
            // Calculate user's rank
            $stmt = $db->prepare("
                SELECT COUNT(*) + 1 as rank
                FROM users u
                LEFT JOIN user_stats us ON u.id = us.user_id
                WHERE u.is_banned = 0 AND {$sortColumn} > (
                    SELECT {$sortColumn}
                    FROM users u2
                    LEFT JOIN user_stats us2 ON u2.id = us2.user_id
                    WHERE u2.id = ?
                )
            ");
            $stmt->execute([$userId]);
            $userRank = $stmt->fetchColumn();
            
            // Get user value
            $stmt = $db->prepare("
                SELECT u.coins, us.tasks_completed, us.referrals_count
                FROM users u
                LEFT JOIN user_stats us ON u.id = us.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                if ($type === 'coins') {
                    $userValue = (float)$userData['coins'];
                } elseif ($type === 'tasks') {
                    $userValue = (int)$userData['tasks_completed'];
                } elseif ($type === 'referrals') {
                    $userValue = (int)$userData['referrals_count'];
                }
            }
        }
    }
    
    jsonResponse([
        'success' => true,
        'type' => $type,
        'leaderboard' => $leaderboard,
        'user_rank' => $userRank ? [
            'rank' => (int)$userRank,
            'value' => $userValue
        ] : null
    ]);
    
} catch (Exception $e) {
    error_log("Leaderboard Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Failed to fetch leaderboard'], 500);
}
?>
